<?php

$plugin = array(
  'label' => t('OM Stream Channel sub-comp w/USGS Morphology'),
  'handler' =>  array(
    'class' => 'dHOMUSGSChannelGeomObject_sub',
    'file' => 'dHOMObjectHydrologyClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM Object Class - USGS Channel Sub-Object',
    'vardesc' => 'An object that employs the USGS physiographic province based methodology to estimate channel geometry. User must input channel length and drainage area',
    'vocabulary' => 'om_object_classes',
    'varunits' => 'n/a',
    'varkey' => 'om_USGSChannelGeomObject_sub',
    'datatype' => 'component',
    'varcode' => 'USGSChannelGeomObject_sub',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'text,code',
    'plugin' => 'dHOMUSGSChannelGeomObject_sub',
    'options' => '',
    'varabbrev' => 'Channel',
  ),
);

?>