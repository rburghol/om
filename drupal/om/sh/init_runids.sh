#!/bin/bash

while IFS= read -r line; do
  #echo "Text read from file: $line"
  IFS="$IFS|" read entity_type featureid varkey propname propvalue <<< "$line"
  drush scr modules/om/src/om_setprop.php cmd $entity_type $featureid $varkey $propname $propvalue
done < /tmp/use_fractions.txt 