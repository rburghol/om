<?php

$plugin = array(
  'label' => t('OM Stream Channel sub-comp w/USGS Morphology'),
  'handler' =>  array(
    'class' => 'dHOMUSGSChannelGeomObject',
    'file' => 'dHOMObjectHydrologyClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM Object Class - USGS Channel Object',
    'vardesc' => 'An object that employs the USGS physiographic province based methodology to estimate channel geometry. User must input channel length and drainage area',
    'vocabulary' => 'om_object_classes',
    'varunits' => 'n/a',
    'varkey' => 'om_USGSChannelGeomObject',
    'datatype' => 'component',
    'varcode' => 'USGSChannelGeomObject',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'text,code',
    'plugin' => 'dHOMUSGSChannelGeomObject',
    'options' => '',
    'varabbrev' => 'Channel',
  ),
);

?>