<?php

$noajax = 1;
$projectid = 3;
$userid = 1;
$scenarioid = 37;
$wd_template_id = 284895;

include_once('xajax_modeling.element.php');
//error_reporting(E_ALL);
##include_once("./lib_batchmodel.php");

if (count($argv) < 2) {
   print("Usage: fn_vahydro2_to_vahydro1_wds.php URL \n");
   die;
}
// Ex 1: tidal Pamunkey segment URL only
//   php fn_vahydro2_to_vahydro1_wds.php http://deq1.bse.vt.edu/d.dh/export-watershed-contains-facility-props/68172/wd_current_mgy
// Ex 2: All Pamunkey segments
//    php fn_vahydro2_to_vahydro1_wds.php http://deq1.bse.vt.edu/d.dh/export-watershed-contains-facility-props/67774,67805,67891,68048,68103,68120,68161,68168,68170,68171,68172,68173,68174,68175,68176,68178/wd_current_mgy 
// Ex 3: segment that has withdrawals without elementid
//    php fn_vahydro2_to_vahydro1_wds.php http://deq1.bse.vt.edu/d.dh/export-watershed-contains-facility-props/67774/wd_current_mgy

$url = $argv[1];
$tsv = readDelimitedFile_fgetcsv($url,"\t", 1, -1);
$cache_file = "./vahydro2_vahydro1_element_cache.tsv";
$cache = readDelimitedFile($cache_file, "\t", TRUE, -1);
$added = array();
foreach ($tsv as $thisline) {
  error_log(print_r($thisline,1));
  $facility_hydrocode = $thisline['facility_hydrocode'];
  $cache_index = array_search($facility_hydrocode, array_column($cache, 'facility_hydrocode'));
  // check for elementid
  $elementid = empty($thisline['elementid']) ? FALSE : $thisline['elementid'];
  if (!$elementid) {
    if (!($cache_index === FALSE)) {
      $fac_cache = $cache[$cache_index];
      error_log("Found cache for elementid of $facility_hydrocode returned index = $cache_index" . print_r($fac_cache,1));
      $elementid = $fac_cache['elementid'];
    }
  }
  $riverseg = str_replace('vahydrosw_wshed_', '', $thisline['watershed_hydrocode']);
  error_log("WD elementid $elementid");
  error_log("River segment $riverseg");
  $elid = getCOVACBPContainer($listobject, $scenarioid, $riverseg);
  error_log("Riverseg elementid = $elid");
  error_log("Model Withdrawal elementid = $elementid");
  
  if (!$elementid) {
    // @todo: see if we can find the withdrawal among the existing ones
    $wds = getCOVAWithdrawals($listobject, $elid);
    error_log("Found wd elements: " . print_r($wds,1));
    foreach ($wds as $thiswd) {
      $loadres = unSerializeSingleModelObject($thiswd['elementid']);
      $wdobject = $loadres['object'];
      if (is_object($wdobject) and ($wdobject->id1 == $thisline['vwuds_userid']) ) {
        $elementid = $thiswd['elementid'];
        error_log("Found matching USERID $wdobject->id1 on $wdobject->name ($elementid)");
        break;
      }
    }
    if (!$elementid) {
      // @todo: insert an element by cloning and renaming our base case
      // see: http://deq1.bse.vt.edu/sifnwiki/index.php/Cova_scenarios_safeyield#New_Withdrawals
      $container = getChildComponentCustom1($listobject, $elid, 'cova_pswd', -1);
      error_log("No model element found for $thisline[facility_name] ");
      error_log("Cloning $wd_template_id and adding to Withdrawal & PS Main Container" . print_r($container,1));
      if (is_array($container)) {
        $cont_info = array_shift($container);
      }
      if (isset($cont_info['elementid'])) {
        // make a clone
        $cloneresult = cloneModelElement($scenarioid, $wd_template_id, $cont_info['elementid'], 1, 1);
        $elementid = $cloneresult['elementid'];
        $added[] = array('elementid'=>$elementid, 'current_mgy' => $thisline['propvalue']);
        // @todo: use REST to set the model property for this withdrawal elementid to avoid adding multiple copies of the same element
        // for now - write to cache tsv (done below)    
      } else {
        error_log("Could not clone $wd_template_id to container $cont_info[elementid] ");
      }
    }
  }
  if ($elementid) {
    if (!$cache_index) {
      $thisline['elementid'] = $elementid;
      $cache[] = $thisline;
    }
    // load the OM object
    $loadres = unSerializeSingleModelObject($elementid);
    $thisobject = $loadres['object'];
    // load json properties from vahydro
    $urlbase = 'http://deq1.bse.vt.edu/d.dh/dh-properties-json/dh_feature';
    $varkeys = array('wd_current_mon_factors');
    $finalurl = implode("/", array($urlbase, $thisline['facility_hydroid'], implode(',', $varkeys)));
    error_log($finalurl);
    $json = file_get_contents($finalurl );
    $json_object = json_decode($json);
    if (is_object($thisobject) and $json_object and property_exists($json_object, 'entity_properties')) {
      $thisobject->name = $thisline['facility_name'];
      // Current Demand
      $subcomp_name = 'current_mgy';
      if (isset($thisobject->processors[$subcomp_name])) {
        $thisobject->processors[$subcomp_name]->equation = $thisline['propvalue'];
      }
      foreach ($json_object->entity_properties as $prop) {
        error_log("Raw Factor Object: " . print_r((array)$prop,1));
        if ($prop->property->varkey == 'wd_current_mon_factors') {
          $factors = json_decode($prop->property->prop_matrix);
          //error_log(print_r($factors->row_1,1));
          $table = (array)$factors->row_1;
          $matrix = array_combine(range(1,12), array_values($table));
          //error_log("Table: " . print_r($matrix,1));
          //@todo: set these matrix - see edit_submatrix.php for example 

          // Monthly Percent distro
          $subcomp_name = 'historic_monthly_pct';
          if (isset($thisobject->processors[$subcomp_name])) {
            print("Editing Matrix $subcomp_name\n ");
            $thisobject->processors[$subcomp_name]->formatMatrix();
            $orig = $thisobject->processors[$subcomp_name]->matrix_formatted;
            print("Original Matrix: " . print_r($orig,1) . "\n");
            foreach (range(1,12) as $ix) {
              $orig[$ix] = $matrix[$ix];
            }
            print("Final Matrix: " . print_r($orig,1) . "\n");
            $thisobject->processors[$subcomp_name]->oneDimArrayToMatrix($orig);
            $msg_html = saveObjectSubComponents($listobject, $thisobject, $elementid, TRUE, FALSE);
            //error_log($msg_html);
            $msg_html = updateObjectPropList($elementid, $thisobject, FALSE);
            //error_log($msg_html);
          } else {
            error_log("Could not find $subcomp_name ");
          }
        }
      }
    }
  } else {
    error_log("Problem adding an elementid for " . print_r($thisline,1));
  }
}
putDelimitedFile($cache_file,$cache,"\t",TRUE,'unix',TRUE);
error_log("Summary of Additions: " . print_r($added,1));
?>