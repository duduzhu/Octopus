#!/usr/bin/perl
use Vampire::IP;

if(!@ARGV)
{
    print "detect_ip.pl <IP> \n";
}
use strict;
my ($host_str) = @ARGV;

my $mask_hex=$1;
if(`ifconfig -a|grep $host_str` =~ /netmask (.{8}) broadcast/)
{
    $mask_hex=$1;
}
else
{
    exit;
}

my $ip = IP::ip2num($host_str) & (hex $mask_hex) + 1;
my $ip_num=0xFFFFFFFF-(hex $mask_hex)-1;

while ($ip_num>0)
{
    system("ping ".IP::num2ip($ip)." 1 >>/dev/null");
    my $arpcmd = 'arp '.IP::num2ip($ip);
    my $arpecho=`$arpcmd`;
    chomp($arpecho);
    my @arpecho = split /\ /, $arpecho;
    my $MAC=$arpecho[3];
    if($MAC=~/.+\:.+\:.+\:.+\:.+\:.+/)
    {
        print("MAC;".$MAC.";IP;".IP::num2ip($ip)."\n");
    }
    $ip_num-=1;
    $ip+=1;
}
