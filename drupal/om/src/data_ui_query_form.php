<?php

function data_ui_query_form($form, &$form_state) {
  //dpm($form_state,'form state');
  $class = FALSE;
  $plugin = ctools_get_plugins('om', 'om_components', 'ObjectmodelQueryWizardHandler', 'handler');
  //dpm($plugin, "ObjectmodelQueryWizardHandler plugin");
  $class = ctools_plugin_get_class($plugin, 'handler');
  //dpm($class, "result of ctools_plugin_get_class($plugin, 'handler')");
  //dpm($form,'pre form');
  $config = isset($form_state['elementid']) ? array('elementid' => $form_state['elementid']) : array();
  if (class_exists($class)) {
    $qwobj = new $class($config);
    $qwobj->buildForm($form, $form_state);
    $qwobj->assembleQuery($form, $form_state);
  }
  //dpm($form,'post form');
  //dpm($form_state['sqlstring'],'post querystring');
  //dpm($form_state,'post');
  return $form;
}

function data_ui_query_form_submit(&$form, &$form_state) {
  // do the search here
  //dpm($form_state,'execute');
  $form_state['rebuild'] = TRUE;
}

// get the entity id for the watershed/object
// look for properties of varkey = om_element_connection
// set "elementid" = the propvalue of the first encountered matching properties
// pass elementid to the form
// @todo: allow users to page through multiple copies? this shouldn't really happen
$a = arg();
if (isset($a[1])) {
  $model_id = $a[1];
  $e = entity_load_single('dh_properties', $model_id );
  $conds = array();
  $conds[] = array(
    'name' => 'varid',
    'value' => dh_varkey2varid('om_element_connection')
  );
  $loaded = $e->loadComponents($conds);
  if (count($loaded)) {
    // @todo: accomodate multiple element linkages
    $c = array_shift($e->dh_properties);
    $form_state = array();
    $form_state['elementid'] = $c->propvalue;
    $elements = drupal_build_form('data_ui_query_form', $form_state);
    $form = drupal_render($elements);
    echo $form;
    
  } else {
    echo "Modeling connection not set for this object.";
  }
} else {
  echo "You must submit an entity id.";
}
?>