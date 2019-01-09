<?php

$noajax = 1;
include('./config.php');
if ($argv[1] == '--help') {
   print("USAGE: php get_usgs_gages.php [gageid=all]\n");
   die;
}

$gageid = '';

if (count($argv) >= 2) {
   $gageid = $argv[1];
}

if (isset($_GET['gageid'])) {
   $gageid = $_GET['gageid'];
}

$usgsdb->querystring = " select station_nu, huc_8_digi, huc_6_digi, regionalba, river_basi ";
$usgsdb->querystring .= " from usgs_drainage_dd ";
if ($gageid <> '') {
   $usgsdb->querystring .= " where station_nu in ('" . join("','", split(",", $gageid)) . "') ";
}
$usgsdb->performQuery();
//print $usgsdb->querystring . "\n";
$out = array2Delimited($usgsdb->queryrecords, ',', 1,'dos');

print $out;
?>
