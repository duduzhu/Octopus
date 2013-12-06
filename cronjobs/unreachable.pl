#!/usr/bin/perl
use strict;
use Vampire::DB;
my ($source) = @ARGV;
my $dbh=DB::dbinit();

my $all_unreachable_parents = $dbh->prepare("SELECT id FROM parent where SOURCE='$source'");
$all_unreachable_parents->execute();
while(my $p_id = $all_unreachable_parents->fetchrow())
{
    my $update = $dbh->prepare("UPDATE parent SET SOURCE='XXX$source' where id=$p_id");
    $update->execute();
}

my $all_unreachable_metas = $dbh->prepare("SELECT id FROM meta where SOURCE='$source'");
$all_unreachable_metas->execute();
while(my $m_id = $all_unreachable_metas->fetchrow())
{
    my $update = $dbh->prepare("UPDATE meta SET SOURCE='XXX$source' where id=$m_id");
    $update->execute();
}

$dbh->disconnect();
