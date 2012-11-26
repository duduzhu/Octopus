#!/usr/bin/perl
use strict;
use DBI;

foreach (@ARGV)
{
    open SOURCE, $_;
    my @filter;
    if(/_absrie.csv$/)
    {
        @filter=(0,2,8,10,3,4);
    }
    if(/_abtrie.csv$/)
    {
        @filter=(0,2,10,11,3,4);
    }
    if(/_atcrie.csv$/)
    {
        @filter=(0,2,8,10,2,3);
    }
    if(/_amerie.csv$/)
    {
        @filter=(0,2,8,10,3,4);
    }
    while(<SOURCE>)
    {
        my @formated;
        my @splited = split(/;/);
        foreach(@filter)
        {
            my $clean = $splited[$_];
            $clean =~ s/\W//g;
            @formated=(@formated,$clean);
        }
        if(exists $ENV{$formated[2]})
        {
# Here we insert the log into db
            print "@formated\n";
#
        }
    }
}
