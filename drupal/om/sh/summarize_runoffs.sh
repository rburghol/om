#!/bin/bash

if [ $# -lt 1 ]; then
  echo 1>&2 "Usage: summarize_runoffs.sh runid "
  exit 2
fi 
tyear=2007
if [ $# -gt 1 ]; then
  tyear=$2
fi 

runid=$1

# @todo: Put in batch post-process and summarize into vahydro 
# Also, replace the normal dH/VAHydro call to php 
# with a call to a shell script that runs the model, then runs a R summary script 
#
# summarize all runoff containers 
q="select a.pid, b.propvalue "
q="$q from dh_properties as a "
q="$q left outer join dh_properties as b "
q="$q on (a.pid = b.featureid and b.propname = 'om_element_connection' and b.entity_type = 'dh_properties') "
q="$q left outer join dh_properties as m "
q="$q on (a.featureid = m.pid) "
q="$q left outer join dh_feature as c "
q="$q on (m.featureid = c.hydroid ) "
q="$q where a.propname = '1. Local Runoff Inflows'"
q="$q and substring(replace(c.hydrocode,'vahydrosw_wshed_', ''),1,1) in ('P','J','Y','R')"
echo $q | psql -h dbase2 drupal.dh03 > /tmp/runoff-models.txt 

n=`< /tmp/runoff-models.txt wc -l`
nm="$((n - 2))"
head -n $nm /tmp/runoff-models.txt > /tmp/head.txt 
n=`< /tmp/head.txt wc -l`
nm="$((n - 2))"
tail -n $nm /tmp/head.txt > /tmp/runoff-models.txt 

cd /var/www/R
# Rscript vahydro/R/post.runoff.R $pid $elid $runid
while IFS= read -r line; do
    #echo "Text read from file: $line"
    IFS="$IFS|" read pid elid <<< "$line"
    Rscript /opt/model/vahydro/R/post.runoff.R $pid $elid $runid $tyear 
done < /tmp/runoff-models.txt 