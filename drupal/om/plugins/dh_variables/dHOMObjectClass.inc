<?php

$plugin = array(
  'label' => t('OM - Object Class'),
  'handler' =>  array(
    'class' => 'dHOMObjectClass',
    'file' => 'dHOMObjectClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM - Object Class',
    'vardesc' => 'Object Class for meta-model simulation component',
    'vocabulary' => 'om',
    'varunits' => 'n/a',
    'varkey' => 'om_object_class',
    'datatype' => 'class',
    'varcode' => 'object_class',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'code',
    'plugin' => 'dHOMObjectClass',
    'options' => '',
    'varabbrev' => '',
  ),
);

?>