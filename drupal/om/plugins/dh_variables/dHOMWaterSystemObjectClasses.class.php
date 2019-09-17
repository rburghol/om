<?php

module_load_include('inc', 'dh', 'plugins/dh.display');
module_load_include('module', 'dh');
// make sure that we have base plugins 
$plugin_def = ctools_get_plugins('dh', 'dh_variables', 'dHOMmodelElement');
$class = ctools_plugin_get_class($plugin_def, 'handler');

// changed parent cause it wasn't embedding 
//class dHOMWaterSystemObject extends dHOMModelContainer {
class dHOMWaterSystemObject extends dHOMModelElement {
  // takes over some duties from wsp_vpdesvwuds, and wsp_waterUser
  //   since most of their function was bacjend database connections 
  // See getDefaults() for default subcomps and properties
  var $object_class = 'blankShell';
  var $attach_method = 'contained';
  
  public function hiddenFields() {
    $hidden = array_merge(array('propcode', 'propvalue'), parent::hiddenFields());
    return $hidden;
  }
  // can create framework here to set properties that are needed, similar to object_class properties
  // being automatically added.
  // will use standard editing for now, but...
  public function addAttachedProperties(&$form, &$entity) {
    dpm($entity, 'addAttachedProperties');
    $dopples = $this->getDefaults($entity);
    foreach ($dopples as $thisvar) {
      if (!isset($thisvar['embed']) or ($thisvar['embed'] === TRUE)) {
        $pn = $this->handleFormPropname($thisvar['propname']);
        $dopple = $entity->{$thisvar['propname']};
        // @todo: if this is a code variable, we should get propcode?
        switch ($this->attach_method) {
          case 'contained':
          $plugin = dh_variables_getPlugins($dopple);
          if ($plugin) {
            if (method_exists($plugin, 'attachNamedForm')) {
              dsm("Using attachNamedForm()");
              $plugin->attachNamedForm($form, $dopple);
            } else {
              dsm("Using formRowEdit()");
              $plugin->formRowEdit($dopple_form, $dopple);
              $form[$pn] = $dopple_form['propvalue'];
            }
          }
          break;
          default:
          $dopple_form = array();
          dsm("Not attaching $pn");
          dh_variables_formRowPlugins($dopple_form, $dopple);
          $form[$pn] = $dopple_form['propvalue'];
          break;
        }
      }
      if (isset($thisvar['#weight'])) {
        $form[$pn]['#weight'] = $thisvar['#weight'];
      }
    }
    dpm($form, 'final form');
  }
  
  public function getDefaults($entity, &$defaults = array()) {
    parent::getDefaults($entity, $defaults);
    // @tbd: 
    // - historic_monthly_pct
    // - historic_annual 
    // - consumption
    // - surface_mgd : an equation that always equals wd_mgd, since these are all ssumed to be intakes not wells
    $defaults = array(
      'riverseg' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0.0,
        'propname' => 'riverseg',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varname' => 'riverseg',
        'vardesc' => 'riverseg.',
        'varid' => dh_varkey2varid('om_class_textField', TRUE),
      ), 
    ) + $defaults;
    //dpm($defaults,'defs');
    return $defaults;
  }
}

?>