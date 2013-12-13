#!/bin/bash
. BASH.lib
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
    curl -s -k -u axadmin:omc3 "https://$1/ensusm/ricgibin/eri.cgi?ensusm=ensusm&username=axadmin&action=Generate&equipment=bts&id=9";
    curl -s -k -u axadmin:omc3 "https://$1/ensusm/ricgibin/eri.cgi?ensusm=ensusm&username=axadmin&action=Generate&equipment=mfs&id=10";
    curl -s -k -u axadmin:omc3 "https://$1/ensusm/ricgibin/eri.cgi?ensusm=ensusm&username=axadmin&action=Generate&equipment=bsc&id=11";
    curl -s -k -u axadmin:omc3 "https://$1/ensusm/ricgibin/eri.cgi?ensusm=ensusm&username=axadmin&action=Generate&equipment=tc&id=12";
}

rm -rf $TEMPDIR/*;

for host in $OMCRList
do
    PING $host
    if [ $? == 0 ]
    then
        expect putKey -i $host -u axadmin -p omc3;
        mkdir $TEMPDIR/$host;

        flush_RI $host >>/dev/null;
        scp axadmin@$host:/alcatel/var/share/AFTR/ARIE/*.csv $TEMPDIR/$host
        perl ri_process.pl $host $TEMPDIR/$host/*

	scp INFOEXPORT.CMD axadmin@$host:/tmp/
	ssh axadmin@$host "rm /alcatel/var/share/AFTR/ACIE/ACIE_NLexport_Dir10/*"
	ssh axadmin@$host "/alcatel/omc3/usmcmd/script/run_usmcmd -f /tmp/INFOEXPORT.CMD"
	scp -r axadmin@$host:/alcatel/var/share/AFTR/ACIE/ACIE_NLexport_Dir10/RnlPowerControl.csv $TEMPDIR/$host.power
	scp -r axadmin@$host:/alcatel/var/share/AFTR/ACIE/ACIE_NLexport_Dir10/Cell.csv $TEMPDIR/$host.cell
	scp -r axadmin@$host:/alcatel/var/share/AFTR/ACIE/ACIE_NLexport_Dir10/RnlAlcatelBSC.csv $TEMPDIR/$host.bsc
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
perl rio_process.pl $TEMPDIR/*.power >`ThisPath`/../application/views/rio.php
