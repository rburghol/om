<?php

function distributeGroupCrops($listobject, $projectid, $scenarioid, $subsheds, $thisyear, $landuses, $cropdata, $debug) {

   # queries for the lrseg landuses in the given set
   # assemble input variables into conditions

   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear in ($thisyear) ";
      $ayrcond = " a.thisyear in ($thisyear) ";
      $byrcond = " b.thisyear in ($thisyear) ";
   } else {
      $yrcond = ' 1 = 1 ';
      $ayrcond = ' 1 = 1 ';
      $byrcond = ' 1 = 1 ';
   }

   if (count($landuses) > 0) {
      $lulist = "'" . join("','", $landuses) . "'";
      $lucond = " luname in ($lulist) ";
      $alucond = " a.luname in ($lulist) ";
      $blucond = " b.luname in ($lulist) ";
   } else {
      $lucond = ' 1 = 1 ';
      $alucond = ' 1 = 1 ';
      $blucond = ' 1 = 1 ';
   }

   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
      # get list of subsheds
      $listobject->querystring = " select subshedid from scen_lrsegs where  ";
      $listobject->querystring .= "   scenarioid = $scenarioid  ";
      $listobject->querystring .= "   and $subshedcond  ";
      $listobject->querystring .= "   and $yrcond  ";
      $listobject->querystring .= "   group by subshedid ";
      if ($debug) { print("<br>$listobject->querystring ;<br>"); }
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
      $subshedcond = ' 1 = 1 ';
      $asubshedcond = ' 1 = 1 ';
      $bsubshedcond = ' 1 = 1 ';
      $isubshedcond = ' 1 = 1 ';
   }

   # gets the crop info into its parts
   $cropname = $cropdata['cropname'];
   $croparea = $cropdata['croparea'];

   $keys = array_keys($cropname);

   # clear old crop data
   $listobject->querystring = " delete from scen_crops ";
   $listobject->querystring .= " where $subshedcond  ";
   $listobject->querystring .= "    and $yrcond ";
   $listobject->querystring .= "    and $lucond ";
   $listobject->querystring .= "    and scenarioid = $scenarioid ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

   foreach ($keys as $thisid) {
      $thisname = $cropname[$thisid];
      $thisarea = $croparea[$thisid];

      # update inputyields records with submitted data
      $listobject->querystring = " insert into scen_crops (scenarioid, subshedid, thisyear,  ";
      $listobject->querystring .= "    luname, cropname, croparea ) ";
      $listobject->querystring .= " select $scenarioid, a.subshedid, a.thisyear, a.luname, '$thisname', ";
      $listobject->querystring .= "    $thisarea * a.luarea / b.groupluarea  ";
      $listobject->querystring .= " from scen_subsheds as a,  ";
      $listobject->querystring .= " ( ";
      $listobject->querystring .= "  select sum(luarea) as groupluarea ";
      $listobject->querystring .= "  from scen_subsheds ";
      $listobject->querystring .= "  where scenarioid = $scenarioid  ";
      $listobject->querystring .= "     and $subshedcond  ";
      $listobject->querystring .= "      and $yrcond ";
      $listobject->querystring .= "      and $lucond ";
      $listobject->querystring .= " ) as b ";
      $listobject->querystring .= " where a.scenarioid = $scenarioid  ";
      $listobject->querystring .= "    and $asubshedcond  ";
      $listobject->querystring .= "    and $ayrcond ";
      $listobject->querystring .= "    and $alucond ";
      if ($debug) { print("<br>$listobject->querystring ;<br>"); }
      $listobject->performQuery();
   }

}

function updateCropCurves($listobject, $projectid, $scenarioid, $subsheds, $cropname, $cropdata, $debug) {

   # queries for the lrseg landuses in the given set
   # assemble input variables into conditions

   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
      # get list of subsheds
      $listobject->querystring = " select subshedid from scen_lrsegs where  ";
      $listobject->querystring .= "   scenarioid = $scenarioid  ";
      $listobject->querystring .= "   and $subshedcond  ";
      $listobject->querystring .= "   group by subshedid ";
      if ($debug) { print("<br>$listobject->querystring ;<br>"); }
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
      $subshedcond = ' 1 = 1 ';
      $asubshedcond = ' 1 = 1 ';
      $bsubshedcond = ' 1 = 1 ';
      $isubshedcond = ' 1 = 1 ';
   }

   # gets the crop info into its parts
   $selected_distros = $cropdata['edit_distro'];

   $keys = array_keys($selected_distros);

   foreach ($keys as $thisid) {
      $source_type = $cropdata['source_type'][$thisid];
      print("Updating $source_type <br>");
      $curvetype = $cropdata['curvetype'][$thisid];
      $plantdate = $cropdata['plantdate'][$thisid];
      $harvestdate = $cropdata['harvestdate'][$thisid];
      $model_plant = $cropdata['model_plant'][$thisid];
      $need_pct = $cropdata['need_pct'][$thisid];

      # clear old crop data for this source_type (allows individual editing)
      $listobject->querystring = " delete from scen_crop_curves ";
      $listobject->querystring .= " where $subshedcond  ";
      $listobject->querystring .= "    and cropname = '$cropname' ";
      $listobject->querystring .= "    and scenarioid = $scenarioid ";
      $listobject->querystring .= "    and source_type = '$source_type' ";
      if ($debug) { print("<br>$listobject->querystring ;<br>"); }
      $listobject->performQuery();

      # actual monthly values
      $jan = $cropdata['jan'][$thisid];
      $feb = $cropdata['feb'][$thisid];
      $mar = $cropdata['mar'][$thisid];
      $apr = $cropdata['apr'][$thisid];
      $may = $cropdata['may'][$thisid];
      $jun = $cropdata['jun'][$thisid];
      $jul = $cropdata['jul'][$thisid];
      $aug = $cropdata['aug'][$thisid];
      $sep = $cropdata['sep'][$thisid];
      $oct = $cropdata['oct'][$thisid];
      $nov = $cropdata['nov'][$thisid];
      $dec = $cropdata['dec'][$thisid];

      # update inputyields records with submitted data
      $listobject->querystring = " insert into scen_crop_curves (scenarioid, subshedid, cropname,  ";
      $listobject->querystring .= "    source_type, curvetype, plantdate, harvestdate, model_plant, need_pct,";
      $listobject->querystring .= "    jan, feb, mar, apr, may, jun, jul, aug, sep, oct, nov, dec ) ";
      $listobject->querystring .= " select $scenarioid, a.subshedid, '$cropname', '$source_type', ";
      $listobject->querystring .= "    $curvetype, '$plantdate', '$harvestdate', '$model_plant', $need_pct,";
      $listobject->querystring .= "    $jan, $feb, $mar, $apr, $may, $jun, $jul, $aug, $sep, $oct, $nov, $dec ";
      $listobject->querystring .= " from scen_subsheds as a ";
      $listobject->querystring .= " where a.scenarioid = $scenarioid  ";
      $listobject->querystring .= "    and $asubshedcond  ";
      $listobject->querystring .= " group by a.subshedid  ";
      if ($debug) { print("<br>$listobject->querystring ;<br>"); }
      $listobject->performQuery();
   }

}


