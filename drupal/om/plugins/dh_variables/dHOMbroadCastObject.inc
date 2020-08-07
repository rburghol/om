<?php

$plugin = array(
  'label' => t('OM Broadcast Object'),
  'handler' =>  array(
    'class' => 'dHOMbroadCastObject',
    'file' => 'dHOMMessageClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM Broadcast Object',
    'vardesc' => 'Object to broadcast or listen on channels during model simulation.',
    'vocabulary' => 'om_object_classes',
    'varunits' => 'n/a',
    'varkey' => 'om_class_broadCastObject',
    'datatype' => 'component',
    'varcode' => 'broadCastObject',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => '',
    'plugin' => 'dHOMbroadCastObject',
    'options' => '',
    'varabbrev' => 'Broadcast',
  ),
);

?>