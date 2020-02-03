#!/bin/bash


if [ $# -lt 2 ]; then
  echo 1>&2 "Usage: create_landuse_table.sh N51045 CFBASE30Y20180615"
  exit 2
fi 

landseg=$1
scenario=$2

# i.e. create_landseg_table.sh N51045 JU1_7630_7490 CFBASE30Y20180615
template="cbp_p6_lseg_runoff_template"
filename="/media/NAS/omdata/p6/out/land/$scenario/eos/${landseg}_0111-0211-0411.csv"
tablename="cbp_p6_${scenario}_${landseg}"
tablename=`echo $tablename | tr '[:upper:]' '[:lower:]'`
hdrcols=`head -n 1 $filename`

echo "Populating $tablename "

set -f
csql=" create table $tablename as select * from $template limit 0;"
isql="copy $tablename ($hdrcols) from '$filename' WITH CSV HEADER "
isql="$isql; update $tablename set timestamp = extract(epoch from thisdate) "
isql="$isql; create index ${tablename}_tix on $tablename (timestamp) "
echo "BEGIN; $csql; $isql; COMMIT;" | psql -U postgres -p 5444 model_scratch
set +f

exit

# Example:
# select array_accum(lname) from (select substring(name,1,6) as lname from dh_feature where bundle = 'landunit' and ftype = 'cbp6_lrseg' group by substring(name,1,6)) as foo;

# Yields: llist="N36023 N42065 N51135 N36095 N51036 N51003 N51007 N51017 N42013 N42081 N51009 N51153 H42013 N54003 N51073 N42063 L51091 N51177 N42043 N36097 N51119 N24005 N24021 N42097 N42079 N42055 N51710 N42107 H51045 N24003 N54065 N36077 N36011 N54093 N51685 N24027 N42117 N24011 N51149 N51610 N54027 N51065 H51015 N51103 N51049 N51760 N24029 N51005 N51660 N24039 N51133 H24021 N51683 N51157 N51095 N36107 N42025 N51053 N24031 N51790 N51011 N51079 N51047 N54075 N51097 N42127 N51810 N54071 H51165 N51840 N42009 N51830 H54071 N54031 H51125 H54031 N51147 N36053 N51087 N51770 N51101 N51580 N51800 N42035 N51085 H42115 N42029 N10005 N51113 N51570 N51137 L51023 H42119 N36043 N24045 N51510 N51775 N36015 N54025 N51600 N42075 N51171 N42113 N36017 N24047 N51740 N42033 N24041 N36007 N24013 N51193 N51033 N24017 N51031 H51023 N24023 N51125 H24023 L51079 N51037 N24510 H51113 N51179 N51820 N51041 N42015 N51131 N24037 N24033 N42047 N51121 N42061 L51157 N36109 L51163 N51111 N51127 N54023 L54023 N42099 H42043 N42071 N10001 N51540 N51165 N51013 N51730 N42109 N36067 N54057 N51001 N51075 H51003 L42113 N36069 N51650 N36123 N42069 N51069 N24009 N42119 N51630 H42079 H51139 N51139 N54083 N51700 N51670 N36003 N42041 N24019 N51187 N42027 N10003 L51015 N36025 N42057 N42105 N54077 H42113 N51735 N51045 H51009 H51157 N51163 H51079 N24015 N51107 N51071 N51515 N51678 N51023 N51550 N42131 N54037 N54063 N51099 N51115 N42093 L42069 N42001 L42079 N51109 H42131 N24001 H54023 N24043 N24025 N51680 N51093 N51015 N51043 N42115 N42067 L54071 N24035 N51199 N51159 L54031 H42117 N51091 N36101 N42111 N51181 N51029 N51161 N51057 N36065 N36051 N42011 H54057 N51530 N42021 N42083 N51059 N42023 N42087 H42027 N42133 N51145 N42037 N11001 N51019 N51061"
for i in $llist; do
  echo create_landseg_table.sh $i CFBASE30Y20180615
  ./create_landseg_table.sh $i CFBASE30Y20180615
done 

for i in $llist; do
   echo create_landseg_table.sh $i CBASE1808L55CY55R45P50R45P50Y;   
   ./create_landseg_table.sh $i CBASE1808L55CY55R45P50R45P50Y; 
done

