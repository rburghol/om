<?php


# set up db connection
$noajax = 1;
$projectid = 3;
$scid = 28;
#/var/www/html/om/xajax_modeling.common.php
include_once('/var/www/html/om/xajax_modeling.element.php');
$noajax = 1;
$projectid = 3;
error_reporting(E_ERROR);

if ( count($argv) < 4 ) {
   print("Usage: edit_subcomp_props.php elementid subcomp_name \"prop=value\" \n");
   die;
}

if (isset($argv[1])) {
   $elid = $argv[1];
} else {
   $elid = '';
}
if (isset($argv[2])) {
   $subcomp_name = $argv[2];
} else {
   $subcomp_name = '';
}
list($prop,$value) = explode('=', $argv[3]);


$loadres = unSerializeSingleModelObject($elid);
$thisobject = $loadres['object'];

if (is_object($thisobject)) {
   print("Trying to set $subcomp_name -> $prop = $value \n");
   $thisobject->processors[$subcomp_name]->$prop = $value;
   saveObjectSubComponents($listobject, $thisobject, $elid );
}
   
print("Finished.\n");

?>
