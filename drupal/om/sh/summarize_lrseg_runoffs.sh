#!/bin/bash

if [ $# -lt 1 ]; then
  echo 1>&2 "Usage: summarize_runoffs.sh runid [rerun=0/1] [region=nova/all/sova] [ftype=cbp6_lrseg/cbp532_lrseg]"
  exit 2
fi 
runid=$1
rerun=0
if [ $# -gt 1 ]; then
  rerun=$2
fi 
region='nova'
if [ $# -gt 2 ]; then
  region=$3
fi 
ftype='cbp6_lrseg'
if [ $# -gt 3 ]; then
  ftype=$4
fi 


# @todo: Put in batch post-process and summarize into vahydro 
# Also, replace the normal dH/VAHydro call to php 
# with a call to a shell script that runs the model, then runs a R summary script 
#
# summarize all runoff containers 
q="select b.pid, om.propvalue
  from dh_feature as f
  left outer join field_data_dh_geofield as g
  on (
    f.hydroid = g.entity_id
    and g.entity_type = 'dh_feature'
  )
  left outer join dh_properties as a
  on (
    f.hydroid = a.featureid
    and a.propcode = 'vahydro-1.0'
  )
  left outer join dh_properties as om
  on (
    a.pid = om.featureid
    and om.propname = 'om_element_connection'
  )
  left outer join dh_properties as b
  on (
    a.pid = b.featureid
    and b.propname = 'runid_$runid'
  )
  left outer join dh_properties as c
  on (
    b.pid = c.featureid
    and c.propname = 'Runit'
  )
  where f.ftype = '$ftype'
    and f.bundle = 'landunit'
    and a.pid is not null 
    and om.pid is not null 
"
# p5 naming convention did not have a prefix, p6 has prefix cbp6, so eliminate 
if [ "$region" == "nova" ]; then
  q="$q and substring(replace(f.hydrocode,'cbp6_', ''),8,1) in ('P','J','Y','R')"
fi
if [ "$region" == "sova" ]; then
  q="$q and substring(replace(f.hydrocode,'cbp6_', ''),8,2) in ('BS', 'OD', 'OR', 'MN', 'NR', 'TU')"
fi
if [ "$rerun" == "0" ]; then
  q="$q and c.pid IS NOT NULL "
fi

echo $q
exit

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
    Rscript /opt/model/vahydro/R/summarize/CBPLandDataConnectionFile.R $pid $elid $runid
done < /tmp/runoff-models.txt 