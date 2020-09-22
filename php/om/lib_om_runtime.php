<?php
// functions that support the use of the OM model 
//error_log("Loading");

function runModelBackground($formValues) {
   global $basedir, $php_exe, $scenarioid, $serverip;
   $objResponse = new xajaxResponse();
   $controlHTML = '';
   $arrOutput = array();
   $controlHTML = forkRun($formValues);
   $status = "<b>Model Run Requested ... contacting server.<br>" . $controlHTML;
   $objResponse->assign("status_bar","innerHTML",$status);
   $objResponse->assign("commandresult","innerHTML",$controlHTML);
   return $objResponse;
}

function forkRun($formValues) {
   global $listobject, $basedir, $php_exe, $scenarioid, $serverip;
   $controlHTML = '';
   error_log("Function forkRun() Called " );
   error_log("Model Run request: " . print_r($formValues, 1));
   if (isset($formValues['elements'])) {
      $elementid = $formValues['elements'];
      $runid = $formValues['runid'];
      $running = array(1,2);
      $status_update = verifyRunStatus($listobject, $elementid);
      $controlHTML .= print_r($status_update,1) . "<br>\n";
      $recent_status = $status_update['status_flag'];
      if (in_array($recent_status, $running)) {
         // this is already running
         error_log( "Element $elementid is already running.  Will not fork a duplicate run.  Returning'");
         error_log( print_r($status_update,1) );
         return "Element $elementid is already running.  Will not fork a duplicate run.  Returning'";
      }
      if (!empty($formValues['cache_runid'])) {
         $cache_runid = $formValues['cache_runid'];
      } else {
         $cache_runid = -1;
      }
      if (isset($formValues['runtype'])) {
         // if this is sdet we will use the new version of the model run routine
         $runtype = $formValues['runtype'];
      } else {
         $runtype = 'oldschool';
      }
      $startdate = '""';
      if (isset($formValues['startdate'])) {
         $startdate = $formValues['startdate'];
      } 
      $enddate= '""';
      if (isset($formValues['enddate'])) {
         $enddate = $formValues['enddate'];
      } 
      if (strlen(trim($runid)) == 0) {
         $runid = -1;
      }
      if (isset($formValues['cache_level'])) {
         $cache_level = $formValues['cache_level'];
      } else {
         $cache_level = -1; // -1 means do not use cached runs
      }
      if (isset($formValues['test_only'])) {
         $test_only = $formValues['test_only'];
      } else {
         $test_only = 0; // -1 means do not use cached runs
      }
      if (isset($formValues['scenarioid'])) {
         $scenarioid = $formValues['scenarioid'];
      } else {
         $scenarioid = -1; // -1 means do not use cached runs
      }
      if (isset($formValues['cache_list'])) {
         $cache_list = join(',',$formValues['cache_list']);
      } else {
         $cache_list = ''; // -1 means do not use cached runs
      }
      switch ($runtype) {
         case 'oldschool':
            // old school routine
            //$command = "$php_exe -f $basedir/test_modelrun.php $elementid $startdate $enddate $runid $cache_level";
            // standard run using new school routine
            $command = "$php_exe -f $basedir/run_model.php $elementid $runid normal $startdate $enddate $cache_runid \"$cache_list\" $cache_level";
            $controlHTML .= "Spawning process for $elementid <br>";
            error_log("Spawning process for $elementid with runtype = $runtype");
            error_log("$command > /dev/null &");
            setStatus($listobject, $elementid, "Model Run for Element $elementid Forked", $serverip, 1, $runid, -1, 1);
            error_log("Model Run for Element $elementid Forked with command: $command");
            $forkout = exec( "$command > /dev/null &", $arrOutput );
         break;
         case 'newschool':
            $command = "$php_exe -f $basedir/run_model.php $elementid $runid $runtype $startdate $enddate $cache_runid \"$cache_list\" $cache_level $test_only $scenarioid ";
            $controlHTML .= "Spawning process for $elementid <br>";
            error_log("Spawning process for $elementid with runtype = $runtype");
            error_log("$command > /dev/null &");
            setStatus($listobject, $elementid, "Model Run for Element $elementid Forked", $serverip, 1, $runid, -1, 1);
            $forkout = exec( "$command > /dev/null &", $arrOutput );
         break;
         
         case 'asynch':
            $pid=pcntl_fork();
            if($pid==0)
            {
               posix_setsid();
               pcntl_exec($cmd,$args,$_ENV);
               // child becomes the standalone detached process
             }
             $command = "Forking a pcntl child - in apache module";
             // parent's stuff
             //exit();
         break;
         
         default:
            $command = "$php_exe -f $basedir/run_model.php $elementid $runid $runtype $startdate $enddate $cache_runid \"$cache_list\" $cache_level $test_only $scenarioid ";
            $controlHTML .= "Spawning process for $elementid <br>";
            error_log("Spawning process for $elementid with runtype = $runtype");
            error_log("$command > /dev/null &");
            setStatus($listobject, $elementid, "Model Run for Element $elementid Forked", $serverip, 1, $runid);
            $forkout = exec( "$command > /dev/null &", $arrOutput );
         break;
      }
      $controlHTML .= $command;
      //$forkout = launchBackgroundProcess($command);
      //$forkout = pclose(popen("start /b $command ", "r"));
      $controlHTML .= "Command result: " . $forkout;
      //$controlHTML .= print_r($arrOutput,1) . "<br>";
   } else {
      $controlHTML .= "Could not run element ID undefined .<br>";
      error_log("Could not run element ID undefined .");
   }
   
   return $controlHTML;
}


function getRunFile($listobject, $elementid, $runid, $debug = 0) {
   $listobject->querystring = "  select a.elementid, a.elemname, b.output_file, b.run_date, b.starttime, b.endtime, b.run_summary, b.run_verified, b.remote_url, b.host from scen_model_element as a, scen_model_run_elements as b ";
   $listobject->querystring .= " where b.elementid = $elementid and a.elementid = b.elementid and b.runid = $runid ";
   if ($debug) {
      error_log("$listobject->querystring\n");
   }
   
   $listobject->performQuery();
   if (count($listobject->queryrecords)) {
      return $listobject->queryrecords[0];
   } else {
      return FALSE;
   }
}

function checkRunDate($listobject, $elementid, $runid, $rundate, $startdate = '', $enddate = '', $debug = 0) {
   $listobject->querystring = "  select count(*) as numruns from scen_model_run_elements ";
   $listobject->querystring .= " where runid = $runid ";
   $listobject->querystring .= " and elementid = $elementid ";
   $listobject->querystring .= " and run_date >= '$rundate' ";
   if ($startdate <> '') {
      $listobject->querystring .= " and starttime <= '$startdate' ";
   }
   if ($enddate <> '') {
      $listobject->querystring .= " and endtime >= '$enddate' ";
   }
   if ($debug) {
      error_log("$listobject->querystring \n");
   }
   $listobject->performQuery();
   $runs = $listobject->getRecordValue(1,'numruns');
   if ($runs == 0) {
      return 0;
   } else {
      return 1;
   }
}

function verifyRunStatus($listobject, $elementid, $qrunid = '', $qhost = '', $timeout = 1800) {
   // run status_flags -1 - run failed/zombied, 0 - not running/run completed successfully, 1 - running, 2 - finishing
   $return_vals = array();
   $status_flag = '';
   $status_mesg = '';
   $interval = '';
   $elemname = '';
   
   $listobject->querystring = "  select a.status_flag, a.status_mesg, a.last_updated, b.elemname, ";
   $listobject->querystring .= "    now() as thistime, host, runid ";
   $listobject->querystring .= " from system_status as a, scen_model_element as b ";
   $listobject->querystring .= " where a.element_name = 'model_run' ";
   $listobject->querystring .= "    and a.element_key = $elementid  ";
   $listobject->querystring .= "    and a.element_key = b.elementid ";
   if ( ($qhost <> '') and ($qrunid == '') ) {
      $listobject->querystring .= "    and a.host = '$qhost' ";
   }
   if ($qrunid == '') {
      // we are looking for any activity at all
      $listobject->querystring .= "    and status_flag > 0 ";
   } else {
      // we are checking on a specific run
      if ( ($qhost <> '')) {
         $listobject->querystring .= "    and a.host = '$qhost' ";
         $listobject->querystring .= "    and a.runid = '$qrunid' ";
      } else {
         $listobject->querystring .= "    and a.runid = '$qrunid' ";
      }
   }
   //print("$listobject->querystring <br>");
   $return_vals['query'] = "VerifyRunStatus SQL:" . $listobject->querystring;
   $listobject->performQuery();
   $return_vals['error'] = "DB Error:" . $listobject->error;
   if ($listobject->numrows > 0) {
      $last_updated = $listobject->getRecordValue(1,'last_updated');
      $thistime = $listobject->getRecordValue(1,'thistime');
      $status_flag = $listobject->getRecordValue(1,'status_flag');
      $status_mesg = $listobject->getRecordValue(1,'status_mesg');
      $runid = $listobject->getRecordValue(1,'runid');
      $host = $listobject->getRecordValue(1,'host');
      $elemname = $listobject->getRecordValue(1,'elemname');
      $last_secs = intval(date('U', strtotime($last_updated)));
      $current_secs = intval(date('U', strtotime($thistime)));
      $interval = $current_secs - $last_secs;
      // runs that have not updated within the timeout that are NOT either
      // 0 - finished, or 3 - queued but waiting, are considered to be zombied
      if ( ($interval >= $timeout) and !(in_array($status_flag,array( 0, 3)) ) ) {
         // this indicates that run is zombied/failed
         $status_flag = -1;
      }
      // this checks to see if a model on another remote machine is running a DIFFERENT runid, which is OK
      // this only will fire if ALL information has been provided to this routine (host and runid), 
      // otherwise, it assumes that for safety sake we do NOT allow multiple runs
      if ( in_array($status_flag, array(1,2)) 
            and ($qrunid <> $runid) 
            and ($qrunid <> '') 
            and ($runid <> '') 
            and ($qhost <> $host) 
            and ($qhost <> '') 
            and ($host <> '') 
         ) {
         // this indicates that run is OK to go because it is a DIFFERENT runid and a DIFFERENT host
         $status_flag = 0;
      }
   } else {
      //error_log ($listobject->querystring);
      $status_flag = NULL;
   }
   $return_vals['elemname'] = $elemname;
   $return_vals['status_flag'] = $status_flag;
   $return_vals['status_mesg'] = $status_mesg;
   $return_vals['interval'] = $interval;
   $return_vals['runid'] = isset($runid) ? $runid : $qrunid;
   
   return $return_vals;
}

function removeRunCache($listobject, $recid, $run_id, $debug=0) {
   $listobject->querystring = " delete from scen_model_run_elements ";
   $listobject->querystring .= " where elementid = $recid ";
   $listobject->querystring .= "    and runid = $run_id ";
   if ($debug) {
      error_log($listobject->querystring);
   }
   $listobject->performQuery();
   /*
   $listobject->querystring = " delete from system_status ";
   $listobject->querystring .= " where element_key = $recid ";
   $listobject->querystring .= "    and runid = $run_id ";
   if ($debug) {
      error_log($listobject->querystring);
   }
   */
   $listobject->performQuery();
}

function clearStatus($listobject, $recid, $run_id, $debug=0) {
   $listobject->performQuery();
   $listobject->querystring = " delete from system_status ";
   $listobject->querystring .= " where element_key = $recid ";
   $listobject->querystring .= "    and runid = $run_id ";
   if ($debug) {
      error_log($listobject->querystring);
   }
   $listobject->performQuery();
}

function removeTreeCacheCriteria($listobject, $recid, $run_id, $types = array(), $custom1 = array(), $custom2 = array(), $ignore = array(), $debug=0) {
  $parts = getNestedContainersCriteria ($listobject, $recid, $types, $custom1, $custom2, $ignore, $debug);
  if ($debug) {
    error_log("parts: " . print_r($parts,1) . "\n");
  }
  foreach ($parts as $piece) {
    removeRunCache($listobject, $piece['elementid'], $run_id, $debug);
    if ($debug) {
      error_log("Query: " . $piece['query'] . "\n");
    }
    error_log("Removing run data $run_id " . $piece['elementid']);
  }
}

function removeTreeCache($listobject, $recid, $run_id, $debug=0) {
   $parts = getNestedContainers($listobject, $recid, $debug);
   if ($debug) {
      error_log("parts: " . print_r($parts,1) . "\n");
   }
   foreach ($parts as $piece) {
      removeRunCache($listobject, $piece['elementid'], $run_id, $debug);
      if ($debug) {
         error_log("Query: " . $piece['query'] . "\n");
      }
   }
}
   

