<html>
<body>
<h3>Test Model Run</h3>

<?php

// model run framework to force caching of select components
# set up db connection
#include('config.php');
$noajax = 1;
$projectid = 3;
include_once('xajax_modeling.element.php');
error_log("Remote Run Parameter: $remote_run ");

#include('qa_functions.php');
#include('ms_config.php');
/* also loads the following variables:
   $libpath - directory containing library files
   $indir - directory for files to be read
   $outdir - directory for files to be written
*/
error_reporting(E_ERROR);
print("Un-serializing Model Object <br>");
$debug = 0;
$startdate = '';
$enddate = '';
$run_date = date('r');

if (isset($_GET['elementid'])) {
   $elementid = $_GET['elementid'];
}
if (isset($_GET['startdate'])) {
   $startdate = $_GET['startdate'];
}
if (isset($_GET['enddate'])) {
   $enddate = $_GET['enddate'];
}
if (isset($argv[1])) {
   $elementid = $argv[1];
}
if (isset($argv[2])) {
   $cache_list = split(',', $argv[2]);
}
if (isset($argv[3])) {
   $startdate = $argv[3];
}
if (isset($argv[4])) {
   $enddate = $argv[4];
}
$runid = -1; // if this is not set, we just store last run (-1)
if (isset($argv[5])) {
   $runid = $argv[5];
}
$cache_level = -1; // if this is not set, we assume no cached runs
if (isset($argv[6])) {
   $cache_level = $argv[6];
}

//************************
//**** TEST DATA        **
//************************
$startdate = '2000-01-01';
$enddate = '2000-01-31';
$elementid = 231299;
$runid = 12;
$cache_runid = 2;
$dynamics = array();
$dynamics[] = array('parentid'=>323683, 'childid'=>321861);
$cache_list = array(231339,231337,231335,231333,231319,231305,231301);

