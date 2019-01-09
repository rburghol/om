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


$xajaxscript = "medit_analysis.php";
error_log("Loading xajax_config.php");
include_once ("xajax_config.php");
error_log("Loading xajax grid lib");
include_once ("$libpath/adg/xajaxgrid.inc.php");
# includes the status bar routines
error_log("Registering Functions");
$xajax->registerFunction("refreshAnalysisWindow");

?>
