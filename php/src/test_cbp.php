<html>
<body>
<h3>Test Model Run</h3>

<?php


# set up db connection
#include('config.php');
$noajax = 1;
$projectid = 3;
$scid = 2; // 2 = p52An
include_once('xajax_modeling.element.php');
//set up cbp DB object
$dbname = 'cbp';
$connstring = "host=$dbip dbname=$dbname user=$dbuser password=$dbpass";
$dbconn = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);

$cbpdb = new pgsql_QueryObject;
$cbpdb->connstring = $connstring;
$cbpdb->ogis_compliant = 1;
$cbpdb->dbconn = $dbconn;
$cbpdb->adminsetuparray = $adminsetuparray;

//include_once('config.php');
#include('qa_functions.php');
#include('ms_config.php');
/* also loads the following variables:
   $libpath - directory containing library files
   $indir - directory for files to be read
   $outdir - directory for files to be written
*/
error_reporting(E_ERROR);
print("Getting Land Use <br>\n");
$debug = 0;
$localdebug = 0;
$segid = '5100';
$seginfo = getCBPSegmentLanduse($cbpdb, $scid, $segid);

$cbpdb->quueryrecords = $seginfo['local_annual'];
$cbpdb->showList();
$cbpdb->quueryrecords = $seginfo['contrib_annual'];
$cbpdb->showList();

?>
</body>

</html>
