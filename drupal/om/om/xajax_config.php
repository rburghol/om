<?php

//if ($debug) {
   error_log("Setting paths");
//}
global $indir, $outdir, $outurl, $goutdir, $gouturl, $goutpath;
$moduledir = drupal_get_path('module', 'dh_om');
$files_dir = variable_get('file_public_path', conf_path() . '/files')
$datadir = $files_dir . "/data/";
$indir = "$basedir/in";
$indir_nodrive = "$basedir_nodrive/in";
$libpath = "$moduledir/lib";
// hardwire projectid
$projectid = 3;
$compdir = "$datadir/proj$projectid/components";
$outdir = "$datadir/proj$projectid/out";
$outurl = "$dataurl/proj$projectid/out";
$outdir_nodrive = "$basedir_nodrive/dirs/proj$projectid/out";
$ucidir = "$datadir/uci/";
$glibdir = "$libpath/jpgraph";
$goutdir = "$outdir/";
$goutpath = $outdir;
$gouturl = $outurl;
# location of the graphics library - jpgraph
$glibdir = "$libpath/jpgraph/";

if ($debug) {
   error_log("Getting db Libraries");
}
# get database and file libraries
include_once("$libpath/psql_functions.php");
include_once("$libpath/file_functions.php");
# custom stream definition to write and read excel files
#include_once("$libpath/xlsstream/excel.php");

# security related libraries
include_once("$libpath/sanitize.inc.php");
include_once("$libpath/misc_functions.php");
include_once("$libpath/db_functions.php");
include_once("$libpath/data_functions.php");

if ($debug) {
   error_log("Loading Model libraries");
}
# get application libraries
include_once("$libpath/hspf.defaults.php");
include_once("$libpath/HSPFFunctions.php");
//error_log("Loading $libpath/lib_hydrology.php");
include_once("$libpath/lib_hydro.php");
include_once("$libpath/lib_hydrology.php");
include_once("$libpath/lib_equation2.php");
include_once("$libpath/lib_gis.php");
include_once("$libpath/lib_usgs.php");
include_once("$libpath/lib_nhdplus.php");
include_once("$libpath/lib_plot.php");
define('DEFAULT_GFORMAT',$default_imagetype);
include_once("$libpath/lib_vwuds.php");
include_once("$libpath/lib_vpdes.php");
include_once("$libpath/lib_wooomm.php");
include_once("$libpath/lib_wooomm.USGS.php");
include_once("$libpath/lib_wooomm.wsp.php");
//error_log("Loading lib_wooomm.noa.php ");
include_once("$libpath/lib_wooomm.noaa.php");
//error_log("Finished lib_wooomm.noa.php ");
include_once("$libpath/lib_batchmodel.php");
//error_log("$libpath/lib_batchmodel.php");
include_once("$libpath/lib_wooomm.hydro.php");
include_once("$libpath/lib_wooomm.cbp.php");
include_once ("$libdir/lib_wooomm.data.php");
include_once("$libpath/Stat1.php");

# get local libraries
include_once("$basedir/lib_local.php");
include_once("$basedir/lib_verify.php");
# get local default values
include_once("$basedir/adminsetup.php");
include_once("$basedir/local_variables.php");
# includes permission routines, etc.
include_once("$basedir/lib_admin.php");
include_once("$basedir/lib_vwp.php");

# get local form functions
#include("$basedir/forms/form_modeldata.php");

// START - set up database connections
$connstring = "host=$dbip dbname=$dbname user=$dbuser password=$dbpass";
$dbconn = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);

$listobject = new pgsql_QueryObject;
$listobject->connstring = $connstring;
$listobject->ogis_compliant = 1;
$listobject->dbconn = $dbconn;
$listobject->adminsetuparray = $adminsetuparray;

$session_connstring = "host=$session_dbip dbname=$session_dbname user=$session_dbuser password=$session_dbpass port=$session_port";
$session_dbconn = pg_connect($session_connstring, PGSQL_CONNECT_FORCE_NEW);
$session_db = new pgsql_QueryObject;
$session_db->connstring = $session_connstring;
$session_db->ogis_compliant = 1;
$session_db->dbconn = $session_dbconn;
$session_db->adminsetuparray = $adminsetuparray;

