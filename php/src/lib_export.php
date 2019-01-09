<?php


#############################################################################################
###################  lib_export.php - Export/Input File Creation Routines ###################
#############################################################################################


function exportMasslinks($listobject, $scenarioid, $outdir, $outurl, $segments, $runname, $landuses, $years, $constits, $debug) {

   $bmprecs = getMasslinks($listobject, $scenarioid, $segments, $landuses, $years, $constits, $debug);
   #print_r($bmprecs);

   # format for output
   $outarr = nestArraySprintf("%s,%s,%s,%s,%3.6f", $bmprecs);

   #print_r($outarr);


   $colnames = array(array_keys($bmprecs[0]));

   $filename = "masslinks_$scenarioid" . "_$runname" . "_$thisyear" . ".csv";
   putDelimitedFile("$outdir/$filename",$colnames,",",1,'unix');

   putArrayToFilePlatform("$outdir/$filename", $outarr,0,'unix');
   $thisyear = join(',', $years);
   print("<a href='$outurl/$filename'>Download Masslink table for $thisyear</a><br>");

   return $bmprecs;

}

function exportBMPAreaEffic($listobject, $scenarioid, $outdir, $outurl, $segments, $landuses, $years, $constits, $debug) {

   $i = 0;

   foreach ($years as $thisyear) {
      $bmprecs = getBMPAreaEffic($listobject, $scenarioid, $segments, $landuses, $years, $constits, $debug);
      #print_r($bmprecs);
      print("Exporting LU Change BMPs for $thisyear<br>");

      if (count($bmprecs) > 0) {
         # format for output
         $outarr = nestArraySprintf("%s,%s,%s,%s,%s,%5.2f,%5.2f,%5.4f,%5.4f", $bmprecs);

         #print_r($outarr);
         $filename = "bmpeffic_$scenarioid" . ".csv";

         if ($i == 0) {
            $colnames = array(array_keys($bmprecs[0]));
            putDelimitedFile("$outdir/$filename",$colnames,",",1,'unix');
         }

         putArrayToFilePlatform("$outdir/$filename", $outarr,0,'unix');
         $thisyear = join(',', $years);

         $i++;
      }
   }

   if (count($years) == 0) {
      $yeartext = 'all years';
   } else {
      $yeartext = join(',', $years);
   }
   print("<a href='$outurl/$filename'>Download BMP Efficiencies by BMP for $yeartext.</a><br>");

   return $bmprecs;

}


function exportSubTypeBMPs($listobject, $projectid, $scenarioid, $outdir, $outurl, $segments, $landuses, $years, $constits, $debug) {

   $i = 0;

   foreach ($years as $thisyear) {
      $bmprecs = getIndividualAppliedBMPs($listobject, $projectid, $scenarioid, $segments, $landuses, array($thisyear), $constits, $debug);
      #print_r($bmprecs);

      if (count($bmprecs) > 0) {
         # format for output
         $outarr = nestArraySprintf("%s,%s,%s,%s,%s,%5.2f,%5.2f", $bmprecs);

         #print_r($outarr);
         $filename = "bmps_$scenarioid" . ".csv";

         if ($i == 0) {
            $colnames = array(array_keys($bmprecs[0]));
            putDelimitedFile("$outdir/$filename",$colnames,",",1,'unix');
         }

         putArrayToFilePlatform("$outdir/$filename", $outarr,0,'unix');
         $thisyear = join(',', $years);

         $i++;
      }
   }

   if (count($years) == 0) {
      $yeartext = 'all years';
   } else {
      $yeartext = join(',', $years);
   }
   print("<a href='$outurl/$filename'>Download BMP table for $yeartext.</a><br>");

   return $bmprecs;

}


function exportLUChangeBMPs($listobject, $projectid, $scenarioid, $outdir, $outurl, $segments, $landuses, $years, $constits, $debug) {

   $i = 0;
#$debug = 1;
   foreach ($years as $thisyear) {
      $bmprecs = getLUChangeBMPs($listobject, $projectid, $scenarioid, $segments, $landuses, array($thisyear), $constits, $debug);
      #print_r($bmprecs);
      print("Exporting LU Change BMPs for $thisyear<br>");

      if (count($bmprecs) > 0) {
         # format for output
         $outarr = nestArraySprintf("%s,%s,%s,%s,%s,%s,%5.2f,%5.2f", $bmprecs);

         #print_r($outarr);
         $filename = "luchangebmps_$scenarioid" . ".csv";

         if ($i == 0) {
            $colnames = array(array_keys($bmprecs[0]));
            putDelimitedFile("$outdir/$filename",$colnames,",",1,'unix');
         }

         putArrayToFilePlatform("$outdir/$filename", $outarr,0,'unix');
         $thisyear = join(',', $years);

         $i++;
      }
   }

   if (count($years) == 0) {
      $yeartext = 'all years';
   } else {
      $yeartext = join(',', $years);
   }
   print("<a href='$outurl/$filename'>Download LU Change BMP table for $yeartext.</a><br>");

   return $bmprecs;

}



function exportLandUses($listobject, $outdir, $outurl, $filename, $scenarioid, $projectid, $allsegs, $selyears, $baselu, $debug) {

   if (count($allsegs) > 0) {
      $sslist = "'" . join("','", $allsegs) . "'";
      $subcond = " lrseg in ( $sslist) ";
   } else {
      $subcond = ' ( 1 = 1 ) ';
   }
   if (strlen($selyears) > 0) {
      $yrcond = " thisyear in ($selyears) ";
   } else {
      $yrcond = " (1 = 1) ";
   }

   if ($baselu) {
      $lutab = " lucomposite ";
      $scenproj = " projectid = $projectid";
      $lucol = " luname ";
   } else {
      $lutab = 'scen_lrsegs';
      $scenproj = " scenarioid = $scenarioid ";
      $lucol = " luname ";
   }

   $listobject->querystring = " select landseg, riverseg, $lucol, thisyear, luarea ";
   $listobject->querystring .= " from $lutab ";
   $listobject->querystring .= " where $scenproj ";
   $listobject->querystring .= "    and $subcond ";
   $listobject->querystring .= "    and $yrcond ";
   $listobject->querystring .= " order by thisyear, riverseg, landseg, luname ";
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $listobject->performQuery();

   $srcrecs = $listobject->queryrecords;
   # format for output
   $outarr = nestArraySprintf("%s,%s,%s,%s,%6.2f", $srcrecs);
   #print_r($outarr);

   $colnames = array(array_keys($srcrecs[0]));

   putDelimitedFile("$outdir/$filename",$colnames,",",1,'unix');

   putArrayToFilePlatform("$outdir/$filename", $outarr,0,'unix');
   print("<a href='$outurl/$filename'>Download Land Use for: $selyears</a><br>");
}

function exportSources($listobject, $outdir, $outurl, $filename, $scenarioid, $subsheds, $selyears, $sourcepolls, $sources, $debug) {

   # THIS DOES NOT YET WORK, MUST SPLIT OUT YEARS BECAUSE SOURCE PRODUCTION ROUTINE DOES NOT DO MULTIPLE YEARS

   $srcrecs = getSourceProduction($listobject, $subsheds, $scenarioid, $polltype, $thisyear, $debug);

   # format for output
   $outarr = nestArraySprintf("%s,%s,%s,%s,%6.2f", $srcrecs);
   #print_r($outarr);

   $colnames = array(array_keys($srcrecs[0]));

   putDelimitedFile("$outdir/$filename",$colnames,",",1,'unix');

   putArrayToFilePlatform("$outdir/$filename", $outarr,0,'unix');
   print("<a href='$outurl/$filename'>Download Land Use for: $selyears</a><br>");
}



