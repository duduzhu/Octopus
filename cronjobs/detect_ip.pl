#!/usr/bin/perl
if(!@ARGV)
{
    print "detect_ip.pl <IP> \n";
}
use strict;
my ($host_str) = @ARGV;
`ifconfig -a|grep $host_str` =~ /netmask (.{8}) broadcast/;
my $mask_hex=$1;

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

my $ip = ip2num($host_str) & (hex $mask_hex) + 1;
my $ip_num=0xFFFFFFFF-(hex $mask_hex)-1;

while ($ip_num>0)
{
#system("ping ".&num2ip($ip)." 1 >>/dev/null");
    print num2ip($ip);print "\n";
    print $ip_num;print "\n";
    my $arpcmd = 'arp '.&num2ip($ip);
    my $arpecho=`$arpcmd`;
    chomp($arpecho);
    my @arpecho = split /\ /, $arpecho;
    my $MAC=$arpecho[3];
    if($MAC=~/.+\:.+\:.+\:.+\:.+\:.+/)
    {
#print("MAC;".$MAC.";IP;".&num2ip($ip)."\n");
    }
    $ip_num-=1;
    $ip+=1;
}