function setStatus($listobject, $elementid, $mesg, $sip, $status_flag=1, $runid = -1, $pid = -1, $debug = 0) {
   //$pid = -1; // do not set a valid pid here, since we can not know it (I don't think)
   if (is_object($listobject)) {
   
      $listobject->querystring = " select count(*) as numrecs from system_status ";
      $listobject->querystring .= " where element_name = 'model_run' ";
      $listobject->querystring .= "    and element_key = $elementid  ";
      $listobject->querystring .= "    and host = '$sip' ";
      $listobject->querystring .= "    and runid = '$runid' ";
      $listobject->performQuery();
      $syslogrec = $listobject->getRecordValue(1,'numrecs');

      if ($syslogrec == 0) {
         $listobject->querystring = " insert into system_status (element_name, element_key, ";
         $listobject->querystring .= "    status_mesg, status_flag, pid, last_updated, host, runid ) ";
         $listobject->querystring .= " values ('model_run', $elementid, '$mesg', $status_flag, $pid, now(), '$sip', $runid) ";
         $listobject->performQuery();
      } else {
         $listobject->querystring = " update system_status ";
         $listobject->querystring .= " set status_mesg = '$mesg', ";
         $listobject->querystring .= "    status_flag = $status_flag, ";
         $listobject->querystring .= "    pid = $pid, ";
         $listobject->querystring .= "    last_updated = now(), ";
         $listobject->querystring .= "    host = '$sip', ";
         $listobject->querystring .= "    runid = '$runid' ";
         $listobject->querystring .= " where element_name = 'model_run' ";
         $listobject->querystring .= "    and element_key = $elementid ";
         $listobject->querystring .= "    and host = '$sip' ";
         $listobject->querystring .= "    and runid = '$runid' ";
         $listobject->performQuery();
      }
      if ($debug or $listobject->error) {
         error_log($listobject->querystring);
      }
   } else {
		error_log("Failed to set message due to undefined listobject");
	}

}

function checkMessages($listobject, $elementid, $sip, $msg_type = '', $runid = NULL) {
   $pid = -1; // do not set a valid pid here, since we can not know it (I don't think)
   if (is_object($listobject)) {
      $listobject->querystring = " select msg_type from system_message ";
      $listobject->querystring .= " where elementid = $elementid  ";
      $listobject->querystring .= "    and host = '$sip' ";
      if (! ($runid === NULL) ) {
         $listobject->querystring .= "    and runid = '$runid' ";
      }
      if ($msg_type <> '') {
         $listobject->querystring .= "    and msg_type = '$msg_type' ";
      }
      $listobject->performQuery();
      if ($debug) { error_log($listobject->querystring);}
   }
   return $listobject->queryrecords;
}

function clearMessages($listobject, $elementid, $sip, $msg_type = '', $runid = NULL) {
   $pid = -1; // do not set a valid pid here, since we can not know it (I don't think)
   if (is_object($listobject)) {
      $listobject->querystring = " delete from system_message ";
      $listobject->querystring .= " where elementid = $elementid  ";
      $listobject->querystring .= "    and host = '$sip' ";
      if (! ($runid === NULL) ) {
         $listobject->querystring .= "    and runid = '$runid' ";
      }
      if ($msg_type <> '') {
         $listobject->querystring .= "    and msg_type = '$msg_type' ";
      }
      error_log($listobject->querystring);
      $listobject->performQuery();
   }
}


function addMessage($listobject, $elementid, $sip, $msg_type, $runid = NULL) {
   $pid = -1; // do not set a valid pid here, since we can not know it (I don't think)
   if (is_object($listobject)) {
      $listobject->querystring = " insert into system_message (elementid, host, msg_type ";
      if (! ($runid === NULL) ) {
         $listobject->querystring .= ", runid ";
      }
      $listobject->querystring .= " ) ";
      $listobject->querystring .= " values ($elementid, '$sip', '$msg_type' ";
      if (! ($runid === NULL) ) {
         $listobject->querystring .= " , '$runid' ";
      }
      $listobject->querystring .= " ) ";
      error_log($listobject->querystring);
      $listobject->performQuery();
   }
}

function checkTreeRunDate($listobject, $recid, $run_id, $startdate, $enddate, $cache_date, $debug = 0, $remove_outdated = 0, $require_verification = 0) {
  // checks whether the given tree has been run UP TO BUT NOT INCLUDING THE OUTLET since the desired cache_date
  // @todo: THis is NOT a true statement, does nothing when the root is flagged complete but unsusccessful
   // also requires that the run be completed successfully... according to the run_verified flag
  $elements = getNestedContainers($listobject, $recid);
  $root_info = getRunFile($listobject, $recid, $run_id, $debug);
  $root_date = $root_info['run_date'];
  $root_time = strtotime($root_date);
  $cache_time = strtotime($cache_date);
  $status = 1;
  $i = 0;
  $running = array(1,2);
  // now check any branches, are they younger than the parent?
  foreach ($elements as $thiselement) {
    $branchid = $thiselement['elementid'];
    $cacheable = getElementCacheable($listobject, $branchid);
    $en = getElementName($listobject, $branchid);
    if ($debug) {
       error_log("Evaluating Branch $en \n");
    }
    // cacheable settings are 0 - not cacheable, 1 - cacheable, 2 - pass-through, 3 - persistent
    $status_vars = verifyRunStatus($listobject, $branchid, $run_id);
    $branch_status = $status_vars['status_flag'];
    if ($debug) {
      error_log("Branch $en cacheable setting $cacheable (0 - not cacheable, 1 - cacheable, 2 - pass-through, 3 - persistent)\n");
      error_log("Run status Check -  $branch_status \n");
    }
    if (in_array($branch_status, $running)) {
      // this element is currently running, return 0 (not finished) regardless of its cache status
      $status = 0;
      error_log("Child Branch $branchid currently running.");
    }
    if ($cacheable == 1) {
      $check = checkRunDate($listobject, $branchid, $run_id, $cache_date, $startdate, $enddate, $debug);
      $status = $status & $check;
      if ($debug) {
        error_log("checkRunDate returned - $check *(status = $status) \n");
      }
      $branch_info = getRunFile($listobject, $branchid, $run_id, $debug);
      if ($branch_info === FALSE) {
       error_log("Branch $branchid has not been run for run ID $run_id .");
      }
      $branch_date = $branch_info['run_date'];
      $branch_time = strtotime($branch_date);
      if ($debug) {
        error_log("Comparing Branch $en ($branchid) time $branch_date to root ($recid) time $root_date \n");
      }
      if ( ($branch_time > $root_time) and ($branchid <> $recid) ) {
        error_log("Branch $branchid time $branch_date > root $root_date ($recid) \n");
        $i++;
        $status = 0;
      }
      if ( ($branch_info['run_verified'] <> 1) and ($branchid == $recid)  and ($require_verification) ) {
        $status = 0;
        error_log("Root $branchid failed verification \n");
      }
    } else {
      if ($debug) {
        error_log("Branch $en cacheable setting $cacheable - skipping \n");
      }
    }
  }
  /*
  if ($root_time < $cache_time) {
    $status = 0;
  }
  */
  if ( ($status == 0) and $remove_outdated ) {
    // clear model run data clearRun
    error_log("Clearing Outdated Run Data for $en ($recid)\n");
  }
  if ($debug) {
    error_log("cache time = $cache_date, root time = $root_date  \n");
    error_log("$i branches with time > root #$recid (status = $status) \n");
  }
  return $status;
}


function checkTreeRunDate2($listobject, $recid, $run_id, $startdate, $enddate, $cache_date, $debug = 0, $remove_outdated = 0, $container_types = array() ) {
   // checks whether the given tree has been run TO THE OUTLET since the desired cache_date
   // also requires that the run be completed successfully... according to the run_verified flag
   $elements = getNestedContainers($listobject, $recid);
   $root_info = getRunFile($listobject, $recid, $run_id);
   if ($root_info === FALSE) {
      if ($debug) {
         error_log("Root $recid has no run record for runid $run_id \n");
      }
   }
   $root_date = $root_info['run_date'];
   $root_time = strtotime($root_date);
   $cache_time = strtotime($cache_date);
   $status = 1;
   $i = 0;
   $en = getElementName($listobject, $branchid);
   if ($debug) {
      error_log("Evaluating Branch $recid \n");
   }
   // now check any branches, are they younger than the parent?
   foreach ($elements as $thiselement) {
      $branchid = $thiselement['elementid'];
      $cacheable = getElementCacheable($listobject, $branchid);
      $en = getElementName($listobject, $branchid);
      if ($debug) {
         error_log("Evaluation Branch $en \n");
      }
      // cacheable settings are 0 - not cacheable, 1 - cacheable, 2 - pass-through, 3 - persistent
      if ($cacheable == 1) {
         $check = checkRunDate($listobject, $branchid, $run_id, $cache_date, $startdate, $enddate, $debug);
         $status = $status & $check;
         if ($debug) {
            error_log("checkRunDate returned - $check *(status = $status) \n");
         }
         $branch_info = getRunFile($listobject, $branchid, $run_id, $debug);
         $branch_date = $branch_info['run_date'];
         $branch_time = strtotime($branch_date);
         if ($debug) {
            error_log("Comparing Branch $en ($branchid) time $branch_time to root $root_time \n");
         }
         if ($branch_time > $root_time) {
            //print("Branch $branchid time $branch_time > root $root_time \n");
            $i++;
            $status = 0;
         }
         if ( ($branch_info['run_verified'] <> 1) and ($branchid == $recid) ) {
            $status = 0;
            if ($debug) {
               error_log("Root $branchid failed verification \n");
            }
         }
      } else {
         if ($debug) {
            error_log("Branch $en cacheable setting $cacheable - skipping \n");
         }
      }
   }
   
   if ($root_time < $cache_time) {
      $status = 0;
   }
   
   //if ( ($status == 0) and $remove_outdated ) {
      // clear model run data clearRun
      error_log("Clearing Outdated Run Data for $en ($recid)\n");
   //}
   if ($debug) {
      error_log("cache time = $cache_time, root time = $root_time  \n");
      error_log("$i branches with time > root #$recid (status = $status) \n");
   }
   return $status;
}

function checkStandAlone($listobject, $recid) {
   $props = getElementPropertyList($recid);
   //print("Properties " . print_r($props,1) . "\n");
   if (isset($props['standalone'])) {
      return $props['standalone'];
   } else {
      return 0;
   }
}


function getElementCacheable($listobject, $elementid) {
   // have we prohibited caching of this object?
   $listobject->querystring = " select cacheable from scen_model_element where elementid = $elementid ";
   //error_log($listobject->querystring);
   $listobject->performQuery();
   if ($listobject->numrows > 0) {
      $cacheable = $listobject->getRecordValue(1,'cacheable');
   } else {
      $cacheable = 0;
   }
   return $cacheable;
   
}


