#!/bin/sh

pid=$1
entity_type=$2
entity_id=$3
# Water Supply Model Element Template 
template=4988636

if [ $# -lt 1 ]; then
  echo 1>&2 "Usage: add_linked_waterSystemElement.sh pid entity_type entity_id [template$template]"
  exit 2
fi 

if [ $# -gt 1 ]; then
  template=$2
fi 

# copy the element fac_current_mgy from it's feature wd_current_mgy 
drush scr modules/om/src/om.model.wsp.props.php cmd $pid $entity_id om_class_Equation fac_current_mgy wd_current_mgy $entity_type 
drush scr modules/om/src/om.model.wsp.props.php cmd $pid $entity_id om_class_Equation gw_frac gw_frac $entity_type 
drush scr modules/om/src/om.model.wsp.props.php cmd $pid $entity_id om_class_Equation wsp2020_2020_mgy wsp2020_2020_mgy $entity_type 
drush scr modules/om/src/om.model.wsp.props.php cmd $pid $entity_id om_class_Equation wsp2020_2040_mgy wsp2020_2040_mgy $entity_type 
monpid=`drush scr modules/om/src/om_getpid.php $entity_type $entity_id "wd_current_mon_factors"`
drush scr modules/om/src/om.model.historic_monthly_pct.php cmd $pid $monpid om_class_DataMatrix historic_monthly_pct field_dh_matrix dh_properties 
# Calculate the riverseg frac ... this could be tough = sum(fac:mp) in riverseg, divided by fac_current_mgy 
