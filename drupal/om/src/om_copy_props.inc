<?php

module_load_include('module', 'dh');
module_load_include('module', 'om');

function om_copy_props_form($form, &$form_state, $prop) {

  $form['pid'] = array(
    '#title' => t('Destination PID'),
    '#type' => 'textfield',
    '#default_value' => $prop->pid,
    '#description' => t(''),
    '#required' => TRUE,
    '#disabled' => TRUE,
    '#weight' => 1,
  );
  $form['entity_type'] = array(
    '#title' => t('Entity Type'),
    '#type' => 'textfield',
    '#default_value' => 'dh_properties',
    '#description' => t(''),
    '#required' => FALSE,
    '#weight' => 1,
  );
  $form['featureid'] = array(
    '#title' => t('Source featureid'),
    '#type' => 'textfield',
    '#default_value' => '',
    '#description' => t(''),
    '#required' => FALSE,
    '#weight' => 2,
  );
  // @todo: use this with an ajax call to limit to only valid props 
  // $options = dh_get_dh_propnames($entity_type, $entity_id);
  $form['propnames'] = array(
    '#title' => t('Properties to copy (csv OK)'),
    '#type' => 'textfield',
    '#default_value' => '',
    '#description' => t(''),
    '#required' => FALSE,
    '#weight' => 2,
  );

  // add controls
  $form['controls'] = om_copy_props_form_controls($form, $form_state);
  
  return $form;

}

function om_copy_props_form_controls(&$form, &$form_state) {
  $controls = array(
    '#type' => 'container',
    '#weight' => 40,
    '#prefix' => '<div id="om_copy_props_controls">',
    '#suffix' => '</div>',
  );
  // this element should have a call to check the status of model at render
  // and then disabled if model is idle.
  $controls['check_model'] = array(
    '#type' => 'button',
    '#value' => t('Copy Selected Properties'),
    '#weight' => 10,
    '#ajax' => array(
      'callback' => 'om_copy_props_form_submit',
      'wrapper' => 'om_copy_props_message',
      'method' => 'replace',
      'progress' => array(
        'type' => 'throbber',
        'message' => t('Copying - please wait'),
      ),
    ),
  );
  $controls['om_copy_props_message'] = array(
    '#prefix' => "<div id ='om_copy_props_message'>",
    '#suffix' => "</div>",
    '#value' => 0,
    '#markup' => t(''),
    '#weight' => 15,
  );
  
  return $controls;
  
}

function om_copy_props_form_submit(&$form, &$form_state) {
  $featureid = intval($form_state['values']['featureid']);
  $entity_type = trim($form_state['values']['entity_type']);
  $propnames = explode(',',$form_state['values']['propnames']);
  $pid = intval($form_state['values']['pid']);
  //dpm($form_state['values'], 'values');
  dsm("$pid om_copy_props_form_submit(); ");
  $src_entity = entity_load_single($entity_type, $featureid);
  $dest_entity = entity_load_single('dh_properties', $pid);
  $outstr = '';
  $del = '';
  foreach ($propnames as $propname) {
    $propname = trim($propname);
    // args are (src_entity, entity_type, propname, $fields = TRUE, $defprops = FALSE, $allprops = FALSE);
    $rez = om_copy_properties($src_entity, $dest_entity, $propname, TRUE, FALSE, FALSE);
    $outstr .= " Calling om_copy_properties($featureid, $pid, $propname, TRUE, FALSE, FALSE); \n";
    if (is_object($rez) and property_exists($rez,'pid')) {
      $outstr .= "<br> pid = ($rez->pid) \n";
    } else {
      $outstr .= "<br> Could not copy $propname \n";
    }
    $del = '<br>';
  }
  $element['om_copy_props_message']['#prefix'] = "<div id ='om_copy_props_message'>";
  $element['om_copy_props_message']['#markup'] = "</div>";
  $element['om_copy_props_message']['#markup'] = "Model Copy Results" . $outstr;
  return $element;
}

function om_find_dh_by_elementid($elementid) {
  $q = "select featureid from {dh_properties} where propvalue = :elid and varid in (select hydroid from dh_variabledefinition where varkey = 'om_element_connection')";
  $pid = db_query($q,array('elid'=>$elementid))->fetchField();
  return $pid;
}
