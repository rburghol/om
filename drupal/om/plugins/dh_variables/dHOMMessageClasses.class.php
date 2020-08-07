<?php

module_load_include('inc', 'dh', 'plugins/dh.display');
module_load_include('module', 'dh');
// make sure that we have base plugins 
$plugin_def = ctools_get_plugins('dh', 'dh_variables', 'dHOMmodelElement');
$class = ctools_plugin_get_class($plugin_def, 'handler');
  
class dHOMbroadCastObject extends dHOMSubComp {
  var $default_bundle = 'om_data_matrix'; // by declaring this we automatically inherit the tablefield data, but need to supply our own code to make it work
  var $matrix_field = 'field_dh_matrix';
  
  public function formRowEdit(&$form, $entity) {
    dpm($form,'form');
    $form['field_dh_matrix']['und'][0]['#description'] = 'Defined local variables in the left hand column and remote variables in the right hand column.  Do not use a header line.';
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
  
  public function load(&$entity) {
    // get field default basics
    //dpm($entity, 'load()');
    parent::load($entity);
    if ($entity->is_new or $entity->reset_defaults) {
      $datatable = $this->tableDefault($entity);
      $this->setCSVTableField($entity, $datatable);
    }
  }
  
  public function entityDefaults(&$entity) {
    //dpm($entity,'entity');
    // special render handlers when displaying in a grouped property block
    $entity->bundle = $this->default_bundle;
    $datatable = $this->tableDefault($entity);
    $this->setCSVTableField($entity, $datatable);
    //dpm($entity, 'entityDefaults');
  }
  
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
  
  function setCSVTableField(&$entity, $csvtable) {
    // requires a table to be set in non-associative format (essentially a csv)
    $instance = field_info_instance($entity->entityType(), $this->matrix_field, $entity->bundle);
    $field = field_info_field($this->matrix_field);
    $default = field_get_default_value($entity->entityType(), $entity, $field, $instance);
    //dpm($default,'default');
    list($imported_tablefield, $row_count, $max_col_count) = dh_tablefield_parse_array($csvtable);
    // set some default basics
    $default[0]['tablefield']['tabledata'] = $imported_tablefield;
    $default[0]['tablefield']['rebuild']['count_cols'] = $max_col_count;
    $default[0]['tablefield']['rebuild']['count_rows'] = $row_count;
    if (function_exists('tablefield_serialize')) {
      $default[0]['value'] = tablefield_serialize($field, $default[0]['tablefield']);
    } else {
      $default[0]['value'] = serialize($default[0]['tablefield']);
    }
    $default[0]['format'] = !isset($default[0]['format']) ? NULL : $default[0]['format'];
    $entity->{$this->matrix_field} = array(
      'und' => $default
    );
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