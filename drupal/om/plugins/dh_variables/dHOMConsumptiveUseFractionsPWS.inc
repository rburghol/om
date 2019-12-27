<?php

$plugin = array(
  'label' => t('OM Water System CWS Consumptive Use Estimator Object'),
  'handler' =>  array(
    'class' => 'dHOMConsumptiveUseFractionsPWS',
    'file' => 'dHOMWaterSystemObjectClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM Water System CWS Consumptive Use Estimator Object',
    'vardesc' => 'Uses the wintertime return flow method.',
    'vocabulary' => 'om_object_classes',
    'varunits' => 'n/a',
    'varkey' => 'om_water_system_cu_cws',
    'datatype' => 'component',
    'varcode' => 'om_water_system_cu_cws',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => '',
    'plugin' => 'dHOMConsumptiveUseFractionsPWS',
    'options' => '',
    'varabbrev' => 'CU',
  ),
);

?>