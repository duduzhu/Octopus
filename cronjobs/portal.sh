#!/bin/bash
. config.sh
TEMPDIR=/tmp/VAMPIRE;

if [ `uname` == "Linux" ]
then
    function PING()
    {
        ping -c 1 -w 1 $1;
        return $?
    }
else
    function PING()
    {
	ping $1 1;
        return $?
    }
fi

[ -d $TEMPDIR ] || mkdir $TEMPDIR
rm -rf $TEMPDIR/*

function flush_RI ()
{
    curl -k -u axadmin:omc3 "https://$1/ensusm/ricgibin/eri.cgi?ensusm=ensusm&username=axadmin&action=Generate&equipment=bts&id=9";
    curl -k -u axadmin:omc3 "https://$1/ensusm/ricgibin/eri.cgi?ensusm=ensusm&username=axadmin&action=Generate&equipment=mfs&id=10";
    curl -k -u axadmin:omc3 "https://$1/ensusm/ricgibin/eri.cgi?ensusm=ensusm&username=axadmin&action=Generate&equipment=bsc&id=11";
    curl -k -u axadmin:omc3 "https://$1/ensusm/ricgibin/eri.cgi?ensusm=ensusm&username=axadmin&action=Generate&equipment=tc&id=12";
}

rm -rf $TEMPDIR/*;

for host in $OMCRList
do
    PING $host
    if [ $? == 0 ]
    then
        flush_RI $host >>/dev/null;
        expect putKey -i $host -u axadmin -p omc3;
        mkdir $TEMPDIR/$host;
        scp axadmin@$host:/alcatel/var/share/AFTR/ARIE/*.csv $TEMPDIR/$host
        perl process_ri.pl $host $TEMPDIR/$host/*
    else
        perl unreachable.pl $host;
    fi

done

for host in $IPDetector
do
    expect putKey -i $host -u axadmin -p omc3;
    scp -r detect_ip.pl Vampire axadmin@$host:~/;
    ssh axadmin@$host "perl detect_ip.pl $host" |perl process.pl;
done

perl routing_check.pl;
