<?php
//error_reporting(E_ERROR);
//error_reporting(E_ALL);
include_once('./config.local.php');
include_once("$libdir/lib_gzip.php");
include_once("$libdir/psql_functions.php");
include_once("$libdir/lib_odbc.php");
include_once("$libdir/PEAR/Tar.php");
include_once("$libpath/PEAR/Serializer.php");
include_once("$libpath/PEAR/Unserializer.php");
# usgs/noaa data retrieval functions
include_once("$libdir/lib_usgs.php");
include_once("$libdir/lib_noaa.php");
# local modeling object
include_once("$libdir/lib_hydrology.php");
include_once("$libdir/lib_equation2.php");
include_once("$libdir/lib_plot.php");

include_once("$libdir/module_gmap.php");
include_once("$libdir/data_functions.php");
include_once("$libdir/db_functions.php");
include_once("$libdir/file_functions.php");
include_once("$libdir/lib_gis.php");
include_once("$libdir/misc_functions.php");
include_once("$libdir/feedcreator/feedcreator.class.php");
include_once("$libdir/module_activemap.php");
include_once("$libdir/sanitize.inc.php");
include_once("$libdir/lib_batchmodel.php");

# local libraries
include_once('./lib_cova_summary.php');
include_once("$basedir/lib_local.php");
include_once("$basedir/lib_admin.php");
include_once("$basedir/lib_verify.php");
# db object formatting information
//error_reporting(E_NONE);
include_once("$basedir/adminsetup.php");
//error_reporting(E_ALL);
include_once("$libdir/lib_wooomm.php");
include_once("$libdir/lib_wooomm.USGS.php");
include_once("$libdir/lib_wooomm.wsp.php");
if (!$noajax) {
   include_once("$basedir/lib_analysisGrid.php");
}

$scriptname = $_SERVER['PHP_SELF'];
# location of the graphics library - jpgraph, and directories that it uses
$glibdir = "$libdir/jpgraph/";
$goutdir = "$httppath/tmp/";
$goutpath = "/tmp";
$gouturl = "/tmp";
$outdir = "$basedir/data";
#include_once("$libdir/lib_plot.php");

require_once("$libdir/magpierss/rss_fetch.inc");

#$dbconn = pg_connect("host=localhost port=5432 dbname=wsp user=wsp_ro password=q_only");
$dbconn = pg_connect("host=$dbip port=5432 dbname=$dbname user=$dbuser password=$dbpass");
$listobject = new pgsql_QueryObject;
$listobject->dbconn = $dbconn;
#$listobject->adminsetuparray = $adminsetuparray;

#$dbconn = pg_connect("host=localhost port=5432 dbname=wsp user=wsp_ro password=q_only");
$vwuds_dbconn = pg_connect("host=$vwuds_dbip port=5432 dbname=$vwuds_dbname user=$vwuds_dbuser password=$vwuds_dbpass");
$vwuds_listobject = new pgsql_QueryObject;
$vwuds_listobject->dbconn = $vwuds_dbconn;
#$listobject->adminsetuparray = $adminsetuparray;

#$dbconn = pg_connect("host=localhost port=5432 dbname=vpdes user=$dbuser password=$dbpass");
$vpdes_dbconn = pg_connect("host=$vwuds_dbip port=5432 dbname=vpdes user=$dbuser password=$dbpass");
$vpdes_listobject = new pgsql_QueryObject;
$vpdes_listobject->dbconn = $vpdes_dbconn;

$session_connstring = "host=$session_dbip dbname=$session_dbname user=$session_dbuser password=$session_dbpass";
$session_dbconn = pg_connect($session_connstring, PGSQL_CONNECT_FORCE_NEW);
$session_db = new pgsql_QueryObject;
$session_db->connstring = $session_connstring;
$session_db->ogis_compliant = 1;
$session_db->dbconn = $session_dbconn;
$session_db->adminsetuparray = $adminsetuparray;

#$dbconn = pg_connect("host=localhost port=5432 dbname=wsp user=wsp_ro password=q_only");
$aquatic_biodb_dbconn = pg_connect("host=$vwuds_dbip port=5432 dbname='aquatic_bio' user=$dbuser password=$dbpass");
$aquatic_biodb = new pgsql_QueryObject;
$aquatic_biodb->dbconn = $aquatic_biodb_dbconn;
#$listobject->adminsetuparray = $adminsetuparray;

# simulation timer
$timer = new timerObject;

?>
