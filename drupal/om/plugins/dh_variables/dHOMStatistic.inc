<?php

$plugin = array(
  'label' => t('OM Component Class - Statistic'),
  'handler' =>  array(
    'class' => 'dHOMStatistic',
    'file' => 'dHOMObjectClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM Object Class - Statistic',
    'vardesc' => 'Statistic object',
    'vocabulary' => 'om_object_classes',
    'varunits' => 'n/a',
    'varkey' => 'om_class_Statistic',
    'datatype' => 'component',
    'varcode' => 'Statistic',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'name,code,text',
    'plugin' => 'dHOMStatistic',
    'options' => '',
    'varabbrev' => 'f()',
  ),
);

?>