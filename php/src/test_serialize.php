<html>
<body>
<h3>Test serialize object</h3>

<?php

   # set up db connection
   include('config.php');
   # disable ajax handling, to output directly to browser
   $noajax = 1;
   include('xajax_modeling.element.php');
error_reporting(E_ALL);
   #include('qa_functions.php');
   #include('ms_config.php');
   /* also loads the following variables:
      $libpath - directory containing library files
      $indir - directory for files to be read
      $outdir - directory for files to be written
   */
$startdate = '2007-01-01';
$enddate = '2007-01-10';
$timer = new simTimer;
$timer->setTime($startdate, $enddate);
$timer->dt = 3600;

print("Creating Object<br>");
$gage = new queryWizardComponent;
$gage->name = 'Test Query Wizard';
$gage->qcols = array('name','Qout','Qin');
$gage->qcols_func = array('','min','min');
$gage->qcols_alias = array('a','b','c');

$gage->init();

print("Creating Serializer Object<br>");
#$options = array('defaultTagName'=>'item');
$options = array(XML_SERIALIZER_OPTION_MODE => XML_SERIALIZER_MODE_SIMPLEXML);
#$options = array(XML_SERIALIZER_OPTION_CLASSNAME_AS_TAGNAME => true);
$serializer = new XML_Serializer($options);
// perform serialization
print("Serializing Parent Object<br>");
#$gage->processors = array();
$result = $serializer->serialize($gage);
print("Printing Result<br>");
// check result code and display XML if success
if($result === true) 
{
 echo $serializer->getSerializedData();
}
$xml = $serializer->getSerializedData();
print("<pre>$xml</pre>");

# now, unserialize this, and look at the result

$thisload = loadElement($xml);
$qwobject = $thisload['object'];
if ($debug) {
   $innerHTML .= $thisload['debug'];
}

      
$qwobject->init();
$efinfo = $qwobject->showEditForm('testform', 0); 

$props = (array)$qwobject;
print("Unserialized properties<br>");
print_r($props);
print("<br><b>Edit Form:</b><br>");
print($efinfo['innerHTML']);
?>
</body>

</html>