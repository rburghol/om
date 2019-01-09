<?php

include('./xajax_modeling.element.php');
include("./lib_verify.php");

$connstring = "host=$dbip dbname=cbp user=$dbuser password=$dbpass";
$dbconn = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);
$cbp_listobject = new pgsql_QueryObject;
$cbp_listobject->connstring = $connstring;
$cbp_listobject->ogis_compliant = 1;
$cbp_listobject->dbconn = $dbconn;
$cbp_listobject->adminsetuparray = $adminsetuparray;

if (isset($_GET['elementid'])) {
   $elid = $_GET['elementid'];
   $format = 'table';
   $runid = -1;
} else {
   $elid = $argv[1];
   $format = $argv[2];
   $runid = $argv[3];
}
if (isset($_GET['runid'])) {
   $runid = $_GET['runid'];
}
if (isset($_GET['format'])) {
   $format = $_GET['format'];
}
if (isset($_GET['host'])) {
   $host = $_GET['host'];
} else {
   $host = $serverip;
}

$container_tree = getStatusTree($listobject, $elid, $runid, $host);
switch ($format) {
   case 'array':
      echo "Number of elements in tree = " . count($container_tree) . "\n";
      echo "Container Tree " . print_r($container_tree, 1) . "\n";
   break;
   
   case 'table':
      $formatted = formatPrintContainer($container_tree);
      print($formatted);
   break;
}

?>