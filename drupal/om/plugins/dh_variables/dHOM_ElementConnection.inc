<?php

$plugin = array(
  'label' => t('OM - Element Link'),
  'handler' =>  array(
    'class' => 'dHOMElementConnect',
    'file' => 'dHOMObjectClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM Element Link',
    'vardesc' => 'Link to OM modeling element in external database.  Provides access to run files, and enables paramterization to be passed to remote element.',
    'vocabulary' => 'om',
    'varunits' => 'n/a',
    'varkey' => 'om_element_connection',
    'datatype' => 'reference',
    'varcode' => 'om_element_connection',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'text,value,code,name',
    'plugin' => 'dHOMElementConnect',
    'options' => '',
    'varabbrev' => 'ElementID',
  ),
);

?>