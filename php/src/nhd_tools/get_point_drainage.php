<?php

$noajax = 1;
include('./config.php');

print(print_r($argv,1) . "\n");
if (count($argv) > 3) {
   $points = array();
   $points[] = $argv[1] . "," . $argv[2] . "," . $argv[3];
   if (isset($argv[4])) {
      $extra_basins = $argv[4];
   }
   if (isset($argv[5])) {
      $debug = $argv[5];
   }
} else {
   if (count($argv) == 3) {
      $pointlist = file_get_contents($argv[1]);
      $presorted = $argv[2];
      $points = split("\n",$pointlist);
   } else {
      if (isset($_POST['pointlist'])) {
         $pointlist = $_POST['pointlist'];
         $points = split("\n",$pointlist);
      } else {
         $pointlist = '';
         $points = array();
      }
   }
}

print("This routine will return a shape file with a drainage basin for each specified outlet point, based on the smallest NHD+ bains that contains the outlet points.  The resulting shape merges all NHD+ basins upstream of the outlet point such that each point submitted results in only 1 complete drainage basin.  For some points, you may wish to specify a value for 'extra basins', that is, additional basins downstream of the given lat/lon pair.  If you omit the value for extra basins, it will default to 0 (zero). \n<br>");
print("<form action='$scriptname' method=post>");
print("Enter a list of desired outlet points as csv entries in the following form:\n<br>");
print("Point_name,lattitude_dd,longitude_dd[,# of extra basins]\n<br>");
print("<br><i>Example: </i> <br>\n");
print("<pre>North Anna River,37.843498,-77.429423
Lake Anna #2,38.022,-77.722</pre><br>");
print(" Note: do not use commas (,) or single quotes (') in any data fields\n<br>");
showTextArea('pointlist',$pointlist,60, 5, '', 0, 0);
print("\n<br><br>");
showSubmitButton('submit','Submit');
print("</form>");


// first, get a set of outlet points, with areas, and then sort by area from smallest to biggest so we can optimize the creation of the previously aggregated shapes
$pt_infos = array();
$presort = array();
$new_nhd = array();

foreach ($points as $thispoint) {
   print("Parsing Data Line: $thispoint \n<br>");
   $pntar = split(',',$thispoint);
   if (count($pntar) < 4) {
      $pntar[] = 0;
   }
   //print("Array: " . print_r($pntar,1) . "\n");
   
   list($pointname, $latdd, $londd, $extrabasins) = $pntar;
   
   $pointname = sanitize_sql_string($pointname);
   $latdd = sanitize_sql_string($latdd);
   $londd = sanitize_sql_string($londd);
   $extrabasins = intval(sanitize_sql_string($extrabasins));
   $pt_info = findNHDSegment($usgsdb, $latdd, $londd);
   if (! ($pt_info === false)) {
      $pts[] = $pt_info['comid'];
      $pt_infos[$pt_info['comid']]= $pt_info;
      $pt_infos[$pt_info['comid']]['pointname'] = $pointname;
      $pt_infos[$pt_info['comid']]['latdd'] = $latdd;
      $pt_infos[$pt_info['comid']]['londd'] = $londd;
      $pt_infos[$pt_info['comid']]['extra_basins'] = $extrabasins;
      $presort[] = array('comid'=>$pt_info['comid'], 'careasqkm'=>'unknown');
   } else {
      print("Point $latdd. $londd not found.\n");
   }
}


if (!$presorted) {
   $ptlist = join(',', $pts);
   $usgsdb->querystring = " select comid, cumdrainag as careasqkm from nhdplus_flatt_flow where comid in ($ptlist) order by careasqkm ";
   print("$usgsdb->querystring ; \n");
   $usgsdb->performQuery();
   $recs = $usgsdb->queryrecords;
} else {
   $recs = $presort;
}

// now, if we have any basins that match our points, create an output table
if (count($recs) > 0) {
   // cannot create a temp table, since the addgeometrycolumn routines fails if table is not permanents
   $usgsdb->querystring = " create table tmp_nhd_fulldrainage (pointname varchar(255), extra_basins integer, nhd_comid integer, latdd float8, londd float8)";
   print("$usgsdb->querystring <br>\n");
   $usgsdb->performQuery();
   $usgsdb->querystring = " select addgeometrycolumn ('tmp_nhd_fulldrainage', 'the_geom', 4269, 'MULTIPOLYGON', 2)";
   print("$usgsdb->querystring <br>\n");
   $usgsdb->performQuery();
}

foreach ($recs as $thispoint) {
   print("Parsing Data Line: $thispoint \n<br>");
   $comid = $thispoint['comid'];
   $areasqkm = $thispoint['careasqkm'];
   print("Outlet COMID $comid has area: $areasqkm \n<br>");
   
   $pointname = $pt_infos[$comid]['pointname'];
   $latdd = $pt_infos[$comid]['latdd'];
   $londd = $pt_infos[$comid]['londd'];
   $extrabasins = $pt_infos[$comid]['extra_basins'];
   
   $usgsdb->querystring = " select count(*) as matches from nhd_fulldrainage where comid = $comid ";
   print("$usgsdb->querystring <br>\n");
   $usgsdb->performQuery();
   $matches = $usgsdb->getRecordValue(1,'matches');
   if ($matches == 0) {
      print("Generating NHD+ Shape for $pointname, $latdd, $londd, $extrabasins, $debug \n<br>");
      $basininfo = getMergedNHDBasin($usgsdb, $latdd, $londd, $extrabasins, $debug);
      //print(" NHD+ Shape Retrieved \n");
      $wkt_geom = $basininfo['the_geom'];
      $outlet_comid = $basininfo['outlet_comid'];
      $comids[] = $outlet_comid;
      //print("NHD+ Shape for $elid = " . substr($wkt_geom,0,64) . " \n");
      // set the shape
      $usgsdb->querystring = " insert into nhd_fulldrainage (comid, the_geom) values ($outlet_comid, multi(geomfromtext('$wkt_geom',4269)) ) ";
      //print("$usgsdb->querystring <br>\n");
      $usgsdb->performQuery();
      $new_nhd[] = $outlet_comid;
   }

   print("Copying NHD+ Shape for $pointname, $latdd, $londd, $extrabasins \n<br>");
   $usgsdb->querystring = " insert into tmp_nhd_fulldrainage (pointname, nhd_comid, extra_basins, latdd, londd, the_geom) select '$pointname', $comid, $extrabasins, $latdd, $londd, a.the_geom from nhd_fulldrainage as a where comid = $comid ";
   //print("$usgsdb->querystring <br>\n");
   $usgsdb->performQuery();

} 

if (count($points) > 0) {
   $batchid = rand(1,1000);
   // create an export file using pgsql2shp in the tmp dir using a random number
   $filebase = "nhdplus_ex" . $batchid;
   $filename = "/tmp/$filebase";
   print("Creating shapefile: pgsql2shp -f $filename -u YYYYY -P XXXXX -h $dbip va_hydro tmp_nhd_fulldrainage");
   shell_exec("pgsql2shp -f $filename -u $dbuser -P $dbpass -h $dbip va_hydro tmp_nhd_fulldrainage");
   print("Creating tar archive: tar -cf $filename" . ".tar $filename* ");
   shell_exec("tar -cf $filename" . ".tar $filename* ");
   print("Compressing archive: gzip $filename" . ".tar<br>\n");
   shell_exec("gzip $filename" . ".tar");
   print("Copying to download directory: mv $filename" . ".tar.gz  $tmpdir<br>\n");
   shell_exec("mv $filename" . ".tar.gz $tmpdir");
   // copy the tar archive into the /tmp directory of the web server
   // create a download link
   print("<hr><a href='$gouturl/$filebase" . ".tar.gz'>Download shapefile</a><br>");
   $usgsdb->querystring = " select dropgeometrycolumn ('tmp_nhd_fulldrainage', 'the_geom')";
   print("$usgsdb->querystring <br>\n");
   $usgsdb->performQuery();
   $usgsdb->querystring = " drop table tmp_nhd_fulldrainage ";
   print("$usgsdb->querystring <br>\n");
   $usgsdb->performQuery();
}

print("Created new shapes for outlets: " . print_r($new_nhd,1) . "\n");
?>
