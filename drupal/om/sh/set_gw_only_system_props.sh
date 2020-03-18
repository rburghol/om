#!/bin/sh

if [ $# -lt 1 ]; then
  echo 1>&2 "Usage: set_gw_only_system_props.sh vahydro_model_pid (entity_type=model parent, default=auto) (entity_id=model parent)"
  exit 2
fi
pid=$1
entity_type='dh_feature'
if [ $# -gt 1 ]; then
  entity_type=$2
fi 
entity_id="auto"
if [ $# -gt 2 ]; then
  entity_id=$3
fi 
# Water Supply Model Element Template 
template=5564183


# make sure it is using the new discharge_mgd variable 
drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties $template dh_properties $pid riverseg_frac
drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties $template dh_properties $pid wd_mgd
drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties $template dh_properties $pid base_demand_mgd
drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties $template dh_properties $pid unmet_demand_mgd
drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties $template dh_properties $pid gw_demand_mgd
drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties $template dh_properties $pid discharge_mgd
drush scr modules/om/src/om.model.wsp.props.php cmd $pid $entity_id om_class_Equation current_mgy wd_current_mgy $entity_type 

