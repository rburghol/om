<?php

$plugin = array(
  'label' => t('OM Component Class - Scenario'),
  'handler' =>  array(
    'class' => 'dHOM_ModelScenario',
    'file' => 'dHOMObjectClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM - Object Model Scenario',
    'vardesc' => 'Object Model Scenario - value is runid (legacy)',
    'vocabulary' => 'om',
    'varunits' => 'n/a',
    'varkey' => 'om_scenario',
    'datatype' => 'collection',
    'varcode' => 'om_scenario',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'value,code,text',
    'plugin' => 'dHVariablePluginDefault',
    'options' => '',
    'varabbrev' => 'Scenario',
  ),
);

?>