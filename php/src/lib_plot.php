<?php

#$glibdir = "/work/htdocs/jpgraph/";
#$goutdir = "/work/htdocs/tmp/";
#$goutpath = "/tmp";
include "$glibdir/jpgraph.php";
include "$glibdir/jpgraph_line.php";
include "$glibdir/jpgraph_bar.php";
include "$glibdir/jpgraph_scatter.php";
include "$glibdir/jpgraph_regstat.php";
include ("$glibdir/jpgraph_error.php");
include ("$glibdir/jpgraph_pie.php");
include ("$glibdir/jpgraph_pie3d.php");


/* *************************************************** */
/* ***********     Plotting Functions    ************ */
/* *************************************************** */

function landUsePieChart($listobject, $thisyear, $subsheds, $scenarioid, $explodelu, $outdir, $outurl, $debug) {

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
   } else {
      $subshedcond = ' 1 = 1 ';
   }

   $listobject->querystring = "select luname, sum(luarea) as luarea ";
   $listobject->querystring .= " from scen_lrsegs ";
   $listobject->querystring .= " where $subshedcond ";
   $listobject->querystring .= " and thisyear = $thisyear ";
   $listobject->querystring .= " and scenarioid = $scenarioid ";
   $listobject->querystring .= " group by luname ";
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $listobject->performQuery();

   $slicerecs = $listobject->queryrecords;
   $i = 1;
   $exsl = 0;
   $data = array();
   $labels = array();

   foreach ($slicerecs as $thisslice) {
      $luarea = $thisslice['luarea'];
      $luname = $thisslice['luname'];
      array_push($data, $thisslice['luarea']);
      array_push($labels, $thisslice['luname']);
      if ($thisslice['luname'] == $explodelu) {
         $exsl = $i;
      }
      if ($debug) { print("$luname = $luarea <br> "); }
   }

   $graph = new PieGraph(330,200,"auto");
   $graph->SetShadow();

   $graph->title->Set("Land Use");
   $graph->title->SetFont(FF_FONT1,FS_BOLD);

   $p1 = new PiePlot3D($data);
   if ($exsl > 0) {
      $p1->ExplodeSlice($exsl);
   }
   $p1->SetCenter(0.45);
   $p1->SetLegends($labels);

   $graph->Add($p1);
   $gfname = "lupie" . ".$luname." . $thisyear . md5(uniqid(time()));
   $graph->Stroke("$outdir/$gfname.gif");
   $outinfo = "$outurl/$gfname.gif";
   if ($debug) { print("$outinf"); }

   return $outinfo;
}

function landUseTypePieChart($listobject, $projectid, $thisyear, $subsheds, $scenarioid, $explodelu, $outdir, $outurl, $debug) {

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
      $bsubshedcond = " b.lrseg in ($sslist) ";
   } else {
      $subshedcond = ' 1 = 1 ';
      $bsubshedcond = ' 1 = 1 ';
   }

   $listobject->querystring = "select a.lutypename, sum(b.luarea) as luarea ";
   $listobject->querystring .= " from major_lutype as a, scen_lrsegs as b, landuses as c ";
   $listobject->querystring .= " where $bsubshedcond ";
   $listobject->querystring .= "    and b.thisyear = $thisyear ";
   $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and c.projectid = $projectid ";
   $listobject->querystring .= "    AND b.luname = c.hspflu ";
   $listobject->querystring .= "    AND a.lutype = c.major_lutype ";
   $listobject->querystring .= " group by a.lutypename ";
   $listobject->querystring .= " order by a.lutypename ";
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $listobject->performQuery();

   $slicerecs = $listobject->queryrecords;
   $i = 1;
   $exsl = 0;
   $data = array();
   $labels = array();

   foreach ($slicerecs as $thisslice) {
      $luarea = $thisslice['luarea'];
      $luname = $thisslice['lutypename'];
      array_push($data, $luarea);
      array_push($labels, $luname);
      if ($thisslice['luname'] == $explodelu) {
         $exsl = $i;
      }
      if ($debug) { print("$luname = $luarea <br> "); }
   }

   $graph = new PieGraph(430,200,"auto");
   $graph->SetShadow();

   $graph->title->Set("Land Use Categories");
   $graph->title->SetFont(FF_FONT1,FS_BOLD);

   if (count($data) > 0) {
      $p1 = new PiePlot3D($data);
      $p1->SetCenter(0.35);
      $p1->SetLegends($labels);
   #   if ($exsl > 0) {
   #      $p1->ExplodeSlice($exsl);
   #   }

      $graph->Add($p1);
      $gfname = "lupie" . ".$luname." . $thisyear . md5(uniqid(time()));
      $graph->Stroke("$outdir/$gfname.gif");
   }
   $outinfo['data'] = $slicerecs;
   $outinfo['url'] = "$outurl/$gfname.gif";
   if ($debug) { print_r("$outinf"); }

   return $outinfo;
}

