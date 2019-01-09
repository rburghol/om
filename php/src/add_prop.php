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
   print("Usage: add_prop.php elementid prop_name object_class [overwrite=FALSE] \n");
   die;
}
$elid = $argv[1];
$subcomp_name = $argv[2];
$object_class = $argv[3];

if (isset($argv[4])) {
   $overwrite = ( ($argv[4] == 1) or (strtolower($argv[4]) == 'true')) ? TRUE : FALSE;
} else {
   $overwrite = FALSE;
}


$loadres = unSerializeSingleModelObject($elid);
$thisobject = $loadres['object'];
if (is_object($thisobject)) {
  print("Trying to add $subcomp_name -> $prop \n");
  if ( $overwrite or (!isset($thisobject->processors[$subcomp_name])) ) {
    if (!class_exists($object_class)) {
      print("Cannot find object_class = $object_class -- skipping.");
    } else {
      print("Adding $subcomp_name of type $object_class\n");
      $syobj = new $object_class;
      $thisobject->addOperator($subcomp_name, $syobj);
      $res = saveObjectSubComponents($listobject, $thisobject, $recid, 1, 0);
    }
  }
}
   
print("Finished.\n");

?>
