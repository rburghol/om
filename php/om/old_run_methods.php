<?php

/* ********************************************
*
* Old run method codes
* 
***********************************************
*/



function runCumulativeWSP ($prop_elid, $runid, $cache_runid, $startdate='', $enddate='', $cache_level = -1) {
   // we want to cache any tributaries coming into this
   $cache_types = array('cova_upstream');
   $cached_custom = array();
   $basic_info = unserializeSingleModelObject($prop_elid);
   $info_obj = $basic_info['object'];
   if (is_object($info_obj)) {
      if (isset($info_obj->processors['locid']) and !$standalone) {
         $parentid = preg_replace("/[^0-9]/", "", $info_obj->processors['locid']->value);
         //error_log("Found Location ID - $parentid.\n");
         if ( (trim($parentid) == '') or (trim($parentid) == -1) ) {
            $parentid = $prop_elid;
         }
      } else {
         $parentid = $prop_elid;
         $output['error'] .= "Location ID (locid) is not specified - running as standalone\n";
         //error_log("Location ID (locid) is not specified - running as standalone\n");
         //return $output;
      }
   }
   $found_types = getChildrenCustomTypes($listobject, $parentid, $cache_types);
   foreach ($found_types as $td) {
      $cached_custom[$td] = $runid;
   }
   runCOVAProposedWithdrawal ($prop_elid, $runid, $cache_runid, $startdate='', $enddate='', $cache_level = -1, $standalone=0, $cached_custom);
}

function runCOVAProposedWithdrawal ($prop_elid, $runid, $cache_runid, $startdate='', $enddate='', $cache_level = -1, $standalone=0, $cached_custom = array()) {
   global $listobject, $unserobjects;
   error_log("runCOVAProposedWithdrawal($prop_elid, $runid, $cache_runid, $startdate, $enddate, $cache_level, $standalone) called.\n");
   // get element properties, such as the COVA container link, and then remove the object from the unserobjects cache
   // $cached_custom = list of elements for which a separate cache_runid is requested
   //     key => value: key = elementid, value = runid to use
   $basic_info = unserializeSingleModelObject($prop_elid);
   $info_obj = $basic_info['object'];
   if (is_object($info_obj)) {
      if (isset($info_obj->processors['locid']) and !$standalone) {
         $parentid = preg_replace("/[^0-9]/", "", $info_obj->processors['locid']->value);
         //error_log("Found Location ID - $parentid.\n");
         if ( (trim($parentid) == '') or (trim($parentid) == -1) ) {
            $parentid = $prop_elid;
         }
      } else {
         $parentid = $prop_elid;
         $output['error'] .= "Location ID (locid) is not specified - running as standalone\n";
         //error_log("Location ID (locid) is not specified - running as standalone\n");
         //return $output;
      }
      $dynamics = array();
      if ($parentid <> $prop_elid) {
         $dynamics[] = array('parentid'=>$parentid, 'childid'=>$prop_elid);
      }
      // cached_list should be composed of the following types of objects:
      // find upstream objects, if a version of the runid exists here, load this from the runid, otherwise put it in the cache list to load from the cache_runid
      $cache_types = array('cova_pswd', 'cova_runoff', 'cova_meteorology', 'cova_lupop', 'cova_tribs', 'cova_upstream');
      // use this list to do the cache swap of cache_runid for runid on upstream tribs
      //$cache_types = array('cova_pswd', 'cova_meteorology', 'cova_lupop', 'cova_tribs', 'cova_upstream');
      // use this list to NOT do the cache swap of cache_runid for runid on upstream tribs
      //$cache_types = array('cova_pswd', 'cova_meteorology', 'cova_lupop', 'cova_tribs');
      $cache_list = array();
      $prelim_cache_list = getChildrenCustomTypes($listobject, $parentid, $cache_types);
      foreach (array_keys($cached_custom) as $cid) {
         $prelim_cache_list[] = $cid;
      }
      //error_log("Initial Cache List: " . print_r($prelim_cache_list,1));
      // upstream tribs may already have a run for this requested RUNID, if so, we do not want to use the cached runid as surrogate
      // how should we handle this?
         // check on caching status of this object
      $cache_assoc = array();
      foreach ($prelim_cache_list as $thisel) {
         $order = getElementOrder($listobject, $thisel);
         $cache_file_exists = 0;
         // new cache check sub-routine
         if (isset($cached_custom[$thisel])) {
            $crid = $cached_custom[$thisel];
         } else {
            $crid = $cache_runid;
         }
         $cache_res = checkObjectCacheStatus($listobject, $thisel, $order, $cache_level, $crid, 1, $startdate, $enddate);
         $cache_type = $cache_res['cache_type'];
         $cache_file_exists = $cache_res['cache_file_exists'];
         $cacheable = $cache_res['cacheable'];
         // if this object has pass-through caching set, allow it if our cache level is > 0
         // if obhect is pass-through, do not allow it to be cached, let the unserialize routine decide if it is OK to
         // load its children from cache
         // state:
         //   object HAS a previous run with the runid
         //      - use the data from the runid if the object is cacheable (should be determined above by checkObjectCacheStatus)
         //      - If object IS NOT CACHEABLE, run it anew
         //   object DOES NOT HAVE a previous run with the runid
         //      - If object HAS a previous run with the cache_id, and is allowed to cache, load the cache_id run
         //      - If object DOES NOT HAVE a previous run with cache_id, run it anew
         if (!($cacheable >= 2) or ($cache_type == 'disabled')) {
            $cache_list[] = $thisel;
            $cache_assoc[] = array('runid'=>$crid, 'elementid'=>$thisel);
         } else {
            //error_log("Found cached run ID $cache_runid for element $thisel - will not force cache from run ID $runid ");
         }
      }
      
      //error_log("Cache List: " . print_r($cache_list,1));
      //error_log("Explicit Cache List: " . print_r($cache_assoc,1));
      //error_log("Dynamics: " . print_r($dynamics,1));
      $cache_list = $cache_assoc;
      // set input props from project run template
      $input_props = array();
      $input_props['flow_mode'] = $info_obj->flow_mode;
      
      // unset so that they are run live
      unset($info_obj);
      //error_log("Before unsetting: " . print_r(array_keys($unserobjects),1));
      unset($unserobjects[$prop_elid]);
      //error_log("After unsetting: " . print_r(array_keys($unserobjects),1));
      runCached($parentid, $runid, $cache_runid, $startdate, $enddate, $cache_list, $cache_level, $dynamics, $input_props);
      summarizeRun($listobject, $parentid, $runid, $startdate, $enddate, 0, 0);
   } else {
      error_log("Failed to instantiate the base object.\n");
   }
}


