<?php

$plugin = array(
  'label' => t('OM Component Class - DataMatrix'),
  'handler' =>  array(
    'class' => 'dHOMDataMatrix',
    'file' => 'dHOMObjectClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'OM Object Class - DataMatrix',
    'vardesc' => 'DataMatrix object - table based element with value evaluated at run time based on row/col inputs (1-d or 2-d).',
    'vocabulary' => 'om_object_classes',
    'varunits' => 'n/a',
    'varkey' => 'om_class_DataMatrix',
    'datatype' => 'component',
    'varcode' => 'dataMatrix',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'name,code,text',
    'plugin' => 'dHOMDataMatrix',
    'options' => '',
    'varabbrev' => 'Matrix',
  ),
);

?>