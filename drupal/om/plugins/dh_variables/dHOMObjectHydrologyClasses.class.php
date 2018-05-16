<?php
// @todo: fuigure out how to insure other plugin files are called when needed bby this plugin
//        OR just move all the base classes into the module ?
module_load_include('inc', 'dh', 'plugins/dh.display');
module_load_include('module', 'dh');
// make sure that we have base plugins 
$plugin_def = ctools_get_plugins('dh', 'dh_variables', 'dHOMmodelElement');
$class = ctools_plugin_get_class($plugin_def, 'handler');
dpm("so far so good");
class dHOMHydroImpoundment extends dHOMModelElement {
  var $object_class = 'hydroImpoundment';
}

?>