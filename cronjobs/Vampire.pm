#!/usr/bin/perl
package Vampire;
use strict;
use DBI;
#use Mail::Sendmail;

sub num2ip
{
    my ($number) = @_;
    my $IP=sprintf("%08x",$number);
    $IP =~ /(..)(..)(..)(..)/;
    return (hex $1).".".(hex $2).".".(hex $3).".".(hex $4);
}
sub ip2num
{
    my ($ip) = @_;
    my @ip = split /\./, $ip;
    return hex sprintf("%02x",$ip[0]).sprintf("%02x",$ip[1]).sprintf("%02x",$ip[2]).sprintf("%02x",$ip[3]);
}

sub dbinit
{
    DBI->connect('DBI:mysql:vampire;host=172.24.12.75', 'user', 'user');
}

sub update_record
{
    chomp(my($p_mn, $p_sn, $m_mn, $m_sn, $user, $dbh, $date, $time) = @_);
    if(!$p_mn || !$m_mn || !$m_sn)
    {
        return;
    }
    my ($meta_id,$meta_user) = get_or_insert($m_mn, $m_sn,$user,"meta",$dbh);
    my ($parent_id,$parent_user) = get_or_insert($p_mn, $p_sn,$user,"parent",$dbh);
    update_link($meta_id, $parent_id, $date, $time, $dbh);
}

sub own_meta_by_sn
{
    my($m_sn, $user, $dbh) = @_;
    my $sth = $dbh->prepare("SELECT id FROM meta where sn=\'$m_sn\'");
    $sth->execute();
    own_meta_by_id($sth->fetchrow(),$user,$dbh);
}
sub own_meta_by_id
{
    my($m_id, $user, $dbh) = @_;
    my $sth = $dbh->prepare("SELECT USER FROM meta where id=\'$m_id\'");
    $sth->execute();
    my $current_user = $sth->fetchrow();
    if($current_user ne $user)
    {
        $dbh->do("UPDATE meta SET USER=\'$user\' where id=$m_id");
        detect_user_misalign($m_id,$dbh);
    }
}
sub own_parent_by_sn
{
    my($p_sn, $user, $dbh) = @_;
    my $sth = $dbh->prepare("SELECT id FROM parent where SN=\'$p_sn\'");
    $sth->execute();
    my $p_id = $sth->fetchrow();
    own_parent_by_id($p_id, $user, $dbh);
}
sub own_parent_by_id
{
    my($p_id, $user, $dbh) = @_;
    my $sth = $dbh->prepare("SELECT id_meta FROM link where id_parent=$p_id");
    $sth->execute();
    while(my $m_id = $sth->fetchrow())
    {
        own_meta_by_id($m_id,$user,$dbh);
    }
}
sub get_user
{
    my ($id,$table,$dbh) = @_;
    my $sth = $dbh->prepare("SELECT USER FROM $table where id=$id");
    $sth->execute();
    return $sth->fetchrow();
}

sub get_or_insert
{
    my($mn, $sn, $user,$table,$dbh) = @_;
    my $sth = $dbh->prepare("SELECT id FROM $table where sn=\'$sn\'");
    $sth->execute();

    if(my $id = $sth->fetchrow())
    {
        return ($id,get_user($id,$table,$dbh));
    }
    else
    {
        $dbh->do("INSERT INTO $table (SN, MNEMONIC, USER) VALUES(\'$sn\',\'$mn\',\'$user\')");
        $sth = $dbh->prepare("SELECT id FROM $table where SN=\'$sn\'");
        $sth->execute();
        return $sth->fetchrow();
    }
}

sub detect_user_misalign
{
    my %mail = ( To    => $ENV{REPORT_MAIL},
                 From      => 'Vampire@'.`hostname`,
                 Subject => '[Vampire] Misalign warning on '.`date`,
                 Message => ""
               );
    my ($meta_id, $dbh) = @_;
    my $sth;
    if($meta_id)
    {
        $sth = $dbh->prepare("SELECT * FROM link where id_meta=\'$meta_id\'");
    }
    else
    {
        $sth = $dbh->prepare("SELECT * FROM link");
    }
    $sth->execute();
    while(my $row=$sth->fetchrow_hashref())
    {

        my $meta_user = get_user($row->{id_meta}, "meta", $dbh);
        my $parent_user = get_user($row->{id_parent}, "parent", $dbh);

        if($meta_user && $parent_user && $meta_user ne $parent_user)
        {
        my $sth = $dbh->prepare("SELECT * FROM meta where id=\'$row->{id_meta}\'");
        $sth->execute();
        my $result = $sth->fetchrow_hashref();
        $mail{Message} .= "$meta_user loses control of $result->{MNEMONIC}-$result->{SN}, it is attached to ";
        $sth = $dbh->prepare("SELECT * FROM parent where id=\'$row->{id_parent}\'");
        $sth->execute();
        $result = $sth->fetchrow_hashref();
        $mail{Message} .= "$result->{MNEMONIC}-$result->{SN} of $parent_user\n";
        }
    }
    if($mail{Message})
    {
#sendmail %mail;
    }
}
sub update_link
{
    use HTTP::Date;
    my $recordtimestamp;
    my $lasttimestamp;
    my ($meta_id, $parent_id, $date, $time, $dbh) = @_;
    my $sth = $dbh->prepare("SELECT id_parent FROM link where id_meta=\'$meta_id\'");
    $sth->execute();
    if($date && $time)
    {
        $date =~ m/(\d{2})\.(\d{2})\.(\d{4})/;
        $recordtimestamp="$3-$2-$1 $time";
    }
    else
    {
        chomp($date = `date +%Y-%m-%d`);
        chomp($time = `date +%H:%M:%S`);
        $recordtimestamp="$date $time";
    }

    if(my $current_parent = $sth->fetchrow())
    {
        $lasttimestamp=$sth->fetchrow();
        if($parent_id ne $current_parent)
        #Things changed
        {
            $sth = $dbh->prepare("SELECT TIMESTAMP FROM link where id_meta=\'$meta_id\'");
            $sth->execute();
            if(str2time($recordtimestamp) gt str2time($lasttimestamp))
            {
                $dbh->do("INSERT INTO history (id_meta, id_parent, TIMESTAMP) VALUES ($meta_id, \'$parent_id\', \'$recordtimestamp\')");
                $dbh->do("UPDATE link SET id_parent=\'$parent_id\' where id_meta=\'$meta_id\'");
            }
        }

        $dbh->do("UPDATE link SET TIMESTAMP=\'$recordtimestamp\' where id_meta=\'$meta_id\'");
    }
    else
    #It does not exist
    {
        $dbh->do("INSERT INTO link (id_meta, id_parent, TIMESTAMP) VALUES(\'$meta_id\',\'$parent_id\',\'$recordtimestamp\')");
        $dbh->do("INSERT INTO history (id_meta, id_parent, TIMESTAMP) VALUES(\'$meta_id\',\'$parent_id\',\'$recordtimestamp\')");
    }
    detect_user_misalign($meta_id,$dbh);
}

1;
