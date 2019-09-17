<?php

$plugin = array(
  'label' => t('OM Water System Object'),
  'handler' =>  array(
    'class' => 'dHOMWaterSystemObject',
    'file' => 'dHOMWaterSystemObjectClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM Water System Object',
    'vardesc' => 'A generic water use system object. Encompasses old BlankShell class.',
    'vocabulary' => 'om_object_classes',
    'varunits' => 'n/a',
    'varkey' => 'om_water_system_element',
    'datatype' => 'component',
    'varcode' => 'wsp_waterUser',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => '',
    'plugin' => 'dHOMWaterSystemObject',
    'options' => '',
    'varabbrev' => 'Element',
  ),
);

?>