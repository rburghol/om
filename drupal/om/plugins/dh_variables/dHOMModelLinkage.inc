<?php

$plugin = array(
  'label' => t('OM - Element Link'),
  'handler' =>  array(
    'class' => 'dHOMLinkage',
    'file' => 'dHOMObjectClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM Model Property Linkage/Containment',
    'vardesc' => 'Establishes linkages for property inputs between elements, parent-child relationships, and remote entities.',
    'vocabulary' => 'om',
    'varunits' => 'n/a',
    'varkey' => 'om_map_model_linkage',
    'datatype' => 'reference',
    'varcode' => 'om_map_model_linkage',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'text,value,code,name',
    'plugin' => 'dHOMLinkage',
    'options' => '',
    'varabbrev' => 'OMlink',
  ),
);

?>