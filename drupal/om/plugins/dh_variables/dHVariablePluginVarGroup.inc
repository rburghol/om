<?php

$plugin = array(
  'label' => t('OM - Grouped Property Controller'),
  'handler' =>  array(
    'class' => 'dHVariablePluginVarGroup',
    'file' => 'dHOMHelperClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM - Grouped Property Controller',
    'vardesc' => 'Provides a property to control display and editing of a group of properties of same variable.',
    'vocabulary' => 'om',
    'varunits' => 'multi',
    'varkey' => 'grouped_prop_controller',
    'datatype' => 'n/a',
    'varcode' => 'grouped_prop_controller',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'name,code',
    # Uses same plugin as full object
    'plugin' => 'dHVariablePluginVarGroup',
    'options' => '',
    'varabbrev' => 'Group',
  ),
);

?>