function unSerializeSingleModelObject($elementid, $input_props = array(), $debug = 0, $runtime_db = FALSE, $cached = FALSE, $cache_runid = -2 ) {
   global $listobject, $tmpdir, $shellcopy, $ucitables, $scenarioid, $outdir, $outurl, $goutdir, $gouturl, $unserobjects, $adminsetuparray, $wdm_messagefile, $basedir, $model_startdate, $model_enddate;
   // Given an object in JSON form.
   // - Instantiates the object from its class
   # goes through all contained objects:
   #   processors
   #   inputs
   #   components
   # and un-serializes them into objects
   # connects objects to parent container
   # returns the object, and any error output in an associative array('debug', 'object')
   #$debug = 1;
   # create a global container to hold any objects that have already been instantiated
   
   if (!is_object($runtime_db)) {
      //error_log("Custom model list object submitted for runtime data storage");
      $runtime_db = $listobject;
   }
   $returnArray = array();
   $returnArray['error'] = 'Error Info';
   $returnArray['debug'] = 'Debug Info';
   $returnArray['object'] = '';
   $returnArray['record'] = array();
   if (!is_array($unserobjects)) {
      if ($debug) {
         $returnArray['debug'] .= "Creating Blank Unser Objects array<br>";
      }
      $unserobjects = array();
   }
   if ($debug) {
      $returnArray['debug'] .= "Unser Objects<br>";
      $returnArray['debug'] .= print_r(array_keys($unserobjects),1);
      $returnArray['debug'] .= "End Unser Objects<br>";
   }

   if ($elementid > 0) {
      if ($cached) {
        error_log("Calling getCachedObjectXML(listobject, $elementid, $cache_runid)");
         $qresult = getCachedObjectXML($listobject, $elementid, $cache_runid);
      } else {
         //error_log("Calling getObjectXML(listobject, $elementid) ");
         $qresult = getObjectXML($listobject, $elementid);
      }
      if ($qresult['error']) {
        error_log("Calling getObjectXML(listobject, $elementid) " . $qresult['error']);
        return FALSE;
      }
      $record = $qresult['record'];
      $returnArray['error'] .= " Retreiving object $elementid : " . $qresult['query'] . " ; <br>";
      $returnArray['record'] = $record;
      $returnArray['record']['elem_xml'] = '';
      $returnArray['record']['elementid'] = $elementid;
      //error_log( " Retreiving object $elementid : " . $qresult['query'] );
      //error_log( " SQL Obj error string : " . $listobject->error );

      $elem_xml = $record['elem_xml'];
      $elemname = $record['elemname'];
      $xcoord = $record['xcoord'];
      $ycoord = $record['ycoord'];
      $wkt_geom = $record['wkt_geom'];
      $extent = $record['geom_extent'];
      $groupid = $record['groupid'];
      $operms = $record['operms'];
      $gperms = $record['gperms'];
      $pperms = $record['pperms'];
      $custom1 = $record['custom1'];
      $custom2 = $record['custom2'];
      $astart = $qresult['astart'];
      $aend = $qresult['aend'];
   } else {
      $elem_xml = '';
      $astart = 0;
      $aend = -1;
   }
   $opxmls = array();
   //error_log("$elemname unserialized, searching for Operators ");
   $returnArray['debug'] .= "Searching for Operators on $elemname <br>";
   for ($i = $astart; $i <= $aend; $i++) {
      if ($debug) {
         $returnArray['debug'] .= "retrieving op $i<br>";
      }
      if ($cached) {
         //error_log("Calling getCachedOperatorXML(listobject, $elementid, $i, $cache_runid)");
         $opresult = getCachedOperatorXML($listobject, $elementid, $i, $cache_runid);
      } else {
        // error_log("Calling getOperatorXML(listobject, $elementid, $i)" );
         $opresult = getOperatorXML($listobject, $elementid, $i);
      }
      $thisxml = $opresult['xml'];
      if (str_replace('"', '', $thisxml) <> '') {
         $opxmls[$i] = $thisxml;
      }
      if ($debug) {
         $returnArray['debug'] .= $opresult['query'] . " ; <br>";
         switch ($debug) {
            case 1:
            $returnArray['debug'] .= "Op $i XML: " . substr($opxmls[$i],0,64) . "<br>";
            break;
            
            case 2:
            $returnArray['debug'] .= "Op $i XML: $opxmls[$i]<br>";
            break;
         }
      }
   }

   if ($debug) {
      $returnArray['debug'] .= "Creating Unserializer<br>";
   }
   // tell the unserializer to create an object
   $options = array("complexType" => "object");
   // tell the unserializer to create an array of properties
   #$options = array("complexType" => "array");

   // create object 
   if (!class_exists('XML_Unserializer')) {
      error_log("class XML_Unserializer - PEAR class needs to be installed ");
   }
   //error_log("calling XML_Unserializer ");
   $unserializer = new XML_Unserializer($options);

   if ($debug) {
      $returnArray['debug'] .= "Unserializing<br>";
   }
   //error_log("Unserializing<br>");
   // unserialize the object. Use "false" since this is not a document, "true" if it is a document
   $result = $unserializer->unserialize($elem_xml, false);
   $returnArray['elemtype'] = $unserializer->getRootName();
   if (is_object($returnArray['elemtype'])) {
      if (get_class($returnArray['elemtype']) == 'PEAR_Error') {
         error_log("PEAR Unserialize Error: " . $returnArray['elemtype']->message);
         error_log("Called from unSerializeSingleModelObject($elementid, : " . print_r($input_props,1));
      }
   } else {
      if ($debug) {
         error_log("Unserialize found elemen type: " . $returnArray['elemtype']);
      }
   }
   if ($debug) {
      $returnArray['debug'] .= "Result of Unserializing<br>";
   }
   // dump the result
   $thisobject = $unserializer->getUnserializedData();
   if ($debug) {
      error_log("Finished Unserializing<br>");
      error_log("Unserialize object class: " . get_class($thisobject));
   }

   # make sure this is a valid object
   if (!is_object($thisobject) or !($result === true)) {
      # problem re-serializing
      if ($debug) {
        error_log( "Problem Un-serializing object: $elemname, ID: $elementid <br>");
      }
      $returnArray['error'] .= "Problem Un-serializing object: $elemname, ID: $elementid <br>";
      return $returnArray;
   }

   # first thing, set the component id, so that all following actions do not get goofed up
   $thisobject->setCompID($elementid);
   $thisobject->groupid = $groupid;
   $thisobject->operms = $operms;
   $thisobject->gperms = $gperms;
   $thisobject->pperms = $pperms;
   $thisobject->object_class = $record['objectclass'];

   if ($debug) {
      $oprops = (array)$thisobject;
      $returnArray['debug'] .= "<br><b>Saved properties</b>:<br>";
      $returnArray['debug'] .= print_r($oprops,1);
      $returnArray['debug'] .= "<br><b>Setting Transient properrties</b><br>";
   }
  if ($debug) {
    error_log("Setting Transient properrties on object");
  }
   if (property_exists($thisobject, 'the_geom')) {
      if ($debug) {
         $returnArray['debug'] .= "Setting a geometry object on this object.<br>";
      }
      if (strlen($wkt_geom) > 0) {
         $thisobject->the_geom = $wkt_geom;
         $thisobject->geomtype = $geomtype;
         $thisobject->extent = $extent;
      } else {
         $thisobject->the_geom = 'POINT( ' . $xcoord . ' ' . $ycoord . ' )';
         $thisobject->geomtype = 1;
         $thisobject->extent = 'BOX( ' . $xcoord . ' ' . $ycoord . ' , ' . $xcoord . ' ' . $ycoord . ' )';
      }
   }

   if (property_exists($thisobject, 'listobject')) {
      if ($debug) {
         $returnArray['debug'] .= "Setting a list object on this object.<br>";
      }
      
      $thisobject->listobject = $runtime_db;
   }

   if (property_exists($thisobject, 'master_db')) {
      if ($debug) {
         $returnArray['debug'] .= "Adding pointer to master list object on this object.<br>";
      }
      
      $thisobject->master_db = $listobject;
   }

   if (property_exists($thisobject, 'geomx')) {
      if ($debug) {
         $returnArray['debug'] .= "Setting x-coord on this object.<br>";
      }
      $thisobject->geomx = $xcoord;
   }

   if (property_exists($thisobject, 'geomy')) {
      if ($debug) {
         $returnArray['debug'] .= "Setting y-coord on this object.<br>";
      }
      $thisobject->geomy = $ycoord;
   }

   if (property_exists($thisobject, 'wdm_messagefile')) {
      if ($debug) {
         $returnArray['debug'] .= "Setting a WDM message file on this object.<br>";
      }
      $thisobject->wdm_messagefile = $wdm_messagefile;
   }
   if (property_exists($thisobject, 'ucitables')) {
      if ($debug) {
         $returnArray['debug'] .= "Setting a ucitables object on this object.<br>";
      }
      $thisobject->ucitables = $ucitables;
   }
  if ($debug) {
    error_log("Setting Input properties on object");
  }
   foreach ($input_props as $this_propname => $this_propvalue) {
      if ($debug) {
         error_log("Setting $this_propname = $this_propvalue ");
      }
      $thisobject->setProp($this_propname, $this_propvalue);
   }
   // disable this since this mode does not require a debugging function
   $enable_debug = 1;
   if (isset($input_props['debug'])) {
      $enable_debug = $input_props['debug'];
   }
   if ($enable_debug == 0) {
      $thisobject->debugmode = -1;
      $thisobject->logerrors = 0;
   }
   
  if ($debug) {
    error_log("Calling setup methods on object");
  }

  if (method_exists($thisobject, 'wake')) {
    if ($debug) { 
      error_log("Calling object wake() method");
    }
    $returnArray['debug'] .= "Calling Object wake() nmethod.<br>\n";
    $thisobject->wake();
    if ($debug) {
      error_log("Finished Calling object wake() method");
    }
  }
   #$returnArray['debug'] .= "<b>Original object debug status: </b>" . $thisobject->debug . "<br>";
   #$thisobject->debug = $debug;
   $thisobject->outdir = $outdir;
   $thisobject->outurl = $outurl;
   $thisobject->goutdir = $goutdir;
   $thisobject->gouturl = $gouturl;
   $thisobject->tmpdir = $tmpdir;
   $thisobject->basedir = $basedir;
   $thisobject->shellcopy = $shellcopy;
   $returnArray['error'] .= " Storing object $elementid in unserobjects <br>";
   $unserobjects[$elementid] = $thisobject;
   $returnArray['error'] .= $thisobject->errorstring;

   if ($debug) {
      $returnArray['debug'] .= "<br><b>Unserializing operators</b><br>";
   }
   //error_log("Unserializing operators<br>");

   #return $returnArray;
   $j = 0;
  if ($debug) {
    error_log("Setting up operators on object");
  }
  foreach ($opxmls as $thisop) {
    // unserialize the object. Use "false" since this is not a document, "true" if it is a document
    $dz = $j + 1;
    if ($debug) {
      error_log("Unserializing op $dz" );
    }
    $result = $unserializer->unserialize($thisop, false);
    if ($debug) {
      error_log("Successfully Unserialized op $dz of " . count($opxmls));
      $returnArray['debug'] .= "<br><b>Result of Unserializing</b><br>";
    }
    if ($result === true) {
      // dump the result
      $opobject = $unserializer->getUnserializedData();
      if ($debug) {
        error_log("Op $dz has name " . $opobject->name);
      }
      if (property_exists($opobject, 'listobject')) {
        if ($debug) {
          $returnArray['debug'] .= "Setting a ucitables object on this object.<br>";
        }
        $opobject->listobject = $runtime_db;
      }
      if (property_exists($opobject, 'master_db')) {
        if ($debug) {
          $returnArray['debug'] .= "Adding pointer to master list object on this object.<br>";
        }
        $opobject->master_db = $listobject;
      }
      if (property_exists($opobject, 'ucitables')) {
        if ($debug) {
          $returnArray['debug'] .= "Setting a ucitables object on this object.<br>";
        }
        $opobject->ucitables = $ucitables;
      }
      // disable this since this mode does not require a debugging function
      $enable_debug = 1;
      if (isset($input_props['debug'])) {
        $enable_debug = $input_props['debug'];
      }
      if ($enable_debug == 0) {
        $opobject->debugmode = -1;
        $opobject->logerrors = 0;
      }
      $opobject->parentobject = $thisobject;
      // **************************************************
      // MODIFIED TO CASCADE ALL SUB-COMP PROPERTIES:
      // **************************************************
      /*
      // now, get any properties from the parent that this subobject is supossed to see
      if ($opobject->debug) {
         error_log("Getting parent properties for adminsetup for $opobject->name ");
      }
      $adminsetuparray = getParentProps($opobject, $thisobject, $adminsetuparray);
      $opobject->listobject->adminsetuparray = $adminsetuparray;
      if (method_exists($opobject, 'wake')) {
         $opobject->wake();
      }
      */
      // **************************************************
      // END - MODIFIED TO SHARE ALL SUB-COMP PROPERTIES
      // **************************************************
      $opobject->basedir = $basedir;
      $opobject->outdir = $outdir;
      $opobject->outurl = $outurl;
      $opobject->goutdir = $goutdir;
      $opobject->gouturl = $gouturl;
      # manually set the componentid, since these do not have a db generated ID, and they only exist in
      # a scope that is local to the containing object, set them to be a decimal on the parent elementid
      $opobject->componentid = "$elementid" . "." . $j;
      $opobject->object_class = get_class($opobject);
      //error_log("$opobject->name class = $opobject->object_class");
      if ($debug) {
        $returnArray['debug'] .= print_r($opobject,1);
        $returnArray['debug'] .= "<br><b>Unserializing operators</b><br>";
      }
      $thisobject->addOperator($opobject->name, $opobject, $opobject->defaultval);
      $j++;
    }
  }
  if ($debug) {
    error_log("Finished adding $j propeties.");
  }
  // **************************************************
  // MODIFIED TO CASCADE ALL SUB-COMP PROPERTIES:
  // **************************************************
  // the next two foreach loops were added, to replace the above version of the second foreach loop
  // first, set the parent props of all supcomps so that the parent knowledge of all exposed subcomp properties is perfect
  //error_log($thisobject->name . ": setting the parent props of all supcomps so that the parent knowledge of all exposed subcomp properties is perfect");
  //error_log("Calling operators initOnParent() methods on object");
  foreach ($thisobject->processors as $thisproc) {
    // now set the parent props for all subcomps, then
    if ($debug) {
      error_log("$thisobject->name checking $thisproc->name for method initOnParent() ");
    }
    if (method_exists($thisproc, 'initOnParent')) {
      $thisproc->initOnParent();
      if ($thisproc->debug) {
        error_log("$thisobject->name calling initOnParent() on $thisproc->name ");
      }
    }
  }
  // then call the wake method for each subcomp
  if ($debug) {
    error_log($thisobject->name . ": calling the wake method for each subcomp");
  }
  foreach ($thisobject->processors as $thisproc) {
    // now set the parent props for all subcomps, then
    if ($debug) {
      error_log("Sub-object $thisobject->name is class " . get_class($thisproc));
      $thisproc->debug = $debug;
      error_log("Getting parent properties for adminsetup for $thisproc->name ");
    }
    if ($debug) {
      error_log("Calling getParentProps() for $thisproc->name ");
    }
    $adminsetuparray = getParentProps($thisproc, $thisobject, $adminsetuparray);
    $thisproc->listobject->adminsetuparray = $adminsetuparray;
    if ($debug) {
      error_log("Calling wake() $thisproc->name ");
    }
    if (method_exists($thisproc, 'wake')) {
      $thisproc->wake();
    }
    if ($debug) {
      error_log("Finished wake()ing $thisproc->name ");
    }
  }
  // maintain updated adminsetup array record on list object
  //$listobject->adminsetuparray = $adminsetuparray;
  // **************************************************
  // END - MODIFIED TO CASCADE ALL SUB-COMP PROPERTIES:
  // **************************************************
  # retrieve input linkages
  $listobject->querystring = "  select src_id, src_prop, dest_prop ";
  $listobject->querystring .= " from map_model_linkages ";
  $listobject->querystring .= " where dest_id = $elementid ";
  //$listobject->querystring .= "    and linktype = 2 ";
  $listobject->querystring .= "    and linktype in ( 2, 3 ) ";
  # screen out non-set objects so as to avoid an error
  #$listobject->querystring .= "    and src_id <> -1 ";
  if ($debug) {
    $returnArray['debug'] .= " $listobject->querystring ; <br>";
  }
  $listobject->performQuery();

  $linkrecs = $listobject->queryrecords;
  if ($debug) {
    $returnArray['debug'] .= " Searching for Input objects in $thisobject->name <br>";
  }
  //error_log("Creating blank objects for linked siblings ");
  foreach ($linkrecs as $thisrec) {
    $src_id = $thisrec['src_id'];
    $src_prop = $thisrec['src_prop'];
    $dest_prop = $thisrec['dest_prop'];
    if ($debug) {
      $returnArray['debug'] .= " Searching for $src_id in " . print_r(array_keys($unserobject)) . '<br>';
    }
    if ($src_id <> -1) {
      # need to insert a dummy object for this linkage
      if ($debug) {
        $returnArray['debug'] .= " Adding Input $linkobj->name : $src_prop -gt $dest_prop <br>";
      }
      //error_log(" Adding Input $linkobj->name : $src_prop -gt $dest_prop ");
      # insert a blank object here, since we really only need a placeholder
      $linkobj = new modelObject;
      $thisobject->addInput($dest_prop, $src_prop, $linkobj);
    } else {
      $linkerror = 'NULL Linkage found';
      $linkdebug = 'NULL Linkage found';
      $returnArray['debug'] .= $linkdebug;
      $linkobj = NULL;
    }
  }

  //error_log("Done initializing placeholders for links");
  #$thisobject->setStateVar();
  $returnArray['object'] = $thisobject;
  #$debug = 0;
  //error_log("Returning object results");
  return $returnArray;
}


