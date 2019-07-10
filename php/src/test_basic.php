<?php
# set up db connection
#include('config.php');
$noajax = 1;
$projectid = 3;
include_once('xajax_modeling.element.php');
#error_reporting(E_ALL);
print("Un-serializing Model Object <br>");
$debug = 0;

// create a shell modelContainer object
// create a timer
error_log("Element $elementid wake() Returned from calling routine.");
$model = new modelContainer();
$model->starttime = '1984-01-01';
$model->endtime = '1984-01-31';
$model->dt = 86400;

// add a timeseriesfile / cbp type
$elementid = 340394;
$thisobresult = unSerializeModelObject($elementid);
$thisobject = $thisobresult['object'];
$thisname = $thisobject->name;
$thisobject->outdir = $outdir;
$thisobject->outurl = $outurl;
$thisobject->landseg = 'tiny';
$thisobject->debug = 0;
$thisobject->filepath = '/opt/model/p6/test/tiny.csv';
error_log("Calling wake() again -- db cache is:" . $thisobject->db_cache_name);

$model->addComponent($thisobject);
$model->debug = 0;
$model->wake();
//$model->init();
// try changing to a different file
//$thisobject->debug = 1;
//$thisobject->debugmode = 1;
$thisobject->setDBCacheName();
$thisobject->init();

?>