function updateAppValues($listobject, $projectid, $scenarioid, $subsheds, $thisyear, $landuses, $debug) {

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions

   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear in ($thisyear) ";
      $ayrcond = " a.thisyear in ($thisyear) ";
      $byrcond = " b.thisyear in ($thisyear) ";
   } else {
      $yrcond = ' 1 = 1 ';
      $ayrcond = ' 1 = 1 ';
      $byrcond = ' 1 = 1 ';
   }

   if (count($landuses) > 0) {
      $lulist = "'" . join("','", $landuses) . "'";
      $lucond = " luname in ($lulist) ";
      $alucond = " a.luname in ($lulist) ";
      $blucond = " b.luname in ($lulist) ";
   } else {
      $lucond = ' 1 = 1 ';
      $alucond = ' 1 = 1 ';
      $blucond = ' 1 = 1 ';
   }

   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
      # get list of subsheds
      $listobject->querystring = " select subshedid from scen_lrsegs where  ";
      $listobject->querystring .= "   scenarioid = $scenarioid  ";
      $listobject->querystring .= "   and $subshedcond  ";
      $listobject->querystring .= "   and $yrcond  ";
      $listobject->querystring .= "   group by subshedid ";
      if ($debug) { print("<br>$listobject->querystring ;<br>"); }
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
      $subshedcond = ' 1 = 1 ';
      $asubshedcond = ' 1 = 1 ';
      $bsubshedcond = ' 1 = 1 ';
      $isubshedcond = ' 1 = 1 ';
   }

   # update inputyields records with submitted data
   $listobject->querystring = "  select maxyieldtarget, optyieldtarget ";
   $listobject->querystring .= " from inputyields ";
   $listobject->querystring .= " where scenarioid = $scenarioid  ";
   $listobject->querystring .= "    and $subshedcond  ";
   $listobject->querystring .= "    and $yrcond ";
   $listobject->querystring .= "    and $lucond ";
   $listobject->querystring .= " group by maxyieldtarget, optyieldtarget ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

   $targrecs = $listobject->queryrecords;

   foreach ($targrecs as $thisrec) {

      # get submitted values
      $maxyieldtarget = $thisrec['maxyieldtarget'];
      $optyieldtarget = $thisrec['optyieldtarget'];

      switch($optyieldtarget) {
         case 1:
         $noptcol = 'mean_needn';
         $poptcol = 'mean_needp';
         break;
         case 2:
         $noptcol = 'mean_uptn';
         $poptcol = 'mean_uptp';
         break;
         case 3:
         $noptcol = 'targ_needn';
         $poptcol = 'targ_needp';
         break;
         case 4:
         $noptcol = 'targ_uptn';
         $poptcol = 'targ_uptp';
         break;
         case 5:
         $noptcol = 'high_needn';
         $poptcol = 'high_needp';
         break;
         case 6:
         $noptcol = 'high_uptn';
         $poptcol = 'high_uptp';
         break;
         default:
         $noptcol = 'high_needn';
         $poptcol = 'high_needp';
         break;
      }

      switch($maxyieldtarget) {
         case 1:
         $nmaxcol = 'mean_needn';
         $pmaxcol = 'mean_needp';
         break;
         case 2:
         $nmaxcol = 'mean_uptn';
         $pmaxcol = 'mean_uptp';
         break;
         case 3:
         $nmaxcol = 'targ_needn';
         $pmaxcol = 'targ_needp';
         break;
         case 4:
         $nmaxcol = 'targ_uptn';
         $pmaxcol = 'targ_uptp';
         break;
         case 5:
         $nmaxcol = 'high_needn';
         $pmaxcol = 'high_needp';
         break;
         case 6:
         $nmaxcol = 'high_uptn';
         $pmaxcol = 'high_uptp';
         break;
         default:
         $nmaxcol = 'high_needn';
         $pmaxcol = 'high_needp';
         break;
      }


      # update inputyields records with submitted data
      $listobject->querystring = " update inputyields set optn = nrate * $noptcol, optp = prate * $poptcol, ";
      $listobject->querystring .= "  maxn = maxnrate * $nmaxcol, maxp = maxprate * $pmaxcol ";
      $listobject->querystring .= " where scenarioid = $scenarioid  ";
      $listobject->querystring .= "    and $subshedcond  ";
      $listobject->querystring .= "    and $yrcond ";
      $listobject->querystring .= "    and $lucond ";
      $listobject->querystring .= "    and maxyieldtarget = $maxyieldtarget ";
      $listobject->querystring .= "    and optyieldtarget = $optyieldtarget ";
      if ($debug) { print("<br>$listobject->querystring ;<br>"); }
      $listobject->performQuery();
   }

}

