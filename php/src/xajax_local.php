<?php

function formRowPlus($formValues,$formname, $parentname, $childname, $adname) {
   global $listobject, $adminsetuparray;

   $objResponse = new xajaxResponse();
   $innerHTML = '';
   $nextNumRes = getLastRowID($formValues);
   $maxNum = $nextNumRes['maxNum'];
   $debugstring = $nextNumRes['debugstring'];
   $nextNum = $maxNum + 1;
   //this bit's new.  try using javascript to add the div instead of messing around with addAppend
   $objResponse->create($parentname,'div',"$childname"."[$nextNum]");
   # need to pass this to the admin record, since the base formName will be something generic
   $adminsetuparray[$adname]['table info']['formName'] = $formname;
   $adminsetuparray[$adname]['table info']['parentname'] = $parentname;
   $adminsetuparray[$adname]['table info']['childname'] = $childname;
   #$innerHTML .= "Next number = $nextNum, formName = $formname, admin name: $adname <br>";
   #$innerHTML .= "Debug: $debugstring <br>";
   $showlabels = $adminsetuparray[$adname]['table info']['showlabels'];
   $innerHTML .= showFormVars($listobject,array(),$adminsetuparray[$adname],$showlabels, 1, $debug, 1, 1, 0, -1, $nextNum);
   $objResponse->assign("$childname"."[$nextNum]",'innerHTML', $innerHTML);

   return $objResponse;
}

 function formRowMinus($formValues,$childname) {
    $objResponse = new xajaxResponse();
    #$lastNum = getLastPhoneNum($formValues);
    $removeid = $formValues['xajax_removeitem'];
    $objResponse->remove("$childname"."[$removeid]");
    return $objResponse;
 }
 function getLastRowID($formValues)
 {
    $maxNum = 0;
    $debugstring = '';
    foreach($formValues as $name=>$value)
    {
       /*
       if (strpos($name,'phonenum')===0)
          $maxNum = max($maxNum, ltrim($name,'ehmnopu'));
       */
       if (count($value) > 0) {
          $thismax = max(array_keys($value));
          if ($thismax > $maxNum) {
             $maxNum = $thismax;
          }
       }
       $debugstring .= print_r($name,1) . "<br>" . print_r($value,1) . "<br> Max: $maxNum <br>";
    }
    return array("maxNum"=>$maxNum, "debugstring"=>$debugstring);
 }
  
 //$xajax->debugOn();
if (is_object($xajax)) {
   $xajax->registerFunction("formRowPlus");
   $xajax->registerFunction("formRowMinus");
   $xajax->registerFunction("getLastRowID");
} 
 ?>