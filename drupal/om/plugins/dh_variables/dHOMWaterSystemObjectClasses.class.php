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
        'propcode_default' => '0.0',
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
        'propcode_default' => '0.0',
        'propvalue_default' => 0.0,
        'propname' => 'current_mgy',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varname' => 'Intake Current MGY',
        'vardesc' => 'Intake specific current average annual demand.',
        'varid' => dh_varkey2varid('om_class_Equation', TRUE),
      ), 
      'current_mgd' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => 'current_mgy * historic_monthly_pct / modays',
        'propvalue_default' => 0.0,
        'propname' => 'current_mgd',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varname' => 'Intake Current MGD',
        'vardesc' => 'Intake specific average daily demand.',
        'varid' => dh_varkey2varid('om_class_Equation', TRUE),
      ), 
      'wsp2020_2020_mgy' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => '0.0',
        'propvalue_default' => 0.0,
        'propname' => 'wsp2020_2020_mgy',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varname' => 'WSP Facility 2020 MGY',
        'vardesc' => 'WSP estimated current average annual demand in 2020.',
        'varid' => dh_varkey2varid('om_class_Equation', TRUE),
      ), 
      'wsp2020_2040_mgy' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => '0.0',
        'propvalue_default' => 0.0,
        'propname' => 'wsp2020_2040_mgy',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varname' => 'WSP Facility 2040 MGY',
        'vardesc' => 'WSP estimated future average annual demand in 2040.',
        'varid' => dh_varkey2varid('om_class_Equation', TRUE),
      ), 
      'wsp2020_2030_mgy' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => '(wsp2020_2020_mgy + wsp2020_2040_mgy) / 2.0',
        'propvalue_default' => 0.0,
        'propname' => 'wsp2020_2030_mgy',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varname' => 'WSP Facility 2040 MGY',
        'vardesc' => 'WSP estimated future average annual demand in 2040.',
        'varid' => dh_varkey2varid('om_class_Equation', TRUE),
      ), 
      'riverseg_frac' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => 'current_mgy / fac_current_mgy',
        'propvalue_default' => 0.0,
        'propname' => 'riverseg_frac',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varname' => 'Intake Current MGY',
        'vardesc' => 'Intake specific current average annual demand.',
        'varid' => dh_varkey2varid('om_class_Equation', TRUE),
      ), 
      'fac_demand_mgd' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => 'fac_demand_mgy * riverseg_frac * historic_monthly_pct / modays',
        'propvalue_default' => 0.0,
        'propname' => 'wsp2020_2030_mgy',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varname' => 'WSP Facility 2040 MGY',
        'vardesc' => 'WSP estimated future average annual demand in 2040.',
        'varid' => dh_varkey2varid('om_class_Equation', TRUE),
      ), 
    ) + $defaults;
    //dpm($defaults,'defs');
    return $defaults;
  }
}

?>