function unSerializeModelObject($elementid, $input_props = array(), $model_listobj = '', $cache_level = -1, $cache_id = -1, $current_level = -1) {
   global $listobject, $tmpdir, $shellcopy, $ucitables, $scenarioid, $debug, $outdir, $outurl, $goutdir, $gouturl, $unserobjects, $adminsetuparray, $wdm_messagefile, $wdimex_exe, $basedir, $model_startdate, $model_enddate, $serverip, $modeldb, $modelcontainerid, $modelcontainername;
   
   //error_log("unSerializeModelObject called for $elementid <br>");
   $modelcontainerid = (!isset($modelcontainerid)) ? $elementid : $modelcontainerid;
   $elemname = getElementName($listobject, $elementid);
   $modelcontainername = (!isset($modelcontainername)) ? $elemname : $modelcontainername;
   
   # goes through all contained objects:
   #   processors
   #   inputs
   #   components
   # and un-serializes them into objects
   # connects objects to parent container
   # returns the object, and any error output in an associative array('debug', 'object')
   #$debug = 1;
   # create a global container to hold any objects that have already been instantiated
   $returnArray = array();
   $returnArray['error'] = '';
   $returnArray['debug'] = '';
   $returnArray['object'] = '';
   $returnArray['complist'] = array();
   $returnArray['cached'] = array();
   $returnArray['live'] = array();
   $returnArray['remote'] = array();
   array_push($returnArray['complist'], $elementid);
   if (!isset($model_startdate)) {
      $model_startdate = '';
      $returnArray['error'] .= "Global model_startdate not defined .<br>";
   }
   //error_log("Checking start date <br>");
   if (isset($input_props['model_startdate'])) {
      $conv_time = new DateTime($input_props['model_startdate']);
      $model_startdate = $conv_time->format('Y-m-d H:i:s');
      $returnArray['error'] .= "Setting model_startdate - $model_startdate .<br>";
   }
   if (!isset($model_enddate)) {
      $model_enddate = '';
      $returnArray['error'] .= "Global model_enddate not defined .<br>";
   }
   //error_log("Checking end date <br>");
   if (isset($input_props['model_enddate'])) {
      $conv_time = new DateTime($input_props['model_enddate']);
      $model_enddate = $conv_time->format('Y-m-d H:i:s');
      $returnArray['error'] .= "Setting model_enddate - $model_enddate .<br>";
   }
   //error_log("Checking listobject $elementid <br>");
   // unless we are passed one, we implicitly assume that the standard list object is valid
   if (!is_object($model_listobj)) {
     // swap modeldb for listobject as default here, when loading objects for editing - does it break anything?
     // maybe it requires the adminsetuparray or something?
      $model_listobj = $listobject;
      //error_log("No Valid Model Object Passed, using default Database for Model Runtime Storage");
   } else {
      //error_log("Received Valid Object for Database for Model Runtime Storage");
   }
   
   if (!is_array($unserobjects)) {
      if ($debug) {
         $returnArray['debug'] .= "Creating Blank Unser Objects array<br>";
      }
      $unserobjects = array();
       // the level in the nested hierarchy
   }
   if ($debug) {
      $returnArray['debug'] .= "Unser Objects<br>";
      $returnArray['debug'] .= print_r($unserobjects,1);
      $returnArray['debug'] .= "End Unser Objects<br>";
   }
   
   // check on caching status of this object
   $order = getElementOrder($listobject, $elementid);
   $cache_file_exists = 0;
   
   // new cache check sub-routine
   
   $cache_res = checkObjectCacheStatus($listobject, $elementid, $order, $cache_level, $cache_id, $current_level, $model_startdate, $model_enddate, $debug);
   $cache_type = $cache_res['cache_type'];
   $cache_file_exists = $cache_res['cache_file_exists'];
   $cacheable = $cache_res['cacheable'];
   $returnArray['error'] .= $cache_res['error'];
   //error_log("Element $elementid: Cache Type: $cache_type - Cacheable - $cacheable <br>");
   //error_log("Element $elementid: Cache Settings: " . print_r($cache_res,1));
   
   if ( ($cache_type <> 'disabled') and (count($unserobjects) >= 1) ) {
      error_log("Loading $elemname ($elementid) as cached.");
      setStatus($listobject, $modelcontainerid, "Loading $elemname ($elementid) as cached.", $serverip, 1, $cache_id, -1, 0);
      // use new loadCachedObject routine
      $res = loadCachedObject($model_listobj, $elementid, $cache_id, $debug);
      $thisobject = $res['object'];
            
      array_push($returnArray['cached'], $elementid);
      //$unserobjects[$elementid] = $thisobject;
      
      $returnArray['error'] .= "Cached object created for $elementid <br>";
      $returnArray['error'] .= "Used New Caching Routine <br>";
      error_log("Cached object created for $elementid <br>");
      error_log('cache info: ' . print_r($res['tableinfo'],1));
         
      // return the object 
      
   } else {
      error_log("Loading $elemname ($elementid) as live model element.");
      setStatus($listobject, $modelcontainerid, "Loading $elemname ($elementid) as live model element.", $serverip, 1, $cache_id, -1, 0);
      array_push($returnArray['live'], $elementid);
      // instantiate this model to run
      // new code, uses unserializeSingleModelObject
      if (!isset($unserobjects[$elementid])) {
         $us_result = unserializeSingleModelObject($elementid, $input_props, $debug, $model_listobj);
         $thisobject = $us_result['object'];
         // we call this AFTER the wake() method of the object, because this will properly format the start and end time
         if (count($unserobjects) <= 1) {
            if (property_exists($thisobject, 'starttime')) {
               if ( ($model_startdate == '') or ($model_enddate == '') ) {
                  $model_startdate = $thisobject->starttime;
                  $model_enddate = $thisobject->endtime;
               }
            }
         }
      } else {
         $thisobject = $unserobjects[$elementid];
         $returnArray['error'] .= "Retrieving object from cache<br>";
      }
      $returnArray['error'] .= "Retrieving object sub-components, dates: $model_startdate, $model_enddate <br>";


      # retrieve child component linkages
      $linkrecs = getChildComponentType($listobject, $elementid);
      if ($debug) {
         $returnArray['debug'] .= " Searching for Contained objects in $thisobject->name <br>";
      }
      foreach ($linkrecs as $thisrec) {
         $src_id = $thisrec['elementid'];
         //error_log("Found child $src_id of parent $elementid");
         if ($debug) {
            $returnArray['debug'] .= " Searching for $src_id in " . print_r(array_keys($unserobjects)) . '<br>';
         }
         if (in_array($src_id, array_keys($unserobjects))) {
            # fetch from already instantiated objects
            $linkobj = $unserobjects[$src_id];
         } else {
            // increment current_level + 1 when we call contained objects
            $returnArray['error'] .= "Unserializing element $src_id with dates $model_startdate, $model_enddate <br>";
            if ($cacheable == 0) {
               $child_cache_level = -1;
            } else {
               $child_cache_level = $cache_level;
            }
            $params = 
            //error_log("Unserializing child $src_id of parent $elementid");
            $linkobjarray = unSerializeModelObject($src_id, array(), $model_listobj, $child_cache_level, $cache_id, $current_level + 1);
            $linkerror = $linkobjarray['error'];
            $linkdebug = $linkobjarray['debug'];
            foreach ($linkobjarray['complist'] as $thiselement) {
               if (!in_array($thiselement, $returnArray['complist'])) {
                  array_push($returnArray['complist'], $thiselement);
               }
            }
            foreach ($linkobjarray['cached'] as $thiselement) {
               if (!in_array($thiselement, $returnArray['cached'])) {
                  array_push($returnArray['cached'], $thiselement);
               }
            }
            foreach ($linkobjarray['live'] as $thiselement) {
               if (!in_array($thiselement, $returnArray['live'])) {
                  $returnArray['live'][] = $thiselement;
               }
            }
            foreach ($linkobjarray['remote'] as $thiselement) {
               if (!in_array($thiselement, $returnArray['remote'])) {
                  $returnArray['remote'][] = $thiselement;
               }
            }
            $returnArray['debug'] .= $linkdebug;
            $linkobj = $linkobjarray['object'];
            if (strlen($linkerror) > 0) {
               # error in sub-object, return the message and quit
               $returnArray['error'] .= " Error instantiating sub-object $src_id :<br>";
               $returnArray['error'] .= $linkerror;
            }
         }
         if ($debug) {
            $returnArray['debug'] .= " Adding Component $linkobj->name  <br>";
         }
         $thisobject->addComponent($linkobj);
      }

      # retrieve input linkages
      $linkrecs = getInputLinkages($listobject, $elementid, array(2,3)); 
      if ($debug) {
         $returnArray['debug'] .= " Searching for Input objects in $thisobject->name <br>";
      }
      foreach ($linkrecs as $thisrec) {
         $src_id = $thisrec['src_id'];
         $src_prop = $thisrec['src_prop'];
         $dest_prop = $thisrec['dest_prop'];
         $linktype = $thisrec['linktype'];
         if ($linktype == 3) {
            // remote link, always use cached, if does not exist, report error and continue
            $returnArray['error'] .= "Found remote link for element $src_id - param $src_prop -> $dest_prop <br>\n";
            error_log("Found remote link for element $src_id - param $src_prop -> $dest_prop <br>\n");
            $res = loadCachedObject($model_listobj, $src_id, $cache_id, $debug);
            if ($res['tableinfo']['record_missing']) {
               $returnArray['error'] .= "Cache file for runid $cache_id and $elementid MISSING<br>";
               error_log("Cache file for runid $cache_id and $elementid MISSING<br>");
            }
            $linkobj = $res['object'];
            $linkobj->debug = 1;
            $linkobj->debugmode = 1;
            array_push($returnArray['cached'], $src_id);
            //$unserobjects[$elementid] = $thisobject;
            
            $returnArray['error'] .= "Cached object created for $src_id <br>";
            $returnArray['error'] .= "Used New Caching Routine <br>";
            error_log("Cached object created for $src_id <br>");
            error_log('cache info: ' . print_r($res['tableinfo'],1));
            $thisobject->addInput($dest_prop, $src_prop, $linkobj);
            // this means that only actual containers can USE a remotely linked object, since there is no way to pass the 
            // init(), step() and other methods to it - could think about adding this in a different way, as a timeseries subcomp
            // perhaps?
            if (method_exists($thisobject, 'addComponent')) {
               $thisobject->addComponent($linkobj);
            }
         } else {
            if ($debug) {
               $returnArray['debug'] .= " Searching for $src_id in " . print_r(array_keys($unserobjects)) . '<br>';
            }
            if ($src_id <> -1) {
               if (in_array($src_id, array_keys($unserobjects))) {
                  # fetch from already instantiated objects
                  $linkobj = $unserobjects[$src_id];
                  if ($debug) {
                     $returnArray['debug'] .= " Adding Input $linkobj->name :from unser array <br>";
                  }
               } else {
                  if ($debug) {
                     $returnArray['debug'] .= " Creating Input $src_id  <br>";
                  }
                  // now, here we could put in a switch to see if we should run the input objects that are NOT contained
                  // by this object.  We could opt to have those objects instantiated as a time series with cached values 
                  // from a previous model run, allowing us a more economical way of running objects that rely on inputs 
                  // from other model containers, external to this one.
                  // could force cacheing with a switch at cache_level
                  $linkobjarray = unSerializeModelObject($src_id, array(), $model_listobj, $cache_level, $cache_id, $current_level);
                  $linkerror = $linkobjarray['error'];
                  $linkdebug = $linkobjarray['debug'];
                  $returnArray['debug'] .= $linkdebug;
                  foreach ($linkobjarray['complist'] as $thiselement) {
                     if (!in_array($thiselement, $returnArray['complist'])) {
                        array_push($returnArray['complist'], $thiselement);
                     }
                  }
                  foreach ($linkobjarray['cached'] as $thiselement) {
                     if (!in_array($thiselement, $returnArray['cached'])) {
                        array_push($returnArray['cached'], $thiselement);
                     }
                  }
                  foreach ($linkobjarray['live'] as $thiselement) {
                     if (!in_array($thiselement, $returnArray['live'])) {
                        $returnArray['live'][] = $thiselement;
                     }
                  }
                  foreach ($linkobjarray['remote'] as $thiselement) {
                     if (!in_array($thiselement, $returnArray['remote'])) {
                        $returnArray['remote'][] = $thiselement;
                     }
                  }
                  $linkobj = $linkobjarray['object'];
                  $returnArray['error'] .= $linkerror;
               }
               if ($debug) {
                  $returnArray['debug'] .= " Adding Input $linkobj->name : $src_prop -gt $dest_prop <br>";
               }
               $thisobject->addInput($dest_prop, $src_prop, $linkobj);
            } else {
               $linkerror = 'NULL Linkage found';
               $linkdebug = 'NULL Linkage found';
               $returnArray['debug'] .= $linkdebug;
               $linkobj = NULL;
            }
         }
      }
   }

   #$thisobject->setStateVar();
   $returnArray['object'] = $thisobject;
   #$debug = 0;
   return $returnArray;

}


