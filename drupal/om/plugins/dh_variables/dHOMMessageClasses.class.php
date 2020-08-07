<?php

module_load_include('inc', 'dh', 'plugins/dh.display');
module_load_include('module', 'dh');
// make sure that we have base plugins 
$plugin_def = ctools_get_plugins('dh', 'dh_variables', 'dHOMmodelElement');
$class = ctools_plugin_get_class($plugin_def, 'handler');

class dHOMbroadCastTable extends dHVarWithTableFieldBase {
  
class dHOMbroadCastObject extends dHOMSubComp {
  var $default_bundle = 'om_data_matrix'; // by declaring this we automatically inherit the tablefield data, but need to supply our own code to make it work
  var $matrix_field = 'field_dh_matrix';
  
  public function formRowEdit(&$form, $entity) {
    dpm($entity,'entity');
    dpm($this,'plugin');
    parent::formRowEdit($form, $entity);
    
  }
  
  public function setAllRemoteProperties($entity, $elid, $path) {
    // this replaces parent method in favor of full object json transfer
    // for now just invoke the parent and return 
    parent::setAllRemoteProperties($entity, $elid, $path);
    return;
    
    // experimental code to use json 
    $ppath = $path;
    array_unshift($ppath, $entity->propname);
    $this->setRemoteProp($entity, $elid, $ppath, "", $this->object_class);
    $exp = $this->exportOpenMI($entity);
    //dpm($exp,"Using JSON export mode");
    $exp_json = addslashes(json_encode($exp[$entity->propname]));
    $this->setRemoteProp($entity, $elid, $ppath, $exp_json, $this->object_class, 'json-2d');
  }
  
  // ***************************
  // BEGIN Code borowed from dHOMDataMatrix
  // ***************************
  
  public function tableDefault($entity) {
    if (isset($entity->field_dh_matrix_default)) {
      $table = $entity->field_dh_matrix_default;
    } else {
      $table = array();
      $table[] = array('local_varname', 'remote_varname');
    }
    return $table;
  }
  
  function getCSVTableField(&$entity) {
    $tabledata = $this->getMatrixFieldTable($entity);
    $csv = array();
    foreach ($tabledata as $rowix => $rowvals) {
      $csv[] = array_values($rowvals);
    }
    return $csv;
  }
  
  public function getMatrixField($entity) {
    $tablefield = om_tablefield_tablefield($entity->{$this->matrix_field});
    return $tablefield;
  }
  
  public function getMatrixFieldTable($entity) {
    $tablefield = $this->getMatrixField($entity);
    $tabledata = $tablefield['tabledata'];
    return $tabledata;
  }
  
  // ***************************
  // END Code borowed from dHOMDataMatrix
  // ***************************
}


?>