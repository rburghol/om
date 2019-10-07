#!/bin/bash
if [ $# -lt 3 ]; then
  echo 1>&2 "Usage: create_landuse_table.sh N51045 JU1_7630_7490 CFBASE30Y20180615"
  exit 2
fi 

landseg=$1
riverseg=$2
scenario=$3

# i.e. create_landuse_table.sh N51045 JU1_7630_7490 CFBASE30Y20180615
template="cbp_p6_cfbase30y20180615_n51161"
filename='/media/NAS/omdata/p6/out/land/$scenario/eos/${landseg}_0111-0211-0411.csv'
tablename="cbp_p6_${scenario}_${landseg}"
tablename=`echo $tablename | tr '[:upper:]' '[:lower:]'`
echo $tablename 

set -f
csql="create table $tablename as select * from cbp_p6_cfbase30y20180615_n51161 limit 0;"
echo $csql | psql -U postgres -p 5444 model_scratch
set +f
hdrcols=`head -n 1 $filename`
echo "copy $tablename ($hdrcols) from '$filename' WITH CSV HEADER" | psql -U postgres -p 5444 model_scratch
echo "update $tablename set timestamp = extract(epoch from thisdate) " | psql -U postgres -p 5444 model_scratch
echo "create index {$tablename}_tix on $tablename (timestamp) " | psql -U postgres -p 5444 model_scratch
