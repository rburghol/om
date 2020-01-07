#!/bin/bash

if [ $# -lt 1 ]; then
  echo 1>&2 "Usage: summarize_runoffs.sh elid runid "
  exit 2
fi 
elid=$1
runid=$2

# @todo: Put in batch post-process and summarize into vahydro 
# Also, replace the normal dH/VAHydro call to php 
# with a call to a shell script that runs the model, then runs a R summary script 

# summarize all runoff containers 
info=`drush scr modules/om/src/om_get_model_info.php $elid`
cd /var/www/R
# Rscript vahydro/R/post.runoff.R $pid $elid $runid
IFS="$IFS|" read pid elid object_class <<< "$info"
sum_script="/opt/model/vahydro/R/summarize.${object_class}.R"
if test -f "$sum_script"; then
    Rscript $sum_script $pid $elid $runid 
fi
