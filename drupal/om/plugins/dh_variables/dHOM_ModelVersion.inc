<?php

$plugin = array(
  'label' => t('OM - Model Version'),
  'handler' =>  array(
    'class' => 'dHOM_ModelVersion',
    'file' => 'dHOMObjectClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM - Object Model Version',
    'vardesc' => 'Object Model Version e.g. vahydro-1.0, cbp6, ...',
    'vocabulary' => 'om',
    'varunits' => 'n/a',
    'varkey' => 'om_model_version',
    'datatype' => 'collection',
    'varcode' => 'om_model_version',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'value,code,text,start,end',
    'plugin' => 'dHOM_ModelVersion',
    'options' => '',
    'varabbrev' => 'Scenario',
  ),
);

?>