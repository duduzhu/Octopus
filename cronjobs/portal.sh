#!/bin/bash
. config.sh
TEMPDIR=/tmp/VAMPIRE;
[ -d $TEMPDIR ] || mkdir $TEMPDIR
rm -rf $TEMPDIR/*

function flush_RI ()
{
    curl -k -u axadmin:omc3 "https://$1/ensusm/ricgibin/eri.cgi?ensusm=ensusm&username=axadmin&action=Generate&equipment=bts&id=9";
    curl -k -u axadmin:omc3 "https://$1/ensusm/ricgibin/eri.cgi?ensusm=ensusm&username=axadmin&action=Generate&equipment=mfs&id=10";
    curl -k -u axadmin:omc3 "https://$1/ensusm/ricgibin/eri.cgi?ensusm=ensusm&username=axadmin&action=Generate&equipment=bsc&id=11";
    curl -k -u axadmin:omc3 "https://$1/ensusm/ricgibin/eri.cgi?ensusm=ensusm&username=axadmin&action=Generate&equipment=tc&id=12";
}

for host in $OMCRList
do
    flush_RI $host >>/dev/null;
    expect putKey -i $host -u axadmin -p omc3;
    scp axadmin@$host:/alcatel/var/share/AFTR/ARIE/*.csv $TEMPDIR ;
    perl process_ri.pl $TEMPDIR/*csv;
    rm $TEMPDIR/*csv;
done

for host in $IPDetector
do
    expect putKey -i $host -u axadmin -p omc3;
    scp -r detect_ip.pl Vampire axadmin@$host:~/;
    ssh axadmin@$host "perl detect_ip.pl $host" |perl process.pl;
done
