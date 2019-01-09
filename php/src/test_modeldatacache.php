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

$scenarioid = 4;
$sessionid = 15714; # element ID of the model object that initiated this run

if (isset($_GET['elementid'])) {
   $elementid = $_GET['elementid'];
   $elclause = " elementid in ($elementid) ";
} else {
   $elclause = " elemname like 'PS%' and objectclass = 'hydroContainer' ";
}

$elclause = " elementid in ( 15714 ) ";

$listobject->querystring = "  select elementid, elemname from scen_model_element where scenarioid = $scenarioid ";
$listobject->querystring .= "    and $elclause ";
print("$listobject->querystring ; <br>\n");
$listobject->performQuery();
$recs = $listobject->queryrecords;

$params = array(
   0=>array(
      'param_block' => '',
      'param_group' => '',
      'param_name' => 'Qout',
      'param_col' => 'Qout'
   )
);
# get list of objects to cache data for

foreach ($recs as $thisrec) {
   $elementid = $thisrec['elementid'];
   $elemname = $thisrec['elemname'];
   $listobject->init();
   $newprops = array();
   print("Reloading cached data from: $elemname<br>\n");
   $thisobresult = unSerializeSingleModelObject($elementid, $newprops);
   print("Object retrieved. Setting timer.<br>\n");
   $thisobject = $thisobresult['object'];
   $thisobject->setProp('sessionid', $sessionid);
   $thisobject->max_memory_values = -1;
   $thisobject->init();
   $thisobject->logFromFile();
   $thisobject->log2listobject();
   $thistable = $thisobject->dbtblname;
   
   $listobject->querystring = " select min(\"time\") as mintime, max(\"time\") as maxtime from $thistable ";
   print("$listobject->querystring ; <br>\n");
   $listobject->performQuery();
   $lowdate = $listobject->getRecordValue(1,'mintime');
   $hidate = $listobject->getRecordValue(1,'maxtime');
   foreach ($params as $thisparam) {
      $param_block = $thisparam['param_block'];
      $param_group = $thisparam['param_group'];
      $param_name = $thisparam['param_name'];
      $param_col = $thisparam['param_col'];
      
      $listobject->querystring = "  delete from scen_model_output ";
      $listobject->querystring .= " where thisdate >= '$lowdate' and thisdate <= '$hidate' ";
      $listobject->querystring .= "    and param_block = '$param_block' ";
      $listobject->querystring .= "    and param_group = '$param_group' ";
      $listobject->querystring .= "    and param_name = '$param_name' ";
      print("$listobject->querystring ; <br>\n");
      $listobject->performQuery();
/*
      $listobject->querystring = " select $scenarioid, \"time\", $elementid, ";
      $listobject->querystring .= "    '$param_block', '$param_group', '$param_name', \"$param_col\" ";
      $listobject->querystring .= " from $thistable LIMIT 1";
      print("$listobject->querystring ; <br>\n");
      $listobject->performQuery();
      $listobject->show = 1;
      $listobject->showList();
*/
      $listobject->querystring = "  insert into scen_model_output (runid, thisdate, elementid, ";
      $listobject->querystring .= "    param_block, param_group, param_name, thisvalue ) ";
      $listobject->querystring .= " select $scenarioid, \"time\", $elementid, ";
      $listobject->querystring .= "    '$param_block', '$param_group', '$param_name', \"$param_col\" ";
      $listobject->querystring .= " from $thistable ";
      print("$listobject->querystring ; <br>\n");
      $listobject->performQuery();
   }

   $thisobject->finish();
   $thisobject->cleanUp();
   unset($thisobject);
}

?>
</body>

</html>
