<?php

$plugin = array(
  'label' => t('Annotation/Text'),
  'handler' =>  array(
    'class' => 'dHVarAnnotation',
    'file' => 'dHOMHelperClasses.class.php',
  ),
  'variable' =>  array(
    'varname' => 'Annotation/Text',
    'vardesc' => 'Text block for annotation of body text.',
    'vocabulary' => 'om',
    'varunits' => 'n/a',
    'varkey' => 'om_annotation',
    'datatype' => 'container',
    'varcode' => 'om_annotation',
    'isregular' => 0,
    'timestep' => 0,
    'timeunits' => 'n/a',
    'nodataval' => 0,
    'data_entry' => 'name,text',
    'plugin' => 'dHVarAnnotation',
    'options' => '',
    'varabbrev' => 'text',
  ),
);

?>