function getCachedObjectXML($listobject, $elementid, $runid) {
   error_log("Reached getCachedObjectXML ");
   $retarr = array('debug'=>'', 'query'=>'');
   $listobject->querystring = "  select b.elem_xml, a.elemname, st_x(st_centroid(a.the_geom)) as xcoord, ";
   $listobject->querystring .= "    a.groupid, a.operms, a.gperms, a.pperms, a.custom1, a.custom2, ";
   $listobject->querystring .= "    st_y(st_centroid(a.the_geom)) as ycoord, array_dims(b.elemoperators) as adims, ";
   $listobject->querystring .= "    CASE ";
   $listobject->querystring .= "       WHEN geomtype = 3 THEN st_astext(a.poly_geom)";
   $listobject->querystring .= "       WHEN geomtype = 2 THEN st_astext(a.line_geom)";
   $listobject->querystring .= "       WHEN geomtype = 1 THEN st_astext(a.point_geom)";
   $listobject->querystring .= "    END as wkt_geom, a.geomtype, a.objectclass, ";
   $listobject->querystring .= "    CASE ";
   $listobject->querystring .= "       WHEN geomtype = 1 THEN box2d(a.point_geom) ";
   $listobject->querystring .= "       WHEN geomtype = 2 THEN box2d(a.line_geom) ";
   $listobject->querystring .= "       WHEN geomtype = 3 THEN box2d(a.poly_geom) ";
   $listobject->querystring .= "       ELSE box2d(a.the_geom) ";
   $listobject->querystring .= "    END as geom_extent ";
   $listobject->querystring .= " from scen_model_element as a, scen_model_run_elements as b ";
   $listobject->querystring .= " where a.elementid = $elementid ";
   $listobject->querystring .= "    and b.elementid = $elementid ";
   $listobject->querystring .= "    and b.runid = $runid ";
   $retarr['query'] = $listobject->querystring;
   //error_log( $listobject->querystring);
   $listobject->performQuery();
   if (count($listobject->queryrecords) > 0) {
      $retarr['record'] = $listobject->queryrecords[0];

      $dimstr = str_replace(']','',str_replace('[','',$listobject->getRecordValue(1,'adims')));
      $retarr['debug'] .= "<br>Dim: $dimstr<br>";
      list($astart, $aend) = explode(':', $dimstr);
      $retarr['astart'] = $astart;
      $retarr['aend'] = $aend;
   } else {
      $retarr['error'] = TRUE;
   }
   return $retarr;
}

function getObjectXML($listobject, $elementid) {
   $retarr = array('debug'=>'', 'query'=>'');
   $listobject->querystring = "  select elem_xml, elemname, st_x(st_centroid(the_geom)) as xcoord, ";
   $listobject->querystring .= "    groupid, operms, gperms, pperms, custom1, custom2, objectclass, ";
   $listobject->querystring .= "    st_y(st_centroid(the_geom)) as ycoord, array_dims(elemoperators) as adims, ";
   $listobject->querystring .= "    CASE ";
   $listobject->querystring .= "       WHEN geomtype = 3 THEN st_asText(poly_geom)";
   $listobject->querystring .= "       WHEN geomtype = 2 THEN st_asText(line_geom)";
   $listobject->querystring .= "       WHEN geomtype = 1 THEN st_asText(point_geom)";
   $listobject->querystring .= "    END as wkt_geom, geomtype, ";
   $listobject->querystring .= "    CASE ";
   $listobject->querystring .= "       WHEN geomtype = 1 THEN st_envelope(point_geom) ";
   $listobject->querystring .= "       WHEN geomtype = 2 THEN st_envelope(line_geom) ";
   $listobject->querystring .= "       WHEN geomtype = 3 THEN st_envelope(poly_geom) ";
   $listobject->querystring .= "       ELSE st_envelope(the_geom) ";
   $listobject->querystring .= "    END as geom_extent ";
   $listobject->querystring .= " from scen_model_element ";
   $listobject->querystring .= " where elementid = $elementid ";
   $retarr['query'] = $listobject->querystring;
   $listobject->performQuery();
   if (count($listobject->queryrecords) > 0) {
      $retarr['record'] = $listobject->queryrecords[0];

      $dimstr = str_replace(']','',str_replace('[','',$listobject->getRecordValue(1,'adims')));
      $retarr['debug'] .= "<br>Dim: $dimstr<br>";
      list($astart, $aend) = explode(':', $dimstr);
      $retarr['astart'] = $astart;
      $retarr['aend'] = $aend;
   } else {
      $retarr['error'] = TRUE;
   }
   return $retarr;
}


function getCachedOperatorXML($listobject, $elementid, $i, $cache_runid) {
   //error_log("Reached getCachedOperatorXML ");
   $retval = array();
   $listobject->querystring = "  select elemoperators[$i] ";
   $listobject->querystring .= " from scen_model_run_elements ";
   $listobject->querystring .= " where elementid = $elementid ";
   $listobject->querystring .= "    and runid = $cache_runid ";
   $listobject->performQuery();
   $thisxml = $listobject->getRecordValue(1,'elemoperators');
   $retval['xml'] = $thisxml;
   $retval['query'] = $listobject->querystring;
   return $retval;

}

function getOperatorXML($listobject, $elementid, $i) {
   //error_log("Reached getOperatorXML ");
   $retval = array();
   $listobject->querystring = "  select elemoperators[$i] ";
   $listobject->querystring .= " from scen_model_element ";
   $listobject->querystring .= " where elementid = $elementid ";
   $listobject->performQuery();
   $thisxml = $listobject->getRecordValue(1,'elemoperators');
   $retval['xml'] = $thisxml;
   $retval['query'] = $listobject->querystring;
   return $retval;
}

function checkObjectCacheStatus($listobject, $elementid, $order, $cache_level, $cache_id, $current_level, $model_startdate, $model_enddate, $debug=0) {
   global $unserobjects;
   // cache_level = 0 means all except the top-most parent will be run as cached time series (if possible)
   // cache_level = 1 means all except the immediate children of this object
   // if cache_level >= 0 and current_level >= cache_level then we go ahead and make time series out of these
   //   cached time series characteristics
   // current level begins as -1 since the top-most object cannot be run from cache
   // if cache_level >= 0 then cache_level will still be greater than the first level at the first object
   // otherwise, we proceed along normal lines
   
   $cache_file_exists = 0;
   $returnArray = array('error'=>'', 'cache_type'=>'', 'cache_file_exists'=>0);
   // now, check if this run has been requested with model data caching on (cache_level >= 0)
   // can also perform a check on the cache based on run-date.  If cache_level is a date, then 
   // we use that logic instead
   if ( $cache_level <> -1) {
      $returnArray['error'] .= "Evaluating intval($cache_level) = " . intval($cache_level) . "<br>";
      //if (intval($cache_level) === $cache_level) {
      if ( !(strtotime($cache_level)) ) {
         // cache_level is an integer
         $cache_type = 'level';
         $returnArray['error'] .= "Cache level is an integer<br>";
      } else {
         // cache_level is a date
         $cache_type = 'date';
         $returnArray['error'] .= "Cache level is a date<br>";
      }
   } else {
      $returnArray['error'] .= "Cache level is -1, forcing disabled<br>";
      $cache_type = 'disabled';
   }
   //error_log("Checking for $elementid - cache_level = $cache_level, cache_type = $cache_type, cache_id = $cache_id, current_level = $current_level <br>");
   $returnArray['error'] .= "Checking element order for $elementid .<br>";
   
   switch ($cache_type) {
      case 'level':
      if ($current_level >= $cache_level) {
         $cache_file = '';
         // check for cached file
         $cache_sql = "  select a.elemname, b.output_file, b.remote_url, b.host from scen_model_run_elements as b, scen_model_element as a ";
         $cache_sql .= " where a.elementid = $elementid and b.elementid = $elementid ";
         $cache_sql .= "    and b.runid = $cache_id ";
         $returnArray['error'] .= "Current level $current_level > cache Level $cache_level - querying database for cache file: $cache_sql .<br>";
      } else {
         $cache_type = 'disabled';
         //error_log("Cacheable Check: $cacheable = getElementCacheable(listobject, $elementid) <br>");
         $returnArray['error'] .= "Current level $current_level is less than cache Level $cache_level - cacheing disabled.<br>";
      }
      break;
      
      case 'date':
         $cache_file = '';
         // check for cached file
         $cache_sql = "  select a.elemname, b.output_file, b.remote_url, b.host from scen_model_run_elements as b, scen_model_element as a ";
         $cache_sql .= " where a.elementid = $elementid and b.elementid = $elementid ";
         $cache_sql .= "    and b.runid = $cache_id ";
         $cache_sql .= "    and b.run_date >= '$cache_level' ";
         $cache_sql .= "    and b.starttime <= '$model_startdate' ";
         $cache_sql .= "    and b.endtime >= '$model_enddate' ";
      break;
      
   }

   
   // check to see if we have manually defined this as uncacheable
   $cacheable = getElementCacheable($listobject, $elementid);
   // need to check to see if this is a container with children, if it is NOT we do not want to run it as cached
   if ($cacheable <> 3) {
      // level 3 allows for persistent caching of 0th level objects
      if ( ($order == 0) or (! ($cacheable == 1))) {
         $cache_type = 'disabled';
         $returnArray['error'] .= "$elementid is a $order'th order element, 'cacheable' setting = $cacheable - cacheing disabled.<br>";
      }
   }
   //error_log("Cacheable Check: $cacheable = getElementCacheable(listobject, $elementid) <br>");
   $cache_file_exists = 0;
   //if ($cache_type <> 'disabled') {
      // verify that the file exists
      $returnArray['error'] .= $cache_sql . "<br>";
      $listobject->querystring = $cache_sql;
      $listobject->performQuery();
      if ($listobject->numrows > 0) {
         // BEGIN - new method
         // use new log file retrieval routine
         $file_host = $listobject->getRecordValue(1,'host');
         if ($file_host <> $serverip) {
            $cache_file = $listobject->getRecordValue(1,'remote_url');
         } else {
            $cache_file = $listobject->getRecordValue(1,'output_file');
         }
         // END - new method
         // old method of file retrieval, broke when spanning hosts
         //$cache_file = $listobject->getRecordValue(1,'output_file');
         $fe = fopen($cache_file,'r');
         $file_size = filesize($cache_file);
         //if ($fe and ($file_size > 0)) {
         if ($fe) {
            $cache_file_exists = 1;
            fclose($fe);
            $returnArray['error'] .= "Found Cache file $cache_file for $elementid.<br>";
         } else {
            $cache_type = 'disabled';
            $returnArray['error'] .= "Cache file $cache_file does not exist (Exists: $fe and Size: $file_size ) - cacheing disabled.<br>";
         }
      } else {
         $cache_type = 'disabled';
         $returnArray['error'] .= "Cache file $cache_file not found - cacheing disabled.<br>";
      }
   //}
      
   $returnArray['error'] .= " cache_level = $cache_level, current_level = $current_level, cache_file_exists = $cache_file_exists, cache_type = $cache_type, number of components = " . count($unserobjects) . "<br>";
   $returnArray['cache_type'] = $cache_type;
   $returnArray['cache_file_exists'] = $cache_file_exists;
   $returnArray['cacheable'] = $cacheable;
   if ($debug) {
      error_log($returnArray['error']);
   }
   return $returnArray;
}

