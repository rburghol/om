<?php

$plugin = array(
  'label' => t('OM Component Class - USGSGageObject'),
  'handler' =>  array(
    'class' => 'dHOM_USGSGageObject',
    'file' => 'dHOMObjectClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM Object Class - USGSGageObject',
    'vardesc' => 'Gage object, retrieves and provides runtime settings for USGS gage',
    'vocabulary' => 'om_object_classes',
    'varunits' => 'n/a',
    'varkey' => 'om_class_USGSGageObject',
    'datatype' => 'component',
    'varcode' => 'USGSGageObject',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'name,code,text',
    'plugin' => 'dHOM_USGSGageObject',
    'options' => '',
    'varabbrev' => 'USGS Gage',
  ),
);

?>