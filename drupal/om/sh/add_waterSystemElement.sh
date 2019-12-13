#!/bin/sh

entity_type=$1
entity_id=$1
template=4988636
om_parent=-1

if [ $# -lt 2 ]; then
  echo 1>&2 "Usage: add_waterSystemElement.sh entity_type entity_id [template$template] [om_parent=NULL]"
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
# create an om_element_connection, with nothing
# modify element connection to have parent submitted
# call push always 

if [ $om_parent -gt 0 ]; then
# @todo: need to have a custom php script to set these linkages and synch
  drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties $template $entity_type $entity_id 
fi 