<?php
// this should go in the module file when appropriate

/**
 * Implements hook_field_settings_form().
 */
function om_field_settings_form($field, $instance, $has_data) {
  $settings = $field['settings'];

  $form = array();
  // get plugins available and display them for default_object_class setting
  // but we may not store any settings at this level?

  return $form;
}

/**
 * Implements hook_field_instance_settings_form().
 */
function om_field_instance_settings_form($field, $instance) {
  $settings = $instance['settings'];
  // get plugins available and display them for default_object_class setting
  $form['default_object_class'] = array(
    '#type' => 'select',
    '#title' => t('Default Object Class'),
    '#default_value' => $settings['default_object_class'],
    '#options' => array(
      t('Plain text'),
      t('Filtered text (user selects text format)'),
    ),
  );

  return $form;
}

?>