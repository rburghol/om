<?php
module_load_include('module', 'dh');
module_load_include('module', 'om');

function om_run_model_form($form, &$form_state, $model = null, $op = 'run') {
  $form['run_id'] = array(
    '#title' => t('Model Run ID'),
    '#type' => 'select',
    '#default_value' => -1,
    '#options' => array(
      '0' => t('Pre-Condition'),
      '1' => t('Historical Conditions'),
      '2' => t('Current Conditions'),
      '3' => t('Permit Term Maximum'),
      '4' => t('Safe Yield (All at Max)'),
    ),
    '#description' => t('Unique Identifier for a model run.'),
    '#required' => TRUE,
    '#multiple' => FALSE,
    '#weight' => 1,
  );
  $date_format = 'Y-m-d';
  $form['startdate'] = array(
    '#title' => t('Start Date'),
    '#description' => t('Start date for model run.'),
    '#required' => FALSE,
    '#default_value' => empty($model->startdate) ? '2000-01-01' : date($date_format,$model->startdate),
    '#date_format' => $date_format,
    '#type' => 'date_select',
    '#date_year_range' => '-100:+5',
  );
  $form['enddate'] = array(
    '#title' => t('End/Expiration Date'),
    '#description' => t('End/Expiration date for property enabled.'),
    '#default_value' => empty($model->enddate) ? '2000-12-31' : date($date_format,$model->enddate),
    '#date_format' => $date_format,
    '#type' => 'date_select',
    '#date_year_range' => '-100:+5',
    '#required' => FALSE,
  );
  $form['dt'] = array(
    '#title' => t('Timestep'),
    '#type' => 'select',
    '#default_value' => -1,
    '#options' => array(
      '60' => t('1 Minute'),
      '600' => t('10 Minutes'),
      '3600' => t('1 Hour'),
      '7200' => t('2 Hours'),
      '14400' => t('4 Hours'),
      '21600' => t('6 Hours'),
      '28800' => t('8 Hours'),
      '43200' => t('12 Hours'),
      '86400' => t('Daily'),
    ),
    '#default_value' => empty($model->dt) ? 86400 : $model->dt,
    '#description' => t('Model timestep.'),
    '#required' => TRUE,
    '#multiple' => FALSE,
    '#weight' => 1,
  );
  // add controls
  $form['controls'] = om_run_model_form_controls($form, $form_state);
  
  return $form;

}

function om_run_model_form_controls(&$form, &$form_state) {
  $controls = array(
    '#type' => 'container',
    '#weight' => 40,
    '#prefix' => '<div id="om_model_controls">',
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
      'wrapper' => 'om_model_controls',
      'method' => 'replace',
      'progress' => array(
        'type' => 'throbber',
        'message' => t('Checking - please wait'),
      ),
      #'event' => 'click', // not needed, default is mousedown
    ),
  );
  // this element should have a call to check the status of model at render
  // and then disabled if model is idle.
  $controls['end_model'] = array(
    '#type' => 'button',
    '#value' => t('Terminate Model Run'),
    '#weight' => 10,
    '#ajax' => array(
      'callback' => 'om_run_model_form_run',
      'wrapper' => 'om_model_controls',
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

// @todo: move this to the base module?
function om_run_model($config) {
  $test_only = TRUE;
  $path = "/var/www/html/om/"; // @todo: put this in the module settings somewhere
  $config += array(
    'run_mode' => 'cached',
  );
  // run the model 
  $run_id = $config['run_id'];
  $elid = $config['elementid'];
  $startdate = $config['startdate'];
  $enddate = $config['enddate'];
  switch ($run_mode) {
    case 'cached':
    default:
    $setstr = "/usr/bin/php -f /var/www/html/om/run_model.php $elid $run_id cached $startdate $enddate -1 \"\" 1 0 ";
    break;
  }
  $cmd_output = array();
  if ($test_only) {
      $cmd = "cd $path \n";
      $cmd .= $setstr;
      //dpm( $cmd, "Testing Only ");
  } else {
    if ($setstr) {
      $cmd = "cd $path \n";
      $cmd .= $setstr;
      //dpm( $cmd, "Executing ");
      // this runs in the same process
      //shell_exec($cmd);
      // this runs in a separate process.
      $forkout = exec( "$cmd > /dev/null &", $cmd_output );
    }
  }
  return $forkout;
}

function om_run_model_form_run(&$form, &$form_state) {
  dpm($form_state,'Model Run Requested');
  // load the property for the model elements
  $model = entity_ui_form_submit_build_entity($form, $form_state);
  $pid = $model->pid;
  $elid = 'no-elid';
  // get the element link ID
  foreach ($model->dh_variables_plugins as $plugin) {
    if (method_exists($plugin, 'findRemoteOMElement')) {
      $path = array();
      $elid = $plugin->findRemoteOMElement($model, $path);
    }
  }
  // figure out which button
  // the last item in the triggering_element parents array will contain the button name
  //    either "run_model" or "end_model"
  $parents = $form_state['triggering_element']['#array_parents'];
  $button = $parents[count($parents) - 1];
  $model_config = $form_state['values'];
  $model_config['elementid'] = $elid;
  // cannot create entirely new element, because it loses info that 
  //   the form passes in.  must modify from $form array instead
  //$element = om_run_model_form_controls($form, $form_state);
  $element = $form['controls'];
  // figure out the run mode
  switch($button) {
    case 'run_model':
      $output = om_run_model($model_config);
      $element['run_model']['#value'] = 'Update Model Status';
      $element['model_status']['#value'] = 1;
      $message = "Model Run forked " . print_r($output,1);
    break;
    
    case 'end_model':
      $message = "Request to Terminate model run.";
      $element['model_status']['#value'] = -1;
    break;
    
    default:
      watchdog('om', "om_run_model_form_run called with unknown button $button");
      $message = "om_run_model_form_run called with unknown button $button .";
    break;
  }
  $element['status_message']['#markup'] = $message;
  //dpm($element, 'element returned');
  return $element;
}

/* USAGE
//echo "This is some cool code.";
module_load_include('inc', 'om', 'src/om_run_model');
$arg = arg();
$form_state = array();
$form_state['wrapper_callback'] = 'entity_ui_main_form_defaults';
$form_state['entity_type'] = 'dh_properties';
$form_state['bundle'] = 'dh_properties';
$pid = $arg[1];
$model = entity_load_single('dh_properties', $pid);
$op = 'run';
form_load_include($form_state, 'inc', 'entity', 'includes/entity.ui');
// set things before initial form_state build
$form_state['build_info']['args'] = array($model, $op, 'dh_properties');

// **********************
// Load the form
// **********************
form_load_include($form_state, 'inc', 'om', 'src/om_run_model');
$elements = drupal_build_form('om_run_model_form', $form_state);
$form = drupal_render($elements);
echo $form;
*/
?>