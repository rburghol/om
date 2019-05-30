<html>
<body>
<h3>Test Model Run</h3>

<?php


# set up db connection
$noajax = 1;
$projectid = 3;
$scid = 4;

//include_once('xajax_modeling.element.php');
include_once('config.php');
#include('qa_functions.php');
#include('ms_config.php');
/* also loads the following variables:
   $libpath - directory containing library files
   $indir - directory for files to be read
   $outdir - directory for files to be written
*/
error_reporting(E_ERROR);
print("Un-serializing Model Object <br>\n");
$debug = 0;

$dt = 86400;
$starttime = '1984-01-01';
$endtime = '2005-12-31';
$scenarioname = 'p52An';

if (isset($_GET['scenarioname'])) {
   $scenarioname = $_GET['scenarioname'];
}
if (isset($argv[1])) {
   $scenarioname = $argv[1];
}
if (isset($argv[2])) {
   $onelu = $argv[2];
   print("Single landuse requested: $onelu \n");
} else {
   $onelu = '';
}
if (isset($argv[3])) {
   $onelseg = trim($argv[3]);
   print("Single land segment requested: $onelseg \n");
} else {
   $onelseg = '';
}

switch ($scenarioname) {
   case 'p5186':
      $scid = 1;
      $modelbase = '/opt/model/p518';
      $metbase = "$modelbase/wdm";
      $landbase = $modelbase;
   break;
   
   case 'p52An':
      $scid = 2;
      $modelbase = '/opt/model/p52';
      $metbase = "$modelbase/input/scenario/climate";
      $landbase = "$modelbase/tmp";
   break;
   
   case 'p52icprb':
      $scid = 3;
      $modelbase = '/opt/model/p52icprb';
      $metbase = "$modelbase/input/scenario/climate";
      $landbase = "$modelbase/tmp";
   break;
   
   case 'p53cal':
      $scid = 4;
      $modelbase = "/opt/model/p53/p532c-sova";
      $metbase = "$modelbase/input/scenario/climate";
      $landbase = "$modelbase/tmp";
   break;

   case 'p53sova':
      $scid = 4; // stores in same as p53cal since they are the same
      $modelbase = "/opt/model/p53/p532c-sova";
      $metbase = "$modelbase/input/scenario/climate";
      $landbase = "$modelbase/tmp";
   break;
   
   default:
      $scid = 2;
      $modelbase = '/opt/model/p52';
      $metbase = "$modelbase/input/scenario/climate";
      $landbase = "$modelbase/tmp";
   break;
   
}

if (strlen($onedsn) > 0) {
   $dsns = array($onedsn);
} else {
   $dsns = array('111','211','411');
}
// right now, for various reasons, only one DSN may be read at a time.  This is a problem from a time 
// stand point, but not that big a deal in the grand scheme of things
$dsn_names = array(
   '111'=>array('param_name'=>'SURO', 'param_block'=>'PERLND', 'param_group'=>'PWATER', 'wdm'=>3),
   '211'=>array('param_name'=>'IFWO', 'param_block'=>'PERLND', 'param_group'=>'PWATER', 'wdm'=>3),
   '411'=>array('param_name'=>'AGWO', 'param_block'=>'PERLND', 'param_group'=>'PWATER', 'wdm'=>3),
   '2000'=>array('param_name'=>'PREC', 'param_block'=>'PERLND', 'param_group'=>'EXTNL', 'wdm'=>2),
   '1000'=>array('param_name'=>'PETINP', 'param_block'=>'PERLND', 'param_group'=>'EXTNL', 'wdm'=>1)
);

$wdms = array(
   1=>array('path'=>"$metbase/met/janstorm/"),
   2=>array('path'=>"$metbase/prad/ns611a902/"),
   3=>array('path'=>"$landbase/wdm/land/")
);
$path = "/opt/model/p52/tmp/wdm/land/";
# to do a single file, simply include its full name here
#$path = "/var/www/html/wooomm/dirs/proj3/components/cbp/PS2_5560_5100.uci";
// since we can only handle one DSN at a time, we can retrieve the custom path for the WDM
$wdm_no = $dsn_names[$dsns[0]]['wdm'];
$path = $wdms[$wdm_no]['path'];

# initilize cbp data connection
// *** this should already be done -- commenting out
/*
$cbp_connstring = "host=$dbip dbname=cbp user=$dbuser password=$dbpass ";
$cbp_dbconn = pg_connect($cbp_connstring, PGSQL_CONNECT_FORCE_NEW);
$cbp_listobject = new pgsql_QueryObject;
$cbp_listobject->ogis_compliant = 1;
$cbp_listobject->connstring = $cbp_connstring;
$cbp_listobject->dbconn = $cbp_dbconn;
*/

# get list of files in uci directory
# iterate through list, using element 589 as the template, and simply setting the uciname to a new value and calling init()

if ( ($onelu == '') ) {
   $lunames = getFileArray($path,'');
   print("Loading lu-names from $path <br>\n");
} else {
   $lunames = array($onelu);
}
$i = 0;
foreach ($lunames as $luname) {

	$cbp_listobject->querystring = "  select a.location_id, a.id2 as landseg,";
	$cbp_listobject->querystring .= "    a.id3 as luname, b.param_name, b.param_block, ";
	$cbp_listobject->querystring .= "    b.param_group, count(b.*) as num_recs ";
	$cbp_listobject->querystring .= " from cbp_model_location as a left outer join ";
	$cbp_listobject->querystring .= "    cbp_scenario_output as b ";
	$cbp_listobject->querystring .= " on (a.location_id = b.location_id ) ";
	$cbp_listobject->querystring .= " where a.scenarioid = $scid ";
	if (strlen($onelseg) > 0) {
	 $cbp_listobject->querystring .= " and id2 = '$onelseg' ";
	}
	$cbp_listobject->querystring .= " and id1 = 'land' ";
	$cbp_listobject->querystring .= " and id3 = '$luname' ";
	$cbp_listobject->querystring .= " group by a.location_id, a.id2, a.id3, b.param_name, ";
	$cbp_listobject->querystring .= "    b.param_block, b.param_group ";
	$cbp_listobject->querystring .= " order by a.id2, a.id3 ";
	print("$cbp_listobject->querystring ; <br>\n");
	$cbp_listobject->performQuery();      
	print("Inventorying: Land-Use: $luname, Land-seg: ");
	if (strlen($onelseg) > 0) {
	 print("$onelseg");
	} else {
	 print("*");
	}
	print(" <br>\n");
	$recs = $cbp_listobject->queryrecords;
	foreach ($recs as $thisrec) {
	 print(print_r($thirecs,1) . " <br>\n");
	}

}

?>
</body>

</html>