function getInputLinkages($listobject, $elementid, $linktypes = array(2,3)) {

   $listobject->querystring = "  select src_id, src_prop, dest_prop, linktype ";
   $listobject->querystring .= " from map_model_linkages ";
   $listobject->querystring .= " where dest_id = $elementid ";
   //$listobject->querystring .= "    and linktype = 2 ";
   $listobject->querystring .= "    and linktype in ( " . join (',', $linktypes) . " ) ";
   # screen out non-set objects so as to avoid an error
   #$listobject->querystring .= "    and src_id <> -1 ";
   if ($debug) {
      $returnArray['debug'] .= " $listobject->querystring ; <br>";
   }
   $listobject->performQuery();

   $linkrecs = $listobject->queryrecords;
   return $linkrecs;
}

function loadCachedObject($model_listobj, $elementid, $runid, $debug) {
   global $listobject, $outdir, $outurl, $goutdir, $gouturl, $tmpdir, $basedir, $shellcopy, $ucitables, $unserobjects;
   $returnArray = array('error'=>'', 'debug'=>'', 'object'=>null, 'tableinfo'=>array() );
   //error_log("Creating cached object for $elementid <br>");
   if ($debug) {
      $returnArray['debug'] .= "Looking for cached copy of element $elementid <br>";
   }
   
   $returnArray['error'] .= "Retrieving cached copy of element $elementid <br>";
   // get a standalone copy (no children) of the element to be run from cache
   $obres = unSerializeSingleModelObject($elementid, array(), 0, $model_listobj);
   $src_object = $obres['object'];
   // create a blank timeseriesfile object
   $thisobject = new timeSeriesFile;
   // retrieve the file name (this routine determines if it is local or remote)
   $tableinfo = getSessionTableNames($thisobject, $elementid, $runid);
   $returnArray['tableinfo'] = $tableinfo;
   $cache_file = $tableinfo['filename'];
   // set the timeseries file to the cache file
   $thisobject->filepath = $cache_file;
   $thisobject->setCompID($elementid);
   $thisobject->name = $src_object->name;
   $thisobject->listobject = $model_listobj;
   $thisobject->debug = $src_object->debug;
   //$thisobject->debug = 1;
   //$thisobject->debugmode = 1;
   if (method_exists($thisobject, 'wake')) {
      $thisobject->wake();
   }
   $thisobject->logDebug("Instantiating $thisobject->name as a cached time series from $cache_file<br>");
   #$returnArray['debug'] .= "<b>Original object debug status: </b>" . $thisobject->debug . "<br>";
   #$thisobject->debug = $debug;
   $thisobject->outdir = $outdir;
   $thisobject->outurl = $outurl;
   $thisobject->goutdir = $goutdir;
   $thisobject->gouturl = $gouturl;
   $thisobject->tmpdir = $tmpdir;
   $thisobject->max_memory_values = 500; // sets the default maximum memory values for these objects
   $thisobject->basedir = $basedir;
   $thisobject->shellcopy = $shellcopy;

   // copy any broadcast to parent objects in the original object to the timeseries
   $j = 0;
   $thisobject->logDebug("Looking for Broadcase sub-comps on $thisobject->name - all other sub-comps run as cached<br>");
   foreach ($src_object->processors as $proc_name => $proc_object) {
      if (is_object($proc_object)) {
         $thisobject->logDebug("Found $proc_name ... ");
         if (get_class($proc_object) == 'broadCastObject') {
            $thisobject->logDebug(" is broadCastObject ... ");
            if ( ($proc_object->broadcast_hub == 'parent') and ($proc_object->broadcast_mode == 'cast') ) {
               $thisobject->logDebug(" running live.<br> ");
               if (property_exists($proc_object, 'listobject')) {
                  if ($debug) {
                     $returnArray['debug'] .= "Setting a listobject object on this object.<br>";
                  }
                  $proc_object->listobject = $model_listobj;
               }
               if (property_exists($proc_object, 'ucitables')) {
                  if ($debug) {
                     $returnArray['debug'] .= "Setting a ucitables object on this object.<br>";
                  }
                  $proc_object->ucitables = $ucitables;
               }
               if (method_exists($proc_object, 'wake')) {
                  $proc_object->wake();
               }
               $proc_object->outdir = $outdir;
               $proc_object->outurl = $outurl;
               $proc_object->goutdir = $goutdir;
               $proc_object->gouturl = $gouturl;
               # manually set the componentid, since these do not have a db generated ID, and they only exist in
               # a scope that is local to the containing object, set them to be a decimal on the parent elementid
               $proc_object->componentid = "$elementid" . "." . $j;

               $thisobject->addOperator($proc_name, $proc_object, 0);
               $returnArray['error'] .= "Adding parent cast $proc_name for $elementid <br>";
               $j++;
            }
         } else {
            $thisobject->logDebug(get_class($proj_object) . " is not broadcast object .<br> ");
         }
      }
   }
   $returnArray['object'] = $thisobject;
   $returnArray['error'] .= "Cached object created for $elementid <br>";
   //error_log("Cached object created for $elementid <br>");
   $unserobjects[$elementid] = $thisobject;
   return $returnArray;
}

function addDynamicChildElements($modeldb, $input_props, $dynamics, $debug=0) {
   global $unserobjects;
   $dyna_cache = array();
   foreach ($dynamics as $thisdyn) {
      $childid = $thisdyn['childid'];
      $parentid = $thisdyn['parentid'];
      // check that the parent exists, if so, instantiate and add the child
      if ($debug) {
         error_log("Looking for $parentid for dynamic object $childid");
      }
      if (isset($unserobjects[$parentid])) {
         if ($debug) {
            error_log("Found parent $parentid for dynamic object $childid");
         }
         if (!isset($unserobjects[$childid])) {
            $thisobresult = unSerializeModelObject($childid, $input_props, $modeldb, -1, -1);
            if (is_object($thisobresult['object'])) {
               $dyna_cache[] = $childid;
               if (!method_exists($unserobjects[$parentid],'addComponent')) {
                  error_log("Error adding $childid to parent object $parentid");
                  if (!isset($unserobjects[$parentid])) {
                     error_log("Parent object $parentid has not been instantiated in unserobjects");
                  } else {
                     error_log("Method addComponent() does not exist  on $parentid " . $unserobjects[$parentid]->name);
                  }
               }
               $unserobjects[$parentid]->addComponent($thisobresult['object']);
               if ($debug) { 
                  error_log("Dynamic object $childid added to $parentid ");
               }
               if ($debug) {
                  error_log("Object $childid contains the following sub-objects: " . print_r($thisobresult['complist'],1) );
               }
               foreach ($thisobresult['complist'] as $compid) {
                  if (!in_array($compid, $dyna_cache) ) {
                     $dyna_cache[] = $compid;
                     if ($debug) {
                        error_log("Adding component $childid to dyna_cache list");
                     }
                  }
               }
            }
         }
      } else {
         if ($debug) {
            error_log("DID NOT FIND parent $parentid for dynamic object $childid");
         }
      }
   }
   return $dyna_cache;
}


function loadModelUsingCached($modeldb, $elementid, $runid, $cache_runid, $input_props, $cache_level, $cache_list, $run_date) {
   global $listobject;
   $retarr = array();
   $retarr['errors'] = '';
   $cache_complete = array();
   // *********************************
   // *** Get Cached Objects       ****
   // *********************************
   
   // iterate through the objects that are requested to be cached and instantiate them
   error_log("Checking the following objects for valid cache entries" . print_r($cache_list,1));
   foreach ($cache_list as $thisel) {
      if (is_array($thisel)) {
         $elid = $thisel['elementid'];
         $crid = $thisel['runid'];
      } else {
         $elid = $thisel;
         $crid = $cache_runid;
      }
      //error_log("Loading cached version of $elid from run $crid");
      // should check cache status here to see if this object should be rerun based on cache_level setting
      // this will allow there to be objects for which cache_runid = runid to use the info from the last run of runid
      // such as upstream objects
      // if cache_runid = -1, we do not intend to use this function (though some may be sent with individual cache requests and should be honored)
      $cacheable = getElementCacheable($listobject, $elid);
      //error_log( "function getElementCacheable(listobject, $elid) returned $cacheable (crid = $crid )");
      //die;
      //if ( ($crid <> -1) and (in_array($cacheable, array(1,3))) ) {
      if ( ($crid <> -1) ) {
      // check cache status, only allow caching if setting is 1 or 3
         $res = loadCachedObject($modeldb, $elid, $crid, $debug);
         $retarr['error'] .= $res['error'];
        error_log("loadCachedObject($elid, $crid) tableinfo:" . print_r($res['tableinfo'],1));
         //copyTreeCacheFiles($elid, $crid, $runid, 0);
         copyTreeCacheFiles($elid, $crid, $runid, 1, 0, $run_date);
         // get all children of this object and copy the cache file into this file
         // copyRunCacheFile($elementid, $src_runid, $dest_runid);
         $cache_complete[] = $elid;
      }
      
   }
   
   // *********************************
   // *** Unserialize Standard Obs ****
   // *********************************
   // then call the normal unserialize routine which will load remaining objects, 
   // using cached copies if they fit the normal criteria
   error_log("Calling unSerializeModelObject($elementid, input_props, modeldb, $cache_level, $runid) ");
   $thisobresult = unSerializeModelObject($elementid, $input_props, $modeldb, $cache_level, $runid);
   $thisobject = $thisobresult['object'];
   $components = $thisobresult['complist'];
   $live = $thisobresult['live'];
   $cachedlist = array_merge($thisobresult['cached'], $cache_complete);
   $errorlog .= "<b>Model Unserialization Errors</b><br>" . $thisobresult['error'] . "<hr>";
   error_log("Sub-Objects that were cached for this run: " . print_r($cachedlist, 1));
   //error_log("Sub-Objects that were instantiated for this run: " . print_r($live, 1));
   $retarr['object'] = $thisobject;
   $retarr['components'] = $components;
   $retarr['cachedlist'] = $cachedlist;
   $retarr['live'] = $live;
   return $retarr;
}

