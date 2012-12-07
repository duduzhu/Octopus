#!/usr/bin/perl
use strict;
use Vampire;
my $dbh=Vampire::dbinit();
Vampire::detect_user_misalign("",$dbh);
$dbh->disconnect();
