<?php
# set up db connection
$noajax = 1;
$projectid = 3;
include_once('./xajax_modeling.element.php');
error_reporting (E_ERROR);

if ( count($argv) < 3 ) {
   error_log("set_component.php called with " . print_r($argv,1));
   error_log("Usage: set_component.php elementid propname \"propvalue\" [object_class=constant] \n");
   die;
}
$elid = $argv[1];
$prop = $argv[2];
$value = $argv[3];
$loadres = unSerializeSingleModelObject($elid);
$thisobject = $loadres['object'];
if (is_object($thisobject)) {
  switch ($object_class) {
    
  if (!isset($thisobject->processors[$prop])) {
    
    if (!class_exists($object_class)) {
      print("Cannot find object_class = $object_class -- skipping.");
    } else {
      print("Adding $subcomp_name of type $object_class\n");
      $syobj = new $object_class;
      $thisobject->addOperator($subcomp_name, $syobj);
      $res = saveObjectSubComponents($listobject, $thisobject, $recid, 1, 0);
    }
  }
    
    'constant':
    default:
    $thisobject->setProp($prop, $value);
    break;
  }
  
}

print("Finished.  Saved $i items.<br>");

?>
