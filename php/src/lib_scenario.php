<?php

function createScenarioOLD($listobject, $projectid, $userid, $scenarioname, $shortname, $subsheds, $debug = 0, $silent = 0) {
   
   $innerHTML = '';
   $errorcode = 0;
   $scid = -1;
   
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $lrcond = " lrseg in ($sslist) ";
      # get list of subsheds
      $listobject->querystring = " select subshedid from scen_lrsegs where  ";
      $listobject->querystring .= "   scenarioid = $scenarioid  ";
      $listobject->querystring .= "   and $lrcond  ";
      $listobject->querystring .= "   and $yrcond  ";
      $listobject->querystring .= "   group by subshedid ";
      if ($debug) { 
         $innerHTML .= "<br>$listobject->querystring ;<br>";
      }
      $listobject->performQuery();
      $sublist = '';
      $ssdel = '';
      $thesews = $listobject->queryrecords;
      if ( count($thesews > 0) ) {
         foreach ($thesews as $thisws) {
            $sublist .= $ssdel . "'" . $thisws['subshedid'] . "'";
            $ssdel = ',';
         }
         $subshedcond = " subshedid in ($sublist) ";
         $asubshedcond = " a.subshedid in ($sublist) ";
         $bsubshedcond = " b.subshedid in ($sublist) ";
         $isubshedcond = " inputyields.subshedid in ($sublist) ";
      } else {
         $subshedcond = ' 1 = 1 ';
         $asubshedcond = ' 1 = 1 ';
         $bsubshedcond = ' 1 = 1 ';
         $isubshedcond = ' 1 = 1 ';
      }
   } else {
      $lrcond = ' 1 = 1 ';
      $subshedcond = ' 1 = 1 ';
      $asubshedcond = ' 1 = 1 ';
      $bsubshedcond = ' 1 = 1 ';
      $isubshedcond = ' 1 = 1 ';
   }

   # get default group for this person
   $listobject->querystring = "select min(groupid) as defgroup from mapusergroups where userid = $userid ";
   if ($debug) { 
      $innerHTML .= "<br>$listobject->querystring ;<br>"; 
   }
   $listobject->performQuery();
   $defgroup = $listobject->getRecordValue(1,'defgroup');
   #print("$listobject->querystring ; <br>");

   $listobject->querystring = "insert into scenario (scenario, shortname, projectid, ownerid, groupid) values ('$scenarioname', '$shortname', $projectid, $userid, $defgroup) ";
   if ($debug) { 
      $innerHTML .= "<br>$listobject->querystring ;<br>";
   }
   $listobject->performQuery();
   $listobject->querystring = "select scenarioid from scenario where scenario = '$scenarioname' and projectid = $projectid ";
   if ($debug) { 
      $innerHTML .= "<br>$listobject->querystring ;<br>"; 
   }
   $listobject->performQuery();
   $scid = $listobject->getRecordValue(1,'scenarioid');
   if ($scid > 0) {
      $innerHTML .= "Scenario $scenarioname with ID $scid created.<br>";
   } else {
      $innerHTML .= "<b>Error:</b> Scenario Creation Failed.<br";
      $errorcode = -1;
   }

   # copy source definitions
   importSourceDefs($projectid, $scid, $listobject, $thisyear, $debug);

   # set up bmp tables
   setupBMPTablesForVortex($listobject, $scid, $projectid, $debug);
   
   return array('innerHTML'=>$innerHTML, 'errorcode'=>$errorcode, 'scenarioid'=>$scid);
}


function deleteScenario($listobject, $scenarioid, $thisyear) {

   if (strlen($thisyear) > 0) {

      $yrcond = "thisyear in ($thisyear)";
   } else {
      $yrcond = " (1 = 1) ";
   }


   $scentables = array(
      'scenario'=>array('yrcol'=>''), 'scen_lrsegs'=>array('yrcol'=>''),
      'scen_pollutant_inputs'=>array('yrcol'=>''), 'scenario_bmps'=>array('yrcol'=>''),
      'scen_masslink_comps'=>array('yrcol'=>''), 'scen_seggroup_bmp'=>array('yrcol'=>''),
      'scen_masslinks'=>array('yrcol'=>''), 'scen_seggrouplu'=>array('yrcol'=>''),
      'scen_bmp_area_effic'=>array('yrcol'=>''), 'scen_max_uptake'=>array('yrcol'=>''),
      'scen_seggroups'=>array('yrcol'=>''), 'scen_bmp_data'=>array('yrcol'=>''),
      'scen_model_delivered'=>array('yrcol'=>''), 'scen_septic'=>array('yrcol'=>''),
      'scen_bmp_luchghist'=>array('yrcol'=>''), 'scen_model_eos'=>array('yrcol'=>''),
      'scen_sourceloadtype'=>array('yrcol'=>''), 'scen_compare_nutman'=>array('yrcol'=>''),
      'scen_modelps_delivered'=>array('yrcol'=>''), 'scen_sourceperunitarea'=>array('yrcol'=>''),
      'scen_distance_index'=>array('yrcol'=>''), 'scen_modelps_eos'=>array('yrcol'=>''),
      'scen_sourcepollprod'=>array('yrcol'=>''), 'scen_eof_balance'=>array('yrcol'=>''),
      'scen_model_stats'=>array('yrcol'=>''), 'scen_sourcepollutants'=>array('yrcol'=>''),
      'scen_error_params_uptk'=>array('yrcol'=>''), ' scen_model_storage'=>array('yrcol'=>''),
      'scen_sourcepops'=>array('yrcol'=>''), 'scen_hspf_parms'=>array('yrcol'=>''),
      'scen_model_uptake'=>array('yrcol'=>''), 'scen_sources'=>array('yrcol'=>''),
      'scen_landseg_uptakes'=>array('yrcol'=>''), 'scen_monperunitarea'=>array('yrcol'=>''),
      'scen_species_uptake'=>array('yrcol'=>''), 'scen_legume_n'=>array('yrcol'=>''),
      'scen_monsubproduction'=>array('yrcol'=>''), 'scen_subshed_atmosdep'=>array('yrcol'=>''),
      'scen_lrpopproject'=>array('yrcol'=>''), 'scen_monthlydistro'=>array('yrcol'=>''),
      'scen_subshed_bmps'=>array('yrcol'=>''), 'scen_lrseg_atmosdep'=>array('yrcol'=>''),
      'scen_nutrient_components'=>array('yrcol'=>''), 'scen_subsheds'=>array('yrcol'=>''),
      'scen_lrseg_bmps'=>array('yrcol'=>''), 'scen_pointsource'=>array('yrcol'=>''),
      'scen_crops'=>array('yrcol'=>''), 'scen_crop_curves'=>array('yrcol'=>''),
      'inputyields'=>array('yrcol'=>'', 'scen_source_transport'=>array('yrcol'=>''))
   );


   foreach (array_keys($scentables) as $scentable) {
      # dont do it if we ask for multiple years and this is the scenario table, then we keep the scenario, but lose the
      # years in the tables
      if ( ! ( (strlen($thisyear) > 0) and ($scentable == 'scenario') ) ) {
         $listobject->querystring = "  delete from $scentable ";
         $listobject->querystring .= " WHERE scenarioid = $scenarioid ";
         $listobject->querystring .= "    AND $yrcond ";

         print("$listobject->querystring ; <br>");
         $listobject->performQuery();
      }

   }

}


