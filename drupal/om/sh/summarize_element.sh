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
#
# summarize all runoff containers 
pid=`drush scr modules/om/src/om_get_model_info.php $elid`
cd /var/www/R
# Rscript vahydro/R/post.runoff.R $pid $elid $runid
while IFS= read -r line; do
    #echo "Text read from file: $line"
    IFS="$IFS|" read pid elid <<< "$line"
    Rscript /opt/model/vahydro/R/post.runoff.R $pid $elid $runid $tyear 
done < /tmp/runoff-models.txt 