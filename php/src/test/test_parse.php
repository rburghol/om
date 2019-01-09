<?php

include("config.php");
if (!isset($argv[1])) {
   print("Usage: php test_loadelement.php xmlfile \n");
   die;
} else {
   $xmlfile = $argv[1];
}
$opxml = file_get_contents($xmlfile);
error_reporting(E_ALL);
print("Parsing " . substr($opxml, 1, 64) . "\n");
$options = array("complexType" => "object");
$unserializer = new XML_Unserializer($options);
$unserializer->unserialize($opxml, false);
$thisobject = $unserializer->getUnserializedData();

print("Done\n");

$props = (array)$thisobject;
print("Parsed the following props " . print_r($props,1) . "\n");
?>