function exportSourcePops($listobject, $outdir, $outurl, $filename, $scenarioid, $subsheds, $selyears, $tracerpoll, $sources, $sumby, $debug) {

   #
   $i = 0;

   foreach (split(',', $selyears) as $thisyear) {

      $srcrecs = getSegmentSources($listobject, $sources, $tracerpoll, $subsheds, $scenarioid, $thisyear, $sumby, $debug);

      # format for output

      if (count($srcrecs) > 0) {
         $outarr = nestArraySprintf("%s,%s,%s,%s,%6.2f,%6.2f,%6.2f", $srcrecs);
         #print_r($outarr);

         $colnames = array(array_keys($srcrecs[0]));

         if ($i == 0) {
            # first group of records, add the header
            putDelimitedFile("$outdir/$filename",$colnames,",",1,'unix');
         }

         putArrayToFilePlatform("$outdir/$filename", $outarr,0,'unix');

         $i++;
      }
   }
   print("<a href='$outurl/$filename'>Download Populations for: $selyears</a><br>");
}



function exportProjectedSources($listobject, $outdir, $outurl, $filename, $scenarioid, $subsheds, $selyears, $sources, $debug) {

   #

   $srcrecs = getProjectedSources($listobject, $subsheds, $scenarioid, $sources, $selyears, $debug);

   # format for output
   $outarr = nestArraySprintf("%s,%s,%s,%s,%s,%6.2f,%6.2f,%6.2f,%6.2f,%6.2f", $srcrecs);
   #print_r($outarr);

   $colnames = array(array_keys($srcrecs[0]));

   putDelimitedFile("$outdir/$filename",$colnames,",",1,'unix');

   putArrayToFilePlatform("$outdir/$filename", $outarr,0,'unix');
   print("<a href='$outurl/$filename'>Download Projected Sources for: $thisyear</a><br>");
}



function makeSepticInputFiles($listobject, $projectid, $scenarioid, $runname, $outurl, $outdir, $thisyear, $allsegs, $constits, $septicid, $debug) {

   if (count($allsegs) > 0) {
      $sslist = "'" . join("','", $allsegs) . "'";
      $subcond = " lrseg in ( $sslist) ";
      $asubcond = " a.lrseg in ( $sslist) ";
   } else {
      $subcond = ' ( 1 = 1 ) ';
      $asubcond = ' ( 1 = 1 ) ';
   }
   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear = $thisyear ";
      $ayrcond = " a.thisyear = $thisyear ";
      $byrcond = " b.thisyear = $thisyear ";
   } else {
      $yrcond = " (1 = 1) ";
      $ayrcond = " (1 = 1) ";
      $byrcond = " (1 = 1) ";
   }
   if (strlen($constits) > 0) {
      $pollclause = " pollutanttype in ($constits) ";
      $apollclause = " a.pollutanttype in ($constits) ";
      $bpollclause = " b.pollutanttype in ($constits) ";
      $bconclause = " b.typeid in ($constits) ";
   } else {
      $pollclause = " (1 = 1) ";
      $apollclause = " (1 = 1) ";
      $bpollclause = " (1 = 1) ";
      $bconclause = " (1 = 1) ";
   }


   if ($listobject->tableExists("septic_out") ) {
      $listobject->querystring = "drop table septic_out ";
      $listobject->performQuery();
      $listobject->querystring = "drop table septic_effluent ";
      $listobject->performQuery();
   }
   # septic loads
   # these loads are reported on lbs / day basis
   # thus, they are the aggregate of any component landuse/subshed intersections
   # where the load per acre for each component land segment is multiplied by the
   # acres of that particular land segment (low density residential is the stuff )
   # then, if more than one of the same component land use are pulled from different
   # landuses they are summed up


   $listobject->querystring = "create temp table septic_out as ";
   $listobject->querystring .= "select a.landseg, a.riverseg, a.thisyear, c.shortname as constit, ";
   $listobject->querystring .= " sum((a.luarea * b.annualapplied )/365.0) as septicload ";
   $listobject->querystring .= " from scen_lrsegs as a, scen_sourceperunitarea as b, pollutanttype as c ";
   $listobject->querystring .= " where a.subshedid = b.subshedid ";
   $listobject->querystring .= " and a.luname = b.luname ";
   # class 10 is septic load class
   $listobject->querystring .= " and b.sourceclass = $septicid ";
   $listobject->querystring .= " and $bpollclause ";
   $listobject->querystring .= " and a.scenarioid = $scenarioid ";
   $listobject->querystring .= " and b.scenarioid = $scenarioid ";
   $listobject->querystring .= " and a.projectid = $projectid ";
   $listobject->querystring .= " and b.projectid = $projectid ";
   $listobject->querystring .= " and $byrcond ";
   $listobject->querystring .= " and $ayrcond ";
   $listobject->querystring .= " and $asubcond ";
   $listobject->querystring .= " and b.pollutanttype = c.typeid ";
   $listobject->querystring .= " group by a.landseg, a.riverseg, a.thisyear, c.shortname ";
   $listobject->querystring .= " order by a.landseg, a.riverseg, a.thisyear, c.shortname ";
   $listobject->performQuery();
   if ($debug) { print("$listobject->querystring ; <br>"); }


   $listobject->querystring = "create temp table septic_effluent as ";
   $listobject->querystring .= "select a.landseg, a.riverseg, a.thisyear, b.shortname as constit, ";
   $listobject->querystring .= " 0.0::float8 as septicload ";
   $listobject->querystring .= " from scen_lrsegs as a, pollutanttype as b ";
   $listobject->querystring .= " where a.riverseg <> '' and a.riverseg <> 'unknown' ";
   $listobject->querystring .= " and scenarioid = $scenarioid ";
   $listobject->querystring .= " and projectid = $projectid ";
   $listobject->querystring .= " and $bconclause ";
   $listobject->querystring .= " and $ayrcond ";
   $listobject->querystring .= " and $asubcond ";
   $listobject->querystring .= " group by landseg, riverseg, thisyear, b.shortname ";
   $listobject->querystring .= " order by landseg, riverseg, b.shortname ";
   $listobject->performQuery();
   if ($debug) { print("$listobject->querystring ; <br>"); }

   $listobject->querystring = "update septic_effluent set septicload = a.septicload ";
   $listobject->querystring .= "from septic_out as a ";
   $listobject->querystring .= " where septic_effluent.riverseg = a.riverseg ";
   $listobject->querystring .= " and septic_effluent.landseg = a.landseg ";
   $listobject->querystring .= " and septic_effluent.constit = a.constit ";
   $listobject->performQuery();
   if ($debug) { print("$listobject->querystring ; <br>"); }


   $listobject->querystring = "select landseg, riverseg, thisyear, constit, septicload ";
   $listobject->querystring .= " from septic_effluent ";
   $listobject->querystring .= " order by landseg, riverseg, constit ";
   $listobject->performQuery();
   if ($debug) { print("$listobject->querystring ; <br>"); }


   print("<b>Septic for $thisyear</b><br>");


   $colnames = array(array_keys($listobject->queryrecords[0]));
   $filename = "septic_$runname" . "_$thisyear.csv";
   putDelimitedFile("$outdir/$filename", $colnames, ',',1,'unix');
   putDelimitedFile("$outdir/$filename", $listobject->queryrecords, ',',0,'unix');
   print("<a href='$outurl/$filename'>Download Septic Model Input table</a><br>");

}


