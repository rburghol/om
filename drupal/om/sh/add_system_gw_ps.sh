#!/bin/sh

if [ $# -lt 1 ]; then
  echo 1>&2 "Usage: add_system_gw_ps.sh vahydro_model_pid (entity_type=model parent) (entity_id=model parent)"
  exit 2
fi 

pid=$1
entity_type='dh_feature'
if [ $# -gt 1 ]; then
  entity_type=$2
fi 
entity_id=-1
if [ $# -gt 2 ]; then
  entity_id=$3
fi 
# Water Supply Model Element Template 
template=4988636

# add facility gw_frac variable as an equation on model
echo "Calling: drush scr modules/om/src/om.model.wsp.props.php cmd $pid $entity_id om_class_Equation gw_frac gw_frac $entity_type "
drush scr modules/om/src/om.model.wsp.props.php cmd $pid $entity_id om_class_Equation gw_frac gw_frac $entity_type 
drush scr modules/om/src/om.model.wsp.props.php cmd $pid $entity_id om_class_Equation sw_frac sw_frac $entity_type 
# fac_demand_mgy has changed to use wsp2020_2020_mgy in the exemptrun (runid 9), so that gw point source can still be calculated.  Actual surface water withdrawal is *still* based on vwp_exempt_mgd however.
drush scr modules/om/src/om.model.wsp.props.php cmd $pid $entity_id om_class_Equation fac_demand_mgy fac_demand_mgy $entity_type 
# make sure it is using the new discharge_mgd variable 
drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties $template dh_properties $pid discharge_mgd
drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties $template dh_properties $pid gw_sw_factor
drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties $template dh_properties $pid gw_demand_mgd
drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties $template dh_properties $pid discharge_from_gw_mgd