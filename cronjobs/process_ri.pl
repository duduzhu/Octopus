#!/usr/bin/perl
use strict;
use Vampire::DB;
my $dbh=DB::dbinit();

foreach (@ARGV)
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
            DB::update_record($formated[0],$formated[1],$formated[2],$formated[6].'-'.$formated[3], "", $dbh,$formated[4],$formated[5]);
        }
    }
    close SOURCE;
}
$dbh->disconnect();
