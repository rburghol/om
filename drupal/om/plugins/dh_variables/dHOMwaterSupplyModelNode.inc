<?php

$plugin = array(
  'label' => t('OM Water Model Node'),
  'handler' =>  array(
    'class' => 'dHOMwaterSupplyModelNode',
    'file' => 'dHOMWaterSystemObjectClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM Water System Object',
    'vardesc' => 'A watershed model container/node. Built for watershed outlet simulation.',
    'vocabulary' => 'om_object_classes',
    'varunits' => 'n/a',
    'varkey' => 'om_water_model_node',
    'datatype' => 'component',
    'varcode' => 'waterSupplyModelNode',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => '',
    'plugin' => 'dHOMwaterSupplyModelNode',
    'options' => '',
    'varabbrev' => 'Element',
  ),
);

?>