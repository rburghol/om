#!/bin/sh

if [ $# -lt 1 ]; then
  echo 1>&2 "Usage: add_system_gw_ps.sh vahydro_model_pid"
  exit 2

# Water Supply Model Element Template 
template=5564183


# make sure it is using the new discharge_mgd variable 
drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties $template dh_properties $pid riverseg_frac
drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties $template dh_properties $pid wd_mgd
drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties $template dh_properties $pid base_demand_mgd
drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties $template dh_properties $pid unmet_demand_mgd
drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties $template dh_properties $pid gw_demand_mgd
drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties $template dh_properties $pid discharge_mgd

