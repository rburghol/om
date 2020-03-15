#!/bin/sh

if [ $# -lt 1 ]; then
  echo 1>&2 "Usage: add_cws_ps.sh vahydro_model_pid entity_type entity_id"
  exit 2
fi 

pid=$1
entity_type=$2
entity_id=$3
# Water Supply Model Element Template 
template=4988636

# add facility gw_frac variable as an equation on model
drush scr modules/om/src/om.model.wsp.props.php cmd $pid $entity_id om_class_Equation gw_frac gw_frac $entity_type 
# make sure it is using the new discharge_mgd variable 
drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties $template dh_properties $pid discharge_mgd
# add the gw_return_factor equation
drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties $template dh_properties $pid gw_return_factor 