#!/bin/sh

filename="$1"
while IFS= read -r line; do
    echo "Text read from file: $line"
    read -r riverseg parentid oldelement src_file <<<"$line"
    echo "archive_runoff.sh $riverseg $parentid $oldelement $src_file"
done < $filename 
