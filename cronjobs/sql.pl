#!/usr/bin/perl
use DBI;

# Connect to target DB
my $dbh = DBI->connect("DBI:mysql:database=vampire;host=localhost","user","user", {'RaiseError' => 1});

# Insert one row 
#my $rows = $dbh->do("INSERT INTO test (id, name) VALUES (1, 'eygle')");
my $sth = $dbh->prepare("select * from meta");
$sth->execute(); 
while(my $ref = $sth->fetchrow_hashref())
{
    print $ref-> ;
}

# query 
#my $sqr = $dbh->prepare("SELECT name FROM test");
#$sqr->execute();

#while(my $ref = $sqr->fetchrow_hashref()) {
#   print "$ref->{'name'}\n";
#}

$dbh->disconnect();
# load module
# use DBI;
#
# # connect
# my $dbh = DBI->connect("DBI:mysql:database=db2;host=localhost", "joe", "guessme", {'RaiseError' => 1});
#
# # execute INSERT query
# my $rows = $dbh->do("INSERT INTO users (id, username, country) VALUES (4, 'jay', 'CZ')");
# print "$rows row(s) affected ";
#
# # execute SELECT query
# my $sth = $dbh->prepare("SELECT username, country FROM users");
# $sth->execute();
#
# # iterate through resultset
# # print values
# while(my $ref = $sth->fetchrow_hashref()) {
#     print "User: $ref-> ";
#         print "Country: $ref-> ";
#             print "---------- ";
#             }
#
#             # clean up
#             $dbh->disconnect();
