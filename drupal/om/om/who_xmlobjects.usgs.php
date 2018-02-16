<?php

if (!isset($who_xmlobjects)) {
   $who_xmlobjects = array();
}
if (!isset($serializer)) {
   error_log("Creating Serializer Object<br>");
   $serializer = new XML_Serializer();
}
include_once("$libpath/lib_wooomm.USGS.php");

error_log("Creating USGSGageObject<br>");
$obj = new USGSGageObject;
// perform serialization
$result = $serializer->serialize($obj);
#print("Printing Result<br>");
// check result code and display XML if success
if($result === true)
{
   $xml = $serializer->getSerializedData();
   $who_xmlobjects['USGSGageObject']['xml'] = $xml;
   $who_xmlobjects['USGSGageObject']['type'] = '4'; # type 1 - stand-alone object, 2 - sub-component only, 3 - model container (runnable), 4 - both stand-alone and sub-comp
   $who_xmlobjects['USGSGageObject']['toolgroup'] = 8;
   $who_xmlobjects['USGSGageObject']['geomtype'] = 3;
   $who_xmlobjects['USGSGageObject']['name'] = 'USGS Gage Time Series';
   # properties to return to child object for use in select lists, etc.
   $who_xmlobjects['USGSGageObject']['description'] = 'Historical stream flow, groundwater, or reservoir data, including quantity and quality, retrieved from the USGS NWIS systems.';
}

error_log("Creating USGSSyntheticRecord<br>");
$obj = new USGSSyntheticRecord;
// perform serialization
$result = $serializer->serialize($obj);
#print("Printing Result<br>");
// check result code and display XML if success
if($result === true)
{
   $xml = $serializer->getSerializedData();
   $who_xmlobjects['USGSSyntheticRecord']['xml'] = $xml;
   $who_xmlobjects['USGSSyntheticRecord']['type'] = '1'; 
   $who_xmlobjects['USGSSyntheticRecord']['toolgroup'] = 1;# type 1 - stand-alone object, 2 - sub-component only, 3 - model container (runnable), 4 - both stand-alone and sub-comp
   $who_xmlobjects['USGSSyntheticRecord']['name'] = 'USGS Synthetic Flow Record';
   # properties to return to child object for use in select lists, etc.
   $who_xmlobjects['USGSSyntheticRecord']['description'] = 'Synthetic flow calclated from a continous gage based on power function.';
}

error_log("Creating USGSArima<br>");
//error_reporting(E_ALL);
#print("Creating Object<br>");
$obj = new USGSArima;
// perform serialization
$result = $serializer->serialize($obj);
#print("Printing Result<br>");
// check result code and display XML if success
if($result === true)
{
   $xml = $serializer->getSerializedData();
   $who_xmlobjects['USGSArima']['xml'] = $xml;
   $who_xmlobjects['USGSArima']['type'] = '2'; # type 1 - stand-alone object, 2 - sub-component only, 3 - model container (runnable), 4 - both stand-alone and sub-comp
   $who_xmlobjects['USGSArima']['toolgroup'] = 6; 
   $who_xmlobjects['USGSArima']['name'] = 'USGS Arima Equation';
   $who_xmlobjects['USGSArima']['parent'] = array('USGSGageObject'); // only avail as sub-comp to this object
   $who_xmlobjects['USGSArima']['parentprops'] = array('q_var'=>'publicvars');
   $who_xmlobjects['USGSArima']['description'] = 'Widget to process ARIMA model.';
}

?>
