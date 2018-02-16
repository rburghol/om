<?php

$plugin = array(
  'label' => t('Data Matrix Class'),
  // methods __construct(), BuildOptionsForm(), EntityDefaults()
  'handler' => array(
    'class' => 'ObjectModelComponentsDataMatrixHandler',
    'file' => 'ObjectModelComponentsDataMatrixHandler.class.php',
  ),
  // methods step(), getValue(ts), init()
  'runtime' => array(
    'class' => 'ObjectModelComponentsDataMatrixRuntime',
    'file' => 'ObjectModelComponentsDataMatrixHandler.class.php',
  ),
  // methods render() 
  'display' => array(
    'class' => 'ObjectModelComponentsDataMatrixDisplay',
    'file' => 'ObjectModelComponentsDataMatrixHandler.class.php',
  ),
);

?>