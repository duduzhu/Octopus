#!/usr/bin/perl
use Vampire::DB;
my $dbh=DB::dbinit();
use strict;

my($omc, @filelist)=@ARGV;

foreach (@filelist)
{
    open SOURCE, $_;
    my @filter;
    if(/_absrie.csv$/)
    {
        @filter=(0,2,8,10,3,4,9);
    }
    if(/_abtrie.csv$/)
    {
        @filter=(0,2,10,11,3,4,12);
    }
    if(/_atcrie.csv$/)
    {
        @filter=(0,2,8,10,2,3,9);
    }
    if(/_amerie.csv$/)
    {
        @filter=(0,2,8,10,3,4,9);
    }
    while(<SOURCE>)
    {
        my @formated;
        my @splited = split(/;/);
        foreach(@filter)
        {
            my $clean = $splited[$_];
            $clean =~ s/(^\W+|\W+$)//g;
            @formated=(@formated,$clean);
        }
        if(exists $ENV{$formated[2]} && $formated[0] && $formated[1] && $formated[3] )
        {
        if($formated[0] =~ /BTS/)
	    {
            my @BTSID = split /\//,  $formated[1];
            chomp(@BTSID);
            $BTSID[0] =~ s/^\s*BSC\s*//;
            $BTSID[1] =~ s/^\s*BTS\s*//;
            $formated[1] = $BTSID[1]."\/".$BTSID[0];
	    }
	    my $user="";
	    $formated[1] =~ /^[^_]*_[^_]*_([^_]*)$/;
	    if($user)
	    {
    	    	$user = `curl http://172.24.12.75/BSC_web/Vampire/?touchUser=$1`;
	    	chomp($user);
	    	$user =~ s/<.*>//;
	    }
            DB::update_record($formated[0],$formated[1],$formated[2],$formated[6].'-'.$formated[3],$user,$omc ,$dbh,$formated[4],$formated[5]);
        }
    }
    close SOURCE;
}
$dbh->disconnect();
