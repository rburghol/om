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

$listobject->querystring = "  select * from project";
print(" $listobject->querystring ; <br>");

$listobject->performQuery();
$listobject->showList();

$modeldb->querystring = "  select 1 as i_see_you";
print(" $modeldb->querystring ; <br>");

$modeldb->performQuery();
$modeldb->showList();
?>