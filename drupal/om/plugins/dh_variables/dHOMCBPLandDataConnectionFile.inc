<?php

$plugin = array(
  'label' => t('OM Component Class - CBPLandDataConnectionFile'),
  'handler' =>  array(
    'class' => 'dHOMCBPLandDataConnectionFile',
    'file' => 'CBPLandDataConnectionFile.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM Object Class - CBPLandDataConnectionFile',
    'vardesc' => 'Data Connection to csv EOS export file stored in persistent model runtime table.',
    'vocabulary' => 'om_object_classes',
    'varunits' => 'n/a',
    'varkey' => 'om_class_cbp_eos_file',
    'datatype' => 'component',
    'varcode' => 'CBPLandDataConnectionFile',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'name,code,text',
    'plugin' => 'dHOMCBPLandDataConnectionFile',
    'options' => '',
    'varabbrev' => 'CBP6 LR-EOS',
  ),
);

?>
