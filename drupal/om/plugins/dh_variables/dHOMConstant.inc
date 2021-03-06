<?php

$plugin = array(
  'label' => t('OM Component Class - Constant'),
  'handler' =>  array(
    'class' => 'dHOMConstant',
    'file' => 'dHOMObjectClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM Object Class - Constant',
    'vardesc' => 'Constant numerical or text value to use in order to simplify runtime requirements since no processing needs to occur.',
    'vocabulary' => 'om_object_classes',
    'varunits' => 'var',
    'varkey' => 'om_class_Constant',
    'datatype' => 'constant',
    'varcode' => 'Constant',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'value,code',
    'plugin' => 'dHOMConstant',
    'options' => '',
    'varabbrev' => 'Constant',
  ),
);

?>