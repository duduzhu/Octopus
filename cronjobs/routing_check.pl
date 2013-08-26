#!/usr/bin/perl
use Vampire::DB;
use strict;

my $dbh = DB::dbinit();
DB::detect_user_misalign($dbh);
DB::clear_isolate($dbh);
$dbh->disconnect();
