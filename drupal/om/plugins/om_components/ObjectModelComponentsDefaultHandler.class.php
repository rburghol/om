<?php


class ObjectModelComponentsDefaultHandler {
  var $options = array();
  var $type;
  var $state = array();
  var $parent_var_prefix;
  
  public function __construct($options = array()) {
    $this->init($options);
    $this->ApplyEntityOptions();
    $this->parent_var_prefix = '';
  }
  
  public function init($options) {
    foreach ($this->DefineOptions() as $opt => $def) {
      if (isset($options[$opt])) {
        $this->options[$opt] = $options[$opt];
      } else {
        $this->options[$opt] = $def['default'];
      }
    }
  }

  function EntityDefaults() {
    // allow_override = can this be set by user in options screen?
    $options = array(
      'parent_var_prefix' => array ('default' => '', 'allow_override'=>FALSE),
    );
    return $options;
  }

  
  function ApplyEntityOptions() {
    // sets entity property based on options for those allowed to be overridden
    foreach ($this->EntityDefaults() as $key => $def) {
      if ($def['allow_override']) {
        $this->$key = isset($this->options[$key]) ? $this->options[$key] : $def['default'];
      }
    }
  }
  
  function DefineOptions() {
    $options = array(
      'enabled' => array ('default' => TRUE),
    );
    return $options;
  }
  
  //public function buildOptionsForm(&$form, FormStateInterface $form_state) {
  // when we go to D8 this will be relevant
  public function buildOptionsForm(&$form, $form_state) {
    // form used for configuration of this as a widget in a panel/pane
    // classes that inherit this will call the parent, but since this is the parent - don't
    // parent::buildOptionsForm($form, $form_state);
    // allow + for or, , for and
    
    $form['enabled'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enabled'),
      '#description' => t('If selected, this component is enabled'),
      '#default_value' => !empty($this->options['enabled']),
    );
  }
  
  //public function buildForm(&$form, FormStateInterface $form_state) {
  // when we go to D8 this will be relevant
  public function buildForm(&$form, $form_state) {
    // form to gather user input
    
    $form['enabled'] = array(
      '#type' => 'checkbox',
      '#title' => t('Enabled'),
      '#description' => t('If selected, this component is enabled'),
      '#default_value' => !empty($this->options['enabled']),
    );
  }
  
  function GetParentVarName($thisvar) {
    $var = (strlen($this->parent_var_prefix) > 0) ? $this->parent_var_prefix . "_" . $thisvar : $thisvar;
    return $var;
  }
   
  function WriteToParent($vars = array(), $verbose = 0) {
    // writeable vars must be in the objects state array and on the wvars list
    if (count($vars) == 0) {
      $vars = $this->wvars;
    }
    if (is_object($this->parentobject)) {
      foreach ($vars as $thisvar) {
        if (method_exists($this->parentobject, 'SetStateVar')) {
          $this->parentobject->SetStateVar($this->GetParentVarName($thisvar), $this->state[$thisvar]);
        }
      }
    }
  }
}

?>