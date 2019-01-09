<?php

include("config.php");
if (count($argv) < 3) {
   print("Usage: php test_csv2sql.php elementid runid \n");
   die;
}
$elementid = $argv[1];
$runid = $argv[2];

$res = unSerializeSingleModelObject($elementid);
$thisobject = $res['object'];
if (!is_object($thisobject)) {
   print("Object instantiation error " . $res['error'] . "\n");
   die;
}
$sinfo = getSessionTableNames($thisobject, $elementid, $runid);
$session_table = $sinfo['tablename'];
$filename = $sinfo['filename'];
$run_date = $sinfo['run_date'];
$dbcoltypes = $thisobject->dbcolumntypes;
$darr = delimitedFileToTable($session_db, $filename, ',', $session_table, 1, -1, array(), $dbcoltypes, 1);

print("Done\n");


?>
