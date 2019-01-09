<html>
<body>
<h3>Test Model Run</h3>

<?php


# set up db connection
#include('config.php');
$noajax = 1;
$projectid = 3;
$scid = 2;

include_once('xajax_modeling.element.php');
//include_once('config.php');
#include('qa_functions.php');
#include('ms_config.php');
/* also loads the following variables:
   $libpath - directory containing library files
   $indir - directory for files to be read
   $outdir - directory for files to be written
*/
error_reporting(E_ERROR);
print("Un-serializing Model Object <br>\n");
$debug = 0;
//$expfile = "./test_parseexp.exp";
$expfile = "./NR6_7960_8050.wdm";
//$expfile = "./cootes.exp";
// need to set a timer for use by the object methods
$newtimer = new simTimer;
$dt = 86400;
$startdate = '2002-09-01';
$enddate = '2002-09-30';
$newtimer->setStep($dt);
$newtimer->setTime($startdate, $enddate);
$dsn = 12;

if (class_exists('HSPFWDM')) {
   $wdmobj = new HSPFWDM;
   $wdmobj->debug = 0;
   $wdmobj->listobject = $listobject;
   $wdmobj->setSimTimer($newtimer);
   $wdmobj->debugmode = 0;
   $wdmobj->startdate = $startdate;
   $wdmobj->enddate = $enddate;
   $wdmobj->tmpdir = $tmpdir; // this is set in config.local.php
   $wdmobj->activateDSN($dsn);
   $wdmobj->filepath = $expfile;
   $wdmobj->wdimex_exe = $wdimex_exe; // this is set in config.local.php
   $wdmobj->wdm_messagefile = $wdm_messagefile;
   $wdmobj->max_memory_values = 1; // forces us to stash values in temp table
   $wdmobj->init();
   $dsnobject = $wdmobj->processors[$dsn];
   $intable = $dsnobject->db_cache_name;
   $listobject->querystring = "  select * from $intable limit 100";
   print("Query: " . $listobject->querystring . " ; <br>");
   $listobject->performQuery();
   $listobject->showList();
   print("Debug out: " . $wdmobj->debugstring);
} else {
   print("Class HSPFWDM does not exist");
}
?>
</body>

</html>
