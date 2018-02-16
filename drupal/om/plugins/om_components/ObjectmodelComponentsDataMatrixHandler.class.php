<?php

class ObjectmodelComponentsDataMatrixHandler extends ObjectModelComponentsDefaultHandler {
  // General purpose class to provide a multi-dimensional data array sub-component
  // Settings:
  var $matrixsource = 'local'; // where the matrix is stored
    // local: stores table in config array, renders it with default form
    // field: select list of fields of type MatrixField or TableField
    // remote: TBD (allow to reference another entity remotely)
  var $valuetype = 2; // default return when data requested
    // 0 - returns entire array (normal), 
    // 1 - single column lookup (col), 
    // 2 - 2 column lookup (col & row) - expects getValue(time, rowkey, colkey)
  var $rowkey; // default row lookup key (used for getValue(time))
    // @todo - rename in methods was $keycol1
  var $colkey; // default column lookup key (used for getValue(time))
    // @todo - rename in methods  was $keycol2
  var $row_lutype; // row LU type 0 - exact match; 1 - interpolate; 2 - stair step
    // @todo - rename in methods  was $lutype1
    // @todo - rekey 0 = exact, 1 = interpolate, 2 = stairstep
  var $col_lutype; // col LU type 0 - exact match; 1 - interpolate; 2 - stair step
    // @todo - rename in methods  was $lutype2
    // @todo - rekey 0 = exact, 1 = interpolate, 2 = stairstep
  // if this is set to true, the first row will be ignored in data requests, assuming that these are labels only
  var $headers; // first row are headers true / false
    // @todo - rename in methods  was $firstrow_colnames
  var $firstrow_ro = 0; // is the first row read-only? If TRUE - shown as disabled.
    // @todo - rename in methods  was $firstrow_colnames
  var $numrows;
  var $numcols;
  var $fixed_cols; # whether or not to restrict the number of rows or columns
  var $fixed_rows;
  // Entity Properties
  var $matrix;
  var $matrix_formatted;
  var $matrix_rowcol;
  var $rownames = array(); // populated when formatting matrix
  var $colnames = array(); // populated when formatting matrix
  // Event Behavior settings
  var $cache_on_sleep; // TRUE or FALSE, if TRUE, SaveData() will be performed on sleep()
  
  function DefineOptions() {
    $options = array();
    $options['rowkey'] = array('default' => '');
    $options['colkey'] = array('default' => '');
    $options['row_lutype'] = array('default' => 'exact');
    $options['col_lutype'] = array('default' => 'exact');
    $options['firstrow_ro'] = array('default' => FALSE);
    $options['numrows'] = array('default' => 1);
    $options['numcols'] = array('default' => 1);
    $options['fixed_cols'] = array('default' => FALSE);
    $options['fixed_rows'] = array('default' => FALSE);
    $options['save_mode'] = array('default' => 'update');
    
    return $options + parent::DefineOptions();
  }

  function EntityDefaults() {
    $options = array(
      'matrix' => array ('default' =>array(), 'allow_override'=>FALSE),
      'matrix_formatted' => array ('default' =>array(), 'allow_override'=>FALSE),
      'matrix_rowcol' => array ('default' =>array(), 'allow_override'=>FALSE),
    );
    return $options + parent::EntityDefaults();
  }
  
  public function buildOptionsForm(&$form, $form_state) {
    $form['cache_num_ts'] = array(
      '#title' => t('Maximum cache age'),
      '#description' => t('Number of timesteps relative to present to maintain in local timeseries table.'),
      '#required' => FALSE,
      '#default_value' => $this->options['cache_num_ts'],
      '#type' => 'textfield',
      '#element_validate' => array('element_validate_number'),
    );
    $units = array(
      'days' => 'Days', 
      'years' => 'Years', 
      'seconds' => 'Seconds',
    );
    $form['cache_type_ts'] = array(
      '#title' => t('Cache Units'),
      '#type' => 'select',
      '#options' => $units,
      '#default_value' => $this->options['cache_type_ts'],
      '#description' => t('Units for cache duration setting.'),
      '#required' => TRUE,
    );
  }
  
  // ******************************************
  // *****   Local Data Functions
  // ******************************************
  
  function GetValue() {
    // retrieve local data
  }
  
  function SaveData() {
    // clean up data if replace mode
    $maxtime = date('U', $this->cache_max_date);
    $mintime = date('U', $this->cache_min_date);
    switch ($this->save_mode) {
      case 'replace':
        $q = " select tid from {dh_timeseries} where entity_id = $this->entity_id and entity_type = '$this>entity_type' and varid = $this->varid ; ";
        $rez = db_query($q);
        foreach ($rez as $ts) {
          entity_delete('dh_timeseries', $ts->tid);
        }
      break;
      
      case 'slice':
        $q = " select tid from {dh_timeseries} where entity_id = $this->entity_id and entity_type = '$this>entity_type' and varid = $this->varid and tstime >= $mintime and tstime <= $maxtime ; ";
        $rez = db_query($q);
        foreach ($rez as $ts) {
          entity_delete('dh_timeseries', $ts->tid);
        }
      break;
    }
    foreach ($this->tsdata as $ts) {
      $tstime = $ts[$this->map['tstime']]; 
      $tsendtime = $ts[$this->map['tsendtime']]; 
      $tsvalue = $ts[$this->map['tsvalue']]; 
      $tscode = $ts[$this->map['tscode']]; 
      $tstext = $ts[$this->map['tstext']]; 
    }
  }
  
  // ******************************************
  // *****   Remote Data Functions
  // ******************************************
  
  
  function GetRemoteData() {
    // retrieve remote data
  }
  
  function SaveRemoteData() {
    // stash data in remote repo
  }
}

?>