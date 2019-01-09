<html>
<body>
<h3>Test serialize object</h3>

<?php


# set up db connection
$noajax = 1;
$projectid = 3;
$scid = 20;

include_once('xajax_modeling.element.php');
$noajax = 1;
$projectid = 3;
#include('qa_functions.php');
#include('ms_config.php');
/* also loads the following variables:
   $libpath - directory containing library files
   $indir - directory for files to be read
   $outdir - directory for files to be written
*/
error_reporting(E_ERROR);
print("Un-serializing Model Object <br>");

$listobject->querystring = " select elementid, elemname from scen_model_element where scenarioid = $scid and objectclass = 'hydroImpoundment' ";
print("$listobject->querystring ; <br>");
$listobject->performQuery();

$elrecs = $listobject->queryrecords;
$debug = 0;
$i = 0;
$serializer = new XML_Serializer();
foreach ($elrecs as $thisrec) {
   $elid = $thisrec['elementid'];
   $obres = unserializeSingleModelObject($elid);
   $srcob = $obres['object'];
   $cbp_seg = $srcob->description;
   $cbp_initstorage = $srcob->initstorage;
   $cbp_unusable_storage = $srcob->unusable_storage;
   $cbp_maxcapacity = $srcob->maxcapacity;
   // get parent container
   $listobject->querystring = " select elementid from scen_model_element where scenarioid = 28 and elemname = '$cbp_seg'";
   print("$listobject->querystring ; <br>");
   $listobject->performQuery();
   $pid = $listobject->getRecordValue(1,'elementid');
   $listobject->querystring = " select elementid from scen_model_element where scenarioid = 28 and objectclass not in ( 'dataConnectionObject', 'CBPLandDataConnection') and elementid in (select src_id from map_model_linkages where dest_id = $pid and linktype = 1 ) ";
   print("$listobject->querystring ; <br>");
   $listobject->performQuery();
   $destid = $listobject->getRecordValue(1,'elementid');
   $prop_array = array('name'=>$thisrec['elemname'], 'description' => $cbp_seg, 'initstorage' => $cbp_initstorage, 'unusable_storage' => $cbp_unusable_storage, 'maxcapacity' => $cbp_maxcapacity);
   updateObjectProps($projectid, $destid, $prop_array);
   $subcomps = array('storage_stage_area');
   
   foreach ($subcomps as $thiscomp) {
      print("Trying to add Sub-comp $thiscomp to Element $destid <br>");
      $cr = copySubComponent($elid, $thiscomp, $destid, $thiscomp);
      //print("$cr<br>");
      print("Sub-comp $thiscomp added to Element $destid <br>");
   }
   
   $i++;
   //break;
}

print("Finished.  Saved $i items.<br>");

?>
</body>

</html>
