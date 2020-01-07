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
info=`drush scr modules/om/src/om_get_element_info.php $elid`
cd /var/www/R
# Rscript vahydro/R/post.runoff.R $pid $elid $runid
IFS="$IFS|" read pid elid object_class <<< "$info"
sum_script="/opt/model/vahydro/R/summarize.${object_class}.R"
echo "Looking for $sum_script "
if test -f "$sum_script"; then
  if [ $pid -gt 0 ]; then
    echo "Running: Rscript $sum_script $pid $elid $runid " >&2
    Rscript $sum_script $pid $elid $runid 
  fi
fi
