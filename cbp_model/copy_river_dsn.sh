#!/bin/bash
# $1 - scenario name
# $2 - landuse
# $3 - land segment
# $4 - DSN (111 - SURO, 211 - IFWO, 411 - AGWO)
u='postgres'
p='5434'
h='dbase2'
. ./cbp.wdm.inc
pn=${pnr[$3]}
pg=${pgr[$3]}
pb=${pbr[$3]}

echo "Param Name: $pn Param Group: $pg ($3)"
EXPECTED_ARGS=3
E_BADARGS=65
if [ $# -lt $EXPECTED_ARGS ]
then
  echo "Usage: copy_river_dsn.sh segid dsn scenarioname [overwrite=0]"
   echo `php cbpdump_river_wdm2.php`
  exit $E_BADARGS
fi

rm /tmp/error.fil
rm /tmp/error-output.txt
fname=`php cbpdump_river_wdm2.php "$2" "$3" "$1" "$4" `
#fname="test.txt"
echo "PHP Script returned $fname "

fid=$RANDOM
sql="create table tmp_dsn_im$fid (thistime timestamp, thisdate date, thisvalue float8)"
echo $sql 
echo $sql | psql -U $u cbp -h $h -p $p
( echo "COPY tmp_dsn_im$fid FROM STDIN WITH DELIMITER E'\t' ;"
    cat "$fname"
    echo '\.'
) | psql -U $u cbp -h $h -p $p

locid=`php get_locationid.php $1 river $2`
sql="delete from cbp_scenario_output where location_id = $locid and param_name = '$pn' and param_group = '$pg' and param_block = '$pb'"
echo $sql 
echo $sql | psql -U $u cbp -h $h -p $p
sql="insert into cbp_scenario_output (location_id, thisdate, thisvalue, param_name, param_group, param_block) "
sql="$sql select $locid, thistime, thisvalue, '$pn', '$pg', '$pb' from tmp_dsn_im$fid "
echo $sql 

echo $sql | psql -U $u cbp -h $h -p $p

sql="drop table tmp_dsn_im$fid"
echo $sql 
#echo $sql | psql -U $u cbp -h $h

rm /tmp/error.fil
rm /tmp/error-output.txt
