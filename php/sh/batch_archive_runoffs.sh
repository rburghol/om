#!/bin/bash

archive_query=`cat ./create_runoff_archive.sql`
echo $archive_query | psql -h dbase2 model
archive_export=`cat ./best_runoff_archives.sql`
echo $archive_export | psql -h dbase2 model > /tmp/archives.txt 
echo "drop table tmp_runoff_archives;" | psql -h dbase2 model

n=`< /tmp/archives.txt wc -l`
nm="$((n - 2))"
head -n $nm /tmp/archives.txt > /tmp/ahead.txt 
n=`< /tmp/ahead.txt wc -l`
nm="$((n - 2))"
tail -n $nm /tmp/ahead.txt > /tmp/archives.txt 


while IFS= read -r line; do
    #echo "Text read from file: $line"
    IFS="$IFS|" read riverseg parentid oldelement src_file <<< "$line"
    ./archive_runoff.sh $riverseg $parentid $oldelement $src_file
done < /tmp/archives.txt 