#!/bin/bash

entity_type=$1
entity_id=$2
system_name=$3
vahydro_model_pid=$4
om_parent=$5
om_template_id=340402

if [ $# -lt 5 ]; then
  echo 1>&2 "Usage: omify_waterSystemElement.sh entity_type entity_id $vahydro_model_pid system_name om_parent [om_template_id=$om_template_id] "
  exit 2
fi 

if [ $# -gt 5 ]; then
  om_template_id=$6
fi 

# create a remote om element below the indicated parent 
# and then push the changes from the vahydro entity to the remote OM record
echo "drush scr modules/om/src/om_setprop.php cmd dh_properties $vahydro_model_pid om_element_connection om_element_connection 0 clone \"om_template_id=$om_template_id&remote_parentid=$om_parent\" "

drush scr modules/om/src/om_setprop.php cmd dh_properties $vahydro_model_pid om_element_connection om_element_connection 0 clone "om_template_id=$om_template_id&remote_parentid=$om_parent"

drush scr modules/om/src/om_setprop.php cmd dh_properties $pid om_element_connection om_element_connection NULL clone "om_template_id=$om_template_id&remote_parentid=$om_parent"

echo "drush scr modules/om/src/om_saveprop.php cmd $entity_type $entity_id \"$system_name\""
drush scr modules/om/src/om_saveprop.php cmd $entity_type $entity_id "$system_name"
