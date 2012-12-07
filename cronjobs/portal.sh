#!/bin/bash
. config.sh
[ -d $TEMPDIR ] || mkdir $TEMPDIR
rm -rf $TEMPDIR/*
CURL=`which curl`;
SSH=`which ssh`;
SCP=`which scp`;
EXPECT=`which expect`;
PERL=`which perl`;

function flush_RI ()
{
    $CURL -k -u axadmin:omc3 "https://$1/ensusm/ricgibin/eri.cgi?ensusm=ensusm&username=axadmin&action=Generate&equipment=bts&id=9";
    $CURL -k -u axadmin:omc3 "https://$1/ensusm/ricgibin/eri.cgi?ensusm=ensusm&username=axadmin&action=Generate&equipment=mfs&id=10";
    $CURL -k -u axadmin:omc3 "https://$1/ensusm/ricgibin/eri.cgi?ensusm=ensusm&username=axadmin&action=Generate&equipment=bsc&id=11";
    $CURL -k -u axadmin:omc3 "https://$1/ensusm/ricgibin/eri.cgi?ensusm=ensusm&username=axadmin&action=Generate&equipment=tc&id=12";
}

for host in $IPDetector
do
    $EXPECT putKey -i $host -u axadmin -p omc3;
    $SCP detect_ip.pl axadmin@$host:~/;
    $SSH axadmin@$host "perl detect_ip.pl $host 255.255.255.0" |perl process.pl;
done

for host in $OMCRList
do
    flush_RI $host >>/dev/null;
    $EXPECT putKey -i $host -u axadmin -p omc3;
    $SCP axadmin@$host:/alcatel/var/share/AFTR/ARIE/*.csv $TEMPDIR ;
    $PERL process_ri.pl $TEMPDIR/*csv;
    rm $TEMPDIR/*csv;
done




$PERL scan_misalign.pl;