function showApplicationUptakeCurve($listobject,$goutdir, $goutpath, $inyears, $subsheds, $luname, $scenarioid, $pt, $debug, $lr = 0) {

   $ssclause = '';
   # check to see if we want subshed (counties) or lrsegs (land/river segs)
   # later, we should deprecate this
   if ($lr) {
      $sscol = 'lrseg';
      $landtable = 'scen_lrsegs';
      $apptable = 'gview_lrseg_inputs';
   } else {
      $sscol = 'subshedid';
      $landtable = 'scen_subsheds';
      $apptable = 'gview_model_inputs';
   }

   if (strlen($luname) > 0) {
      $llist = "'" . join("','", split(",", $luname)) . "'";
      $lucond = " luname in ($llist) ";
      $alucond = " a.luname in ($llist) ";
      $blucond = " b.luname in ($llist) ";
   } else {
      $lucond = ' (1 = 1) ';
      $alucond = ' (1 = 1) ';
      $blucond = ' (1 = 1) ';
   }


   if (strlen($subsheds) > 0) {
      $slist = join("','", split(',', $subsheds));
      $ssclause = "and $sscol in ('$slist')";
      # changed 6-28 RWB to accomodate lrseg and subshedid type queries
    #  $stclause = "and a.stcofips in ('$slist')";
      $stclause = "and b.$sscol in ('$slist')";
   }

   $theseyears = split(',', $inyears);

   $allappxdata = array();
   $allappydata = array();
   $allupxdata = array();
   $allupydata = array();
   $allxlabels = array();
   $lx = 0;

   $split = $listobject->startsplit();

   foreach ($theseyears as $thisyear) {
      # get uptakes
      $listobject->querystring = "  select sum(a.jan * b.luarea)/sum(b.luarea) as jan,  ";
      $listobject->querystring .= " sum(a.feb * b.luarea)/sum(b.luarea) as feb,  ";
      $listobject->querystring .= " sum(a.mar * b.luarea)/sum(b.luarea) as mar,  ";
      $listobject->querystring .= " sum(a.apr * b.luarea)/sum(b.luarea) as apr,  ";
      $listobject->querystring .= " sum(a.may * b.luarea)/sum(b.luarea) as may,  ";
      $listobject->querystring .= " sum(a.jun * b.luarea)/sum(b.luarea) as jun,  ";
      $listobject->querystring .= " sum(a.jul * b.luarea)/sum(b.luarea) as jul,  ";
      $listobject->querystring .= " sum(a.aug * b.luarea)/sum(b.luarea) as aug,  ";
      $listobject->querystring .= " sum(a.sep * b.luarea)/sum(b.luarea) as sep,  ";
      $listobject->querystring .= " sum(a.oct * b.luarea)/sum(b.luarea) as oct,  ";
      $listobject->querystring .= " sum(a.nov * b.luarea)/sum(b.luarea) as nov,  ";
      $listobject->querystring .= " sum(a.dec * b.luarea)/sum(b.luarea) as dec ";
      $listobject->querystring .= " from cb_uptake as a, $landtable as b ";
      $listobject->querystring .= " where a.scenarioid = $scenarioid ";
      $listobject->querystring .= "    and a.thisyear = $thisyear  ";
      # START - modified to aggregate multiple land uses
      #$listobject->querystring .= "    and a.luname = '$luname' ";
      #$listobject->querystring .= "    and b.luname = '$luname' ";
      $listobject->querystring .= "    and $alucond ";
      $listobject->querystring .= "    and $blucond ";
      # END - modification
      $listobject->querystring .= "    and b.luname = a.luname ";
      $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
      $listobject->querystring .= "    and b.thisyear = $thisyear  ";
      $listobject->querystring .= "    and b.luarea > 0 ";
      $listobject->querystring .= "    and b.subshedid = a.stcofips ";
      $listobject->querystring .= "    $stclause ";
      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();
      $split = $listobject->startsplit();
      if ($debug) { print("Query Time: $split<br>"); }

      $xlabels = array('jan','feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec');
      $upydata = array_values($listobject->queryrecords[0]);

      # get applications
      $listobject->querystring = "  select sum(jan * luarea/(annualapplied + legume))/sum(luarea) as jan,  ";
      $listobject->querystring .= " sum(feb * luarea/(annualapplied + legume))/sum(luarea) as feb,  ";
      $listobject->querystring .= " sum(mar * luarea/(annualapplied + legume))/sum(luarea) as mar,  ";
      $listobject->querystring .= " sum(apr * luarea/(annualapplied + legume))/sum(luarea) as apr,  ";
      $listobject->querystring .= " sum(may * luarea/(annualapplied + legume))/sum(luarea) as may,  ";
      $listobject->querystring .= " sum(jun * luarea/(annualapplied + legume))/sum(luarea) as jun,  ";
      $listobject->querystring .= " sum(jul * luarea/(annualapplied + legume))/sum(luarea) as jul,  ";
      $listobject->querystring .= " sum(aug * luarea/(annualapplied + legume))/sum(luarea) as aug,  ";
      $listobject->querystring .= " sum(sep * luarea/(annualapplied + legume))/sum(luarea) as sep,  ";
      $listobject->querystring .= " sum(oct * luarea/(annualapplied + legume))/sum(luarea) as oct,  ";
      $listobject->querystring .= " sum(nov * luarea/(annualapplied + legume))/sum(luarea) as nov,  ";
      $listobject->querystring .= " sum(dec * luarea/(annualapplied + legume))/sum(luarea) as dec ";
      $listobject->querystring .= " from $apptable ";
      $listobject->querystring .= " where scenarioid = $scenarioid ";
      $listobject->querystring .= "    and thisyear = $thisyear  ";
      # START - modified to aggregate multiple land uses
      #$listobject->querystring .= "    and luname = '$luname' ";
      $listobject->querystring .= "    and $lucond ";
      # END - modification
      $listobject->querystring .= "    and luarea > 0 ";
      $listobject->querystring .= "    and pt = $pt ";
      $listobject->querystring .= "    and (annualapplied + legume) > 0 ";
      $listobject->querystring .= "    $ssclause ";
      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();
      $split = $listobject->startsplit();
      if ($debug) { print("Query Time: $split<br>"); }

      $appxdata = array(1+$lx,2+$lx,3+$lx,4+$lx,5+$lx,6+$lx,7+$lx,8+$lx,9+$lx,10+$lx,11+$lx,12+$lx);
      $lx += 12;
      $upxdata = $appxdata;

      $appydata = array_values($listobject->queryrecords[0]);

      $allappxdata = array_merge($allappxdata, $appxdata);
      $allappydata = array_merge($allappydata, $appydata);
      $allupxdata = array_merge($allupxdata, $upxdata);
      $allupydata = array_merge($allupydata, $upydata);
      $allxlabels = array_merge($allxlabels, $xlabels);
   }

   // Get the interpolated values by creating
   // a new Spline object.
   $upspline = new Spline($allupxdata,$allupydata);
   $appspline = new Spline($allappxdata,$allappydata);

   // For the new data set we want 40 points to
   // get a smooth curve.
   list($upnewx,$upnewy) = $upspline->Get(50);
   list($appnewx,$appnewy) = $appspline->Get(50);

   // Create the graph
   $wid = 300 + (count($theseyears) - 1) * 100;
   $g = new Graph($wid,200,"auto");
  # print("Setting Scale<br>");
   $g->SetScale("lin");
  # print("Scale Set<br>");

   $g->SetMargin(30,20,40,30);
   $g->title->Set("Application Versus Uptake Curves");
   $g->title->SetColor('black');
   #$g->title->SetFont(FF_ARIAL,FS_NORMAL,12);
   $g->subtitle->Set('(Application in Green)');
   $g->subtitle->SetColor('darkred');
   $g->SetMarginColor('lightblue');
   $g->xaxis->SetTickLabels($allxlabels);

   //$g->img->SetAntiAliasing();

   // We need a linlin scale since we provide both
   // x and y coordinates for the data points.
   #$g->SetScale('linlin');

   // We want 1 decimal for the X-label
   #$g->xaxis->SetLabelFormat('%1.1f');

   // We use a scatterplot to illustrate the original
   // contro points.
   $upsplot = new ScatterPlot($allupydata,$allupxdata);
   $appsplot = new ScatterPlot($allappydata,$allappxdata);

   //
   $upsplot->mark->SetFillColor('red@0.3');
   $upsplot->mark->SetColor('red@0.5');
   $appsplot->mark->SetFillColor('blue@0.3');
   $appsplot->mark->SetColor('blue@0.5');

   // And a line plot to stroke the smooth curve we got
   // from the original control points
   $uplplot = new LinePlot($upnewy,$upnewx);
   $applplot = new LinePlot($appnewy,$appnewx);
   $uplplot->SetColor('navy');
   $applplot->SetColor('green');

   // Add the plots to the graph and stroke
   if (count($uplplot > 0)) {
      $g->Add($uplplot);
      $g->Add($applplot);
   }
   if (count($upsplot > 0)) {
      $g->Add($upsplot);
      $g->Add($appsplot);
   }
   $g->SetScale("linlin",0,1);

   $gfname = "uptk" . $luname . $thisyear . md5(uniqid(time()));
   $g->Stroke("$goutdir/$gfname.gif");

   if ( (count($appydata) == 0) and (count($upydata) == 0) ) {
      $outinfo = '-1';
   } else {
      $outinfo = "$goutpath/$gfname.gif";
   }

   return $outinfo;

}