function updateAppRates($listobject, $projectid, $scenarioid, $subsheds, $thisyear, $landuses, $apprateinfo, $debug) {

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions

   # not multi-land use safe

   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear in ($thisyear) ";
      $ayrcond = " a.thisyear in ($thisyear) ";
      $byrcond = " b.thisyear in ($thisyear) ";
   } else {
      $yrcond = ' 1 = 1 ';
      $ayrcond = ' 1 = 1 ';
      $byrcond = ' 1 = 1 ';
   }

   if (count($landuses) > 0) {
      $lulist = "'" . join("','", $landuses) . "'";
      $lucond = " luname in ($lulist) ";
      $alucond = " a.luname in ($lulist) ";
      $blucond = " b.luname in ($lulist) ";
   } else {
      $lucond = ' 1 = 1 ';
      $alucond = ' 1 = 1 ';
      $blucond = ' 1 = 1 ';
   }

   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
      # get list of subsheds
      $listobject->querystring = " select subshedid from scen_lrsegs where  ";
      $listobject->querystring .= "   scenarioid = $scenarioid  ";
      $listobject->querystring .= "   and $subshedcond  ";
      $listobject->querystring .= "   and $yrcond  ";
      $listobject->querystring .= "   group by subshedid ";
      if ($debug) { print("<br>$listobject->querystring ;<br>"); }
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
      $subshedcond = ' 1 = 1 ';
      $asubshedcond = ' 1 = 1 ';
      $bsubshedcond = ' 1 = 1 ';
      $isubshedcond = ' 1 = 1 ';
   }

   # get submitted values
   $limconstit = $apprateinfo['limconstit'];
   $nrate = $apprateinfo['nrate'];
   $prate = $apprateinfo['prate'];
   $maxnrate = $apprateinfo['maxnrate'];
   $maxprate = $apprateinfo['maxprate'];
   $maxyieldtarget = $apprateinfo['maxyieldtarget'];
   $optyieldtarget = $apprateinfo['optyieldtarget'];


   # insert blank values if any are missing
   $luname = $landuses[0];
   $listobject->querystring = "  insert into inputyields (scenarioid, projectid, thisyear, luname, subshedid) ";
   $listobject->querystring .= " select $scenarioid, $projectid, $thisyear, '$luname', a.subshedid  ";
   $listobject->querystring .= " from (select subshedid from scen_subsheds where  ";
   $listobject->querystring .= "        scenarioid = $scenarioid  ";
   $listobject->querystring .= "        and $subshedcond  ";
   $listobject->querystring .= "        and $yrcond  ";
   $listobject->querystring .= "        group by subshedid ";
   $listobject->querystring .= "    ) as a ";
   $listobject->querystring .= "  where subshedid not in (select subshedid from inputyields where  ";
   $listobject->querystring .= "        scenarioid = $scenarioid  ";
   $listobject->querystring .= "        and $subshedcond  ";
   $listobject->querystring .= "        and $yrcond  ";
   $listobject->querystring .= "        and $lucond  ";
   $listobject->querystring .= "        group by subshedid ";
   $listobject->querystring .= "    ) ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

   switch($optyieldtarget) {
      case 1:
      $noptcol = 'mean_needn';
      $poptcol = 'mean_needp';
      break;
      case 2:
      $noptcol = 'mean_uptn';
      $poptcol = 'mean_uptp';
      break;
      case 3:
      $noptcol = 'targ_needn';
      $poptcol = 'targ_needp';
      break;
      case 4:
      $noptcol = 'targ_uptn';
      $poptcol = 'targ_uptp';
      break;
      case 5:
      $noptcol = 'high_needn';
      $poptcol = 'high_needp';
      break;
      case 6:
      $noptcol = 'high_uptn';
      $poptcol = 'high_uptp';
      break;
      default:
      $noptcol = 'high_needn';
      $poptcol = 'high_needp';
      break;
   }

   switch($maxyieldtarget) {
      case 1:
      $nmaxcol = 'mean_needn';
      $pmaxcol = 'mean_needp';
      break;
      case 2:
      $nmaxcol = 'mean_uptn';
      $pmaxcol = 'mean_uptp';
      break;
      case 3:
      $nmaxcol = 'targ_needn';
      $pmaxcol = 'targ_needp';
      break;
      case 4:
      $nmaxcol = 'targ_uptn';
      $pmaxcol = 'targ_uptp';
      break;
      case 5:
      $nmaxcol = 'high_needn';
      $pmaxcol = 'high_needp';
      break;
      case 6:
      $nmaxcol = 'high_uptn';
      $pmaxcol = 'high_uptp';
      break;
      default:
      $nmaxcol = 'high_needn';
      $pmaxcol = 'high_needp';
      break;
   }


   if ($apprateinfo['edit_nr']) {
      # a request to manually edit the need rates for this land use has been submitted
      # update inputyields records with submitted data
      # get submitted values
      $high_needn = $apprateinfo['high_needn'];
      $high_needp = $apprateinfo['high_needp'];
      $high_uptn = $apprateinfo['high_uptn'];
      $high_uptp = $apprateinfo['high_uptp'];
      $mean_needn = $apprateinfo['mean_needn'];
      $mean_needp = $apprateinfo['mean_needp'];
      $mean_uptn = $apprateinfo['mean_uptn'];
      $mean_uptp = $apprateinfo['mean_uptp'];
      $targ_needn = $apprateinfo['targ_needn'];
      $targ_needp = $apprateinfo['targ_needp'];
      $targ_uptn = $apprateinfo['targ_uptn'];
      $targ_uptp = $apprateinfo['targ_uptp'];
      $uptake_n = $apprateinfo['uptake_n'];
      $uptake_p = $apprateinfo['uptake_p'];
      $listobject->querystring = " update inputyields set high_needn = $high_needn, high_needp = $high_needp, ";
      $listobject->querystring .= "  high_uptn = $high_uptn, high_uptp = $high_uptp, ";
      $listobject->querystring .= "  mean_needn = $mean_needn, mean_needp = $mean_needp, ";
      $listobject->querystring .= "  mean_uptn = $mean_uptn, mean_uptp = $mean_uptp, ";
      $listobject->querystring .= "  targ_needn = $targ_needn, targ_needp = $targ_needp, ";
      $listobject->querystring .= "  targ_uptn = $targ_uptn, targ_uptp = $targ_uptp, ";
      $listobject->querystring .= "  uptake_n = $uptake_n, uptake_p = $uptake_p  ";
      $listobject->querystring .= " where scenarioid = $scenarioid  ";
      $listobject->querystring .= "    and $subshedcond  ";
      $listobject->querystring .= "    and $yrcond ";
      $listobject->querystring .= "    and $lucond ";
      if ($debug) { print("<br>$listobject->querystring ;<br>"); }
      $listobject->performQuery();
   }

   # update inputyields records with submitted data
   $listobject->querystring = " update inputyields set nm_planbase = $limconstit,  ";
   $listobject->querystring .= "  nrate = $nrate, prate = $prate, ";
   $listobject->querystring .= "  optn = $nrate * $noptcol, optp = $prate * $poptcol,  ";
   $listobject->querystring .= "  maxnrate = $maxnrate, maxprate = $maxprate, ";
   $listobject->querystring .= "  maxn = $maxnrate * $nmaxcol, maxp = $maxprate * $pmaxcol,  ";
   $listobject->querystring .= "  maxyieldtarget = $maxyieldtarget, optyieldtarget = $optyieldtarget  ";
   $listobject->querystring .= " where scenarioid = $scenarioid  ";
   $listobject->querystring .= "    and $subshedcond  ";
   $listobject->querystring .= "    and $yrcond ";
   $listobject->querystring .= "    and $lucond ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

}


