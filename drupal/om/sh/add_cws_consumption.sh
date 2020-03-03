#!/bin/sh


if [ $# -lt 1 ]; then
  echo 1>&2 "Usage: add_cws_consumption.sh vahydro_model_pid "
  exit 2
fi 

pid=$1
# Water Supply Model Element Template 
template=4988636

drush scr modules/om/src/om_setprop.php cmd dh_properties $pid om_class_Equation consumption "0.0" consumption_monthly
drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties $template dh_properties $pid "consumption_monthly_cws_auto|consumption_monthly"; 