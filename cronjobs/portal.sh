#!/bin/bash
. config.sh
. ProcessMethod

for host in $OMCRList
do
#flush_RI $host >>/dev/null;
    clearRecord $host >>/dev/null;
    expect scpFrom $host $TEMPDIR >>/dev/null;
done

perl process_ri.pl $TEMPDIR/*csv;
#perl process_ip.pl *ip.csv;
