#!/bin/bash
if [ $# -lt 1 ]; then
  echo 1>&2 "Usage: batch_archive_runoff.sh filename"
  exit 2
fi 
filename="$1"
while IFS= read -r line; do
    echo "Text read from file: $line"
    read riverseg parentid oldelement src_file <<< "$line"
    ./archive_runoff.sh $riverseg $parentid $oldelement $src_file
done < $filename 
