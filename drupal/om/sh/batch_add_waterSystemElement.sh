#!/bin/sh

filename=$1
for i in `cat $filename`; do
  /opt/model/om-dev/drupal/om/sh/add_common_waterSystemElement.sh $i
done
