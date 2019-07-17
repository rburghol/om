<?php

$plugin = array(
  'label' => t('OM Component Class - textField'),
  'handler' =>  array(
    'class' => 'dHOMtextField',
    'file' => 'dHOMObjectClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM Object Class - textField (subcomp)',
    'vardesc' => 'Text based sub-component value to store things like variable names, file paths, etc.',
    'vocabulary' => 'om_object_classes',
    'varunits' => 'var',
    'varkey' => 'om_class_AlphanumericConstant',
    'datatype' => 'constant',
    'varcode' => 'textField',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'value,code',
    'plugin' => 'dHOMtextField',
    'options' => '',
    'varabbrev' => 'Constant',
  ),
);

?>