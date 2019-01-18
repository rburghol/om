<?php

# set up db connection
#include('config.php');
$noajax = 1;
$projectid = 3;
include_once('xajax_modeling.element.php');
#include('qa_functions.php');
#include('ms_config.php');
/* also loads the following variables:
   $libpath - directory containing library files
   $indir - directory for files to be read
   $outdir - directory for files to be written
*/
#error_reporting(E_ALL);
error_log("Un-serializing Model Object <br>");
$debug = 0;

$elementid = 276486;

if (isset($_GET['elementid'])) {
   $elementid = $_GET['elementid'];
}
if (isset($argv[1])) {
   $elementid = $argv[1];
}
   
$single = TRUE;
if ($single) {
  $thisobresult = unSerializeSingleModelObject($elementid);
} else {
  $thisobresult = unSerializeModelObject($elementid);
}
$thisobject = $thisobresult['object'];
$thisname = $thisobject->name;
$thisobject->debug = 1;
$thisobject->outdir = $outdir;
$thisobject->outurl = $outurl;

//$thisobject->wake();
//$thisobject->sleep();
//error_log("Element $elementid wake() Returned from calling routine.");
// Should we init() if we are jsut returning?
/*
$thisobject->init();
error_log("Element $elementid init() Returned from calling routine.");
$debugstring = '';
$debugstring .= "Object creation debugging: " . $thisobresult['debug'] . " <hr>";
$debugstring .= "Object specific debugging: " . $thisobject->debugstring . '<hr>';
*/
$foo = new StdClass();
$foo->hello = "world";
$foo->bar = "baz";
unset($thisobject->the_geom);
$json = json_encode($foo);
echo $json;
//=> {"hello":"world","bar":"baz"}
//echo "Obj: " . print_r((array)$thisobject,1);
$obj_array = (array)$thisobject;
// getting Json error: Type is not supported
$json = json_encode($obj_array);
//$json = json_encode($thisobject,0,1);
//error_log($debugstring);
$info = json_last_error_msg();
error_log("Json error: $info");
//http://php.net/manual/en/function.json-last-error.php
echo $json;

?>