// set up the model runtime database
if ( ($dbip <> $runtime_dbip) or ($dbname <> $runtime_dbname) ) {
   $connstring = "host=$runtime_dbip dbname=$runtime_dbname user=$runtime_dbuser password=$runtime_dbpass port=$runtime_dbport";
   //error_log("Setting runtime DB host=$runtime_dbip dbname=$runtime_dbname user=$runtime_dbuser password=$runtime_dbpass port=$runtime_dbport\n");
   $dbconn = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);
   $modeldb = new pgsql_QueryObject;
   $modeldb->connstring = $connstring;
   $modeldb->ogis_compliant = 1;
   $modeldb->dbconn = $dbconn;
   $modeldb->adminsetuparray = $adminsetuparray;
} else {
   $modeldb = $listobject;
}

// linkage to cbp database with ICPRB info
$connstring = "host=$dbip dbname=cbp user=$dbuser password=$dbpass";
$dbconn = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);
$cbp_listobject = new pgsql_QueryObject;
$cbp_listobject->connstring = $connstring;
$cbp_listobject->ogis_compliant = 1;
$cbp_listobject->dbconn = $dbconn;
$cbp_listobject->adminsetuparray = $adminsetuparray;
// vwuds database link
$vwuds_dbconn = pg_connect("host=$vwuds_dbip port=5432 dbname=$vwuds_dbname user=$vwuds_dbuser password=$vwuds_dbpass");
$vwuds_listobject = new pgsql_QueryObject;
$vwuds_listobject->dbconn = $vwuds_dbconn;

// aquatic bio db
$aquatic_biodb_dbconn = pg_connect("host=$vwuds_dbip port=5432 dbname='aquatic_bio' user=$dbuser password=$dbpass");
$aquatic_biodb = new pgsql_QueryObject;
$aquatic_biodb->dbconn = $aquatic_biodb_dbconn;

// END - set up database connections

# misc
$panimapfile = 'anim_precip.map';
$aggmapfile = 'map_drought.map';

# Timer
$timer = new timerObject;

$useragent = $_SERVER['HTTP_USER_AGENT'];
if (preg_match('|MSIE ([0-9].[0-9]{1,2})|',$useragent,$matched)) {
   // IE no like .ico files in pages, so use gif
   $icons = array(
      'modelContainer'=>"$iconurl/model_container.gif",
      'waterSupplyModelNode'=>"$iconurl/model_container.gif",
      'waterSupplyElement'=>"$iconurl/model_container.gif",
      'HSPFContainer'=>"$iconurl/basins.jpg",
      'graphObject'=>"$iconurl/pie_chart.gif",
      'channelObject'=>"$iconurl/river.gif",
      'USGSChannelGeomObject'=>"$iconurl/river.gif",
      'hydroObject'=>"$iconurl/river.gif",
      'domain'=>"$iconurl/earth_sm.png",
      'NOAADataObject'=>"$iconurl/weather.gif",
      'giniGraph'=>"$iconurl/gini.gif",
      'USGSGageObject'=>"$iconurl/usgs.gif",
      'clone'=>"$iconurl/clone.gif",
      'deepclone'=>"$iconurl/deep-clone.gif",
      'edit'=>"$iconurl/edit.gif",
      'trash'=>"$iconurl/icon_trash.gif",
      'default'=>"$iconurl/default.gif",
      'tools'=>"$iconurl/tools.gif",
      'tools'=>"$iconurl/tools.gif",
      'CBPModelContainer'=>"$iconurl/cbp.gif",
      'CBPDataConnection'=>"$iconurl/cbp.gif",
      'CBPLandDataConnection'=>"$iconurl/cbp.gif",
      'CBPDataInsert'=>"$iconurl/cbp.gif"
   );
} else {
   $icons = array(
      'modelContainer'=>"$iconurl/model_container.ico",
      'waterSupplyModelNode'=>"$iconurl/model_container.ico",
      'waterSupplyElement'=>"$iconurl/model_container.ico",
      'HSPFContainer'=>"$iconurl/basins.jpg",
      'graphObject'=>"$iconurl/pie_chart.ico",
      'channelObject'=>"$iconurl/river.ico",
      'USGSChannelGeomObject'=>"$iconurl/river.ico",
      'hydroObject'=>"$iconurl/river.ico",
      'domain'=>"$iconurl/earth_sm.png",
      'NOAADataObject'=>"$iconurl/weather.ico",
      'giniGraph'=>"$iconurl/gini.ico",
      'USGSGageObject'=>"$iconurl/usgs.ico",
      'clone'=>"$iconurl/clone.ico",
      'deepclone'=>"$iconurl/deep-clone.ico",
      'edit'=>"$iconurl/edit.ico",
      'trash'=>"$iconurl/icon_trash.gif",
      'default'=>"$iconurl/default.ico",
      'tools'=>"$iconurl/tools.ico",
      'tools'=>"$iconurl/tools.ico",
      'CBPModelContainer'=>"$iconurl/cbp.ico",
      'CBPDataConnection'=>"$iconurl/cbp.ico",
      'CBPLandDataConnection'=>"$iconurl/cbp.ico",
      'CBPDataInsert'=>"$iconurl/cbp.ico"
   );
}

