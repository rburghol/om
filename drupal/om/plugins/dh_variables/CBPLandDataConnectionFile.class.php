<?php
module_load_include('inc', 'dh', 'plugins/dh.display');
module_load_include('module', 'dh');
// make sure that we have base plugins 
$plugin_def = ctools_get_plugins('dh', 'dh_variables', 'dHOMmodelElement');
$class = ctools_plugin_get_class($plugin_def, 'handler');



class dHOMCBPLandDataConnectionFile extends dHOMmodelElement {
  var $object_class = 'Equation';
  
  public function getDefaults($entity, &$defaults = array()) {
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
    //dsm("setAllRemoteProperties from dHOMEquation");
    array_unshift($path, 'equation');
    $this->setRemoteProp($entity, $elid, $path, $entity->propcode, $this->object_class);
  }
}
  
?>