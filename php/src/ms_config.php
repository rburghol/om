<?php


# Connect to Microsoft SQL Server Database
#$msdbconn = @mssql_connect("GAR-DEV", "nmdatauser", "nmdatauser!@#$");
$msdbconn = @mssql_connect("GAR-DEV", "cbpweb", "cbpwebZAQ!");
$msdbsel = @mssql_select_db("VortexV5");
#print("MSSQL DEBUG: $msdbconn, $msdbsel <br>");
require_once("$libpath/mssql_functions.php");
# Create new mssql query object
$vortexdb = new mssql_QueryObject;
$vortexdb->dbconn = $msdbconn;
$vortexdb->adminsetuparray = $adminsetuparray;


?>