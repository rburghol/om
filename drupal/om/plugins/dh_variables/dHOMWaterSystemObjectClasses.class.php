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
      'vwp_exempt_mgd' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => '0.0',
        'propvalue_default' => 0.0,
        'propname' => 'vwp_exempt_mgd',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varname' => 'Estimated Exempt Value (MGD)',
        'vardesc' => 'Estimated Exempt Value.',
        'varid' => dh_varkey2varid('om_class_Equation', TRUE),
      ), 
    ) + $defaults;
    //dpm($defaults,'defs');
    return $defaults;
  }
}

// ## Special function for use by tiered and simple flowby vars
function om_formatCFB(&$form, $entity) {
  // combine into fieldset
  $form['cfb'] = array(
    '#type' => 'fieldset',
    '#weight' => -5,
  );
  $form['cfb']['enable_cfb'] = $form['enable_cfb'];
  $form['cfb']['cfb_condition'] = $form['cfb_condition'];
  $form['cfb']['cfb_var'] = $form['cfb_var'];
  unset($form['cfb_var']);
  unset($form['cfb_condition']);
  unset($form['enable_cfb']);
}

class dHOMWaterSystemFlowBy extends dHOMSubComp {
  var $object_class = 'wsp_flowby';
  var $attach_method = 'contained';
  
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
      'flowby_eqn' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => '0.0',
        'propvalue_default' => 0.0,
        'propname' => 'flowby_eqn',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varname' => 'Base Flowby/Release',
        'vardesc' => 'Base equation for calculating flowby/release.',
        'varid' => dh_varkey2varid('om_class_Equation', TRUE),
      ), 
      'enable_cfb' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0,
        'datatype' => 'boolean',
        'propname' => 'enable_cfb',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varname' => 'Enable Conditional Variable',
        'vardesc' => 'Select TRUE to utilize riser structure alorithm to solve for outflow.',
        'varid' => dh_varkey2varid('om_class_Constant', TRUE),
      ),
      'cfb_var' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0,
        'datatype' => 'numeric',
        'propname' => 'cfb_var',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varname' => 'If/Else Variable',
        'vardesc' => 'Variable to compare to select alternate for flowby/release.',
        'varid' => dh_varkey2varid('om_class_PublicVars', TRUE),
      ),
      'cfb_condition' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0,
        'datatype' => 'numeric',
        'propname' => 'cfb_condition',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varname' => 'When',
        'vardesc' => 'Condition for comparison.',
        'varid' => dh_varkey2varid('om_class_AlphanumericConstant', TRUE),
      ),
    ) + $defaults;
    //dpm($defaults,'defs');
    return $defaults;
  }
  
  public function formRowEdit(&$form, $entity) {
    parent::formRowEdit($form, $entity);
    
    $form['cfb_condition']['#type'] = 'select';
    $form['cfb_condition']['#options'] = array('lt'=>'<', 'gt'=>'>');
    
    // orders
    $form['varid']['#weight'] = -10;
    $form['propname']['#weight'] = -9;
    $form['enable_cfb']['#weight'] = -5;
    $form['cfb_condition']['#weight'] = -4;
    $form['cfb_var']['#weight'] = -3;
    om_formatCFB($form, $entity);
  }
  
}

class dHOMWaterSystemTieredFlowBy extends dHOMDataMatrix {
  var $object_class = 'wsp_1tierflowby';
  var $attach_method = 'contained';
  
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
    // - surface_mgd : an equation that always equals wd_mgd, since these are all assumed to be intakes not wells
    $defaults = array(
      'enable_cfb' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0,
        'datatype' => 'boolean',
        'propname' => 'enable_cfb',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varname' => 'Enable Conditional Variable',
        'vardesc' => 'Select TRUE to utilize riser structure alorithm to solve for outflow.',
        'varid' => dh_varkey2varid('om_class_Constant', TRUE),
      ),
      'cfb_var' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0,
        'datatype' => 'numeric',
        'propname' => 'cfb_var',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varname' => 'If/Else Variable',
        'vardesc' => 'Variable to compare to select alternate for flowby/release.',
        'varid' => dh_varkey2varid('om_class_PublicVars', TRUE),
      ),
      'cfb_condition' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0,
        'datatype' => 'numeric',
        'propname' => 'cfb_condition',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varname' => 'When',
        'vardesc' => 'Condition for comparison.',
        'varid' => dh_varkey2varid('om_class_AlphanumericConstant', TRUE),
      ),
    ) + $defaults;
    //dpm($defaults,'defs');
    $defaults['lutype2']['propvalue_default'] = 2;
    return $defaults;
  }
  
  public function formRowEdit(&$form, $entity) {
    parent::formRowEdit($form, $entity);
    $form['keycol2']['#type'] = 'hidden';
    $form['lutype2']['#type'] = 'hidden';
    //unset($form['lutype2']);
    //unset($form['keycol2']);
    
    $form['cfb_condition']['#type'] = 'select';
    $form['cfb_condition']['#options'] = array('lt'=>'<', 'gt'=>'>');
    
    // orders
    $form['varid']['#weight'] = -10;
    $form['propname']['#weight'] = -9;
    $form['keycol1']['#weight'] = -8;
    $form['lutype1']['#weight'] = -7;
    $form['field_dh_matrix']['#weight'] = -6;
    $form['enable_cfb']['#weight'] = -5;
    $form['cfb_condition']['#weight'] = -4;
    $form['cfb_var']['#weight'] = -3;
    om_formatCFB($form, $entity);
  }
  
  public function tableDefault($entity) {
    // Returns associative array keyed table (like is used in OM)
    // This format is not used by Drupal however, so a translation 
    //   with tablefield_parse_assoc() is usually in order (such as is done in load)
    // set up defaults - we can sub-class this to handle each version of the model land use
    // This version is based on the Chesapeake Bay Watershed Phase 5.3.2 model land uses
    // this brings in an associative array keyed as $table[$luname] = array( $year => $area )
    $table = array();
    $table[] = array('xTrigger', 'xMIF');
    $table[] = array(0,0);
    return $table;
  }
}
?>