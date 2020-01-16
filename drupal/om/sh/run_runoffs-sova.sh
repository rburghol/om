#!/bin/sh

if [ $# -lt 4 ]; then
  echo 1>&2 "Usage: run_runoffs-sova.sh runid run_mode flow_mode force(0=none,1=all,2=trunk,3=watersheds) [template$template]"
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
  cachedate=$7
fi 
# run all runoff containers 
q="select elementid 
  from scen_model_element 
  where elemname like 'VAHydro 1.0/CBP5.3.2 Model%'
    and ( substring(replace(elemname,'VAHydro 1.0/CBP5.3.2 Model:', ''),1,2) 
      in ('BS', 'OD', 'OR', 'MN', 'NR', 'TU') )
    and custom1 = 'va_hydro'
    and scenarioid = 30
  "
echo $q | psql -h dbase2 model > /tmp/sova_runoffs.txt 

n=`< /tmp/sova_runoffs.txt wc -l`
nm="$((n - 2))"
head -n $nm /tmp/sova_runoffs.txt > /tmp/head.txt 
n=`< /tmp/head.txt wc -l`
nm="$((n - 2))"
tail -n $nm /tmp/head.txt > /tmp/sova_runoffs.txt 

cd /var/www/html/om/
/usr/bin/php run_shakeTree.php 6 '/tmp/sova_runoffs.txt' $runid $startdate $enddate $cachedate $force 30 -1 $run_mode normal flow_mode=$flow_mode 
