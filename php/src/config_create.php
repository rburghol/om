<?php
# lite-weight config file for use only with accoun creation routine.  

#error_reporting(E_ALL);
#error_reporting(E_ERROR);
error_reporting(E_NONE);

$scriptname = $_SERVER['PHP_SELF'];

include_once('./config.local.php');

$indir = "$basedir/in";
$compdir = "$basedir/dirs/proj$projectid/components";
$outdir = "$basedir/dirs/proj$projectid/out";
$outurl = "$baseurl/dirs/proj$projectid/out";
$ucidir = "$httppath/uci/";
$glibdir = "$libpath/jpgraph";
$goutdir = "$httproot/tmp/";
$goutpath = "$httproot/tmp";
# location of the graphics library - jpgraph
$glibdir = "$libpath/jpgraph";
include_once("$libpath/module_activemap.php");

# security related libraries
include_once("$libpath/sanitize.inc.php");

# get database and file libraries
include_once("$libpath/psql_functions.php");
include_once("$libpath/file_functions.php");

if ($debug) {
   error_log("Loading Misc Libraries");
}
include_once("$libpath/misc_functions.php");
include_once("$libpath/db_functions.php");

if ($debug) {
   error_log("Connecting to Database Object");
}
$connstring = "host=$dbip dbname=$dbname user=$dbuser password=$dbpass";
$dbconn = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);
#$dbconn = pg_connect($connstring);

$listobject = new pgsql_QueryObject;
$listobject->connstring = $connstring;
$listobject->dbconn = $dbconn;
$listobject->adminsetuparray = array();


###############################################################
###################       Misc. Settings      #################
###############################################################
# row colors for formatted printouts
$rc[0] = 'white';
$rc[1] = 'grey';

$defutype = 2;
$defuproj = 3;
$defgid = 2;

?>
