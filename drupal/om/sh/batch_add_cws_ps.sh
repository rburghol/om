#!/bin/bash

filename=$1
while IFS= read -r line; do
  #echo "Text read from file: $line"
  IFS="$IFS|" read pid entity_type entity_id <<< "$line"
  ./modules/om/sh/add_cws_ps.sh $pid $entity_type $entity_id
done < $filename 
