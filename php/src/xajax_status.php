<?php

include_once("xajax_status.common.php");
#require_once ("$libpath/xajax/xajax_core/xajax.inc.php");

#$noajax = 1;

if (!$noajax) {
   $xajax->processRequest();
}
# process info by owner

function showStatus($formValues) {
   $objResponse = new xajaxResponse();
   $statusHTML = '';
   $listobject->querystring = " select * from system_status where ownerid = $userid ";
   $listobject->performQuery();
   $listobject->show = 0;
   $statusHTML .= "Current System Status<br>";
   $statusHTML .= $listobject->showList();
   $objResponse->assign("status_bar","innerHTML",$statusHTML);
   return $objResponse;
}


?>