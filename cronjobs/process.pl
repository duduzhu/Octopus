#!/usr/bin/perl
use strict;
use Vampire;
my $dbh=Vampire::dbinit();
while(chomp($_=<>))
{
    my @formated = split(/;/);
    Vampire::update_record($formated[0],$formated[1],$formated[2],$formated[3], "", $dbh,"","");
}

$dbh->disconnect();
