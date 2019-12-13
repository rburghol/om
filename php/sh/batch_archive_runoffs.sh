#!/bin/sh

filename="$1"
while IFS= read -r line; do
    echo "Text read from file: $line"
    read -r run_id run_mode flow_mode <<<"$line"
    echo "$second $fourth"
done < $filename 