function showModeledObservedUptakeCurve($cropobject, $listobject, $goutdir, $goutpath, $subsheds, $luname, $awyield, $showeof, $scenarioid, $modelscen, $pt, $fyear, $lyear, $debug, $lr = 0, $fixpct = 1.0) {

   # check to see if we want subshed (counties) or lrsegs (land/river segs)
   # later, we should deprecate this
   if ($lr) {
      $sscol = 'b.lrseg';
      $stcol = 'b.lrseg';
      $bstcol = 'b.lrseg';
      $landtable = 'scen_lrsegs';
      $apptable = 'gview_lrseg_inputs';
   } else {
      $sscol = 'a.subshedid';
      $stcol = 'a.stcofips';
      $bstcol = 'b.subshedid';
      $landtable = 'scen_subsheds';
      $apptable = 'gview_model_inputs';
   }

   # $fixpct = whether or not to manufacture the observed as a fixed percent of the applied
   # this is used for hay and pasture and any other for which there is no observed data.

#   print ("step 1<br>");

   if (strlen($luname) > 0) {
      $llist = "'" . join("','", split(",", $luname)) . "'";
      $alucond = " a.luname in ($llist) ";
      $blucond = " b.luname in ($llist) ";
      $clucond = " c.luname in ($llist) ";
   } else {
      $alucond = ' (1 = 1) ';
      $blucond = ' (1 = 1) ';
      $clucond = ' (1 = 1) ';
   }

   $ssclause = '';
   $assclause = '';
   $stclause = '';
   $stonlyclause = '';

   if (strlen($subsheds) > 0) {
      $slist = join("','", split(',', $subsheds));
      $ssclause = "and $sscol in ('$slist')";
      $assclause = "and $sscol in ('$slist')";
      $stclause = "and $stcol in ('$slist')";
      $bstclause = "and $bstcol in ('$slist')";
      $stonlyclause = "and stcofips in ('$slist')";
   } else {
      $ssclause = " ( 1 = 1 ) ";
      $assclause = " ( 1 = 1 ) ";
      $stclause = " ( 1 = 1 ) ";
      $bstclause = " ( 1 = 1 ) ";
      $stonlyclause = " ( 1 = 1 ) ";
   }

   switch ($pt) {
      case 1:
      $nutcol = 'uptake_n';
      break;

      case 2:
      $nutcol = 'uptake_p';
      break;

      default:
      $nutcol = 'uptake_n';
      break;
   }

   $scaleobs = 1.3;

   $split = $listobject->startsplit();

   # get modeled uptakes
   $listobject->querystring = "  select a.thisyear, ";
   $listobject->querystring .= " sum(b.luarea * a.annual_uptake)/sum(b.luarea) as wgtd_uptk, ";
   $listobject->querystring .= " avg(a.annual_uptake) as mean_uptk, ";
   $listobject->querystring .= " min(a.annual_uptake) as min_uptk, max(a.annual_uptake) as max_uptk, ";
   $listobject->querystring .= " sum(b.luarea * a.annual_eof)/sum(b.luarea) as wgtd_eof, ";
   $listobject->querystring .= " sum(b.luarea * a.annual_eof) as total_eof ";
   $listobject->querystring .= " from scen_model_uptake as a, scen_lrsegs as b, pollutanttype as c ";
   $listobject->querystring .= " where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and b.scenarioid = $scenarioid  ";
   $listobject->querystring .= "    and a.model_scen = '$modelscen' ";
   # modified to allow aggregated land uses
   #$listobject->querystring .= "    and a.luname = '$luname' ";
   #$listobject->querystring .= "    and b.luname = '$luname' ";
   $listobject->querystring .= "    and $alucond ";
   $listobject->querystring .= "    and $blucond ";
   $listobject->querystring .= "    and a.luname = b.luname";
   # added to allow aggregated land uses
   $listobject->querystring .= "    and a.landseg = b.landseg ";
   $listobject->querystring .= "    and a.thisyear = b.thisyear ";
   $listobject->querystring .= "    and b.luarea > 0 ";
   $listobject->querystring .= "    and a.constit = c.shortname ";
   $listobject->querystring .= "    and c.typeid = $pt ";
   $listobject->querystring .= "    $assclause ";
   $listobject->querystring .= " group by a.thisyear ";
   $listobject->querystring .= " order by a.thisyear ";
   $listobject->performQuery();
   if ($debug) { print("$listobject->querystring ;<br>"); }
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }

   $modxdata = array();
   $modmeanydata = array();
   $modminydata = array();
   $modmaxydata = array();
   $modeofdata = array();
   $totaleofdata = array();
   $annualeof = array();
   $errdatay = array();
   $listformatted = array();
   $maxty = 0;
   $minty = -1;
   $scalemax = 0;
   foreach ($listobject->queryrecords as $thiscroprec) {
      $ty = $thiscroprec['thisyear'];
      if ($ty > $maxty) { $maxty = $ty; }
      if ( ($ty < $minty) or ($minty == -1) ) { $minty = $ty; }
      array_push($modxdata, $thiscroprec['thisyear']);
      array_push($modmeanydata, $thiscroprec['wgtd_uptk']);
      array_push($modminydata, $thiscroprec['min_uptk']);
      array_push($modeofdata, $thiscroprec['wgtd_eof']);
      $annualeof[$ty] = $thiscroprec['wgtd_eof'];
      $totaleofdata[$ty] = $thiscroprec['total_eof'];
      $listformatted[$ty]['thisyear'] = $ty;
      $listformatted[$ty]['moduptake'] = $thiscroprec['wgtd_uptk'];
      $listformatted[$ty]['modeof'] = $thiscroprec['wgtd_eof'];
      #$mm = $thiscroprec['max_uptk'];
      $mm = $thiscroprec['wgtd_uptk'];
      array_push($modmaxydata, $mm);
      if ($mm > $scalemax) {
         $scalemax = $mm;
      }
      if ($thiscroprec['wgtd_eof'] > $scalemax) {
         $scalemax = $thiscroprec['wgtd_eof'];
      }
      array_push($errdatay, $thiscroprec['min_uptk']);
      array_push($errdatay, $thiscroprec['max_uptk']);
      # now, if there is an observation for this model year, insert it,
      # otherwise insert a blank since their must be an equal number of points
      # in each scatter plot
    #  array_push($obydata, $thiscroprec['max_uptk']);
   }

   # get reported uptakes
   if ($debug) {
      print("Yield Switch: $awyield <br>");
   }
   switch ($awyield) {
      case 1:
      # we want to area weight the yield info
      $listobject->querystring = "  select a.thisyear,  $scaleobs * sum(b.luarea * a.agc_uptake)/sum(b.luarea) as agc_uptake ";
      $listobject->querystring .= " from nass_reported_ylds as a, scen_lrsegs as b ";
      $listobject->querystring .= " where b.scenarioid = $scenarioid  ";
      # modified to allow aggregated land uses
      #$listobject->querystring .= "    and a.luname = '$luname' ";
      #$listobject->querystring .= "    and b.luname = '$luname' ";
      $listobject->querystring .= "    and $alucond ";
      $listobject->querystring .= "    and $blucond ";
      $listobject->querystring .= "    and a.luname = b.luname";
      # added to allow aggregated land uses
      $listobject->querystring .= "    and b.thisyear = a.thisyear ";
      $listobject->querystring .= "    and a.stcofips = b.subshedid ";
      $listobject->querystring .= "    and b.luarea > 0 ";
      $listobject->querystring .= "    and a.pollutantid = $pt ";
      $listobject->querystring .= "    $stclause ";
      $listobject->querystring .= "    and a.thisyear >= $minty ";
      $listobject->querystring .= "    and a.thisyear <= $maxty ";
      $listobject->querystring .= " group by a.thisyear ";
      $listobject->querystring .= " order by a.thisyear ";
      break;

      case 2:
      if ($debug) {  print("Scaling Uptake based on percent of maximum <br>"); }
      # we want to area weight the yield info based on yield percent,
      # to adjust for years with missing crops
      $listobject->querystring = "  select a.thisyear, $scaleobs * sum(b.luarea * a.yld_pct * c.$nutcol)/sum(b.luarea) as agc_uptake ";
      $listobject->querystring .= " from nass_reported_ylds as a, scen_lrsegs as b, scen_subsheds as c ";
      $listobject->querystring .= " where b.scenarioid = $scenarioid  ";
      $listobject->querystring .= "    and b.thisyear >= $minty ";
      $listobject->querystring .= "    and b.thisyear <= $maxty ";
      $listobject->querystring .= "    $stclause ";
      $listobject->querystring .= "    and c.scenarioid = $scenarioid ";
      # START - modified to allow aggregated land uses
      #$listobject->querystring .= "    and a.luname = '$luname' ";
      #$listobject->querystring .= "    and b.luname = '$luname' ";
      #$listobject->querystring .= "    and c.luname = '$luname' ";
      $listobject->querystring .= "    and $alucond ";
      $listobject->querystring .= "    and $blucond ";
      $listobject->querystring .= "    and $clucond ";
      $listobject->querystring .= "    and a.luname = b.luname";
      $listobject->querystring .= "    and a.luname = c.luname";
      # END - added to allow aggregated land uses
      $listobject->querystring .= "    and b.thisyear = a.thisyear ";
      $listobject->querystring .= "    and b.thisyear = c.thisyear ";
      $listobject->querystring .= "    and a.stcofips = b.subshedid ";
      $listobject->querystring .= "    and a.stcofips = c.subshedid ";
      $listobject->querystring .= "    and b.luarea > 0 ";
      $listobject->querystring .= "    and a.pollutantid = $pt ";
      $listobject->querystring .= "    and a.thisyear >= $minty ";
      $listobject->querystring .= "    and a.thisyear <= $maxty ";
      $listobject->querystring .= " group by a.thisyear ";
      $listobject->querystring .= " order by a.thisyear ";
      break;

      case 3:
      if ($debug) {  print("Scaling Uptake based on percent of maximum <br>"); }
      # we want to area weight the yield info based on yield percent,
      # to adjust for years with missing crops
      $listobject->querystring = "  select b.thisyear,  $scaleobs * ";
      $listobject->querystring .= " sum(b.luarea * $fixpct * c.annualapplied)/sum(b.luarea) as agc_uptake ";
      $listobject->querystring .= " from $landtable as b, scen_monperunitarea as c ";
      $listobject->querystring .= " where b.scenarioid = $scenarioid ";
      $listobject->querystring .= "    and c.scenarioid = $scenarioid ";
      $listobject->querystring .= "    and $blucond ";
      $listobject->querystring .= "    and $clucond ";
      $listobject->querystring .= "    and b.luname = c.luname ";
      $listobject->querystring .= "    and b.thisyear = c.thisyear ";
      $listobject->querystring .= "    and b.subshedid = c.subshedid ";
      $listobject->querystring .= "    and b.luarea > 0 ";
      $listobject->querystring .= "    and c.pollutanttype = $pt ";
      $listobject->querystring .= "    $bstclause ";
      $listobject->querystring .= "    and b.thisyear >= $minty ";
      $listobject->querystring .= "    and b.thisyear <= $maxty ";
      $listobject->querystring .= " group by b.thisyear ";
      $listobject->querystring .= " order by b.thisyear ";
      break;

      default:
      $listobject->querystring = "  select thisyear, $scaleobs * agc_uptake as agc_uptake ";
      $listobject->querystring .= " from nass_reported_ylds as a ";
      $listobject->querystring .= " where $alucond ";
      $listobject->querystring .= "    and a.pollutantid = $pt ";
      $listobject->querystring .= "    $stonlyclause ";
      $listobject->querystring .= " order by a.thisyear ";
   }

   if ($debug) { print("$listobject->querystring ;<br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }

   $xlabels = array();
   $obsdata = array();
   $obxdata = array();
   $obydata = array();

   foreach ($listobject->queryrecords as $thiscroprec) {
      $obsdata[$thiscroprec['thisyear']] = $thiscroprec['agc_uptake'];
      array_push($obxdata, $thiscroprec['thisyear']);
      array_push($obydata, $thiscroprec['agc_uptake']);
      $listformatted[$thiscroprec['thisyear']]['obsuptake'] = $thiscroprec['agc_uptake'];
      if ($thiscroprec['agc_uptake'] > $scalemax) {
         $scalemax = $thiscroprec['agc_uptake'];
      }
   }

   $scalemax = $scalemax * 1.2;

 #  print_r($modminydata);
 #  print_r($modxdata);
 #  print_r($obydata);

#   print ("step 2<br>");
   // Create the graph
   $g = new Graph(600,400,"auto");
   $g->SetScale("lin");

   $g->SetMargin(30,20,40,30);
   $g->title->Set("Observed Versus Modeled Uptake");
   $g->title->SetColor('black');
   #$g->title->SetFont(FF_ARIAL,FS_NORMAL,12);
   if ($showeof) {
      $g->subtitle->Set('(Modeled in Blue, EOF in brown - values on Right Axis)');
   } else {
      $g->subtitle->Set('(Modeled Uptake in Blue');
   }
   $g->subtitle->SetColor('darkred');
   $g->SetMarginColor('lightblue');


   if (count($obydata) > 0) {
      $obsplot = new ScatterPlot($obydata,$obxdata);
      $obsplot->mark->SetFillColor('red@0.3');
      $obsplot->mark->SetColor('red@0.5');
      $g->Add($obsplot);
   } else {
     # print("No Monitored Data<br>");
   }

  # print_r($modmeanydata);
 #  print_r($modxdata);
   $gfname = "modeuptk" . $luname . $thisyear . md5(uniqid(time()));

   if ( (count($modmeanydata) > 0) and (count($modxdata) > 0) ) {
 #     print("Has Modeled Data<br>");
   #   print ("step 3<br>");
      $meanplot = new ScatterPlot($modmeanydata,$modxdata);
      $eofplot = new ScatterPlot($modeofdata,$modxdata);
      $meanplot->mark->SetFillColor('blue@0.3');
      $meanplot->mark->SetColor('blue@0.5');
      $eofplot->mark->SetFillColor('brown@0.3');
      $eofplot->mark->SetColor('brown@0.5');
      $errplot=new ErrorPlot($errdatay, $modxdata);
      $g->Add($meanplot);
      if ($showextremes) {
         $g->Add($errplot);
      }
      # put eof on separate axis?
      if ($showeof) {
         #$g->AddY2($eofplot);
         #$g->SetY2Scale("lin");
         $g->Add($eofplot);
      }
      $g->SetScale("linlin",0,$scalemax);
      $g->Stroke("$goutdir/$gfname.gif");
   }

   $output = array();
   $output['imgpath'] = "$goutpath/$gfname.gif";
   $output['wgtd_eof'] = $annualeof;
   $output['total_eof'] = $totaleofdata;
   $output['listformat'] = $listformatted;
   return $output;
}


###############################################################################################
##############################        BMP Plotting Routines       #############################
###############################################################################################

function showBMPAcreage($listobject, $goutdir, $gouturl, $bmpname, $subsheds, $thisyear, $scenarioid, $projectid, $debug) {

   $conv = 1; # true or false to convert BMP input unts to area
   $bmprecs = getOneLRBmps($listobject, $bmpname, $subsheds, $thisyear, $scenarioid, $projectid, $conv, $debug);

   $bmpxdata = array();
   $bmpeligdata = array();
   $bmpapparea = array();
   $bmpmaxydata = array();
   $bmppctdata = array();

   $maxty = 0;
   $minty = -1;
   $scalemax = 0;
   foreach ($bmprecs as $thisbmprec) {
      $ty = $thiscroprec['thisyear'];
      if ($ty > $maxty) { $maxty = $ty; }
      if ( ($ty < $minty) or ($minty == -1) ) { $minty = $ty; }
      $ea = $thisbmprec['eligarea'];
      $appa = $thisbmprec['bmparea'];
      array_push($bmpxdata, $thisbmprec['thisyear']);
      array_push($bmpeligdata, $ea);
      array_push($bmpapparea, $appa);

      $mm = $thisbmprec['eligarea'];
      array_push($bmpmaxydata, $mm);
      if ($mm > $scalemax) {
         $scalemax = $mm;
      }

      if ($ea > 0) {
         $thispct = $appa / $ea;
      } else {
         $thispct = 0;
      }
      array_push($bmppctdata, $thispct);

   }

#   print_r($bmpxdata);
#   print_r($bmpeligdata);
#   print_r($bmpapparea);

#   print ("step 2<br>");
   // Create the graph.
   $graph = new Graph(400,200,"auto");
   $graph->SetScale("intlin");

   $graph->img->SetMargin(60,100,20,40);
   $graph->SetShadow();

   // Create the bar plot
   $eplot = new BarPlot($bmpeligdata);
   $eplot->SetFillColor("orange");
   $bplot = new BarPlot($bmpapparea);
   $bplot->SetFillColor("blue");
   $bplot->SetLegend('Applied');
   $eplot->SetLegend('Eligible');

   // Add the plots to the graph
   $graph->Add($eplot);
   $graph->Add($bplot);

   $graph->title->Set("Annual BMP Data for $bmpname");
   $graph->xaxis->title->Set("Year");
   $graph->yaxis->title->Set('');

   $graph->title->SetFont(FF_FONT1,FS_BOLD);
   $graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
   $graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

   $graph->xaxis->SetTickLabels($bmpxdata);

   // Display the graph
   $gfname = "bmp" . ".$bmpname." . md5(uniqid(time())) . '.png';
   $gpath = "$goutdir/$gfname";
   $gurl = "$gouturl/$gfname";
   $graph->Stroke($gpath);
   return $gurl;
}


function showBMPTypeAcreage($listobject, $goutdir, $gouturl, $typeid, $subsheds, $thisyear, $scenarioid, $projectid, $debug) {

   $bmprecs = getOneLRBmpType($listobject, $typeid, $subsheds, $thisyear, $scenarioid, $projectid, $debug);
   if ($debug) { print_r($bmprecs); }

   if (count($bmprecs) > 0) {
      $bmpxdata = array();
      $bmpeligdata = array();
      $bmpapparea = array();
      $bmpmaxydata = array();
      $bmppctdata = array();
   } else {
      $bmpxdata = array(0);
      $bmpeligdata = array(0);
      $bmpapparea = array(0);
      $bmpmaxydata = array(0);
      $bmppctdata = array(0);
   }


   $maxty = 0;
   $minty = -1;
   $scalemax = 0;
   foreach ($bmprecs as $thisbmprec) {
      $ty = $thiscroprec['thisyear'];
      if ($ty > $maxty) { $maxty = $ty; }
      if ( ($ty < $minty) or ($minty == -1) ) { $minty = $ty; }
      $ea = $thisbmprec['eligarea'];
      $appa = $thisbmprec['bmparea'];
      $bmpname = $thisbmprec['bmp_name'];
      array_push($bmpxdata, $thisbmprec['thisyear']);
      array_push($bmpeligdata, $ea);
      array_push($bmpapparea, $appa);

      $mm = $thisbmprec['eligarea'];
      array_push($bmpmaxydata, $mm);
      if ($mm > $scalemax) {
         $scalemax = $mm;
      }

      if ($ea > 0) {
         $thispct = $appa / $ea;
      } else {
         $thispct = 0;
      }
      array_push($bmppctdata, $thispct);

   }

#   print_r($bmpxdata);
#   print_r($bmpeligdata);
#   print_r($bmpapparea);

#   print ("step 2<br>");
   // Create the graph.
   $graph = new Graph(400,200,"auto");
   $graph->SetScale("intlin");

   $graph->img->SetMargin(60,100,20,40);
   $graph->SetShadow();

   // Create the bar plot
   $eplot = new BarPlot($bmpeligdata);
   $eplot->SetFillColor("orange");
   $bplot = new BarPlot($bmpapparea);
   $bplot->SetFillColor("blue");
   $bplot->SetLegend('Applied');
   $eplot->SetLegend('Eligible');

   // Add the plots to the graph
   $graph->Add($eplot);
   $graph->Add($bplot);

   $graph->title->Set("Annual BMP Data for $bmpname");
   $graph->xaxis->title->Set("Year");
   $graph->yaxis->title->Set('');

   $graph->title->SetFont(FF_FONT1,FS_BOLD);
   $graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
   $graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

   $graph->xaxis->SetTickLabels($bmpxdata);

   // Display the graph
   $gfname = "bmp" . ".$bmpname." . md5(uniqid(time())) . '.png';
   $gpath = "$goutdir/$gfname";
   $gurl = "$gouturl/$gfname";
   $graph->Stroke($gpath);
   return $gurl;
}


function showGenericPie($listobject, $goutdir, $gouturl, $graphrecs, $xcol, $ycol, $title, $width, $height, $debug) {

   if (count($graphrecs) > 0) {
      $xdata = array();
      $ydata = array();
   } else {
      $xdata = array(0);
      $ydata = array(0);
   }

   $scalemax = 0;
   foreach ($graphrecs as $thisrec) {
      $xval = $thisrec[$xcol];
      $yval = $thisrec[$ycol];
      array_push($xdata, $xval);
      array_push($ydata, $yval);

      if ($yval > $scalemax) {
         $scalemax = $mm;
      }

   }


   $graph = new PieGraph($width, $height,"auto");
   $graph->SetShadow();

   $graph->title->Set("$title");
   $graph->title->SetFont(FF_FONT1,FS_BOLD);

   $p1 = new PiePlot3D($ydata);
   $p1->SetCenter(0.35);
   $p1->SetLegends($xdata);
#   if ($exsl > 0) {
#      $p1->ExplodeSlice($exsl);
#   }

   $graph->Add($p1);
   $gfname = "lupie" . ".$luname." . $thisyear . md5(uniqid(time()));
   $graph->Stroke("$goutdir/$gfname.gif");
   $outinfo = "$gouturl/$gfname.gif";
   if ($debug) { print_r("$outinfo"); }

   return $outinfo;

}

function showGenericBar($listobject, $goutdir, $gouturl, $graphrecs, $xcol, $ycol, $color, $title, $xlabel, $ylabel, $debug) {

   if (count($graphrecs) > 0) {
      $xdata = array();
      $ydata = array();
   } else {
      $xdata = array(0);
      $ydata = array(0);
   }

   $scalemax = 0;
   foreach ($graphrecs as $thisrec) {
      $xval = $thisrec[$xcol];
      $yval = $thisrec[$ycol];
      array_push($xdata, $xval);
      array_push($ydata, $yval);

      if ($yval > $scalemax) {
         $scalemax = $mm;
      }

   }

#   print ("step 2<br>");
   // Create the graph.
   $graph = new Graph(400,200,"auto");
   $graph->SetScale("intlin");
   $graph->img->SetMargin(60,100,20,40);
   $graph->SetShadow();

   // Create the bar plot
   $eplot = new BarPlot($ydata);
   $eplot->SetFillColor($color);
   $eplot->SetLegend($ylabel);

   // Add the plots to the graph
   $graph->Add($eplot);

   $graph->title->Set("$title");
   $graph->xaxis->title->Set("$xlabel");
   $graph->yaxis->title->Set("$ylabel");
   $graph->title->SetFont(FF_FONT1,FS_BOLD);
   $graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
   $graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);
   $graph->xaxis->SetTickLabels($xdata);

   // Display the graph
   $gfname = "$ycol." . md5(uniqid(time())) . '.png';
   $gpath = "$goutdir/$gfname";
   $gurl = "$gouturl/$gfname";
   $graph->Stroke($gpath);
   return $gurl;
}

