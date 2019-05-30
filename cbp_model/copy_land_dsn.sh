#!/bin/sh
# $1 - scenario name
# $2 - landuse
# $3 - land segment
# $4 - DSN (111 - SURO, 211 - IFWO, 411 - AGWO)
u='postgres'
p='314159'
h='192.168.0.20'
pg='PWATER'
. ./cbp.wdm.inc
pn=${pnr[$4]}
pg=${pgr[$4]}

echo "Param Name: $pn Param Group: $pg ($4)"

rm /tmp/error.fil
rm /tmp/error-output.txt
fname=`php cbpcopy_wdm-land.php "$1" "$2" "$3" "$4" `
#fname="test.txt"
echo "PHP Script returned $fname "

fid=$RANDOM
sql="create table tmp_dsn_im$fid (thistime timestamp, thisdate date, thisvalue float8)"
echo $sql | psql -U $u cbp -h $h
( echo "COPY tmp_dsn_im$fid FROM STDIN WITH DELIMITER '\t' ;"
    cat "$fname"
    echo '\.'
) | psql -U $u cbp -h $h

locid=`php get_locationid.php $1 land $3 $2`
sql="delete from cbp_scenario_output where location_id = $locid and param_name = '$pn' and param_group = '$pg' and param_block = 'PERLND'"
echo $sql | psql -U $u cbp -h $h
sql="insert into cbp_scenario_output (location_id, thisdate, thisvalue, param_name, param_group, param_block) "
sql="$sql select $locid, thistime, thisvalue, '$pn', '$pg', 'PERLND' from tmp_dsn_im$fid "
echo $sql | psql -U $u cbp -h $h

sql="drop table tmp_dsn_im$fid"
echo $sql | psql -U $u cbp -h $h

rm /tmp/error.fil
rm /tmp/error-output.txt
