<?php

# config file
#error_reporting(0);
error_reporting(E_ERROR);
#error_reporting(E_ALL);
$basedir = '/Library/WebServer/Documents/wooommdev/test';
$basepath = '/wooommdev/test';
$libdir = "/Library/WebServer/Documents/lib";
include("$libdir/lib_hydrology.php");
include("$libdir/lib_usgs.php");
include("$libdir/file_functions.php");
include("$libdir/misc_functions.php");
include("$libdir/db_functions.php");
include("$libdir/lib_equation2.php");
# must set glibdir before calling lib_plot.php
$glibdir = "$libdir/jpgraph";
include("$libdir/lib_plot.php");
include("./lib_local.php");

# load and create database object
include("$libdir/psql_functions.php");
$dbconn = pg_connect("host=localhost port=5432 dbname=wsp user=postgres password=314159");
$listobject = new pgsql_QueryObject;
$listobject->dbconn = $dbconn;
$listobject->adminsetuparray = $adminsetuparray;

# project info
$projectid = 2;

# HSPF specific stuff
# get application libraries
include("$libdir/HSPFFunctions.php");
# get local libraries
include("$libdir/hspf.defaults.php");

# set up UCI Object
$compdir = $basedir . "/dirs/proj$projectid/components";
$ucidir = $basedir . "/dirs/proj$projectid/components/comp2";
$outdir = $basedir . "/dirs/proj$projectid/components/comp2/out";
$outpath = $basepath . "/dirs/proj$projectid/components/comp2/out";
$uciobject = new HSPF_UCIobject;
$uciobject->ucidir = $ucidir;
$uciobject->listobject = $listobject;
$uciobject->ucitables = $ucitables;


?>
