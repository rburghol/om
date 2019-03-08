<?php

$plugin = array(
  'label' => t('OM Component - object_class'),
  'handler' =>  array(
    'class' => 'dHOMObjectClass',
    'file' => 'dHOMObjectClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM Object Class - object_class ',
    'vardesc' => 'Class to be used by underlying coding for model execution.',
    'vocabulary' => 'om_object_classes',
    'varunits' => 'var',
    'varkey' => 'om_object_class',
    'datatype' => 'constant',
    'varcode' => 'object_class',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'code',
    'plugin' => 'dHOMObjectClass',
    'options' => '',
    'varabbrev' => 'Class',
  ),
);

?>