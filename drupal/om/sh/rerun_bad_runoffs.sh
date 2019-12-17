#!/bin/bash

if [ $# -lt 4 ]; then
  echo 1>&2 "Usage: run_runoffs.sh runid run_mode flow_mode force(0=none,1=all,2=trunk,3=watersheds) [template$template]"
  exit 2
fi 

runid=$1
run_mode=$2
flow_mode=$3
force=$4
startdate="1984-01-01"
if [ $# -gt 4 ]; then
  startdate=$5
fi 
enddate="2014-12-31"
if [ $# -gt 5 ]; then
  enddate=$6
fi 
cachedate="2019-12-01"
if [ $# -gt 6 ]; then
  cachedate=$6
fi 
basinclause=""
if [ $7 = 'nova' ]; then
  basinclause="and substring(replace(f.hydrocode,'vahydrosw_wshed_', ''),1,1) in ('P','J','Y','R')"
fi 
if [ $7 = 'nova' ]; then
  basinclause="and substring(replace(f.hydrocode,'vahydrosw_wshed_', ''),1,1) in ('BS', 'OD', 'OR', 'MN', 'NR', 'TU')"
fi 
# run all runoff containers 
q=" select d.propvalue as elementid
  from dh_properties as a 
  left outer join dh_properties as b 
  on (
    a.pid = b.featureid 
    and b.entity_type = 'dh_properties'
  ) 
  left outer join dh_properties as c 
  on (
    b.pid = c.featureid 
    and c.entity_type = 'dh_properties'
  ) 
  left outer join dh_properties as d 
  on (
    a.pid = d.featureid 
    and d.entity_type = 'dh_properties'
    and d.propname = 'om_element_connection'
  ) 
  left outer join dh_properties as m 
  on (a.featureid = m.pid) 
  left outer join dh_feature as f 
  on (m.featureid = f.hydroid )  
  where c.propname = 'R2k7sd'
   and c.propvalue < 0.1 
  $basinclause"
echo $q | psql -h dbase2 drupal.dh03 > /tmp/rerunoffs.txt 

n=`< /tmp/rerunoffs.txt wc -l`
nm="$((n - 2))"
head -n $nm /tmp/rerunoffs.txt > /tmp/head.txt 
n=`< /tmp/head.txt wc -l`
nm="$((n - 2))"
tail -n $nm /tmp/head.txt > /tmp/rerunoffs.txt 

#cd /var/www/html/om/
#/usr/bin/php run_shakeTree.php 6 '/tmp/rerunoffs.txt' $runid $startdate $enddate $cachedate $force 37 -1 $run_mode #normal flow_mode=$flow_mode 

#cd /var/www/R
# Rscript vahydro/R/post.runoff.R $pid $elid $runid
#while IFS= read -r line; do
    #echo "Text read from file: $line"
#    IFS="$IFS|" read pid elid <<< "$line"
#    Rscript /opt/model/vahydro/R/post.runoff.R $pid $elid $runid
#done < /tmp/rerunoffs.txt 