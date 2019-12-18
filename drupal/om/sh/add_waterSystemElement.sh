#!/bin/sh

entity_type=$1
entity_id=$1
template=4988636
om_parent=-1

if [ $# -lt 2 ]; then
  echo 1>&2 "Usage: add_waterSystemElement.sh entity_type entity_id system_name [src_entity$template] [propname] [om_parent=NULL]"
  exit 2
fi 

if [ $# -gt 2 ]; then
  template=$3
fi 

if [ $# -gt 3 ]; then
  om_parent=$4
fi 

# create the element
drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties $template $entity_type $entity_id 
drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties 4725405 dh_properties 4725405 "$template_name|$system_name"
# create an om_element_connection, with nothing
# modify element connection to have parent submitted
# call push always 

if [ $om_parent -gt 0 ]; then
# @todo: need to have a custom php script to set these linkages and synch
# om_element_connection has all the guts needed to do this. 
# Load model element property as $prop 
# Load om_element_conection prop as $conprop 
# set $conprop->propcode = 'clone'
# set $conprop->om_template_id 
# set $conprop->remote_parentid
# $conprop->save()
# Then, save property
# $prop->save()
  drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties $om_template_id $entity_type $entity_id 
fi 