function makeManureInputFiles($listobject, $projectid, $scenarioid, $runname, $outurl, $outdir, $thisyear, $allsegs, $constits, $manure_sclass, $spread_manure, $nullval, $debug) {

   if (count($allsegs) > 0) {
      $sslist = "'" . join("','", $allsegs) . "'";
      $subcond = " lrseg in ( $sslist) ";
      $asubcond = " a.lrseg in ( $sslist) ";
   } else {
      $subcond = ' ( 1 = 1 ) ';
      $asubcond = ' ( 1 = 1 ) ';
   }
   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear = $thisyear ";
      $ayrcond = " a.thisyear = $thisyear ";
      $byrcond = " b.thisyear = $thisyear ";
   } else {
      $yrcond = " (1 = 1) ";
      $ayrcond = " (1 = 1) ";
      $byrcond = " (1 = 1) ";
   }
   if (strlen($constits) > 0) {
      $pollclause = " pollutanttype in ($constits) ";
      $apollclause = " a.pollutanttype in ($constits) ";
      $bpollclause = " b.pollutanttype in ($constits) ";
      $bconclause = " b.typeid in ($constits) ";
      $dconclause = " d.typeid in ($constits) ";
   } else {
      $pollclause = " (1 = 1) ";
      $apollclause = " (1 = 1) ";
      $bpollclause = " (1 = 1) ";
      $bconclause = " (1 = 1) ";
      $dconclause = " (1 = 1) ";
   }

   # land loads
   # these loads are reported on lbs / month basis
   # thus, they are the aggregate of any component landuse/subshed intersections
   # where the load per acre for each component land segment is multiplied by the
   # acres of that particular land segment (low density residential is the stuff )
   # then, if more than one of the same component land use are pulled from different
   # landuses they are summed up

   # custom mon applied table, accepts tables to sum as input, pass all tables except
   # for septic table, since it is weighted by low intensity, but actually applied
   # to water.
   # must do in seperate files for manure, fertilizer and the results of legume fixation

   # First, the manure apps

   if ($listobject->tableExists("manure_apptab") ) {
      $listobject->querystring = "drop table manure_apptab ";
      $listobject->performQuery();
      $listobject->querystring = "drop table manure_out ";
      $listobject->performQuery();
   }

   $listobject->querystring = "create temp table manure_apptab as select a.landseg as lseg, b.luname as lu, b.pollutanttype, ";
   $listobject->querystring .= "b.JAN,";
   $listobject->querystring .= "b.FEB,";
   $listobject->querystring .= "b.MAR,";
   $listobject->querystring .= "b.APR,";
   $listobject->querystring .= "b.MAY,";
   $listobject->querystring .= "b.JUN,";
   $listobject->querystring .= "b.JUL,";
   $listobject->querystring .= "b.AUG,";
   $listobject->querystring .= "b.SEP,";
   $listobject->querystring .= "b.OCT,";
   $listobject->querystring .= "b.NOV,";
   $listobject->querystring .= "b.DEC";
   $listobject->querystring .= " from temp_landsegs as a, ";
   $listobject->querystring .= "    ( select subshedid, luname, pollutanttype,  ";
   $listobject->querystring .= "         sum(JAN) as JAN,";
   $listobject->querystring .= "         sum(FEB) as FEB,";
   $listobject->querystring .= "         sum(MAR) as MAR,";
   $listobject->querystring .= "         sum(APR) as APR,";
   $listobject->querystring .= "         sum(MAY) as MAY,";
   $listobject->querystring .= "         sum(JUN) as JUN,";
   $listobject->querystring .= "         sum(JUL) as JUL,";
   $listobject->querystring .= "         sum(AUG) as AUG,";
   $listobject->querystring .= "         sum(SEP) as SEP,";
   $listobject->querystring .= "         sum(OCT) as OCT,";
   $listobject->querystring .= "         sum(NOV) as NOV,";
   $listobject->querystring .= "         sum(DEC) as DEC";
   $listobject->querystring .= "      from temp_scensource as b  ";
   $listobject->querystring .= "      where $pollclause ";
   $listobject->querystring .= "         and b.sourceclass in ($manure_sclass) ";
   $listobject->querystring .= "      group by subshedid, luname, pollutanttype ";
   $listobject->querystring .= " ) as b ";
   $listobject->querystring .= " WHERE a.subshedid = b.subshedid ";
   $listobject->querystring .= "    and a.luname = b.luname ";
   $listobject->querystring .= " order by lseg, lu, pollutanttype ";
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $listobject->performQuery();

   $listobject->querystring = "create temp table manure_out as select ";
   $listobject->querystring .= " a.landseg as lseg, a.luname as lu, ";
   $listobject->querystring .= " d.shortname as constituent, ";
   $listobject->querystring .= " d.typeid as pollutanttype, ";
   $listobject->querystring .= " 0.0 as JAN,";
   $listobject->querystring .= " 0.0 as FEB,";
   $listobject->querystring .= " 0.0 as MAR,";
   $listobject->querystring .= " 0.0 as APR,";
   $listobject->querystring .= " 0.0 as MAY,";
   $listobject->querystring .= " 0.0 as JUN,";
   $listobject->querystring .= " 0.0 as JUL,";
   $listobject->querystring .= " 0.0 as AUG,";
   $listobject->querystring .= " 0.0 as SEP,";
   $listobject->querystring .= " 0.0 as OCT,";
   $listobject->querystring .= " 0.0 as NOV,";
   $listobject->querystring .= " 0.0 as DEC";
   $listobject->querystring .= " from temp_landsegs as a,  ";
   $listobject->querystring .= "      pollutanttype as d ";
   $listobject->querystring .= " where $dconclause ";
   $listobject->querystring .= "    and d.typeid in ( ";
   $listobject->querystring .= "       select pollutanttype from temp_scensource ";
   $listobject->querystring .= "       group by pollutanttype ) ";
   # only get landuses that COULD receive manure applications
   $listobject->querystring .= "   and ( ";
   # manure spreading distros from local_apply table
   $listobject->querystring .= "       a.luname in ( select luname ";
   $listobject->querystring .= "      from local_apply as a, spreadtype as b  ";
   $listobject->querystring .= "      where b.spreadid in ( $spread_manure ) ";
   $listobject->querystring .= "      and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "      and b.shortname = a.source_type ";
   $listobject->querystring .= "      group by a.luname ) ";
   # manure spreading distros from default distro table
   $listobject->querystring .= "      OR a.luname in ( select b.hspflu ";
   $listobject->querystring .= "      from monthlydistro as a, landuses as b  ";
   $listobject->querystring .= "      where b.landuseid = a.landuseid ";
   # manure spreading distros
   $listobject->querystring .= "      and a.spreadid in ( $spread_manure ) ";
   $listobject->querystring .= "      and a.projectid = $projectid ";
   $listobject->querystring .= "      and b.projectid = $projectid ";
   $listobject->querystring .= "      group by b.hspflu ) ";
   $listobject->querystring .= "   ) ";

   if (!$loadwater) {
      # don't include water landuses in this data set
      $listobject->querystring .= "    and a.luname not in ( 'wat', 'inw' ) ";
   }
   $listobject->querystring .= " group by lseg, lu, pollutanttype, constituent ";
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $listobject->performQuery();


   $listobject->querystring = " update manure_out set JAN = a.JAN, ";
   $listobject->querystring .= " FEB = a.FEB, ";
   $listobject->querystring .= " MAR = a.MAR,";
   $listobject->querystring .= " APR = a.APR,";
   $listobject->querystring .= " MAY = a.MAY,";
   $listobject->querystring .= " JUN = a.JUN,";
   $listobject->querystring .= " JUL = a.JUL,";
   $listobject->querystring .= " AUG = a.AUG,";
   $listobject->querystring .= " SEP = a.SEP,";
   $listobject->querystring .= " OCT = a.OCT,";
   $listobject->querystring .= " NOV = a.NOV,";
   $listobject->querystring .= " DEC = a.DEC ";
   $listobject->querystring .= " from manure_apptab as a ";
   $listobject->querystring .= " where manure_out.lseg = a.lseg ";
   $listobject->querystring .= "   and manure_out.lu = a.lu ";
   $listobject->querystring .= "   and manure_out.pollutanttype = a.pollutanttype ";
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $listobject->performQuery();

   $listobject->querystring = " update manure_out set JAN = $nullval, ";
   $listobject->querystring .= " FEB = $nullval, ";
   $listobject->querystring .= " MAR = $nullval,";
   $listobject->querystring .= " APR = $nullval,";
   $listobject->querystring .= " MAY = $nullval,";
   $listobject->querystring .= " JUN = $nullval,";
   $listobject->querystring .= " JUL = $nullval,";
   $listobject->querystring .= " AUG = $nullval,";
   $listobject->querystring .= " SEP = $nullval,";
   $listobject->querystring .= " OCT = $nullval,";
   $listobject->querystring .= " NOV = $nullval,";
   $listobject->querystring .= " DEC = $nullval";
   $listobject->querystring .= " from temp_landsegs as a ";
   $listobject->querystring .= " where manure_out.lseg = a.landseg ";
   $listobject->querystring .= "   and manure_out.lu = a.luname ";
   $listobject->querystring .= "   and a.luarea <= 0 ";
   $listobject->performQuery();

   $listobject->querystring = " select lseg, lu, constituent, jan, feb, mar, apr, may, jun, jul, aug, sep, oct, nov, dec from manure_out ";
   $listobject->querystring .= " order by lseg, lu, constituent ";
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $listobject->performQuery();


   # format for output
   $outarr = nestArraySprintf("%6s,%3s,%4s,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f", $listobject->queryrecords);

   #print_r($outarr);

   print("<b>Manure for $thisyear</b><br>");

   $colnames = array(array_keys($listobject->queryrecords[0]));

   $filename = "manure_$runname" . "_$thisyear.csv";
   putDelimitedFile("$outdir/$filename",$colnames,",",1,'unix');

   putArrayToFilePlatform("$outdir/$filename", $outarr,0,'unix');
   print("<a href='$outurl/$filename'>Download NPS Manure Input table for $thisyear</a><br>");

}



