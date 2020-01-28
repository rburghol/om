<?php

// cd to home
// call bash script
// add_waterSystemElement.sh entity_type entity_id system_name [om_parent=NULL] [template_entity_type] [template_entity_id] [src_propname]
/* 
for water system:
  entity_type=$1
  entity_id=$2
  system_name=$3
  template_entity_type='dh_feature'
  template_entity_id=72575
  src_propname='Water Supply Model Element Template'
  om_parent=-1
  om_template_id=340402
*/

$a = arg();

if (count($a) < 9) {
  dpm($a,"Incorrect Number of Arguments");
} else {
  list($page, $entity_type, $entity_id, $system_name, $template_entity_type, $template_entity_id, $src_propname, $om_parent, $om_template_id) = $a;
  dpm($a,'a');
  dpm($GLOBALS,'globals');
  $path = str_replace('index.php', '', $GLOBALS['_SERVER']['SCRIPT_FILENAME']);
  $cmd = " cd $path \n";
  $cmd .= "modules/om/sh/add_waterSystemElement.sh $entity_type $entity_id \"$system_name\" $om_parent $template_entity_type $template_entity_id \"$src_propname\" ";
  dsm("$cmd");
  shell_exec($cmd);
}

?>