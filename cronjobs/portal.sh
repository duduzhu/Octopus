#!/bin/bash
ROOT=$(cd "$(dirname "$0")"; pwd);
. $ROOT/config.sh
. $ROOT/ProcessMethod

for host in $OMCRList
do
#flush_RI $host >>/dev/null;
    clearRecord $host >>/dev/null;
    expect scpFrom $host $TEMPDIR >>/dev/null;
done

perl process_csv.pl $TEMPDIR/*csv;
