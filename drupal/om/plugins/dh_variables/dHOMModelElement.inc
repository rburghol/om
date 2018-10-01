<?php

$plugin = array(
  'label' => t('OM Model Element'),
  'handler' =>  array(
    'class' => 'dHOMModelElement',
    'file' => 'dHOMObjectClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'Om Model Element',
    'vardesc' => 'A standalone model element capable of being part of a simulation. Encompasses old BlankShell class.',
    'vocabulary' => 'om_object_classes',
    'varunits' => 'n/a',
    'varkey' => 'om_model_element',
    'datatype' => 'component',
    'varcode' => 'modelObject',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => '',
    'plugin' => 'dHOMModelElement',
    'options' => '',
    'varabbrev' => 'Element',
  ),
);

?>