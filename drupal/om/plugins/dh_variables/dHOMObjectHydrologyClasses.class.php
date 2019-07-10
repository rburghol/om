<?php
// @todo: figure out how to insure other plugin files are called when needed by this plugin
//        OR just move all the base classes into the module ?
module_load_include('inc', 'dh', 'plugins/dh.display');
module_load_include('module', 'dh');
// make sure that we have base plugins 
$plugin_def = ctools_get_plugins('dh', 'dh_variables', 'dHOMmodelElement');
$class = ctools_plugin_get_class($plugin_def, 'handler');
//dpm("so far so good");
class dHOMHydroImpoundment extends dHOMModelElement {
  var $object_class = 'hydroImpoundment';
  var $attach_method = 'contained';
  
  public function hiddenFields() {
    $hidden = array_merge(array('propcode', 'propvalue'), parent::hiddenFields());
    return $hidden;
  }
  // can create framework here to set properties that are needed, similar to object_class properties
  // being automatically added.
  // will use standard editing for now, but...
  
  public function getDefaults($entity, &$defaults = array()) {
    parent::getDefaults($entity, $defaults);
    $defaults += array(
      'initstorage' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 0.0,
        'propname' => 'initstorage',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varid' => dh_varkey2varid('om_class_Constant', TRUE),
      ),
      'maxcapacity' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 100.0,
        'propname' => 'maxcapacity',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varid' => dh_varkey2varid('om_class_Constant', TRUE),
      ),
      'unusable_storage' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 10.0,
        'propname' => 'unusable_storage',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varid' => dh_varkey2varid('om_class_Constant', TRUE),
      ),
      'riser_diameter' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 10.0,
        'propname' => 'riser_diameter',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varid' => dh_varkey2varid('om_class_Constant', TRUE),
      ), 
      'riser_length' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 10.0,
        'propname' => 'riser_length',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varid' => dh_varkey2varid('om_class_Constant', TRUE),
      ), 
      'storage_stage_area' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 10.0,
        'propname' => 'storage_stage_area',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varid' => dh_varkey2varid('om_class_DataMatrix', TRUE),
      ), 
    );
    return $defaults;
  }
   
  // Advanced embedded properties in form   
  // these properties can also be added to the main form, and handled with an entity map
  // or handled directly
  // how to automatically include properties on the form?
  // 1. load the prop
  // 2. load its plugins
  // 3. create a 
  // 4. pass the form to prop->plugin->formRowEdit
}

class dHOMHydroImpoundmentSmall extends dHOMHydroImpoundment {
  var $object_class = 'hydroImpSmall';

}

class dHOMUSGSChannelGeomObject extends dHOMModelElement {
  var $object_class = 'USGSChannelGeomObject';
  var $base; // base width of channel in feet
  var $length; // channel length in feet
  var $drainage_area; // in square miles
  var $Z; // side slope Z
  var $n; // Manning's n
  
  // can create framework here to set properties that are needed, similar to object_class properties
  // being automatically added.
  // will use standard editing for now, but...
  
  public function getDefaults($entity, &$defaults = array()) {
    $defaults = parent::getDefaults($entity, $defaults);
    // length, drainage_area, Z, base, province
    $defaults += array(
      'length' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 5000.0,
        'propname' => 'length',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varid' => dh_varkey2varid('om_class_Constant', TRUE),
      ),
      'base' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 1.0,
        'propname' => 'base',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varid' => dh_varkey2varid('om_class_Constant', TRUE),
      ),
      'drainage_area' => array(
        'entity_type' => $entity->entityType(),
        'propcode_default' => NULL,
        'propvalue_default' => 1.0,
        'propname' => 'drainage_area',
        'singularity' => 'name_singular',
        'featureid' => $entity->identifier(),
        'varid' => dh_varkey2varid('om_class_Constant', TRUE),
      ),
    );
    return $defaults;
  }
   
   function setChannelGeom() {
      switch ($this->province) {
         case 1:
            # Appalachian Plateau
            # bank full stage
            $hc = 2.030;
            $he = 0.2310;
            # bank full width
            $bfc = 12.175;
            $bfe = 0.4711;
            # base width
            $bc = 5.389;
            $be = 0.5349;
            $n = 0.036; # these are mannings N, using only the median numbers from USGS study,
                        # later we should incorporate the changing N as it is for high median and low flows
         break;
         case 2:
            # Valley and Ridge
            $hc = 1.435;
            $he = 0.2830;
            $bfc = 13.216;
            $bfe = 0.4532;
            $bc = 4.667;
            $be = 0.5489;
            $n = 0.038;
         break;
         case 3:
            # Piedmont
            $hc = 2.137;
            $he = 0.2561;
            $bfc = 14.135;
            $bfe = 0.4111;
            $bc = 6.393;
            $be = 0.4604;
            $n = 0.095;
         break;
         case 4:
            # Coastal Plain
            $hc = 2.820;
            $he = 0.2000;
            $bfc = 15.791;
            $bfe = 0.3758;
            $bc = 6.440;
            $be = 0.4442;
            $n = 0.040;
         break;
         
         default:
            $hc = 2.030;
            $he = 0.2000;
            $bfc = 12.175;
            $bfe = 0.4711;
            $bc = 5.389;
            $be = 0.5349;
            $n = 0.036;
         break;
      }
         
      $h = $hc * pow($this->drainage_area, $he);
      $bf = $bfc * pow($this->drainage_area, $bfe);
      $b = $bc * pow($this->drainage_area, $be);
      $z = 0.5 * ($bf - $b) / $h; 
         # since Z is increase slope of a single side, 
         # the top width increases (relative to the bottom) at a rate of (2 * Z * h)
      # only use these derived values if they are non-zero, otherwise, use defaults
      if ($z > 0) {
         $this->Z = $z;
      } else {
         dsm("Calculated Z value from (0.5 * ($bf - $b) / $h) less than zero, using default " . $this->Z);
      }
      if ($b > 0) {
         $this->base = $b;
      } else {
         dsm("Calculated base value from ($bc * pow($this->drainage_area, $be)) less than zero, using default " . $this->base);
      }
      dsm("Calculated base value from ($bc * pow($this->drainage_area, $be)), = $b / " . $this->base . " Province: $this->province");
      $this->n = $n;

      return;

   }
   
}

class dHOMUSGSChannelGeomObject_sub extends dHOMUSGSChannelGeomObject {
  var $object_class = 'USGSChannelGeomObject_sub';
  
}
?>
