<?php

include_once ("$libpath/xajax/xajax_core/xajax.inc.php");
if (strlen($xajaxscript) > 0) {
   $xajax = new xajax($xajaxscript);
   $ajargtester = new xajaxArgumentManager;
   $ajargtester->xajaxArgumentManager();
} else {
   $xajax = new xajax(NULL);
}

include_once ("$libpath/xdg/xajaxgrid.inc.php");
# includes the status bar routines
$xajax->registerFunction("ag_refreshAnalysisWindow");

if (!$noajax) {
   $xajax->processRequest();
}

function ag_refreshAnalysisWindow($formValues) {
   //include_once("adminsetup.php");
   $objResponse = new xajaxResponse();
   $divname = $formValues['divname'];
   $awin = showAnalysisWindow($formValues);
   $controlHTML = $awin['innerHTML'];
   //$controlHTML = 'test';
   $objResponse->assign($divname,"innerHTML",$controlHTML);
   return $objResponse;
}

function ag_showAnalysisGrid($formValues, $tablename, $form_name) {
   global $session_db;
   // now that the table is set up, show the dataGrid
   $result = array('innerHTML'=>'', 'subquery'=>'');
   if (isset($formValues['offset'])) {
      $offset = $formValues['offset'];
   } else {
      $offset = 0;
   }
   if (isset($formValues['first'])) {
      $offset = 0;
   }
   if (isset($formValues['limit'])) {
      $limit = $formValues['limit'];
   } else {
      $limit = 10;
   }
   if (isset($formValues['order'])) {
      $order = $formValues['order'];
   } else {
      $order = '';
   }
   // is this unique enough for yah??
   //$divname = $tablename;
   $divname = 'div' . $form_name;
   $ag = ag_createxAjaxGrid($tablename, $divname, $offset, $limit, null, null, $order, $form_name);
   $result['innerHTML'] = $ag['innerHTML'];
   $result['subquery'] = $ag['query'];
   /*
   $session_db->tablename = $tablename;
   $session_db->getAllRecords($offset,$limit,$order);
   $session_db->show = 0;
   $session_db->showList();
   $html = $session_db->outstring;
   */ 
   return $result;
}

function ag_createxAjaxGrid($tablename, $divname, $start = 0, $limit = 1,$filter = null, $content = null, $order = null, $form_name){
   global $session_db;
   $result = array('innerHTML'=>'', 'query'=>'');
   $html = '';
   
   // for now, we keep this format, but make sure that filter and content (the two sides of the where x = y clause) are null
   // later, we will integrate this with the query wizard component to give advanced column control as well as conditions
   // and ordering
   $session_db->tablename = $tablename;
   if($content == null){
      $numRows = $session_db->getNumRows();
      $session_db->getAllRecords($start,$limit,"\"$order\"");
      $arreglo = $session_db->queryrecords;
      //$html .= "axaxGrid query: " . $session_db->querystring . "<br>";
   }else{
      $numRows = $session_db->getNumRows($filter, $content);
      $arreglo = $session_db->getAllRecords($start,$limit,"\"$order\"");
      // this is disabled until we get integration with the query wizard
      //$arreglo =& Person::getRecordsFiltered($start, $limit, $filter, $content, $order);  
   }
   $result['query'] = $session_db->querystring;
   if($filter != null)
      $_SESSION['filter'] = $filter;
   
   // Editable zone
   // get any formatting info for the variables from our object
   $columns = $session_db->getColumns($tablename);
   
   $headers = array();
   $attribsHeader = array();
   $attribsCols = array();
   $eventHeader = array();
   $fieldsFromSearch = array();
   $fieldsFromSearchShowAs = array();
   
   $numcols = count($columns);
   
   if (class_exists('ScrollTable')) {
      $table = new ScrollTable($numcols,$start,$limit,$filter,$numRows,$content,$order);
      
      $table->formname = $form_name;
      $table->use_post = 1;
      $table->read_only = 1;
      $table->edtext = 'Edit';
      $table->deltext = 'Delete';
      $table->img_url = '/images';
      $table->show_funcjs = 'xajax_ag_refreshAnalysisWindow';
      $table->setFooter();
      foreach (array_keys($arreglo[0]) as $thiscol) {
         $headers[] = $thiscol;
         $eventHeader[] = $table->generateHeaderAction($thiscol);
      }
      $table->setHeader('title',$headers,$attribsHeader,$eventHeader);

      $j = 0;
      foreach ( $arreglo as $row) {
         // Change here by the name of fields of its database table
         $rowc = array();
         foreach (array_keys($row) as $thiskey) {
            $rowc[] = $row[$thiskey];
         }

         //$rowc[] = '<a href="?" onClick="xajax_show('.$row['id'].');return false">'.$row['lastname'].'</a>';
         $table->addRow($divname,$rowc);
         $j++;

      }

      // End Editable Zone

      $html .= $table->render();
   } else {
      $html .= "Class: ScrollTable - does not exist.<br>";
   }
   
   $result['innerHTML'] = $html;
   return $result;
}



function ag_showAnalysisQueryWizard ($formValues, $session_table, $form_name) {
   global $adminsetuparray, $session_db;
   $query_wiz = array();
   $controlHTML = '';
   
   ############################################################
   ###                 CUSTOM OUTPUT FORM                   ###
   ############################################################
   $controlHTML .= "<a class=\"mH\"";
   $controlHTML .= "onclick=\"toggleMenu('$form_name" . "_format')\">+ Show/Hide Custom Query</a>";
   $controlHTML .= "<div id=\"$form_name" . "_format\" class=\"mL\">";
   # show a set of custom queryWizard objects
   $queryparent = new blankShell;
   # setting this to the query assembled by the search object
   $queryparent->dbtblname = $session_table;
   $querywizard = new queryWizardComponent;
   $querywizard->parentobject = $queryparent;
   $querywizard->listobject = $session_db;
   # create a list for use in the form drop-downs of the various columns that we can select
   $aslist = '';
   $asep = '';
   $table_cols = $session_db->getColumns($session_table);
   foreach ($table_cols as $thiscol) {
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
   $querywizard->quote_tablename = 1;
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
   $controlHTML .= "<center>" . showGenericButton('search','Search', "xajax_ag_refreshAnalysisWindow(xajax.getFormValues(\"$form_name\")) ; ", 1, 0) . "</center>";
   $controlHTML .= "</div><hr>";
   
   ############################################################
   ###                  END CUSTOM OUTPUT FORM              ###
   ############################################################
   $query_wiz['innerHTML'] = $controlHTML;
   $query_wiz['sql'] = $querywizard->sqlstring;
   return $query_wiz;
}
?>