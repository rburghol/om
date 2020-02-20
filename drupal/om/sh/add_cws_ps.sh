#!/bin/sh

pid=$1
entity_type=$2
entity_id=$3
# Water Supply Model Element Template 
template=4988636

# get pid of 
monpid=`drush scr modules/om/src/om_getpid.php $entity_type $entity_id "wd_current_mon_factors"`
# Now 
drush scr modules/om/src/om.model.historic_monthly_pct.php cmd $pid $monpid om_class_DataMatrix historic_monthly_pct field_dh_matrix dh_properties 
drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties $template dh_properties $pid "consumption_monthly_cws_auto|consumption_monthly"; 