function showGenericMultiBar($goutdir, $gouturl, $inrecs, $debug) {

   # $indata  - array containing multiple data sets and info describing these data sets
   # 'title' -0 title of the graph
   # 'xlabel' - label for X-axis
   # 'bargraphs' - a collection for each bar graph to create, containing the following:
   #    'graphrecs' - the records to graph
   #    'xcol' -
   #    'ycol' -
   #    'color' -
   #    'ylegend' - legend entry for this dataset

   # set up the initial graph container
   // Create the graph.
   $graph = new Graph(400,200,"auto");
   $graph->SetScale("intlin");
   $graph->img->SetMargin(60,100,20,40);
   $graph->SetShadow();

   $bargraphs = $inrecs['bargraphs'];
   $title = $inrecs['title'];
   $xlabel = $inrecs['xlabel'];

   $x = 0;
   $maxrecs = 0;
   foreach ($bargraphs as $thisgraph) {
      $graphrecs = $thisgraph['graphrecs'];
      $xcol = $thisgraph['xcol'];
      $ycol = $thisgraph['ycol'];
      $color = $thisgraph['color'];
      $ylegend = $thisgraph['ylegend'];
      if(isset($thisgraph['alpha'])) {
         $alpha = $thisgraph['alpha'];
         $color .= '@' . $alpha;
      }
      if (count($graphrecs) > 0) {
         $xdata = array();
         $ydata = array();
      } else {
         $xdata = array(0);
         $ydata = array(0);
      }

      $scalemax = 0;
      foreach ($graphrecs as $thisrec) {
         $xval = $thisrec[$xcol];
         $yval = $thisrec[$ycol];
         array_push($xdata, $xval);
         array_push($ydata, $yval);

         if ($yval > $scalemax) {
            $scalemax = $mm;
         }

      }


      // Create the bar plot
      $eplot[$x] = new BarPlot($ydata);
      $eplot[$x]->SetFillColor($color);
      $eplot[$x]->SetLegend($ylegend);

      // Add the plots to the graph
      $graph->Add($eplot[$x]);
      if (count($xdata) > $maxrecs) {
         $graph->xaxis->SetTickLabels($xdata);
         $maxrecs = count($xdata);
      }
      $x++;
   }

   $graph->title->Set("$title");
   $graph->xaxis->title->Set("$xlabel");
   $graph->yaxis->title->Set("$ylabel");
   $graph->title->SetFont(FF_FONT1,FS_BOLD);
   $graph->yaxis->title->SetFont(FF_FONT1,FS_BOLD);
   $graph->xaxis->title->SetFont(FF_FONT1,FS_BOLD);

   // Display the graph
   $gfname = "$ycol." . md5(uniqid(time())) . '.png';
   $gpath = "$goutdir/$gfname";
   $gurl = "$gouturl/$gfname";
   $graph->Stroke($gpath);
   return $gurl;
}

