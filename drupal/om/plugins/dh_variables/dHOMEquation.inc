<?php

$plugin = array(
  'label' => t('OM Component Class - Equation'),
  'handler' =>  array(
    'class' => 'dHOMEquation',
    'file' => 'dHOMObjectClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM Object Class - Equation',
    'vardesc' => 'Equation object',
    'vocabulary' => 'om_object_classes',
    'varunits' => 'n/a',
    'varkey' => 'om_class_Equation',
    'datatype' => 'component',
    'varcode' => 'Equation',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'name,code,text',
    'plugin' => 'dHOMEquation',
    'options' => '',
    'varabbrev' => 'f()',
  ),
);

?>