function testCiaWatershed ($prop_elid, $runid, $cache_runid, $startdate='', $enddate='', $cache_level = -1, $standalone=0, $cached_custom = array(), $test_only, $include_other_contained = 1, $contained_scenarioid = -1) {
   global $listobject, $unserobjects, $serverip;
   // The element indicated in prop_elid can be viewed in 1 of 2 ways: 
   // 1. as a Container  (standalone == true OR standalone == false and locid is set
   // 2. As a Control Point (standalone == false and locid is set)
   // ** This is indicated by the standalone function parameter and/or the locid suibcomp as indicated above
   // ** Other control points can be included based on the setting "include_other_contained"
   // The model can be run in 4 ways:
   // 1 - as a "control point", (locid is set AND w/standalone = false), include_other_contained = true, INCLUDING any other control points in its scenario
   // 2 - as a "control point", (locid is set AND w/standalone = false), include_other_contained = false, only the parent and this point
   // 3 - as a "container", (locid is NOT set OR w/standalone = true)  and NOT including any other control points in its scenario
   // 4 - as a "standalone", (locid is NOT set OR w/standalone = true), include_other_contained = false, NOT including other control points in scenario
   // The scenario to search for other control points is EITHER the scenarioid of the element indicated by prop_elid OR contained_scenarioid
   error_log("runCiaWatershed($prop_elid, $runid, $cache_runid, $startdate, $enddate, $cache_level, $standalone, cached_custom = " . print_r($cached_custom,1) .", test_only = $test_only, include_other_contained = $include_other_contained, contained_scenarioid = $contained_scenarioid ) called @ " . date('r'));
   // get element properties, such as the COVA container link, and then remove the object from the unserobjects cache
   // $cached_custom = list of elements for which a separate cache_runid is requested
   //     key => value: key = elementid, value = runid to use
   $basic_info = unserializeSingleModelObject($prop_elid);
   $prop_elem_info = getElementInfo($listobject, $prop_elid);
   if ($contained_scenarioid <> -1) {
      $prop_elem_info['scenarioid'] = $contained_scenarioid;
   }
   $info_obj = $basic_info['object'];
   if (is_object($info_obj)) {
      if (isset($info_obj->processors['locid']) and !$standalone) {
         $parentid = preg_replace("/[^0-9]/", "", $info_obj->processors['locid']->value);
         error_log("Found Location ID - $parentid.\n");
         if ( (trim($parentid) == '') or (trim($parentid) == -1) ) {
            $parentid = $prop_elid;
         }
      } else {
         $parentid = $prop_elid;
         $output['error'] .= "Location ID (locid) is not specified - running as standalone\n";
         //error_log("Location ID (locid) is not specified - running as standalone\n");
         //return $output;
      }
      $el_info = getElementInfo($listobject, $parentid);
      $en = $el_info['elemname'];
      error_log("Initiating Model Run for Elementid $en ($parentid) with runCIA() function");
      $dynamics = array();
      if ($parentid <> $prop_elid) {
         $dynamics[] = array('parentid'=>$parentid, 'childid'=>$prop_elid);
      }
      //error_log("Include contained local subwatersheds? $include_other_contained ");
      // if run asks to include_other_contained = 1
      if ($include_other_contained == 1) {
         // look for other elements in this scenario that are ALSO within the bounds of this parent object
         // also look for things contained by local tributaries, and do them first so as not to have duplicates
         // ********* GET THE TRIBS FIRST *********//
         // two types of tribs -
         // 1 - those in the tribs folder, 
         // 2 - those that are at large in the parent
         $trib_container = getCOVATribs($listobject, $parentid);
         $upstream_container = getCOVAUpstream($listobject, $parentid);
         $trib_types = array('cova_ws_subnodal');
         //error_log("Looking for children of types " . print_r($trib_types,1));
         // use this list to do the cache swap of cache_runid for runid on upstream tribs
         //$cache_types = array('cova_pswd', 'cova_runoff', 'cova_meteorology', 'cova_lupop', 'cova_tribs', 'cova_upstream');
         $cache_list = array();
         error_log("Looking in $trib_container for children of types " . print_r($trib_types,1));
         $allcontainers = getChildrenCustomTypes($listobject, $trib_container, $trib_types);
         // at-large containers should be dealt with exhaustively, since they are not 
         // in the normal vahydro hierarchy they should be searched nested 
         //$at_large_containers = getChildrenCustomTypes($listobject, $parentid, $trib_types);
         error_log("Looking for at-large children of types " . print_r($trib_types,1));
         $alc = getNestedContainersCriteria ($listobject, $parentid, array(), $trib_types, array(), array($upstream_container, $trib_container));
         error_log("At large search returned " . print_r($alc,1));
         $at_large_containers = extract_arrayvalue($alc, 'elementid');
         error_log("At large elids " . print_r($at_large_containers,1));
         $use_spatial = 1;
         $params = array();
         $params['scenarioid'] = $prop_elem_info['scenarioid'];
         $params['custom1'] = 'cova_fe_project';
         // ********* THEN ADD THE PARENT TO THE LIST *********//
         $allcontainers[] = $parentid;
         $allcontainers = array_merge($allcontainers, $at_large_containers);
         error_log("Searching containers " . print_r($allcontainers,1));
         // ********* NOW SEARCH FOR OTHER CONTROL POINTS IN THIS MODEL DOMAIN *********//
         foreach ($allcontainers as $this_container) {
            $el_info = getElementInfo($listobject, $this_container);
            $en = $el_info['elemname'];
            error_log("Handling element $en ($this_container) ");
            error_log("Calling getSpatiallyContainedObjects($this_container, " . print_r($params,1) . ", $use_spatial)");
            $contained = getSpatiallyContainedObjects($this_container, $params, $use_spatial);
            error_log("Local Trib $this_container contains the following control points: " . print_r($contained,1));
            foreach ($contained['elements'] as $thisel) {
               $dynamics[] = array('parentid'=>$this_container, 'childid'=>$thisel['elementid']);
            }
         }
      }
      return;
      // cached_list should be composed of the following types of objects:
      // find upstream objects, if a version of the runid exists here, load this from the runid, otherwise put it in the cache list to load from the cache_runid
      //$cache_types = array('cova_pswd', 'cova_runoff', 'cova_meteorology', 'cova_lupop', 'cova_upstream');
      // don't use upstream - these should be loaded from the actual runid if they exist.
      $cache_types = array('cova_pswd', 'cova_runoff', 'cova_meteorology', 'cova_lupop');
      // use this list to do the cache swap of cache_runid for runid on upstream tribs
      //$cache_types = array('cova_pswd', 'cova_runoff', 'cova_meteorology', 'cova_lupop', 'cova_tribs', 'cova_upstream');
      $cache_list = array();
      $prelim_cache_list = getChildrenCustomTypes($listobject, $parentid, $cache_types);
      foreach (array_keys($cached_custom) as $cid) {
         $prelim_cache_list[] = $cid;
      }
      //error_log("Initial Cache List: " . print_r($prelim_cache_list,1));
      // upstream tribs may already have a run for this requested RUNID, if so, we do not want to use the cached runid as surrogate
      // how should we handle this?
         // check on caching status of this object
      $cache_assoc = array();
      foreach ($prelim_cache_list as $thisel) {
         $order = getElementOrder($listobject, $thisel);
         $cache_file_exists = 0;
         // new cache check sub-routine
         if (isset($cached_custom[$thisel])) {
            $crid = $cached_custom[$thisel];
         } else {
            $crid = $cache_runid;
         }
         $level = 1; // current "level" for cache  eval, by definition these are at level 0 because they are direct children
         // a cache_level = -1 is cache disabled
         // cache_level = -2 can be used however, to indicate that all should be cached
         $clevel = 0;
         // this just checks to see if the cache file exists, because if it does we want to use it
         $cache_res = checkObjectCacheStatus($listobject, $thisel, $order, $clevel, $crid, $level, $startdate, $enddate);
         $cache_type = $cache_res['cache_type'];
         $cache_file_exists = $cache_res['cache_file_exists'];
         $cacheable = $cache_res['cacheable'];
         //if ( $cache_file_exists and ($cacheable <> 0) ) {
         if ( $cache_file_exists ) {
            $cache_list[] = $thisel;
            $cache_assoc[] = array('runid'=>$crid, 'elementid'=>$thisel);
         } else {
            //error_log("Did not find cached run ID $cache_runid for element $thisel ");
         }
      }
      // now - check on upstream containers that pass thru - if they
      
      error_log("Cache List: " . print_r($cache_list,1));
      error_log("Explicit Cache List: " . print_r($cache_assoc,1));
      error_log("Dynamics: " . print_r($dynamics,1));
      $cache_list = $cache_assoc;
      // set input props from project run template
      $input_props = array();
      $input_props['flow_mode'] = $info_obj->flow_mode;
      //$test_only = 1;
      // unset so that they are run live
      unset($info_obj);
      if ($test_only) {
         error_log("Before unsetting: " . print_r(array_keys($unserobjects),1));
      }
      unset($unserobjects[$prop_elid]);
      if ($test_only) {
         error_log("After unsetting: " . print_r(array_keys($unserobjects),1));
      }
      runCached($parentid, $runid, $cache_runid, $startdate, $enddate, $cache_list, 1, $dynamics, $input_props, $test_only);
      if (!$test_only) {
         summarizeRun($listobject, $parentid, $runid, $startdate, $enddate, 0, 0);
      }
   } else {
      error_log("Failed to instantiate the base object.\n");
   }
}

