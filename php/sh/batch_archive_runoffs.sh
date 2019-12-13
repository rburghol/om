#!/bin/bash
if [ $# -lt 1 ]; then
  echo 1>&2 "Usage: batch_archive_runoff.sh filename"
  exit 2
fi 
filename="$1"

archive_query=`cat ./create_runoff_archive.sql`
echo $archive_query | psql -h dbase2 model
archive_export=`cat ./best_runoff_archives.sql`
echo $archive_export | psql -h dbase2 model > /tmp/archives.txt 

n=`< /tmp/archives.txt wc -l`
nm="$((n - 2))"
head -n $nm /tmp/archives.txt > /tmp/ahead.txt 
n=`< /tmp/ahead.txt wc -l`
nm="$((n - 2))"
tail -n $nm /tmp/head.txt > /tmp/archives.txt 


while IFS= read -r line; do
    echo "Text read from file: $line"
    read riverseg parentid oldelement src_file <<< "$line"
    ./archive_runoff.sh $riverseg $parentid $oldelement $src_file
done < $filename 
