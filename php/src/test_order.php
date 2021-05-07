<?php
# set up db connection
#include('config.php');
$noajax = 1;
$projectid = 3;
$runid = 401;
include_once('xajax_modeling.element.php');
#error_reporting(E_ALL);
print("Un-serializing Model Object <br>");
$debug = 0;

// load a model 
global $modeldb;
$elementid = 211633;
$model_elements = loadModelUsingCached($modeldb, $elementid, $runid, -1, array(), '2019-12-01', array(), '2019-12-01');

error_log("Returned from LoadMOdelUsingCached");

$thisobject = $model_elements['object'];
$thisobject->setSessionID();
error_log("Result of setSessionID" . $thisobject->sessionid);
//$thisobject->debug = 1;
error_log("Calling $thisobject->name orderOperations() ");
$thisobject->orderOperations();

error_log("Operation List" . print_r($thisobject->execlist,1));
echo "Finished testing\n";
//error_log("$this->name orderComponents() ");
//$thisobject->orderComponents();

?>