function calculateLUNeedFromCrops($listobject, $projectid, $scenarioid, $subsheds, $thisyear, $landuses, $dc_method, $dc_value, $debug) {

   # calcuates the aggregate, crop area weighted crop uptake, need, etc. from the crops in the crop table

   # this is NOT multi-landuse safe. Only one land use can be edited at a time with this script

   # $dc_method - how to calculate double crops
   # 1 - use overlap of winter and summer double crops ONLY
   # 2 - Use overlap of winter/summer, or crop_area - luarea, whichever is SMALLER
   # 3 - Use crop_area - luarea ONLY
   # 4 - Manually Enter Double Cropping Percentage
   # -1 - do NOT set a value for dc_method, use the previous values

   if ($dc_value > 0.5) {
      $dc_value = 0.5;
   }

   if ( !($dc_value) or ($dc_value < 0.0) ) {
      $dc_value = 0.0;
   }

   $thisdate = date('r',time());

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions


   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear = $thisyear ";
      $ayrcond = " a.thisyear = $thisyear ";
      $byrcond = " b.thisyear = $thisyear ";
      $iyrcond = " inputyields.thisyear = $thisyear ";
   } else {
      $yrcond = ' 1 = 1 ';
      $ayrcond = ' 1 = 1 ';
      $byrcond = ' 1 = 1 ';
      $iyrcond = ' 1 = 1 ';
   }

   if (strlen($landuses) > 0) {
      $lulist = "'" . join("','", $landuses) . "'";
      $lucond = " luname = '$landuses' ";
      $alucond = " a.luname = '$landuses' ";
      $blucond = " b.luname = '$landuses' ";
      $ilucond = " inputyields.luname = '$landuses' ";
   } else {
      return;
   }

   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
      # get list of subsheds
      $listobject->querystring = " select subshedid from scen_lrsegs where  ";
      $listobject->querystring .= "   scenarioid = $scenarioid  ";
      $listobject->querystring .= "   and $subshedcond  ";
      $listobject->querystring .= "   and $yrcond  ";
      $listobject->querystring .= "   group by subshedid ";
      if ($debug) { print("<br>$listobject->querystring ;<br>"); }
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
      $subshedcond = ' 1 = 1 ';
      $asubshedcond = ' 1 = 1 ';
      $bsubshedcond = ' 1 = 1 ';
      $isubshedcond = ' 1 = 1 ';
   }


   # insert blank values if any are missing
   $listobject->querystring = "  insert into inputyields (scenarioid, projectid, thisyear, luname, subshedid) ";
   $listobject->querystring .= " select $scenarioid, $projectid, $thisyear, '$landuses', a.subshedid  ";
   $listobject->querystring .= " from (select subshedid from scen_subsheds where  ";
   $listobject->querystring .= "        scenarioid = $scenarioid  ";
   $listobject->querystring .= "        and $subshedcond  ";
   $listobject->querystring .= "        and $yrcond  ";
   $listobject->querystring .= "        group by subshedid ";
   $listobject->querystring .= "    ) as a ";
   $listobject->querystring .= "  where subshedid not in (select subshedid from inputyields where  ";
   $listobject->querystring .= "        scenarioid = $scenarioid  ";
   $listobject->querystring .= "        and $subshedcond  ";
   $listobject->querystring .= "        and $yrcond  ";
   $listobject->querystring .= "        and $lucond  ";
   $listobject->querystring .= "        group by subshedid ";
   $listobject->querystring .= "    ) ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

   # calculate double crop potential from winter and summer double-croppable crops,
   # and then calculate the overlap that might be inferred from the difference between
   # the total land use area, and the sum of all crops in that land use

   # drop the double cropping table if it already exists
   if ($listobject->tableExists('tmp_dc')) {
      $listobject->querystring = "  drop table tmp_dc  ";
      if ($debug) { print("<br>$listobject->querystring ;<br>"); }
      $listobject->performQuery();
   }
   $listobject->querystring = "  select a.subshedid, a.luname, a.winter_dc, b.summer_dc, ";
   $listobject->querystring .= "    CASE ";
   $listobject->querystring .= "       WHEN a.winter_dc > b.summer_dc THEN b.summer_dc ";
   $listobject->querystring .= "       ELSE a.winter_dc ";
   $listobject->querystring .= "    END as max_dc, 0.0 as overlap_area, $dc_method as dc_method ";
   $listobject->querystring .= " into temp table tmp_dc ";
   $listobject->querystring .= " from ";
   $listobject->querystring .= "    ( ";
   $listobject->querystring .= "        select a.subshedid, a.luname, sum(a.croparea) as winter_dc ";
   $listobject->querystring .= "        from scen_crops as a, proj_crop_type as b ";
   $listobject->querystring .= "        where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "           and b.projectid = $projectid ";
   $listobject->querystring .= "           and a.cropname = b.cropname ";
   # screens for type "Winter Crop May Double"
   $listobject->querystring .= "           and b.crop_dc = 2 ";
   $listobject->querystring .= "           and $asubshedcond ";
   $listobject->querystring .= "           and $ayrcond ";
   $listobject->querystring .= "           and $alucond ";
   $listobject->querystring .= "        group by a.subshedid, a.luname ";
   $listobject->querystring .= "    ) as a, ";
   $listobject->querystring .= "    ( ";
   $listobject->querystring .= "        select a.subshedid, a.luname, sum(a.croparea) as summer_dc ";
   $listobject->querystring .= "        from scen_crops as a, proj_crop_type as b ";
   $listobject->querystring .= "        where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "           and b.projectid = $projectid ";
   $listobject->querystring .= "           and a.cropname = b.cropname ";
   # screens for type "Summer Crop May Double"
   $listobject->querystring .= "           and b.crop_dc = 3 ";
   $listobject->querystring .= "           and $asubshedcond  ";
   $listobject->querystring .= "           and $ayrcond ";
   $listobject->querystring .= "           and $alucond ";
   $listobject->querystring .= "        group by a.subshedid, a.luname ";
   $listobject->querystring .= "    ) as b ";
   $listobject->querystring .= " where a.subshedid = b.subshedid ";
   $listobject->querystring .= "    and a.luname = b.luname ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

   # if dc_method = -1, then we copy the dc_method from the previous value
   if ($dc_method == -1) {
      $listobject->querystring = " update tmp_dc set dc_method = a.dc_method ";
      $listobject->querystring .= " from inputyields as a  ";
      $listobject->querystring .= " where a.scenarioid = $scenarioid  ";
      $listobject->querystring .= "    and $asubshedcond  ";
      $listobject->querystring .= "    and $ayrcond  ";
      $listobject->querystring .= "    and $alucond ";
      $listobject->querystring .= "    and tmp_dc.luname = a.luname ";
      $listobject->querystring .= "    and tmp_dc.subshedid = a.subshedid ";
      if ($debug) { print("<br>$listobject->querystring ;<br>"); }
      $listobject->performQuery();
   }

   # now update to match area excess, if requested
   $listobject->querystring = " update tmp_dc set overlap_area = (a.croparea - b.luarea) ";
   $listobject->querystring .= " from ";
   $listobject->querystring .= "    ( ";
   $listobject->querystring .= "        select a.subshedid, a.luname, sum(a.croparea) as croparea ";
   $listobject->querystring .= "        from scen_crops as a ";
   $listobject->querystring .= "        where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "           and $asubshedcond  ";
   $listobject->querystring .= "           and $ayrcond ";
   $listobject->querystring .= "           and $alucond ";
   $listobject->querystring .= "         group by a.subshedid, a.luname ";
   $listobject->querystring .= "    ) as a, ";
   $listobject->querystring .= "    ( ";
   $listobject->querystring .= "        select subshedid, luname, sum(luarea) as luarea ";
   $listobject->querystring .= "        from scen_subsheds ";
   $listobject->querystring .= "        where scenarioid = $scenarioid  ";
   $listobject->querystring .= "           and $subshedcond  ";
   $listobject->querystring .= "           and $yrcond  ";
   $listobject->querystring .= "           and $lucond ";
   $listobject->querystring .= "        group by subshedid, luname ";
   $listobject->querystring .= "    ) as b ";
   $listobject->querystring .= " where tmp_dc.subshedid = a.subshedid ";
   $listobject->querystring .= "    and a.subshedid = b.subshedid ";
   $listobject->querystring .= "    and a.croparea > b.luarea ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

   # adjust for double cropping, where dc acres > 0
   $listobject->querystring = " update tmp_dc set max_dc = overlap_area ";
   $listobject->querystring .= "    where ( ( max_dc > overlap_area and dc_method = 2 ) ";
   $listobject->querystring .= "           or ( dc_method = 3 ) ) ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();


   # now, area weight the need values for each crop to get a composite need/uptake/etc for the land use
   $listobject->querystring = "  update inputyields set total_acres = a.total_acres, ";
   $listobject->querystring .= "    legume_n = a.legume_n, ";
   $listobject->querystring .= "    uptake_n = a.uptake_n, ";
   $listobject->querystring .= "    uptake_p = a.uptake_p, ";
   $listobject->querystring .= "    mean_uptn = a.mean_uptn, ";
   $listobject->querystring .= "    mean_uptp = a.mean_uptp, ";
   $listobject->querystring .= "    high_uptn = a.high_uptn, ";
   $listobject->querystring .= "    high_uptp = a.high_uptp, ";
   $listobject->querystring .= "    targ_uptn = a.targ_uptn, ";
   $listobject->querystring .= "    targ_uptp = a.targ_uptp, ";
   $listobject->querystring .= "    mean_needn = a.mean_needn,";
   $listobject->querystring .= "    mean_needp = a.mean_needp, ";
   $listobject->querystring .= "    high_needn = a.high_needn, ";
   $listobject->querystring .= "    high_needp = a.high_needp, ";
   $listobject->querystring .= "    targ_needn = a.targ_needn, ";
   $listobject->querystring .= "    targ_needp = a.targ_needp, ";
   $listobject->querystring .= "    n_urratio = a.n_urratio, ";
   $listobject->querystring .= "    p_urratio = a.p_urratio, ";
   $listobject->querystring .= "    rundate = '$thisdate'::timestamp, ";
   $listobject->querystring .= "    n_fix = a.n_fix ";
   if ($dc_method > 0) {
      $listobject->querystring .= "    , dc_pct = a.dc_pct, ";
      $listobject->querystring .= "    dc_method = a.dc_method ";
   }
   $listobject->querystring .= " from ";
   $listobject->querystring .= " ( select $projectid, $scenarioid, a.subshedid, a.thisyear, a.luname, ";
   $listobject->querystring .= "    sum(croparea) as total_acres,";
   $listobject->querystring .= "    sum(c.n_fix * b.high_yld * a.croparea * c.n_uptake) ";
   $listobject->querystring .= "       /sum(a.croparea) as legume_n, ";
   $listobject->querystring .= "    sum(b.max_yld * a.croparea * c.n_uptake) ";
   $listobject->querystring .= "       /sum(a.croparea) as uptake_n, ";
   $listobject->querystring .= "    sum(b.max_yld * a.croparea * c.p_uptake) ";
   $listobject->querystring .= "       /sum(a.croparea) as uptake_p , ";
   $listobject->querystring .= "    1.3 as nrate, 1.3 as prate, 1.8 as maxnrate, 1.8 as maxprate, ";
   $listobject->querystring .= "    sum(b.mean_yld * a.croparea * c.n_uptake) ";
   $listobject->querystring .= "       /sum(a.croparea) as mean_uptn, ";
   $listobject->querystring .= "    sum(b.mean_yld * a.croparea * c.p_uptake) ";
   $listobject->querystring .= "       /sum(a.croparea) as mean_uptp, ";
   $listobject->querystring .= "    sum(b.high_yld * a.croparea * c.n_uptake) ";
   $listobject->querystring .= "       /sum(a.croparea) as high_uptn, ";
   $listobject->querystring .= "    sum(b.high_yld * a.croparea * c.p_uptake) ";
   $listobject->querystring .= "       /sum(a.croparea) as high_uptp, ";
   $listobject->querystring .= "    sum(b.targ_yld * a.croparea * c.n_uptake) ";
   $listobject->querystring .= "       /sum(a.croparea) as targ_uptn, ";
   $listobject->querystring .= "    sum(b.targ_yld * a.croparea * c.p_uptake) ";
   $listobject->querystring .= "       /sum(a.croparea) as targ_uptp, ";
   $listobject->querystring .= "    sum((1.0 - c.n_fix) * b.mean_yld * a.croparea * c.n_uptake) ";
   $listobject->querystring .= "       /sum(a.croparea) as mean_needn, ";
   $listobject->querystring .= "    sum(b.mean_yld * a.croparea * c.p_uptake)/sum(a.croparea) as mean_needp, ";
   $listobject->querystring .= "    sum((1.0 - c.n_fix) * b.high_yld * a.croparea * c.n_uptake)";
   $listobject->querystring .= "       /sum(a.croparea) as high_needn, ";
   $listobject->querystring .= "    sum(b.high_yld * a.croparea * c.p_uptake)/sum(a.croparea) as high_needp, ";
   $listobject->querystring .= "    sum((1.0 - c.n_fix) * b.targ_yld * a.croparea * c.n_uptake)";
   $listobject->querystring .= "       /sum(a.croparea) as targ_needn, ";
   $listobject->querystring .= "    sum(b.targ_yld * a.croparea * c.p_uptake)";
   $listobject->querystring .= "       /sum(a.croparea) as targ_needp, ";
   $listobject->querystring .= "    sum(b.mean_yld * a.croparea * c.n_uptake * c.n_urratio)";
   $listobject->querystring .= "       /sum(b.mean_yld * c.n_uptake * a.croparea) as n_urratio, ";
   $listobject->querystring .= "    sum(b.mean_yld * a.croparea * c.p_uptake * c.p_urratio)";
   $listobject->querystring .= "       /sum(b.mean_yld * c.p_uptake * a.croparea) as p_urratio, ";
   $listobject->querystring .= "    sum(c.n_fix * a.croparea)/sum(a.croparea) as n_fix ";
   if ($dc_method > 0) {
      # do a double cropping calculation
      $listobject->querystring .= "    ,0.0 as dc_pct, $dc_method as dc_method ";
   }
   $listobject->querystring .= " from scen_crops as a, proj_cropyield as b, proj_crop_type as c ";
   $listobject->querystring .= " where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and b.projectid = $projectid ";
   $listobject->querystring .= "    and c.projectid = $projectid ";
   $listobject->querystring .= "    and a.croparea > 0 ";
   $listobject->querystring .= "    and a.cropname = b.cropname ";
   $listobject->querystring .= "    and a.cropname = c.cropname ";
   $listobject->querystring .= "    and a.subshedid = b.subshedid ";
   $listobject->querystring .= "    and $ayrcond ";
   $listobject->querystring .= "    and $asubshedcond  ";
   $listobject->querystring .= " group by a.subshedid, a.thisyear, a.luname ";
   $listobject->querystring .= " order by a.thisyear, a.subshedid, a.luname ";
   $listobject->querystring .= " ) as a ";
   $listobject->querystring .= " where inputyields.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and $isubshedcond  ";
   $listobject->querystring .= "    and $iyrcond ";
   $listobject->querystring .= "    and $ilucond ";
   $listobject->querystring .= "    and inputyields.subshedid = a.subshedid ";
   $listobject->querystring .= "    and inputyields.luname = a.luname ";
   $listobject->querystring .= "    and inputyields.thisyear = a.thisyear ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

   # set double cropping pct, where dc acres > 0
   $listobject->querystring = " update inputyields set dc_pct = ";
   if ($dc_method == 4) {
      # Manually set percent
      $listobject->querystring .= " $dc_value ";
   } else {
      $listobject->querystring .= " CASE WHEN (a.max_dc / total_acres) > 0.5 THEN 0.5 ";
      $listobject->querystring .= " ELSE (a.max_dc / total_acres) ";
      $listobject->querystring .= " END ";
   }
   $listobject->querystring .= " from tmp_dc as a ";
   $listobject->querystring .= " where inputyields.scenarioid = $scenarioid  ";
   $listobject->querystring .= "    and $isubshedcond  ";
   $listobject->querystring .= "    and $iyrcond ";
   $listobject->querystring .= "    and $ilucond ";
   $listobject->querystring .= "    and inputyields.subshedid = a.subshedid ";
   $listobject->querystring .= "    and inputyields.luname = a.luname ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

   # adjust for double cropping, where dc acres > 0
   $listobject->querystring = " update inputyields set ";
   $listobject->querystring .= "    uptake_n = uptake_n / (1.0 - dc_pct), ";
   $listobject->querystring .= "    uptake_p = uptake_p / (1.0 - dc_pct), ";
   $listobject->querystring .= "    mean_needp = mean_needp / (1.0 - dc_pct), ";
   $listobject->querystring .= "    mean_needn = mean_needn / (1.0 - dc_pct), ";
   $listobject->querystring .= "    high_needp = high_needp / (1.0 - dc_pct), ";
   $listobject->querystring .= "    high_needn = high_needn / (1.0 - dc_pct), ";
   $listobject->querystring .= "    targ_needp = targ_needp / (1.0 - dc_pct), ";
   $listobject->querystring .= "    targ_needn = targ_needn / (1.0 - dc_pct), ";
   $listobject->querystring .= "    mean_uptp = mean_uptp / (1.0 - dc_pct), ";
   $listobject->querystring .= "    mean_uptn = mean_uptn / (1.0 - dc_pct), ";
   $listobject->querystring .= "    high_uptp = high_uptp / (1.0 - dc_pct), ";
   $listobject->querystring .= "    high_uptn = high_uptn / (1.0 - dc_pct), ";
   $listobject->querystring .= "    targ_uptp = targ_uptp / (1.0 - dc_pct), ";
   $listobject->querystring .= "    targ_uptn = targ_uptn / (1.0 - dc_pct) ";
   $listobject->querystring .= " where scenarioid = $scenarioid  ";
   $listobject->querystring .= "    and $subshedcond  ";
   $listobject->querystring .= "    and $yrcond ";
   $listobject->querystring .= "    and $lucond ";
   $listobject->querystring .= "    and dc_pct < 1.0 ";
   $listobject->querystring .= "    and dc_pct > 0.0 ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

}


