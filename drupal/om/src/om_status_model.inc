<?php

module_load_include('module', 'dh');
module_load_include('module', 'om');

function om_status_model_form($form, &$form_state, $model = null, $op = 'run') {
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

	$form['Advanced'] = array();
	$form['Advanced']['#type'] = 'fieldset';
	$form['Advanced']['#title'] = t('Advanced Run Options');
	$form['Advanced']['#collapsible'] = TRUE;
	$form['Advanced']['#collapsed'] = TRUE;
	$form['Advanced']['#weight'] = 2;
  $form['Advanced']['runtype'] = array(
    '#title' => t('Run Caching'),
    '#type' => 'select',
    '#options' => array(
      'cached' => 'Standard (Container Tree cached)',
      'shake_tree' => 'Tree (Sequential Tree, with caching)',
    ),
    '#size' => 1,
    '#default_value' => empty($model->runtype) ? 'shake_tree' : $model->runtype
  );
  $run_mode_options = om_run_mode_options(); // will determine what a user can run
  $form['Advanced']['run_mode'] = array(
    '#title' => t('Run Mode (wd/ps/lakes)'),
    '#type' => 'select',
    '#options' => $run_mode_options,
    '#size' => 1,
    '#default_value' => empty($run_mode) ? 2 : $run_mode,
  );
  $flow_mode_options = om_flow_mode_options(); // will determine what a user can run
  $form['Advanced']['flow_mode'] = array(
    '#title' => t('Flow Mode (runoff dataset)'),
    '#type' => 'select',
    '#options' => $flow_mode_options,
    '#size' => 1,
    '#default_value' => empty($flow_mode) ? 3 : $flow_mode,
  );
  //dpm($model,'model');
  //dsm("empty(model->flow_mode) ? = " . empty($model->flow_mode));
  // advanced options for shakeTree
  $form['Advanced']['cache_date'] = array(
    '#title' => t('Cache Date'),
    '#description' => t('Date to determine if cacheable components are re-run or not.'),
    '#default_value' => $cache_date,
    '#date_format' => $date_format,
    '#type' => 'date_select',
    '#date_year_range' => '-20:+0',
    '#required' => FALSE,
  );
  $form['Advanced']['force_overwrite'] = array(
    '#title' => 'Force Re-run (no caching)',
    '#type' => 'select',
    '#options' => array(
      '0' => 'FALSE',
      '1' => 'TRUE',
    ),
    '#size' => 1,
    '#default' => $model->force_overwrite
  );
  
  // add controls
  $form['controls'] = om_status_model_form_controls($form, $form_state);
  
  return $form;

}

function om_status_model_form_controls(&$form, &$form_state) {
  $controls = array(
    '#type' => 'container',
    '#weight' => 40,
    '#prefix' => '<div id="om_model_status">',
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
      'callback' => 'om_run_model_form_run',
      'wrapper' => 'om_model_status',
      'method' => 'replace',
      'progress' => array(
        'type' => 'throbber',
        'message' => t('Checking - please wait'),
      ),
    ),
  );
  $controls['status_message'] = array(
    '#prefix' => "<div>",
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
