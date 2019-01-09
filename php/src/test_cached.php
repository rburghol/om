<?php

// model run framework to force caching of select components
# set up db connection
#include('config.php');
$noajax = 1;
$projectid = 3;
include_once('xajax_modeling.element.php');
error_log("Remote Run Parameter: $remote_run ");
$startdate = '2000-07-01';
$enddate = '2000-07-31';
$runid = 15;
$cache_runid = 2;
$prop_elid = 257685;
$standalone = 1; // 0 - if this is a dynamic element to add such as a proposed withdrawal, if this is a model container node to run using a list of cached child objects, then use 1
print("Calling runCached()\n");
$cached_custom = array();
$cached_custom[257083] = 12;
//runCOVAProposedWithdrawal ($prop_elid, $runid, $cache_runid, $startdate, $enddate);
runCOVAProposedWithdrawal ($prop_elid, $runid, $cache_runid, $startdate, $enddate, -1, $standalone, $cached_custom);
print("Returned from runCached()\n");
?>
