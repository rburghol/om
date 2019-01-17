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
   


$thisobresult = unSerializeModelObject($elementid);
$thisobject = $thisobresult['object'];
$thisname = $thisobject->name;
$thisobject->debug = 1;
$thisobject->outdir = $outdir;
$thisobject->outurl = $outurl;

$thisobject->wake();
error_log("Element $elementid wake() Returned from calling routine.");
$thisobject->init();
error_log("Element $elementid init() Returned from calling routine.");
$debugstring = '';
$debugstring .= "Object creation debugging: " . $thisobresult['debug'] . " <hr>";
$debugstring .= "Object specific debugging: " . $thisobject->debugstring . '<hr>';

//error_log($debugstring);
echo json_encode($thisobject);
?>