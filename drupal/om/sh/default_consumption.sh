#!/bin/sh

filename=$1
for i in `cat $filename`; do
  IFS="$IFS|" read pid entity_type entity_id <<< "$line"
done
