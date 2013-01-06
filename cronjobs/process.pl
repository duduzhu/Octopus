#!/usr/bin/perl
use strict;
use Vampire;
my $dbh=Vampire::dbinit();
while(chomp($_=<>))
{
    next if /^#/;

    my @formated = split(/[\,,;]/);
    next if @formated < 4 || @formated >5;
        if($formated[4])
        {
            Vampire::update_record($formated[0],$formated[1],$formated[2],$formated[3], $formated[4], $dbh,"","");
        }
        else
        {
            Vampire::update_record($formated[0],$formated[1],$formated[2],$formated[3], "", $dbh,"","");
        }
}

$dbh->disconnect();
