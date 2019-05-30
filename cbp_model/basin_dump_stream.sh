#!/bin/sh
if [ "$1" = '' ]; then
   echo "Usage: basin_dump_stream.sh Basin_Abbrev (i.e. JA, JL,...) [scenario=p53cal] [DSN=1000]\n"
   exit
fi

if [ "$2" = '' ]; then
   scen="p53cal"
else
   scen=$2
fi

if [ "$3" = '' ]; then
   dsn="1000"
else
   dsn=$3
fi

seglist=`./list_segs.csh $1`
for i in $seglist; do
   echo "Processing Segment $i\n"
   outlet=`echo $i | cut --fields=2 --delimiter=_`
   echo "Exec: php cbpdump_river_wdm.php $outlet $dsn $scen"
   php cbpdump_river_wdm.php $outlet $dsn $scen
done
