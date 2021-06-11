#!/bin/sh

for tbl in groups map_model_linkages mapusergroups perms project scen_model_element scen_model_run scen_model_run_data scen_model_element  scen_model_run_elements scenario system_status users who_xmlobjects wooomm_toolgroups; do
  echo "Saving Table Schema for $tbl"
  pg_dump -h dbase2 model --schema-only --table=$tbl > ./sql/$tbl.def.sql
done
