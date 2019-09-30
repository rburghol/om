<?php

$userid = 1;
$noajax = 1;
include('./xajax_modeling.element.php');

//include_once('/var/www/html/om/xajax_modeling.element.php');
//include_once('lib_batchmodel.php');

// get outlet for terminal CBP segment
// OR, set them up manually as in here


error_reporting(E_ERROR);

if (count($argv) < 2) {
   error_log("Usage: php copy_element.php mode[silent/verbose] dest_scenarioid elementid [dest_parent (-1)] [copychildren=1,0 (no children),-1 (no links at all)] [param_name1=param_val1|param_name2|param_val2,...] \n");
   die;
}

$scenid = $argv[1];
$elementid = $argv[2];
if (isset($argv[3])) {
   $destination = $argv[3];
} else {
   $destination = -1;
}
if (isset($argv[4])) {
   $copychildren = $argv[4];
} else {
   $copychildren = 1;
}
$params = array();
if (isset($argv[5])) {
   $params2set = $argv[5];
   print("Params = " . $params2set . "\n");
   $pairs = explode("|", $params2set);
   
   foreach ($pairs as $thispair) {
      list($param, $val) = explode("=", $thispair);
      $params[] = array('key'=>$param, 'val'=>$val);
   }
}
// place holder for list of sub-comps or props that require a call to reCreate()
$recreate = array();

print("Creating a copy and setting these params: " . print_r($params,1) . "\n");


if ($destination > 0) {
   // get scenarioid from destination parent
   $info = getElementInfo($listobject, $destination, 1);
   $scenid = $info['scenarioid'];
   print("Parent Info: " . print_r($info,1) . "\n");
}
//die;


//$debug = 1;

switch ($copychildren) {
   case 0:
      $cloneresult = cloneModelElement($scenid, $elementid, $destination, 1, 1);
      $newelid = $cloneresult['elementid'];
      $retarr['elementid'] = $newelid;
      if ($destination > 0) {
         $innerHTML .= "New object group $newelid inserted underneath $destination <br>";
         $innerHTML .= createObjectLink($projectid, $scenid, $newelid, $destination, 1);
      }
   break;
   case -1:
      // without any linkages
      $cloneresult = cloneModelElement($scenid, $elementid, $destination, 0, 1);
      $newelid = $cloneresult['elementid'];
      $retarr['elementid'] = $newelid;
      if ($destination > 0) {
         $innerHTML .= "New object group $newelid inserted underneath $destination <br>";
         $innerHTML .= createObjectLink($projectid, $scenid, $newelid, $destination, 1);
      } else {
         error_log($cloneresult['innerHTML'] . "\n");
      }
   break;
      
   case 1:

   $cbp_copy_params = array(
      'projectid'=>$projectid,
      'dest_scenarioid'=>$scenid,
      'elements'=>array($elementid),
      'dest_parent'=>$destination
   );
   $output = copyModelGroupFull($cbp_copy_params, 1);

   error_log(print_r($output,1));
   $newelid = $output['elementid'];
   if ($destination > 0) {
      $oldparentid = getElementContainer($listobject, $elementid);
      error_log("Changing direct links from the old parent to the new parent for the copied element");
      // we should update any links that the copied object had to its old parent to the new parent
      $listobject->querystring = " update map_model_linkages set dest_id = $destination where src_id = $newelid and linktype = 2";
      error_log("$listobject->querystring ; \n");
      $listobject->performQuery();
      $listobject->querystring = " update map_model_linkages set src_id = $destination where src_id = $oldparentid and dest_id = $newelid and linktype = 2 ";
      error_log("$listobject->querystring ; \n");
      $listobject->performQuery();
   }
   break;
}
if ( !($newelid > 0) ) {
   error_log("Error copying elements\n");
} else {
   error_log("New object created with elementid $newelid \n");
   global $unserobjects;
   error_log("Trying to set params: " . print_r($params,1));
   if ( (count($params) > 0) ) {
      //$loadres = unSerializeSingleModelObject($newelid);
      //$thisobject = $loadres['object'];
      $loadres = loadModelElement($newelid, array(), 0);
      $thisobject = $loadres['object'];
      if (is_object($thisobject)) {
         error_log("Object Type: " . get_class($thisobject) . "\n");
         foreach ($params as $thisparam) {
            $key = $thisparam['key'];
            $val = $thisparam['val'];
            error_log("Searching el $newelid for property $key > $val\n");
            if (isset($thisobject->processors[$key])) {
               if (property_exists(get_class($thisobject->processors[$key]), 'recreate_list')) {
                  $rec_list = explode(',', trim($thisobject->processors[$key]->recreate_list));
               } else {
                  $rec_list = array();
               }
               $targs =  explode('~', $val);
               if (count($targs) == 1) {
                  error_log("Sub-comp $key exists, but data must be given in format key=prop|val to edit sub-comp properties\n");
               } else {
                  error_log("Updating $elemname ($newelid) $subcomp_name -> " . $targs[0] ."  = " . $targs[1] . " \n");
                  $thisobject->processors[$key]->setProp($targs[0], $targs[1]);
                  if ( (count($rec_list) > 0) and ($rec_list[0] <> '') ) {
                     //error_log("$key has rec list: " . print_r($rec_list,1));
                     if (in_array($targs[0], $rec_list)) {
                        //error_log("Found $propname in Recreate List");
                        //error_log("Comparing $propval to " . $thisobject->getProp($propname));
                        if ($propval <> $thisobject->getProp($propname)) {
                           $recreate[$newelid][$key] = 1;
                        }
                     }
                  }
               }
               $thisobject->processors[$key]->wake();
            } else {
              $props = array('name');
              if (in_array($key, $props)) {
                saveModelObject($elementid, $thisobject, array($key => $val), $debug) ;
              } else {
                 error_log("$key not found in processors\n");
                $pparms[$key] = $val;
                if ($debug) {
                   error_log(print_r(array_keys($thisobject->processors),1) . "\n");
                }
              }
            }
         }
         error_log("Saving sub-comps \n");
         foreach (array_keys($recreate[$newelid]) as $thisprop) {
            if (is_object($thisobject->processors[$thisprop])) {
               error_log("Calling reCreate() on sub-comp $thisprop");
               $thisobject->processors[$thisprop]->reCreate();
            }
         }
         saveObjectSubComponents($listobject, $thisobject, $newelid, 1);
      
         error_log("Setting " . print_r($pparms,1) . "\n");
         $res = updateObjectProps($projectid, $newelid, $pparms, $debug);
         error_log("Result: " . print_r($res['innerHTML'],1) . "\n");
      } else { 
         error_log("Object $thiselid is not a valid object \n");
      }
         
   }  
}
?>
