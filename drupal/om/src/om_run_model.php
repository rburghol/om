<?php
module_load_include('module', 'dh');

function om_run_model_form($form, &$form_state, $model = null, $op = 'run') {
dpm($form_state,'state');
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
    '#default_value' => empty($model->startdate) ? NULL : date($date_format,$model->startdate),
    '#date_format' => $date_format,
    '#type' => 'date_select',
    '#date_year_range' => '-100:+5',
  );
  $form['enddate'] = array(
    '#title' => t('End/Expiration Date'),
    '#description' => t('End/Expiration date for property enabled.'),
    '#default_value' => empty($model->enddate) ? NULL : date($date_format,$model->enddate),
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
    '#description' => t('Model timestep.'),
    '#required' => TRUE,
    '#multiple' => FALSE,
    '#weight' => 1,
  );
  $form['actions'] = array('#type' => 'actions');
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Run Model'),
    '#weight' => 40,
  );
  
  return $form;

}

function om_run_model_form_submit(&$form, &$form_state) {
  dpm($form_state,'Model Run Requested');
  //$test_only = TRUE;
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
  // figure out the run mode
  $run_mode = 'cached';
  // run the model 
  $run_id = $form_state['values']['run_id'];
  $startdate = $form_state['values']['startdate'];
  $enddate = $form_state['values']['enddate'];
  switch ($run_mode) {
    case 'cached':
    default:
    $setstr = "/usr/bin/php -f /var/www/html/om/run_model.php $elid $run_id cached $startdate $enddate -1 \"\" 1 0 ";
    break;
  }
  $path = "/var/www/html/om/";
  if ($test_only) {
      $cmd = "cd $path \n";
      $cmd .= $setstr;
      dpm( $cmd, "Testing ");
  } else {
    if ($setstr) {
      $cmd = "cd $path \n";
      $cmd .= $setstr;
      dpm( $cmd, "Executing ");
      shell_exec($cmd);
    }
  }
  
  // standalone "cached" mode (assumes all upstream segments have already been run
  // /usr/bin/php -f /var/www/html/om//run_model.php 337724 999 cached 1984-01-01 2005-12-31 -1 "" 1 0 

  // tree mode (checks to see if upstream segment is run, if not, run the first)
  // php run_shakeTree.php 4 OR2_8130_7900 204 1984-07-01 1984-08-31 2013-09-11 0 37 0 3 &

  // cached_cova mode 
  // /usr/bin/php -f /var/www/html/om//run_model.php 339871 779 cached_cova 1984-01-01 1984-12-31 2 "" -1 0 -1
}
//echo "This is some cool code.";
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
$elements = drupal_build_form('om_run_model_form', $form_state);
$form = drupal_render($elements);
echo $form;

?>