function copyScenario($listobject, $scenarioid, $src_scenarioid, $tables, $theseyears) {

   if (strlen($theseyears) > 0) {
      $yrcond = "thisyear in ($theseyears)";
   } else {
      $yrcond = " (1 = 1) ";
   }


   $scentables = array(
      'scenario'=>array('yrcol'=>''), 'scen_lrsegs'=>array('yrcol'=>''),
      'scen_pollutant_inputs'=>array('yrcol'=>''), 'scenario_bmps'=>array('yrcol'=>''),
      'scen_masslink_comps'=>array('yrcol'=>''), 'scen_seggroup_bmp'=>array('yrcol'=>''),
      'scen_masslinks'=>array('yrcol'=>''), 'scen_seggrouplu'=>array('yrcol'=>''),
      'scen_bmp_area_effic'=>array('yrcol'=>''), 'scen_max_uptake'=>array('yrcol'=>''),
      'scen_seggroups'=>array('yrcol'=>''), 'scen_bmp_data'=>array('yrcol'=>''),
      'scen_model_delivered'=>array('yrcol'=>''), 'scen_septic'=>array('yrcol'=>''),
      'scen_bmp_luchghist'=>array('yrcol'=>''), 'scen_model_eos'=>array('yrcol'=>''),
      'scen_sourceloadtype'=>array('yrcol'=>''), 'scen_compare_nutman'=>array('yrcol'=>''),
      'scen_modelps_delivered'=>array('yrcol'=>''), 'scen_sourceperunitarea'=>array('yrcol'=>''),
      'scen_distance_index'=>array('yrcol'=>''), 'scen_modelps_eos'=>array('yrcol'=>''),
      'scen_sourcepollprod'=>array('yrcol'=>''), 'scen_eof_balance'=>array('yrcol'=>''),
      'scen_model_stats'=>array('yrcol'=>''), 'scen_sourcepollutants'=>array('yrcol'=>''),
      'scen_error_params_uptk'=>array('yrcol'=>''), ' scen_model_storage'=>array('yrcol'=>''),
      'scen_sourcepops'=>array('yrcol'=>''), 'scen_hspf_parms'=>array('yrcol'=>''),
      'scen_model_uptake'=>array('yrcol'=>''), 'scen_sources'=>array('yrcol'=>''),
      'scen_landseg_uptakes'=>array('yrcol'=>''), 'scen_monperunitarea'=>array('yrcol'=>''),
      'scen_species_uptake'=>array('yrcol'=>''), 'scen_legume_n'=>array('yrcol'=>''),
      'scen_monsubproduction'=>array('yrcol'=>''), 'scen_subshed_atmosdep'=>array('yrcol'=>''),
      'scen_lrpopproject'=>array('yrcol'=>''), 'scen_monthlydistro'=>array('yrcol'=>''),
      'scen_subshed_bmps'=>array('yrcol'=>''), 'scen_lrseg_atmosdep'=>array('yrcol'=>''),
      'scen_nutrient_components'=>array('yrcol'=>''), 'scen_subsheds'=>array('yrcol'=>''),
      'scen_lrseg_bmps'=>array('yrcol'=>''), 'scen_pointsource'=>array('yrcol'=>''),
      'scen_crops'=>array('yrcol'=>''), 'scen_crop_curves'=>array('yrcol'=>''),
      'inputyields'=>array('yrcol'=>'', 'scen_source_transport'=>array('yrcol'=>''))
   );


   foreach (array_keys($scentables) as $scentable) {
      # dont do it if we ask for multiple years and this is the scenario table, then we keep the scenario, but lose the
      # years in the tables
      if ( ! ( (strlen($thisyear) > 0) and ($scentable == 'scenario') ) ) {
         $listobject->querystring = "  delete from $scentable ";
         $listobject->querystring .= " WHERE scenarioid = $scenarioid ";
         $listobject->querystring .= "    AND $yrcond ";

         print("$listobject->querystring ; <br>");
         $listobject->performQuery();
      }

   }

}



?>