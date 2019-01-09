<html>
<body>
<?php


include("./config.php");

error_reporting(E_ALL);

$innerHTML = '';
$maxg = 3;

$lat = $_POST['lat'];
$lon = $_POST['lon'];
if (isset($_POST['maxg'])) {
   $maxg = $_POST['maxg'];
}

$innerHTML .= "<form action='test_cia.php' method=post>";
$innerHTML .= "<b>Latitude (dd):</b> " . showWidthTextField('lat', $lat, 12, '', 1, 0);
$innerHTML .= "<b>Longitude (dd):</b> " . showWidthTextField('lon', $lon, 12, '', 1, 0);
$innerHTML .= "<b>Number of Nested Watersheds to retrieve:</b> " . showWidthTextField('maxg', $maxg, 12, '', 1, 0);
$innerHTML .= "<br>" . showSubmitButton('submit','submit', '', 1, 0);

print($innerHTML . "<hr>");

if ( ($lat <> '') and ($lon <> '') ) {
   print("Performing Cumulative Imapct<br>");
   $thisobject = new wsp_CumulativeImpactObject;
   $thisobject->lon = $lon;
   $thisobject->lat = $lat;
   $thisobject->usgs_maxrecs = $maxg;
   
   $thisobject->listobject = $listobject;
   $thisobject->outdir = $outdir;
   $thisobject->outurl = $outurl;

   $thisobject->init();
   
   $thisobject->finish();

   print($thisobject->reportstring . "<hr>");
   print($thisobject->debugstring . "<hr>");
   print_r($thisobject->usgs_basins,1 );
}

?>
</body>
</html>
