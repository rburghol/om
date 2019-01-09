<?php

# xajax based library - watersupply
require("xajax_watersupply.common.php");

if (!$noajax) {
   $xajax->processRequest();
}

function showPlanningForm($formValues) {
   global $libpath, $projectid, $adminsetuparray, $planpages, $userid;
   if (strlen($formValues['seglist']) > 0) {
      $seglist = $formValues['seglist'];
   } else {
      $seglist = 'NULL';
   }
   #$subseglist = '1000';
   include("adminsetup.php");
   $objResponse = new xajaxResponse();
   $controlHTML = planningForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   return $objResponse;
}

function showDroughtIndicatorForm($formValues) {
   $objResponse = new xajaxResponse();
   $controlHTML = droughtIndicatorForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   return $objResponse;
}

function showDroughtIndicatorResult($formValues) {
   $objResponse = new xajaxResponse();
   $controlHTML = droughtIndicatorForm($formValues);
   $innerHTML = droughtIndicatorResult($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   $objResponse->assign("workspace","innerHTML",$innerHTML);
   return $objResponse;
}

function showFlowZoneForm($formValues) {
   $objResponse = new xajaxResponse();
   $controlHTML = flowZoneForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   return $objResponse;
}

function showFlowZoneResult($formValues) {
   $objResponse = new xajaxResponse();
   $innerHTML = flowZoneResult($formValues);
   $controlHTML = flowZoneForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   $objResponse->assign("workspace","innerHTML",$innerHTML);
   return $objResponse;
}

function showCreateFlowForm($formValues) {
   # synthetic hydrograph method
   include_once('./xajax_watersupply.synthflow.php');
   $objResponse = new xajaxResponse();
   $controlHTML = flowCreateForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   $objResponse->assign("workspace","innerHTML",'');
   return $objResponse;
}

function doCreateFlow($formValues) {
   include_once('./xajax_watersupply.synthflow.php');
   $objResponse = new xajaxResponse();
   $innerHTML = createSyntheticFlow($formValues);
   $controlHTML = flowCreateForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   $objResponse->assign("workspace","innerHTML",$innerHTML);
   return $objResponse;
}

function showAnnualReportingForm($formValues) {
   $objResponse = new xajaxResponse();
   $controlHTML = annualReportingForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   return $objResponse;
}

function showSingleAnnualReportingForm($formValues) {
   $objResponse = new xajaxResponse();
   $controlHTML = annualReportingForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   return $objResponse;
}

function showAnnualReportingFormResult($formValues) {
   global $basedir;
   $objResponse = new xajaxResponse();
   $innerHTML = annualReportingFormResult($formValues);
   $controlHTML = annualReportingForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   $objResponse->assign("workspace","innerHTML",$innerHTML);
   return $objResponse;
}

function showAnnualDataCreationForm($formValues) {
   $objResponse = new xajaxResponse();
   $controlHTML = annualDataCreationForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   return $objResponse;
}

function showAnnualDataCreationResult($formValues) {
   global $basedir;
   $objResponse = new xajaxResponse();
   $innerHTML = annualDataCreationResult($formValues);
   $controlHTML = annualDataCreationForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   $objResponse->assign("workspace","innerHTML",$innerHTML);
   return $objResponse;
}

function showWithdrawalForm($formValues) {
   $objResponse = new xajaxResponse();
   $controlHTML = withdrawalForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   return $objResponse;
}

function showWithdrawalResult($formValues) {
   $objResponse = new xajaxResponse();
   $innerHTML = withdrawalResult($formValues);
   $controlHTML = withdrawalForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   $objResponse->assign("workspace","innerHTML",$innerHTML);
   return $objResponse;
}

function showWithdrawalInfoForm($formValues) {
   $objResponse = new xajaxResponse();
   $controlHTML = withdrawalInfoForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   return $objResponse;
}

function showWithdrawalInfoResult($formValues) {
   $objResponse = new xajaxResponse();
   $innerHTML = withdrawalInfoResult($formValues);
   $controlHTML = withdrawalInfoForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   $objResponse->assign("workspace","innerHTML",$innerHTML);
   return $objResponse;
}

function showMeasuringPointForm($formValues) {
   $objResponse = new xajaxResponse();
   $controlHTML = measuringPointForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   return $objResponse;
}

function showMeasuringPointResult($formValues) {
   $objResponse = new xajaxResponse();
   $innerHTML = measuringPointResult($formValues);
   $controlHTML = measuringPointForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   $objResponse->assign("workspace","innerHTML",$innerHTML);
   return $objResponse;
}

function showVWUDSForm($formValues) {
   $objResponse = new xajaxResponse();
   $controlHTML = VWUDSForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   return $objResponse;
}

function showFacilityViewForm($formValues) {
   $objResponse = new xajaxResponse();
   $controlHTML = facilityViewForm2($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   return $objResponse;
}


function VWUDSForm($formValues) {
   global $listobject, $fno, $adminsetuparray, $outdir_nodrive, $outurl, $userid, $usergroupids;

   $controlHTML = '';
   $errorMSG = '';
   $resultMSG = '';
   #$debug = 1;
   $projectid = $formValues['projectid'];
   $search_view = $formValues['search_view'];
   $currentgroup = $formValues['currentgroup'];
   $lreditlist = $formValues['lreditlist'];
   $allgroups = $formValues['allgroups'];
   $custom_to_file = $formValues['custom_to_file'];

   $tablename = 'annual_data';
   if (isset($formValues['function'])) {
      $function = $formValues['function'];
   } else {
      $function = 'vwudsedit';
   }
   switch ($function) {
      case 'mpedit':
         $tablename = 'vwuds_measuring_point';
      break;

      case 'regionedit':
         $tablename = 'vwuds_deq_regions';
      break;

      case 'facilityedit':
         $tablename = 'facilities';
      break;

      case 'annualedit':
         $tablename = 'annual_data';
      break;

      case 'users':
         $tablename = 'users';
      break;

      default:
         $tablename = 'annual_data';
      break;
   }

   $aset = $adminsetuparray[$tablename];
   $listobject->adminsetup = $aset;
   $pkcol = $adminsetuparray[$tablename]['table info']['pk'];
   $divname = $adminsetuparray[$tablename]['table info']['divname'];
   $form_title = $adminsetuparray[$tablename]['table info']['form_title'];
   if (isset($adminsetuparray[$tablename]['search info']['pk_search_var'])) {
      $pksearch = $adminsetuparray[$tablename]['search info']['pk_search_var'];
   } else {
      $pksearch = $pkcol;
   }
   if (isset($adminsetuparray[$tablename]['table info']['pk_seq'])) {
      $pk_seq = $adminsetuparray[$tablename]['table info']['pk_seq'];
   } else {
      $pk_seq = '';
   }

   # check permissions
   if (isset($formValues[$pkcol])) {
      $pkval = $formValues[$pkcol];
   }
   if (isset($formValues[$pksearch])) {
      $pkval = $formValues[$pksearch];
   }
   
   # set up the search object
   $searchobject = new listObjectSearchForm;
   $searchobject->listobject = $listobject;
   $searchobject->debug = FALSE;
   $searchobject->adminsetup = $aset;
   $searchobject->setVariableNames($formValues);
   
   if (($pkval == '') and ($search_view == 'edit')) {
      # we have an edit requested without a pk, possibly the result of changing
      #$controlHTML .= print_r($formValues,1) . "<br>";
      #$controlHTML .= "No pk value passed, checking search form for hint " . $searchobject->pksearchcol . "<br>";
      #$controlHTML .= print_r($searchobject->searchnames, 1) . "<br>";
      if (isset($formValues[$searchobject->pksearchcol])) {
         $pkval = $formValues[$searchobject->pksearchcol];
         #$controlHTML .=  $searchobject->pksearchcol . " found = $pkval<br>";
      }
   }
      

   $perms = getVWUDSPerms($listobject, $aset, $pkval, $userid, $usergroupids, 1);
   if ($debug) {
      $controlHTML .= print_r($perms, 1) . "<br>";
   }
   $ap = $perms['rowperms'] & 2;
   if ( ($perms['rowperms'] & 2) or ($perms['tableperms'] & 2)) {
      $readonly = 0;
   } else {
      $readonly = 1;
   }
   if ( ($perms['rowperms'] & 1) or ($perms['tableperms'] & 1)) {
      $candelete = 1;
      if ($debug) {
         $controlHTML .= "User has delete permissions on this record.<br>";
      }
   } else {
      $candelete = 0;
      if ($debug) {
         $controlHTML .= "User DOES NOT have delete permissions on this record.<br>";
      }
   }

   if ($perms['tableperms'] & 1) {
      $caninsert = 1;
   } else {
      $caninsert = 0;
   }

   # check for a new record
   if (isset($formValues['searchtype'])) {
      $searchtype = $formValues['searchtype'];
   }
   #$controlHTML .= "Perms returned: " . print_r($perms,1) . "<br> , ReadOnly = $readonly, CanDelete = $candelete<br>";

   # Give the search the record read/write info and call the search form routine.   
   # This will take all of the variables that come in the search and interpret them,
   # returning the record of interest
   $searchobject->insertOK = $caninsert;
   $searchobject->deleteOK = $candelete;
   $searchobject->readonly = $readonly;
   $searchobject->record_submit_script = "xajax_showVWUDSForm(xajax.getFormValues(\"control\")); show_next(\"$divname" . "_data0\", \"$divname" . "_0\", \"$divname" . "\"); ";
   $searchobject->search_submit_script = "xajax_showVWUDSForm(xajax.getFormValues(\"control\")); show_next(\"$divname" . "_data0\", \"$divname" . "_0\", \"$divname" . "\"); ";
   $searchobject->page_submit_script = "xajax_showVWUDSForm(xajax.getFormValues(\"control\")); ";


   # first, check to see if a record "save" has been called
   $blankrec = array();
   if (isset($formValues['searchtype'])) {
      switch ($formValues['searchtype']) {
         case 'save':
            $recordsave = processMultiFormVars($listobject,$formValues,$aset,0,$debug, 0, 1);
            $listobject->querystring = $recordsave['updatesql'];
            if ($debug) {
               $controlHTML .= " SAVE SQL: $listobject->querystring ; <br>";
            }
            if ( ($perms['rowperms'] & 2) or ($perms['tableperms'] & 2)) {
               $listobject->performQuery();
               if ($listobject->error) {
                  $errorMSG .= "<b>Error: </b>" . $listobject->error . "<br>";
               } else {
                  $controlHTML .= "Record Saved.<br>";
                  $resultMSG .= "Record Saved.<br>";
               }
            } else {
               $errorMSG .= "<b>Error: </b>You do not have permission to edit this record.<br>";
            }
         break;

         case 'delete':
            $recordsave = processMultiFormVars($listobject,$formValues,$aset,0,$debug, 0, 1);
            $tablename = $aset['table info']['table_name'];
            $listobject->querystring = "DELETE FROM $tablename ";
            $listobject->querystring .= $recordsave['pkclause'];
            if ($debug) {
               $controlHTML .= " DELETE SQL: $listobject->querystring ; <br>";
            }
            if ($perms['rowperms'] & 2) {
               $listobject->performQuery();
               if ($listobject->error) {
                  $errorMSG .= "<b>Error: </b>" . $listobject->error . "<br>";
               } else {
                  $controlHTML .= "Record Deleted.<br>";
               }
               $formValues = array();
            } else {
               $errorMSG .= "<b>Error: </b>You do not have permission to delete this record.<br>";
            }
         break;

         case 'insert':
            $recordsave = processMultiFormVars($listobject,$formValues,$aset,0,$debug, 0, 1);
            $listobject->querystring = $recordsave['insertsql'];
            if ($debug) {
               $controlHTML .= " INSERT SQL: $listobject->querystring ; <br>";
               $controlHTML .= " Error?: " . $recordsave['error'] . "<br>";
               $controlHTML .= " Error Message: " . $recordsave['errormesg'] . "<br>";
            }
            if ($perms['tableperms'] & 1) {
               if ($recordsave['error'] == 0) {
                  $listobject->performQuery();
                  if ($listobject->error) {
                     $errorMSG .= "<b>Error: </b>" . $listobject->error . "<br>";
                     $blankrec = $formValues;
                     $formValues['searchtype'] = 'new';
                     $searchtype = 'new';
                     $formValues['search_view'] = 'edit';
                  } else {
                     $searchtype = 'browse';
                     $formValues['search_view'] = 'edit';
                     $listobject->querystring = "SELECT currval('$pk_seq') ";
                     $listobject->performQuery();
                     #$listobject->show = 0;
                     #$listobject->showList();
                     #$innerHTML .= "$listobject->outstring <br>";
                     $pkid = $listobject->getRecordValue(1,'currval');
                     if ($pkid > 0) {
                        $formValues[$pkcol] = $pkid;
                        if ($debug) {
                           $controlHTML .= "<b>New PK: </b>" . $pkid . "<br>";
                        }
                        # we need to force the interface to show the just inserted record, since it may
                        # or may not be contained in the search criteria
                        $searchobject->forcePK = $pkid;
                     }
                     $resultMSG .= "Record Inserted with ID $pkid.<br>";
                  }
               } else {
                  $errorMSG .= "<b>Error: </b>" . $recordsave['errormesg'] . "<br>";
                  $blankrec = $formValues;
                  $formValues['searchtype'] = 'new';
                  $formValues['search_view'] = 'edit';
                  $searchtype = 'new';
               }
            } else {
               $errorMSG .= "<b>Error: </b>You do not have permission to edit this record.<br>";
            }
         break;
      }
   }
   #$controlHTML .= "Search View: " . print_r($formValues['search_view'], 1) . " Search type " . $formValues['searchtype'] . "<br>";
   $searchForm = $searchobject->showSearchForm($formValues);

   $prev_record_id = $searchForm['previd'];
   $next_record_id = $searchForm['nextid'];
   $numrecs = $searchForm['numrecs'];
   $page_offset = $searchForm['page_offset'];
   $next_page = $searchForm['next_page'];
   $prev_page = $searchForm['prev_page'];
   $num_pages = $searchForm['num_pages'];
   $currentpos = $searchForm['currentpos'];
   $search_view = $searchForm['search_view'];
   # the first record returned, or the target record if this is the result of a click on the prev/next button
   $props = $searchForm['recordvalue'];
   #$controlHTML .= "Search View: " . $search_view . "<br>";
   #$controlHTML .= "Geom For External: " . $searchForm['geomcollectsql'] . "<br>";

   if ($debug) {
      $controlHTML .= print_r($formValues['search_view'],1) . "<br>" . $formValues['searchtype'] . "<br>";
   }

   $controlHTML .= "<div style=\"border: 1px solid rgb(0 , 0, 0); border-style: dotted; overflow: auto; width: 600px; \">";
   $controlHTML .= "<h3>$form_title</h3><br>";
   $controlHTML .= "<form id=control name=control>";
   # hidden system variables
   $controlHTML .= showHiddenField('projectid', $projectid, 1);
   $controlHTML .= showHiddenField('currentgroup', $currentgroup, 1);
   $controlHTML .= showHiddenField('lreditlist', $lreditlist, 1);
   $controlHTML .= showHiddenField('function', $function, 1);
   #$controlHTML .= print_r($formValues, 1) . "<br>";
   ############################################################
   ###                        SEARCH FORM                   ###
   ############################################################
   $controlHTML .= "<a class=\"mH\"";
   $controlHTML .= "onclick=\"toggleMenu('$divname" . "_search')\">+ Show/Hide Advanced Search Form</a>";
   $controlHTML .= "<div id=\"$divname" . "_search\" class=\"mL\"><ul>";
   # display the search form
   $resetscript = "clearForm(\"control\");";
   $controlHTML .= showGenericButton('resetform','Reset Search Form', $resetscript, 1) . "<br>";
   $controlHTML .= $searchForm['formHTML'];
   $controlHTML .= "</div>";
   $controlHTML .= "<br><i><b>Result Display Options: </b></i><table width=100%><tr align=center><td>" . $searchForm['searchOptions'] . '</td></tr></table>';
   $controlHTML .= "<hr>";
   #if ($debug) {
      $controlHTML .= $searchForm['query'] .'<hr>';
   #}
   ############################################################
   ###                 CUSTOM OUTPUT FORM                   ###
   ############################################################
   $aset = $adminsetuparray[$tablename];
   $controlHTML .= "<a class=\"mH\"";
   $controlHTML .= "onclick=\"toggleMenu('$divname" . "_format')\">+ Show/Hide Custom Result Formatting</a>";
   $controlHTML .= "<div id=\"$divname" . "_format\" class=\"mL\">";
   # show a set of custom queryWizard objects
   $queryparent = new blankShell;
   # setting this to the query assembled by the search object
   $subquery = " (" . $searchForm['query'] . " ) as foo ";
   $queryparent->dbtblname = $subquery;
   $querywizard = new queryWizardComponent;
   $querywizard->parentobject = $queryparent;
   $querywizard->listobject = $listobject;
   # create a list for use in the form drop-downs of the various columns that we can select
   $aslist = '';
   $asep = '';
   foreach (array_keys($aset['column info']) as $thiscol) {
      if (isset($aset['column info'][$thiscol]['label'])) {
         $thislabel = $aset['column info'][$thiscol]['label'];
      } else {
         $thislabel = $thiscol;
      }
      $aslist .= $asep . $thiscol . '|' . $thiscol;
      $asep = ',';
   }
   //$controlHTML .= " Column List: $aslist <br>";
   $qset = array();
   $qset['queryWizardComponent'] = $adminsetuparray['queryWizardComponent'];
   # blank this out, since we do not want any of the informational fields
   
   $qset['queryWizardComponent']['column info'] = array("custom_to_file"=>array("type"=>3,"params"=>"0|False,1|True:ctfid:ctfname::0","label"=>"Output Results to File?","visible"=>1, "readonly"=>0, "width"=>6)); 
   foreach (array('queryWizard_selectcolumns'=>'qcols', 'queryWizard_wherecolumns'=>'wcols', 'queryWizard_ordercolumns'=>'ocols') as $colname => $lname) {
      $qset[$colname] = $adminsetuparray[$colname];
      $asrec = split(':',$qset[$colname]['column info'][$lname]['params']);
      $asrec[0] = $aslist;
      $asparams = join(':', $asrec);
      $qset[$colname]['column info'][$lname]['params'] = $asparams;
      //$controlHTML .= " Column Array for <b>$colname</b>: " . print_r($asrec,1). " <br>";
      //$controlHTML .= " Column Select Record: " . $asparams . " <br>";
   }
   $qset['queryWizard_selectcolumns']['column info']['qcols_txt']['visible'] = 0; 
   $qset['queryWizard_selectcolumns']['table info']['showlabels'] = 1; 
   $querywizard->force_cols = 1;
   $querywizard->force_names = array('custom_to_file'=>$custom_to_file);
   $querywizard->qcols = $formValues['qcols'];
   $querywizard->qcols_func = $formValues['qcols_func'];
   $querywizard->qcols_alias = $formValues['qcols_alias'];
   $querywizard->wcols = $formValues['wcols'];
   $querywizard->wcols_op = $formValues['wcols_op'];
   $querywizard->wcols_value = $formValues['wcols_value'];
   $querywizard->wcols_refcols = $formValues['wcols_refcols'];
   $querywizard->ocols = $formValues['ocols'];
   
   $querywizard->listobject->adminsetuparray = $qset;
   $formatinfo = $querywizard->showEditForm('custom');
   $controlHTML .= $formatinfo['innerHTML'];
   $querywizard->assembleQuery();
   $controlHTML .= $querywizard->sqlstring . "<br>";
   $controlHTML .= "<center>" . showGenericButton('search','Search', "document.forms[\"control\"].elements.searchtype.value=\"search\"; document.forms[\"control\"].elements.page_offset.value=0; $searchobject->search_submit_script ; ", 1, 0) . "</center>";
   $controlHTML .= "</div><hr>";
   
   ############################################################
   ###                  END CUSTOM OUTPUT FORM              ###
   ############################################################
   
   # Check for preprocessing
   switch ($search_view) {
      
      case 'custom':
         $listobject->querystring = $querywizard->sqlstring;
         #$listobject->tablename = 'vwuds_measuring_point';
         //$controlHTML .= "Doing Custom Query: " . $querywizard->sqlstring . "<br>";
         $listobject->performQuery();
         $props = $listobject->queryrecords;
         $numrecs = count($props);
         $max_screenrecs = 100;
         if (count($props) > $max_screenrecs) {
            # this is too big to display on screen, we will have to dump it to a file
            $controlHTML .= "Custom Query yielded more than $max_screenrecs results, outputting to file.<br>";
            $search_view = 'file';
         }
         if ($custom_to_file) {
            # this is too big to display on screen, we will have to dump it to a file
            $controlHTML .= "Custom query requested outputting to file.<br>";
            $search_view = 'file';
         }
         //$controlHTML .= "Results: " . print_r( $props,1) . "<br>";
      break;
   }
   
   # now show number of records
   $controlHTML .= showHiddenField('numrecs', $numrecs, 1);
   
   $x1 = $searchForm['x1'];
   $y1 = $searchForm['y1'];
   $x2 = $searchForm['x2'];
   $y2 = $searchForm['y2'];
   $tol = 0.02;
   if ( (abs($x2 - $x1) < $tol) and (abs($y2 - $y1) < $tol) ) {
      $x1 += -1.0 * $tol;
      $x2 += $tol;
      $y1 += -1.0 * $tol;
      $y2 += $tol;
      $controlHTML .= "<b>Notice:</b> Zooming to fixed distance from single selected point.<br>";
   }
   $controlHTML .= "<hr>";
   if (count($props) == 0) {
      $errorMSG .= "<i><b>Error:</b> No records match the selected criteria.<br></i>";
      $controlHTML .= "<div id=errorinfo class='errorInfo'>" . $errorMSG . "</div>";
      $controlHTML .= "<div id=errorinfo class='resultInfo'>" . $resultMSG . "</div>";
   } else {
      $controlHTML .= "<i><b>Search Results:</b> Search Returned $numrecs record(s) matching your criteria.</i><br>";
      $controlHTML .= "<div id=errorinfo class='errorInfo'>" . $errorMSG . "</div>";
      $controlHTML .= "<div id=errorinfo class='resultInfo'>" . $resultMSG . "</div>";
      #$controlHTML .= "gmapZoom($x1, $y1, $x2, $y2)<br>";
      $zoomscript = "gmapZoom($x1, $y1, $x2, $y2) ; var wktshapes = new Array(); ";
      if (strlen($searchForm['geomcollectsql']) > 0) {
         # try to find records to hilite
         if (strlen($aset['table info']['geom_col']) > 0) {
            $geomcol = $aset['table info']['geom_col'];
            if (isset($aset['table info']['maplabelcols'])) {
               $labelcols = split(",", $aset['table info']['maplabelcols']);
            } else {
               $labelcols = array($pkcol);
            }
            $listobject->querystring = $searchForm['geomcollectsql'];
            if ($debug) {
               $controlHTML .= $listobject->querystring . " ; <br>";
            }
            $listobject->performQuery();
            $zoomrecs = $listobject->queryrecords;
            $zi = 0;
            foreach ($zoomrecs as $thisrec) {
               $zgeom = $thisrec[$geomcol];
               $ztext = '';
               $listobject->queryrecords = array($thisrec);
               $listobject->show = 0;
               $aset['column info'][$aset['table info']['geom_col']]['visible'] = 0;
               $aset['table info']['output type'] = 'generic';
               $listobject->adminview = 1;
               $listobject->adminsetup = $aset;
               $listobject->showList();
               $ztext = str_replace(array("\r", "\n"), array('\r', '\n'), addslashes(htmlentities($listobject->outstring, ENT_QUOTES)));
               /*
               foreach ($labelcols as $thiscol) {
                  $ztext .= $thisrec[$thiscol] . "<br>";
               }
               */
               $zoomscript .= " wktshapes[$zi] = [ \"$zgeom\", \"$ztext\"] ; ";
               $zi++;
            }
         }
         $zoomscript .=  " putWKTGoogleShape(wktshapes); ";
      }
      $controlHTML .= showGenericButton('centermap','Zoom Map To Selected Records', $zoomscript, 1);
      $controlHTML .= "<br>";
      switch ($search_view) {
         case 'list':
         $controlHTML .= "Viewing Page " . ($page_offset + 1) . " of $num_pages.<br>";
         break;

         case 'batchedit':
         $controlHTML .= "Viewing Page " . ($page_offset + 1) . " of $num_pages.<br>";
         break;

         case 'file':
         $controlHTML .= "$numrecs records output to temporary file.<br>";
         break;

         case 'edit':
            $controlHTML .= "Editing Record <br>";
         break;

         case 'custom':
            $controlHTML .= "Showing Custom Query Output Format.<br>";
         break;

         default:
            $controlHTML .= "Viewing Record " . ($currentpos + 1) . " out of $numrecs<br>";
         break;
      }
   }
   $listobject->show = 0;
   if ($debug) {
      $controlHTML .= "Search View: $search_view <br>";
   }
      
   switch ($search_view) {
      case 'list':
         $listobject->queryrecords = $props;
         $listobject->showList();
         //$controlHTML .= "Results: " . print_r( $props,1) . "<br>";
         $controlHTML .= $listobject->outstring;
      break;
      
      case 'custom':
         # treat this like a list
         $listobject->queryrecords = $props;
         $listobject->tablename = '';
         $listobject->adminview = 0;
         $listobject->showList();
         $controlHTML .= $listobject->outstring;
      break;

      case 'detail':
      # just use the normal form, except set disabled = 1, since this is a read only view
         $disabled = 1;
         $controlHTML .= showFormVars($listobject, $props, $aset, 1, 0, $debug, 0, 1, $disabled, $fno);
      break;

      case 'file':
         $colnames = array(array_keys($props[0]));
         $filename = 'tmp_vwuds_' . $userid . '.xls';
         if ($debug) {
            $colcsv = join(',', $colnames);
            $controlHTML .= "Columns: $colcsv <br>";
            //$controlHTML .= print_r(array_keys($props[0]),1) . "<br>";
         }

         if ($debug) {
            $numlines = count($props);
            $controlHTML .= "Outputting: $numlines lines <br>";
         }

         #putDelimitedFile("$outdir/$filename", $colnames,"\t",1,'dos');
         putExcelFile("$outdir_nodrive/$filename", $props);
         $controlHTML .= "<a href='$outurl/$filename' target=_new>Click Here to Download Spreadsheet File of Records.</a><br>";
      break;

      case 'edit':
         $disabled = $readonly;
         if ($searchtype == 'new') {
            if ($caninsert) {
               $disabled = 0;
            }
            $controlHTML .= showFormVars($listobject, $blankrec, $aset, 1, 0, $debug, 0, 1, $disabled, $fno);
         } else {
            $controlHTML .= showFormVars($listobject, $props, $aset, 1, 0, $debug, 0, 1, $disabled, $fno);
         }
         if (isset($aset['table info']['geom_col'])) {
            if (strlen($aset['table info']['geom_col']) > 0) {
               $geomcol = $aset['table info']['geom_col'];
               $controlHTML .= showGenericButton('copygeom','Capture Selected Geometry', "document.forms[\"control\"].elements.$geomcol" . ".value=getScratchGmapGeom(); alert(\"Geometry Copied, you must save for this to take effect.\")", 1) . "<br>";
            }
         }
      break;

      case 'batchedit':
         $disabled = $readonly;
         $aset['table info']['outputformat'] = 'column';
         $controlHTML .= showFormVars($listobject, $props, $aset, 1, 0, $debug, 1, 1, $disabled, $fno);
      break;

      default:
         $controlHTML .= showFormVars($listobject, $props, $aset, 1, 0, $debug, 0, 1, $disabled, $fno);
      break;
   }

   # should we disable the previous button? (at first record)
   if ($prev_record_id == '') {
      $pd = 1;
   } else {
      $pd = 0;
   }

   $controlHTML .= $searchForm['navButtonHTML'] ;
   $controlHTML .= '</form>';

   $controlHTML .= "</div>";
   return $controlHTML;
}

function measuringPointForm($formValues) {
   global $listobject, $fno, $adminsetuparray, $outdir, $outurl, $userid;

   $controlHTML = '';
   #$debug = 1;
   $projectid = $formValues['projectid'];
   $currentgroup = $formValues['currentgroup'];
   $lreditlist = $formValues['lreditlist'];
   $allgroups = $formValues['allgroups'];

   # call the search form routine.   This will take all of the variables that come in the search and interpret them,
   # returning the record of interest
   $searchForm = measuringPointSearchForm($formValues);
   $record_id = $searchForm['recordid'];
   $prev_record_id = $searchForm['previd'];
   $next_record_id = $searchForm['nextid'];
   $numrecs = $searchForm['numrecs'];
   $page_offset = $searchForm['page_offset'];
   $next_page = $searchForm['next_page'];
   $prev_page = $searchForm['prev_page'];
   $num_pages = $searchForm['num_pages'];
   $currentpos = $searchForm['currentpos'];
   $search_view = $searchForm['search_view'];
   # the first record returned, or the target record if this is the result of a click on the prev/next button
   $props = $searchForm['recordvalue'];

   $controlHTML .= "<div style=\"border: 1px solid rgb(0 , 0, 0); border-style: dotted; overflow: auto; width: 600px; \">";
   $controlHTML .= "<form id=control name=control>";
   # hidden system variables
   $controlHTML .= showHiddenField('projectid', $projectid, 1);
   $controlHTML .= showHiddenField('currentgroup', $currentgroup, 1);
   $controlHTML .= showHiddenField('lreditlist', $lreditlist, 1);
   $controlHTML .= showHiddenField('numrecs', $numrecs, 1);
   # action info
   $controlHTML .= showHiddenField('searchtype', $searchtype, 1);
   #$controlHTML .= print_r($formValues, 1) . "<br>";
   ############################################################
   ###                        SEARCH FORM                   ###
   ############################################################
   $controlHTML .= "<a class=\"mH\"";
   $controlHTML .= "onclick=\"toggleMenu('mp_search')\">+ Show/Hide Advanced Search Form</a>";
   $controlHTML .= "<div id=\"mp_search\" class=\"mL\"><ul>";
   # display the search form
   $controlHTML .= $searchForm['formHTML'];
   $controlHTML .= "</div>";
   $controlHTML .= "<hr>";
   $controlHTML .= "Search Options: " . $searchForm['searchOptions'];
   $controlHTML .= "<hr>";
   if (count($props) == 0) {
      $controlHTML .= "<i><b>Error:</b> No records match the selected criteria.<br></i>";
   } else {
      $controlHTML .= "<i><b>Search Results:</b> Search Returned $numrecs record(s) matching your criteria.</i><br>";
      switch ($search_view) {
         case 'list':
         $controlHTML .= "Viewing Page " . ($page_offset + 1) . " of $num_pages.<br>";
         break;

         case 'file':
         $controlHTML .= "$numrecs records output to temporary file.<br>";
         break;

         default:
            $controlHTML .= "Viewing Record $currentpos out of $numrecs<br>";
         break;
      }
   }
   $listobject->show = 0;
   if ($debug) {
      $controlHTML .= "Search View: $search_view <br>";
   }
   switch ($search_view) {
      case 'list':
         $listobject->queryrecords = $props;
         #$listobject->tablename = 'vwuds_measuring_point';
         $listobject->showList();
         $controlHTML .= $listobject->outstring;
         # must add hidden variable for pk and page offset
         $controlHTML .= showHiddenField('record_id', $record_id, 1);
         $page_offset = $searchForm['page_offset'];
         $controlHTML .= showHiddenField('page_offset', $page_offset, 1);
      break;

      case 'detail':
      # just use the normal form, except set disabled = 1, since this is a read only view
         $disabled = 1;
         $controlHTML .= showFormVars($listobject,$props,$adminsetuparray['vwuds_measuring_point'],1, 0, $debug, 0, 1, $disabled, $fno);
      break;

      case 'file':
         $colnames = array(array_keys($props[0]));
         $filename = 'tmp_vwuds_' . $userid . '.csv';
         if ($debug) {
            $colcsv = join(',', $colnames);
            $controlHTML .= "Columns: $colcsv <br>";
         }

         if ($debug) {
            $numlines = count($props);
            $controlHTML .= "Outputting: $numlines lines <br>";
         }

         putDelimitedFile("$outdir/$filename", $colnames,',',1,'dos');
         putDelimitedFile("$outdir/$filename", $props,',',0,'dos');
         $controlHTML .= "<a href='$outurl/$filename' target=_new>Click Here to Download Spreadsheet File of Records.</a>";
         break;

      default:
         $controlHTML .= showFormVars($listobject,$props,$adminsetuparray['vwuds_measuring_point'],1, 0, $debug, 0, 1, $disabled, $fno);
      break;
   }

   # should we disable the previous button? (at first record)
   if ($prev_record_id == '') {
      $pd = 1;
   } else {
      $pd = 0;
   }

   switch ($searchForm['search_view']) {
      case 'list':
         #$controlHTML .= "Previous record_id = $prev_record_id ";
         if ($prev_page == $page_offset) {
            $pd = 1;
         } else {
            $pd = 0;
         }
         $controlHTML  .= showGenericButton('prev_page','<-- Previous Page', "document.forms[\"control\"].elements.searchtype.value=\"browse\"; document.forms[\"control\"].elements.page_offset.value=\"$prev_page\"; xajax_showMeasuringPointForm(xajax.getFormValues(\"control\")); ", 1, $pd);
         # should we disable the next button? (at last record)
         if ($next_page == $page_offset) {
            $nd = 1;
         } else {
            $nd = 0;
         }
         #$controlHTML .= "Next record_id = $next_record_id ";
         $controlHTML .= showGenericButton('next_page','Next Page -->', "document.forms[\"control\"].elements.searchtype.value=\"browse\"; document.forms[\"control\"].elements.page_offset.value=\"$next_page\"; xajax_showMeasuringPointForm(xajax.getFormValues(\"control\")); ", 1, $nd);
         $controlHTML .= "</form>";
         #print("<br>");

      break;

      case 'file':
      # do nothing
      break;

      case 'detail':

         #$controlHTML .= "Previous record_id = $prev_record_id ";
         $edit_disabled = 0; # this needs to be set by a permission check function, for now, it will allow editing by default
         $controlHTML  .= showGenericButton('editrecord','Edit', "document.forms[\"control\"].elements.searchtype.value=\"browse\"; document.forms[\"control\"].elements.search_view.value=\"edit\"; for (i=0;i<document.forms[\"control\"].elements.search_view.length;i++) { if (document.forms[\"control\"].elements.search_view[i].value == \"edit\") { document.forms[\"control\"].elements.search_view[i].checked=true ; } } ; document.forms[\"control\"].elements.record_id.value=\"$record_id\"; xajax_showMeasuringPointForm(xajax.getFormValues(\"control\")); show_next(\"mp_data0\", \"mp_0\", \"mp\"); ", 1, $edit_disabled);
         $controlHTML  .= "<br>";
         $controlHTML  .= showGenericButton('showprev','<-- Previous', "document.forms[\"control\"].elements.searchtype.value=\"browse\"; document.forms[\"control\"].elements.record_id.value=\"$prev_record_id\"; xajax_showMeasuringPointForm(xajax.getFormValues(\"control\")); show_next(\"mp_data0\", \"mp_0\", \"mp\"); ", 1, $pd);
         # should we disable the next button? (at last record)
         if ($next_record_id == '') {
            $nd = 1;
         } else {
            $nd = 0;
         }
         #$controlHTML .= "Next record_id = $next_record_id ";
         $controlHTML .= showGenericButton('shownext','Next -->', "document.forms[\"control\"].elements.searchtype.value=\"browse\"; document.forms[\"control\"].elements.record_id.value=\"$next_record_id\"; xajax_showMeasuringPointForm(xajax.getFormValues(\"control\")); show_next(\"mp_data0\", \"mp_0\", \"mp\"); ", 1, $nd);
         $controlHTML .= "</form>";
         #print("<br>");
         #$debug = 1;
      break;

      default:

         #$controlHTML .= "Previous record_id = $prev_record_id ";
         $controlHTML  .= showGenericButton('showprev','<-- Previous', "document.forms[\"control\"].elements.searchtype.value=\"browse\"; document.forms[\"control\"].elements.record_id.value=\"$prev_record_id\"; xajax_showMeasuringPointForm(xajax.getFormValues(\"control\")); show_next(\"mp_data0\", \"mp_0\", \"mp\"); ", 1, $pd);
         # should we disable the next button? (at last record)
         if ($next_record_id == '') {
            $nd = 1;
         } else {
            $nd = 0;
         }
         #$controlHTML .= "Next record_id = $next_record_id ";
         $controlHTML .= showGenericButton('shownext','Next -->', "document.forms[\"control\"].elements.searchtype.value=\"browse\"; document.forms[\"control\"].elements.record_id.value=\"$next_record_id\"; xajax_showMeasuringPointForm(xajax.getFormValues(\"control\")); show_next(\"mp_data0\", \"mp_0\", \"mp\"); ", 1, $nd);
         $controlHTML .= "</form>";
         #print("<br>");
         #$debug = 1;
      break;
   }

   $controlHTML .= "</div>";
   return $controlHTML;
}

function measuringPointSearchForm($formValues) {
   global $listobject, $fno, $adminsetuparray;

   $controlHTML = '';
   $searchresult = array();

   #$debug = 1;
   $projectid = $formValues['projectid'];
   $currentgroup = $formValues['currentgroup'];
   $lreditlist = $formValues['lreditlist'];
   $allgroups = $formValues['allgroups'];
   # for now, we assume that geo constraints are in effect
   $constraingeo = $formValues['constraingeo'];
   $constrainrecid = $formValues['constrainrecid'];
   $min_userid = $formValues['min_userid'];
   $max_userid = $formValues['max_userid'];
   $vwp_no = $formValues['vwp_no'];
   $gw_no = $formValues['gw_no'];
   $constrain_gwno = $formValues['constrain_gwno'];
   $constrain_vwpno = $formValues['constrain_vwpno'];
   if (isset($formValues['search_view'])) {
      $search_view = $formValues['search_view'];
   } else {
      $search_view = 'list';
   }
   if (isset($formValues['page_records'])) {
      $page_records = $formValues['page_records'];
   } else {
      if ($search_view == 'list') {
         $page_records = 10;
      } else {
         $page_records = 1;
      }
   }
   if ($page_records <= 0) {
      $page_records = 1;
   }
   if (isset($formValues['page_offset'])) {
      $page_offset = $formValues['page_offset'];
   } else {
      $page_offset = 0;
   }
   $rec_offset = $page_offset * $page_records;

   if (isset($formValues['searchtype'])) {
      $searchtype = $formValues['searchtype'];
   } else {
      $searchtype = 'browse';
   }
   $show_locality = $formValues['show_locality'][0];
   if (count($show_locality) > 0) {
      $fips_clause = " stcofips in ( '" . join("','", $show_locality) . "') " ;
   } else {
      $fips_clause = " ( 1 = 1 ) " ;
   }
   $show_usetypes = $formValues['show_usetypes'][0];
   if (count($show_usetypes) > 0) {
      $use_clause = " \"CAT_MP\" in ( '" . join("','", $show_usetypes) . "') " ;
   } else {
      $use_clause = " ( 1 = 1 ) " ;
   }
   $show_sourcetypes = $formValues['show_sourcetypes'][0];
   if (count($show_sourcetypes) > 0) {
      $src_clause = " \"TYPE\" in ( '" . join("','", $show_sourcetypes) . "') " ;
   } else {
      $src_clause = " ( 1 = 1 ) " ;
   }
   $show_sourcesubtypes = $formValues['show_sourcesubtypes'][0];
   if (count($show_sourcesubtypes) > 0) {
      $srcsub_clause = " \"SUBTYPE\" in ( '" . join("','", $show_sourcesubtypes) . "') " ;
   } else {
      $srcsub_clause = " ( 1 = 1 ) " ;
   }
   $show_actiontypes = $formValues['show_actiontypes'][0];
   if (count($show_actiontypes) > 0) {
      $action_clause = " \"ACTION\" in ( '" . join("','", $show_actiontypes) . "') " ;
   } else {
      $action_clause = " ( 1 = 1 ) " ;
   }
   if ($constrainrecid == 1) {
      $userid_clause = " \"USERID\" >= '$min_userid' AND \"USERID\" <= '$max_userid' " ;
   } else {
      $userid_clause = " ( 1 = 1 ) " ;
   }
   if ($constrain_vwpno == 1) {
      $vwpno_clause = " \"VWP_PERMIT\" = '$vwp_no' " ;
   } else {
      $vwpno_clause = " ( 1 = 1 ) " ;
   }
   if ($constrain_gwno == 1) {
      $gwno_clause = " \"GWPERMIT\" = '$gw_no' " ;
   } else {
      $gwno_clause = " ( 1 = 1 ) " ;
   }

   if ( isset($formValues['record_id']) and ($searchtype <> 'search') ) {
      $record_id = $formValues['record_id'];
      $numrecs = $formValues['numrecs'];
   } else {
      # if the record_id is NOT set, OR if this is a search, we go for this
      $listobject->querystring = "select min(record_id) as record_id, count(*) as numrecs from vwuds_measuring_point ";
      $listobject->querystring .= " WHERE $action_clause ";
      $listobject->querystring .= " AND $userid_clause ";
      $listobject->querystring .= " AND $vwpno_clause ";
      $listobject->querystring .= " AND $gwno_clause ";
      $listobject->querystring .= " AND $src_clause ";
      $listobject->querystring .= " AND $srcsub_clause ";
      $listobject->querystring .= " AND $use_clause ";
      $listobject->querystring .= " AND $fips_clause ";
      if ($debug) {
         $controlHTML .= " $listobject->querystring ; ";
      }
      $listobject->performQuery();
      $record_id = $listobject->getRecordValue(1,'record_id');
      $numrecs = $listobject->getRecordValue(1,'numrecs');
   }

   switch ($search_view) {
      case 'list':
         $listobject->querystring = "select * from vwuds_measuring_point ";
         $listobject->querystring .= " WHERE $action_clause ";
         $listobject->querystring .= " AND $userid_clause ";
         $listobject->querystring .= " AND $vwpno_clause ";
         $listobject->querystring .= " AND $gwno_clause ";
         $listobject->querystring .= " AND $src_clause ";
         $listobject->querystring .= " AND $srcsub_clause ";
         $listobject->querystring .= " AND $use_clause ";
         $listobject->querystring .= " AND $fips_clause ";
         $listobject->querystring .= " LIMIT $page_records OFFSET $rec_offset ";
      break;

      case 'file':
         $listobject->querystring = "select * from vwuds_measuring_point ";
         $listobject->querystring .= " WHERE $action_clause ";
         $listobject->querystring .= " AND $userid_clause ";
         $listobject->querystring .= " AND $vwpno_clause ";
         $listobject->querystring .= " AND $gwno_clause ";
         $listobject->querystring .= " AND $src_clause ";
         $listobject->querystring .= " AND $srcsub_clause ";
         $listobject->querystring .= " AND $use_clause ";
         $listobject->querystring .= " AND $fips_clause ";
         break;

      case 'detail':
         $listobject->querystring = "select * from vwuds_measuring_point where record_id = $record_id ";
      break;

      default:
         $listobject->querystring = "select * from vwuds_measuring_point where record_id = $record_id ";
         $listobject->querystring .= " AND $action_clause ";
      break;

   }
   if ($debug) {
      $controlHTML .= " $listobject->querystring ; ";
   }
   $listobject->performQuery();
   switch ($search_view) {
      case 'list':
         $props = $listobject->queryrecords;
         $record_id = $props[0]['record_id'];
      break;

      case 'file':
         $props = $listobject->queryrecords;
         $record_id = $props[0]['record_id'];
      break;

      default:
         $props = $listobject->queryrecords[0];
         $record_id = $props['record_id'];
      break;
   }

   $searchResult['recordvalue'] = $props;
   $searchResult['recordid'] = $record_id;
   $searchResult['numrecs'] = $numrecs;
   $searchResult['page_offset'] = $page_offset;
   if ($page_offset > 0) {
      $searchResult['prev_page'] = $page_offset - 1;
   } else {
      $searchResult['prev_page'] = $page_offset;
   }
   $searchResult['num_pages'] = ceil($numrecs / $page_records);
   if ( ($page_offset + 1) >= ($numrecs / $page_records) ) {
      $searchResult['next_page'] = $page_offset;
   } else {
      $searchResult['next_page'] = $page_offset + 1;
   }
   #$debug = 0;
   # get previous and next
   $listobject->querystring = "select min(record_id) as next_record_id from vwuds_measuring_point ";
   if ( ltrim(rtrim($record_id)) <> '') {
      $listobject->querystring .= " WHERE record_id > $record_id ";
   } else {
      $listobject->querystring .= " WHERE ( 1 = 1 ) ";
   }
   $listobject->querystring .= " AND $action_clause ";
   $listobject->querystring .= " AND $userid_clause ";
   $listobject->querystring .= " AND $vwpno_clause ";
   $listobject->querystring .= " AND $gwno_clause ";
   $listobject->querystring .= " AND $src_clause ";
   $listobject->querystring .= " AND $srcsub_clause ";
   $listobject->querystring .= " AND $use_clause ";
   $listobject->querystring .= " AND $fips_clause ";
   if ($debug) {
      $controlHTML .= " $listobject->querystring ; ";
   }
   $listobject->performQuery();
   $next_record_id = $listobject->getRecordValue(1,'next_record_id');
   $listobject->querystring = "select max(record_id) as prev_record_id, count(*) as prevno from vwuds_measuring_point ";
   if ( ltrim(rtrim($record_id)) <> '') {
      $listobject->querystring .= " WHERE record_id < $record_id ";
   } else {
      $listobject->querystring .= " WHERE ( 1 = 1 ) ";
   }
   $listobject->querystring .= " AND $action_clause ";
   $listobject->querystring .= " AND $userid_clause ";
   $listobject->querystring .= " AND $vwpno_clause ";
   $listobject->querystring .= " AND $gwno_clause ";
   $listobject->querystring .= " AND $src_clause ";
   $listobject->querystring .= " AND $srcsub_clause ";
   $listobject->querystring .= " AND $use_clause ";
   $listobject->querystring .= " AND $fips_clause ";
   if ($debug) {
      $controlHTML .= " $listobject->querystring ; ";
   }
   $listobject->performQuery();
   $prev_record_id = $listobject->getRecordValue(1,'prev_record_id');
   $currentpos = $listobject->getRecordValue(1,'prevno');
   $searchResult['nextid'] = $next_record_id;
   $searchResult['previd'] = $prev_record_id;
   $searchResult['currentpos'] = $currentpos;

   #print_r($allsegs);
   ############################################################
   ###                        SEARCH FORM                   ###
   ############################################################
   $controlHTML .= "<table border=1>";
   $controlHTML .= "<tr>";
   $controlHTML .= "<td valign='top'>";
   # show check box to decide if we will constrain our geography to the currently selected area
   $controlHTML .= showCheckBox('constraingeo', 1, $constraingeo, '', 1, 0);
   $controlHTML .= "<b> Restrict to Current Watershed?</b><br>";
   $controlHTML .= showCheckBox('constrainrecid', 1, $constrainrecid, '', 1, 0);
   $controlHTML .= "<b> Restrict to USERID Range (below)?</b><br>";
   $controlHTML .= "&nbsp;&nbsp; from " . showWidthTextField('min_userid', $min_userid, 12, '', 1, 0);
   $controlHTML .= " to " . showWidthTextField('max_userid', $max_userid, 12, '', 1, 0);
   $controlHTML .= '<br>' . showCheckBox('constrain_gwno', 1, $constrain_gwno, '', 1, 0);
   $controlHTML .= "<b> Show GW Permit #:</b> ";
   $controlHTML .= showWidthTextField('gw_no', $gw_no, 12, '', 1, 0);
   $controlHTML .= '<br>' . showCheckBox('constrain_vwpno', 1, $constrain_vwpno, '', 1, 0);
   $controlHTML .= "<b> Show VWP Permit #:</b> ";
   $controlHTML .= showWidthTextField('vwp_no', $vwp_no, 12, '', 1, 0);
   $controlHTML .= "<br><b> Search By Locality:</b><br>";
   $controlHTML .=  showMultiList2($listobject,'show_locality', 'poli_bounds', 'poli1', 'name', $show_locality, "$projectid = $projectid", 'name', $debug, 4, 1, 0);
   $controlHTML .= "</td>";
   $controlHTML .= "<td valign='top'>";
   $controlHTML .= "<b> Show Selected Action Types:</b><br>";
   $controlHTML .=  showMultiList2($listobject,'show_actiontypes', 'vwuds_action', 'abbrev', 'action_text', $show_actiontypes, '', 'action_text', $debug, 2, 1, 0);
   $controlHTML .= "<br><table><tr>";
   $controlHTML .= "<td colspan=2><b> Show Selected Source Types/Subtypes:</b></td>";
   $controlHTML .= "</tr><tr>";
   $controlHTML .=  "<td>" . showMultiList2($listobject,'show_sourcetypes', 'watersourcetype', 'wsabbrev', 'wsname', $show_sourcetypes, '', 'wsname', $debug, 2, 1, 0) . "</td>";
   $controlHTML .=  "<td>" . showMultiList2($listobject, 'show_sourcesubtypes', 'vwuds_source_subtype', 'abbrev', 'typename', $show_sourcesubtypes, '', 'typename', $debug, 2, 1, 0) . "</td>";
   $controlHTML .= "</tr></table>";
   $controlHTML .= "<br><b> Show Selected Use Types:</b><br>";
   $controlHTML .=  showMultiList2($listobject,'show_usetypes', 'waterusetype', 'typeabbrev', 'typename', $show_usetypes, '', 'typename', $debug, 2, 1, 0);
   $controlHTML .= "</td>";
   $controlHTML .= "</tr>";
   $controlHTML .= "<tr>";
   $controlHTML .= "<td colspan=2 align=center>";
   $controlHTML .= showRadioButton('search_view', 'list', $search_view, '', 1, 0) . ' List View';
   $controlHTML .= showRadioButton('search_view', 'detail', $search_view, '', 1, 0) . ' Detail View';
   $controlHTML .= showRadioButton('search_view', 'edit', $search_view, '', 1, 0) . ' Edit View';
   $controlHTML .= showRadioButton('search_view', 'file', $search_view, '', 1, 0) . ' Output to File';
   $controlHTML .= "</td>";
   $controlHTML .= "</tr>";
   $controlHTML .= "</table>";
   $controlHTML  .= showGenericButton('search','Search', "document.forms[\"control\"].elements.searchtype.value=\"search\"; xajax_showMeasuringPointForm(xajax.getFormValues(\"control\")); show_next(\"mp_data0\", \"mp_0\", \"mp\"); ", 1, $pd);

   $searchResult['formHTML'] = $controlHTML;
   $searchResult['search_view'] = $search_view;

   return $searchResult;
}

function annualReportingForm($formValues) {
   global $listobject, $userid, $adminsetuparray;

   $controlHTML = '';

   if (isset($formValues['thisyear'])) {
      $thisyear = $formValues['thisyear'];
   } else {
      $thisyear = 2008;
   }
   if (isset($formValues['progressreport'])) {
      $progressreport = $formValues['progressreport'];
   } else {
      $progressreport = 0;
   }
   if (isset($formValues['user_primary'])) {
      $user_primary = $formValues['user_primary'];
   }
   if (isset($formValues['user_secondary'])) {
      $user_secondary = $formValues['user_secondary'];
   }
   if (isset($formValues['planner'])) {
      $planner = $formValues['planner'];
   }
   if (isset($formValues['other'])) {
      $other = $formValues['other'];
   }
   if (isset($formValues['other_userid'])) {
      $other_userid = $formValues['other_userid'];
   }

   if (isset($formValues['fac_userid'])) {
      $fac_userid = $formValues['fac_userid'][0];
   } else {
      $fac_userid = array();
   }
   if (isset($formValues['mailordownload'])) {
      $mailordownload = $formValues['mailordownload'];
   } else {
      $mailordownload = 'downloadhtml';
   }
   #error_reporting(E_ALL);
   if (isset($formValues['auditdate'])) {
      $auditdate = $formValues['auditdate'];
   } else {
      $auditdate = 'all';
   }
   
   if ($auditdate <> 'all') {
      switch ($auditdate) {
         case 'include':
            $audop = 'in';
         break;
         
         case 'exclude':
            $audop = 'not in';
         break;
         
         default:
            $audop = 'not in';
         break;
      }
      if (isset($formValues['datesent'])) {
         $datesent = $formValues['datesent'][0];
         if (count($datesent) > 0) {
            $thesedates = "'" . join("','", $datesent) . "'";
            $datewhere = " userid $audop (select userid from edwrd_audit where date_trunc('day', datesent) in ($thesedates))";
         } else {
            $datewhere = " ( 1 = 1 ) ";
         }
      } else {
         $datesent = '';
         $datewhere = " ( 1 = 1 ) ";
      }
   } else {
      $datewhere = " ( 1 = 1 ) ";
   }


   $controlHTML .= "<form method=post action='' id=control>";
   $controlHTML .= "<b>Select Facility(s):</b><br>";
   ############################################################
   ###                        SEARCH FORM                   ###
   ############################################################
   $tablename = 'facilities';
   $aset = $adminsetuparray[$tablename];
   # call the search form routine.   This will take all of the variables that come in the search and interpret them,
   # returning the record of interest
   $searchobject = new listObjectSearchForm;
   $searchobject->listobject = $listobject;
   $searchobject->debug = FALSE;
   $searchobject->insertOK = 0;
   $searchobject->deleteOK = 0;
   $searchobject->adminsetup = $aset;
   $searchobject->readonly = 1;
   $searchobject->record_submit_script = '';
   $searchobject->search_submit_script = "xajax_showAnnualReportingForm(xajax.getFormValues(\"control\"));  ";
   $searchobject->page_submit_script = "";
   $controlHTML .= "<a class=\"mH\"";
   $controlHTML .= "onclick=\"toggleMenu('$divname" . "_search')\">+ Show/Hide Advanced Search Form</a>";
   $controlHTML .= "<div id=\"$divname" . "_search\" class=\"mL\"><ul>";
   # display the search form
   $searchForm = $searchobject->showSearchForm($formValues);
   $controlHTML .= $searchForm['formHTML'];
   $controlHTML .= "</div><br>";
   $subquery = "( select userid, ownname, facility, system from (" . $searchForm['query'] . " ) as foo ) as bar ";
   $controlHTML .= $subquery . " ;<br>";
   $controlHTML .= "<br>";   
   
   $controlHTML .= "<b>Additional Search Options:</b> <ul>";
   $datesentfoo = "(select date_trunc('day', datesent) as sentdate from edwrd_audit group by sentdate ) as foo";
   $controlHTML .= "<li>" . showList($listobject,'auditdate',array(0=>array('auditdate'=>'include','auditlabel'=>'Include'),1=>array('auditdate'=>'exclude','auditlabel'=>'Exclude'),2=>array('auditdate'=>'all','auditlabel'=>'N/A')),'auditlabel', 'auditdate','',$auditdate,0, 1, 0);
   $controlHTML .= " Facilities which were emailed on the foloowing dates: <br>" . showMultiList2($listobject, 'datesent', $datesentfoo, 'sentdate', 'sentdate', $datesent, '', 'sentdate', $debug, 5, 1, 0);
   $controlHTML .= "</ul>";
   
   $controlHTML .= showGenericButton('search','Search', "xajax_showAnnualReportingForm(xajax.getFormValues(\"control\")); ", 1, $pd);
   $controlHTML .= "<hr>";

   ############################################################
   ###                    END SEARCH FORM                   ###
   ############################################################
   $controlHTML .= "<b>Select Facilities:</b><br>";
   # old facility where clause
   #$facwhere = "userid in (select \"USERID\" from annual_data where \"YEAR\" = $thisyear group by \"USERID\") ";
   $facwhere = "userid in (select \"USERID\" from annual_data where \"YEAR\" = $thisyear group by \"USERID\") AND $datewhere ";
   $controlHTML .= showMultiList2($listobject, 'fac_userid', $subquery, 'userid', 'ownname, facility, system', $fac_userid, $facwhere, 'ownname, facility, system', 0, 8, 1, 0);
   $yrfoo = "(select \"YEAR\" as thisyear from annual_data where \"YEAR\" is not null group by thisyear ) as foo";
   $controlHTML .= "<br><b>Select the year to send: </b>" . showList($listobject,'thisyear',$yrfoo, 'thisyear','thisyear','',$thisyear,0, 1, 0) . "<br>";
   $controlHTML .= "<b>Select Output Options:</b> <ul>";
   $controlHTML .= "<li>" . showList($listobject,'mailordownload',array(0=>array('mailordownload'=>'email','mdlabel'=>'email'),1=>array('mailordownload'=>'downloadword','mdlabel'=>'Download MS Word (may result in multiple files)'),2=>array('mailordownload'=>'downloadhtml','mdlabel'=>'Download As HTML (single file)')),'mdlabel', 'mailordownload','',$mailordownload,0, 1, 0) . " Reports";
   $controlHTML .= "<li>" . showCheckBox('progressreport',1,$progressreport, '', 1, 0) . " Send Summary of Actions to Planner";
   $controlHTML .= "</ul>";
   $controlHTML .= "<b>Select Recipient(s):</b><br>";
   $controlHTML .= "<ul>";
   # currently the select boxes for facility contacts will be disabled - until we go live!
   $controlHTML .= "<li>" . showCheckBox('user_primary','user_primary',$user_primary, '', 1, 0) . "Facility Primary Contact</li>";
   $controlHTML .= "<li>" . showCheckBox('user_secondary','user_secondary',$user_secondary, '', 1, 0) . "Facility Secondary Contact</li>";
   $controlHTML .= "<li>" . showCheckBox('planner','planner',$planner, '', 1) . "Regional Planner</li>";
   $controlHTML .= "<li>" . showCheckBox('other','other',$other, '', 1);
   $controlHTML .= "Other EDWrD User ";
   $controlHTML .= showList($listobject,'other_userid','users', 'lastname,firstname','userid','userid in (select userid from mapusergroups where groupid in (3,4))',$other_userid,0, 1, 0);
   $controlHTML .= "</li>";
   $controlHTML .= "</ul>";
   $controlHTML  .= showGenericButton('sendmail','Send Reporting Form', "xajax_showAnnualReportingFormResult(xajax.getFormValues(\"control\")); ", 1, 0);
   $controlHTML .= "</form>";

   return $controlHTML;
}

function annualReportingFormResult($formValues) {
   global $listobject, $userid, $adminsetuparray, $outdir_nodrive, $basedir;

   $controlHTML = '';
   $edwrduserid = $userid;

   #$controlHTML .= print_r($formValues,1) . '<br>';

   if (isset($formValues['thisyear'])) {
      $thisyear = $formValues['thisyear'];
   } else {
      $thisyear = 2008;
   }
   if (isset($formValues['progressreport'])) {
      $progressreport = $formValues['progressreport'];
   } else {
      $progressreport = 0;
   }

   # check box to decide if sending to facility user, planner, or both
   $recip_type = array();
   if (isset($formValues['user_primary'])) {
      array_push($recip_type, $formValues['user_primary']);
   }
   if (isset($formValues['user_secondary'])) {
      array_push($recip_type, $formValues['user_secondary']);
   }
   if (isset($formValues['planner'])) {
      array_push($recip_type, $formValues['planner']);
   }
   if (isset($formValues['other'])) {
      array_push($recip_type, $formValues['other']);
   }
   if (isset($formValues['mailordownload'])) {
      $mailordownload = $formValues['mailordownload'];
   } else {
      $mailordownload = 'downloadhtml';
   }


   $error = 0;

   if (isset($formValues['fac_userid'])) {
      $fac_userid = $formValues['fac_userid'][0];
   } else {
      $errorMSG .= "<b>Error:</b> You must select a recipient facility.";
      $error = 1;
   }
   #error_reporting(E_ALL);

   $reportdoc = '';
   $sic_file = "./mailform/siccodes.xls";

   # Inputs:
      # year - the year for the reporting data
      #

   # query the facilities table for facility info
   # add this to $reportdoc
   # query all related MP data:
      # match up every MP record corresponding to that facility for the calendar year indicated
      # order by the action - WD, SD, SL
   $projectid = $formValues['projectid'];
   $recipient = $formValues['recipient'];
   $planner_recipient = $formValues['planner_recipient'];

   #$debug = 1;
   if ($startyear == '') { $startyear = Date('Y'); }
   if ($endyear == '') { $endyear = Date('Y'); }
   #print_r($allsegs);

   # create repository for summary records in the even that we chose to send a summary of emailed records to the planners
   $summary = array();

   # parse the search form, and select matching records
   # create email object
   // ----- Creating the tar object (uncompressed archive)
   $tarfilename = str_replace("/", "\\", $basedir . "out/wateruse2008_docs$userid" . ".tar");
   $tardownload = "./out/wateruse2008_docs$userid" . ".tar";
   $tar_object = new Archive_Tar($tarfilename);
   $tar_object->setErrorHandling(PEAR_ERROR_PRINT);

   // ----- Creating the archive
   $v_list = array();
   $tar_object->create($v_list);
   $used = array();
   $filenum = rand(1,5000);
   while(in_array($used, $filenum)) {
      $filenum = rand(1,5000);
   }
   # filenum is cleared for uniqueness, go ahead
   array_push($used, $filenum);
   // create a single file for the batch download, set it to zero length
   $multifilename = str_replace("/", "\\", $basedir . "out/wateruse2008_merged$filenum" . ".htm");
   $batchfilename = str_replace("/", "\\", $basedir . "out/wateruse2008_batch$filenum" . ".htm");
   #$multifilename = str_replace("/", "\\", $basedir . 'out/blankrecords2008_1244.htm');
   $multidownload = "./out/wateruse2008_merged$filenum" . ".doc";
   $batchdownload = "./out/wateruse2008_batch$filenum" . ".htm";
   
   $fp = fopen($multifilename, 'w');
   fclose($fp);
   $fp = fopen($batchfilename, 'w');
   fclose($fp);
   
   // create an array to house the mailing addresses
   $addressfilename = "wateruse2008_labels$filenum" . ".xls";
   $alladdressfilename = "wateruse2008_batch$filenum" . ".xls";
   $addressfiledl = "./dirs/proj/out/wateruse2008_labels$filenum" . ".xls";
   $alladdressfiledl = "./dirs/proj/out/wateruse2008_batch$filenum" . ".xls";
   $mailaddresses = array();
   $alladdresses = array();
   #$mailaddresses[0] = array('name','title','mailadd1','mailadd2','mailcity','mailst','mailzip');
   # how many records can we store in a single file????
   $maxperfile = 400000; # max bytes in a file before we write it to a word document
   $j = 0; # keep track of how many we are putting out for formatting purposes
   foreach ($fac_userid as $facid) {
      # create the mailer
      $msg_string = file_get_contents("./mailform/msg_text.txt");
      # SEND MAIL
      # set the sender address to the responsible planners name and address for familiarity
      $mail = new phpMailer();
      $mail->SMTPDebug = 0;
      $mail->IsSMTP();
      $mail->Host = '172.16.1.68';
      #$mail->Protocol = 'ssl';
      #$mail->Host = "DEQEX01.deq.local";
      $mail->Port = 827;
      #$mail->SMTPAuth = true;
      $mail->Username = 'rwburgholzer';
      $mail->Mailer = 'smtp';
      # later we will set this to be the planner of interest, or the EDWrD administrator
      $mail->From = 'rwburgholzer@deq.virginia.gov';
      # get the planners name from the database by linking to the ownerid from the facilities record
      $listobject->querystring = "  select a.userid, a.firstname || ' ' || a.lastname as sender, a.email as replyto, ";
      $listobject->querystring .= " a.phone as contactphone, a.address1, a.address2, a.city, a.state, a.zip, b.ownerid ";
      $listobject->querystring .= " from users as a, facilities as b ";
      $listobject->querystring .= " where b.userid = '$facid' ";
      $listobject->querystring .= "    AND a.userid = b.ownerid ";
      $listobject->querystring .= " GROUP BY a.userid, sender, a.email, b.ownerid, a.phone, a.address1, a.address2, a.city, a.state, a.zip ";
      if ($debug) {
         $controlHTML .= $listobject->querystring . " ; <br>";
      }
      $listobject->performQuery();
      $headerdata = $listobject->queryrecords;
      $sendname = $listobject->getRecordValue(1,'sender');
      $replyto = $listobject->getRecordValue(1,'replyto');
      $plannerid = $listobject->getRecordValue(1,'userid');
      # being to set up summary data)
      if (!isset($summary[$plannerid])) {
         $summary[$plannerid] = array();
      }
      $mail->FromName = $sendname;
      $mail->AddReplyTo($replyto, $sendname);
      $controlHTML .= "Sender name: $sendname <br>";
      $controlHTML .= "Reply-to Address: $replyto <br>";
      # get the appropriate facility
      $listobject->querystring = "select * from facilities where userid = '$facid'";
      if ($debug) {
         $controlHTML .= $listobject->querystring . ";<br>";
      }
      $listobject->performQuery();
      $theserecs = $listobject->queryrecords;
      $facrec = $theserecs[0];
      $ownername = $facrec['ownname'];
      $facname = $facrec['facility'];
      $systemname = $facrec['system'];
         
      # stash the mailing address in an array
      array_push($mailaddresses, 
         array(
            'ownname'=>$facrec['ownname'], 
            'facility'=>$facrec['facility'], 
            'system'=>$facrec['system'], 
            'name'=>$facrec['name'], 
            'title'=>$facrec['title'], 
            'mailadd1'=>$facrec['mailadd1'], 
            'mailadd2'=>$facrec['mailadd2'], 
            'mailcity'=>$facrec['mailcity'], 
            'mailst'=>$facrec['mailst'], 
            'mailzip'=>$facrec['mailzip']
         )
      );
      # Also stash the mailing address in an array that will contain all, not paginated
      array_push($alladdresses, 
         array(
            'ownname'=>$facrec['ownname'], 
            'facility'=>$facrec['facility'], 
            'system'=>$facrec['system'], 
            'name'=>$facrec['name'], 
            'title'=>$facrec['title'], 
            'mailadd1'=>$facrec['mailadd1'], 
            'mailadd2'=>$facrec['mailadd2'], 
            'mailcity'=>$facrec['mailcity'], 
            'mailst'=>$facrec['mailst'], 
            'mailzip'=>$facrec['mailzip']
         )
      );
      # build recipient list
      foreach ($recip_type as $thistype) {
         if (!isset($summary[$plannerid][$facid])) {
            $summary[$plannerid][$facid] = array('sentto'=>'', 'msg'=>"$ownername, $facname, $systemname", 'numrecs'=>0);
         }
         $controlHTML .= "$thistype recipient requested.<br>";
         switch ($thistype) {
            case 'user_primary':
               $thisrecip = $facrec['email'];
            break;

            case 'user_secondary':
               $thisrecip = $facrec['email2'];
            break;

            case 'planner':
               # since the planner is the record owner, we have already retrieved this one
               $thisrecip = $replyto;
            break;

            case 'other':
               $other_userid = '';
               if (isset($formValues['other_userid'])) {
                  $other_userid = $formValues['other_userid'];
               } else {
                  $errorMSG .= "<b>Error:</b> You must select a user when specifying 'Other EDWrD User'.";
                  $error = 1;
               }
               if ($other_userid <> '') {
                  $listobject->querystring = "select email from users where userid = $other_userid";
                  if ($debug) {
                     $controlHTML .= $listobject->querystring . ' ; <br>';
                  }
                  $listobject->performQuery();
                  $other_email = $listobject->getRecordValue(1,'email');
               } else {
                  $errorMSG .= "<b>Error:</b> You must select a user when specifying 'Other EDWrD User'.";
                  $error = 1;
               }
               $thisrecip = $other_email;
            break;
         }
         if (strlen($thisrecip) > 0) {
            $mail->AddAddress($thisrecip,"Receiver");
            $controlHTML .= "Sending a copy to $thisrecip. <br>";
         } else {
            $errorMSG .= "<b>Error:</b> Selected recipient '$thistype' does not have a valid email address.";
            $error = 1;
         }

         $summary[$plannerid][$facid]['sentto'] .= " " . $thisrecip;
      }

      if ($error == 0) {
         # IF everything is OK, we go ahead and assemble the email attachment, and send the message
         # choose the header based on the method, since something in the "protect" command causes Word
         # to crash if you open more than 2 documents simultaneously.
         switch ($mailordownload) {
            case 'email':
               $header = file_get_contents("./mailform/edwrd_header_mail.html");
            break;

            case 'downloadword':
               $header = file_get_contents("./mailform/edwrd_header_print.html");
            break;

            case 'downloadhtml':
               $header = file_get_contents("./mailform/edwrd_header_print.html");
            break;
         }
         # now load the intro text
         $introtemplate = file_get_contents("./mailform/edwrd_intro.html");
         $aset = $adminsetuparray['plannercontact'];
         $intro = showCustomHTMLForm($listobject,$headerdata[0],$aset, $introtemplate, $ismulti, -1, 0);
         #$controlHTML .= "Header info<hr>" . $header . "<hr>";
         $footer = file_get_contents("./mailform/edwrd_footer.html");
         $content = file_get_contents("./mailform/edwrd_facility.html");
         $aset = $adminsetuparray['facilities'];
         $mindex = 0;
         $ismulti = 1;
         $facility = "<br clear=all style='mso-special-character:line-break; page-break-before:always'>";
         $facility .= showCustomHTMLForm($listobject,$facrec,$aset, $content, $ismulti, $mindex, 0);
         $mindex++;

         $content = file_get_contents("./mailform/edwrd_mpannual.html");
         $listobject->querystring = "select * from vwuds_annual_mp_data where \"USERID\" = '$facid' and \"YEAR\" = $thisyear ";
         if ($debug) {
            $controlHTML .= $listobject->querystring . " ; <br>";
         }
         $listobject->performQuery();
         $theserecs = $listobject->queryrecords;
         #print_r($theserecs);
         $aset = $adminsetuparray['annual_data'];
         # set fields that are read-only in EDWrD view for annual data to RW for the external form
         $ro_2_rw = array('lat_flt','lon_flt','stcofips','SOURCE','abandoned','CAT_MP','ACTION','GWPERMIT', 'VPDES', 'VDH_NUM','VWP_PERMIT', 'WELLNO', 'DEQ_WELL', 'SIC_MP');
         foreach ($ro_2_rw as $rocol) {
            $aset['column info'][$rocol]['readonly'] = 0;
         }
         $aset['column info']['USERID']['readonly'] = 1;
         $aset['column info']['USERID']['type'] = 1;
         $aset['column info']['SIC_MP']['type'] = 1;
         $annual = '';
         $numr = count($theserecs);
         $summary[$plannerid][$facid]['numrecs'] = $numr;
         foreach ($theserecs as $thisrec) {
            $annual .= "<br clear=all style='mso-special-character:line-break; page-break-before:always'>";
            $annual .= showCustomHTMLForm($listobject,$thisrec,$aset, $content, $ismulti, $mindex, 0);
            $mindex++;
         }


         $outHTML = $header;
         $outHTML .= $intro;
         $outHTML .= "<form method=post action='http://$mapservip/html/whodev/test/formtest.php'>";
         $outHTML .= $facility;
         $outHTML .= $annual;
         # don't show the submit button, we will add this later when we receive the form back from the end user.
         # this should eliminate confusion on the end users part, in case they think that the 'submit' button
         # actually works!!
         #$outHTML .= showSubmitButton('submit','submit', '', 1, 0);
         $outHTML .= "</form>";
         $outHTML .= $footer;
         # END - create attachment text
         $mail->Subject = "Virginia DEQ Water User Reporting Form, Facility ID: $facid";
         #$mail->Subject = "Virginia DEQ Water User Reporting Form";
         $mail->Body = $msg_string;

         # save text as HTML first, then open in word, and save as a .doc
         $filename = str_replace("/", "\\", $basedir . "out/wateruse2008_$facid" . ".htm");
         #$controlHTML .= "Output directory defined as " . $basedir . "<br>";
         #$controlHTML .= "Output file defined as " . $filename . "<br>";

         $fp = fopen($filename, 'w');
         fwrite($fp, $outHTML);
         fclose($fp);

         switch ($mailordownload) {
            case 'email':

               $word = new COM("word.application");
               $word->Documents->Open($filename);
               $new_filename = substr($filename,0,-4) . ".doc";
               // the '2' parameter specifies saving in txt format
               $word->Documents[1]->SaveAs($new_filename,1);
               $word->Documents[1]->Close(false);
               $word->Quit();
               //free object resources
               #$word->Release();
               $word = null;
            break;
            
            case 'downloadword':
               $mailHTML = '';
               if ($j == 0) {
                  # first time, so we put in the header info, otherwise, we just do the text
                  $mailHTML = $header;
               } else {
                  $mailHTML = "<br clear=all style='mso-special-character:line-break; page-break-before:always'>";
               }
               $mailHTML .= $intro;
               $mailHTML .= "<form method=post action='http://$mapservip/html/whodev/test/formtest.php' id=form$j>";
               $mailHTML .= $facility;
               $mailHTML .= $annual;
               # don't show the submit button, we will add this later when we receive the form back from the end user.
               # this should eliminate confusion on the end users part, in case they think that the 'submit' button
               # actually works!!
               #$outHTML .= showSubmitButton('submit','submit', '', 1, 0);
               $mailHTML .= "</form>";

               # need to stash this in a batch of files
               $controlHTML .= "Adding $facid to batch document, length: " . strlen($outHTML) . " characters.<br>";
               $fp = fopen($multifilename, 'a');
               fwrite($fp, $mailHTML);
               $mfsize += strlen($mailHTML);
               fclose($fp);
               
            break;
            
            case 'downloadhtml':
               $mailHTML = '';
               if ($j == 0) {
                  # first time, so we put in the header info, otherwise, we just do the text
                  $mailHTML = $header;
               } else {
                  $mailHTML = "<br clear=all style='mso-special-character:line-break; page-break-before:always'>";
               }
               $mailHTML .= $intro;
               $mailHTML .= "<form method=post action='http://$mapservip/html/whodev/test/formtest.php' id=form$j>";
               $mailHTML .= $facility;
               $mailHTML .= $annual;
               # don't show the submit button, we will add this later when we receive the form back from the end user.
               # this should eliminate confusion on the end users part, in case they think that the 'submit' button
               # actually works!!
               #$outHTML .= showSubmitButton('submit','submit', '', 1, 0);
               $mailHTML .= "</form>";

               # need to stash this in a batch of files
               $controlHTML .= "Adding $facid to batch document, length: " . strlen($outHTML) . " characters.<br>";
               $fp = fopen($batchfilename, 'a');
               fwrite($fp, $mailHTML);
               fclose($fp);
               
            break;
            
         }

         # now, create a blank record form in case these users have new measuring points to report on
         # save text as HTML first, then open in word, and save as a .doc
         $blankfilename = str_replace("/", "\\", $basedir . "out/blankrecords2008_$facid" . ".htm");
         #$controlHTML .= "Output directory defined as " . $basedir . "<br>";
         #$controlHTML .= "Output file defined as " . $filename . "<br>";
         $blankhead = file_get_contents("./mailform/edwrd_blankheader_mail.html");
         $blankform = "<br clear=all style='mso-special-character:line-break; page-break-before:always'>";
         $blankform .= "<form method=post action='http://$mapservip/html/whodev/test/formtest.php'>";
         
         $blankfields = $listobject->getColumns('vwuds_annual_mp_data');
         $aset = $adminsetuparray['annual_data'];
         # set fields that are read-only in EDWrD view for annual data to RW for the external form
         $ro_2_rw = array('lat_flt','lon_flt','stcofips','SOURCE','abandoned','CAT_MP','ACTION','GWPERMIT', 'VPDES', 'VDH_NUM','VWP_PERMIT', 'WELLNO', 'DEQ_WELL', 'TYPE', 'SUBTYPE', 'SIC_MP');
         foreach ($ro_2_rw as $rocol) {
            $aset['column info'][$rocol]['readonly'] = 0;
         }
         $aset['column info']['USERID']['readonly'] = 1;
         $aset['column info']['USERID']['type'] = 1;
         $aset['column info']['SIC_MP']['type'] = 1;
         foreach ($blankfields as $thisfield) {
            if ($aset['column info'][$thisfield]['visible'] == 1) {
               $blankrec[$thisfield] = '';
            }
         }
         $blankrecords = '';
         $content = file_get_contents("./mailform/edwrd_mpannual_blank.html");
         $blankrec['USERID'] = $facid;
         $blankrec['ownname'] = $ownername;
         $blankrec['facility'] = $facname;
         $blankrec['system'] = $systemname;
         for ($i = -1; $i >= -2; $i--) {
            if ($i < -1) {
               $blankrecords .= "<br clear=all style='mso-special-character:line-break; page-break-before:always'>";
            }
            $blankrecords .= showCustomHTMLForm($listobject,$blankrec,$aset, $content, $ismulti, $i, 0);
         }
         $blankHTML = '';
         $blankHTML .= $blankhead;
         $blankHTML .= $blankform;
         $blankHTML .= $blankrecords;
         $blankHTML .= "</form>";
         $blankHTML .= "</body></html>";
         
         $fp = fopen($blankfilename, 'w');
         fwrite($fp, $blankHTML);
         fclose($fp);


         switch ($mailordownload) {
            case 'email':
               $word = new COM("word.application");
               $word->Documents->Open($blankfilename);
               $new_blankfilename = substr($blankfilename,0,-4) . ".doc";
               // the '2' parameter specifies saving in txt format
               $word->Documents[1]->SaveAs($new_blankfilename,1);
               $word->Documents[1]->Close(false);
               $word->Quit();
               //free object resources
               #$word->Release();
               $word = null;
               #$mail->AddStringAttachment($outHTML, "water_reporting.doc");
               $mail->AddAttachment($new_filename);
               $mail->AddAttachment($new_blankfilename);
               $mail->AddAttachment($sic_file);

               if(!$mail->Send()) {
                  $controlHTML .= "<p>Message was not sent <p>";
                  $controlHTML .= "Mailer Error: " . $mail->ErrorInfo;
                  #$controlHTML .= "We WOULD have sent: <hr>" . $outHTML;
               } else {
                  $controlHTML .= "Message sent.";
                  # stash a record of this transaction
                  if (in_array('user_primary', $recip_type) or in_array('user_secondary', $recip_type)) {
                     $listobject->querystring = "  insert into edwrd_audit (userid, numrecs, recordyear, senderid) ";
                     $listobject->querystring .= " values ('$facid', $numr, $thisyear, $edwrduserid) ";
                     $listobject->performQuery();
                  }
               }
            break;

            case 'downloadword':
               # need to stash this in a batch of files
               $fp = fopen($multifilename, 'a');
               $mailHTML = '';
               $mailHTML .= $blankform;
               $mailHTML .= $blankrecords;
               $mailHTML .= "</form>";
               fwrite($fp, $mailHTML);
               $mfsize += strlen($mailHTML);
               fclose($fp);
               

              // ----- Adding more files
              #$tar_object->add($new_filename);
           break;

            case 'downloadhtml':
               # need to stash this in a batch of files
               
               $fp = fopen($batchfilename, 'a');
               $mailHTML = '';
               $mailHTML .= $blankform;
               $mailHTML .= $blankrecords;
               $mailHTML .= "</form>";
               fwrite($fp, $mailHTML);
               fclose($fp);

              // ----- Adding more files
              #$tar_object->add($new_filename);
           break;
        }

         # create temp document
         # attach email to doc
         # send email
         # delete temp document

         #print("<br>");
         #$debug = 1;

         #$controlHTML .= $reportdoc;
      }
      $j++;
      
      #$controlHTML .= "$mfsize length of output HTML file.<br>";
      if ( $mfsize >= $maxperfile) {
         # we have enough, now write out another batch
         # then we have to regenerate a new, unique file name, and open that file for writing

         // now, if this is a batch download request, we should make one big word doc with the records in it, and return it to the user
         if ($mailordownload == 'downloadword') {
            # need to finish off the document with the final HTML stuff

            $mailHTML = $footer;
            $fp = fopen($multifilename, 'a');
            fwrite($fp, $mailHTML);
            fclose($fp);

            $word = new COM("word.application");
            $word->Documents->Open($multifilename);
            $new_multifilename = substr($multifilename,0,-4) . ".doc";
            // the '2' parameter specifies saving in txt format
            $word->Documents[1]->SaveAs($new_multifilename,1);
            $word->Documents[1]->Close(false);
            $word->Quit();

            //free object resources
            #$word->Release();
            $word = null;
            
            # print out information and down load links for this file
            #$controlHTML .= "<a href='$tardownload'>Click Here to Download an Archive of Printable Forms</a>";
            $controlHTML .= "<a href='$multidownload' target=_new>Click Here to Download an Archive of Printable Forms</a>";
            putExcelFile("$outdir_nodrive/$addressfilename", $mailaddresses);
            $controlHTML .= " - <a href='$addressfiledl' target=_new>Spreadsheet File of Mailing Addresses.</a>";
            # generate new files
            $filenum = rand(1,5000);
            while(in_array($used, $filenum)) {
               $filenum = rand(1,5000);
            }
            # filenum is cleared for uniqueness, go ahead
            array_push($used, $filenum);
            // create a single file for the batch download, set it to zero length
            $multifilename = str_replace("/", "\\", $basedir . "out/wateruse2008_merged$filenum" . ".htm");
            #$multifilename = str_replace("/", "\\", $basedir . 'out/blankrecords2008_1244.htm');
            $multidownload = "./out/wateruse2008_merged$filenum" . ".doc";

            $fp = fopen($multifilename, 'w');
            fclose($fp);
            # reset mailing address array,
            $mailaddresses = array();
            $addressfilename = "wateruse2008_labels$filenum" . ".xls";
            $addressfiledl = "./dirs/proj/out/wateruse2008_labels$filenum" . ".xls";
            
            # finally, reset $j to zero so that we will get proper headers.
            $j = 0;
            $mfsize = 0;

         }
         
      }
   }
   if ($mailordownload == 'downloadhtml') {
      # need to finish off the document with the final HTML stuff

      $mailHTML = $footer;
      $fp = fopen($batchfilename, 'a');
      fwrite($fp, $mailHTML);
      fclose($fp);
      $controlHTML .= "<hr><a href='$batchdownload' target=_new>Click Here to Display all Printable Forms as a single HTML document</a>";
      putExcelFile("$outdir_nodrive/$alladdressfilename", $alladdresses);
      $controlHTML .= " - <a href='$alladdressfiledl' target=_new>Spreadsheet File of Mailing Addresses.</a>";

   }

   if ( ($progressreport == 1) and ($mailordownload == 'email')) {
      foreach ($summary as $pid => $sum) {
         $mail = new phpMailer();
         $mail->SMTPDebug = 0;
         $mail->IsSMTP();
         $mail->Host = '172.16.1.68';
         #$mail->Protocol = 'ssl';
         #$mail->Host = "DEQEX01.deq.local";
         $mail->Port = 827;
         #$mail->SMTPAuth = true;
         $mail->Username = 'rwburgholzer';
         $mail->Mailer = 'smtp';
         # later we will set this to be the planner of interest, or the EDWrD administrator
         $mail->From = 'rwburgholzer@deq.virginia.gov';
         $mail->FromName = 'Robert Burgholzer';
         $mail->AddReplyTo($mail->From, $mail->FromName);
         $listobject->querystring = " select email from users where userid = $pid ";
         if ($debug) {
            $controlHTML .= $listobject->querystring . " ; <br>";
         }
         $listobject->performQuery();
         $plannermail = $listobject->getRecordValue(1,'email');
         $mail->AddAddress($plannermail,"Receiver");
         #$mail->AddAddress($mail->From,"Receiver");
         $mail->Subject = 'EDWrD eMail Summary';
         $mail->Body = "The following facilities have been contacted on your behalf:\n";
         foreach ($sum as $thisrec) {
            $mail->Body .= $thisrec['numrecs'] . " record(s) for " . $thisrec['msg'] . " sent to: " . $thisrec['sentto'] ."\n\n";
         }
         if ($debug) {
            $controlHTML .= $mail->Body . " ; <br>";
            $controlHTML .= print_r($sum,1) . " ; <br>";
         }

         if(!$mail->Send()) {
            $controlHTML .= "<p>Progress report was not sent </p>";
            $controlHTML .= "Mailer Error: " . $mail->ErrorInfo;
            #$controlHTML .= "We WOULD have sent: <hr>" . $outHTML;
         } else {
            $controlHTML .= "Progress report sent.";
         }
      }
   }

   $controlHTML .= "<div id=errorinfo class='errorInfo'>" . $errorMSG . "</div>";


   return $controlHTML;

}

function facilityViewForm($formValues) {
   global $listobject, $fno, $adminsetuparray, $outdir_nodrive, $outurl, $userid, $usergroupids, $tmpdir, $outdir, $outurl, $goutdir, $gouturl;

   $controlHTML = '';
   $errorMSG = '';
   $resultMSG = '';
   #$debug = 1;
   $projectid = $formValues['projectid'];
   if (isset($formValues['quicksearch_userid'])) {
      $facilityid = $formValues['quicksearch_userid'];
      $quicksearch_userid = $formValues['quicksearch_userid'];
      $quicksearch_mpid = $formValues['quicksearch_mpid'];
   } else {
      $facilityid = '0241';
      $quicksearch_userid = '';
   }
   if (isset($formValues['quicksearch_enabled'])) {
      $quicksearch_enabled = $formValues['quicksearch_enabled'];
      if (!$quicksearch_enabled) {
         $quicksearch_userid = '';
         $quicksearch_mpid = '';
      }
   } else {
      $quicksearch_enabled = 0;
      $quicksearch_userid = '';
      $quicksearch_mpid = '';
   }
   $mpid = '-1';

   $controlHTML .= "<form method=post action='' id=control>";
   ############################################################
   ###                        SEARCH FORM                   ###
   ############################################################
   $tablename = 'facility_view';
   $aset = $adminsetuparray[$tablename];
   # call the search form routine.  This will take all of the variables that come in the search and interpret them,
   # returning the record of interest
   $searchobject = new listObjectSearchForm;
   $searchobject->listobject = $listobject;
   $searchobject->debug = FALSE;
   $searchobject->insertOK = 0;
   $searchobject->deleteOK = 0;
   $searchobject->adminsetup = $aset;
   $searchobject->readonly = 1;
   $searchobject->record_submit_script = "last_tab[\"facilitybased\"] = \"facilitybased_data0\";
 last_button[\"facilitybased\"] = \"facilitybased_0\"; xajax_showFacilityViewForm(xajax.getFormValues(\"control\")); ";
   $searchobject->search_submit_script = "last_tab[\"facilitybased\"] = \"facilitybased_data0\";
 last_button[\"facilitybased\"] = \"facilitybased_0\"; xajax_showFacilityViewForm(xajax.getFormValues(\"control\"));  ";
   $searchobject->page_submit_script = "last_tab[\"facilitybased\"] = \"facilitybased_data0\";
 last_button[\"facilitybased\"] = \"facilitybased_0\"; xajax_showFacilityViewForm(xajax.getFormValues(\"control\")); ";
   
   $controlHTML .= "<a class=\"mH\"";
   $controlHTML .= "onclick=\"toggleMenu('$divname" . "_search')\">+ Show/Hide Advanced Search Form</a>";
   $controlHTML .= "<div id=\"$divname" . "_search\" class=\"mL\"><ul>";
   # display the search form
   if ($quicksearch_enabled) {
      $quickvars = array();
      $searchobject->setVariableNames();
      $quickvars[$searchobject->searchnames['userid']['search_var']] = $quicksearch_userid;
      if ($quicksearch_mpid <> '') {
         $quickvars[$searchobject->searchnames['MPID']['search_var']] = $quicksearch_mpid;
      }
      $quickvars['search_view'] = 'edit';
      $controlHTML .= "Forcing quick search criteria to advanced search: " . print_r($quickvars,1) . "<br>";
      $searchForm = $searchobject->showSearchForm($quickvars);
   } else {
      #$controlHTML .= "No quick search requested.<br>";
      $searchForm = $searchobject->showSearchForm($formValues);
   }
   # get properties from search form
   $search_view = $searchForm['search_view'];
   $numrecs = $searchForm['numrecs'];
   $currentpos = $searchForm['currentpos'];
   
   $searchfields_jsarray = '[';
   $sdel = '';
   foreach (array_keys($aset['search info']['columns']) as $thisvar) {
      if (isset($aset['search info']['columns'][$thisvar]['search_var'])) {
         $thisvarname = $aset['search info']['columns'][$thisvar]['search_var'];
      } else {
         $thisvarname = 'srch_' . $thisvar;
      }
      $searchfields_jsarray .= $sdel . "\"$thisvarname\"";
      $sdel = ',';
   }
   $searchfields_jsarray .= ']';
   #$controlHTML .= "js search array: $searchfields_jsarray <BR>";
   $resetscript = "clearForm(\"control\", $searchfields_jsarray);";
   $controlHTML .= showGenericButton('resetform','Reset Search Form', $resetscript, 1) . "<br>";
   $controlHTML .= $searchForm['formHTML'];
   $controlHTML .= "</div><br>";
   
   $controlHTML .= "<i><b>Result Display Options: </b></i><table width=100%><tr align=center><td>" . $searchForm['searchOptions'] . '</td></tr></table>';
   $controlHTML .= "<hr>";
   
   # assemble the base query
   $subquery = "( select userid, ownname, facility, system from (" . $searchForm['query'] . " ) as foo ) as bar ";
   $controlHTML .= $subquery . " ;<br>";
   //$controlHTML .= showGenericButton('search','Search', "last_tab[\"facilitybased\"] = \"facilitybased_data0\"; last_button[\"facilitybased\"] = \"facilitybased_0\"; xajax_showFacilityViewForm(xajax.getFormValues(\"control\")); ", 1, $pd);
   #$controlHTML .= print_r($formValues,1) . "<br>";
   $controlHTML .= "<hr>";
   $controlHTML .= showHiddenField('quicksearch_enabled',0, 1);
   //$controlHTML .= showHiddenField('quicksearch_mpid',-1, 1);
   $controlHTML .= "<b>User ID:" . showWidthTextField('quicksearch_userid',$quicksearch_userid, 6, $quicksearch_userid, 1, 0) . " ";
   $controlHTML .= "<b>MPID (optional):" . showWidthTextField('quicksearch_mpid',$quicksearch_mpid, 16, $quicksearch_mpid, 1, 0) . " ";
   $controlHTML .= showGenericButton('quicksearch','Quick Search', "last_tab[\"facilitybased\"] = \"facilitybased_data0\";
 last_button[\"facilitybased\"] = \"facilitybased_0\"; document.forms[\"control\"].elements.quicksearch_enabled.value=1 ; xajax_showFacilityViewForm(xajax.getFormValues(\"control\")); ", 1, $pd);
   $controlHTML .= "<br>";
   
   ############################################################
   ###                    END SEARCH FORM                   ###
   ############################################################
   
   # did we get a save, insert or delete command?
   # if so, let's deal with it
   if (isset($formValues['searchtype'])) {
      $searchtype = $formValues['searchtype'];
   } else {
      $searchtype = '';
   }
   $resultMSG = '';
   
   switch ($searchtype) {
      case 'save':
         $controlHTML .= "Saving records:<br>";
         #$controlHTML .= print_r($formValues,1) . "<br>";
         # record 0 will have facility information
         # record 1 will ahve MP info
         
         
         # records 2-n if they exist, will have the annual data
         # look for annual_data records
         if (isset($formValues['userid'])) {
            $uid = $formValues['userid'][0];
            # save facility updates
            $formvars = array();
            #$controlHTML .= "Checking for variables: ";
            foreach (array_keys($formValues) as $thisvar) {
               #$controlHTML .= "<br>" . $thisvar . ' ';
               if (isset($formValues[$thisvar][0])) {
                  #$thisvar is part of the annual data record, so include it in the form variables
                  $formvars[$thisvar] = $formValues[$thisvar][0];
                  #$controlHTML .= '(found) ';
               }
            }
            #$controlHTML .= "<br>";
            #$controlHTML .= print_r($formvars, 1) . "<br>";
            #$listobject->performQuery();
            $aset = $adminsetuparray['facilities'];
            # force these, since the multiform wants a PK, but we can't actually use the REAL pk (rec_id)
            # because it is possible that a records rec_id may have changed between form mail out and return
            # thus, we simply set mpid as a read-only pk, and later we will add our own where criteria
            # to make sure that we don't miss it
            #$aset['table info']['pkcol'] = 'mpid';
            #$formvars['mpid'] = $mpid;

            $update_rec = processMultiFormVars($listobject,$formvars,$aset,0,$debug, $nullpk = -1, $strictnull = 0);
            #print_r($update_rec);
            #$controlHTML .= print_r($formvars, 1) . "<br>";
            #$controlHTML .= print_r($update_rec, 1) . "<br>";
            #if ($debug) {
               $taboutput->tab_HTML['debug'] .= $update_rec['debugstr'] . " ;<br>";
               $controlHTML .= $update_rec['debugstr'] . " ;<br>";
            #}
            $rec_saved = 0;
            if (isset($formvars['userid'])) {
               $uid = $formvars['userid'];
               if ($uid == -1) {
                  # insert
                  $controlHTML .= "Blank record insert requested<br>";
                  $listobject->querystring = $update_rec['insertsql'];
                  # don't import yet, we will do this later, or write a nother script to handle this seperately
                  #$controlHTML .= "$listobject->querystring ; <br>";
                  #$listobject->performQuery();
               } else {
                  # update
                  #$controlHTML .= "Update requested<br>";
                  $listobject->querystring = "UPDATE facilities SET " . $update_rec['updatequery'] . " WHERE \"userid\" = '$uid'";
                  if ($debug) {
                     $controlHTML .= "$listobject->querystring ; <br>";
                  }
                  $listobject->performQuery();
               }
               if ($listobject->error) {
                  $errorMSG .= $listobject->error . "<br>";
               } else {
                  $rec_saved++;
               }
            }
            if ($rec_saved) {
               $resultMSG .= "Facility Record $uid updated.<br>";
            }
         }
               
         if (isset($formValues['MPID'])) {
            $mpid = $formValues['MPID'][1];
         } else {
            $mpid = -1;
         }
         if ($mpid <> -1) {
            # can onkly move forward with annual data records, or MPI records if MPID is also set!
            # later, we will use this as a means to generate a new MPID, but for now, just ignore

            # save MP updates
            $formvars = array();
            #$controlHTML .= "Checking for variables: ";
            foreach (array_keys($formValues) as $thisvar) {
               #$controlHTML .= $thisvar . ' ';
               if (isset($formValues[$thisvar][1])) {
                  #$thisvar is part of the annual data record, so include it in the form variables
                  $formvars[$thisvar] = $formValues[$thisvar][1];
                  #$controlHTML .= '(found) ';
               }
            }
            #$controlHTML .= "<br>";
            #$controlHTML .= print_r($formvars, 1) . "<br>";
            #$listobject->performQuery();
            $aset = $adminsetuparray['vwuds_measuring_point'];
            #$aset['column info']['YEAR']['readonly'] = 1;
            # force these, since the multiform wants a PK, but we can't actually use the REAL pk (rec_id)
            # because it is possible that a records rec_id may have changed between form mail out and return
            # thus, we simply set mpid as a read-only pk, and later we will add our own where criteria
            # to make sure that we don't miss it
            #$aset['table info']['pkcol'] = 'mpid';
            #$formvars['mpid'] = $mpid;

            $update_rec = processMultiFormVars($listobject,$formvars,$aset,0,$debug, $nullpk = -1, 1);
            #print_r($update_rec);
            #$controlHTML .= print_r($update_rec, 1) . "<br>";
            $rec_saved = 0;
            if (isset($formvars['record_id'])) {
               $record_id = $formvars['record_id'];
               if ($mpid == -1) {
                  # insert
                  $controlHTML .= "Blank record insert requested<br>";
                  $listobject->querystring = $update_rec['insertsql'];
                  # don't import yet, we will do this later, or write a nother script to handle this seperately
                  #$controlHTML .= "$listobject->querystring ; <br>";
                  #$listobject->performQuery();
               } else {
                  # update
                  #$controlHTML .= "Update requested<br>";
                  $listobject->querystring = "UPDATE vwuds_measuring_point SET " . $update_rec['updatequery'] . " WHERE record_id = $record_id";
                  if ($debug) {
                     $controlHTML .= "$listobject->querystring ; <br>";
                  }
                  $listobject->performQuery();
               }
               if ($listobject->error) {
                  $errorMSG .= "Measuring Point record $mpid: " . $listobject->error . "<br>";
                  $errorMSG .= "$listobject->querystring ; <br>";
               } else {
                  $rec_saved++;
               }
            }
            if ($rec_saved) {
               $resultMSG .= "MP Record $mpid updated.<br>";
            }
               
            # save annual_data updates
            $rec_saved = 0;
            if (isset($formValues['JANUARY'])) {
               foreach (array_keys($formValues['JANUARY']) as $postkey) {
                  $formvars = array();
                  #$controlHTML .= "Checking for variables: ";
                  foreach (array_keys($formValues) as $thisvar) {
                     #$controlHTML .= $thisvar . ' ';
                     if (isset($formValues[$thisvar][$postkey])) {
                        #$thisvar is part of the annual data record, so include it in the form variables
                        $formvars[$thisvar] = $formValues[$thisvar][$postkey];
                        #$controlHTML .= '(found) ';
                     }
                  }
                  #$controlHTML .= "<br>";
                  #$controlHTML .= print_r($formvars, 1) . "<br>";
                  #$listobject->performQuery();
                  $aset = $adminsetuparray['annual_data'];
                  $yr = $formValues['JANUARY'][$postkey];
                  # force these, since the multiform wants a PK, but we can't actually use the REAL pk (rec_id)
                  # because it is possible that a records rec_id may have changed between form mail out and return
                  # thus, we simply set mpid as a read-only pk, and later we will add our own where criteria
                  # to make sure that we don't miss it
                  #$aset['table info']['pkcol'] = 'mpid';
                  #$formvars['mpid'] = $mpid;

                  $update_rec = processMultiFormVars($listobject,$formvars,$aset,0,$debug, $nullpk = -1, $strictnull = 0);
                  #print_r($update_rec);
                  #$controlHTML .= print_r($update_rec, 1) . "<br>";
                  if (isset($formvars['recid'])) {
                     $recid = $formvars['recid'];
                     if ($mpid == -1) {
                        # insert
                        $controlHTML .= "Blank record insert requested<br>";
                        $listobject->querystring = $update_rec['insertsql'];
                        # don't import yet, we will do this later, or write a nother script to handle this seperately
                        #$controlHTML .= "$listobject->querystring ; <br>";
                        #$listobject->performQuery();
                     } else {
                        # update
                        #$controlHTML .= "Update requested<br>";
                        $listobject->querystring = "UPDATE annual_data SET " . $update_rec['updatequery'] . " WHERE \"recid\" = '$recid'";
                        #$controlHTML .= "$listobject->querystring ; <br>";
                        $listobject->performQuery();
                     }
                     $rec_saved++;
                  }
                  if ($listobject->error) {
                     $errorMSG .= "Annual Data record for $yr: " . $listobject->error . "<br>";
                  } else {
                     $rec_saved++;
                  }
               }
            }
            if ($rec_saved) {
               $resultMSG .= "$rec_saved Annual Record(s) Saved.<br>";
            }
         }
      break;
      
   }

   #$search_view = 'list'; # force edit view for now
   $listobject->show = 0;
   if ($debug) {
      $controlHTML .= "Search View: $search_view <br>";
   }
   if ($debug) {
      $controlHTML .= $searchForm['debug'];
   }
   $controlHTML .= "<div id=errorinfo class='errorInfo'>" . $errorMSG . "</div>";
   $controlHTML .= "<div id=errorinfo class='resultInfo'>" . $resultMSG . "</div>";
   $controlHTML .= "<i><b>Search Results:</b> Search Returned $numrecs record(s) matching your criteria.</i><br>";
   $controlHTML .= "<br>Viewing Record " . ($currentpos + 1) . " out of $numrecs<br>";
   #$controlHTML .= print_r($searchForm, 1) . "<br>";
   $props = $searchForm['recordvalue'];
   
   switch ($search_view) {
      case 'list':
         $controlHTML .= "<div style=\"overflow: auto; height: 600px; width: 800px;\">";
         $listobject->queryrecords = $props;
         #$listobject->tablename = 'vwuds_measuring_point';
         $listobject->showList();
         $controlHTML .= $listobject->outstring;
         $controlHTML .= "</div>";
      break;
      
      case 'detail' || 'edit':
         
         # create a paneled object
         # Panel #1: Facility Record
         # Panel #2: MP record
         # Panel #3: All withdrawal data for this facility/MP sorted by year, descending
         # panel #4: comments and other measurement info for each year, descending
         # Need to have q2uick navigation to other measuring points of this same facility
         # and quick navigation to other facilities by entering userid
         $taboutput = new tabbedListObject;
         $taboutput->name = 'facilitybased';
         $taboutput->tab_names = array('facility','measuringpoint','annualdata','additional','trends');
         #if ($debug) {
            array_push($taboutput->tab_names, 'debug');
         #}
         $taboutput->tab_buttontext = array(
            'facility'=>'Facility Info',
            'measuringpoint'=>'Measuring Point',
            'annualdata'=>'Annual Data',
            'additional'=>'Other Info',
            'pending_review'=>'Submittal Pending Review',
            'trends'=>'Trend Analysis',
            'debug'=>'Debug Info'
         );
         $taboutput->init();
         $taboutput->tab_HTML['facility'] .= "<b><font face='arial'>Facility Information:</font></b><br>";
         $taboutput->tab_HTML['measuringpoint'] .= "<b><font face='arial'>Measuring Point Info:</font></b><br>";
         $taboutput->tab_HTML['annualdata'] .= "<b><font face='arial'>Annual Reporting:</font></b><br>";
         $taboutput->tab_HTML['pending_review'] .= "<b><font face='arial'>Annual Submittal Data Pending Review:</font></b><br>";
         $taboutput->tab_HTML['additional'] .= "<b><font face='arial'>Additional Information:</font></b><br>";
         $taboutput->tab_HTML['trends'] .= "<b><font face='arial'>Trend Analysis:</font></b><br>";
         if ($debug) {
            $taboutput->tab_HTML['debug'] .= "<b><font face='arial'>Debugging Information:</font></b><br>";
         }

         #$controlHTML .= "Props: " . print_r($props,1) . "<br>";
         # get facility information
         $facilityid = $props['userid'];
         $gid = $props['gid'];
         if (isset($props['MPID'])) {
            $mpid = $props['MPID'];
         }
         $controlHTML .= "<b>Facility ID: $facilityid, Measuring Point: $mpid<br>";
         
         $adminsetup = $adminsetuparray['facilities'];
         $perms = getVWUDSPerms($listobject, $adminsetup, $gid, $userid, $usergroupids, 1);
         $ap = $perms['rowperms'] & 2;
         $facreadonly = 1;
         if ( ($perms['rowperms'] & 2) or ($perms['tableperms'] & 2)) {
            $facreadonly = 0;
         }
         if ($debug) {
            $taboutput->tab_HTML['debug'] .= print_r($perms, 1) . "<br> Read-Only (Facility Screen): $facreadonly <br>";
         }
         $adminname = 'facilities';
         $adminsetup = $adminsetuparray[$adminname];
         $tblname = $adminsetup['table info']['tabledef'];
         $listobject->querystring = "  select * from $tblname where userid = '$facilityid' ";
         if ($debug) {
            $taboutput->tab_HTML['debug'] .= $listobject->querystring . " ;<br>";
         }
         $listobject->performQuery();
         $facrecord = $listobject->queryrecords[0];

         $mindex = 0;
         $ismulti = 1;
         #$facility = showCustomHTMLForm($listobject,$facrec,$aset, $content, $ismulti, $mindex, 0);
         $content = file_get_contents("./forms/facility.html");
         $taboutput->tab_HTML['facility'] .= showCustomHTMLForm($listobject,$facrecord,$adminsetup, $content, $ismulti, $mindex, $debug = 0, $facreadonly);#$taboutput->tab_HTML['facility'] .= showFormVars($listobject,$facrecord,$adminsetup,0, 1, $debug, 1, 1, 0, $fno, $mindex, 0);
         $mindex++;

         # get measuring point information
         $adminname = 'vwuds_measuring_point';
         $adminsetup = $adminsetuparray[$adminname];
         $tblname = $adminsetup['table info']['tabledef'];
         $listobject->querystring = "  select * from $tblname where \"USERID\" = '$facilityid' ";
         if ($mpid <> '-1') {
            $listobject->querystring .= " and \"MPID\" = '$mpid' ";
         } else {
            # if we have not specified a measuring point, we just grab the first one that comes along
            $listobject->querystring .= " LIMIT 1 ";
         }
         if ($debug) {
            $taboutput->tab_HTML['debug'] .= $listobject->querystring . " ;<br>";
         }
         $listobject->performQuery();
         $mprecord = $listobject->queryrecords[0];
         $mpid = $mprecord['MPID'];
         $record_id = $mprecord['record_id'];
         $perms = getVWUDSPerms($listobject, $adminsetup, $record_id, $userid, $usergroupids, 1);
         $ap = $perms['rowperms'] & 2;
         $mpreadonly = 1;
         if ( ($perms['rowperms'] & 2) or ($perms['tableperms'] & 2)) {
            $mpreadonly = 0;
         }
         if ($debug) {
            $taboutput->tab_HTML['debug'] .= print_r($perms,1) . " <br> MP ro - $mpreadonly;<br>";
         }

         $ismulti = 1;
         #$facility = showCustomHTMLForm($listobject,$facrec,$aset, $content, $ismulti, $mindex, 0);
         $content = file_get_contents("./forms/measuring_point.html");
         $taboutput->tab_HTML['measuringpoint'] .= showCustomHTMLForm($listobject,$mprecord,$adminsetup, $content, $ismulti, $mindex, $debug = 0, $mpreadonly);
         #$taboutput->tab_HTML['measuringpoint'] .= showFormVars($listobject,$mprecord,$adminsetup,0, 1, $debug, 1, 1, 0, $fno, $mindex, 0);
         $mindex++;

         ###########################################
         # BEGIN - Annual DATA Tab
         ###############################################
         # get annual data information
         $adminname = 'annual_data';
         $adminsetup = $adminsetuparray[$adminname];
         $adminsetup['column info']['YEAR']['readonly'] = 1;
         $tblname = $adminsetup['table info']['tabledef'];
         $listobject->querystring = "  select * from $tblname where \"USERID\" = '$facilityid' ";
         $listobject->querystring .= " and \"MPID\" = '$mpid' ";
         $listobject->querystring .= " ORDER BY \"YEAR\" DESC ";
         #if ($debug) {
            $taboutput->tab_HTML['debug'] .= $listobject->querystring . " ;<br>";
         #}
         $listobject->performQuery();
         $adrecord = $listobject->queryrecords;
         if ($debug) {
            $taboutput->tab_HTML['debug'] .= print_r($adrecord, 1) . " ;<br>";
         }

         $ismulti = 1;
         #$facility = showCustomHTMLForm($listobject,$facrec,$aset, $content, $ismulti, $mindex, 0);
         $taboutput->tab_HTML['annualdata'] .= "<table>";
         $taboutput->tab_HTML['annualdata'] .= "<tr>" . file_get_contents("./forms/annual_data_header.html") . "</tr>";
         $template = file_get_contents("./forms/annual_data_row.html");
         $writable = 0;
         $anlog = array();
         $yrs = 0;
         # set up tally for annual data trends
         $addmonth = array(1=>array('total'=>0,'pct'=>0),
            1=>array('total'=>0,'pct'=>0, 'month'=>'Jan'),
            2=>array('total'=>0,'pct'=>0, 'month'=>'Feb'),
            3=>array('total'=>0,'pct'=>0, 'month'=>'Mar'),
            4=>array('total'=>0,'pct'=>0, 'month'=>'Apr'),
            5=>array('total'=>0,'pct'=>0, 'month'=>'May'),
            6=>array('total'=>0,'pct'=>0, 'month'=>'Jun'),
            7=>array('total'=>0,'pct'=>0, 'month'=>'Jul'),
            8=>array('total'=>0,'pct'=>0, 'month'=>'Aug'),
            9=>array('total'=>0,'pct'=>0, 'month'=>'Sep'),
            10=>array('total'=>0,'pct'=>0, 'month'=>'Oct'),
            11=>array('total'=>0,'pct'=>0, 'month'=>'Nov'),
            12=>array('total'=>0,'pct'=>0, 'month'=>'Dec')
         );
         
         foreach ($adrecord as $thisrec) {
            
            ###########################################
            # BEGIN - Annual Edit Form
            ###############################################
            #$taboutput->tab_HTML['annualdata'] .= showFormVars($listobject,$thisrec,$adminsetup,0, 1, $debug, 1, 1, 0, $fno, $mindex, 0);
            $pkval = $thisrec['recid'];
            $perms = getVWUDSPerms($listobject, $adminsetup, $pkval, $userid, $usergroupids, 1);
            $ap = $perms['rowperms'] & 2;
            $readonly = 1;
            if ($perms['rowperms'] & 2) {
               $readonly = 0;
            } 
            # keep track of the number of writeable records
            if (!$readonly) {
               $writable++;
            }
            if ($debug) {
               $taboutput->tab_HTML['debug'] .= print_r($perms, 1) . "<br> Read-Only: $readonly <br>";
            }
            $taboutput->tab_HTML['annualdata'] .= "<tr> " . showCustomHTMLForm($listobject,$thisrec,$adminsetup, $template, $ismulti, $mindex, 0, $readonly) . "</tr>";
            $mindex++;
            ###########################################
            # END - Annual Edit Form
            ###############################################
            ###########################################
            # BEGIN - trend Analysis data
            ###############################################
            $anlog[$thisrec['YEAR']] = array('thisyear'=>$thisrec['YEAR'], 'total'=>$thisrec['ANNUAL/365'], 'maxdaily'=>$thisrec['MAXDAY']);
            if ($thisrec['ANNUAL'] > 0) {
               $yrs++;
               $addmonth[1]['total'] = $addmonth[1]['total'] + $thisrec['JANUARY'] / $thisrec['ANNUAL'];
               $addmonth[2]['total'] += $thisrec['FEBRUARY'] / $thisrec['ANNUAL'];
               $addmonth[3]['total'] += $thisrec['MARCH'] / $thisrec['ANNUAL'];
               $addmonth[4]['total'] += $thisrec['APRIL'] / $thisrec['ANNUAL'];
               $addmonth[5]['total'] += $thisrec['MAY'] / $thisrec['ANNUAL'];
               $addmonth[6]['total'] += $thisrec['JUNE'] / $thisrec['ANNUAL'];
               $addmonth[7]['total'] += $thisrec['JULY'] / $thisrec['ANNUAL'];
               $addmonth[8]['total'] += $thisrec['AUGUST'] / $thisrec['ANNUAL'];
               $addmonth[9]['total'] += $thisrec['SEPTEMBER'] / $thisrec['ANNUAL'];
               $addmonth[10]['total'] += $thisrec['OCTOBER'] / $thisrec['ANNUAL'];
               $addmonth[11]['total'] += $thisrec['NOVEMBER'] / $thisrec['ANNUAL'];
               $addmonth[12]['total'] += $thisrec['DECEMBER'] / $thisrec['ANNUAL'];
            } 
            ###########################################
            # END - trend Analysis data
            ###############################################
         }
         $taboutput->tab_HTML['annualdata'] .= "</table>";

         ###########################################
         # END - Annual DATA Tab
         ###############################################
         
         ###############################################
         ### create a trends analysis of collected data
         ###############################################
         # finish up data summaries for plots
         if ($yrs > 0) {
            for ($d = 1; $d <= 12; $d++) {
               $addmonth[$d]['pct'] = $addmonth[$d]['total'] / $yrs;
            }
         }
         $angraph = new GraphObject;
         $angraph->sessionid = $mpid;
         $angraph->componentid = 1;
         $angraph->init();
         $angraph->outdir = $outdir;
         $angraph->outurl = $outurl;
         $angraph->goutdir = $goutdir;
         $angraph->gouturl = $gouturl;
         $angraph->tmpdir = $tmpdir;
         $angraph->logtable = array();
         $angraph->log2db = 0;
         $angraph->logRetrieved = 1;
         $angraph->graphtype = 'multi';
         $angraph->xlabel = 'Year';
         $angraph->ylabel = 'Total Use (MGD)';
         
         $andata = new GraphComponent;
         $andata->init();
         $andata->xcol = 'thisyear';
         $andata->ycol = 'total';
         $andata->graphtype = 'bar';
         
         $andata1 = new GraphComponent;
         $andata1->init();
         $andata1->xcol = 'thisyear';
         $andata1->ycol = 'maxdaily';
         $andata1->color = 'red';
         $andata1->graphtype = 'bar';
         
         $angraph->logtable = $anlog;
         sort($angraph->logtable);
         $angraph->addOperator('annualtrend', $andata, 0);
         $angraph->addOperator('annualmax', $andata1, 0);
         $angraph->finish();
         
         $mograph = new GraphObject;
         $mograph->sessionid = $mpid;
         $mograph->componentid = 2;
         $mograph->init();
         $mograph->outdir = $outdir;
         $mograph->outurl = $outurl;
         $mograph->goutdir = $goutdir;
         $mograph->gouturl = $gouturl;
         $mograph->tmpdir = $tmpdir;
         $mograph->logtable = array();
         $mograph->log2db = 0;
         $mograph->logRetrieved = 1;
         $mograph->x_interval = 1;
         $mograph->graphtype = 'multi';
         $mograph->xlabel = '';
         $mograph->ylabel = 'Percent of Annual Use';
         
         $modata = new GraphComponent;
         $modata->init();
         $modata->xcol = 'month';
         $modata->ycol = 'pct';
         $modata->graphtype = 'bar';
         $mograph->logtable = $addmonth;
         $mograph->addOperator('annualtrend', $modata, 0);
         $mograph->finish();
         
         $taboutput->tab_HTML['trends'] .= "<table><tr>";
         $taboutput->tab_HTML['trends'] .= "<td align=center><b>Annual Totals By Year</b><br>" . $angraph->graphstring . "</td>";
         $taboutput->tab_HTML['trends'] .= "<td align=center><b>Average Use By Month</b><br>" . $mograph->graphstring . "</td>";
         $taboutput->tab_HTML['trends'] .= "</tr></table>";
         ###############################################
         ###  END - Trends Analysis
         ###############################################
         
         ###############################################
         ###  START - Pending Record Review Form
         ###############################################
         
         # make the form printed out read-only, in the style that it was mailed (looks as if you are reviewing a paper copy)
         # then, we can avoid the conflicts between the variable names in the MPID and facility records
         # the only variables that need to be returned are a lookup for a new MPID (if it
         
         ###############################################
         ###  END - Pending Record Review Form
         ###############################################

         $taboutput->createTabListView();
         $controlHTML .= $taboutput->innerHTML;
      break;
   }
   if ( ($writable > 0) or (!$facreadonly) or (!$mpreadonly)) {
      $searchobject->readonly = 0;
   }
   #$controlHTML .= "Writeable: $writable , fac $facreadonly <br>";
   $controlHTML .= "<table><tr><td width=33%>";
   $controlHTML .= $searchobject->showPrevNextRecordButtons();
   $controlHTML .= "</td>";
   $controlHTML .= "<td width=33%>&nbsp;";
   $controlHTML .= "</td>";
   $controlHTML .= "<td width=33%>";
   $controlHTML .= $searchobject->showSaveButton('');
   $controlHTML .= "</td>";
   $controlHTML .= "</tr></table>";
   #$controlHTML .= $searchForm['navButtonHTML'] ;
   $controlHTML .= "</form>";
   return $controlHTML;
}

function facilityViewForm2($formValues) {
   global $listobject, $fno, $adminsetuparray, $outdir_nodrive, $outurl, $userid, $usergroupids, $tmpdir, $outdir, $outurl, $goutdir, $gouturl;

   $controlHTML = '';
   $errorMSG = '';
   $resultMSG = '';
   #$debug = 1;
   $facilityid = '5241';
   $thisyear = '2008';
   $projectid = $formValues['projectid'];

   $mpid = '-1';

   $controlHTML .= "<form method=post action='' id=control>";
   
   # did we get a save, insert or delete command?
   # if so, let's deal with it
   if (isset($formValues['actiontype'])) {
      $actiontype = $formValues['actiontype'];
   } else {
      $actiontype = '';
   }
   $controlHTML .= showHiddenField('actiontype',$actiontype, 1);
   $resultMSG = '';
   
   
   $controlHTML .= "New school form";
   
   switch ($actiontype) {
      case 'save':
         $controlHTML .= "Saving records:<br>";
         #$controlHTML .= print_r($formValues,1) . "<br>";
         # record 0 will have facility information
         # record 1 will ahve MP info
         # records 2-n if they exist, will have the annual data
         # look for annual_data records
         if (isset($formValues['userid'])) {
            $uid = $formValues['userid'][0];
            # save facility updates
            $formvars = array();
            #$controlHTML .= "Checking for variables: ";
            foreach (array_keys($formValues) as $thisvar) {
               #$controlHTML .= "<br>" . $thisvar . ' ';
               if (isset($formValues[$thisvar][0])) {
                  #$thisvar is part of the annual data record, so include it in the form variables
                  $formvars[$thisvar] = $formValues[$thisvar][0];
                  #$controlHTML .= '(found) ';
               }
            }
            #$controlHTML .= "<br>";
            #$controlHTML .= print_r($formvars, 1) . "<br>";
            #$listobject->performQuery();
            $aset = $adminsetuparray['facilities'];
            # force these, since the multiform wants a PK, but we can't actually use the REAL pk (rec_id)
            # because it is possible that a records rec_id may have changed between form mail out and return
            # thus, we simply set mpid as a read-only pk, and later we will add our own where criteria
            # to make sure that we don't miss it
            #$aset['table info']['pkcol'] = 'mpid';
            #$formvars['mpid'] = $mpid;

            $update_rec = processMultiFormVars($listobject,$formvars,$aset,0,$debug, $nullpk = -1, $strictnull = 0);
            #print_r($update_rec);
            #$controlHTML .= print_r($formvars, 1) . "<br>";
            #$controlHTML .= print_r($update_rec, 1) . "<br>";
            #if ($debug) {
               $taboutput->tab_HTML['debug'] .= $update_rec['debugstr'] . " ;<br>";
               $controlHTML .= $update_rec['debugstr'] . " ;<br>";
            #}
            $rec_saved = 0;
            if (isset($formvars['userid'])) {
               $uid = $formvars['userid'];
               if ($uid == -1) {
                  # insert
                  $controlHTML .= "Blank record insert requested<br>";
                  $listobject->querystring = $update_rec['insertsql'];
                  # don't import yet, we will do this later, or write a nother script to handle this seperately
                  #$controlHTML .= "$listobject->querystring ; <br>";
                  #$listobject->performQuery();
               } else {
                  # update
                  #$controlHTML .= "Update requested<br>";
                  $listobject->querystring = "UPDATE facilities SET " . $update_rec['updatequery'] . " WHERE \"userid\" = '$uid'";
                  //if ($debug) {
                     $controlHTML .= "$listobject->querystring ; <br>";
                  //}
                  //$listobject->performQuery();
               }
               if ($listobject->error) {
                  $errorMSG .= $listobject->error . "<br>";
               } else {
                  $rec_saved++;
               }
            }
            if ($rec_saved) {
               $resultMSG .= "Facility Record $uid updated.<br>";
            }
         }
               
         if (isset($formValues['MPID'])) {
            $mpid = $formValues['MPID'][1];
         } else {
            $mpid = -1;
         }
         if ($mpid <> -1) {
            # can onkly move forward with annual data records, or MPI records if MPID is also set!
            # later, we will use this as a means to generate a new MPID, but for now, just ignore

            # save MP updates
            $formvars = array();
            #$controlHTML .= "Checking for variables: ";
            foreach (array_keys($formValues) as $thisvar) {
               #$controlHTML .= $thisvar . ' ';
               if (isset($formValues[$thisvar][1])) {
                  #$thisvar is part of the annual data record, so include it in the form variables
                  $formvars[$thisvar] = $formValues[$thisvar][1];
                  #$controlHTML .= '(found) ';
               }
            }
            #$controlHTML .= "<br>";
            #$controlHTML .= print_r($formvars, 1) . "<br>";
            #$listobject->performQuery();
            $aset = $adminsetuparray['vwuds_measuring_point'];
            #$aset['column info']['YEAR']['readonly'] = 1;
            # force these, since the multiform wants a PK, but we can't actually use the REAL pk (rec_id)
            # because it is possible that a records rec_id may have changed between form mail out and return
            # thus, we simply set mpid as a read-only pk, and later we will add our own where criteria
            # to make sure that we don't miss it
            #$aset['table info']['pkcol'] = 'mpid';
            #$formvars['mpid'] = $mpid;

            $update_rec = processMultiFormVars($listobject,$formvars,$aset,0,$debug, $nullpk = -1, 1);
            #print_r($update_rec);
            #$controlHTML .= print_r($update_rec, 1) . "<br>";
            $rec_saved = 0;
            if (isset($formvars['record_id'])) {
               $record_id = $formvars['record_id'];
               if ($mpid == -1) {
                  # insert
                  $controlHTML .= "Blank record insert requested<br>";
                  $listobject->querystring = $update_rec['insertsql'];
                  # don't import yet, we will do this later, or write a nother script to handle this seperately
                  #$controlHTML .= "$listobject->querystring ; <br>";
                  #$listobject->performQuery();
               } else {
                  # update
                  #$controlHTML .= "Update requested<br>";
                  $listobject->querystring = "UPDATE vwuds_measuring_point SET " . $update_rec['updatequery'] . " WHERE record_id = $record_id";
                  //if ($debug) {
                     $controlHTML .= "$listobject->querystring ; <br>";
                  //}
                  $listobject->performQuery();
               }
               if ($listobject->error) {
                  $errorMSG .= "Measuring Point record $mpid: " . $listobject->error . "<br>";
                  $errorMSG .= "$listobject->querystring ; <br>";
               } else {
                  $rec_saved++;
               }
            }
            if ($rec_saved) {
               $resultMSG .= "MP Record $mpid updated.<br>";
            }
               
            # save annual_data updates
            $rec_saved = 0;
            if (isset($formValues['JANUARY'])) {
               foreach (array_keys($formValues['JANUARY']) as $postkey) {
                  $formvars = array();
                  #$controlHTML .= "Checking for variables: ";
                  foreach (array_keys($formValues) as $thisvar) {
                     #$controlHTML .= $thisvar . ' ';
                     if (isset($formValues[$thisvar][$postkey])) {
                        #$thisvar is part of the annual data record, so include it in the form variables
                        $formvars[$thisvar] = $formValues[$thisvar][$postkey];
                        #$controlHTML .= '(found) ';
                     }
                  }
                  #$controlHTML .= "<br>";
                  #$controlHTML .= print_r($formvars, 1) . "<br>";
                  #$listobject->performQuery();
                  $aset = $adminsetuparray['annual_data'];
                  $yr = $formValues['JANUARY'][$postkey];
                  # force these, since the multiform wants a PK, but we can't actually use the REAL pk (rec_id)
                  # because it is possible that a records rec_id may have changed between form mail out and return
                  # thus, we simply set mpid as a read-only pk, and later we will add our own where criteria
                  # to make sure that we don't miss it
                  #$aset['table info']['pkcol'] = 'mpid';
                  #$formvars['mpid'] = $mpid;

                  $update_rec = processMultiFormVars($listobject,$formvars,$aset,0,$debug, $nullpk = -1, $strictnull = 0);
                  #print_r($update_rec);
                  #$controlHTML .= print_r($update_rec, 1) . "<br>";
                  if (isset($formvars['recid'])) {
                     $recid = $formvars['recid'];
                     if ($mpid == -1) {
                        # insert
                        $controlHTML .= "Blank record insert requested<br>";
                        $listobject->querystring = $update_rec['insertsql'];
                        # don't import yet, we will do this later, or write a nother script to handle this seperately
                        #$controlHTML .= "$listobject->querystring ; <br>";
                        #$listobject->performQuery();
                     } else {
                        # update
                        #$controlHTML .= "Update requested<br>";
                        $listobject->querystring = "UPDATE annual_data SET " . $update_rec['updatequery'] . " WHERE \"recid\" = '$recid'";
                        $controlHTML .= "$listobject->querystring ; <br>";
                        //$listobject->performQuery();
                     }
                     $rec_saved++;
                  }
                  if ($listobject->error) {
                     $errorMSG .= "Annual Data record for $yr: " . $listobject->error . "<br>";
                  } else {
                     $rec_saved++;
                  }
               }
            }
            if ($rec_saved) {
               $resultMSG .= "$rec_saved Annual Record(s) Saved.<br>";
            }
         }
      break;
      
   }
   

   $search_view = 'edit'; # force edit view for now
   $listobject->show = 0;
   if ($debug) {
      $controlHTML .= "Search View: $search_view <br>";
   }
   
   $controlHTML .= "<div id=errorinfo class='errorInfo'>" . $errorMSG . "</div>";
   $controlHTML .= "<div id=errorinfo class='resultInfo'>" . $resultMSG . "</div>";
   $controlHTML .= "<i><b>Search Results:</b> Search Returned $numrecs record(s) matching your criteria.</i><br>";
   $controlHTML .= "<br>Viewing Record " . ($currentpos + 1) . " out of $numrecs<br>";
   #$controlHTML .= print_r($searchForm, 1) . "<br>";
   
   switch ($search_view) {
      case 'list':
         $controlHTML .= "<div style=\"overflow: auto; height: 600px; width: 800px;\">";
         //$listobject->queryrecords = $props;
         #$listobject->tablename = 'vwuds_measuring_point';
         $listobject->showList();
         $controlHTML .= $listobject->outstring;
         $controlHTML .= "</div>";
      break;
      
      case 'detail' || 'edit':
         
         # create a paneled object
         # Panel #1: Facility Record
         # Panel #2: MP record
         # Panel #3: All withdrawal data for this facility/MP sorted by year, descending
         # panel #4: comments and other measurement info for each year, descending
         # Need to have q2uick navigation to other measuring points of this same facility
         # and quick navigation to other facilities by entering userid
         $taboutput = new tabbedListObject;
         $taboutput->name = 'facilitybased';
         $taboutput->tab_names = array('facility','measuringpoint','additional','trends');
         #if ($debug) {
            array_push($taboutput->tab_names, 'debug');
         #}
         $taboutput->tab_buttontext = array(
            'facility'=>'Facility Info',
            'measuringpoint'=>'Measuring Point / Annual Data',
            'additional'=>'Other Info',
            'pending_review'=>'Submittal Pending Review',
            'trends'=>'Trend Analysis',
            'debug'=>'Debug Info'
         );
         $taboutput->init();
         $taboutput->tab_HTML['facility'] .= "<b><font face='arial'>Facility Information:</font></b><br>";
         $taboutput->tab_HTML['measuringpoint'] .= "<b><font face='arial'>Measuring Point Info / Annual Data:</font></b><br>";
         $taboutput->tab_HTML['pending_review'] .= "<b><font face='arial'>Annual Submittal Data Pending Review:</font></b><br>";
         $taboutput->tab_HTML['additional'] .= "<b><font face='arial'>Additional Information:</font></b><br>";
         $taboutput->tab_HTML['trends'] .= "<b><font face='arial'>Trend Analysis:</font></b><br>";
         if ($debug) {
            $taboutput->tab_HTML['debug'] .= "<b><font face='arial'>Debugging Information:</font></b><br>";
         }

         #$controlHTML .= "Props: " . print_r($props,1) . "<br>";
         # get facility information
         if (isset($props['MPID'])) {
            $mpid = $props['MPID'];
         }
         $controlHTML .= "<b>Facility ID: $facilityid, Measuring Point: $mpid<br>";
         
         $adminsetup = $adminsetuparray['facilities'];
         $listobject->querystring = "  select gid from $tblname where userid = '$facilityid' ";
         if ($debug) {
            $taboutput->tab_HTML['debug'] .= $listobject->querystring . " ;<br>";
         }
         $listobject->performQuery();
         $gid = $listobject->getRecordValue(1,'groupid');
         $perms = getVWUDSPerms($listobject, $adminsetup, $gid, $userid, $usergroupids, 1);
         $ap = $perms['rowperms'] & 2;
         $facreadonly = 1;
         if ( ($perms['rowperms'] & 2) or ($perms['tableperms'] & 2)) {
            $facreadonly = 0;
         }
         if ($debug) {
            $taboutput->tab_HTML['debug'] .= print_r($perms, 1) . "<br> Read-Only (Facility Screen): $facreadonly <br>";
         }
         $adminname = 'facilities';
         $adminsetup = $adminsetuparray[$adminname];
         $tblname = $adminsetup['table info']['tabledef'];
         $listobject->querystring = "  select * from $tblname where userid = '$facilityid' ";
         if ($debug) {
            $taboutput->tab_HTML['debug'] .= $listobject->querystring . " ;<br>";
         }
         $listobject->performQuery();
         $facrecord = $listobject->queryrecords[0];

         $mindex = 0;
         $ismulti = 1;
         #$facility = showCustomHTMLForm($listobject,$facrec,$aset, $content, $ismulti, $mindex, 0);
         $content = file_get_contents("./forms/facility.html");
         $taboutput->tab_HTML['facility'] .= showCustomHTMLForm($listobject,$facrecord,$adminsetup, $content, $ismulti, $mindex, $debug = 0, $facreadonly);#$taboutput->tab_HTML['facility'] .= showFormVars($listobject,$facrecord,$adminsetup,0, 1, $debug, 1, 1, 0, $fno, $mindex, 0);
         $mindex++;

         # get measuring point information
         $adminname = 'annual_data';
         $adminsetup = $adminsetuparray[$adminname];
         # set fields that are read-only in EDWrD view for annual data to RW for the external form
         $ro_2_rw = array('lat_flt','lon_flt','stcofips','SOURCE','abandoned','CAT_MP','GWPERMIT', 'VPDES', 'VDH_NUM','VWP_PERMIT', 'WELLNO', 'DEQ_WELL', 'SIC_MP');
         foreach ($ro_2_rw as $rocol) {
            $adminsetup['column info'][$rocol]['readonly'] = 0;
         }
         $adminsetup['column info']['USERID']['readonly'] = 1;
         $adminsetup['column info']['USERID']['type'] = 1;
         $adminsetup['column info']['SIC_MP']['type'] = 1;
         
         $tblname = $adminsetup['table info']['tabledef'];
         $listobject->querystring = "  select * from $tblname where \"USERID\" = '$facilityid' ";
         $listobject->querystring .= " and ( (\"SUBYR_MP\" is null) ";
         $listobject->querystring .= "    OR ( \"SUBYR_MP\" = '')  ";
         $listobject->querystring .= "    OR ( \"SUBYR_MP\" > $thisyear ) ";
         $listobject->querystring .= " ) ";
         $listobject->querystring .= " and \"YEAR\" = $thisyear ";
            
         //if ($debug) {
            $taboutput->tab_HTML['debug'] .= $listobject->querystring . " ;<br>";
         //}
         $listobject->performQuery();
         
         $mprecs = $listobject->queryrecords;
         
         $content = file_get_contents("./forms/edwrd_mpannual.html");
         foreach ($mprecs as $mprecord) {
            
         }
         $mpdatadiv = '';
         $mpheaderdiv = '';
         $mpscroll_list = array();
         foreach ($mprecs as $mprecord) {
            $mpid = $mprecord['MPID'];
            $record_id = $mprecord['record_id'];
            array_push($mpscroll_list, array('linkid'=>"record$mpid",'linktext'=>"MP $mpid"));
            $perms = getVWUDSPerms($listobject, $adminsetup, $record_id, $userid, $usergroupids, 1);
            $ap = $perms['rowperms'] & 2;
            $mpreadonly = 1;
            if ( ($perms['rowperms'] & 2) or ($perms['tableperms'] & 2)) {
               $mpreadonly = 0;
            }
            if ($debug) {
               $taboutput->tab_HTML['debug'] .= print_r($perms,1) . " <br> MP ro - $mpreadonly;<br>";
            }

            $ismulti = 1;
            #$facility = showCustomHTMLForm($listobject,$facrec,$aset, $content, $ismulti, $mindex, 0);
            $mpdatadiv .= "<div id='record$mpid'>";
            $mpdatadiv .= showCustomHTMLForm($listobject,$mprecord,$adminsetup, $content, $ismulti, $mindex, $debug = 0, $mpreadonly);
            $mpdatadiv .= "</div>";
            #$mpdatadiv .= showFormVars($listobject,$mprecord,$adminsetup,0, 1, $debug, 1, 1, 0, $fno, $mindex, 0);
            $mpdatadiv .= "<hr>";
            $mindex++;
         }
         $mpheaderdiv .= "<div id='mpheader' height=48>";
         # need to add a javascript to show the 'Measuring Point/Annual Data' panel before the showIt() routine is called
         # this will have the desired effect.
         $onchange = "var ql=document.getElementById(\"mpquicklist\"); elid=ql.value; showIt(elid);";
         $mpheaderdiv .= showActiveList($mpscroll_list, 'jumpmp', '', 'linktext','linkid', '','', $onchange, 'linktext', $debug, 1, 0, 'mpquicklist');
         $mpheaderdiv .= "</div>";
         $mpcount = count($mpscroll_list);
         $mpjumptext = "<b>There are $mpcount MP's for this facility. </b><br>Select an MPID from this list to jump to the desired MPID." . $mpheaderdiv;
         //$taboutput->tab_HTML['measuringpoint'] .= $mpheaderdiv;
         $taboutput->tab_HTML['measuringpoint'] .= $mpdatadiv;


         ###########################################
         # BEGIN - Annual DATA Tab
         ###############################################
         # get annual data information
         $adminname = 'annual_data';
         $adminsetup = $adminsetuparray[$adminname];
         $adminsetup['column info']['YEAR']['readonly'] = 1;
         $tblname = $adminsetup['table info']['tabledef'];
         $listobject->querystring = "  select * from $tblname where \"USERID\" = '$facilityid' ";
         $listobject->querystring .= " ORDER BY \"YEAR\" DESC ";
         #if ($debug) {
            $taboutput->tab_HTML['debug'] .= $listobject->querystring . " ;<br>";
         #}
         $listobject->performQuery();
         $adrecord = $listobject->queryrecords;
         if ($debug) {
            $taboutput->tab_HTML['debug'] .= print_r($adrecord, 1) . " ;<br>";
         }

         $ismulti = 1;
         $writable = 0;
         $anlog = array();
         $yrs = 0;
         # set up tally for annual data trends
         $addmonth = array(1=>array('total'=>0,'pct'=>0),
            1=>array('total'=>0,'pct'=>0, 'month'=>'Jan'),
            2=>array('total'=>0,'pct'=>0, 'month'=>'Feb'),
            3=>array('total'=>0,'pct'=>0, 'month'=>'Mar'),
            4=>array('total'=>0,'pct'=>0, 'month'=>'Apr'),
            5=>array('total'=>0,'pct'=>0, 'month'=>'May'),
            6=>array('total'=>0,'pct'=>0, 'month'=>'Jun'),
            7=>array('total'=>0,'pct'=>0, 'month'=>'Jul'),
            8=>array('total'=>0,'pct'=>0, 'month'=>'Aug'),
            9=>array('total'=>0,'pct'=>0, 'month'=>'Sep'),
            10=>array('total'=>0,'pct'=>0, 'month'=>'Oct'),
            11=>array('total'=>0,'pct'=>0, 'month'=>'Nov'),
            12=>array('total'=>0,'pct'=>0, 'month'=>'Dec')
         );
         
         foreach ($adrecord as $thisrec) {
            
            ###########################################
            # BEGIN - trend Analysis data
            ###############################################
            $anlog[$thisrec['YEAR']] = array('thisyear'=>$thisrec['YEAR'], 'total'=>$thisrec['ANNUAL/365'], 'maxdaily'=>$thisrec['MAXDAY']);
            if ($thisrec['ANNUAL'] > 0) {
               $yrs++;
               $addmonth[1]['total'] = $addmonth[1]['total'] + $thisrec['JANUARY'] / $thisrec['ANNUAL'];
               $addmonth[2]['total'] += $thisrec['FEBRUARY'] / $thisrec['ANNUAL'];
               $addmonth[3]['total'] += $thisrec['MARCH'] / $thisrec['ANNUAL'];
               $addmonth[4]['total'] += $thisrec['APRIL'] / $thisrec['ANNUAL'];
               $addmonth[5]['total'] += $thisrec['MAY'] / $thisrec['ANNUAL'];
               $addmonth[6]['total'] += $thisrec['JUNE'] / $thisrec['ANNUAL'];
               $addmonth[7]['total'] += $thisrec['JULY'] / $thisrec['ANNUAL'];
               $addmonth[8]['total'] += $thisrec['AUGUST'] / $thisrec['ANNUAL'];
               $addmonth[9]['total'] += $thisrec['SEPTEMBER'] / $thisrec['ANNUAL'];
               $addmonth[10]['total'] += $thisrec['OCTOBER'] / $thisrec['ANNUAL'];
               $addmonth[11]['total'] += $thisrec['NOVEMBER'] / $thisrec['ANNUAL'];
               $addmonth[12]['total'] += $thisrec['DECEMBER'] / $thisrec['ANNUAL'];
            } 
            ###########################################
            # END - trend Analysis data
            ###############################################
         }
         
         ###############################################
         ### create a trends analysis of collected data
         ###############################################
         # finish up data summaries for plots
         if ($yrs > 0) {
            for ($d = 1; $d <= 12; $d++) {
               $addmonth[$d]['pct'] = $addmonth[$d]['total'] / $yrs;
            }
         }
         $angraph = new GraphObject;
         $angraph->sessionid = $mpid;
         $angraph->componentid = 1;
         $angraph->init();
         $angraph->outdir = $outdir;
         $angraph->outurl = $outurl;
         $angraph->goutdir = $goutdir;
         $angraph->gouturl = $gouturl;
         $angraph->tmpdir = $tmpdir;
         $angraph->logtable = array();
         $angraph->log2db = 0;
         $angraph->logRetrieved = 1;
         $angraph->graphtype = 'multi';
         $angraph->xlabel = 'Year';
         $angraph->ylabel = 'Total Use (MGD)';
         
         $andata = new GraphComponent;
         $andata->init();
         $andata->xcol = 'thisyear';
         $andata->ycol = 'total';
         $andata->graphtype = 'bar';
         
         $andata1 = new GraphComponent;
         $andata1->init();
         $andata1->xcol = 'thisyear';
         $andata1->ycol = 'maxdaily';
         $andata1->color = 'red';
         $andata1->graphtype = 'bar';
         
         $angraph->logtable = $anlog;
         sort($angraph->logtable);
         $angraph->addOperator('annualtrend', $andata, 0);
         $angraph->addOperator('annualmax', $andata1, 0);
         $angraph->finish();
         
         $mograph = new GraphObject;
         $mograph->sessionid = $mpid;
         $mograph->componentid = 2;
         $mograph->init();
         $mograph->outdir = $outdir;
         $mograph->outurl = $outurl;
         $mograph->goutdir = $goutdir;
         $mograph->gouturl = $gouturl;
         $mograph->tmpdir = $tmpdir;
         $mograph->logtable = array();
         $mograph->log2db = 0;
         $mograph->logRetrieved = 1;
         $mograph->x_interval = 1;
         $mograph->graphtype = 'multi';
         $mograph->xlabel = '';
         $mograph->ylabel = 'Percent of Annual Use';
         
         $modata = new GraphComponent;
         $modata->init();
         $modata->xcol = 'month';
         $modata->ycol = 'pct';
         $modata->graphtype = 'bar';
         $mograph->logtable = $addmonth;
         $mograph->addOperator('annualtrend', $modata, 0);
         $mograph->finish();
         
         $taboutput->tab_HTML['trends'] .= "<table><tr>";
         $taboutput->tab_HTML['trends'] .= "<td align=center><b>Annual Totals By Year</b><br>" . $angraph->graphstring . "</td>";
         $taboutput->tab_HTML['trends'] .= "<td align=center><b>Average Use By Month</b><br>" . $mograph->graphstring . "</td>";
         $taboutput->tab_HTML['trends'] .= "</tr></table>";
         ###############################################
         ###  END - Trends Analysis
         ###############################################
         
         ###############################################
         ###  START - Pending Record Review Form
         ###############################################
         
         # make the form printed out read-only, in the style that it was mailed (looks as if you are reviewing a paper copy)
         # then, we can avoid the conflicts between the variable names in the MPID and facility records
         # the only variables that need to be returned are a lookup for a new MPID (if it
         
         ###############################################
         ###  END - Pending Record Review Form
         ###############################################

         $taboutput->createTabListView();
         $controlHTML .= $mpjumptext;
         $controlHTML .= $taboutput->innerHTML;
      break;
   }

/*
   if ( ($writable > 0) or (!$facreadonly) or (!$mpreadonly)) {
      $searchobject->readonly = 0;
   }
   #$controlHTML .= "Writeable: $writable , fac $facreadonly <br>";
   $controlHTML .= "<table><tr><td width=33%>";
   $controlHTML .= $searchobject->showPrevNextRecordButtons();
   $controlHTML .= "</td>";
   $controlHTML .= "<td width=33%>&nbsp;";
   $controlHTML .= "</td>";
   $controlHTML .= "<td width=33%>";
   $controlHTML .= $searchobject->showSaveButton('');
   $controlHTML .= "</td>";
   $controlHTML .= "</tr></table>";
   #$controlHTML .= $searchForm['navButtonHTML'] ;
*/
   $save_script = "last_tab[\"facilitybased\"] = \"facilitybased_data0\"; last_button[\"facilitybased\"] = \"facilitybased_0\"; document.forms[\"control\"].elements.actiontype.value=\"save\";  xajax_showFacilityViewForm(xajax.getFormValues(\"control\")); ";
   
   
   $controlHTML .= showGenericButton('save','Save Reporting Form', $save_script, 1, 0);
   $controlHTML .= "</form>";
   return $controlHTML;
}

function annualDataCreationForm($formValues) {
   global $listobject, $userid, $adminsetuparray, $usergroupids;

   $controlHTML = '';

   # check user permissions
   $aset = $adminsetuparray['annual_data'];
   $perms = getVWUDSPerms($listobject, $aset, $pkval, $userid, $usergroupids, 1);
   if ($debug) {
      $controlHTML .= print_r($perms, 1) . "<br>";
   }

   if ($perms['tableperms'] & 1) {
      $caninsert = 1;
   } else {
      $controlHTML .= "<b>Error:</b> You do not have permission to create blank reporting records.<br>";
      return $controlHTML;
   }

   if (isset($formValues['thisyear'])) {
      $thisyear = $formValues['thisyear'];
   } else {
      $thisyear = date('Y');
   }
   if (isset($formValues['selected_only'])) {
      $selected_only = $formValues['selected_only'];
   } else {
      $selected_only = 1;
   }
   if (isset($formValues['replace_existing'])) {
      $replace_existing = $formValues['replace_existing'];
   } else {
      $replace_existing = 0;
   }
   if (isset($formValues['fac_userid'])) {
      $fac_userid = $formValues['fac_userid'][0];
   } else {
      $fac_userid = array();
   }
   #error_reporting(E_ALL);

   $controlHTML .= "<form method=post action='' id=control>";
   $controlHTML .= "<b>Select Facility(s):</b><br>";
   ############################################################
   ###                        SEARCH FORM                   ###
   ############################################################
   $tablename = 'facilities';
   $aset = $adminsetuparray[$tablename];
   # call the search form routine.   This will take all of the variables that come in the search and interpret them,
   # returning the record of interest
   $searchobject = new listObjectSearchForm;
   $searchobject->listobject = $listobject;
   $searchobject->debug = FALSE;
   $searchobject->insertOK = 0;
   $searchobject->deleteOK = 0;
   $searchobject->adminsetup = $aset;
   $searchobject->readonly = 1;
   $searchobject->record_submit_script = '';
   $searchobject->search_submit_script = "xajax_showAnnualDataCreationForm(xajax.getFormValues(\"control\"));  ";
   $searchobject->page_submit_script = "";
   $controlHTML .= "<a class=\"mH\"";
   $controlHTML .= "onclick=\"toggleMenu('$divname" . "_search')\">+ Show/Hide Advanced Search Form</a>";
   $controlHTML .= "<div id=\"$divname" . "_search\" class=\"mL\"><ul>";
   # display the search form
   $searchForm = $searchobject->showSearchForm($formValues);
   $controlHTML .= $searchForm['formHTML'];
   $controlHTML .= "</div><br>";
   $subquery = "( select userid, ownname, facility, system from (" . $searchForm['query'] . " ) as foo ) as bar ";
   #$controlHTML .= $subquery . " ;<br>";
   $controlHTML .= "<br>";
   $controlHTML .= showGenericButton('search','Search', "xajax_showAnnualDataCreationForm(xajax.getFormValues(\"control\")); ", 1, $pd);
   $controlHTML .= "<hr>";

   ############################################################
   ###                    END SEARCH FORM                   ###
   ############################################################
   $controlHTML .= "<b>Select Facilities:</b><br>";
   $controlHTML .= showMultiList2($listobject, 'fac_userid', $subquery, 'userid', 'ownname, facility, system', $fac_userid, '', 'ownname, facility', 0, 8, 1, 0);
   $controlHTML .= "<b>Select Option(s):</b><br>";
   $controlHTML .= "<ul>";
   $controlHTML .= "<li>" . showWidthTextField('thisyear', $thisyear, 8, '', 1) . "Year to create</li>";
   $controlHTML .= "<li>" . showCheckBox('replace_existing',1,$replace_existing, '', 1) . "Overwrite Existing Records?</li>";
   $controlHTML .= "<li>" . showCheckBox('selected_only',1,$selected_only, '', 1) . "Only create records for selected facilities?</li>";
   $controlHTML .= "</ul>";
   $controlHTML  .= showGenericButton('createrecords','Create Blank Annual Records', "xajax_showAnnualDataCreationResult(xajax.getFormValues(\"control\")); ", 1, 0);
   $controlHTML .= "</form>";

   return $controlHTML;
}


function annualDataCreationResult($formValues) {
   global $listobject, $userid, $adminsetuparray, $usergroupids;

   # LIMITATIONS:
   # 1. This currently only allows access to records by facility USERID, which means that
   #    individual measuring points cannot be added and subtracted, only all the points by group

   $controlHTML = '';

   # check user permissions
   $aset = $adminsetuparray['annual_data'];
   $perms = getVWUDSPerms($listobject, $aset, $pkval, $userid, $usergroupids, 1);
   if ($debug) {
      $controlHTML .= print_r($perms, 1) . "<br>";
   }

   if ($perms['tableperms'] & 1) {
      $caninsert = 1;
   } else {
      $errorMSG .= "<b>Error:</b> You do not have permission to create blank reporting records.<br>";
      $controlHTML .= "<div id=errorinfo class='errorInfo'>" . $errorMSG . "</div>";
      return $controlHTML;
   }

   if (isset($formValues['thisyear'])) {
      $thisyear = $formValues['thisyear'];
   } else {
      $thisyear = date('Y');
   }
   if (isset($formValues['selected_only'])) {
      $selected_only = $formValues['selected_only'];
   } else {
      $selected_only = 1;
   }
   if (isset($formValues['replace_existing'])) {
      $replace_existing = $formValues['replace_existing'];
   } else {
      $replace_existing = 0;
   }
   if (isset($formValues['fac_userid'])) {
      $fac_userid = $formValues['fac_userid'][0];
   } else {
      $fac_userid = array();
   }

   # need to report on records that user tried to replace/delete that are locked (perms do not match)
   $numlocked = 0;
   if ( $replace_existing ) {
      $listobject->querystring = "  select count(*) as numlocked from annual_data ";
      $listobject->querystring .= " where \"YEAR\" = $thisyear ";
      $listobject->querystring .= " AND \"USERID\" in ( ";
      $listobject->querystring .= "    select \"USERID\" from annual_data ";
      $listobject->querystring .= "    where \"YEAR\" = $thisyear ";
      if ( $selected_only and (count($fac_userid) > 0)) {
         $listobject->querystring .= "       and \"USERID\" in ( '" . join("','", $fac_userid) . "')";
      }
      $listobject->querystring .= "  ) ";
      # verify that records are not locked
      $listobject->querystring .= "     AND NOT ((gperms & 1) = 1) ";
      if ($debug) {
         $controlHTML .= $listobject->querystring . " ; <br>";
      }
      $listobject->performQuery();
      $numlocked = $listobject->getRecordValue(1,'numlocked');
   }

   if ($numlocked > 0) {
      $errorMSG .= "<b>Error:</b> There were $numlocked records in the requested data set that were NOT replaced.<br>";
      $errorMSG .= "Please consult the system administrator if you need to unlock these records.<br>";
   }

   if ( $replace_existing ) {
      $controlHTML .= "<b>Notice: </b> Attempting to delete existing records for selected facilities in year $thisyear.<br>";
      $listobject->querystring = "  delete from annual_data ";
      $listobject->querystring .= " where \"YEAR\" = $thisyear ";
      $listobject->querystring .= " AND \"USERID\" in ( ";
      $listobject->querystring .= "    select \"USERID\" from annual_data ";
      $listobject->querystring .= "    where \"YEAR\" = $thisyear ";
      if ( $selected_only and (count($fac_userid) > 0)) {
         $listobject->querystring .= "       and \"USERID\" in ( '" . join("','", $fac_userid) . "')";
      }
      $listobject->querystring .= "  ) ";
      # verify that records are not locked
      $listobject->querystring .= "     AND ((gperms & 1) = 1) ";
      #if ($debug) {
         $controlHTML .= $listobject->querystring . " ; <br>";
      #}
      $listobject->performQuery();
   } else {
      $controlHTML .= "<b>Notice: </b> Will NOT overwrite any existing records for $thisyear.<br>";
   }

   $controlHTML .= "<b>Notice: </b> Attempting to insert blank records for selected facilities in year $thisyear.<br>";
   #error_reporting(E_ALL);
   $listobject->querystring = "  insert into annual_data (\"YEAR\", \"USERID\", \"ACTION\", \"MPID\", ";
   $listobject->querystring .= "    \"REGION\", source, ownerid ) ";
   # fails on missing region
   $listobject->querystring .= "  select thisyear, userid, \"ACTION\", mpmpid, \"REGION\", \"SOURCE\", ownerid ";
   $listobject->querystring .= " from (";
   $listobject->querystring .= "    select foo.thisyear, foo.userid, foo.\"ACTION\", foo.\"MPID\" as mpmpid, ";
   $listobject->querystring .= "       bar.\"MPID\" AS annual_mpid, foo.\"REGION\", foo.\"SOURCE\", foo.ownerid ";
   $listobject->querystring .= "    from ( ";
   $listobject->querystring .= "    select $thisyear as thisyear, b.userid, a.\"ACTION\", a.\"MPID\", ";
   $listobject->querystring .= "        a.\"REGION\", a.\"SOURCE\", c.ownerid ";
   $listobject->querystring .= "     from vwuds_measuring_point as a, facilities as b ";
   $listobject->querystring .= "     left outer join vwuds_deq_regions as c  ";
   $listobject->querystring .= "     on (b.region = c.reg_id) ";
   $listobject->querystring .= "     where a.\"USERID\" = b.userid  ";
   $listobject->querystring .= "        and  ";
   $listobject->querystring .= "           ( ( a.\"SUBYR_MP\" is null ) ";
   $listobject->querystring .= "           OR (a.\"SUBYR_MP\" = '' ) ";
   $listobject->querystring .= "           OR (a.\"SUBYR_MP\" > $thisyear) ";
   $listobject->querystring .= "        ) ";
   # only get selected of all possible
   if ( $selected_only and (count($fac_userid) > 0)) {
      $listobject->querystring .= "   AND b.userid in ( '" . join("','", $fac_userid) . "')";
   }
   $listobject->querystring .= "    ) as foo left outer join annual_data as bar ";
   $listobject->querystring .= "    on (foo.userid = bar.\"USERID\" ";
   $listobject->querystring .= "        and foo.\"MPID\" = bar.\"MPID\" ";
   $listobject->querystring .= "        and foo.\"ACTION\" = bar.\"ACTION\" ";
   $listobject->querystring .= "        and bar.\"YEAR\" = $thisyear";
   $listobject->querystring .= "       ) ";
   # this outer join insures that any entry that is ALREADY IN the table WILL HAVE A Non-Null bar.MPID
   # if any of the three JOIN conditions, ACTION, MPID, or USERID do not match
   # if bar.MPID IS NULL, then it is OK to insert
   $listobject->querystring .= "    where bar.\"MPID\" is null ";
   $listobject->querystring .= "       and foo.\"MPID\" is not null and foo.\"MPID\" <> '' ";
   $listobject->querystring .= " ) as foobar ";
   #if ($debug) {
      $controlHTML .= $listobject->querystring . " ; <br>";
   #}
   $listobject->performQuery();

   $controlHTML .= "<div id=errorinfo class='errorInfo'>" . $errorMSG . "</div>";

   return $controlHTML;
}

function droughtIndicatorForm($formValues) {
   global $listobject;

   $controlHTML = '';

   if (isset($formValues['thismetric'])) {
      $thismetric = $formValues['thismetric'];
   }
   $projectid = $formValues['projectid'];
   $currentgroup = $formValues['currentgroup'];
   $lreditlist = $formValues['lreditlist'];
   $allgroups = $formValues['allgroups'];
   $startdate = $formValues['startdate'];
   $enddate = $formValues['enddate'];
   #$debug = 1;
   if ($startyear == '') { $startyear = Date('Y'); }
   if ($endyear == '') { $endyear = Date('Y'); }
   #print_r($allsegs);
   $controlHTML .= "<form id=control name=control>";
   $controlHTML .= showHiddenField('projectid', $projectid, 1);
   $controlHTML .= showHiddenField('currentgroup', $currentgroup, 1);
   $controlHTML .= showHiddenField('lreditlist', $lreditlist, 1);
   $controlHTML .= showCheckBox('allgroups', 1, $allgroups, $onclick='', 1);
   $controlHTML .= " <b>Show Stats for All Groups (default is active group only)</b>";
   $controlHTML .= "<br>";
   $controlHTML  .= "<b>Start Date: </b> " . showWidthTextField('startdate', $startdate, 8, '', 1);
   $controlHTML  .= "<b>End Date: </b> " . showWidthTextField('enddate', $enddate, 8, '', 1) . '<br>';
   /*
   $controlHTML  .= "<br><b>Select Summary Period:</b><br>";
   $disql = "  (select thismetric, min(startdate) || ' to ' || max(enddate) as thisrange ";
   $disql .= " from proj_group_stat ";
   $disql .= " where projectid = $projectid ";
   $disql .= "    and gid = $currentgroup ";
   $disql .= "    and ( ( thismetric like 'tmp_flow%' ) ";
   $disql .= "        or ( thismetric like 'roll365flow%' ) ";
   $disql .= "        or ( thismetric like 'rollwyflow%' ) ";
   $disql .= "    ) ";
   $disql .= " group by thismetric ) as foo ";
   $controlHTML  .= showMultiList2($listobject,'thisrange', $disql, 'thisrange', 'thisrange', $thesemetrics, '', 'thisrange DESC', $debug, 5, 1);
   */
   $metricarray[0]['thismetric'] = 'flow';
   $metricarray[0]['thislabel'] = 'Flow';
   $metricarray[1]['thismetric'] = 'gw';
   $metricarray[1]['thislabel'] = 'Grounwater Observation Well';
   $metricarray[2]['thismetric'] = 'lake';
   $metricarray[2]['thislabel'] = 'Reservoir Level';
   $metricarray[3]['thismetric'] = 'precip';
   $metricarray[3]['thislabel'] = 'Precipitation';
   $thesemetrics = join(',', $thismetric[0]);
   $controlHTML .= showMultiList2($metricarray,'thismetric', $disql, 'thismetric', 'thislabel', $thesemetrics, '', 'thislabel', $debug, 5, 1);
   $controlHTML  .= showGenericButton('showindicators','Show Indicators', "xajax_showDroughtIndicatorResult(xajax.getFormValues(\"control\"))", 1);
   $controlHTML .= "</form>";
   #print("<br>");
   #$debug = 1;

   return $controlHTML;
}

function droughtIndicatorResult($formValues) {
   global $listobject, $debug, $userid;

   $innerHTML = '';

   $innerHTML .= "<font class='heading1'>Drought Report</font><br>";

   $thesemetrics = $formValues['thismetric'][0];
   if (isset($formValues['thisrange'])) {
      $theseranges = $formValues['thisrange'][0];
   } else {
      # hardwire
      if (isset($formValues['startdate']) and isset($formValues['enddate'])) {
         $theseranges = array( $formValues['startdate'] . ' to ' . $formValues['enddate'] );
      } else {
         $theseranges = array('2007-10-01 to 2008-10-21');
      }
   }
   # hardwire
   #$thesemetrics = array('flow', 'gw', 'lake', 'precip');
   $currentgroup = $formValues['currentgroup'];
   $projectid = $formValues['projectid'];
   $allgroups = $formValues['allgroups'];

   #$debug = 1;
   if ($debug) {
      $innerHTML .= print_r($thesemetrics, 1);
      $innerHTML .= print_r($formValues, 1);
   }

   if ( ($currentgroup > 0) and (!($allgroups == 1)) ) {
      $agclause = "a.gid = $currentgroup ";
      $bgclause = "b.gid = $currentgroup ";
      $cgclause = "c.gid = $currentgroup ";
   } else {
      $agclause = "( 1 = 1)";
      $bgclause = "a.gid = b.gid ";
      $cgclause = "a.gid = c.gid ";
   }
   
   /*
   # this is disabled in order to use the speed of caching with mod_php - mapsxcript is incompatible with mod_php
   # first, show current aggregate map
   $aggmap = showAggregateMap($formValues, 'current');
   $agimg = $aggmap['image_url'];

   $innerHTML .= "<img src='$agimg'><br>";
   */
   $innerHTML .= "<table class=\"lineborder\">";
   $innerHTML .= "<tr><th colspan=2><b>Indicator Key:</b></th></tr>";
   $innerHTML .= "<tr>";
   $innerHTML .= "<td width=50%>GW</td><td width=50%>Precip</td>";
   $innerHTML .= "</tr><tr>";
   $innerHTML .= "<td width=50%>Rsvr</td><td width=50%>Flow</td>";
   $innerHTML .= "</tr></table><br>";

   foreach ($thesemetrics as $thismetric) {
      foreach ($theseranges as $thisrange) {
         list($startdate, $enddate) = split(' to ', $thisrange);
         # supresses output, stores it in an object property "outstring"
         $listobject->show = 0;
         if ($thismetric == 'precip') {
            # get precip metrics
            $listobject->querystring = "  SELECT extract(month from a.startdate) as thismo, ";
            $listobject->querystring .= "    extract(year from a.startdate) as thisyr, a.thismetric, ";
            $listobject->querystring .= "    max(a.startdate) as ed ";
            $listobject->querystring .= " FROM proj_group_stat as a ";
            $listobject->querystring .= " WHERE a.projectid = $projectid ";
            $listobject->querystring .= "    AND a.thismetric = 'daily_precip_obs' ";
            $listobject->querystring .= "    AND a.startdate >= '$startdate' ";
            $listobject->querystring .= "    AND a.startdate <= '$enddate' ";
            $listobject->querystring .= " GROUP BY thismo, thisyr, a.thismetric ";
            $listobject->querystring .= " ORDER BY thisyr DESC, thismo DESC";
            #if ($debug) {
               $innerHTML .= " $listobject->querystring <br>";
            #}
            $listobject->performQuery();
            $metricrecs = $listobject->queryrecords;
            # get precip metric data
            foreach ($metricrecs as $thisrec) {
               $ametric = $thisrec['thismetric'];
               # $startdate remains static, and we build a monthly cumulative
               # by changing the enddate to the end of each month returned
               # OR
               # enddate remains static, and we build a monthly cumulative
               # by changing the startdate to the beginning of each month returned
               //$enddate = $thisrec['ed'];
               $mostart = $thisrec['thisyr'] . '-' . str_pad($thisrec['thismo'],2,'0', STR_PAD_LEFT) . '-' . '01';
               $innerHTML .= "<b>Metric:</b> $ametric <br>";

               $innerHTML .= "<table><tr><td>";

               # try out new daily slice summary (realtime, super fast)
               #select period summary for a given seggroup
               $listobject->querystring = " select a.gid, a.groupname, a.thismetric, a.startdate, a.enddate, ";
               $listobject->querystring .= "    a.thisvalue as obs, b.thisvalue as nml,  ";
               $listobject->querystring .= "    a.thisvalue / b.thisvalue as pct, a.num_obs as numrecs, b.num_nml  ";
               $listobject->querystring .= " from (  ";
               $listobject->querystring .= "    select gid, groupname, thismetric, min(startdate) as startdate, ";
               $listobject->querystring .= "       max(startdate) as enddate, sum(thisvalue) as thisvalue, ";
               $listobject->querystring .= "       count(gid) as num_obs   ";
               $listobject->querystring .= "    from proj_group_stat  ";
               $listobject->querystring .= "    where thismetric = 'daily_precip_obs' ";
               $listobject->querystring .= "    and startdate >= '$mostart' ";
               $listobject->querystring .= "    and enddate <= '$enddate'  ";
               #make sure this is a single day entry
               $listobject->querystring .= "    and startdate = enddate ";
               $listobject->querystring .= "    AND gid in  ";
               $listobject->querystring .= "    (select gid from proj_seggroups  ";
               $listobject->querystring .= "     where ownerid = $userid  ";
               $listobject->querystring .= "        and projectid = $projectid ) ";
               $listobject->querystring .= "    group by gid, groupname, thismetric  ";
               $listobject->querystring .= " ) as a, (  ";
               $listobject->querystring .= "    select gid, avg(pct_cover) as pct_cover, sum(thisvalue) as thisvalue,  ";
               $listobject->querystring .= "       count(gid) as num_nml  ";
               $listobject->querystring .= "    from proj_group_stat  ";
               $listobject->querystring .= "    where thismetric = 'daily_precip_nml' ";
               $listobject->querystring .= "    and startdate >= '$mostart' ";
               $listobject->querystring .= "    and enddate <= '$enddate'  ";
               #make sure this is a single day entry
               $listobject->querystring .= "    and startdate = enddate ";
               $listobject->querystring .= "    AND gid in  ";
               $listobject->querystring .= "    (select gid from proj_seggroups  ";
               $listobject->querystring .= "     where ownerid = $userid  ";
               $listobject->querystring .= "        and projectid = $projectid ) ";
               $listobject->querystring .= "    group by gid  ";
               $listobject->querystring .= " ) as b  ";
               $listobject->querystring .= " where b.gid = a.gid ";
               $listobject->querystring .= " order by a.groupname ";
               $listobject->performQuery();
               $listobject->show = 0;
               $listobject->tablename = 'droughtprecip';
               $listobject->showList();
               #$innerHTML .= $listobject->querystring . "<br>";
               $innerHTML .= $listobject->outstring;

                # only needed when showing old and new method of calcualtion
               $innerHTML .= "</td></tr></table>";

            }


         } else {
            # get gw, flow, and reservoir metrics
            $listobject->querystring = "  SELECT min(a.startdate) as mindate, a.thismetric ";
            $listobject->querystring .= " FROM proj_group_stat as a ";
            $listobject->querystring .= " WHERE a.projectid = $projectid ";
            $listobject->querystring .= "    AND ( ";
            $listobject->querystring .= "            (a.thismetric like 'tmp_$thismetric%') ";
            $listobject->querystring .= "            or (a.thismetric like 'roll365$thismetric%') ";
            $listobject->querystring .= "            or (a.thismetric like 'rollwy$thismetric%') ";
            $listobject->querystring .= "            or (a.thismetric like 'last7days$thismetric%') ";
            $listobject->querystring .= "    ) ";
            $listobject->querystring .= "    AND a.thismetric not like '%_value' ";
            $listobject->querystring .= "    AND $agclause ";
            $listobject->querystring .= " GROUP BY a.thismetric ";
            $listobject->querystring .= " order by mindate DESC, a.thismetric ";
            /*
            # get gw, flow, and reservoir metrics
            $listobject->querystring = "  SELECT a.startdate, a.enddate, a.thismetric ";
            $listobject->querystring .= " FROM proj_group_stat as a ";
            $listobject->querystring .= " WHERE a.projectid = $projectid ";
            $listobject->querystring .= "    AND ( ";
            $listobject->querystring .= "            (a.thismetric like 'tmp_$thismetric%') ";
            $listobject->querystring .= "            or (a.thismetric like 'roll365$thismetric%') ";
            $listobject->querystring .= "            or (a.thismetric like 'rollwy$thismetric%') ";
            $listobject->querystring .= "            or (a.thismetric like 'last7days$thismetric%') ";
            $listobject->querystring .= "    ) ";
            $listobject->querystring .= "    AND a.thismetric not like '%_value' ";
            $listobject->querystring .= "    AND $agclause ";
            $listobject->querystring .= " GROUP BY a.startdate, a.enddate, a.thismetric ";
            $listobject->querystring .= " order by a.startdate DESC, a.enddate DESC, a.thismetric ";
            */
            if ($debug) {
               $innerHTML .= " $listobject->querystring <br>";
            }
            $listobject->performQuery();
            if ($debug) {
               $listobject->showList();
               $innerHTML .= $listobject->outstring;
            }
            $metricrecs = $listobject->queryrecords;
            # get gw, flow, and reservoir metric data
            foreach ($metricrecs as $thisrec) {
               $ametric = $thisrec['thismetric'];
               $innerHTML .= "<b>Metric:</b> $ametric <br>";
               $listobject->querystring = "  SELECT b.groupname, a.startdate, a.enddate, ";
               $listobject->querystring .= "    min(c.thisvalue) as minval, max(c.thisvalue) as maxval, ";
               $listobject->querystring .= "    min(a.thisvalue) as minpct, max(a.thisvalue) as maxpct ";
               # shows individuals
               #$listobject->querystring .= "    a.thisvalue as thispct, c.thisvalue as thisval ";
               $listobject->querystring .= " FROM proj_group_stat as a, proj_seggroups as b, proj_group_stat as c ";
               $listobject->querystring .= " WHERE a.projectid = $projectid ";
               $listobject->querystring .= "    AND a.thismetric = '$ametric' ";
               $listobject->querystring .= "    AND c.thismetric = '$ametric' || '_value' ";
               $listobject->querystring .= "    AND $agclause ";
               $listobject->querystring .= "    AND $bgclause ";
               $listobject->querystring .= "    AND $cgclause ";
               $listobject->querystring .= "    AND a.startdate = c.startdate ";
               $listobject->querystring .= "    AND a.enddate = c.enddate ";
               $listobject->querystring .= "    AND c.projectid = $projectid ";
               $listobject->querystring .= "    AND b.projectid = $projectid ";
               $listobject->querystring .= "    AND b.ownerid = $userid ";
               $listobject->querystring .= " group by b.groupname, a.startdate, a.enddate ";
               $listobject->querystring .= " order by a.startdate, b.groupname ";
               if ($debug) {
                  $innerHTML .= " $listobject->querystring <br>";
               }
               $listobject->performQuery();
               $listobject->tablename = 'droughtflowgwlake';
               $listobject->showList();
               $innerHTML .= $listobject->outstring;
            }
         }
      }
   }

   return $innerHTML;
}

function showAggregateMap($formValues, $startdate, $enddate = 'today') {
   global $listobject, $aggmapfile, $basedir, $debug;

   $end_obj = new DateTime($enddate);
   $endmo = $end_obj->format('m');

   $aggmap = array();

   if ($startdate == 'current') {
      # select beginning of water year
      if ($endmo >= 10) {
         $num_mos = $endmo - 10;
         $startyear = $thisyear;
      } else {
         $num_mos = $endmo + 2;
         $startyear = $thisyear - 1;
      }
      $start_obj = new DateTime("$startyear-10-01");
   } else {
      $start_obj = new DateTime($startdate);
   }

   $mapbuffer = 0.1;
   $initmap = 1;
   #$debug = 1;
   # manually set to html mode (no need for applet)
   $gbIsHTMLMode = 1;
   #print_r($_POST);
   $invars = $formValues;
   #print_r($invars);

   $projectid = $formValues['projectid'];
   $syear = $formValues['syear'];
   $eyear = $formValues['eyear'];
   $smonth = $formValues['smonth'];
   $emonth = $formValues['emonth'];
   $sday = $formValues['sday'];
   $eday = $formValues['eday'];
   $allgroups = $formValues['allgroups'];

   if ($debug) {
      $innerHTML .= "Creating map objects.<br>";
   }

   if ($initmap) {
      if ($debug) {
         $innerHTML .= "Creating map objects.<br>";
      }
      $amap = new ActiveMap;
      $amap->gbIsHTMLMode = 0;
      $amap->debug = $debug;
      if ($debug) { $innerHTML .= "Locating map file.<br>"; }
      $amap->setMap($basedir,$aggmapfile);
      #$HTTP_SESSION_VARS["amap"] = $amap;
   } else {
      $amap->debug = $debug;
      $amap->setMap($basedir,$panimapfile);
   }

   # zoom to selected extent
   if (isset($formValues['currentgroup'])) {
      if ($allgroups) {
         $gcond = '';
      } else {
         $gcond = $formValues['currentgroup'];
      }
      $coords = getGroupExtents($listobject, 'proj_seggroups', 'the_geom', 'gid', $gcond, " projectid = $projectid ", $mapbuffer, $debug );
      list($lox,$loy,$hix,$hiy) = split(',', $coords);
      $amap->quickView($lox,$loy,$hix,$hiy);
      if ($debug) {
         $innerHTML .= "Coords: $coords<br>";
      }
      $thislayer = $amap->map->getLayerByName('proj_seggroups');
      $thislayer->setFilter("gid = " . $formValues['currentgroup'] . " ");
   }

   # set the proper layer projectid's and metric names
   $thislayer = $amap->map->getLayerByName('proj_seggroups');
   $thislayer->setFilter("projectid = $projectid and agg_group <> 1");
   $thislayer = $amap->map->getLayerByName('sym_box');
   $thislayer->setFilter("projectid = $projectid and agg_group <> 1");
   $thislayer = $amap->map->getLayerByName('sym_flow');
   $thislayer->setFilter("projectid = $projectid and thismetric = 'last7daysflow'  and agg_group <> 1");
   $thislayer = $amap->map->getLayerByName('sym_res');
   $thislayer->setFilter("projectid = $projectid and thismetric = 'tmp_lake_mtd_4mos'  and agg_group <> 1");
   $thislayer = $amap->map->getLayerByName('sym_precip');
   $thislayer->setFilter("projectid = $projectid and thismetric = 'tmp_precip_4mos_dep_pct'  and agg_group <> 1");
   $thislayer = $amap->map->getLayerByName('sym_gw');
   $thislayer->setFilter("projectid = $projectid and thismetric = 'last7daysgw'  and agg_group <> 1");


   $image = $amap->map->draw();
   $image_url = $image->saveWebImage(MS_GIF,1,1,0);
   $file_path = $amap->map->web->imagepath
                    . substr(strrchr($image_url, "/"),1);
   $aggmap['image_url'] = $image_url;

   return $aggmap;
}


function withdrawalInfoForm($formValues) {
   global $listobject, $debug;

   $controlHTML = '';


   $startyear = $formValues['startyear'];
   $endyear = $formValues['endyear'];
   $projectid = $formValues['projectid'];
   $currentgroup = $formValues['currentgroup'];
   $lreditlist = $formValues['lreditlist'];
   if (isset($formValues['sourcetypes'])) {
      $sourcetypes = $formValues['sourcetypes'][0];
   } else {
      $sourcetypes = array();
   }

   $groups = getGroupWKT($formValues);
   $mpinfo = getUserMPIDsByWKT($listobject, $wktshape, $mptypes, $mpactions, $debug);

   if ($startyear == '') { $startyear = Date('Y'); }
   if ($endyear == '') { $endyear = Date('Y'); }
   #$controlHTML  .= print_r($sourcetypes, 1);
   $controlHTML .= "<form id=control name=control>";
   $controlHTML .= showHiddenField('projectid', $projectid, 1);
   $controlHTML .= showHiddenField('currentgroup', $currentgroup, 1);
   $controlHTML .= showHiddenField('lreditlist', $lreditlist, 1);
   $controlHTML  .= "<br><b>Select Starting year to Sum Water Use:</b>";
   $controlHTML  .= showWidthTextField('startyear', $startyear, 8, '', 1);
   $controlHTML  .= "<br><b>Select Ending year to Sum Water Use:</b>";
   $controlHTML  .= showWidthTextField('endyear', $endyear, 8, '', 1);
   $controlHTML .= "<br><b> Select Withdrawal Source Type:</b><br>";
   $controlHTML .= showMultiList2($listobject,'sourcetypes', 'watersourcetype', 'wsabbrev', 'wsname', $sourcetypes, '', 'wsname', $debug, 5, 1);
   $controlHTML  .= "<br>" . showGenericButton('showmps','Retrieve Measuring Points', "xajax_showWithdrawalInfoResult(xajax.getFormValues(\"control\"))", 1);
   $controlHTML .= "</form>";
   #print("<br>");
   #$debug = 1;

   return $controlHTML;

}

function withdrawalInfoResult($formValues) {
   global $listobject, $debug;

   $controlHTML = '';
   $controlHTML .= "<div style=\"border: 1px solid rgb(0 , 0, 0); border-style: dotted; overflow: auto; width: 600px; height: 600px\">";

   $groups = getGroupWKT($formValues);
   $mptypes = $formValues['sourcetypes'][0];
   $startyear = $formValues['startyear'];
   $endyear = $formValues['endyear'];

   $wktshape = $groups[0]['wktshape'];
   $mpinfo = getUserMPIDsByWKT($listobject, $wktshape, $mptypes, '', $debug);

   #$controlHTML .= $mpinfo['query'] . "<br>";
   #$controlHTML .= print_r($mpinfo, 1) ."<br>";
   $listobject->show = 0;
   $listobject->queryrecords = $mpinfo['records'];
   #$listobject->tablename = 'vwuds_monthly';
   $listobject->showList();
   $controlHTML .= $listobject->outstring;
   foreach ($mpinfo['records'] as $thisrec) {
      $userid = $thisrec['USERID'];
      $mpid = $thisrec['mpid'];
      $src = $thisrec['SOURCE'];
      $other = $thisrec['OTHER_MP'];
      $ownname = $thisrec['ownname'];
      $facility = $thisrec['facility'];
      $system = $thisrec['system'];
      $controlHTML .= "<hr><b>$ownname - $facility - $system</b><br>";
      $controlHTML .= "<b>Annual Records for $src - $other</b><br>";
      #$listobject->querystring = "  SELECT a.\"MPID\" as mpid, a.\"USERID\" as userid, ";
      #$listobject->querystring .= "   a.\"YEAR\" as thisyear, a.\"ANNUAL\" as annual ";
      #$listobject->querystring .= "   a.\"JANUARY\" as jan, a.\"FEBRUARY\" as feb ";
      $listobject->querystring = "  SELECT a.* ";
      $listobject->querystring .= " FROM annual_data AS a ";
      $listobject->querystring .= " WHERE a.\"USERID\" = '$userid' ";
      $listobject->querystring .= " and a.\"MPID\" = '$mpid' ";
      $listobject->querystring .= " and a.\"YEAR\" >=  $startyear  ";
      $listobject->querystring .= " and a.\"YEAR\" <=  $endyear  ";
      $listobject->querystring .= " and a.\"ACTION\" = 'WL' ";
      $listobject->querystring .= " ORDER BY a.\"MPID\", a.\"YEAR\" ";
      $listobject->performQuery();
      $listobject->showList();
      #$controlHTML .= $listobject->querystring;
      $controlHTML .= $listobject->outstring;

   }
   $controlHTML .= "</div>";
   return $controlHTML;

}

function withdrawalForm($formValues) {
   global $listobject;

   $controlHTML = '';


   $startyear = $formValues['startyear'];
   $endyear = $formValues['endyear'];
   $projectid = $formValues['projectid'];
   $currentgroup = $formValues['currentgroup'];
   $lreditlist = $formValues['lreditlist'];

   if ($startyear == '') { $startyear = Date('Y'); }
   if ($endyear == '') { $endyear = Date('Y'); }
   #print_r($allsegs);
   $controlHTML .= "<form id=control name=control>";
   $controlHTML  .= showHiddenField('projectid', $projectid, 1);
   $controlHTML  .= showHiddenField('currentgroup', $currentgroup, 1);
   $controlHTML  .= showHiddenField('lreditlist', $lreditlist, 1);
   $controlHTML  .= "<br><b>Select Starting year to Sum Water Use:</b>";
   $controlHTML  .= showWidthTextField('startyear', $startyear, 8, '', 1);
   $controlHTML  .= "<br><b>Select Ending year to Sum Water Use:</b>";
   $controlHTML  .= showWidthTextField('endyear', $endyear, 8, '', 1);
   #$controlHTML  .= "<br> $currentgroup - $lreditlist";
   $controlHTML  .= showGenericButton('showwithdrawals','Retrieve Withdrawals', "xajax_showWithdrawalResult(xajax.getFormValues(\"control\"))", 1);
   $controlHTML .= "</form>";
   #print("<br>");
   #$debug = 1;

   return $controlHTML;
}


function withdrawalResult($formValues) {
   global $listobject, $debug, $outdir, $outurl, $goutdir, $gouturl;

   $innerHTML = '';
   #$debug = 1;


   $startyear = $formValues['startyear'];
   $endyear = $formValues['endyear'];
   $projectid = $formValues['projectid'];
   $currentgroup = $formValues['currentgroup'];
   $lreditlist = $formValues['lreditlist'];

   if ($startyear == '') { $startyear = Date('Y'); }
   if ($endyear == '') { $endyear = Date('Y'); }
   #print("<br>");
   #$debug = 1;
   # supresses output, stores it in an object property "outstring"
   $listobject->show = 0;

   if (isset($formValues['showwithdrawals'])) {

      $groups = getGroupWKT($formValues);

      foreach ($groups as $thisgroup) {
        $gname = $thisgroup['groupname'];
        $wktshape = $thisgroup['wktshape'];
        $innerHTML  .= "<b>Summary for:</b> $gname <br>";
        $output = getTotalSurfaceWithdrawalByWKT($listobject, $startyear, $endyear, $wktshape, $debug);
        $totalwd = $output['totalannual'];
        $totalcon = $output['totalconsumptive'];
        $message = $output['message'];
        if ($debug) {
           $innerHTML .= $message;
        }
        $innerHTML  .= "<b>Total for $startyear-$endyear =</b> $totalwd ($totalcon consumptive)<br>";
        $listobject->queryrecords = $output['records'];
        $listobject->tablename = 'vwuds_monthly';
        $listobject->showList();
        $innerHTML .= "$listobject->outstring";
        if ($startyear <> $endyear) {
           $innerHTML  .= "Annual Totals:<br>";
           #$debug = 1;
           $output = getTotalAnnualSurfaceWithdrawalByWKT($listobject, $startyear, $endyear, $wktshape, $debug);
           $annuals = $output['maxdayrecs'];
           $message = $output['message'];
           if ($debug) {
              $innerHTML .= $message;
           }
           $listobject->queryrecords = $annuals;
           $listobject->tablename = 'vwuds_monthly';
           $listobject->showList();
           $innerHTML .= "$listobject->outstring";
           #$debug = 0;
        }
        $innerHTML  .= "<br>By Category:<br>";
        $output = getTotalSurfaceCATWithdrawalByWKT($listobject, $startyear, $endyear, $wktshape, $debug);
        $mpidrecs = $output['maxdayrecs'];
        $message = $output['message'];
        if ($debug) {
           $innerHTML .= $message;
        }
        $listobject->queryrecords = $mpidrecs;
        $listobject->tablename = 'vwuds_monthly';
        $listobject->showList();
        $innerHTML .= $listobject->outstring;
        # generate a pie chart of these mean annual category withdrawals
        $prefs = array(
           'showvalues'=>1,
           'margincolor'=>'white',
           'transparent'=>'white',
           'uselegend'=>1,
           'piescale'=>150,
           'piecenter'=>0.4
        );
        $prefix = 'verbose_';
        $verbosepie = showGenericNamedPie($listobject, $goutdir, $gouturl, $mpidrecs, 'category', 'totalannual', $title, 480, 480, "wu_pie.$projectid" . ".$startyear" . ".$endyear" , $prefs, $debug);
        $innerHTML .= "<img src='$verbosepie'><br>";
        if ($startyear <> $endyear) {
           $innerHTML  .= "<br>By Category, By Year:<br>";
           $output = getTotalAnnualSurfaceCATWithdrawalByWKT($listobject, $startyear, $endyear, $wktshape, $debug);
           $totalcatrecs = $output['totalcatrecs'];
           $message = $output['message'];
           if ($debug) {
              $innerHTML .= $message;
           }
           $targetyears = 2030;
           projectWaterUse($listobject, $totalcatrecs, $targetyears, $projectid, $debug);
           $bfimg = graphBestFitUse($listobject, $outdir, $outurl, $targetyears, $debug);
           $innerHTML .= "<br><img src='$bfimg'><br>";
           $listobject->queryrecords = $totalcatrecs;
           $listobject->tablename = 'vwuds_monthly';
           $listobject->showList();
           $innerHTML .= "$listobject->outstring";
        }
      }
   }

   return $innerHTML;
}

function flowZoneForm($formValues){

   global $listobject, $projectid;

   if (isset($formValues['days'])) {
      $days = $formValues['days'];
   } else {
      $days = 7;
   }

   $innerHTML = '';
   $controlHTML = '';

   # set up input form
   $controlHTML .= "<form id=control name=control>";
   $controlHTML .= "<b>Enter the number of days to analyze:</b>";
   $controlHTML .= showWidthTextField('days', $days, 6, '', 1);
   $controlHTML .= showHiddenField('projectid', $projectid, 1);
   $controlHTML .= "<br>";
   $controlHTML .= showGenericButton('showts', 'Show Flow Series', "xajax_showFlowZoneResult(xajax.getFormValues(\"control\"))", 1);
   $controlHTML .= "</form>";

   return $controlHTML;
}

function flowZoneResult($formValues){

   global $goutdir, $gouturl, $listobject;

   $dr_zones = array(
      'Watch'=>array('01633000'=>120, '01634000'=>150, '01632000'=>100),
      'Warning'=>array('01633000'=>75, '01634000'=>90, '01632000'=>60),
      'Emergency'=>array('01633000'=>30, '01634000'=>65, '01632000'=>25)
   );
   if (isset($formValues['days'])) {
      $days = $formValues['days'];
   } else {
      $days = 7;
   }

   $innerHTML = '';

   $stations = array('01632000'=>"NF Shenandoah at Coote's Store", '01633000'=>"NF Shenandoah at Mount Jackson", '01634000'=>"NF Shenandoah at Strasburg");
   $innerHTML .= "<table><tr>";
   $numstats = 0;
   foreach (array_keys($stations) as $staid) {
      $rname = $stations[$staid];
      $fzdata = createFlowZoneGraph($goutdir, $gouturl, $dr_zones, $staid, $days, 0, $debug);
      #$innerHTML .= join(',', array_keys($fzdata)) . join(',', array_values($fzdata));
      $area = number_format($fzdata['area'],2);
      $thisimg = $fzdata['imageurl'];
      $data = $fzdata['data'];
      $innerHTML .= "<td><center>$rname ($area sq. mi.)<br>";
      $innerHTML .= "<div style=' border:1px solid black; width:320px; height:100px; overflow:auto;'>";
      $innerHTML .= "<b>$days day flow:</b><br>";
      foreach ($data as $thisdata) {
         $thisdate = $thisdata['thisdate'];
         $Qinches = number_format($thisdata['Qinches'],4);
         $Qcfs = number_format($thisdata['Qout'],2);
         $innerHTML .= "$thisdate - $Qcfs cfs ($Qinches inch/day) <br>";
      }
      $innerHTML .= "</div>";
      $innerHTML .= "<img src='$thisimg'></center></td>";
      $numstats += 1;
   }
   $innerHTML .= "</tr></table>";

   return $innerHTML;
}

function showHSI($formValues){

   $objResponse = new xajaxResponse();
   $innerHTML = '';
   $controlHTML = '';

   # set up input form
   $controlHTML .= "<form id=control name=control>";
   $controlHTML .= "<b>Enter the number of days to analyze:</b>";

   $controlHTML .= "<br><b>Enter Start Date (YYYY MM DD):</b> ";
   $controlHTML .= showWidthTextField('syear', $syear, 8, '', 1);
   $controlHTML .= showWidthTextField('smonth', $smonth, 3, '', 1);
   $controlHTML .= showWidthTextField('sday', $sday, 3, '', 1);
   $controlHTML .= "<br> ";
   $controlHTML .= "<b>Enter End Date (YYYY MM DD):</b> ";
   $controlHTML .= showWidthTextField('eyear', $eyear, 8, '', 1);
   $controlHTML .= showWidthTextField('emonth', $emonth, 3, '', 1);
   $controlHTML .= showWidthTextField('eday', $eday, 3, '', 1);
   $controlHTML .= showHiddenField('actiontype', $actiontype, 1);
   $controlHTML .= "<br>";
   $controlHTML .= showGenericButton('showts', 'Show Flow Series', "xajax_showFlowZone(xajax.getFormValues(\"control\"))", 1);
   $controlHTML .= "</form>";

   $innerHTML = "<table><tr>";
   $innerHTML .= "<td align=center>";
   $innerHTML .= "<b>Habitat Suitability Model Estimates at Mount Jackson for Summer 2007 (Laurel Hill Site) </b><br>";
   $innerHTML .= "<img src='/html/hydro_object/out/am.658545adc485711c0c95bf7605561205.png'>";
   $innerHTML .= "</td></tr></table>";

   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   $objResponse->assign("workspace","innerHTML",$innerHTML);

   return $objResponse;
}

function showPrecipTrends($formValues){

   $objResponse = new xajaxResponse();

   $innerHTML = showPrecipTrendsForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$innerHTML);

   return $objResponse;
}

function showPrecipTrendsResult($formValues){

   $objResponse = new xajaxResponse();

   $innerHTML .= precipCumulativeMap($formValues);
   $innerHTML .= '<br>' . precipTrendsResult($formValues);
   $controlHTML = showPrecipTrendsForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   $objResponse->assign("workspace","innerHTML",$innerHTML);

   return $objResponse;
}

function showPrecipTrendsForm($formValues) {

   $controlHTML = '';

   $controlHTML .= "<form id=control name=control>";
   $controlHTML .= "<b>Enter Start Date (YYYY MM DD):</b> ";
   $today = new DateTime();
   $tm = $today->format('m');
   $td = $today->format('d');
   $ty = $today->format('Y');

   if (!(isset($formValues['syear']))) {
      $syear = $ty;
      $eyear = $ty;
      $smonth = $tm;
      $emonth = $tm;
      $sday = 1;
      $eday = $td;
   } else {
      $syear = $formValues['syear'];
      $eyear = $formValues['eyear'];
      $smonth = $formValues['smonth'];
      $emonth = $formValues['emonth'];
      $sday = $formValues['sday'];
      $eday = $formValues['eday'];
   }
   $startdate = $syear . str_pad($smonth, 0, 2, STR_PAD_LEFT) . str_pad($sday, 0, 2, STR_PAD_LEFT);
   $enddate = $syear . str_pad($emonth, 0, 2, STR_PAD_LEFT) . str_pad($eday, 0, 2, STR_PAD_LEFT);
   $currentgroup = $formValues['currentgroup'];
   $projectid = $formValues['projectid'];


   $controlHTML .= showWidthTextField('syear', $syear, 8, '', 1);
   $controlHTML .= showWidthTextField('smonth', $smonth, 3, '', 1);
   $controlHTML .= showWidthTextField('sday', $sday, 3, '', 1);
   $controlHTML .= "<br> ";
   $controlHTML .= "<b>Enter End Date (YYYY MM DD):</b> ";
   $controlHTML .= showWidthTextField('eyear', $eyear, 8, '', 1);
   $controlHTML .= showWidthTextField('emonth', $emonth, 3, '', 1);
   $controlHTML .= showWidthTextField('eday', $eday, 3, '', 1);
   $controlHTML .= showHiddenField('currentgroup', $currentgroup, 1);
   $controlHTML .= showHiddenField('projectid', $projectid, 1);
   $controlHTML .= "<br> ";
   $controlHTML .= showGenericButton('showts', 'Show Time Series', "xajax_showPrecipTrendsResult(xajax.getFormValues(\"control\"))", 1);

   return $controlHTML;
}

function precipCumulativeMap($formValues) {
   global $listobject, $panimapfile, $debug, $basedir, $libpath;
   #include("$libpath/module_activemap.php");
   $innerHTML = '';

   $today = new DateTime();
   $tm = $today->format('m');
   $td = $today->format('d');
   $ty = $today->format('Y');
   if (!(isset($formValues['syear']))) {
      $syear = $ty;
      $eyear = $ty;
      $smonth = $tm;
      $emonth = $tm;
      $sday = 1;
      $eday = $td;
   } else {
      $syear = $formValues['syear'];
      $eyear = $formValues['eyear'];
      $smonth = $formValues['smonth'];
      $emonth = $formValues['emonth'];
      $sday = $formValues['sday'];
      $eday = $formValues['eday'];
   }
   $startdate = $syear . '-' . str_pad($smonth, 0, 2, STR_PAD_LEFT) . '-' . str_pad($sday, 0, 2, STR_PAD_LEFT);
   $enddate = $syear . '-' . str_pad($emonth, 0, 2, STR_PAD_LEFT) . '-' . str_pad($eday, 0, 2, STR_PAD_LEFT);


   # shows precip cumulative map, if there is a formValue for "dataname" we use that, otherwise, we default to 'wy2date'
   # which stands for "water year to date"

   $initmap = 1;
   $mapbuffer = 0.1;
   #$debug = 1;
   # manually set to html mode (no need for applet)
   $gbIsHTMLMode = 1;
   #print_r($_POST);
   $invars = $formValues;
   #print_r($invars);

   if (isset($formValues['dataname'])) {
      $dataname = $formValues['dataname'];
   } else {
      $dataname = 'wy2date';
   }

   if ($startdate == $enddate) {
      # if these are set, then we have a non-singular dataname, otherwise, we assume dataname is singular
      $singular = 0;
   } else {
      $singular = 1;
   }

   # for now, we force a singular wy2date map
   $dataname = 'wy2date';
   $singular = 1;

   $innerHTML .= "Precip Map for $dataname ";

   $projectid = $formValues['projectid'];

   $amap = new ActiveMap;
   $amap->gbIsHTMLMode = 0;
   $amap->debug = $debug;
   if ($debug) { $innerHTML .= "Locating map file. $basedir,$panimapfile <br>"; }
   $amap->setMap($basedir,$panimapfile);
   #$debug = 1;
   /*
   if (!checkDate($smonth, $sday, $syear)) {
      $innerHTML .= "<b>Error:</b> Start Date, $syear-$smonth-$sday is not valid.";
      return $innerHTML;
   }

   # should break up the date and validate it here, or allow a date to
   $sdate = new DateTime("$syear-$smonth-$sday");

   if (!checkDate($emonth, $eday, $eyear)) {
      $innerHTML .= "<b>Error:</b> End Date, $eyear-$emonth-$eday is not valid.";
      return $innerHTML;
   }
   $edate = new DateTime("$eyear-$emonth-$eday");

   $su = $sdate->format('U');
   $eu = $edate->format('U');

   if (!( $eu >= $su)) {
      $innerHTML .= "<b>Error:</b>End Date must be >= start date.";
      return $innerHTML;
   }

   */


   # zoom to selected extent
   if (isset($formValues['currentgroup'])) {
      $coords = getGroupExtents($listobject, 'proj_seggroups', 'the_geom', 'gid', $formValues['currentgroup'], " projectid = $projectid ", $mapbuffer, 0 );
      list($lox,$loy,$hix,$hiy) = split(',', $coords);
      $amap->quickView($lox,$loy,$hix,$hiy);
      if ($debug) {
         $innerHTML .= "Coords: $coords<br>";
      }
      $gfilta = " projectid = $projectid ";
      $thislayer = $amap->map->getLayerByName('proj_seggroups');
      if ($formValues['currentgroup'] > 0) {
         $gfilta .= "AND gid = " . $formValues['currentgroup'] . " ";
      }
      $thislayer->setFilter($gfilta);
      $innerHTML .= "$filter <br>";
   }

   $preciplayer = $amap->map->getLayerByName('precip_period');
   $filter = " dataname = '$dataname' ";
   if (!$singular) {
      $filter .= " AND startdate = '$startdate' AND enddate = '$enddate' ";
   }
   $innerHTML .= "$filter <br>";
   $preciplayer->setFilter($filter);
   $preciplayer->set("status",MS_ON);

   /*
   # get quantiles for the data set
   $listobject->querystring = "  select r_quantile(array_accum(globvalue), 0.25) as q25, ";
   $listobject->querystring .= " r_quantile(array_accum(globvalue), 0.5) as q50,";
   $listobject->querystring .= " r_quantile(array_accum(globvalue), 0.75) as q75,";
   $listobject->querystring .= " from precip_gridded_period ";
   $listobject->querystring .= " where dataname = 'wy2date' ";
   $listobject->querystring .= "    and the_geom && (select setsrid(extent(the_geom),4326) ";
   $listobject->querystring .= "                     from proj_seggroups ";
   $listobject->querystring .= "                     where projectid = $projectid ";
   $listobject->querystring .= "    ) ";
   if (!$singular) {
      $listobject->querystring .= " AND startdate = \"$startdate\" AND enddate = \"$enddate\" ";
   }
   if (strlen(ltrim(rtrim($formValues['currentgroup']))) > 0) {
      $listobject->querystring .= " AND gid = " . $formValues['currentgroup'];
   }
   if ($debug) {
      $innerHTML .= $listobject->querystring;
   }
   $listobject->performQuery();
   $q25 = $listobject->getRecordValue(1,'q25');
   $q50 = $listobject->getRecordValue(1,'q50');
   $q75 = $listobject->getRecordValue(1,'q75');
   */
   $annotext = "Cumulative precip from $startdate to $enddate  ";
   $annotlayer = $amap->map->getLayerByName('copyright');
   $annotation = $annotlayer->getClass(0);
   $annotation->setText($annotext);
   $annotlayer->set("status",MS_ON);
   $image = $amap->map->draw();
   $image_url = $image->saveWebImage(MS_GIF,1,1,0);
   $innerHTML .= "<img src='$image_url'>";

   return $innerHTML;

}


function precipTrendsResult($formValues) {
   global $listobject, $panimapfile, $debug, $basedir, $libpath;
   #include("$libpath/module_activemap.php");
   $innerHTML = '';

   $today = new DateTime();
   $tm = $today->format('m');
   $td = $today->format('d');
   $ty = $today->format('Y');
   if (!(isset($formValues['syear']))) {
      $syear = $ty;
      $eyear = $ty;
      $smonth = $tm;
      $emonth = $tm;
      $sday = 1;
      $eday = $td;
   } else {
      $syear = $formValues['syear'];
      $eyear = $formValues['eyear'];
      $smonth = $formValues['smonth'];
      $emonth = $formValues['emonth'];
      $sday = $formValues['sday'];
      $eday = $formValues['eday'];
   }
   $startdate = $syear . '-' . str_pad($smonth, 0, 2, STR_PAD_LEFT) . '-' . str_pad($sday, 0, 2, STR_PAD_LEFT);
   $enddate = $syear . '-' . str_pad($emonth, 0, 2, STR_PAD_LEFT) . '-' . str_pad($eday, 0, 2, STR_PAD_LEFT);

   $timedelay = 60;
   #$anim_gif_cmd = '"C:\Program Files\Apache Group\Apache2\cgi-bin\gifsicle" -l -d 100 -O2 ';
   $anim_gif_cmd = "gifsicle -l -d $timedelay -O2 ";
   $initmap = 1;
   $mapbuffer = 0.1;
   #$debug = 1;
   # manually set to html mode (no need for applet)
   $gbIsHTMLMode = 1;
   #print_r($_POST);
   $invars = $formValues;
   #print_r($invars);

   $projectid = $formValues['projectid'];
   $syear = $formValues['syear'];
   $eyear = $formValues['eyear'];
   $smonth = $formValues['smonth'];
   $emonth = $formValues['emonth'];
   $sday = $formValues['sday'];
   $eday = $formValues['eday'];

   if ($initmap) {
      if ($debug) {
         $innerHTML .= "Creating map objects.<br>";
      }
      $amap = new ActiveMap;
      $amap->gbIsHTMLMode = 0;
      $amap->debug = 0;
      if ($debug) { $innerHTML .= "Locating map file.<br>"; }
      $amap->setMap($basedir,$panimapfile);
      #$HTTP_SESSION_VARS["amap"] = $amap;
   } else {
      $amap->debug = $debug;
      $amap->setMap($basedir,$panimapfile);
   }

   $amap->img_type = MS_GIF;

   if (!checkDate($smonth, $sday, $syear)) {
      $innerHTML .= "<b>Error:</b> Start Date, $syear-$smonth-$sday is not valid.";
      return $innerHTML;
   }
   $sdate = new DateTime("$syear-$smonth-$sday");

   if (!checkDate($emonth, $eday, $eyear)) {
      $innerHTML .= "<b>Error:</b> End Date, $eyear-$emonth-$eday is not valid.";
      return $innerHTML;
   }
   $edate = new DateTime("$eyear-$emonth-$eday");

   $su = $sdate->format('U');
   $eu = $edate->format('U');

   if (!( $eu >= $su)) {
      $innerHTML .= "<b>Error:</b>End Date must be >= start date.";
      return $innerHTML;
   }


   # zoom to selected extent
   if (isset($formValues['currentgroup'])) {
      $coords = getGroupExtents($listobject, 'proj_seggroups', 'the_geom', 'gid', $formValues['currentgroup'], " projectid = $projectid ", $mapbuffer, 0 );
      list($lox,$loy,$hix,$hiy) = split(',', $coords);
      $amap->quickView($lox,$loy,$hix,$hiy);
      if ($debug) {
         $innerHTML .= "Coords: $coords<br>";
      }
      $gfilta = " projectid = $projectid ";
      $thislayer = $amap->map->getLayerByName('proj_seggroups');
      if ($formValues['currentgroup'] > 0) {
         $gfilta .= "AND gid = " . $formValues['currentgroup'] . " ";
      }
      $thislayer->setFilter($gfilta);
      $innerHTML .= "$gfilta <br>";
   }

   #print("$su - $eu <br>");
   $gifmaps = array();
   while ($su <= $eu) {
      array_push($gifmaps, $sdate->format('Y-m-d'));
      $sdate->modify("+1 day");
      $su = $sdate->format('U');
      #print("$su / $eu ");
   }

   $i = 1;

   if ($debug) {
      $innerHTML .= "Adding";
   }
   $thislayer = $amap->map->getLayerByName('poli_bounds');
   $thislayer->setFilter(" projectid = $projectid ");
   foreach ($gifmaps as $thisdate) {
      if ($debug) {
         $innerHTML .= " ... $thisdate ";
      }
      $thislayer = $amap->map->getLayerByName('precip_obs');
      $filter = " thisdate = '$thisdate' ";
      $listobject->querystring = "  select thisdate, count(*) as numrecs ";
      $listobject->querystring .= " from precip_gridded ";
      $listobject->querystring .= " where $filter ";
      $listobject->querystring .= " group by thisdate ";
      $listobject->performQuery();
      $numrecs = $listobject->getRecordValue(1,'numrecs');
      $annotlayer = $amap->map->getLayerByName('copyright');
      $oPoint = ms_newPointObj();
      $oPoint->setXY(150,180);
      $x = $oPoint->x;
      $y = $oPoint->y;
      if ($debug) {
         $innerHTML .= "Point coords are: $x, $y <br>";
      }
      $annotation = $annotlayer->getClass(0);
      if ($numrecs > 0) {
         $thislayer->setFilter($filter);
         $thislayer->set("status",MS_ON);
         $annotlayer->set("status",MS_ON);
         #$annotation->setText("Precipitation for $thisdate");
         $image[$i] = $amap->map->draw();
         #$annotlayer->set("status",MS_ON);
         #$pres = $oPoint->draw($amap->map, $annotlayer, $image[$i], 0, "Test text");
         #if( $pres == MS_SUCCESS ) {
         #   if ($debug) {
         #      $innerHTML .= "Point Drawn: $pres.<br>";
         #   }
         #}
         #$image[$i] = $amap->map->draw();
         #$image_url[$i] = $image[$i]->saveWebImage(MS_GIF,1,1,0);
         $image_url[$i] = $image[$i]->saveWebImage();
         $file_path[$i] = $amap->map->web->imagepath
                          . substr(strrchr($image_url[$i], "/"),1);
         $anim_files   = $anim_files . '"' . $file_path[$i] . '"' . " ";
         $i++;
         if ($debug) {
            $innerHTML .= " Adding <i>$filter</i>, $image_url[$i] <br>";
         }
      }
   }

   // Create a unique filename and URL for the animated GIF output
   $anim_name = "anim".substr(strrchr($image_url[1], '/'),1);
   $anim_path = '"' . $amap->map->web->imagepath . $anim_name .'"';
   $anim_url = $amap->map->web->imageurl . $anim_name;

   if ($debug) {
      $innerHTML .= "Creating $anim_path <br>";
   }
   $cmd = $anim_gif_cmd . " -o " . $anim_path . " " .  $anim_files;
   #if ($debug) {
      $innerHTML .= "Using: $cmd <br>";
   #}

   system($cmd);

   $innerHTML .= "<img src='$anim_url'>";
   #$amap->map->legend->set('width', 128);
   $amap->drawLegend();
   $legendurl = $amap->legend_url;
   $innerHTML .= "<img src='$legendurl'>";

   #$innerHTML .= showPrecipStat($formValues, $amap);
   #$innerHTML .= showFlowStat($formValues, $amap);
   #$innerHTML .= showGWStat($formValues, $amap);

   return $innerHTML;

}

function showPrecipStat($formValues, $amap) {
   global $listobject, $projectid, $panimapfile, $debug, $basedir, $libpath;

   $currentgroup = $formValues['currentgroup'];
   if ( ($currentgroup > 0) ) {
      $gids = $currentgroup;
   } else {
      $gids = " select gid from proj_seggroups where projectid = $projectid ";
   }
   # should set this in an object some where
   if ($projectid == 2) {
      $gids = '19,20,21';
   }
   $timeperiods = array(0,1,2,'rollwyprecip');

   $thislayer = $amap->map->getLayerByName('poli_bounds');
   $thislayer->set("status",MS_ON);
   $thislayer->setFilter("projectid = $projectid");

   $thislayer = $amap->map->getLayerByName('precip_obs');
   $thislayer->set("status",MS_OFF);
   $thislayer = $amap->map->getLayerByName('gw_stat');
   $thislayer->set("status",MS_OFF);
   $thislayer = $amap->map->getLayerByName('flow_stat');
   $thislayer->set("status",MS_OFF);
   $thislayer = $amap->map->getLayerByName('proj_seggroups');
   $filter = " gid in ($gids) ";
   $thislayer->setFilter($filter);
   $thislayer->set("status",MS_OFF);
   foreach ($timeperiods as $thisperiod) {
      if ($thisperiod == 0) {
         $annotext = "Month to date precipitation";
      } else {
         $annotext = "Month to date +$thisperiod months precipitation";
      }
      if (is_string($thisperiod)) {
         $obsmetric = "'$thisperiod" . "_obs'";
         $pctmetric = "'$thisperiod" . "_dep_pct'";
         $annotext = "Previous Water Year to date precipitation";
      } else {
         $obsmetric = "'tmp_precip_$thisperiod" . "mos_obs'";
         $pctmetric = "'tmp_precip_$thisperiod" . "mos_dep_pct'";
      }
      $listobject->querystring = "  select initcap(a.groupname) as groupname, a.startdate, a.enddate, ";
      $listobject->querystring .= "    a.thisvalue as pct, b.thisvalue as observed ";
      $listobject->querystring .= " from proj_group_stat as a left outer join proj_group_stat as b ";
      $listobject->querystring .= "    on ( b.projectid = a.projectid ";
      $listobject->querystring .= "    and a.gid = b.gid ";
      $listobject->querystring .= "    and b.thismetric = $obsmetric ";
      $listobject->querystring .= "    ) ";
      $listobject->querystring .= " where a.projectid = $projectid ";
      $listobject->querystring .= "    and a.gid in ($gids) ";
      $listobject->querystring .= "    and a.thismetric = $pctmetric ";
      $listobject->querystring .= " order by a.groupname ";
      $listobject->performQuery();

      $innerHTML .= "<table>";
      $innerHTML .= "$listobject->querystring ; <br>";
      $innerHTML .= "<tr><td><center><b>$annotext</b><br>";
      $innerHTML .= "<div style=' border:1px solid black; width:480px; height:100px; overflow:auto;'>";
      $innerHTML .= "<table><tr><td>";
      foreach ($listobject->queryrecords as $thisdata) {
         $startdate = date('m-d-Y',strtotime($thisdata['startdate']));
         $enddate = date('m-d-Y',strtotime($thisdata['enddate']));
         $groupname = $thisdata['groupname'];
         $pct = number_format(100.0 * $thisdata['pct'],2);
         $observed = number_format($thisdata['observed'],2);
         $innerHTML .= "$groupname, $startdate to $enddate: $observed in ($pct %) <br>";
      }
      $innerHTML .= "</td><td>";

      # try out new daily slice summary (realtime, super fast)
      #select period summary for a given seggroup
      $listobject->querystring .= " select a.gid, a.groupname, a.thismetric, a.thisvalue as obs, b.thisvalue as norm,  ";
      $listobject->querystring .= "    a.thisvalue / b.thisvalue as pct, a.num_obs, b.num_nml  ";
      $listobject->querystring .= " from (  ";
      $listobject->querystring .= "    select gid, groupname, thismetric, sum(thisvalue) as thisvalue, count(gid) as num_obs   ";
      $listobject->querystring .= "    from proj_group_stat  ";
      $listobject->querystring .= "    where thismetric = 'daily_precip_obs' ";
      $listobject->querystring .= "    and startdate >= '$startdate' ";
      $listobject->querystring .= "    and enddate <= '$enddate'  ";
      #make sure this is a single day entry
      $listobject->querystring .= "    and startdate = enddate ";
      $listobject->querystring .= "    and gid in ($gids) ";
      $listobject->querystring .= "    group by gid, groupname, thismetric  ";
      $listobject->querystring .= " ) as a, (  ";
      $listobject->querystring .= "    select gid, sum(thisvalue) as thisvalue, count(gid) as num_nml  ";
      $listobject->querystring .= "    from proj_group_stat  ";
      $listobject->querystring .= "    where thismetric = 'daily_precip_nml' ";
      $listobject->querystring .= "    and startdate >= '$startdate' ";
      $listobject->querystring .= "    and enddate <= '$enddate'  ";
      #make sure this is a single day entry
      $listobject->querystring .= "    and startdate = enddate ";
      $listobject->querystring .= "    and gid in ($gids) ";
      $listobject->querystring .= "    group by gid  ";
      $listobject->querystring .= " ) as b  ";
      $listobject->querystring .= " where b.gid = a.gid; ";
      $listobject->performQuery();
      $listobject->show = 0;
      $listobject->showList();
      $innerHTML .= $listobject->querystring;
      $innerHTML .= $listobject->outstring;

      $innerHTML .= "</td></tr></table>";
      $innerHTML .= "</div></center></td></tr>";
      $thislayer = $amap->map->getLayerByName('precip_stat');
      $filter = " thismetric = $pctmetric and gid in ($gids) ";
      $thislayer->setFilter($filter);
      $thislayer->set("status",MS_ON);
      $annotlayer = $amap->map->getLayerByName('copyright');
      $annotation = $annotlayer->getClass(0);
      $annotlayer->set("status",MS_ON);
      $annotation->setText($annotext);
      $image = $amap->map->draw();
      $image_url = $image->saveWebImage(MS_GIF,1,1,0);
      $innerHTML .= "<img src='$image_url'>";
   }
   $amap->drawLegend();
   $legendurl = $amap->legend_url;
   $innerHTML .= "<img src='$legendurl'>";

   return $innerHTML;


}

function showFlowStat($formValues, $amap) {
   global $listobject, $projectid, $panimapfile, $debug, $basedir, $libpath;

   # should set this in an object some where
   $currentgroup = $formValues['currentgroup'];
   if ( ($currentgroup > 0) ) {
      $gids = $currentgroup;
   } else {
      $gids = " select gid from proj_seggroups where projectid = $projectid ";
   }
   # should set this in an object some where
   if ($projectid == 2) {
      $gids = '19,20,21';
   }

   $timeperiods = array(0,1,2,'rollwyflow');

   $thislayer = $amap->map->getLayerByName('poli_bounds');
   $thislayer->set("status",MS_ON);
   $thislayer->setFilter("projectid = $projectid");

   # layers to turn off
   $thislayer = $amap->map->getLayerByName('precip_stat');
   $thislayer->set("status",MS_OFF);
   $thislayer = $amap->map->getLayerByName('precip_obs');
   $thislayer->set("status",MS_OFF);
   $thislayer = $amap->map->getLayerByName('gw_stat');
   $thislayer->set("status",MS_OFF);

   # label layer
   $thislayer = $amap->map->getLayerByName('proj_seggroups');
   $filter = " gid in ($gids) ";
   $thislayer->setFilter($filter);
   $thislayer->set("status",MS_OFF);
   foreach ($timeperiods as $thisperiod) {
      if ($thisperiod == 0) {
         $annotext = "Month to date flow";
      } else {
         $annotext = "Month to date +$thisperiod months flow";
      }

      if (is_string($thisperiod)) {
         $obsmetric = "'$thisperiod" . "_value'";
         $pctmetric = "'$thisperiod'";
         $annotext = "Previous Water Year to date flow";
      } else {
         $obsmetric = "'tmp_flow_mtd_$thisperiod" . "mos_value'";
         $pctmetric = "'tmp_flow_mtd_$thisperiod" . "mos'";
      }
      $listobject->querystring = "  select initcap(a.groupname) as groupname, a.startdate, a.enddate, ";
      $listobject->querystring .= "    a.thisvalue as pct, b.thisvalue as observed ";
      $listobject->querystring .= " from proj_group_stat as a left outer join proj_group_stat as b ";
      $listobject->querystring .= "    on ( b.projectid = a.projectid ";
      $listobject->querystring .= "    and a.gid = b.gid ";
      $listobject->querystring .= "    and b.thismetric = $obsmetric ";
      $listobject->querystring .= "    ) ";
      $listobject->querystring .= " where a.projectid = $projectid ";
      $listobject->querystring .= "    and a.gid in ($gids) ";
      $listobject->querystring .= "    and a.thismetric = $pctmetric ";
      $listobject->querystring .= " order by a.groupname ";
      $listobject->performQuery();

      $innerHTML .= "<table>";
      #$innerHTML .= "$listobject->querystring ; <br>";
      $innerHTML .= "<tr><td><center>$annotext<br>";
      $innerHTML .= "<div style=' border:1px solid black; width:480px; height:100px; overflow:auto;'>";
      foreach ($listobject->queryrecords as $thisdata) {
         $startdate = $thisdata['startdate'];
         $enddate = $thisdata['enddate'];
         $groupname = $thisdata['groupname'];
         $pct = number_format(100.0 * $thisdata['pct'],2);
         $observed = number_format($thisdata['observed'],2);
         $innerHTML .= "$groupname, $startdate - $enddate: $observed cfs ($pct %) <br>";
      }
      $innerHTML .= "</div></td></tr>";
      $thislayer = $amap->map->getLayerByName('flow_stat');
      $filter = " thismetric = $pctmetric and gid in ($gids) ";
      $thislayer->setFilter($filter);
      $thislayer->set("status",MS_ON);
      $annotlayer = $amap->map->getLayerByName('copyright');
      $annotation = $annotlayer->getClass(0);
      $annotlayer->set("status",MS_ON);
      $annotation->setText($annotext);
      $image = $amap->map->draw();
      $image_url = $image->saveWebImage(MS_GIF,1,1,0);
      $innerHTML .= "<tr><td><img src='$image_url'></td></tr>";
      $innerHTML .= "</table>";
   }
   $amap->drawLegend();
   $legendurl = $amap->legend_url;
   $innerHTML .= "<img src='$legendurl'>";

   return $innerHTML;


}

function showGWStat($formValues, $amap) {
   global $listobject, $projectid, $panimapfile, $debug, $basedir, $libpath;

   # should set this in an object some where

   $currentgroup = $formValues['currentgroup'];
   if ( ($currentgroup > 0) ) {
      $gids = $currentgroup;
   } else {
      $gids = " select gid from proj_seggroups where projectid = $projectid ";
   }
   # should set this in an object some where
   if ($projectid == 2) {
      $gids = '19,20,21';
   }
   $timeperiods = array(0,1,2,'rollwygw');

   $thislayer = $amap->map->getLayerByName('poli_bounds');
   $thislayer->set("status",MS_ON);
   $thislayer->setFilter("projectid = $projectid");

   # layers to turn off
   $thislayer = $amap->map->getLayerByName('precip_stat');
   $thislayer->set("status",MS_OFF);
   $thislayer = $amap->map->getLayerByName('precip_obs');
   $thislayer->set("status",MS_OFF);

   # label layer
   $thislayer = $amap->map->getLayerByName('proj_seggroups');
   $filter = " gid in ($gids) ";
   $thislayer->setFilter($filter);
   $thislayer->set("status",MS_OFF);
   foreach ($timeperiods as $thisperiod) {
      if ($thisperiod == 0) {
         $annotext = "Month to date groundwater";
      } else {
         $annotext = "Month to date +$thisperiod months groundwater";
      }
      if (is_string($thisperiod)) {
         $obsmetric = "'$thisperiod" . "_value'";
         $pctmetric = "'$thisperiod'";
         $annotext = "Previous Water Year to date groundwater";
      } else {
         $obsmetric = "'tmp_gw_mtd_$thisperiod" . "mos_value'";
         $pctmetric = "'tmp_gw_mtd_$thisperiod" . "mos'";
      }
      $listobject->querystring = "  select initcap(a.groupname) as groupname, a.startdate, a.enddate, ";
      $listobject->querystring .= "    a.thisvalue as pct, b.thisvalue as observed ";
      $listobject->querystring .= " from proj_group_stat as a left outer join proj_group_stat as b ";
      $listobject->querystring .= "    on ( b.projectid = a.projectid ";
      $listobject->querystring .= "    and a.gid = b.gid ";
      $listobject->querystring .= "    and b.thismetric = $obsmetric ";
      $listobject->querystring .= "    ) ";
      $listobject->querystring .= " where a.projectid = $projectid ";
      $listobject->querystring .= "    and a.gid in ($gids) ";
      $listobject->querystring .= "    and a.thismetric = $pctmetric ";
      $listobject->querystring .= " order by a.groupname ";
      $listobject->performQuery();

      $innerHTML .= "<table>";
      #$innerHTML .= "$listobject->querystring ; <br>";
      $innerHTML .= "<tr><td><center>$annotext<br>";
      $innerHTML .= "<div style=' border:1px solid black; width:480px; height:100px; overflow:auto;'>";
      foreach ($listobject->queryrecords as $thisdata) {
         $startdate = $thisdata['startdate'];
         $enddate = $thisdata['enddate'];
         $groupname = $thisdata['groupname'];
         $pct = number_format(100.0 * $thisdata['pct'],2);
         $observed = number_format($thisdata['observed'],2);
         $innerHTML .= "$groupname, $startdate - $enddate: $observed ft ($pct %) <br>";
      }
      $innerHTML .= "</div></td></tr>";
      $thislayer = $amap->map->getLayerByName('flow_stat');
      $filter = " thismetric = $pctmetric and gid in ($gids) ";
      $thislayer->setFilter($filter);
      $thislayer->set("status",MS_ON);
      $annotlayer = $amap->map->getLayerByName('copyright');
      $annotation = $annotlayer->getClass(0);
      $annotlayer->set("status",MS_ON);
      $annotation->setText($annotext);
      $image = $amap->map->draw();
      $image_url = $image->saveWebImage(MS_GIF,1,1,0);
      $innerHTML .= "<img src='$image_url'>";
   }
   $amap->drawLegend();
   $legendurl = $amap->legend_url;
   $innerHTML .= "<img src='$legendurl'>";

   return $innerHTML;


}

function showWaterPlanList($listobject, $projectid, $userid, $elementid) {
   global $debug;
   $foosql = "  ( select elementid, elemname ";
   $foosql .= "   from proj_element ";
   $foosql .= "   where elemtype = 'wsplan' ";
   $foosql .= "      and projectid = $projectid and ( (ownerid = $userid  and operms >= 4) ";
   $foosql .= "      or ( groupid in (select groupid from mapusergroups where userid = $userid) and gperms >= 4 ) ";
   $foosql .= "      or (pperms >= 4) ) ";
   $foosql .= "  ) as foo ";
   if ($debug) {
      $controlHTML .= "$foosql ; <br>";
   }
   $controlHTML .= showActiveList($listobject, 'loadelement', $foosql, 'elemname', 'elementid', '', $elementid, 'document.forms["planningform"].elements.actiontype.value="loadelement"; xajax_showPlanningForm(xajax.getFormValues("planningform"))', 'elemname', $debug, 1);

   return $controlHTML;
}

function showInfoWindow() {
   $controlHTML = "<div id=\"window1\">";
   $controlHTML .= "   <div class=\"floatingWindowContent\">";
   $controlHTML .= "   This is a window where I have disabled the scrollbar at the right. If you try to resize it, you will see that some of this text will be hidden";
   $controlHTML .= "   below the bottom edge of the window.<br><br>";
   $controlHTML .= "   I have sent removed the close button from the window.";
   $controlHTML .= "   </div>";
   $controlHTML .= "   <div class=\"floatingWindowContent\">";
   $controlHTML .= "   This script is based on simple ordinary div tags. This makes it very easy to set up. Put in your HTML content and call a javascript function to initialize the window.  ";
   $controlHTML .= "   </div>";
   $controlHTML .= "   <div class=\"floatingWindowContent\">";
   $controlHTML .= "   Content 3   ";
   $controlHTML .= "   </div>";
   $controlHTML .= "</div> ";

   return $controlHTML;
}

function planningForm($formValues) {
   global $listobject, $projectid, $scenarioid, $adminsetuparray, $planpages, $userid, $usergroupids, $defaultgroupid;

   $controlHTML = '';

   $controlHTML .= "<form id='planningform' name='planningform'>";


   $elementid = '';

   if (isset($formValues['elementid'])) {
      $elementid = $formValues['elementid'];
   }

   #$controlHTML .= '<br>Form Values: <br>' . print_r($formValues, 1);
   if (isset($formValues['loadelement'])) {
      $elementid = $formValues['loadelement'];
      $seglist = $formValues['seglist'];
   }
   if ($elementid > 0) {
      $elemperms = getProjElementPerms($listobject, $projectid, $elementid, $userid, $usergroupids, $debug);
      if ( !($elemperms & 2) ) {
         $disabled = 1;
      } else {
         $disabled = 0;
      }
   }
   if (isset($formValues['seglist'])) {
      $seglist = $formValues['seglist'];
   }
   # set up blank plan information
   $template = $adminsetuparray['watersupply_plan']['column info'];
   $plandata = array();
   foreach (array_keys($template) as $plancol) {
      $plandata[$plancol] = '';
      if ($debug) {
         $controlHTML .= " $plancol set to '' ";
      }
   }
   $options = array(XML_SERIALIZER_OPTION_MODE => XML_SERIALIZER_MODE_SIMPLEXML);
   $serializer = new XML_Serializer($options);
   $unserializer = new XML_Unserializer($options);

   $saved_date = array();
   #$controlHTML .= '<br>XML Values: <br>' . print_r($plandata, 1);

   # check to see if we are loading/saving a plan
   if ($elementid >= 1) {
      # load the most recent information into an XML object
      $listobject->querystring = " select elem_xml from proj_element where elementid = $elementid";
      if ($debug) {
         $controlHTML .= "$listobject->querystring ; <br>";
      }
      $listobject->performQuery();
      $elem_xml = $listobject->getRecordValue(1,'elem_xml');
      $result = $unserializer->unserialize($elem_xml, false);
      $saved_data = $unserializer->getUnserializedData();

      if ($result === true) {
         # overwrite template info with saved data
         foreach (array_keys($plandata) as $thiscol) {
            $plandata[$thiscol] = $saved_data[$thiscol];
            if ($debug) {
               $controlHTML .= "setting $thiscol = $saved_data[$thiscol] <br>";
            }
         }
      }
   }
   #$controlHTML .= '<br>XML Values: <br>' . print_r($plandata, 1);
   #$controlHTML .= '<br>XML Values: <br>' . print_r($plandata['resources_recreation'], 1);
   #$controlHTML .= '<br>Form Values: <br>' . print_r($formValues['gw_sourcename'], 1);
   #$controlHTML .= '<br>Form Values: <br>' . print_r($formValues, 1);
   if (isset($formValues['actiontype'])) {
      $actiontype = $formValues['actiontype'];
      switch ($actiontype) {
         case 'updateplan':
         # we came from our form, save any changes
         #$controlHTML .= '<br>Form Values: <br>' . print_r($formValues, 1);

         # first, check permissions
         #$controlHTML .= "Element Permissions: $elemperms <br> ";
         if ( !($elemperms & 2) ) {
            $controlHTML .= "<b>Notice:</b> You do not have edit permissions on this plan. <br>";
         } else {
            foreach ($planpages[$formValues['activepage']]['components'] as $thiscomp) {
               $colinfo = $adminsetuparray[$thiscomp]['column info'];
               # check for data type, multi y/n
               if ($planpages[$formValues['activepage']]['multi']) {
                  # multi-dimensional data
                  $colnames = array_keys($colinfo);
                  $n = count($formValues[$colnames[0]]);
                  $c = 0;
                  $plantemp = array();
                  for ($j = 0; $j < $n; $j++) {
                     $planrec = array();
                     foreach (array_keys($colinfo) as $thiscol) {
                        $formplandata = array_shift($formValues[$thiscol]);
                        $planrec[$thiscol] = $formplandata;
                        #$controlHTML .= "shifting $thiscol #$j<br> ";
                     }
                     $planreclength = strlen(join('',array_values_recursive($planrec)));
                     #$controlHTML .= " $planreclength length<br> ";
                     if ($planreclength > 0) {
                        $plantemp[$c] = $planrec;
                        #$controlHTML .= "Record $c Added<br> ";
                        $c++;
                     }
                  }
                  $plandata[$thiscomp] = $plantemp;
                  #$controlHTML .= "Record " . print_r($plandata[$thiscomp],1) . "<br> ";
               } else {
                  # uni-dimensional data
                  foreach (array_keys($colinfo) as $thiscol) {
                     $plandata[$thiscol] = $formValues[$thiscol];
                     #$controlHTML .= "setting $thiscol = $formValues[$thiscol] <br> ";
                  }
               }
            }

            #$controlHTML .= '<br>XML Values: <br>' . print_r($plandata, 1);
            $result = $serializer->serialize($plandata);
            $elem_xml = $serializer->getSerializedData();
            $elemname = $plandata['planname'];
            $listobject->querystring = "  update proj_element set elem_xml = '$elem_xml', elemname = '$elemname' ";
            $listobject->querystring .= " where elementid = $elementid ";
            if ($debug) {
               $controlHTML .= "$listobject->querystring ; <br>";
            }
            $listobject->performQuery();
         }
         break;

         case 'createplan':
         # insert new plan
         #$debug = 1;
         $listobject->querystring = "  insert into proj_element(projectid, ownerid, groupid, elemtype) ";
         $listobject->querystring .= "  values ($projectid, $userid, $defaultgroupid, 'wsplan') ";
         if ($debug) {
            $controlHTML .= "$listobject->querystring ; <br>";
         }
         $listobject->performQuery();
         $listobject->querystring = "  select max(elementid) as elementid from proj_element ";
         $listobject->querystring .= " where projectid = $projectid ";
         $listobject->querystring .= " and ownerid = $userid ";
         $listobject->querystring .= " and groupid = $defaultgroupid ";
         $listobject->querystring .= " and elemtype = 'wsplan' ";
         if ($debug) {
            $controlHTML .= "$listobject->querystring ; <br>";
         }
         $listobject->performQuery();
         $elementid = $listobject->getRecordValue(1,'elementid');
         $listobject->querystring = " select elem_xml from proj_element where elementid = $elementid";
         if ($debug) {
            $controlHTML .= "$listobject->querystring ; <br>";
         }
         $listobject->performQuery();
         $elem_xml = $listobject->getRecordValue(1,'elem_xml');
         $result = $unserializer->unserialize($elem_xml, false);
         $saved_data = $unserializer->getUnserializedData();

         if ($result === true) {
            # overwrite template info with saved data
            foreach (array_keys($plandata) as $thiscol) {
               $plandata[$thiscol] = $saved_data[$thiscol];
            }
         }
         #$debug = 0;
         break;
      }
   }

   # show list of users/groups plans
   $controlHTML .= '<b>Plan:</b>';
   $controlHTML .= showWaterPlanList($listobject, $projectid, $userid, $elementid);

   $controlHTML .= "<a class='mE' onclick=\"document.forms['planningform'].elements.actiontype.value='createplan'; ";
   $controlHTML .= "xajax_showPlanningForm(xajax.getFormValues('planningform'))\"> New Plan </a>";

   if ($debug) {
      $controlHTML .= '<br>Form Values: <br>' . print_r($plandata['element_seggroup'], 1);
   }
   # refreshes the adminsetup file
   include("adminsetup.php");
   $activepage = '';
   if (isset($formValues['gotopage'])) {
      $activepage = $formValues['gotopage'];
      #$controlHTML .= $activepage;
   }
   #$elementid = $formValues['elementid'];

   if (strlen($activepage) == 0) {
      $pagenames = array_keys($planpages);
      $activepage = $pagenames[0];
   }

   $controlHTML .= showHiddenField('currentgroup', $currentgroup, 1);
   $controlHTML .= showHiddenField('scenarioid', $scenarioid, 1);
   $controlHTML .= showHiddenField('projectid', $projectid, 1);
   $controlHTML .= showHiddenField('activepage', $activepage, 1);
   $controlHTML .= showHiddenField('gotopage', $activepage, 1);
   $controlHTML .= showHiddenField('actiontype', 'updateplan', 1);
   $controlHTML .= showHiddenField('elementid', $elementid, 1);
   $controlHTML .= showHiddenField('seglist', $seglist, 1);
   $controlHTML .= "<ul id='tabmenu'>";
   foreach ($planpages as $thispage) {

      $tab_text = $thispage['tab_text'];
      $title = $thispage['title'];
      $formatname = $thispage['formatname'];
      if ($activepage == $formatname) {
         $class = "class='active'";
      } else {
         $class = '';
      }
      $controlHTML .= "<li><a $class onclick=\"document.forms['planningform'].elements.gotopage.value='$formatname'; ";
      $controlHTML .= "xajax_showPlanningForm(xajax.getFormValues('planningform'))\"> $tab_text </a>";
   }
   $controlHTML .= "</ul>";

   $pagetitle = $planpages[$activepage]['title'];
   $multiform = $planpages[$activepage]['multi'];
   $controlHTML .= "<font class='heading1'>$pagetitle </font><br>";
   if ($elementid > 0) {
      # we have a plan selected, or recently created, show the form
      foreach ($planpages[$activepage]['components'] as $thiscomp) {
         #$controlHTML .= "Loading $thiscomp <br>";
         $controlHTML .= "<div style=\"border: 1px solid rgb(0 , 0, 0);\">";
         if ($multiform) {
            $comptitle = $adminsetuparray['watersupply_plan']['column info'][$thiscomp]['label'];
            $controlHTML .= "<font class='heading2'>$comptitle:</font><br>";
            $showlabels = 1;
            $blankrow = array();
            $colnames = array_keys($adminsetuparray[$thiscomp]['column info']);
            # set up blank row
            foreach ($colnames as $thiscolname) {
               # add a blank row
               $blankrow[$thiscolname] = '';
            }
            $controlHTML .= "<table>";
            $i = 0;
            # add a key
            $blank = 0;
            foreach ($plandata[$thiscomp] as $thisdataline) {
               $blank = 1;
               $rowcontents = join('', $thisdataline);
               if (strlen(ltrim(rtrim($rowcontents))) > 0) {
                  $blank = 0;
                  #$controlHTML .= "<tr><td>row is not blank " . print_r($thisdataline,1) . "</td></tr>";
               }
               # add numeric key column for display
               $thisdataline['numkey'] = $i;
               if ($i > 0) {
                  # no header
                  $showlabels = 0;
                  $adminsetuparray[$thiscomp]['table info']['valign'] = 'top';
               }
               /*
               if ($thiscomp == 'existing_gw_sources') {
                  $debug = 1;

                  $controlHTML .= "values retrieved " . print_r($thisdataline,1) . "<br>";
               }
               */
               $controlHTML .= showFormVars($listobject,$thisdataline,$adminsetuparray[$thiscomp],$showlabels, 0, $debug, $multiform, 1, $disabled);
               $i++;
               /*
               if ($thiscomp == 'existing_gw_sources') {
                  $debug = 0;
               }
               */
            }

            if (!$blank) {
               # insert two blank rows at the end
               #$blankrow['numkey'] = $i;
               if ($i == 0) {
                  $showlabels = 1;
               } else {
                  $showlabels = 0;
               }
               $controlHTML .= showFormVars($listobject,$blankrow,$adminsetuparray[$thiscomp],$showlabels, 0, $debug, $multiform, 1, $disabled);
               $i++;
               $showlabels = 0;
               $blankrow['numkey'] = $i;
               $controlHTML .= showFormVars($listobject,$blankrow,$adminsetuparray[$thiscomp],$showlabels, 0, $debug, $multiform, 1, $disabled);
               $i++;
               $blankrow['numkey'] = $i;
               $controlHTML .= showFormVars($listobject,$blankrow,$adminsetuparray[$thiscomp],$showlabels, 0, $debug, $multiform, 1, $disabled);
            }
            if (isset($adminsetuparray['watersupply_plan']['column info'][$thiscomp]['comment'])) {
               $numcols = count($blankrow);
               $thiscomment = $adminsetuparray['watersupply_plan']['column info'][$thiscomp]['comment'];
               $controlHTML .= "<tr><td colspan=$numcols class='tablecomment'>$thiscomment</td></tr>";
            }
            $controlHTML .= "</table>";
         } else {
            $controlHTML .= showFormVars($listobject,$plandata,$adminsetuparray[$thiscomp],1, 0, $debug, $multiform, 1, $disabled);
         }
         $controlHTML .= "</div>";
      }

      $controlHTML .= showGenericButton('savevalues', 'Save Values', "xajax_showPlanningForm(xajax.getFormValues(\"planningform\"))", 1, $disabled);
   } else {
      $controlHTML .= "<i>No plan selected. Please select a plan from the drop-down menu, or click on 'New Plan' to create a new plan.</i>";
   }
   $controlHTML .= "</form>";

   return $controlHTML;
}
?>