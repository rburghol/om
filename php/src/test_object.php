<html>
<body>
<h3>Test Model Run</h3>

<?php


# set up db connection
#include('config.php');
$noajax = 1;
$projectid = 3;
include_once('xajax_modeling.element.php');
#include('qa_functions.php');
#include('ms_config.php');
/* also loads the following variables:
   $libpath - directory containing library files
   $indir - directory for files to be read
   $outdir - directory for files to be written
*/
error_reporting(E_ERROR);
print("Un-serializing Model Object <br>");
$debug = 0;

$elementid = 6;

if (isset($_GET['elementid'])) {
   $elementid = $_GET['elementid'];
}
if (isset($argv[1])) {
   $elementid = $argv[1];
}

$debug = 1;
$thisobresult = unSerializeSingleModelObject($elementid);
$thisobject = $thisobresult['object'];

#$thisobject->setDebug(1,2);
$thisobject->debug = 1;

$taboutput->tab_HTML['runlog'] .= $thisobject->outstring . " <br>";
print('<b>Debugging:</b>' . $thisobresult['error'] . " <hr>");
print('<b>Errors:</b>' . $thisobresult['error'] . " <hr>");


print($innerHTML);
?>
</body>

</html>
