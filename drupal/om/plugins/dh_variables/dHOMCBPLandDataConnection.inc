<?php

$plugin = array(
  'label' => t('OM Component Class - CBPLandDataConnection (legacy)'),
  'handler' =>  array(
    'class' => 'dHOMCBPLandDataConnection',
    'file' => 'CBPLandDataConnectionFile.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM Object Class - Legacy CBPLandDataConnection',
    'vardesc' => 'Data Connection to legacy XML EOS export script.',
    'vocabulary' => 'om_object_classes',
    'varunits' => 'n/a',
    'varkey' => 'om_class_cbp_eos',
    'datatype' => 'component',
    'varcode' => 'CBPLandDataConnection',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'name,code,text',
    'plugin' => 'dHOMCBPLandDataConnection',
    'options' => '',
    'varabbrev' => 'CBP6532 LR-EOS',
  ),
);

?>
