<?php
module_load_include('inc', 'dh', 'plugins/dh.display');
module_load_include('module', 'dh');
// make sure that we have base plugins 
$plugin_def = ctools_get_plugins('dh', 'dh_variables', 'dHOMModelElement');
$class = ctools_plugin_get_class($plugin_def, 'handler');

class dHOMCBPLandDataConnectionFile extends dHOMModelElement {
  var $object_class = 'CBPLandDataConnectionFile';
  
  public function getDefaults($entity, &$defaults = array()) {
    // needs:
    //   - land seg - constant alpha
    //   - riverseg - constant alpha 
    //   - landuse - default matrix with acreage 
    //   - landuse_var - default matrix with runmode/luvar name pairs (defaults to all being "landuse")
    //   @tbd - file_var - make object in OM be flexible at startup to use a lookup or alpha variable to select 
    //          the file to use.  Allows us to switch between climate change or other meteorology runs.
    //   @tbd: - luyear - land use year, defaults to timer year, but may be fixed.
    $defaults = parent::getDefaults($entity, $defaults);
    $defaults += array(
      'defaultval' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propname' => 'defaultval',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'vardesc' => 'Initial value.',
        'varname' => 'Initial Value',
        'varid' => dh_varkey2varid('om_class_AlphanumericConstant', TRUE),
      ),
    );
    return $defaults;
  }
  
  public function formRowEdit(&$rowform, $entity) {
    parent::formRowEdit($rowform, $entity);
    $rowform['propcode']['#title'] = '';
    $rowform['propcode']['#prefix'] = ' = ';
  }
  
  public function setAllRemoteProperties($entity, $elid, $path) {
    parent::setAllRemoteProperties($entity, $elid, $path);
  }
}
  
?>