function makeFertilizerInputFiles($listobject, $projectid, $scenarioid, $runname, $outurl, $outdir, $thisyear, $allsegs, $constits, $fertsourceclasses, $spread_fert, $nullval, $debug) {

   if (count($allsegs) > 0) {
      $sslist = "'" . join("','", $allsegs) . "'";
      $subcond = " lrseg in ( $sslist) ";
      $asubcond = " a.lrseg in ( $sslist) ";
   } else {
      $subcond = ' ( 1 = 1 ) ';
      $asubcond = ' ( 1 = 1 ) ';
   }
   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear = $thisyear ";
      $ayrcond = " a.thisyear = $thisyear ";
      $byrcond = " b.thisyear = $thisyear ";
   } else {
      $yrcond = " (1 = 1) ";
      $ayrcond = " (1 = 1) ";
      $byrcond = " (1 = 1) ";
   }
   if (strlen($constits) > 0) {
      $pollclause = " pollutanttype in ($constits) ";
      $apollclause = " a.pollutanttype in ($constits) ";
      $bpollclause = " b.pollutanttype in ($constits) ";
      $bconclause = " b.typeid in ($constits) ";
      $dconclause = " d.typeid in ($constits) ";
   } else {
      $pollclause = " (1 = 1) ";
      $apollclause = " (1 = 1) ";
      $bpollclause = " (1 = 1) ";
      $bconclause = " (1 = 1) ";
      $dconclause = " (1 = 1) ";
   }

   # land loads
   # these loads are reported on lbs / month basis
   # thus, they are the aggregate of any component landuse/subshed intersections
   # where the load per acre for each component land segment is multiplied by the
   # acres of that particular land segment (low density residential is the stuff )
   # then, if more than one of the same component land use are pulled from different
   # landuses they are summed up

   # custom mon applied table, accepts tables to sum as input, pass all tables except
   # for septic table, since it is weighted by low intensity, but actually applied
   # to water.
   # must do in seperate files for manure, fertilizer and the results of legume fixation

   if ($listobject->tableExists("fert_out") ) {
      $listobject->querystring = "drop table fert_out ";
      $listobject->performQuery();
      $listobject->querystring = "drop table fert_defaults ";
      $listobject->performQuery();
   }

   $listobject->querystring = "create temp table fert_defaults as ";
   $listobject->querystring .= "       select a.landseg, a.subshedid, a.luname, ";
   $listobject->querystring .= "          CASE  ";
   $listobject->querystring .= "             WHEN a.luarea = 0 THEN $nullval::float8  ";
   $listobject->querystring .= "             ELSE 0.0::float8  ";
   $listobject->querystring .= "          END as defval,  ";
   $listobject->querystring .= "          b.typeid as pollutanttype ";
   $listobject->querystring .= "       from temp_landsegs as a, pollutanttype as b ";
   $listobject->querystring .= "       where $bconclause ";
   $listobject->querystring .= "          and a.luname in  ";
   $listobject->querystring .= "          ( select luname from scen_monthlydistro ";
   $listobject->querystring .= "            where scenarioid = $scenarioid ";
   $listobject->querystring .= "               and spreadid in ($spread_fert) ";
   $listobject->querystring .= "            group by luname ";
   $listobject->querystring .= "          ) ";
   $listobject->querystring .= "          and b.typeid in ";
   $listobject->querystring .= "             ( select pollutanttype ";
   $listobject->querystring .= "               from sourcepollutants ";
   $listobject->querystring .= "               where sourcetypeid in ";
   $listobject->querystring .= "                  ( select typeid ";
   $listobject->querystring .= "                    from sourceloadtype ";
   $listobject->querystring .= "                    where projectid = $projectid ";
   $listobject->querystring .= "                       and sourceclass in ( $fertsourceclasses ) ";
   $listobject->querystring .= "                  ) ";
   $listobject->querystring .= "              ) ";
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $listobject->performQuery();

