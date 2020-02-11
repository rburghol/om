<?php


# set up db connection
$noajax = 1;
$projectid = 3;
$scid = 28;

include_once('./xajax_modeling.element.php');
$noajax = 1;
$projectid = 3;
error_reporting(E_ERROR);

if ( count($argv) < 3 ) {
   error_log("Usage: edit_submatrix.php scenarioid subcomp_name \"prop=value\" [elementid] [elemname] [custom1] [custom2] [function (append,overwrite,delete)]\n");
   error_log("Use '-1' as value for scenarioid to update all scenarios (use with caution) \n");
   die;
}

$scenarioid = $argv[1];
$subcomp_name = $argv[2];
list($prop,$value) = explode('=', $argv[3]);

if (isset($argv[4])) {
   $elid = $argv[4];
} else {
   $elid = '';
}
if (isset($argv[5])) {
   $elemname = $argv[5];
} else {
   $elemname = '';
}
if (isset($argv[6])) {
   $custom1 = $argv[6];
} else {
   $custom1 = '';
}
if (isset($argv[7])) {
   $custom2 = $argv[7];
} else {
   $custom2 = '';
}
if (isset($argv[8])) {
   $function = $argv[8];
} else {
   $function = 'append';
}

$segs = array();
$listobject->querystring = "  select elementid, elemname from scen_model_element  ";
$listobject->querystring .= " where ( (scenarioid = $scenarioid) or ($scenarioid = -1) ) ";
if ($elid <> '') {
   $listobject->querystring .= " AND elementid = $elid ";
}
if ($elemname <> '') {
   $listobject->querystring .= " AND elemname = '$elemname' ";
}
if ($custom1 <> '') {
   $listobject->querystring .= " AND custom1 = '$custom1' ";
}
if ($custom2 <> '') {
   $listobject->querystring .= " AND custom2 = '$custom2' ";
}
error_log("Looking for match <br>\n");
error_log("$listobject->querystring ; <br>\n");
$listobject->performQuery();
$recs = $listobject->queryrecords;

foreach ($recs as $thisrec) {
   $elid = $thisrec['elementid'];
   $elemname = $thisrec['elemname'];
   error_log("Editing $subcomp_name on $elemname ($elid) \n");
   $loadres = unSerializeSingleModelObject($elid);
   $thisobject = $loadres['object'];
   if (is_object($thisobject)) {
      if (isset($thisobject->processors[$subcomp_name])) {
         error_log("Editing broadcast $subcomp_name\n ");
         $bv = $thisobject->processors["broadcast_withdrawals"]->broadcast_varname;
         foreach($bv as $key=>$val) {
            if ($val == 'withdrawal_cfs') {
               $bv[$key] = 'withdrawal_mgd';
            }
         }
         $thisobject->processors["broadcast_withdrawals"]->broadcast_varname = $bv;
      }
   }
}
   
error_log("Finished.\n");

?>
