#!/usr/bin/perl
package IP;
use strict;
sub num2ip
{
    my ($number) = @_;
    my $IP=sprintf("%08x",$number);
    $IP =~ /(..)(..)(..)(..)/;
    return (hex $1).".".(hex $2).".".(hex $3).".".(hex $4);
}
sub ip2num
{
    my ($ip) = @_;
    my @ip = split /\./, $ip;
    return hex sprintf("%02x",$ip[0]).sprintf("%02x",$ip[1]).sprintf("%02x",$ip[2]).sprintf("%02x",$ip[3]);
}

1;
