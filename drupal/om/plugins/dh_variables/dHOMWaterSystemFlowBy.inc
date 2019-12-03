<?php

$plugin = array(
  'label' => t('OM Water System Flow-By/Release Object'),
  'handler' =>  array(
    'class' => 'dHOMWaterSystemFlowBy',
    'file' => 'dHOMWaterSystemObjectClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM Water System Flow-By/Release Object',
    'vardesc' => 'Contains an equation to calculate base flow-by/release, plus an if/else type condition to enable override, e.g. 10% of flow or MIF whichever is lower.',
    'vocabulary' => 'om_object_classes',
    'varunits' => 'n/a',
    'varkey' => 'om_water_system_flowby',
    'datatype' => 'component',
    'varcode' => 'wsp_flowby',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => '',
    'plugin' => 'dHOMWaterSystemFlowBy',
    'options' => '',
    'varabbrev' => 'Flowby',
  ),
);

?>