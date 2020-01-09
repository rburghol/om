<?php

$plugin = array(
  'label' => t('OM Water System Object'),
  'handler' =>  array(
    'class' => 'dHOMUSGSRecharge',
    'file' => 'dHOMWaterSystemObjectClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM Water System Object',
    'vardesc' => 'A generic water use system object. Encompasses old BlankShell class.',
    'vocabulary' => 'om_object_classes',
    'varunits' => 'n/a',
    'varkey' => 'om_usgs_recharge',
    'datatype' => 'component',
    'varcode' => 'USGSRecharge',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => '',
    'plugin' => 'dHOMUSGSRecharge',
    'options' => '',
    'varabbrev' => 'Element',
  ),
);

?>