function copyTreeCacheFiles($elementid, $src_runid, $dest_runid, $overwrite = 0, $debug = 0, $run_date = NULL) {
   global $listobject;
   $elements = getTree($listobject, $elementid);
   if ($debug) {
      error_log("Tree with trunk $elementid: " . print_r($elements,1));
   }
   // add code to iterate through each record, get the elementid and copyRunCacheFile
   foreach ($elements as $thisel) {
      copyRunCacheFile($thisel['elementid'], $src_runid, $dest_runid, $overwrite, $run_date);
   }
   error_log("Tree Loaded");
}

function copyRunCacheFile($elementid, $src_runid, $dest_runid, $overwrite = 0, $run_date = NULL) {
   // creates a duplicate run entry file for the dest_runid, using all of the information from the src run record
   global $listobject;
   
   if ($overwrite) {
      removeRunCache($listobject, $elementid, $dest_runid);
   }
   $listobject->querystring = "  insert into scen_model_run_elements (runid, elementid, starttime, endtime, ";
   $listobject->querystring .= " elem_xml, output_file, run_date, host, fullpath, run_summary, run_verified, ";
   $listobject->querystring .= " remote_path, exec_time_mean, verified_date, remote_url, elemoperators, report) ";
   $listobject->querystring .= " select $dest_runid,   elementid, starttime, endtime, elem_xml, output_file, ";
   if ($run_date === NULL) {
      $listobject->querystring .= " now(), host, fullpath, run_summary, run_verified, remote_path, ";
   } else {
      $listobject->querystring .= " '$run_date', host, fullpath, run_summary, run_verified, remote_path, ";
   }
   $listobject->querystring .= " exec_time_mean, verified_date, remote_url, elemoperators, report ";
   $listobject->querystring .= " from scen_model_run_elements ";
   $listobject->querystring .= " where elementid = $elementid ";
   if (!$overwrite) {
      $listobject->querystring .= " and elementid NOT in (select elementid from scen_model_run_elements where elementid = $elementid and runid = $dest_runid ) ";
   }
   $listobject->querystring .= " and runid = $src_runid ";
   //error_log($listobject->querystring);
   $listobject->performQuery();

}

function runCached($elementid, $runid, $cache_runid, $startdate, $enddate, $cache_list, $cache_level, $dynamics, $input_props = array(), $test_only = 0) {
   global $modeldb, $listobject, $outdir, $serverip;
   $run_date = date('r');
   if (!is_array($cache_list)) {
      $cache_list = explode(',', $cache_list);
   }
   // this routine, if passed empty cache_list and dynamics list will simply perform a normal model run
   if (is_array($elementid)) {
      if (count($elementid) > 0) {
         $firstel = $elementid[0];
      } else {
         $firstel = -1;
      }
   } else {
      $firstel = $elementid;
   }
  $input_props['outdir'] = isset($input_props['outdir']) ? $input_props['outdir'] : $outdir;

  if ($elementid > 0) {
    if ( (strlen($startdate) > 0) and (strlen($enddate) > 0)) {
       $input_props['model_startdate'] = $startdate;
       $input_props['model_enddate'] = $enddate;
    }
    
    setStatus($listobject, $firstel, "Initiating Model Run for Elementid $firstel with runCached() function", $serverip, 1, $runid, -1, 1);
    
    // load up all of the things that are in the base model, with caching specified
    error_log("Calling loadModelUsingCached(modeldb, $elementid, $runid, $cache_runid with cache_level = $cache_level");
    $model_elements = loadModelUsingCached($modeldb, $elementid, $runid, $cache_runid, $input_props, $cache_level, $cache_list, $run_date);
    $error = $model_elements['error'];
    $thisobject = $model_elements['object'];
    $components = $model_elements['components'];
    $cachedlist = $model_elements['cachedlist'];
    $live = $model_elements['live'];
    if ($test_only) {
       error_log("Live running list: " . print_r($live,1));
       error_log("Cached running list: " . print_r($cachedlist,1));
       error_log("All components running list: " . print_r($components,1));
    }
    // if we did not pass in a start and enddate, grab it from the parent object now
    if ( (strlen($startdate) == 0) and (strlen($enddate) == 0)) {
       $startdate = $thisobject->starttime;
       $enddate = $thisobject->endtime;
    }
    // *********************************
    // *** Dynamic Inserts Now      ****
    // *********************************
    // now unserialize and add any dynamically inserted objects
    $dyna_cache = addDynamicChildElements($modeldb, $input_props, $dynamics, $test_only);
    //error_log("The following elements were loaded dynamically into the simulation " . print_r($dyna_cache,1));
    // ask this to report status for children
    // this version will do reports for all children and chidlren of children
    //$thisobject->childstatus = $dyna_cache;
    // this version just does the dynamic parents requested
    $thisobject->childstatus = extract_arrayvalue($dynamics, 'childid');
    //error_reporting(E_ALL);
    if (isset($input_props['dt'])) {
      $thisobject->dt = $input_props['dt'];
    }
    if (!$test_only) {
       $meanexectime = performRun($listobject, $thisobject, $startdate, $enddate, $runid);
    }
    // store the model run data
    if ($cache_runid <> $runid) {
       //error_log("$cache_runid <> $runid - logging all components \n");
       // go ahead and store a copy of all run data for this if it will not overwrite
       // the source run "cache_runid"
       //$log_components = array_merge(array($elementid),$live, $dyna_cache);
       $log_components = array_unique(array_merge(array($elementid),$live, $dyna_cache, $components));
    } else {
       // Just store the elements that were actually run live 
       $log_components = array_merge($live, $dyna_cache);
    }
    //error_log("Model Run Data to be stored for " . print_r($log_components,1));
    if (!$test_only) {
       storeElementRunData($listobject, $elementid, $log_components, $runid, $run_date, $startdate, $enddate, $meanexectime, 0);
    }
    // stash the run info and create summary files for the desired dynamics:
    if ($cache_runid <> $runid) {
       // create a cache entry for all objects in this simulation, whether they ran live or cached
       $summarize = array_unique(array_merge(array($elementid), $dyna_cache));
    } else {
       //error_log("($cache_runid <> $runid)");
       $summarize = $dyna_cache;
       $summarize = array_unique(array_merge(array($elementid), $dyna_cache));
    }
    //error_log("Creating run summary files for " . print_r($summarize,1));
    if (!$test_only) {
       // cache only the object of interest and the parent object
       // @todo: this was disabled for some reason?
       createModelRunSummaryFiles($listobject, $summarize, $runid);
    }
    return;
  }
}



function shakeTreeCached($listobject, $sip, $num_sim, $recid, $run_id, $startdate, $enddate, $cache_date, $debug = 0, $strict=1) {
   //error_log("strictness setting: $strict \n");
   // Algorithm
   // check to see if this object is allowed to run standalone - if not return 1
   //checkTreeRunDate
   //if LRD of tree is > CD, return 1, else
   //if this element is currently running (runstate = 1 or 2), return 0, else
   //set runStatus = 3 (queued but not yet running)
   //Iterate through directly linked children call shakeTree
   //If shakeTree any child returns 0, return 0, else
   //If num_running < max_simultaneous, Run this element, return 0
   /*
   $solo_check = checkStandAlone($listobject, $recid);
   if (!$solo_check) {
      // this can't run solo, so we assume that it MUST be run as a part of its parent container
      // thus we return true, so it will be ignored in this batch, but run as part of its parent
      removeRunCache($listobject, $recid, $run_id);
      return 1;
   }
   */
   $elemname = getElementName($listobject, $recid);
   $cacheable = getElementCacheable($listobject, $recid);
   if ($debug) {
      error_log("Element $elemname ($recid) Cacheable Mode - $cacheable \n");
   }
   switch ($cacheable) {
      case 0:
      //error_log("Item can not be cached, returning 1 \n");
      return 1;
      break;
      
      case 2:
      // proceed on, but since this object can not be cached by itself, it will return 1 when it gets to the 
      // step to be run
      //error_log("Item permits pass-through caching, checking for cacheable/runnable children \n");
      break;
      
      default:
      // proceed on, this is a fully stand-alone object, capable of separate running and caching
      //error_log("Item permits full caching, checking last run date \n");
      break;
      
   }
   summarizeRun($listobject, $recid, $run_id, $startdate, $enddate, 0, $strict);
   
   $tree_check = checkTreeRunDate($listobject, $recid, $run_id, $startdate, $enddate, $cache_date, $debug);
   if ($debug) {
      error_log("Tree Check - $tree_check : checkTreeRunDate(listobject, $recid, $run_id, $startdate, $enddate, $cache_date);\n");
   }
   if ($tree_check) {
      // this tree has been run since the cache_date - nothing to do!
      return 1;
   }
   $running = array(1,2);
   $status_vars = verifyRunStatus($listobject, $recid, $run_id, $sip);
   $status = $status_vars['status_flag'];
   if ($debug) {
      error_log("Run status Check -  $status \n");
   }
   if (in_array($status, $running)) {
      // this element is currently running, return 0 (not finished)
      return 0;
   }
   // set status to 3
   if ($status <> 3) {
      if ($cacheable <> 2) {
         //setStatus($listobject, $recid, "Element $recid Queued for Run", $sip, 3, $run_id);
      } else {
         //setStatus($listobject, $recid, "Searching pass-through cache $recid for children", $sip, 0, $run_id);
      }
   }
   $children = getNextContainers($listobject, $recid);
   $child_status = 1;
   foreach ($children as $thischild) {
      $childid = $thischild['elementid'];
      $check = shakeTreeCached($listobject, $sip, $num_sim, $childid, $run_id, $startdate, $enddate, $cache_date, 0, $strict);
      $child_status = $child_status & $check;
      if ($debug) {
         error_log("Result of shakeTree($childid) - $check / (group status - $child_status) \n");
      }
   }
   
   if (!$child_status) {
      //error_log("Children of object $recid currently running - returning \n");
      return 0;
   }
   if ($cacheable == 2) {
      // this is a special type of object, which can not be cached, but may contain cacheable children
      // since we reached this step, all of its children have been verified so we go home
      //error_log("Object $recid is un-cacheable, all children run or cached - returning \n");
      return 1;
   } 

   $active_models = returnPids('php');
   $num_running = count($active_models);
   if ($num_running < $num_sim) {
      // spawn a new one
      $prop_array = array('run_mode' => $run_id, 'debug' => 0, 'cascadedebug' => 1);
      updateObjectProps($projectid, $recid, $prop_array);
      $run_params = array();
      $run_params['elements'] = $recid;
      $run_params['runid'] = $run_id;
      $run_params['startdate'] = $startdate;
      $run_params['enddate'] = $enddate;
      $run_params['cache_level'] = $cache_date;
      // check status one last time just in case another thread has called this one in the interim
      $status_update = verifyRunStatus($listobject, $recid);
      $recent_status = $status_update['status_flag'];
      if (!in_array($recent_status, $running)) {
         error_log("Forking $recid \n");
         //setStatus($listobject, $recid, "Model Run for Element $recid Forked", $sip, 1, $run_id);
         //runCOVAProposedWithdrawal ($prop_elid, $runid, $cache_runid, $startdate='', $enddate='', $cache_level = -1, 1);
         // can use runcovaproposed, but only need to use it for the actual proposed withdrawal, so maybe this should take place outside of the run of the VWP proposed object?
         // maybe we should run the VWP object from the VWP interface, then do a custom runcached?
         // or, since runCOVAProposedWithdrawal is now set to be optional in terms of the "locid" custom parent
         // just call it iteratively on the whole tree?
         forkRun($run_params);
      }
      //if ($debug) {
         error_log("Model Run for Element $recid Forked \n");
         error_log("With parameters: " . print_r($run_params,1) . " \n");
      //}
      deleteRunRecord($listobject, $recid, $run_id);
   }
   return 0;
   
}

function createModelRunSummaryFiles($listobject, $elementlist, $runid = -1) {
   global $unserobjects;
   foreach ($elementlist as $thisel) { 
      if (isset($unserobjects[$thisel])) {
         $thisobject = $unserobjects[$thisel];
         createSingleModelRunSummaryFile($listobject, $thisobject, $thisel, $runid);
      }
   }
}

