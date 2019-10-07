filename='/media/NAS/omdata/p6/out/land/CFBASE30Y20180615/eos/N51121_0111-0211-0411.csv'

csql="create table cbp_p6_cfbase30y20180615_n51121 as select * from cbp_p6_cfbase30y20180615_n51045 limit 0;"
echo $csql | psql -U postgres -p 5444 model_scratch
hdrcols =`head -n 1 $filename`
echo "copy cbp_p6_cfbase30y20180615_n51121 ($hdrcols) from '$filename' WITH CSV HEADER" | psql -U postgres -p 5444 model_scratch
