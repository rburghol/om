<?php

$noajax = 1;
$projectid = 3;
include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);

global $listobject;

function updateObjectPropTest($elementid) {     
   global $listobject, $debug;
   if ($debug) {
      error_log("Updating properties on object $elementid");
      error_log("Unserializing $elementid");
   }
   #$debug = 1;
   $loadres = unSerializeSingleModelObject($elementid);
   #$debug = 0;
   $thisobject = $loadres['object'];
   print("<b>DEBUG:</b>" . $loadres['debug'] . "<hr>");
   print("<b>ERROR:</b>" . $loadres['error'] . "<hr>");
   #if ($debug) {
      error_log("Compacting and updating inputs $elementid");
   #}
   if (is_object($thisobject)) {
      $compres = compactSerializeObject($thisobject);
      if (!$compres['error']) {
         #if ($debug) {
            error_log("Storing inputs and properties on $elementid");
         #}
         $object_xml = $compres['object_xml'];
         $props_xml = $compres['props_xml'];
         $inputs_xml = $compres['inputs_xml'];

         # get the object back
         $listobject->querystring = " update scen_model_element set elemprops = '$props_xml', eleminputs = '$inputs_xml' ";
         $listobject->querystring .= " where elementid = $elementid ";
         if ($debug) {
            $innerHTML .= "$listobject->querystring<br>";
            error_log($listobject->querystring);
         }
         $listobject->performQuery();
         if ($debug) {
            error_log("Database update for $elementid");
         }
      } else {
         $innerHTML .= $compres['errorHTML'];
      }
   } else {
      $innerHTML .= "There was a problem loading object $elementid - " . $loadres['error'];
   }
   
   return $innerHTML;
}


$a = new stdClass;
$a->childprop = 'Some Text';
$b = new stdClass;
$b->childprop = 153;
$c = new stdClass;
$c->childprop = array('object_a'=>$a);

$qrecs = array($a, $b, $c);

$options = array(
                    XML_SERIALIZER_OPTION_INDENT      => '    ',
                    XML_SERIALIZER_OPTION_LINEBREAKS  => "\n",
                    XML_SERIALIZER_OPTION_DEFAULT_TAG => 'unnamedItem',
                    XML_SERIALIZER_OPTION_TYPEHINTS   => true
          );
$serializer = new XML_Serializer($options);
// perform serialization
$j = 0;
foreach($qrecs as $thisrec) {
   $result = $serializer->serialize($thisrec);
   if ($debug) {
      error_log("Object serialized");
   }
   // check result code and display XML if success
   if ($debug) {
      error_log("Retrieving XML data for storage.");
   }
   if ($result === true)
   {
      $debugHTML .= "Storing XML in database<br>";
      $object_xml = $serializer->getSerializedData();
   }
   
   print("<b>Object $j :</b>$object_xml <hr>");
   $j++;
}


die;
   

$listobject->querystring = "select src_id from map_model_linkages where dest_id = 245 and linktype = 1 group by src_id";
$listobject->performQuery();
$qrecs = $listobject->queryrecords;

foreach ($qrecs as $thisrec) {
   $elid = $thisrec['src_id'];
   error_log("Updating properties for $elid");
   print("Updating properties for $elid<br>");
   updateObjectPropTest($elid);
   error_log("Finished $elid");
   print("Finished $elid<br>");
}

?>