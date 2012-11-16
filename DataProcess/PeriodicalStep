#!/bin/bash
. $(cd "$(dirname "$0")"; pwd)/../RIC.conf
. $(cd "$(dirname "$0")"; pwd)/ProcessMethod

for host in $OMCRList
do
    ProcessRIFrom $host
done

mv currentnew.ri $RICDIR/current.ri
