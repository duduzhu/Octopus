#!/usr/bin/perl
use strict;
my ($subnet, $mask) = @ARGV;
my @subnet = split /\./, $subnet;
my @mask = split /\./, $mask;

sub num2ip
{
    my ($number) = @_;
    my $ip1=$number/(255*255*255);
    $number=$number%(255*255*255);
    my $ip2=$number/(255*255);
    $number=$number%(255*255);
    my $ip3=$number/(255);
    my $ip4=$number%(255);
    $ip1=~ s/\..*//;
    $ip2=~ s/\..*//;
    $ip3=~ s/\..*//;
    $ip4=~ s/\..*//;
    return "$ip1.$ip2.$ip3.$ip4";
}
sub ip2num
{
    my ($ip) = @_;
    my @ip = split /\./, $ip;
    return $ip[0]*255*255*255 + $ip[1]*255*255 + $ip[2]*255 + $ip[3];
}
my $ip = &ip2num($subnet) + 1;
my $MAC="";
$mask[0] = 255 - $mask[0];
$mask[1] = 255 - $mask[1];
$mask[2] = 255 - $mask[2];
$mask[3] = 255 - $mask[3];
my $ip_num = $mask[0]*255*255*255 + $mask[1]*255*255 + $mask[2]*255 + $mask[3] - 2;

while ($ip_num>0)
{
    $MAC;
    system("ping ".&num2ip($ip)." 1 >>/dev/null");
    my $arpcmd = 'arp '.&num2ip($ip);
    my $arpecho=`$arpcmd`;
    chomp($arpecho);
    if($arpecho =~ /.*\ at (.*\:..\:..\:..\:..\:..)$/)
    {
        $MAC=$1;
    }
    $ip_num-=1;
    $ip+=1;
    print("MAC;".$MAC.";IP;".&num2ip($ip)."\n");
}