############################################################################

function splineEx($goutdir, $goutpath, $fname) {
   $xdata = array(1,3,5,7,9,12,15,17.1);
   $ydata = array(5,1,9,6,4,3,19,12);

   // Get the interpolated values by creating
   // a new Spline object.
   $spline = new Spline($xdata,$ydata);

   // For the new data set we want 40 points to
   // get a smooth curve.
   list($newx,$newy) = $spline->Get(50);

   // Create the graph
   $g = new Graph(300,200);
   $g->SetMargin(30,20,40,30);
   $g->title->Set("Natural cubic splines");
   $g->title->SetFont(FF_ARIAL,FS_NORMAL,12);
   $g->subtitle->Set('(Control points shown in red)');
   $g->subtitle->SetColor('darkred');
   $g->SetMarginColor('lightblue');

   //$g->img->SetAntiAliasing();

   // We need a linlin scale since we provide both
   // x and y coordinates for the data points.
   $g->SetScale('linlin');

   // We want 1 decimal for the X-label
   $g->xaxis->SetLabelFormat('%1.1f');

   // We use a scatterplot to illustrate the original
   // contro points.
   $splot = new ScatterPlot($ydata,$xdata);

   //
   $splot->mark->SetFillColor('red@0.3');
   $splot->mark->SetColor('red@0.5');

   // And a line plot to stroke the smooth curve we got
   // from the original control points
   $lplot = new LinePlot($newy,$newx);
   $lplot->SetColor('navy');

   // Add the plots to the graph and stroke
   $g->Add($lplot);
   $g->Add($splot);

   $g->Stroke("$goutdir/$fname.gif");

   return "$goutpath/$fname.gif";
}

?>
