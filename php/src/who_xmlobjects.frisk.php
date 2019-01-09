<?php

if (!isset($who_xmlobjects)) {
   $who_xmlobjects = array();
}
if (!isset($serializer)) {
   error_log("Creating Serializer Object<br>");
   $serializer = new XML_Serializer();
}
include_once("$libpath/lib_om.frisk.php");

error_log("Creating VTFungusRiskModel<br>");
error_reporting(E_ALL);
#print("Creating Object<br>");
$obj = new VTFungusRiskModel;
// perform serialization
$result = $serializer->serialize($obj);
#print("Printing Result<br>");
// check result code and display XML if success
if($result === true)
{
   $xml = $serializer->getSerializedData();
   $who_xmlobjects['VTFungusRiskModel']['xml'] = $xml;
   $who_xmlobjects['VTFungusRiskModel']['type'] = '2'; # type 1 - stand-alone object, 2 - sub-component only, 3 - model container (runnable), 4 - both stand-alone and sub-comp
   $who_xmlobjects['VTFungusRiskModel']['toolgroup'] = 6; 
   $who_xmlobjects['VTFungusRiskModel']['name'] = 'Fungus Risk Model';
   $who_xmlobjects['VTFungusRiskModel']['parent'] = array('timeSeriesFile', 'modelContainer'); // only avail as sub-comp to this object
   $who_xmlobjects['VTFungusRiskModel']['parentprops'] = array('t_var'=>'publicvars', 'w_var'=>'publicvars', 'd_var'=>'publicvars');
   $who_xmlobjects['VTFungusRiskModel']['description'] = 'Widget to process ARIMA model.';
}

?>
