<?php
# set up db connection
$noajax = 1;
$projectid = 3;
include_once('./xajax_modeling.element.php');
error_reporting (E_ERROR);

if ( count($argv) < 3 ) {
   error_log("setprop.php called with " . print_r($argv,1));
   error_log("Usage: setprop.php elementid \"prop=value\"  \n");
   die;
}
$elid = $argv[1];
list($prop,$value) = explode('=', $argv[2]);
$prop_array = array($prop => $value );
//error_log("Cmd = $prop = $value ");
updateObjectProps($projectid, $elid, $prop_array, 0);
//error_log("Finished.  Saved $i items.<br>");

?>
