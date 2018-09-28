<?php
# set up db connection
$noajax = 1;
$projectid = 3;
$scid = 28;

include_once('./xajax_modeling.element.php');
$noajax = 1;
$projectid = 3;
error_reporting(E_ERROR);

if ( count($argv) < 5 ) {
   print("Usage: edit_subprop.php elementid subcomp_name \"prop=value\" objectclass [overwrite=FALSE] \n");
   die;
}
$elid = $argv[1];
$subcomp_name = $argv[2];
list($prop,$value) = explode('=', $argv[3]);
$object_class = $argv[4];

if (isset($argv[5])) {
   $overwrite = ( ($argv[5] == 1) or (strtolower($argv[5]) == 'true')) ? TRUE : FALSE;
} else {
   $overwrite = FALSE;
}

// list of supported component types to add
$supported = array('Equation');

$loadres = unSerializeSingleModelObject($elid);
$thisobject = $loadres['object'];
if (!isset($thisobject->processors[$subcomp_name])) {
  error_log("Can not find $subcomp_name in " . print_r(array_keys($thisobject->processors),1));
  //die;
}

if (is_object($thisobject)) {
  print("Trying to set $subcomp_name -> $prop = $value \n");
  if ( $overwrite or (!isset($thisobject->processors[$subcomp_name])) ) {
    if (!class_exists($object_class)) {
      print("Cannot find objectclass = $object_class -- skipping.");
    }
    if (in_array($object_class, $supported)) {
      print("Adding $subcomp_name of type $object_class\n");
      $syobj = new $object_class;
      $thisobject->addOperator($subcomp_name, $syobj);
      $res = saveObjectSubComponents($listobject, $thisobject, $recid, 1, 0);
    } else {
      error_log("$object_class not in supported " . print_r($supported, 1));
    }
  }
  if (isset($thisobject->processors[$subcomp_name])) {
    error_log("Changing thisobject->processors[$subcomp_name] (" . $thisobject->processors[$subcomp_name]->{$prop} . ") = $value; ");
    $thisobject->processors[$subcomp_name]->{$prop} = $value;
  }      
  saveObjectSubComponents($listobject, $thisobject, $elid );
}
   
print("Finished.\n");

?>