function calculateCropCurves($listobject, $projectid, $scenarioid, $subsheds, $thisyear, $landuse, $debug) {

   # calcuates the aggregate, crop area weighted crop distributions, need, etc. from the crops in the crop table

   # this IS NOT multi-landuse safe. Only one land use can be edited at a time with this script
   # this will use custom weighting for a few known distributions, and then assume a default
   # distribution for others of (uptake_n * acres) for weighting

   # known methods - how to calculate curves - curvetype
   #   1 - crop uptake - area * uptake_n
   #   2 - application of stored - area * uptake_n
   #   3 - direct deposition - area * optn
   #   4 - return to organic (senescence) - area
   #   5 - canopy cover fraction - area
   #   6 - tillage specific residue cover fraction - area

   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear = $thisyear ";
      $ayrcond = " a.thisyear = $thisyear ";
      $byrcond = " b.thisyear = $thisyear ";
      $cyrcond = " c.thisyear = $thisyear ";
   } else {
      $yrcond = ' 1 = 1 ';
      $ayrcond = ' 1 = 1 ';
      $byrcond = ' 1 = 1 ';
      $cyrcond = ' 1 = 1 ';
   }

   if (strlen($landuse) > 0) {
      $lucond = " luname = '$landuse' ";
      $alucond = " a.luname = '$landuse' ";
      $blucond = " b.luname = '$landuse' ";
      $clucond = " c.luname = '$landuse' ";
   } else {
      return;
   }

   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
      # get list of subsheds
      $listobject->querystring = " select subshedid from scen_lrsegs where  ";
      $listobject->querystring .= "   scenarioid = $scenarioid  ";
      $listobject->querystring .= "   and $subshedcond  ";
      $listobject->querystring .= "   and $yrcond  ";
      $listobject->querystring .= "   group by subshedid ";
      if ($debug) { print("<br>$listobject->querystring ;<br>"); }
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
         $csubshedcond = " c.subshedid in ($sublist) ";
         $dsubshedcond = " d.subshedid in ($sublist) ";
      } else {
         $subshedcond = ' 1 = 1 ';
         $asubshedcond = ' 1 = 1 ';
         $bsubshedcond = ' 1 = 1 ';
         $csubshedcond = ' 1 = 1 ';
         $dsubshedcond = ' 1 = 1 ';
      }
   } else {
      $subshedcond = ' 1 = 1 ';
      $asubshedcond = ' 1 = 1 ';
      $bsubshedcond = ' 1 = 1 ';
      $csubshedcond = ' 1 = 1 ';
      $dsubshedcond = ' 1 = 1 ';
   }

   # delete old values
   $listobject->querystring = "  delete from local_apply ";
   $listobject->querystring .= " where scenarioid = $scenarioid  ";
   $listobject->querystring .= "    and $subshedcond  ";
   $listobject->querystring .= "    and $yrcond  ";
   $listobject->querystring .= "    and $lucond  ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

   #  values
   $listobject->querystring = "  select curvetype from scen_crop_curves ";
   $listobject->querystring .= " where scenarioid = $scenarioid  ";
   $listobject->querystring .= "    and $subshedcond ";
   $listobject->querystring .= "    and cropname in ( ";
   $listobject->querystring .= "       select cropname ";
   $listobject->querystring .= "       from scen_crops ";
   $listobject->querystring .= "       where scenarioid = $scenarioid ";
   $listobject->querystring .= "          and $subshedcond";
   $listobject->querystring .= "          and $lucond ";
   $listobject->querystring .= "       group by cropname ";
   $listobject->querystring .= "       ) ";
   $listobject->querystring .= " group by curvetype  ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

   $srcrecs = $listobject->queryrecords;

   foreach ($srcrecs as $thisrec) {

      $curvetype = $thisrec['curvetype'];

      switch($curvetype) {
         case 1:
         #- crop uptake - area * uptake_n
         $wgtcols = ' a.croparea * b.uptake_n ';
         break;

         case 2:
         #- application of stored - area * uptake_n
         $wgtcols = ' a.croparea * b.uptake_n ';
         break;

         case 3:
         #- direct deposition - area * optn
         $wgtcols = ' c.luarea * b.optn ';
         break;

         case 4:
         #- return to organic (senescence) - area
         $wgtcols = ' a.croparea ';
         break;

         case 5:
         #- canopy cover fraction - area
         $wgtcols = ' a.croparea ';
         break;

         case 6:
         #- tillage specific residue cover fraction - area
         $wgtcols = ' a.croparea ';
         break;

         default:
         $wgtcols = ' c.luarea ';
         break;
      }

      # now, area weight the need values for each crop to get a composite need/uptake/etc for the land use
      $listobject->querystring = "  insert into local_apply (scenarioid, projectid, subshedid, luname, thisyear, ";
      $listobject->querystring .= "    curvetype, source_type, need_pct, ";
      $listobject->querystring .= "    jan, feb, mar, apr, may, jun, jul, aug, sep, oct, nov, dec ) ";
      $listobject->querystring .= " select $scenarioid, $projectid, c.subshedid, c.luname, c.thisyear, ";
      $listobject->querystring .= "    d.curvetype, d.source_type, ";
      $listobject->querystring .= "    sum( d.need_pct * $wgtcols ) / sum( $wgtcols ) as need_pct, ";
      $listobject->querystring .= "    sum( d.jan * $wgtcols ) / sum( $wgtcols ) as jan, ";
      $listobject->querystring .= "    sum( d.feb * $wgtcols ) / sum( $wgtcols ) as feb, ";
      $listobject->querystring .= "    sum( d.mar * $wgtcols ) / sum( $wgtcols ) as mar, ";
      $listobject->querystring .= "    sum( d.apr * $wgtcols ) / sum( $wgtcols ) as apr, ";
      $listobject->querystring .= "    sum( d.may * $wgtcols ) / sum( $wgtcols ) as may, ";
      $listobject->querystring .= "    sum( d.jun * $wgtcols ) / sum( $wgtcols ) as jun, ";
      $listobject->querystring .= "    sum( d.jul * $wgtcols ) / sum( $wgtcols ) as jul, ";
      $listobject->querystring .= "    sum( d.aug * $wgtcols ) / sum( $wgtcols ) as aug, ";
      $listobject->querystring .= "    sum( d.sep * $wgtcols ) / sum( $wgtcols ) as sep, ";
      $listobject->querystring .= "    sum( d.oct * $wgtcols ) / sum( $wgtcols ) as oct, ";
      $listobject->querystring .= "    sum( d.nov * $wgtcols ) / sum( $wgtcols ) as nov, ";
      $listobject->querystring .= "    sum( d.dec * $wgtcols ) / sum( $wgtcols ) as dec ";
      $listobject->querystring .= " from scen_crops as a, inputyields as b, ";
      if ( $curvetype == 6 ) {
         # this is residue cover, tillage specific, match to tillage entry in land use
         $listobject->querystring .= " landuses as e, tillage as f, ";
      }
      $listobject->querystring .= "    scen_subsheds as c, scen_crop_curves as d ";
      $listobject->querystring .= " where a.scenarioid = $scenarioid ";
      $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
      $listobject->querystring .= "    and c.scenarioid = $scenarioid ";
      $listobject->querystring .= "    and d.scenarioid = $scenarioid ";
      if ( $curvetype == 6 ) {
         # this is residue cover, tillage specific, match to tillage entry in land use
         $listobject->querystring .= " and e.projectid = $projectid ";
         $listobject->querystring .= " and e.tillage = f.tid ";
         # matches on landuse name
         $listobject->querystring .= " and e.hspflu = c.luname ";
         # matches on tillage name
         $listobject->querystring .= " and d.source_type = f.shortname ";
      }
      $listobject->querystring .= "    and $asubshedcond  ";
      $listobject->querystring .= "    and $bsubshedcond  ";
      $listobject->querystring .= "    and $csubshedcond  ";
      $listobject->querystring .= "    and $dsubshedcond  ";
      $listobject->querystring .= "    and $ayrcond ";
      $listobject->querystring .= "    and $byrcond ";
      $listobject->querystring .= "    and $cyrcond ";
      $listobject->querystring .= "    and $alucond ";
      $listobject->querystring .= "    and $blucond ";
      $listobject->querystring .= "    and $clucond ";
      $listobject->querystring .= "    and ( $wgtcols > 0 ) ";
      $listobject->querystring .= "    and d.curvetype = $curvetype ";
      $listobject->querystring .= "    and a.subshedid = b.subshedid ";
      $listobject->querystring .= "    and a.subshedid = c.subshedid ";
      $listobject->querystring .= "    and a.subshedid = d.subshedid ";
      $listobject->querystring .= "    and a.cropname = d.cropname ";
      $listobject->querystring .= " group by c.subshedid, c.luname, c.thisyear,  ";
      $listobject->querystring .= "    d.curvetype, d.source_type ";
      if ($debug) { print("<br>$listobject->querystring ;<br>"); }
      $listobject->performQuery();
   }

}


?>