<?php

include("./config.local.php");
include("$basedir/lib_local.php");
include("$libpath/file_functions.php");
include("$libpath/db_functions.php");
include("$libpath/lib_hydro.php");
include("$libpath/lib_hydrology.php");
include("$libpath/psql_functions.php");
include("$libpath/HSPFFunctions2.php");
include("./lib_hspf_cbp.php");
include("$libpath/PEAR/Tar.php");
include_once("$libpath/PEAR/Serializer.php");
include_once("$libpath/PEAR/Unserializer.php");

$connstring = "host=$dbip dbname=$dbname user=$dbuser password=$dbpass  port=$cbp_port";
$dbconn = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);

$listobject = new pgsql_QueryObject;
$listobject->connstring = $connstring;
$listobject->ogis_compliant = 1;
$listobject->dbconn = $dbconn;
$listobject->adminsetuparray = $adminsetuparray;


# initilize cbp data connection
$cbp_connstring = "host=$dbip dbname=cbp user=$dbuser password=$dbpass port=$cbp_port";
error_log("Connecting CBP: $cbp_connstring");
$cbp_dbconn = pg_connect($cbp_connstring, PGSQL_CONNECT_FORCE_NEW);
$cbp_listobject = new pgsql_QueryObject;
$cbp_listobject->ogis_compliant = 1;
$cbp_listobject->connstring = $cbp_connstring;
$cbp_listobject->dbconn = $cbp_dbconn;

/* set up UCI Object */

$uciobject = new HSPF_UCIobject;
$uciobject->ucidir = $indir;
$uciobject->uciname = $runname;
$uciobject->listobject = $cbp_listobject;
include("./hspf.defaults.php");
$uciobject->ucitables = $ucitables;
// this is done later after setting ucifile
//$uciobject->init();
$uciobject->debug = 0;
# masslinks loaded in config file
//$uciobject->masslinks = $masslinks;



?>
