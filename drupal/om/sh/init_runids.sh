#!/bin/bash

varid=`drush scr modules/om/src/om_getvardef.php om_scenario`
variants_fid=`drush scr modules/om/src/om_getpid.php dh_variabledefinition $varid variants`
while IFS= read -r line; do
  #echo "Text read from file: $line"
  read run_id run_name <<< "$line"
  propcode="runid_$run_id"
  echo "drush scr modules/om/src/om_setprop.php cmd dh_properties $variants_fid om_class_AlphanumericConstant '$run_name' $run_id $propcode"
  drush scr modules/om/src/om_setprop.php cmd dh_properties $variants_fid om_class_AlphanumericConstant '$run_name' $run_id $propcode
done < /opt/model/om/drupal/om/data/run_ids.txt