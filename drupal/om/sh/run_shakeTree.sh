#!/bin/bash

pid=$1
query_type=$2
elid=$3
runid=$4
startdate=$5
enddate=$6
cachedate=$7
force=$8
scenid=$9
strictness=$10
run_mode=$11
run_method=$12
urlextras=$13

cd /var/www/html/om/
/usr/bin/php run_shakeTree.php $query_type $elid $runid $startdate $enddate $cachedate $force $scenid $strictness $run_mode $run_method "$urlextras"

# cd /var/www/R/om
# Rscript vahydro/R/post.runoff.R $pid $elid $runid