/*
      $listobject->querystring = "select * from fert_defaults ";
      $listobject->performQuery();
      $listobject->showList();
*/
   $listobject->querystring = "create temp table fert_out as select a.landseg as lseg, a.luname as lu, ";
   $listobject->querystring .= "   c.shortname as constituent, a.defval, ";
   $listobject->querystring .= " sum(b.JAN) as JAN,";
   $listobject->querystring .= " sum(b.FEB) as FEB,";
   $listobject->querystring .= " sum(b.MAR) as MAR,";
   $listobject->querystring .= " sum(b.APR) as APR,";
   $listobject->querystring .= " sum(b.MAY) as MAY,";
   $listobject->querystring .= " sum(b.JUN) as JUN,";
   $listobject->querystring .= " sum(b.JUL) as JUL,";
   $listobject->querystring .= " sum(b.AUG) as AUG,";
   $listobject->querystring .= " sum(b.SEP) as SEP,";
   $listobject->querystring .= " sum(b.OCT) as OCT,";
   $listobject->querystring .= " sum(b.NOV) as NOV,";
   $listobject->querystring .= " sum(b.DEC) as DEC";
   $listobject->querystring .= " from fert_defaults as a left outer join ";
   $listobject->querystring .= "    ( select subshedid, luname, pollutanttype,  ";
   $listobject->querystring .= "         sum(JAN) as JAN,";
   $listobject->querystring .= "         sum(FEB) as FEB,";
   $listobject->querystring .= "         sum(MAR) as MAR,";
   $listobject->querystring .= "         sum(APR) as APR,";
   $listobject->querystring .= "         sum(MAY) as MAY,";
   $listobject->querystring .= "         sum(JUN) as JUN,";
   $listobject->querystring .= "         sum(JUL) as JUL,";
   $listobject->querystring .= "         sum(AUG) as AUG,";
   $listobject->querystring .= "         sum(SEP) as SEP,";
   $listobject->querystring .= "         sum(OCT) as OCT,";
   $listobject->querystring .= "         sum(NOV) as NOV,";
   $listobject->querystring .= "         sum(DEC) as DEC";
   $listobject->querystring .= "      from temp_scensource as b  ";
   $listobject->querystring .= "      where $pollclause ";
   $listobject->querystring .= "         and b.sourceclass in ($fertsourceclasses) ";
   $listobject->querystring .= "      group by subshedid, luname, pollutanttype ";
   $listobject->querystring .= " ) as b ";
   $listobject->querystring .= "    on ( ";
   $listobject->querystring .= "       a.subshedid = b.subshedid ";
   $listobject->querystring .= "       and a.luname = b.luname ";
   $listobject->querystring .= "       and a.pollutanttype = b.pollutanttype ";
   $listobject->querystring .= "    ) left outer join pollutanttype as c ";
   $listobject->querystring .= "    on ( ";
   $listobject->querystring .= "       a.pollutanttype = c.typeid ";
   $listobject->querystring .= "    )  ";
   $listobject->querystring .= "group by a.landseg, a.luname, a.defval, c.shortname";
   $listobject->querystring .= " order by a.landseg, a.luname, c.shortname ";
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $listobject->performQuery();


   $listobject->querystring = " update fert_out set ";
   $listobject->querystring .= "JAN = defval, ";
   $listobject->querystring .= "FEB = defval, ";
   $listobject->querystring .= "MAR = defval, ";
   $listobject->querystring .= "APR = defval, ";
   $listobject->querystring .= "MAY = defval, ";
   $listobject->querystring .= "JUN = defval, ";
   $listobject->querystring .= "JUL = defval, ";
   $listobject->querystring .= "AUG = defval, ";
   $listobject->querystring .= "SEP = defval, ";
   $listobject->querystring .= "OCT = defval, ";
   $listobject->querystring .= "NOV = defval, ";
   $listobject->querystring .= "DEC = defval ";
   $listobject->querystring .= "where JAN is null ";
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $listobject->performQuery();


   # alfalfa adjust - HSPF has a bad habit of changing the output files if there are zero
   # applications on a land use, and since alfalfa may at times receive nitrogen inputs
   # this little hack is introduced
   $listobject->querystring = " update fert_out set ";
   $listobject->querystring .= " JAN = JAN + 0.01 ";
   $listobject->querystring .= " WHERE lu in ('nal', 'alf')";
   $listobject->querystring .= "    and JAN <> $nullval ";
   $listobject->performQuery();
   if ($debug) {print("$listobject->querystring ; <br>"); }

   # now, format for output
   $listobject->querystring = " select lseg, lu, constituent, jan, feb, mar, apr, may, jun, jul, aug,  ";
   $listobject->querystring .= " sep, oct, nov, dec from fert_out ";
   $listobject->querystring .= " order by lseg, lu, constituent ";
   $listobject->performQuery();
   if ($debug) { print("$listobject->querystring ; <br>"); }

   $outarr = nestArraySprintf("%6s,%3s,%4s,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f", $listobject->queryrecords);

   print("<b>Fertilizer for $thisyear</b><br>");
   $colnames = array(array_keys($listobject->queryrecords[0]));

   $filename = "fert_$runname" . "_$thisyear.csv";
   putDelimitedFile("$outdir/$filename",$colnames,",",1,'unix');
   putArrayToFilePlatform("$outdir/$filename", $outarr,0,'unix');
   print("<a href='$outurl/$filename'>Download NPS Fertilizer Input table for $thisyear</a><br>");

}


function makeLegumeInputFiles($listobject, $projectid, $scenarioid, $runname, $outurl, $outdir, $thisyear, $allsegs, $constits, $legume_nut, $nullval, $debug) {

   if (count($allsegs) > 0) {
      $sslist = "'" . join("','", $allsegs) . "'";
      $subcond = " lrseg in ( $sslist) ";
      $asubcond = " a.lrseg in ( $sslist) ";
   } else {
      $subcond = ' ( 1 = 1 ) ';
      $asubcond = ' ( 1 = 1 ) ';
   }
   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear = $thisyear ";
      $ayrcond = " a.thisyear = $thisyear ";
      $byrcond = " b.thisyear = $thisyear ";
   } else {
      $yrcond = " (1 = 1) ";
      $ayrcond = " (1 = 1) ";
      $byrcond = " (1 = 1) ";
   }
   if (strlen($constits) > 0) {
      $pollclause = " pollutanttype in ($constits) ";
      $apollclause = " a.pollutanttype in ($constits) ";
      $bpollclause = " b.pollutanttype in ($constits) ";
      $bconclause = " b.typeid in ($constits) ";
      $dconclause = " d.typeid in ($constits) ";
   } else {
      $pollclause = " (1 = 1) ";
      $apollclause = " (1 = 1) ";
      $bpollclause = " (1 = 1) ";
      $bconclause = " (1 = 1) ";
      $dconclause = " (1 = 1) ";
   }

   # now do the legume apps

   if ($listobject->tableExists("legume_out") ) {
      $listobject->querystring = "drop table legume_out ";
      $listobject->performQuery();
      $listobject->querystring = "drop table legume_defaults ";
      $listobject->performQuery();
   }

   $listobject->querystring = "create temp table legume_defaults as ";
   $listobject->querystring .= "       select a.landseg, a.subshedid, a.luname, ";
   $listobject->querystring .= "          CASE  ";
   $listobject->querystring .= "             WHEN a.luarea = 0 THEN $nullval::float8  ";
   $listobject->querystring .= "             ELSE 0.0::float8  ";
   $listobject->querystring .= "          END as defval,  ";
   $listobject->querystring .= "          b.typeid as pollutanttype, ";
   $listobject->querystring .= "          b.shortname ";
   $listobject->querystring .= "       from temp_landsegs as a, pollutanttype as b ";
   $listobject->querystring .= "       where $bconclause ";
   $listobject->querystring .= "          and b.typeid in ($legume_nut) ";
   $listobject->querystring .= "          and a.luname in  ";
   $listobject->querystring .= "          ( select luname from local_apply ";
   $listobject->querystring .= "            where scenarioid = $scenarioid ";
   $listobject->querystring .= "               and source_type = 'n_fix' ";
   $listobject->querystring .= "            group by luname ";
   $listobject->querystring .= "          ) ";
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $listobject->performQuery();
#              print("$listobject->querystring ; <br>");

