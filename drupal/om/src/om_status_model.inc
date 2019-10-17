<?php

module_load_include('module', 'dh');
module_load_include('module', 'om');

function om_status_model_form($form, &$form_state) {
  $run_id_options = om_run_id_options(); // will determine what a user can run
 
  $form['mins'] = array(
    '#title' => t('Show activity in last'),
    '#type' => 'select',
    '#default_value' => -1,
    '#options' => array(
      '60' => t('1 Hour'),
      '120' => t('2 Hours'),
      '240' => t('4 Hours'),
      '480' => t('8 Hours'),
      '960' => t('24 Hours'),
    ),
    '#default_value' => 60,
    '#description' => t('Model timestep. NOT YET ENABLED - To change timestep, edit model property named "dt"'),
    '#required' => TRUE,
    '#multiple' => FALSE,
    '#weight' => 1,
  );

  // add controls
  $form['controls'] = om_status_model_form_controls($form, $form_state);
  
  return $form;

}

function om_status_model_form_controls(&$form, &$form_state) {
  $controls = array(
    '#type' => 'container',
    '#weight' => 40,
    '#prefix' => '<div id="om_model_status_controls">',
    '#suffix' => '</div>',
  );
  // this element should have a call to check the status of model at render
  // and then 2 version of the button text "run model" and "update model status" 
  // depending on if the specific elementid is currently running.  
  // this can prevent double-running
  $controls['run_model'] = array(
    '#type' => 'button',
    '#value' => t('Run Model'),
    //'#markup' => t('Run Model'),
    '#weight' => 5,
    '#ajax' => array(
      'callback' => 'om_run_model_form_run',
      'wrapper' => 'om_model_status',
      'method' => 'replace',
      'progress' => array(
        'type' => 'throbber',
        'message' => t('Run requested - please wait'),
      ),
      #'event' => 'click', // not needed, default is mousedown
    ),
  );
  // this element should have a call to check the status of model at render
  // and then disabled if model is idle.
  $controls['check_model'] = array(
    '#type' => 'button',
    '#value' => t('Check Model Status'),
    '#weight' => 10,
    '#ajax' => array(
      'callback' => 'om_status_model_form_run',
      'wrapper' => 'om_model_status_message',
      'method' => 'replace',
      'progress' => array(
        'type' => 'throbber',
        'message' => t('Checking - please wait'),
      ),
    ),
  );
  $controls['om_model_status_message'] = array(
    '#prefix' => "<div id ='om_model_status_message'>",
    '#suffix' => "</div>",
    '#value' => 0,
    '#markup' => t('Model Run Status Unknown'),
    '#weight' => 15,
  );
  $controls['model_status'] = array(
    '#type' => 'hidden',
    '#value' => 0,
    '#weight' => 15,
  );
  return $controls;
  
}

function om_status_model_form_run(&$form, &$form_state) {
  //dpm($form_state,'Model Run Requested');
  // load the property for the model elements
  /*
  $model = entity_ui_form_submit_build_entity($form, $form_state);
  $pid = $model->pid;
  $elementid = 'no-elementid';
  // get the element link ID
  foreach ($model->dh_variables_plugins as $plugin) {
    if (method_exists($plugin, 'findRemoteOMElement')) {
      $path = array();
      $elementid = $plugin->findRemoteOMElement($model, $path);
    }
  }
  
  $status = om_get_model_status($elementid);
  */
  $status = om_get_model_status(-1, 120);
  $status = (array)$status;
  $content = array();
  
  $content['table'] = array(
    '#theme' => 'table',
    '#header' => array_keys($status),
    '#rows' => array(0=> array_values($status)),
    '#attributes' => array (
      'class' => array('views-table', 'cols-3', 'table', 'table-hover', 'table-striped'),
    ),
  );
  
  
  //dpm($status, 'status returned');
  //$element['status_message']['#markup'] = $message;
  $element['status_message']['#markup'] = render($content);
  return $element;
}
