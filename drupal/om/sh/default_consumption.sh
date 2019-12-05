#!/bin/sh

filename=$1
for i in `cat $filename`; do
  drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties 4988636 dh_properties $i consumption_monthly; 
done