function createSingleModelRunSummaryFile($listobject, $thisobject, $elementid, $runid = -1) {
   global $outdir, $outurl;
   if (is_object($thisobject)) {
      $debugstring = '';
      error_log("Assembling Panels.");
      $report = $thisobject->outstring . " <br>";
      $errorlog .= '<b>Model Execution Errors:</b>' . $thisobresult['error'] . " <br>";
      if (strlen($thisobject->errorstring) <= 4096) {
         $errorlog .= $thisobject->errorstring . " <br>";
      } else {
         //error_log("Writing errors to file.");
         # stash the debugstring in a file, give a link to download it
         $fname = 'error' . $thisobject->componentid . ".html";
         $floc = $outdir . '/' . $fname;
         $furl = $outurl . '/' . $fname;
         $fp = fopen ($floc, 'w');
         fwrite($fp, "Component Logging Info: <br>");
         fwrite($fp, $thisobject->errorstring . " <br>");
         $errorlog .= "<a href='$furl' target=_new>Click Here to Download Model Error Info</a>";
      }
      $report .= "Component Logging Info: <br>";
      $report .= $thisobject->reportstring . " <br>";
      # stash the debugstring in a file, give a link to download it
      $report .= "Finished.";
      $fname = 'report' . $thisobject->componentid . "-$runid" . ".log";
      $floc = $outdir . '/' . $fname;
      error_log("Writing reports to file $floc ");
      $furl = $outurl . '/' . $fname;
      $fp = fopen ($floc, 'w');
      fwrite($fp, "Component Logging Info:\n");
      fwrite($fp, $report . "\n");
      
      $debuglog .= $thisobresult['debug'] . " <br>";
      $debuglog .= $thisobject->debugstring . '<br>';
      // need to generate the tabbed list view in a subroutine
      $innerHTML = "Results Pending";
      $innerHTML = createHTMLModelRunSummary($listobject, $thisobject);
      error_log("Storing $elementid model output in database");
      $listobject->querystring = "  update scen_model_element set output_cache = '" . addslashes($innerHTML) . "'";
      $listobject->querystring .= " where elementid = $elementid ";
      $listobject->performQuery();
      //error_log("$listobject->querystring");
      //error_log("Storing model run data in scen_model_run_elements for $elementid ");
      // and a unique runid specifier 
   }
}

function createHTMLModelRunSummary($listobject, $thisobject) {
   global $outdir, $outurl;
   if (!is_object($thisobject)) {
      error_log("ERROR: object passed to createHTMLModelRunSummary() is not valid - returning");
   } else {
      error_log("createHTMLModelRunSummary() called - summarizing object.");
      # format output into tabbed display object
      $taboutput = new tabbedListObject;
      $taboutput->name = 'modelout';
      $taboutput->tab_names = array('modelcontrol','runlog','graphs','reports','errorlog', 'debug');
      $taboutput->tab_buttontext = array(
      'modelcontrol'=>'Model Controls',
      'runlog'=>'Run Log',
      'graphs'=>'Graphs',
      'reports'=>'Reports',
      'errorlog'=>'Error Log',
      'debug'=>'Debug Info'
      );
      $meanexectime = $thisobject->meanexectime;
      $debugstring = '';
      error_log("Assembling Panels.");
      $taboutput->tab_HTML['runlog'] .= $thisobject->outstring . " <br>";
      $taboutput->tab_HTML['errorlog'] .= '<b>Model Execution Errors:</b>' . $thisobresult['error'] . " <br>";
      if (strlen($thisobject->errorstring) <= 4096) {
         //error_log("Error String shorter than 4096 chars, stashing in db.");
         $taboutput->tab_HTML['errorlog'] .= $thisobject->errorstring . " <br>";
      } else {
         error_log("Writing errors to file.");
         # stash the debugstring in a file, give a link to download it
         $fname = 'error' . $thisobject->componentid . ".html";
         $floc = $outdir . '/' . $fname;
         $furl = $outurl . '/' . $fname;
         $fp = fopen ($floc, 'w');
         fwrite($fp, "Component Logging Info: <br>");
         fwrite($fp, $thisobject->errorstring . " <br>");
         $taboutput->tab_HTML['errors'] .= "<a href='$furl' target=_new>Click Here to Download Model Error Info</a>";
         error_log("Error File written for $thisobject->componentid to $floc / $furl ");
      }
      if (strlen($thisobject->reportstring) <= 4096) {
         //error_log("Report String shorter than 4096 chars, stashing in db.");
         $taboutput->tab_HTML['reports'] .= "Component Logging Info: <br>";
         $taboutput->tab_HTML['reports'] .= $thisobject->reportstring . " <br>";
      } else {
         error_log("Writing reports to file.");
         # stash the debugstring in a file, give a link to download it
         $fname = 'report' . $thisobject->componentid . ".html";
         $floc = $outdir . '/' . $fname;
         $furl = $outurl . '/' . $fname;
         $fp = fopen ($floc, 'w');
         fwrite($fp, "Component Logging Info: <br>");
         fwrite($fp, $thisobject->reportstring . " <br>");
         $taboutput->tab_HTML['reports'] .= "<a href='$furl' target=_new>Click Here to Download Model Reporting Info</a>";
         error_log("report File written for $thisobject->componentid to $floc / $furl ");
      }
      if (strlen($thisobject->graphstring) <= 1024) {
         //error_log("Graph String shorter than 1024 chars, stashing in db.");
         $taboutput->tab_HTML['graphs'] .= "<img src='' id='image_screen' height=400 width=600>";
         $taboutput->tab_HTML['graphs'] .= "<div id='view_box' style=\"border: 1px solid rgb(0 , 0, 0); border-style: dotted; overflow: auto; height: 180; width: 624; display: block;  background: #eee9e9;\">";
         $taboutput->tab_HTML['graphs'] .= $thisobject->graphstring . " <br>";
         $taboutput->tab_HTML['graphs'] .= "</div>";
      } else {
      error_log("Writing graph output to file.");
         # stash the debugstring in a file, give a link to download it
         $fname = 'graph' . $thisobject->componentid . ".html";
         $floc = $outdir . '/' . $fname;
         $furl = $outurl . '/' . $fname;
         $fp = fopen ($floc, 'w');
         fwrite($fp, $thisobject->graphstring);
         $taboutput->tab_HTML['graph'] .= "<a href='$furl' target=_new>Click Here to Download Graphs Info</a>";
      }

      if (strlen($thisobject->debugstring) <= 4096) {
         //error_log("Debug String shorter than 4096 chars, stashing in db.");
         $taboutput->tab_HTML['debug'] .= $thisobresult['debug'] . " <br>";
         $taboutput->tab_HTML['debug'] .= $thisobject->debugstring . '<br>';
      } else {
         error_log("Writing debug output to file.");
         # stash the debugstring in a file, give a link to download it
         $fname = 'debug' . $thisobject->componentid . ".html";
         $floc = $outdir . '/' . $fname;
         $furl = $outurl . '/' . $fname;
         $fp = fopen ($floc, 'w');
         fwrite($fp, $thisobresult['debug'] . " <br>");
         fwrite($fp, $thisobject->debugstring . '<br>');
         $taboutput->tab_HTML['debug'] .= "<a href='$furl' target=_new>Click Here to Download Debug Info</a>";
         error_log("Debug File written for $thisobject->componentid to $floc / $furl ");
      }

      $taboutput->tab_HTML['runlog'] .= "Finished.<br>";
      error_log("Creating output in html form.");
      $taboutput->createTabListView();
      $innerHTML .= $taboutput->innerHTML . "</div>";
      error_log("Storing $elementid model output in database");
      $listobject->querystring = "  update scen_model_element set output_cache = '" . addslashes($innerHTML) . "'";
      $listobject->querystring .= " where elementid = $elementid ";
      $listobject->performQuery();
      //error_log("$listobject->querystring");
   }
   $taboutput->createTabListView();
   return $taboutput->innerHTML;
}
 
function performRun($systemlog_obj, $thisobject, $startdate, $enddate, $runid) {
   global $outdir, $outurl, $serverip;
   $thisname = $thisobject->name;
   $thisobject->outdir = $outdir;
   $thisobject->outurl = $outurl;
   if ( (strlen($startdate) > 0) and (strlen($enddate) > 0)) {
      $thisobject->starttime = $startdate;
      $thisobject->endtime = $enddate;
      error_log("Setting Start and End Date for model to $startdate - $enddate \n");
   } else {
      $startdate = $thisobject->starttime;
      $enddate = $thisobject->endtime;
   }
   // set the model system log to be the parent model run database 
   // this will insure that all of the run status messages go to a central place
   $thisobject->modelhost = $serverip;
   $thisobject->runid = $runid;
   $thisobject->systemlog_obj = $systemlog_obj;
   $debuglog .= "Model Debug Status: " . $thisobject->debug . "<br>";
   $runlog .= "Running component group: $thisname <br>";
   
   if (method_exists($thisobject, 'runModel')) {
      $thisobject->runModel();
   } else {
      error_log("runModel() is not defined on object $thisobject->name of class: " . get_class($thisobject));
   }
   $meanexectime = $thisobject->meanexectime;
   error_log("runModel() Returned from calling routine.");
   return $meanexectime;
}

function runTree($listobject, $elementid, $sleep_factor, $max_simultaneous, $startdate, $enddate, $thisid, $cache_date, $prop_array = array()) {
   global $php_exe, $basedir, $outdir;
   $heap = getNestedContainers($listobject, $elementid);
   $batch_ptr = fopen ("./batch_$elementid" . ".$thisid" . ".out", 'w');
   //$batch_ptr = fopen ("$outdir/batch_$elementid" . ".$thisid" . ".out", 'w');
   //print("Heap: " . print_r($heap,1) . "<br>\n");
   $running = array();
   $checked = array();
   $current_order = 1;
   while ( count($heap) > 0 ) {
      // if it is a 1st order segment, we should have no need to re-run it
      $thisrec = array_shift($heap);
      $recid = $thisrec['elementid'];
      $order = $thisrec['order'];
      //print("Checking $recid order $order<br>\n");
      // need to check to see if we need to increment the value of $current_order
      if (in_array($recid, $checked)) {
         // if it is already been checked at this level, we go ahead and increment the order level
         fwrite($batch_ptr, "Ran " . count($checked) . " of Order $current_order \n");
         $current_order++;
         $checked = array();
         // now we have to wait for all members of the previous level to complete
         while (count($running) > 0) {
            foreach ($running as $thisone) {
               $still_running = array();
               $status = verifyRunStatus($listobject, $thisone, 3600);
               if ( !in_array($status, array(-1,0)) ) {
                  $still_running[] = $thisone;
               }
               if ( ($status == -1) and $abort_zombie ) {
                  fwrite($batch_ptr, "Member $thisone died during execution - batch job aborted.<br>\n");
                  fclose($batch_ptr);
                  return;
               }
               if ( $status == 0 ) {
                  fwrite($batch_ptr, "Member $thisone completed successfully.<br>\n");
               }
            }
            sleep($sleep_factor);
            $running = $still_running;
         }
      }
      // check to see if it is on the order that we are on
      if ($order == $current_order) {
         //print("$order = $current_order - chcking run date<br>\n");
         // run it, unless it is 1st order, AND it has existing files for each of the runs that we want

         // check to see if it has already been run since the cache date, or if it is the parent container
         if (!checkRunDate($listobject, $recid, $thisid, $cache_date) or ($thisid == $elementid)) {
            $waiting = 1;
            while ( $waiting )  {
               $active_models = returnPids('php');
               $num_active = count($active_models);
               //print("Only $num_active \n");
               if ($num_active < $max_simultaneous) {
                  // spawn a new one
                  updateObjectProps($projectid, $recid, $prop_array);
                  $arrOutput = array();
                  
                  $command = "$php_exe -f $basedir/test_modelrun.php $recid $startdate $enddate $thisid $cache_date";
                  fwrite($batch_ptr, "Spawning process for $recid, run # $thisid, Order: $current_order <br>\n");
                  fwrite($batch_ptr, "$command > /dev/null &\n");
                  error_log("$command > /dev/null &");
                  $forkout = exec( "$command > /dev/null &", $arrOutput );
                  $waiting = 0;
                  $running[] = $recid;
               }
               sleep($sleep_factor);   
            }
         } else {
            fwrite($batch_ptr, "Member $recid already run since $cache_date - skipping.<br>\n");
         }

      } else {
         // stick it back on the heap
         $heap[] = $thisrec;
         $checked[] = $recid;
      }
   }
   // include this afterwards so that we follow the batch run till the end
   while (count($running) > 0) {
      foreach ($running as $thisone) {
         $still_running = array();
         $status = verifyRunStatus($listobject, $thisone, 3600);
         if ( !in_array($status, array(-1,0)) ) {
            $still_running[] = $thisone;
         }
         if ( ($status == -1) and $abort_zombie ) {
            fwrite($batch_ptr, "Member $thisone died during execution - batch job aborted.<br>\n");
            fclose($batch_ptr);
            return;
         }
         if ( $status == 0 ) {
            fwrite($batch_ptr, "Member $thisone completed successfully.<br>\n");
         }
      }
      sleep($sleep_factor);
      $running = $still_running;
   }
   fclose($batch_ptr);
   fwrite($batch_ptr, "Batch run for parent $elementid completed successfully.<br>\n");
   fwrite($batch_ptr, "Done.<br>\n");
}

?>