//session_start();
#print_r($_GET);
if (!isset($userid)) {
   $userid = -1;
}
# ajax requests should ONLY occur if the user is already authenticated,
# therefore, only test for session variable. if no session is active, then die.
if (isset($_SESSION['username']) or $noajax) {
   error_log("Performing login $noajax");
   if (!$noajax) {
      $up = sanitize($_SESSION['userpass'], SQL);
      $un = $_SESSION['username'];
      $listobject->querystring = "select * from users where username = '$un' and userpass = '$up'";
      $listobject->performQuery();
      #error_log("$listobject->querystring ; <br>");
      if (count($listobject->queryrecords) > 0) {
         # stash user info
         $userinfo = $listobject->queryrecords[0];
         $userid = $listobject->getRecordValue(1,'userid');
         $usertype = $listobject->getRecordValue(1,'usertype');
         $defaultgroupid = $listobject->getRecordValue(1,'groupid');
         $userproject = $listobject->getRecordValue(1,'defaultproject');
         $_SESSION['userid'] = $userid;
         $_SESSION['usertype'] = $usertype;
         $_SESSION['indir'] = $indir . '/users/' . $listobject->getRecordValue(1,'indir');
         $_SESSION['outdir'] = $outdir . '/users/' . $listobject->getRecordValue(1,'outdir');
         $_SESSION['projectid'] = $projectid;

         $loggedin = 1;
         //error_log("Username: $un verified in session " . session_id());
      }
   } else {
      $_SESSION['userid'] = $userid;
      #print("Login Failed.<br>");
      $loggedin = $noajax;
   }
   $listobject->querystring = "select groupid from mapusergroups where userid = $userid ";
   $listobject->performQuery();
   $usergroupids = $defaultgroupid;
   if (count($listobject->queryrecords) > 0) {
      $udel = ',';
      foreach($listobject->queryrecords as $thisrec) {
         $usergroupids .= $udel . $thisrec['groupid'];
      }
   }
} else {
   //error_log("<b>Error: </b> No session authentication.<br>");
}

$defscenarioid = -1;
if (isset($_GET['scenarioid']) ) {
   $scenarioid = $_GET['scenarioid'];
}

if (isset($_POST['scenarioid']) ) {
   $scenarioid = $_POST['scenarioid'];
}
if (!isset($scenarioid)) {
   $scenarioid = $defscenarioid;
}
if ( ($scenarioid == -1) or ($scenarioid == '') ) {
   $scenarioid = $defscenarioid;
}
$_SESSION['scenarioid'] = $scenarioid;

?>
