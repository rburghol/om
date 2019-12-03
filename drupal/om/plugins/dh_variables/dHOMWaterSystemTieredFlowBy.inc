<?php

$plugin = array(
  'label' => t('OM Water System Tiered Flow-By/Release Object'),
  'handler' =>  array(
    'class' => 'dHOMWaterSystemTieredFlowBy',
    'file' => 'dHOMWaterSystemObjectClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM Water System Flow-By/Release Object',
    'vardesc' => 'Contains a lookup table to calculate base flow-by/release, plus an if/else type condition to enable override, e.g. 10% of flow or MIF whichever is lower.',
    'vocabulary' => 'om_object_classes',
    'varunits' => 'n/a',
    'varkey' => 'om_water_system_tiered_flowby',
    'datatype' => 'component',
    'varcode' => 'wsp_1tierflowby',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => '',
    'plugin' => 'dHOMWaterSystemTieredFlowBy',
    'options' => '',
    'varabbrev' => 'Flowby',
  ),
);

?>