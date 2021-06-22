#!/bin/bash

varid=`drush scr modules/om/src/om_getvardef.php om_scenario`
variants_fid=`drush scr modules/om/src/om_getpid.php dh_variabledefinition $varid variants`
while IFS= read -r line; do
  #echo "Text read from file: $line"
  read run_id run_name run_abbrev <<< "$line"
  propcode="runid_$run_id"
  echo "drush scr modules/om/src/om_setprop.php cmd dh_properties $variants_fid om_class_AlphanumericConstant '$run_name' $run_id $propcode"
  scen_pid=`drush scr modules/om/src/om_setprop.php cmd dh_properties $variants_fid om_scenario "$run_name" $run_id $propcode`
  # now, add the basic meta-data on this for cia scenario 
  reports_pid=`drush scr modules/om/src/om_setprop.php cmd dh_properties $scen_pid om_class_AlphanumericConstant "reports" NULL "Report_Config"`
  # reports -> cia -> 
  cia_pid=`drush scr modules/om/src/om_setprop.php cmd dh_properties $reports_pid om_class_AlphanumericConstant cia NULL "CIA Report_Info"`
  
  drush scr modules/om/src/om_setprop.php cmd dh_properties $cia_pid om_class_AlphanumericConstant scenario_narrative NULL "Detailed summary text."
  drush scr modules/om/src/om_setprop.php cmd dh_properties $cia_pid om_class_AlphanumericConstant scenario_name NULL "$run_name"
  if [ -z "$run_abbrev" ]; then
    run_abbrev=$propcode
  fi 
  drush scr modules/om/src/om_setprop.php cmd dh_properties $cia_pid om_class_AlphanumericConstant scenario_short_name NULL "$run_abbrev"
done < /opt/model/om/drupal/om/data/run_ids.txt