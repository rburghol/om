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

$xajaxscript = "xajax_maintenance.php";
include_once ("xajax_config.php");

$xajax->registerFunction("showUserAddForm");
$xajax->registerFunction("showUserAddResult");
$xajax->registerFunction("showGroupCopyForm");
$xajax->registerFunction("showGroupCopyResult");
$xajax->registerFunction("showChangePasswordForm");
$xajax->registerFunction("showLogins");
$xajax->registerFunction("showCreateDomainForm");
$xajax->registerFunction("showCreateUserGroupForm");
$xajax->registerFunction("showCreateUserGroupResult");
$xajax->registerFunction("showEditUserGroupForm");
$xajax->registerFunction("showEditUserGroupResult");
?>