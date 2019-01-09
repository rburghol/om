<?php
/*
   File: multiply.common.php

   Example which demonstrates a multiplication using xajax.

   Title: Multiplication Example

   Please see <copyright.inc.php> for a detailed description, copyright
   and license information.
*/

/*
   Section: Files

   - <multiply.php>
   - <multiply.common.php>
   - <multiply.server.php>
*/

/*
   @package xajax
   @version $Id: multiply.common.php 362 2007-05-29 15:32:24Z calltoconstruct $
   @copyright Copyright (c) 2005-2006 by Jared White & J. Max Wilson
   @license http://www.xajaxproject.org/bsd_license.txt BSD License
*/

#$libpath = "C:/Program Files/Apache Group/Apache2/htdocs/lib";

$xajaxscript = "xajax_watersupply.php";
include_once ("xajax_config.php");

$xajax->registerFunction("showFlowZoneForm");
$xajax->registerFunction("showFlowZoneResult");
$xajax->registerFunction("showPrecipTrends");
$xajax->registerFunction("showPrecipTrendsResult");
$xajax->registerFunction("showHSI");
$xajax->registerFunction("showCreateFlowForm");
$xajax->registerFunction("doCreateFlow");
$xajax->registerFunction("showWithdrawalForm");
$xajax->registerFunction("showWithdrawalResult");
$xajax->registerFunction("showWithdrawalInfoForm");
$xajax->registerFunction("showWithdrawalInfoResult");
$xajax->registerFunction("showDroughtIndicatorForm");
$xajax->registerFunction("showDroughtIndicatorResult");
$xajax->registerFunction("showPlanningForm");
$xajax->registerFunction("showMeasuringPointForm");
$xajax->registerFunction("showVWUDSForm");
$xajax->registerFunction("showAnnualReportingForm");
$xajax->registerFunction("showAnnualReportingFormResult");
$xajax->registerFunction("showAnnualDataCreationForm");
$xajax->registerFunction("showAnnualDataCreationResult");


?>