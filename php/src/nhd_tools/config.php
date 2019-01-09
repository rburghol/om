<?php

//error_reporting(E_ALL);
error_reporting(E_ERROR);
include_once('./config.local.php');

include("$libdir/lib_gzip.php");
include("$libdir/psql_functions.php");
include("$libdir/lib_odbc.php");
include("$libdir/PEAR/Tar.php");
include_once("$libpath/PEAR/Serializer.php");
include_once("$libpath/PEAR/Unserializer.php");
# usgs/noaa data retrieval functions
include("$libdir/lib_usgs.php");
include("$libdir/lib_noaa.php");
# local modeling object
include("$libdir/lib_hydrology.php");

include("$libdir/module_gmap.php");
include("$libdir/data_functions.php");
include("$libdir/db_functions.php");
include("$libdir/file_functions.php");
include("$libdir/lib_gis.php");
include("$libdir/lib_nhdplus.php");
include("$libdir/misc_functions.php");
include("$libdir/feedcreator/feedcreator.class.php");
include("$libdir/module_activemap.php");
# security related libraries
include_once("$libpath/sanitize.inc.php");

# local libraries
include("$basedir/lib_local.php");
include("$basedir/lib_batchmodel.php");
include("$basedir/lib_verify.php");
# db object formatting information
//error_reporting(E_NONE);
include("$basedir/adminsetup.php");
//error_reporting(E_ALL);
include_once("$basedir/lib_wooomm.php");

$scriptname = $_SERVER['PHP_SELF'];
# location of the graphics library - jpgraph, and directories that it uses
$glibdir = "$libdir/jpgraph/";
$goutdir = "$httppath/tmp/";
$goutpath = "/tmp";
$gouturl = "/tmp";
$outdir = "$basedir/data";
#include("$libdir/lib_plot.php");

//require_once("$libdir/magpierss/rss_fetch.inc");

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

$session_connstring = "host=$session_dbip dbname=$session_dbname user=$session_dbuser password=$session_dbpass";
$session_dbconn = pg_connect($session_connstring, PGSQL_CONNECT_FORCE_NEW);
$session_db = new pgsql_QueryObject;
$session_db->connstring = $session_connstring;
$session_db->ogis_compliant = 1;
$session_db->dbconn = $session_dbconn;
$session_db->adminsetuparray = $adminsetuparray;


$connstring = "host=$dbip dbname=va_hydro user=$dbuser password=$dbpass";
$dbconn = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);

$usgsdb = new pgsql_QueryObject;
$usgsdb->connstring = $connstring;
$usgsdb->ogis_compliant = 1;
$usgsdb->dbconn = $dbconn;
$usgsdb->adminsetuparray = $adminsetuparray;

# simulation timer
$timer = new timerObject;

?>
