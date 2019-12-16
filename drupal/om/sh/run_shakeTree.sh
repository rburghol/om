#!/bin/bash

query_type=$1
elements=$2
runid=$3
startdate=$4
enddate=$5
cachedate=$6
force=$7
scenid=$8
strictness=$9
run_mode=$10
run_method=$11
urlextras=$12

cd /var/www/html/om/
/usr/bin/php run_shakeTree.php $query_type $elements $runid $startdate $enddate $cachedate $force $scenid $strictness $run_mode $run_method flow_mode=$flow_mode 

# cd /var/www/R/om
# Rscript vahydro/code/om_postProcess.R 