<?php
include("./config.php");

/*
echo "landseg,landuse,thisyear,jan,feb,mar,apr,may,jun,jul,aug,sep,oct,nov,dec" > "p51_all_cover.csv"
for j in alf hom hyo lwm nhi nhy pas hwm hyw nal nho nlo npa urs; do
   fn1="/opt/model/p518/pp/data/land_cover/$j/land_cover_$j"
   fn2="_base12_*"
   fn="$fn1$fn2"
   fgrep -h -v land $fn >> "p51_all_cover.csv"
done

# we will use bedford county as our surrogate for cover coefficients for puh and pul
for i in A51019;do
   fgrep -h $i /opt/model/p52/input/scenario/land/crop_cover/crop_cover_p52cal_1987.csv | grep pul
   fgrep -h $i /opt/model/p52/input/scenario/land/crop_cover/crop_cover_p52cal_1987.csv | grep puh
   fgrep -h $i /opt/model/p52/input/scenario/land/crop_cover/crop_cover_p52cal_1987.csv | grep trp
done

# done from the input/scenario/land directory
for i in `ls`;do
   echo "Searching $i"
   fgrep A37009 $i/*_p52cal_1982.csv
done


*/

# LAND COVER
$overwrite = 0;
# get the data from the old version for the ag land uses
$srcfile = "./p51_all_cover.csv";
$destfile_base = "crop_cover_p53cal";
$destfile = "crop_cover_p53sova";
$destdir = "/opt/model/p53/input/scenario/land/crop_cover/";

# taken from bedford county, 1987
$new_entries = array(
   'pul'=>"'pul',0.79638499021530151,0.79638499021530151,0.79638499021530151,0.79638499021530151,0.79638499021530151,0.79638499021530151,0.79638499021530151,0.79638499021530151,0.79638499021530151,0.79638499021530151,0.79638499021530151,0.79638499021530151",
   'puh'=>"'puh',0.79638499021530151,0.79638499021530151,0.79638499021530151,0.79638499021530151,0.79638499021530151,0.79638499021530151,0.79638499021530151,0.79638499021530151,0.79638499021530151,0.79638499021530151,0.79638499021530151,0.79638499021530151",
   'trp'=>"'trp',0.52870965003967285,0.36321428418159485,0.2987096905708313,0.273333340883255,0.2732258141040802,0.273333340883255,0.2732258141040802,0.27387097477912903,0.273333340883255,0.2732258141040802,0.34700000286102295,0.56225806474685669"
);



# get and reformat the old stuff for all land uses except 
// pull old 518 version into temp table
$src_data = delimitedFileToTable($listobject, $srcfile, ',', 'tmp_cover_src', 0);
$cs = count($src_data);
if ($cs > 0) {
   print("Retrieved $cs lines of data from $srcfile\n");
}
// set the beginning year to 1985 as per the new convention in cbp files
$listobject->querystring = " update tmp_cover_src set thisyear = 1985 ";
$listobject->querystring .= " where thisyear = 1982 ";
print("$listobject->querystring ; \n");
$listobject->performQuery();
// create the ending year of 2005 as per the new convention in cbp files
$listobject->querystring = "  insert into tmp_cover_src (thisyear, landseg,landuse,jan,feb,mar,"; 
$listobject->querystring .= "    apr,may,jun,jul,aug,sep,oct,nov,dec) "; 
$listobject->querystring .= " select 2005, landseg,landuse,jan,feb,mar,"; 
$listobject->querystring .= "    apr,may,jun,jul,aug,sep,oct,nov,dec "; 
$listobject->querystring .= " from tmp_cover_src where thisyear = 2002 ";
print("$listobject->querystring ; \n");
$listobject->performQuery();
// get years in p518 file
$listobject->querystring = "  select thisyear from tmp_cover_src group by thisyear order by thisyear ";
print("$listobject->querystring ; \n");
$listobject->performQuery();
$yrs = $listobject->queryrecords;
// for each year in 518 file, 
foreach ($yrs as $yrrec) {
   $thisyear = $yrrec['thisyear'];
   $dfile = "$destdir/$destfile_base" . "_$thisyear" . ".csv";
   // pull new version into temp table
   $dest_data = delimitedFileToTable($listobject, $dfile, ',', "tmp_cover_dest_$thisyear", 0);
   $cs = count($src_data);
   if ($cs > 0) {
      print("Retrieved $cs lines of data for tmp_cover_dest_$thisyear \n");
   }
   $thisyear = $yrrec['thisyear'];
   // insert copies from p518 version where none exists in the p53 version
   $listobject->querystring = "  insert into tmp_cover_dest_$thisyear (landseg,landuse,jan,feb,mar,"; 
   $listobject->querystring .= "    apr,may,jun,jul,aug,sep,oct,nov,dec) "; 
   $listobject->querystring .= " select a.landseg,a.landuse,a.jan,a.feb,a.mar,"; 
   $listobject->querystring .= "    a.apr,a.may,a.jun,a.jul,a.aug,a.sep,a.oct,a.nov,a.dec "; 
   $listobject->querystring .= " from tmp_cover_src as a left outer join tmp_cover_dest_$thisyear as b "; 
   $listobject->querystring .= " on ( a.landseg = b.landseg "; 
   $listobject->querystring .= "      and a.landuse = b.landuse ) "; 
   $listobject->querystring .= " where a.thisyear = $thisyear "; 
   $listobject->querystring .= "    and b.landseg is null "; 
   print("$listobject->querystring ; \n");
   $listobject->performQuery();
   // insert land uses that are new to cover files since p518 (puh, pul, trp)
   foreach ($new_entries as $thislu=>$thisline) {
      $listobject->querystring = "  insert into tmp_cover_dest_$thisyear (landseg,landuse,jan,feb,mar,"; 
      $listobject->querystring .= "    apr,may,jun,jul,aug,sep,oct,nov,dec) "; 
      $listobject->querystring .= " select a.landseg, $thisline "; 
      $listobject->querystring .= " from tmp_cover_src as a left outer join tmp_cover_dest_$thisyear as b "; 
      $listobject->querystring .= " on ( a.landseg = b.landseg "; 
      $listobject->querystring .= "      and b.landuse = '$thislu' ) "; 
      $listobject->querystring .= " where a.thisyear = $thisyear "; 
      $listobject->querystring .= "    and b.landseg is null "; 
      $listobject->querystring .= "    and a.landuse = 'alf' "; 
      print("$listobject->querystring ; \n");
      $listobject->performQuery();
   }
   // write p53 output file
   $listobject->querystring = " select * from tmp_cover_dest_$thisyear ";
   $listobject->performQuery();
   $listrecs = $listobject->queryrecords;
   $colnames = array(array_keys($listrecs[0]));
   $datafile = "$destdir/$destfile" . "_$thisyear" . ".csv";
   print("Writing $datafile \n");
   putDelimitedFile($datafile,$colnames,',',1,'unix');
   putDelimitedFile($datafile,$listrecs,',',0,'unix');
}
// END loop

# now, copy a surrogate and replace
?>