function runCiaWatershed ($prop_elid, $runid, $cache_runid, $startdate='', $enddate='', $cache_level = -1, $standalone=0, $cached_custom = array(), $test_only, $include_other_contained = 1, $contained_scenarioid = -1) {
   global $listobject, $unserobjects, $serverip;
   // The element indicated in prop_elid can be viewed in 1 of 2 ways: 
   // 1. as a Container  (standalone == true OR standalone == false and locid is set
   // 2. As a Control Point (standalone == false and locid is set)
   // ** This is indicated by the standalone function parameter and/or the locid suibcomp as indicated above
   // ** Other control points can be included based on the setting "include_other_contained"
   // The model can be run in 4 ways:
   // 1 - as a "control point", (locid is set AND w/standalone = false), include_other_contained = true, INCLUDING any other control points in its scenario
   // 2 - as a "control point", (locid is set AND w/standalone = false), include_other_contained = false, only the parent and this point
   // 3 - as a "container", (locid is NOT set OR w/standalone = true)  and NOT including any other control points in its scenario
   // 4 - as a "standalone", (locid is NOT set OR w/standalone = true), include_other_contained = false, NOT including other control points in scenario
   // The scenario to search for other control points is EITHER the scenarioid of the element indicated by prop_elid OR contained_scenarioid
   error_log("runCiaWatershed($prop_elid, $runid, $cache_runid, $startdate, $enddate, $cache_level, $standalone, cached_custom = " . print_r($cached_custom,1) .", test_only = $test_only, include_other_contained = $include_other_contained, contained_scenarioid = $contained_scenarioid ) called @ " . date('r'));
   // get element properties, such as the COVA container link, and then remove the object from the unserobjects cache
   // $cached_custom = list of elements for which a separate cache_runid is requested
   //     key => value: key = elementid, value = runid to use
   $basic_info = unserializeSingleModelObject($prop_elid);
   $prop_elem_info = getElementInfo($listobject, $prop_elid);
   if ($contained_scenarioid <> -1) {
      $prop_elem_info['scenarioid'] = $contained_scenarioid;
   }
   $info_obj = $basic_info['object'];
   if (is_object($info_obj)) {
      if (isset($info_obj->processors['locid']) and !$standalone) {
         $parentid = preg_replace("/[^0-9]/", "", $info_obj->processors['locid']->value);
         error_log("Found Location ID - $parentid.\n");
         if ( (trim($parentid) == '') or (trim($parentid) == -1) ) {
            $parentid = $prop_elid;
         }
      } else {
         $parentid = $prop_elid;
         $output['error'] .= "Location ID (locid) is not specified - running as standalone\n";
         //error_log("Location ID (locid) is not specified - running as standalone\n");
         //return $output;
      }
      setStatus($listobject, $parentid, "Initiating Model Run for Elementid $parentid with runCIA() function", $serverip, 1, $runid, -1, 1);
      $dynamics = array();
      if ($parentid <> $prop_elid) {
         $dynamics[] = array('parentid'=>$parentid, 'childid'=>$prop_elid);
      }
      //error_log("Include contained local subwatersheds? $include_other_contained ");
      // if run asks to include_other_contained = 1
      if ($include_other_contained == 1) {
         // look for other elements in this scenario that are ALSO within the bounds of this parent object
         // also look for things contained by local tributaries, and do them first so as not to have duplicates
         // ********* GET THE TRIBS FIRST *********//
         // two types of tribs -
         // 1 - those in the tribs folder, 
         // 2 - those that are at large in the parent
         $trib_container = getCOVATribs($listobject, $parentid);
         $upstream_container = getCOVAUpstream($listobject, $parentid);
         $trib_types = array('cova_ws_subnodal');
         //error_log("Looking for children of types " . print_r($trib_types,1));
         // use this list to do the cache swap of cache_runid for runid on upstream tribs
         //$cache_types = array('cova_pswd', 'cova_runoff', 'cova_meteorology', 'cova_lupop', 'cova_tribs', 'cova_upstream');
         $cache_list = array();
         $allcontainers = getChildrenCustomTypes($listobject, $trib_container, $trib_types);
         // at-large containers should be dealt with exhaustively, since they are not 
         // in the normal vahydro hierarchy they should be searched nested 
         $alc = getNestedContainersCriteria ($listobject, $parentid, array(), $trib_types, array(), array($upstream_container, $trib_container));
         error_log("At large search returned " . print_r($alc,1));
         $at_large_containers = extract_arrayvalue($alc, 'elementid');
         error_log("At large search returned " . print_r($at_large_containers,1));
         $use_spatial = 1;
         $params = array();
         $params['scenarioid'] = $prop_elem_info['scenarioid'];
         $params['custom1'] = 'cova_fe_project';
         // ********* THEN ADD THE PARENT TO THE LIST *********//
         $allcontainers[] = $parentid;
         $allcontainers = array_merge($allcontainers, $at_large_containers);
         // ********* NOW SEARCH FOR OTHER CONTROL POINTS IN THIS MODEL DOMAIN *********//
         foreach ($allcontainers as $this_container) {
            //error_log("Calling getSpatiallyContainedObjects($this_container, " . print_r($params,1) . ", $use_spatial)");
            $contained = getSpatiallyContainedObjects($this_container, $params, $use_spatial);
            //error_log("Local Trib $this_container contains the following control points: " . print_r($contained,1));
            foreach ($contained['elements'] as $thisel) {
               $dynamics[] = array('parentid'=>$this_container, 'childid'=>$thisel['elementid']);
            }
         }
      }
      //return;
      // cached_list should be composed of the following types of objects:
      // find upstream objects, if a version of the runid exists here, load this from the runid, otherwise put it in the cache list to load from the cache_runid
      //$cache_types = array('cova_pswd', 'cova_runoff', 'cova_meteorology', 'cova_lupop', 'cova_upstream');
      // don't use upstream - these should be loaded from the actual runid if they exist.
      $cache_types = array('cova_pswd', 'cova_runoff', 'cova_meteorology', 'cova_lupop');
      // use this list to do the cache swap of cache_runid for runid on upstream tribs
      //$cache_types = array('cova_pswd', 'cova_runoff', 'cova_meteorology', 'cova_lupop', 'cova_tribs', 'cova_upstream');
      $cache_list = array();
      $prelim_cache_list = getChildrenCustomTypes($listobject, $parentid, $cache_types);
      foreach (array_keys($cached_custom) as $cid) {
         $prelim_cache_list[] = $cid;
      }
      //error_log("Initial Cache List: " . print_r($prelim_cache_list,1));
      // upstream tribs may already have a run for this requested RUNID, if so, we do not want to use the cached runid as surrogate
      // how should we handle this?
         // check on caching status of this object
      $cache_assoc = array();
      foreach ($prelim_cache_list as $thisel) {
         $order = getElementOrder($listobject, $thisel);
         $cache_file_exists = 0;
         // new cache check sub-routine
         if (isset($cached_custom[$thisel])) {
            $crid = $cached_custom[$thisel];
         } else {
            $crid = $cache_runid;
         }
         $level = 1; // current "level" for cache  eval, by definition these are at level 0 because they are direct children
         // a cache_level = -1 is cache disabled
         // cache_level = -2 can be used however, to indicate that all should be cached
         $clevel = 0;
         // this just checks to see if the cache file exists, because if it does we want to use it
         $cache_res = checkObjectCacheStatus($listobject, $thisel, $order, $clevel, $crid, $level, $startdate, $enddate);
         $cache_type = $cache_res['cache_type'];
         $cache_file_exists = $cache_res['cache_file_exists'];
         $cacheable = $cache_res['cacheable'];
         //if ( $cache_file_exists and ($cacheable <> 0) ) {
         if ( $cache_file_exists ) {
            $cache_list[] = $thisel;
            $cache_assoc[] = array('runid'=>$crid, 'elementid'=>$thisel);
         } else {
            //error_log("Did not find cached run ID $cache_runid for element $thisel ");
         }
      }
      // now - check on upstream containers that pass thru - if they
      
      error_log("Cache List: " . print_r($cache_list,1));
      error_log("Explicit Cache List: " . print_r($cache_assoc,1));
      error_log("Dynamics: " . print_r($dynamics,1));
      $cache_list = $cache_assoc;
      // set input props from project run template
      $input_props = array();
      $input_props['flow_mode'] = $info_obj->flow_mode;
      //$test_only = 1;
      // unset so that they are run live
      unset($info_obj);
      if ($test_only) {
         error_log("Before unsetting: " . print_r(array_keys($unserobjects),1));
      }
      unset($unserobjects[$prop_elid]);
      if ($test_only) {
         error_log("After unsetting: " . print_r(array_keys($unserobjects),1));
      }
      runCached($parentid, $runid, $cache_runid, $startdate, $enddate, $cache_list, 1, $dynamics, $input_props, $test_only);
      if (!$test_only) {
         summarizeRun($listobject, $parentid, $runid, $startdate, $enddate, 0, 0);
      }
   } else {
      error_log("Failed to instantiate the base object.\n");
   }
}










?>