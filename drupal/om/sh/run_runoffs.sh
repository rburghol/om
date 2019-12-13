#!/bin/sh

if [ $# -lt 4 ]; then
  echo 1>&2 "Usage: run_runoffs.sh runid run_mode flow_mode force(0=none,1=all,2=trunk,3=watersheds) [template$template]"
  exit 2
fi 

runid=$1
run_mode=$2
flow_mode=$3
force=$4
startdate="1984-01-01"
enddate="2014-12-31"
cachedate="2019-12-01"
# run all runoff containers 
q="select b.propvalue from dh_properties as a left outer join dh_properties as b "
q="$q on (a.pid = b.featureid and b.propname = 'om_element_connection') "
q="$q where a.propname = '1. Local Runoff Inflows'"
echo $q | psql -h dbase2 drupal.dh03 > /tmp/runoffs.txt 

n=`< /tmp/runoffs.txt wc -l`
nm="$((n - 2))"
head -n $nm /tmp/runoffs.txt > /tmp/head.txt 
n=`< /tmp/head.txt wc -l`
nm="$((n - 2))"
tail -n $nm /tmp/head.txt > /tmp/runoffs.txt 

cd /var/www/html/om/
/usr/bin/php run_shakeTree.php 6 '/tmp/runoffs.txt' $runid $startdate $enddate $cachedate $force 37 -1 $run_mode normal flow_mode=$flow_mode 
