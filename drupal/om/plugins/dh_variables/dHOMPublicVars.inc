<?php

$plugin = array(
  'label' => t('OM Component Class - OM Public Vars'),
  'handler' =>  array(
    'class' => 'dHOMPublicVars',
    'file' => 'dHOMObjectClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM Attributes - Public Vars',
    'vardesc' => 'Accessors of exposed properties of a component and its parent model object.',
    'vocabulary' => 'om_object_classes',
    'varunits' => 'var',
    'varkey' => 'om_class_PublicVars',
    'datatype' => 'constant',
    'varcode' => 'PublicVars',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'code',
    'plugin' => 'dHOMPublicVars',
    'options' => '',
    'varabbrev' => 'Var',
  ),
);

?>