<?php

$plugin = array(
  'label' => t('OM Object Class - HydroImpoundment'),
  'handler' =>  array(
    'class' => 'dHOMHydroImpoundment',
    'file' => 'dHOMObjectHydrologyClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM Object Class - HydroImpoundment',
    'vardesc' => 'Lake or reservoir class.',
    'vocabulary' => 'om_object_classes',
    'varunits' => 'n/a',
    'varkey' => 'om_hydroimpoundment',
    'datatype' => 'component',
    'varcode' => 'hydroImpoundment',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => '',
    'plugin' => 'dHOMHydroImpoundment',
    'options' => '',
    'varabbrev' => 'Imp',
  ),
);

?>