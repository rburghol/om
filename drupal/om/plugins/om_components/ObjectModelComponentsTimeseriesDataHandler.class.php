<?php

class ObjectModelComponentsTimeseriesDataHandler extends ObjectModelComponentsDefaultHandler {
  // this facilitates the aquisition and maintenance of data 
  // stored in the dh_timeseries table and/or in a remote repository
  var $cache_min_date; // NULL = no min date
  var $cache_max_date; // NULL = no max date
  var $entity_type; // 
  var $entity_id; // 
  var $varid; // 
  var $tsdata = array(); // 
  var $save_mode; 
    // 'update' will only save or replace items matching values in tsdata array property.  
    // 'replace' will delete all data except that in the object tsdate array.
    // 'slice' will delete all data within start and end date of
  var $cache_on_sleep; // TRUE or FALSE, if TRUE, SaveData() will be performed on sleep()
  
  function DefineOptions() {
    $options = array();
    $options['cache_min_date'] = array('default' => (mktime() - 86400));
    $options['cache_max_date'] = array('default' => mktime());
    $options['entity_type'] = array('default' => '');
    $options['entity_id'] = array('default' => '');
    $options['varid'] = array('default' => 0);
    $options['save_mode'] = array('default' => 'update');
    $options['cache_on_sleep'] = array('default' => TRUE);
    $options['map'] = array(
      'tstime' => 'tstime',
      'tsendtime' => 'tsendtime',
      'tsvalue' => 'tsvalue',
      'tscode' => 'tscode',
      'tstext' => 'tstext',
    );
    return $options + parent::DefineOptions();
  }

  function EntityDefaults() {
    $options = array(
      'cache_num_ts' => array ('default' => 30, 'allow_override'=>TRUE),
      'cache_type_ts' => array ('default' =>'dayS', 'allow_override'=>TRUE),
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
  
  function GetData() {
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