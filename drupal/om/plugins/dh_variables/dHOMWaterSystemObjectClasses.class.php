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
  var $object_class = 'waterSupplyElement';
  var $attach_method = 'contained';
  var $om_template_id = 340402; // remote server template ID, set FALSE if not used.
  
  public function hiddenFields() {
    $hidden = array_merge(array('propvalue'), parent::hiddenFields());
    return $hidden;
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
      'fac_current_mgy' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0.0,
        'propname' => 'fac_current_mgy',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varname' => 'Facility Current MGY',
        'vardesc' => 'Total current average annual demand for the facility associated with this mode.',
        'varid' => dh_varkey2varid('om_class_Equation', TRUE),
      ), 
      'current_mgy' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0.0,
        'propname' => 'current_mgy',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varname' => 'Intake Current MGY',
        'vardesc' => 'Intake specific urrent average annual demand.',
        'varid' => dh_varkey2varid('om_class_Equation', TRUE),
      ), 
    ) + $defaults;
    //dpm($defaults,'defs');
    return $defaults;
  }
}

?>