if ($elementid > 0) {

   $input_props = array();
   if ( (strlen($startdate) > 0) and (strlen($enddate) > 0)) {
      $input_props['model_startdate'] = $startdate;
      $input_props['model_enddate'] = $enddate;
   }
   
   // *********************************
   // *** Get Cached Objects       ****
   // *********************************
   
   // iterate through the objects that are requested to be cached and instantiate them
   foreach ($cache_list as $thisel) {
      error_log("Loading cached version of $thisel ");
      $res = loadCachedObject($modeldb, $thisel, $cache_runid, $debug);
      
   }
   
   // *********************************
   // *** Unserialize Standard Obs ****
   // *********************************
   // then call the normal unserialize routine which will load remaining objects, 
   // using cached copies if they fit the normal criteria
   $thisobresult = unSerializeModelObject($elementid, $input_props, $modeldb, $cache_level, $runid);
   $thisobject = $thisobresult['object'];
   $components = $thisobresult['complist'];
   $cachedlist = array_merge($thisobresult['cached'], $cache_list);
   $errorlog .= "<b>Model Unserialization Errors</b><br>" . $thisobresult['error'] . "<hr>";
   error_log("Sub-Objects that were instantiated for this run: " . print_r($components));
   // *********************************
   // *** Dynamic Inserts Now      ****
   // *********************************
   // now unserialize and add any dynamically inserted objects
   $dyna_cache = array();
   foreach ($dynamics as $thisdyn) {
      $childid = $thisdyn['childid'];
      if (isset($thisdyn['parentid'])) {
         $parentid = $thisdyn['parentid'];
      } else {
         $parentid = $elementid;
      }
      // check that the parent exists, if so, instantiate and add the child
      if (isset($unserobjects[$parentid])) {
         error_log("Found parent $parentid for dynamic object $childid");
         $thisobresult = unSerializeModelObject($childid, $input_props, $modeldb, -1, -1);
         if (is_object($thisobresult['object'])) {
            $unserobjects[$parentid]->addComponent($thisobresult['object']);
            error_log("Dynamic object $childid added to $parentid for");
         }
      }
	  $dyna_cache[] = $childid;
   }
   
   
   $thisname = $thisobject->name;
   $thisobject->outdir = $outdir;
   $thisobject->outurl = $outurl;
   if ( (strlen($startdate) > 0) and (strlen($enddate) > 0)) {
      $thisobject->starttime = $startdate;
      $thisobject->endtime = $enddate;
      error_log("Setting Start and End Date for model to $startdate - $enddate \n");
   } else {
      $startdate = $thisobject->starttime;
      $enddate = $thisobject->endtime;
   }
   // set the model system log to be the parent model run database 
   // this will insure that all of the run status messages go to a central place
   $thisobject->modelhost = $serverip;
   $thisobject->runid = $runid;
   $thisobject->systemlog_obj = $listobject;
   $debuglog .= "Model Debug Status: " . $thisobject->debug . "<br>";
   $runlog .= "Running component group: $thisname <br>";
   #$thisobject->cascadedebug = 1;
   #$thisobject->setDebug(1,2);
   $thisobject->runModel();
   $meanexectime = $thisobject->meanexectime;
   error_log("runModel() Returned from calling routine.");
   
   // store the model run data
   if ($cache_runid <> $runid) {
      // go ahead and store a copy of all run data for this if it will not overwrite
      // the source run "cache_runid"
      $log_components = array_merge($cachedlist,$components, $dyna_cache);
   } else {
      // Just store the elements that were actually run live 
      $log_components = array_merge($components, $dyna_cache);
   }
   error_log("Model Run Data to be stored for " . print_r($log_components,1));
   storeElementRunData($listobject, $elementid, $log_components, $runid, $run_date, $startdate, $enddate, $meanexectime);
   
/*
   $debugstring = '';
   error_log("Assembling Panels.");
   $runlog .= $thisobject->outstring . " <br>";
   $errorlog .= '<b>Model Execution Errors:</b>' . $thisobresult['error'] . " <br>";
   if (strlen($thisobject->errorstring) <= 4096) {
      $errorlog .= $thisobject->errorstring . " <br>";
   } else {
      error_log("Writing errors to file.");
      # stash the debugstring in a file, give a link to download it
      $fname = 'error' . $thisobject->componentid . ".html";
      $floc = $outdir . '/' . $fname;
      $furl = $outurl . '/' . $fname;
      $fp = fopen ($floc, 'w');
      fwrite($fp, "Component Logging Info: <br>");
      fwrite($fp, $thisobject->errorstring . " <br>");
      $errorlog .= "<a href='$furl' target=_new>Click Here to Download Model Error Info</a>";
   }
   if (strlen($thisobject->reportstring) <= 4096) {
      $reports .= "Component Logging Info: <br>";
      $reports .= $thisobject->reportstring . " <br>";
   } else {
      error_log("Writing reports to file.");
      # stash the debugstring in a file, give a link to download it
      $fname = 'report' . $thisobject->componentid . ".html";
      $floc = $outdir . '/' . $fname;
      $furl = $outurl . '/' . $fname;
      $fp = fopen ($floc, 'w');
      fwrite($fp, "Component Logging Info: <br>");
      fwrite($fp, $thisobject->reportstring . " <br>");
      $reports .= "<a href='$furl' target=_new>Click Here to Download Model Reporting Info</a>";
   }

   $debuglog .= $thisobresult['debug'] . " <br>";
   $debuglog .= $thisobject->debugstring . '<br>';


   $runlog .= "Finished.<br>";
   error_log("Creating output in html form.");
   // need to generate the tabbed list view in a subroutine
   $innerHTML = "Results Pending";
   error_log("Storing $elementid model output in database");
   $listobject->querystring = "  update scen_model_element set output_cache = '" . addslashes($innerHTML) . "'";
   $listobject->querystring .= " where elementid = $elementid ";
   $listobject->performQuery();
   //error_log("$listobject->querystring");
   error_log("Storing model run data in scen_model_run_elements");
   // and a unique runid specifier 
*/
}
?>
</body>

</html>
