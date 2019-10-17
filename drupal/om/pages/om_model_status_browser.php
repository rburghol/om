<?php
module_load_include('inc', 'om', 'src/om_status_model');
$arg = arg();
$form_state = array();
$form_state['wrapper_callback'] = 'entity_ui_main_form_defaults';
$form_state['entity_type'] = 'dh_properties';
$form_state['bundle'] = 'dh_properties';

$op = 'run';
//form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
// set things before initial form_state build
$form_state['build_info']['args'] = array();
form_load_include($form_state, 'inc', 'om', 'src/om_status_model');

// **********************
// Load the form
// **********************
$elements = drupal_build_form('om_status_model_form', $form_state);
$form = drupal_render($elements);
echo $form;

?>