/*
      $listobject->querystring = "select * from legume_defaults ";
      $listobject->performQuery();
      $listobject->showList();
*/

   $listobject->querystring = "create temp table legume_out as select a.landseg as lseg, a.luname as lu, ";
   $listobject->querystring .= "   c.shortname as constituent, a.defval, ";
   $listobject->querystring .= " sum(b.JAN) as JAN,";
   $listobject->querystring .= " sum(b.FEB) as FEB,";
   $listobject->querystring .= " sum(b.MAR) as MAR,";
   $listobject->querystring .= " sum(b.APR) as APR,";
   $listobject->querystring .= " sum(b.MAY) as MAY,";
   $listobject->querystring .= " sum(b.JUN) as JUN,";
   $listobject->querystring .= " sum(b.JUL) as JUL,";
   $listobject->querystring .= " sum(b.AUG) as AUG,";
   $listobject->querystring .= " sum(b.SEP) as SEP,";
   $listobject->querystring .= " sum(b.OCT) as OCT,";
   $listobject->querystring .= " sum(b.NOV) as NOV,";
   $listobject->querystring .= " sum(b.DEC) as DEC";
   $listobject->querystring .= " from legume_defaults as a left outer join ";
   $listobject->querystring .= "    local_apply as b ";
   $listobject->querystring .= "    on ( ";
   $listobject->querystring .= "       a.subshedid = b.subshedid ";
   $listobject->querystring .= "       and a.luname = b.luname ";
   $listobject->querystring .= "       and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "       and b.thisyear = $thisyear ";
   $listobject->querystring .= "       and b.source_type = 'n_fix' ";
   $listobject->querystring .= "    ) left outer join pollutanttype as c ";
   $listobject->querystring .= "    on ( ";
   $listobject->querystring .= "       a.pollutanttype = c.typeid ";
   $listobject->querystring .= "    )  ";
   $listobject->querystring .= "group by a.landseg, a.luname, a.defval, c.shortname";
   $listobject->querystring .= " order by a.landseg, a.luname, c.shortname ";
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $listobject->performQuery();


   $listobject->querystring = " update legume_out set ";
   $listobject->querystring .= "JAN = defval, ";
   $listobject->querystring .= "FEB = defval, ";
   $listobject->querystring .= "MAR = defval, ";
   $listobject->querystring .= "APR = defval, ";
   $listobject->querystring .= "MAY = defval, ";
   $listobject->querystring .= "JUN = defval, ";
   $listobject->querystring .= "JUL = defval, ";
   $listobject->querystring .= "AUG = defval, ";
   $listobject->querystring .= "SEP = defval, ";
   $listobject->querystring .= "OCT = defval, ";
   $listobject->querystring .= "NOV = defval, ";
   $listobject->querystring .= "DEC = defval ";
   $listobject->querystring .= "where JAN is null ";
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $listobject->performQuery();
#               print("$listobject->querystring ; <br>");

   $listobject->querystring = "select lseg, lu, constituent, jan, feb, mar, apr, may, jun, jul, aug, sep, oct, nov, dec from legume_out order by lseg, lu";
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $listobject->performQuery();


   $outarr = nestArraySprintf("%6s,%3s,%4s,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f", $listobject->queryrecords);

   print("<b>Maximum Legume Fixed N for $thisyear</b><br>");

   $colnames = array(array_keys($listobject->queryrecords[0]));

   $filename = "legume_$runname" . "_$thisyear.csv";
   putDelimitedFile("$outdir/$filename",$colnames,",",1,'unix');

   putArrayToFilePlatform("$outdir/$filename", $outarr,0,'unix');
   print("<a href='$outurl/$filename'>Download NPS Legume Input table for $thisyear</a><br>");

}


function makeLanduseInputFiles($listobject, $projectid, $scenarioid, $runname, $outurl, $outdir, $thisyear, $allsegs, $nullval, $debug) {

   # use crosstab function on a temp table excerpted from the scen table
   # expects that temp_lrsegs has been created and contains all desired lrsegs

   $lrfoo = " ( select riverseg, ";
   $lrfoo .= " landseg, luname, luarea from temp_lrsegs ) as foo ";

   $listobject->querystring = doGenericCrossTab ($listobject, $lrfoo, 'riverseg, landseg', 'luname', 'luarea');
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $listobject->performQuery();

   print("<b>Landuse for $thisyear</b><br>");

   $colnames = array(array_keys($listobject->queryrecords[0]));

   $filename = "land_use_$runname" . "_$thisyear.csv";
   putDelimitedFile("$outdir/$filename",$colnames,",",1,'unix');

   putDelimitedFile("$outdir/$filename", $listobject->queryrecords, ',',0,'unix');
   print("<a href='$outurl/$filename'>Download Land Use table</a><br>");

}


function makeUptakeCurveFiles($listobject, $projectid, $scenarioid, $runname, $outurl, $outdir, $thisyear, $allsegs, $constits, $crop_lutypes, $nullval, $debug) {

   if ($listobject->tableExists("lu_uptakes") ) {
      $listobject->querystring = "drop table lu_uptakes ";
      $listobject->performQuery();
   }

   print("<hr><h3>Crop Land Uptake Curves:</h3><br>");
   print("<b>$groupname </b><br>");

   $listobject->querystring = "create temp table lu_uptakes as select a.subshedid, ";
   $listobject->querystring .= " a.luname, ";
   $listobject->querystring .= " 0.0 as jan, 0.0 as feb, 0.0 as mar, ";
   $listobject->querystring .= " 0.0 as apr, 0.0 as may, 0.0 as jun, 0.0 as jul, ";
   $listobject->querystring .= " 0.0 as aug, 0.0 as sep, 0.0 as oct, 0.0 as nov, ";
   $listobject->querystring .= " 0.0 as dec ";
   $listobject->querystring .= " from scen_subsheds as a, landuses as b ";
   $listobject->querystring .= " where b.landusetype in ($crop_lutypes) ";
   $listobject->querystring .= " and b.hspflu = a.luname ";
   $listobject->querystring .= " and a.scenarioid = $scenarioid ";
   $listobject->querystring .= " and a.thisyear = $thisyear ";
   $listobject->querystring .= " and b.projectid = $projectid ";
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $listobject->performQuery();

   $listobject->querystring = "update lu_uptakes set jan = a.jan, feb = a.feb, ";
   $listobject->querystring .= " mar = a.mar, apr = a.apr, may = a.may, jun = a.jun, ";
   $listobject->querystring .= " jul = a.jul, aug = a.aug, sep = a.sep, oct = a.oct, ";
   $listobject->querystring .= " nov = a.nov, dec = a.dec ";
   $listobject->querystring .= " from local_apply as a ";
   $listobject->querystring .= " where lu_uptakes.subshedid = a.subshedid ";
   $listobject->querystring .= " and a.scenarioid = $scenarioid ";
   $listobject->querystring .= " and lu_uptakes.luname = a.luname ";
   $listobject->querystring .= " and a.thisyear = $thisyear ";
   $listobject->querystring .= " and a.source_type = 'uptake' ";
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $listobject->performQuery();

   # null out the ones with zero acres
   $listobject->querystring = "update lu_uptakes set jan = $nullval, feb = $nullval, ";
   $listobject->querystring .= " mar = $nullval, apr = $nullval, may = $nullval, jun = $nullval, ";
   $listobject->querystring .= " jul = $nullval, aug = $nullval, sep = $nullval, oct = $nullval, ";
   $listobject->querystring .= " nov = $nullval, dec = $nullval ";
   $listobject->querystring .= " from scen_subsheds as a ";
   $listobject->querystring .= " where lu_uptakes.subshedid = a.subshedid ";
   $listobject->querystring .= " and lu_uptakes.luname = a.luname ";
   $listobject->querystring .= " and a.luarea <= 0";
   $listobject->querystring .= " and a.scenarioid = $scenarioid ";
   $listobject->querystring .= " and a.thisyear = $thisyear ";
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $listobject->performQuery();

   if ($makecsv) {
      # temporarily disabled
      #print("<a href='$outurl/$scenarioid.$thisyear.tar.gz'>All outfiles as a single tar</a><hr>");
   }


   $listobject->querystring = "( select a.landseg as lseg, a.luname as lu, 'nitr' as constituent, b.jan, b.feb, b.mar, b.apr, b.may, b.jun, b.jul, b.aug, b.sep, b.oct, b.nov, b.dec from temp_lrsegs as a, lu_uptakes as b where a.subshedid = b.subshedid and a.luname = b.luname order by a.subshedid )";
   $listobject->querystring .= " UNION (select a.landseg as lseg, a.luname as lu, 'phos' as constituent, b.jan, b.feb, b.mar, b.apr, b.may, b.jun, b.jul, b.aug, b.sep, b.oct, b.nov, b.dec from temp_lrsegs as a, lu_uptakes as b where a.subshedid = b.subshedid and a.luname = b.luname order by a.subshedid) ";
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $listobject->performQuery();

   $outarr = nestArraySprintf("%3s,%3s,%4s,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f", $listobject->queryrecords);

   if ((count($listobject->queryrecords) > 0) ) {

      $colnames = array(join(",",array_keys($listobject->queryrecords[0])));
      $filename = "uptakecurve" . "_$runname" . "_$thisyear.csv";
      putArrayToFilePlatform("$outdir/$filename", $colnames,1,'unix');
      putArrayToFilePlatform("$outdir/$filename", $outarr,0,'unix');
      print("<a href='$outurl/$filename'>Uptake Curve for $thisyear</a><br>");
      array_push($outfiles, "$outdir/$filename");


      print("Uptake Mass Balance (both min and max should be 1.0 (or darn close):<br>");
      # show summary of uptakes, to make sure that all are equal to 1.0
      $listobject->querystring = "select a.luname, max(a.jan + a.feb + a.mar + a.apr+ a.may+ a.jun+ a.jul+ a.aug+ a.sep+ a.oct+ a.nov+ a.dec) as maxuptake, min(a.jan+ a.feb+ a.mar+ a.apr+ a.may+ a.jun+ a.jul+ a.aug+ a.sep+ a.oct+ a.nov+ a.dec) as minuptake from lu_uptakes as a where 1 = 1 and (a.jan+ a.feb+ a.mar+ a.apr+ a.may+ a.jun+ a.jul+ a.aug+ a.sep+ a.oct+ a.nov+ a.dec) <> ( $nullval * 12.0) and $anwc group by a.luname";
      $listobject->performQuery();
      $listobject->showList();

   }


   # append the fils to the output tar
   # temporarily disabled
   if ($makecsv) {
      #$tar->create($outfiles) or die ("Could not create tar archive<br>");
   }

}


