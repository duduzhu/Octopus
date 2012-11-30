#!/usr/bin/perl
use strict;
use DBI;
use Vampire;
my $dbh=Vampire::dbinit();
while(<>)
{
    my @formated = split(/;/);
    Vampire::update_record($formated[0],$formated[1],$formated[2],$formated[3], "", $dbh,"","");
}

$dbh->disconnect();