function makeCanopyCurveFiles($listobject, $projectid, $scenarioid, $runname, $outurl, $outdir, $thisyear, $allsegs, $maxc, $crop_lutypes, $res_canopy, $nullval, $debug) {
   print("Generating Cover Values <br>");
   # grab the basic data for these tables
   $listobject->querystring = " select a.landseg as land, a.luname, a.thisyear, ";
   $listobject->querystring .= "    $nullval::float as jan, $nullval::float as feb, ";
   $listobject->querystring .= "    $nullval::float as mar, $nullval::float as apr, ";
   $listobject->querystring .= "    $nullval::float as may, $nullval::float as jun, ";
   $listobject->querystring .= "    $nullval::float as jul, $nullval::float as aug, ";
   $listobject->querystring .= "    $nullval::float as sep, $nullval::float as oct, ";
   $listobject->querystring .= "    $nullval::float as nov, $nullval::float as dec ";
   $listobject->querystring .= " into temp table tmp_cover ";
   $listobject->querystring .= " from temp_landsegs as a ";
   $listobject->querystring .= " where a.luname in ( ";
   $listobject->querystring .= "         select luname ";
   $listobject->querystring .= "         from local_apply ";
   $listobject->querystring .= "         where scenarioid = $scenarioid ";
   $listobject->querystring .= "            and curvetype in ( $res_canopy ) ";
   $listobject->querystring .= "         group by luname ";
   $listobject->querystring .= "    ) ";
   $listobject->querystring .= " group by a.landseg, a.luname, a.thisyear ";
   if ($debug) { print("$listobject->querystring<br>"); }
   $listobject->performQuery();

   $listobject->querystring = " select a.landseg as land, b.luname, a.thisyear, ";
   $listobject->querystring .= "    sum(b.jan) as jan, sum(b.feb) as feb, ";
   $listobject->querystring .= "    sum(b.mar) as mar, sum(b.apr) as apr, ";
   $listobject->querystring .= "    sum(b.may) as may, sum(b.jun) as jun, ";
   $listobject->querystring .= "    sum(b.jul) as jul, sum(b.aug) as aug, ";
   $listobject->querystring .= "    sum(b.sep) as sep, sum(b.oct) as oct, ";
   $listobject->querystring .= "    sum(b.nov) as nov, sum(b.dec) as dec ";
   $listobject->querystring .= " into temp table tmp_crfacts ";
   $listobject->querystring .= " from temp_landsegs as a, local_apply as b ";
   $listobject->querystring .= " where a.luname in ( ";
   $listobject->querystring .= "         select luname ";
   $listobject->querystring .= "         from local_apply ";
   $listobject->querystring .= "         where scenarioid = $scenarioid ";
   $listobject->querystring .= "            and curvetype in ( $res_canopy ) ";
   $listobject->querystring .= "         group by luname ";
   $listobject->querystring .= "    ) ";
   $listobject->querystring .= "    and b.luname = a.luname ";
   $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and b.thisyear = $thisyear ";
   $listobject->querystring .= "    and a.subshedid = b.subshedid ";
   $listobject->querystring .= "    and b.curvetype in ( $res_canopy ) ";
   $listobject->querystring .= " group by a.landseg, b.luname, a.thisyear ";
   if ($debug) { print("$listobject->querystring<br>"); }
   $listobject->performQuery();


   $listobject->querystring = " update tmp_cover set ";
   $listobject->querystring .= "    jan = ";
   $listobject->querystring .= "       CASE WHEN a.jan < $maxc THEN a.jan ";
   $listobject->querystring .= "       ELSE $maxc ";
   $listobject->querystring .= "       END, ";
   $listobject->querystring .= "    feb = ";
   $listobject->querystring .= "       CASE WHEN a.feb < $maxc THEN a.feb ";
   $listobject->querystring .= "       ELSE $maxc ";
   $listobject->querystring .= "       END, ";
   $listobject->querystring .= "    mar = ";
   $listobject->querystring .= "       CASE WHEN a.mar < $maxc THEN a.mar ";
   $listobject->querystring .= "       ELSE $maxc ";
   $listobject->querystring .= "       END, ";
   $listobject->querystring .= "    apr = ";
   $listobject->querystring .= "       CASE WHEN a.apr < $maxc THEN a.apr ";
   $listobject->querystring .= "       ELSE $maxc ";
   $listobject->querystring .= "       END, ";
   $listobject->querystring .= "    may = ";
   $listobject->querystring .= "       CASE WHEN a.may < $maxc THEN a.may ";
   $listobject->querystring .= "       ELSE $maxc ";
   $listobject->querystring .= "       END, ";
   $listobject->querystring .= "    jun = ";
   $listobject->querystring .= "       CASE WHEN a.jun < $maxc THEN a.jun ";
   $listobject->querystring .= "       ELSE $maxc ";
   $listobject->querystring .= "       END, ";
   $listobject->querystring .= "    jul = ";
   $listobject->querystring .= "       CASE WHEN a.jul < $maxc THEN a.jul ";
   $listobject->querystring .= "       ELSE $maxc ";
   $listobject->querystring .= "       END, ";
   $listobject->querystring .= "    aug = ";
   $listobject->querystring .= "       CASE WHEN a.aug < $maxc THEN a.aug ";
   $listobject->querystring .= "       ELSE $maxc ";
   $listobject->querystring .= "       END, ";
   $listobject->querystring .= "    sep = ";
   $listobject->querystring .= "       CASE WHEN a.sep < $maxc THEN a.sep ";
   $listobject->querystring .= "       ELSE $maxc ";
   $listobject->querystring .= "       END, ";
   $listobject->querystring .= "    oct = ";
   $listobject->querystring .= "       CASE WHEN a.oct < $maxc THEN a.oct ";
   $listobject->querystring .= "       ELSE $maxc ";
   $listobject->querystring .= "       END, ";
   $listobject->querystring .= "    nov = ";
   $listobject->querystring .= "       CASE WHEN a.nov < $maxc THEN a.nov ";
   $listobject->querystring .= "       ELSE $maxc ";
   $listobject->querystring .= "       END, ";
   $listobject->querystring .= "    dec = ";
   $listobject->querystring .= "       CASE WHEN a.dec < $maxc THEN a.dec ";
   $listobject->querystring .= "       ELSE $maxc ";
   $listobject->querystring .= "       END ";
   $listobject->querystring .= " from tmp_crfacts as a ";
   $listobject->querystring .= " where tmp_cover.land = a.land ";
   $listobject->querystring .= "    and tmp_cover.luname = a.luname ";
   if ($debug) { print("$listobject->querystring<br>"); }
   $listobject->performQuery();

   $listobject->querystring = "  select luname ";
   $listobject->querystring .= " from local_apply ";
   $listobject->querystring .= " where scenarioid = $scenarioid ";
   $listobject->querystring .= "    and curvetype in ( $res_canopy ) ";
   $listobject->querystring .= " group by luname ";
   if ($debug) { print("$listobject->querystring<br>"); }
   $listobject->performQuery();
   $lrecs = $listobject->queryrecords;

   foreach ($lrecs as $lus) {
      $ln = $lus['luname'];

      $listobject->querystring = "  select * ";
      $listobject->querystring .= " from tmp_cover ";
      $listobject->querystring .= " where luname = '$ln' ";
      $listobject->querystring .= " order by land ";
      if ($debug) { print("$listobject->querystring<br>"); }
      $listobject->performQuery();

      $outarr = nestArraySprintf("%3s,%3s,%4s,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f", $listobject->queryrecords);

      $colnames = array(join(",",array_keys($listobject->queryrecords[0])));
      $filename = "land_cover_$ln" . "_$runname" . "_$thisyear.csv";
      putArrayToFilePlatform("$outdir/$filename", $colnames,1,'unix');
      putArrayToFilePlatform("$outdir/$filename", $outarr,0,'unix');
      print("<a href='$outurl/$filename'>Cover Curve for $ln $thisyear</a><br>");
      array_push($outfiles, "$outdir/$filename");
   }

   # append the fils to the output tar
   # temporarily disabled
   if ($makecsv) {
      #$tar->create($outfiles) or die ("Could not create tar archive<br>");
   }
}


function makeAnnualUptakeFiles($listobject, $projectid, $scenarioid, $runname, $outurl, $outdir, $thisyear, $allsegs, $crop_lutypes, $nullval, $debug) {
   # crop need tables - Annual total uptake

   if ($listobject->tableExists("totalneed_out") ) {
      $listobject->querystring = "drop table totalneed_out ";
      $listobject->performQuery();
   }

   print("<b>Total uptake rate per month/year for </b> $groupname ($subwatersheds):<br>");

   $listobject->querystring = " create temp table totalneed_out as ";
   $listobject->querystring .= " ( select a.landseg as lseg, a.luname as lu, a.luarea,";
   $listobject->querystring .= " 'nitr' as constituent, b.n_urratio * b.uptake_n as max_uptake ";
   $listobject->querystring .= " from (select subshedid, landseg, luname, sum(luarea) as luarea ";
   $listobject->querystring .= "    from temp_lrsegs as a ";
   $listobject->querystring .= "    group by subshedid, landseg, luname ";
   $listobject->querystring .= " ) as a left outer join ";
   $listobject->querystring .= " scen_subsheds as b ";
   $listobject->querystring .= " on ( a.subshedid = b.subshedid ";
   $listobject->querystring .= "    and a.luname = b.luname ";
   $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and b.thisyear = $thisyear ";
   $listobject->querystring .= " and b.projectid = $projectid ) ";
   $listobject->querystring .= " order by a.landseg ";
   $listobject->querystring .= " ) UNION ";
   $listobject->querystring .= " (select a.landseg as lseg, a.luname as lu, a.luarea, ";
   $listobject->querystring .= "  'phos' as constituent, b.p_urratio * uptake_p as max_uptake ";
   $listobject->querystring .= "  from ";
   $listobject->querystring .= "     (select subshedid, landseg, luname, ";
   $listobject->querystring .= "       sum(luarea) as luarea ";
   $listobject->querystring .= "     from temp_lrsegs as a ";
   $listobject->querystring .= "     group by subshedid, landseg, luname ";
   $listobject->querystring .= "     ) as a left outer join ";
   $listobject->querystring .= " scen_subsheds as b ";
   $listobject->querystring .= " on ( a.subshedid = b.subshedid ";
   $listobject->querystring .= "    and a.luname = b.luname ";
   $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and b.thisyear = $thisyear ";
   $listobject->querystring .= " and b.projectid = $projectid ) ";
   $listobject->querystring .= " order by a.landseg )";
   if ($debug) { print("$listobject->querystring<br>"); }


   $listobject->performQuery();

   $listobject->querystring = " update totalneed_out set max_uptake = $nullval ";
   $listobject->querystring .= " where luarea <= 0 or max_uptake is null";
   if ($debug) { print("$listobject->querystring<br>"); }
   $listobject->performQuery();

   $listobject->querystring = " select lseg, lu, constituent, max_uptake from totalneed_out ";
   $listobject->querystring .= " where lu in ( select hspflu from landuses  ";
   $listobject->querystring .= " where landusetype in ($crop_lutypes) ";
   $listobject->querystring .= "    and projectid = $projectid ";
   $listobject->querystring .= "    and hspflu not in ('pas', 'trp', 'npa') ) ";
   $listobject->querystring .= " order by lseg ";
   if ($debug) { print("$listobject->querystring<br>"); }
   $listobject->performQuery();


  # print("$listobject->querystring ; <br>");

  # $listobject->performQuery();

   $outarr = $listobject->queryrecords;


   $colnames = array(array_keys($outarr[0]));
   $pname = ereg_replace("[^a-z^A-Z^0-9]",'',$groupname);
   $filename = "max_uptake" . "_$runname" . "_$thisyear.csv";
   putDelimitedFile("$outdir/$filename", $colnames, ',',1,'unix');
   putDelimitedFile("$outdir/$filename", $outarr, ',',0,'unix');
   print("<a href='$outurl/$filename'>Download Maximum Uptake File for $thisyear</a><br>");


}
?>