<?php

##########################################
#         Library of Land Use
#       and BMP Transformation
#           functions
##########################################

function makeTempScenarioTable($listobject, $basetable, $desttable, $columns, $landuses, $subsheds, $thisyear, $otherwhere, $scenarioid, $debug, $mktemp = 1) {

   # use postgresql style temp syntax?
   if ($mktemp) {
      $tstring = 'temp';
   }

   if (count($landuses) > 0) {
      $lulist = "'" . join("','", $landuses) . "'";
      $lucond = " luname in ($lulist) ";
   } else {
      $lucond = ' 1 = 1 ';
   }
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
   } else {
      $subshedcond = ' 1 = 1 ';
   }
   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear in ($thisyear) ";
   } else {
      $yrcond = ' 1 = 1 ';
   }
   if (!(strlen($columns) > 0)) {
      $columns = '*';
   }
   if (!(strlen($otherwhere) > 0)) {
      $otherwhere = '(1 = 1)';
   }
   $listobject->querystring = " select $columns ";
   $listobject->querystring .= " into $tstring $desttable ";
   $listobject->querystring .= " from $basetable ";
   $listobject->querystring .= " where $lucond";
   $listobject->querystring .= "   and $subshedcond ";
   $listobject->querystring .= "   and $yrcond ";
   $listobject->querystring .= "   and $otherwhere ";
   $listobject->querystring .= "   and scenarioid = $scenarioid ";
   if ($debug) {
      print("$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

}

function deleteFromScenarioTable($listobject, $basetable, $landuses, $subsheds, $thisyear, $otherwhere, $scenarioid, $debug) {

   # use postgresql style temp syntax?
   if ($mktemp) {
      $tstring = 'temp';
   }

   if (count($landuses) > 0) {
      $lulist = "'" . join("','", $landuses) . "'";
      $lucond = " luname in ($lulist) ";
   } else {
      $lucond = ' 1 = 1 ';
   }
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
   } else {
      $subshedcond = ' 1 = 1 ';
   }
   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear in ($thisyear) ";
   } else {
      $yrcond = ' 1 = 1 ';
   }
   if (!(strlen($columns) > 0)) {
      $columns = '*';
   }
   if (!(strlen($otherwhere) > 0)) {
      $otherwhere = '(1 = 1)';
   }
   $listobject->querystring = " DELETE from $basetable ";
   $listobject->querystring .= " where $lucond";
   $listobject->querystring .= "   and $subshedcond ";
   $listobject->querystring .= "   and $yrcond ";
   $listobject->querystring .= "   and $otherwhere ";
   $listobject->querystring .= "   and scenarioid = $scenarioid ";
   if ($debug) {
      print("$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

}

function performBestFitLU($listobject, $goutdir, $goutpath, $landuses, $allsegs, $srcyears, $targetyears, $scenarioid, $debug) {

   # create/show best fit table

   # isoalte the areas of interest in the land use table - creates table templu
   makeTempScenarioTable($listobject, 'scen_lrsegs', 'templu', 'oid as dupoid, *', $landuses, $allsegs, $srcyears, '', $scenarioid, $debug);

   # fill in the gaps, so that we have all of our years in the resulting graph
   $listobject->querystring = "select min(thisyear) as minyr, max(thisyear) as maxyr from templu";
   $listobject->performQuery();
   $minyr = $listobject->getRecordValue(1,'minyr');
   $maxyr = $listobject->getRecordValue(1,'maxyr');
   $yrar = array($minyr, $maxyr);
   $tar = array();
   if (strlen($targetyears) > 0) {
      $tar = split(",", $targetyears);
   }
   $allyrs = array_merge($yrar, $tar);
   $loyr = min($allyrs);
   $hiyr = max($allyrs);
   $exyrs = '';
   $exdel = '';
   for ($j = $loyr; $j <= $hiyr; $j++) {
      $exyrs .= "$exdel" . $j;
      $exdel = ',';
   }
   if ($debug) { print("Years: $exyrs<br>"); }


   genericExtrap($listobject, $exyrs, 'templu', 'lubestfit', 'thisyear', 'subshedid, landseg, riverseg, lrseg, luname', 'luarea', 0, 1, 0, '', 0, $debug, 1, 1);

   $listobject->querystring = "  select sum(m) / sum(ym) as avgpct ";
   $listobject->querystring .= " FROM lubestfit_regtable ";
   $listobject->querystring .= " WHERE ym > 0 ";

}

function applyBestFitCrops($listobject, $goutdir, $goutpath, $landuses, $subsheds, $targetyears, $scenarioid, $yieldcols, $debug) {

   # create/show best fit table

   if (count($landuses) > 0) {
      $lulist = "'" . join("','", $landuses) . "'";
      $lucond = " luname in ($lulist) ";
   } else {
      $lucond = ' 1 = 1 ';
   }
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
   } else {
      $subshedcond = ' 1 = 1 ';
   }
   if (strlen($targetyears) > 0) {
      $yrcond = " thisyear in ($targetyears) ";
      $ayrcond = " a.thisyear in ($targetyears) ";
   } else {
      $yrcond = ' 1 = 1 ';
      $ayrcond = ' 1 = 1 ';
   }

   # delete old records from crops (these are optional)
   $listobject->querystring = "  delete from scen_crops ";
   $listobject->querystring .= " where scenarioid = $scenarioid ";
   $listobject->querystring .= " and $lucond ";
   $listobject->querystring .= " and $yrcond ";
   $listobject->querystring .= " and subshedid in ";
   $listobject->querystring .= "    (select subshedid from scen_lrsegs ";
   $listobject->querystring .= "     where scenarioid = $scenarioid ";
   $listobject->querystring .= "        and $subshedcond ";
   $listobject->querystring .= "     group by subshedid ";
   $listobject->querystring .= "    ) ";
   if ($debug) { print(" $listobject->querystring ; <br>"); }
   $listobject->performQuery();

   # insert best fit crops
   $listobject->querystring = "  insert into scen_crops (scenarioid, thisyear, subshedid, luname, ";
   $listobject->querystring .= "    croparea, cropname) ";
   $listobject->querystring .= " select $scenarioid, thisyear, subshedid, luname, croparea, cropname  ";
   $listobject->querystring .= " from multi_cropbestfit ";
   $listobject->querystring .= " where $yrcond ";
   if ($debug) { print(" $listobject->querystring ; <br>"); }
   $listobject->performQuery();

   # insert blank values if any are missing from inputyields (must have an entry for these)
   $listobject->querystring = "  insert into inputyields (scenarioid, projectid, thisyear, luname, subshedid) ";
   $listobject->querystring .= " select $scenarioid, $projectid, $thisyear, '$landuses', a.subshedid  ";
   $listobject->querystring .= " from (select subshedid from scen_subsheds where  ";
   $listobject->querystring .= "        scenarioid = $scenarioid  ";
   $listobject->querystring .= "        and $subshedcond  ";
   $listobject->querystring .= "        and $yrcond  ";
   $listobject->querystring .= "        group by subshedid ";
   $listobject->querystring .= "    ) as a ";
   $listobject->querystring .= "  where subshedid not in  ";
   $listobject->querystring .= "    (select subshedid from scen_lrsegs ";
   $listobject->querystring .= "     where scenarioid = $scenarioid ";
   $listobject->querystring .= "        and $subshedcond ";
   $listobject->querystring .= "     group by subshedid ";
   $listobject->querystring .= "    ) ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();


   # modify inputyield records
   $listobject->querystring = "  update inputyields SET ";
   $yieldar = split(',',$yieldcols);
   $idel = '';
   foreach ($yieldar as $thiscol) {
      $thiscol = ltrim(rtrim($thiscol));
      $listobject->querystring .= "$idel $thiscol = a.$thiscol ";
      $idel = ',';
   }
   $listobject->querystring .= " from multi_yieldbestfit as a ";
   $listobject->querystring .= " where inputyields.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and inputyields.thisyear = a.thisyear ";
   $listobject->querystring .= "    and inputyields.luname = a.luname ";
   $listobject->querystring .= "    and inputyields.subshedid = a.subshedid ";
   $listobject->querystring .= "    and $ayrcond ";
   if ($debug) { print(" $listobject->querystring ; <br>"); }
   $listobject->performQuery();
}

function performBestFitCrops($listobject, $goutdir, $goutpath, $landuses, $subsheds, $srcyears, $targetyears, $scenarioid, $yieldcols, $debug) {

   # create/show best fit table

   if (count($landuses) > 0) {
      $lulist = "'" . join("','", $landuses) . "'";
      $lucond = " luname in ($lulist) ";
   } else {
      $lucond = ' 1 = 1 ';
   }
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
   } else {
      $subshedcond = ' 1 = 1 ';
   }
   if (strlen($srcyears) > 0) {
      $yrcond = " thisyear in ($srcyears) ";
   } else {
      $yrcond = ' 1 = 1 ';
   }

   # isolate the areas of interest in temp tables
   $listobject->querystring = "  create temp table tmp_crops as ";
   $listobject->querystring .= " select * from scen_crops ";
   $listobject->querystring .= " where scenarioid = $scenarioid ";
   $listobject->querystring .= " and $lucond ";
   $listobject->querystring .= " and $yrcond ";
   $listobject->querystring .= " and subshedid in ";
   $listobject->querystring .= "    (select subshedid from scen_lrsegs ";
   $listobject->querystring .= "     where scenarioid = $scenarioid ";
   $listobject->querystring .= "        and $subshedcond ";
   $listobject->querystring .= "     group by subshedid ";
   $listobject->querystring .= "    ) ";
   if ($debug) { print(" $listobject->querystring ; <br>"); }
   $listobject->performQuery();

   $listobject->querystring = "  create temp table tmp_yields as ";
   $listobject->querystring .= " select * from inputyields ";
   $listobject->querystring .= " where scenarioid = $scenarioid ";
   $listobject->querystring .= " and $lucond ";
   $listobject->querystring .= " and $yrcond ";
   $listobject->querystring .= " and subshedid in ";
   $listobject->querystring .= "    (select subshedid from scen_lrsegs ";
   $listobject->querystring .= "     where scenarioid = $scenarioid ";
   $listobject->querystring .= "        and $subshedcond ";
   $listobject->querystring .= "     group by subshedid ";
   $listobject->querystring .= "    ) ";
   if ($debug) { print(" $listobject->querystring ; <br>"); }
   $listobject->performQuery();


   # fill in the gaps, so that we have all of our years in the resulting graph
   $listobject->querystring = "select min(thisyear) as minyr, max(thisyear) as maxyr from tmp_crops";
   if ($debug) { print(" $listobject->querystring ; <br>"); }
   $listobject->performQuery();
   $minyr = $listobject->getRecordValue(1,'minyr');
   $maxyr = $listobject->getRecordValue(1,'maxyr');
   if ( !(($minyr > 0) or ($maxyr > 0)) ) {
      $listobject->querystring = "select min(thisyear) as minyr, max(thisyear) as maxyr from tmp_yields";
      if ($debug) { print(" $listobject->querystring ; <br>"); }
      $listobject->performQuery();
   }
   $tar = array();
   if (strlen($targetyears) > 0) {
      $tar = array_values(split(",", $targetyears));
   } else {
      print("<b>Error:</b> You must enter at least one target year. <br>");
      return;
   }
   $allyrs = $tar;
   $yrar = array();
   if (count($listobject->queryrecords) > 0) {
      $minyr = $listobject->getRecordValue(1,'minyr');
      $maxyr = $listobject->getRecordValue(1,'maxyr');
      $yrar = array($minyr, $maxyr);
      if ( ($minyr > 0) or ($maxyr > 0) ) {
         $allyrs = array_merge($yrar, $tar);
      } else {
         print("<b>Error: </b>No source years found. <br>");
         return;
      }
   } else {
      print("<b>Error: </b>No source years found. <br>");
      return;
   }
   if ($debug) { print_r($tar); }
   $loyr = min($allyrs);
   $hiyr = max($allyrs);
   $exyrs = '';
   $exdel = '';
   for ($j = $loyr; $j <= $hiyr; $j++) {
      $exyrs .= "$exdel" . $j;
      $exdel = ',';
   }
   if ($debug) { print("Years: $exyrs<br>"); }


   genericMultiExtrap($listobject, $exyrs, 'tmp_crops', 'multi_cropbestfit', 'thisyear', 'subshedid, cropname, luname', 'croparea', 0, 1, 0, '', 0, $debug, 1, 1);


   genericMultiExtrap($listobject, $exyrs, 'tmp_yields', 'multi_yieldbestfit', 'thisyear', 'subshedid, luname', $yieldcols, 0, 1, 0, '', 0, $debug, 1, 1);

   # now, since the columns nm_planbase, maxyieldtarget and optyieldtarget are integers,
   #we insure that this is the case
   $listobject->querystring = "  update multi_yieldbestfit set nm_planbase = floor(nm_planbase), ";
   $listobject->querystring .= "    optyieldtarget = floor(optyieldtarget ), ";
   $listobject->querystring .= "    maxyieldtarget = floor(maxyieldtarget ) ";
   $listobject->performQuery();

}

function performBestFitBMP($listobject, $typeid, $allsegs, $srcyears, $targetyears, $scenarioid, $projectid, $rejectsingle, $debug) {

   # create/show best fit table
   # $rejectsingle - 1/0 - whether to reject entries with only 1 point

   if (strlen($typeid) > 0) {
      # is we pass in -1 as the typeid, all BMPs will be done simultaneously
      $bmpwhere = " bmpname in (select bmpname from bmp_subtypes where ( (typeid = $typeid) or ($typeid = -1) ) and projectid = $projectid) ";
   } else {
      $bmpwhere = '';
   }

   # isoalte the areas of interest in the land use table - creates table templu
   makeTempScenarioTable($listobject, 'scen_lrseg_bmps', 'tempbmp', 'oid as dupoid, *', array(), $allsegs, $srcyears, $bmpwhere, $scenarioid, $debug);

   # fill in the gaps, so that we have all of our years in the resulting graph

   if ($debug) {
      $listobject->querystring = "select * from tempbmp";
      $listobject->performQuery();
      $listobject->showList();
   }

   $listobject->querystring = "select min(thisyear) as minyr, max(thisyear) as maxyr from tempbmp";
   $listobject->performQuery();
   $minyr = $listobject->getRecordValue(1,'minyr');
   $maxyr = $listobject->getRecordValue(1,'maxyr');
   $yrar = array($minyr, $maxyr);
   $tar = array();
   if (strlen($targetyears) > 0) {
      $tar = split(",", $targetyears);
   }
   $allyrs = array_merge($yrar, $tar);

   $loyr = min($allyrs);
   $hiyr = max($allyrs);
   if ($loyr == '') {
      $loyr = $hiyr;
   }
   $exyrs = '';
   $exdel = '';
   for ($j = $loyr; $j <= $hiyr; $j++) {
      $exyrs .= "$exdel" . $j;
      $exdel = ',';
   }
   if ($debug) {print("Years: $exyrs<br>");}

   genericExtrap($listobject, $exyrs, 'tempbmp', 'bmpbestfit', 'thisyear', 'lrseg, bmpname', 'bmparea', 0, 1, 0, '', $rejectsingle, $debug, 1, 1);


   if ($debug) {
      $listobject->querystring = "select * from bmpbestfit";
      $listobject->performQuery();
      $listobject->showList();
   }


}



function applyBestFitPops($listobject, $sourceids, $allsegs, $targetyears, $scenarioid, $projectid, $debug) {

   $thisdate = date('r',time());
   if (strlen(ltrim(rtrim($targetyears))) > 0) {
      $yrcond = " thisyear in ($targetyears) ";
   } else {
      $yrcond = ' (1 = 1) ';
   }

   if (count($sourceids) > 0) {
      $srclist = "'" . join("','", $sourceids) . "'";
      $srccond = " sourceid in ($srclist) ";
   } else {
      $srccond = ' (1 = 1) ';
   }

   if (count($allsegs) > 0) {
      $sslist = "'" . join("','", $allsegs) . "'";
      $subcond = " subshedid in ";
      $subcond .= "   (select subshedid ";
      $subcond .= "    from tmp_srcextrap ";
      $subcond .= "    where $yrcond ";
      $subcond .= "       AND $srccond ) ";
   } else {
      $subcond = ' (1 = 1) ';
   }

   # delete records to be replaced in original table
   $listobject->querystring = " delete from scen_sourcepops ";
   $listobject->querystring .= " WHERE $yrcond ";
   $listobject->querystring .= "    AND $srccond ";
   $listobject->querystring .= "    AND $subcond ";
   $listobject->startSplit();
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

   # now update records that are already present in the original table
   $listobject->querystring = " insert into scen_sourcepops ( scenarioid, subshedid, sourceid, ";
   $listobject->querystring .= " thisyear, sourcepop, rundate, src_citation ) ";
   $listobject->querystring .= " SELECT $scenarioid, subshedid, sourceid, ";
   $listobject->querystring .= "    thisyear, actualpop, '$thisdate'::timestamp, 30 ";
   $listobject->querystring .= " FROM tmp_srcextrap ";
   $listobject->querystring .= " WHERE $yrcond ";
   $listobject->querystring .= "    AND $srccond ";
   $listobject->startSplit();
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();
   $split = $listobject->startSplit();
   if ($debug) {
      print("Query Time: $split<br>");
      $listobject->showList();
   }

   #

}


function applyBestFitLU($listobject, $landuses, $allsegs, $targetyears, $scenarioid, $projectid, $debug) {

   $thisdate = date('r',time());

   deleteFromScenarioTable($listobject, 'scen_lrsegs',  $landuses, $allsegs, $targetyears, '', $scenarioid, $debug);

   # now update records that are already present in the original table
   $listobject->querystring = " insert into scen_lrsegs ( scenarioid, projectid, subshedid, landseg, riverseg, lrseg, ";
   $listobject->querystring .= " thisyear, luname, luarea, rundate, src_citation ) ";
   $listobject->querystring .= " SELECT $scenarioid as scenarioid, $projectid, subshedid, landseg, riverseg, lrseg, ";
   $listobject->querystring .= "    thisyear, luname, luarea, '$thisdate'::timestamp as rundate, 30 as src_citation ";
   $listobject->querystring .= " FROM lubestfit ";
   $listobject->querystring .= " WHERE thisyear in ($targetyears) ";
   $listobject->startSplit();
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();
   $split = $listobject->startSplit();
   if ($debug) {
      print("Query Time: $split<br>");
      $listobject->showList();
   }

   #

}

function applyBestFitBMP($listobject, $typeid, $allsegs, $targetyears, $scenarioid, $projectid, $debug) {


   #############################3
   ###### STILL NOT FINISHED
   ###############################
   #### NEED TO CHECK THAT I INSERT WHEN THERE ARE NO RECORDS TO BEGIN WITH
   #### NEED TO CHECK FOR THE LAND USE EXTRAPOLATION ROUTINE ALSO
   ###############################
   # create/show best fit table
   if (strlen($typeid) > 0) {
      $bmpwhere = " bmpname in (select bmpname from bmp_subtypes where ( (typeid = $typeid) or ($typeid = -1) ) and projectid = $projectid) ";
   } else {
      return;
   }

   deleteFromScenarioTable($listobject, 'scen_lrseg_bmps', array(), $allsegs, $targetyears, $bmpwhere, $scenarioid, $debug);

   # now update records that are already present in the original table
   $listobject->querystring = " insert into scen_lrseg_bmps ( scenarioid, lrseg, ";
   $listobject->querystring .= " thisyear, bmpname, bmparea, src_citation ) ";
   $listobject->querystring .= " SELECT $scenarioid as scenarioid, lrseg, ";
   $listobject->querystring .= "    thisyear, bmpname, bmparea, 30 as src_citation ";
   $listobject->querystring .= " FROM bmpbestfit ";
   $listobject->querystring .= " WHERE thisyear in ($targetyears) ";
   $listobject->querystring .= "    AND $bmpwhere ";
   $listobject->startSplit();
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();
   $split = $listobject->startSplit();
   if ($debug) {
      print("Query Time: $split<br>");
      $listobject->showList();
   }


   #

}

function graphBestFitLU($listobject, $goutdir, $goutpath, $landuses, $allsegs, $srcyears, $targetyears, $scenarioid, $debug) {

   $lus = join(", ", $landuses);
   # get records for all input years
   # this query assumes the the temp tables 'lubestfit', and 'templu' have already been created
   $listobject->querystring = " select  ";
   $listobject->querystring .= "    CASE  ";
   $listobject->querystring .= "       WHEN b.thisyear IS NULL THEN a.thisyear ";
   $listobject->querystring .= "       ELSE b.thisyear ";
   $listobject->querystring .= "    END as thisyear, ";
   $listobject->querystring .= "    CASE  ";
   $listobject->querystring .= "       WHEN a.thisyear IS NULL THEN 0.0 ";
   $listobject->querystring .= "       ELSE sum(a.luarea) ";
   $listobject->querystring .= "    END as totalarea ";
   $listobject->querystring .= " from templu as a  ";
   $listobject->querystring .= " full join  ";
   $listobject->querystring .= " (select thisyear from lubestfit group by thisyear) as b ";
   $listobject->querystring .= " on (a.thisyear = b.thisyear) ";
   $listobject->querystring .= " group by a.thisyear, b.thisyear ";
   $listobject->querystring .= " order by b.thisyear ";
   #print("$listobject->querystring <br>");
   $listobject->performquery();
   #$listobject->showList();
   $lurecs = $listobject->queryrecords;

   # get all best-fit years
   $listobject->querystring = "select thisyear, sum(luarea) as totalarea from lubestfit group by thisyear order by thisyear";
   $listobject->performquery();
   $bfrecs = $listobject->queryrecords;

   # get ONLY requested Best-Fit years
   if (strlen($targetyears) > 0) {
      $listobject->querystring = " select  ";
      $listobject->querystring .= "    CASE  ";
      $listobject->querystring .= "       WHEN b.thisyear IS NULL THEN a.thisyear ";
      $listobject->querystring .= "       ELSE b.thisyear ";
      $listobject->querystring .= "    END as thisyear, ";
      $listobject->querystring .= "    CASE  ";
      $listobject->querystring .= "       WHEN a.thisyear IS NULL THEN 0.0 ";
      $listobject->querystring .= "       ELSE a.totalarea ";
      $listobject->querystring .= "    END as totalarea ";
      $listobject->querystring .= " from (select thisyear, sum(luarea) as totalarea from lubestfit where thisyear in ($targetyears) group by thisyear) as a  ";
      $listobject->querystring .= " full join  ";
      $listobject->querystring .= " (select thisyear from lubestfit group by thisyear) as b ";
      $listobject->querystring .= " on (a.thisyear = b.thisyear) ";
      $listobject->querystring .= " order by b.thisyear ";
      #print("$listobject->querystring <br>");
      $listobject->performquery();
      #$listobject->showList();
      $extraprecs = $listobject->queryrecords;
   }

   $lugraph = array();
   $lugraph['graphrecs'] = $lurecs;
   $lugraph['xcol'] = 'thisyear';
   $lugraph['ycol'] = 'totalarea';
   $lugraph['color'] = 'orange';
   $lugraph['ylegend'] = 'Historic';

   # a totally trasnparent (outline only) display of the best fit line
   $bfgraph = array();
   $bfgraph['graphrecs'] = $bfrecs;
   $bfgraph['xcol'] = 'thisyear';
   $bfgraph['ycol'] = 'totalarea';
   $bfgraph['color'] = 'brown';
   $bfgraph['ylegend'] = 'Best Fit';
   $bfgraph['alpha'] = 1.0;

   # selected records to extrapolate from the best fit in blue
   $extrapgraph = array();
   $extrapgraph['graphrecs'] = $extraprecs;
   $extrapgraph['xcol'] = 'thisyear';
   $extrapgraph['ycol'] = 'totalarea';
   $extrapgraph['color'] = 'blue';
   $extrapgraph['ylegend'] = 'Targeted';
   $extrapgraph['alpha'] = 0.3;
   $multibar = array('title'=>"Historic versus Best Fit for: $lus", 'xlabel'=>'Year', 'bargraphs'=>array($lugraph, $bfgraph, $extrapgraph));

   $bfgraph = showGenericMultiBar($goutdir, $goutpath, $multibar, $debug);
   return $bfgraph;

}


function graphBestFitBMP($listobject, $goutdir, $goutpath, $bmps, $allsegs, $srcyears, $targetyears, $scenarioid, $debug) {

   $bmpnames = join(", ", $bmps);
   # get records for all input years
   $listobject->querystring = " select  ";
   $listobject->querystring .= "    CASE  ";
   $listobject->querystring .= "       WHEN b.thisyear IS NULL THEN a.thisyear ";
   $listobject->querystring .= "       ELSE b.thisyear ";
   $listobject->querystring .= "    END as thisyear, ";
   $listobject->querystring .= "    CASE  ";
   $listobject->querystring .= "       WHEN a.thisyear IS NULL THEN 0.0 ";
   $listobject->querystring .= "       ELSE sum(a.bmparea) ";
   $listobject->querystring .= "    END as totalarea ";
   $listobject->querystring .= " from tempbmp as a  ";
   $listobject->querystring .= " full join  ";
   $listobject->querystring .= " (select thisyear from bmpbestfit group by thisyear) as b ";
   $listobject->querystring .= " on (a.thisyear = b.thisyear) ";
   $listobject->querystring .= " group by a.thisyear, b.thisyear ";
   $listobject->querystring .= " order by b.thisyear ";
   if ($debug) { print("$listobject->querystring <br>"); }
   $listobject->performquery();
   #$listobject->showList();
   $lurecs = $listobject->queryrecords;

   # get all best-fit years
   $listobject->querystring = "select thisyear, sum(bmparea) as totalarea from bmpbestfit group by thisyear order by thisyear";
   $listobject->performquery();
   $bfrecs = $listobject->queryrecords;

   # get ONLY requested Best-Fit years
   if (strlen($targetyears) > 0) {
      $listobject->querystring = " select  ";
      $listobject->querystring .= "    CASE  ";
      $listobject->querystring .= "       WHEN b.thisyear IS NULL THEN a.thisyear ";
      $listobject->querystring .= "       ELSE b.thisyear ";
      $listobject->querystring .= "    END as thisyear, ";
      $listobject->querystring .= "    CASE  ";
      $listobject->querystring .= "       WHEN a.thisyear IS NULL THEN 0.0 ";
      $listobject->querystring .= "       ELSE a.totalarea ";
      $listobject->querystring .= "    END as totalarea ";
      $listobject->querystring .= " from (select thisyear, sum(bmparea) as totalarea from bmpbestfit where thisyear in ($targetyears) group by thisyear) as a  ";
      $listobject->querystring .= " full join  ";
      $listobject->querystring .= " (select thisyear from bmpbestfit group by thisyear) as b ";
      $listobject->querystring .= " on (a.thisyear = b.thisyear) ";
      $listobject->querystring .= " order by b.thisyear ";
      if ($debug) { print("$listobject->querystring <br>"); }
      $listobject->performquery();
      #$listobject->showList();
      $extraprecs = $listobject->queryrecords;
   }

   $lugraph = array();
   $lugraph['graphrecs'] = $lurecs;
   $lugraph['xcol'] = 'thisyear';
   $lugraph['ycol'] = 'totalarea';
   $lugraph['color'] = 'orange';
   $lugraph['ylegend'] = 'Historic';

   # a totally trasnparent (outline only) display of the best fit line
   $bfgraph = array();
   $bfgraph['graphrecs'] = $bfrecs;
   $bfgraph['xcol'] = 'thisyear';
   $bfgraph['ycol'] = 'totalarea';
   $bfgraph['color'] = 'brown';
   $bfgraph['ylegend'] = 'Best Fit';
   $bfgraph['alpha'] = 1.0;

   # selected records to extrapolate from the best fit in blue
   $extrapgraph = array();
   $extrapgraph['graphrecs'] = $extraprecs;
   $extrapgraph['xcol'] = 'thisyear';
   $extrapgraph['ycol'] = 'totalarea';
   $extrapgraph['color'] = 'blue';
   $extrapgraph['ylegend'] = 'Targeted';
   $extrapgraph['alpha'] = 0.3;
   $multibar = array('title'=>"Historic versus Best Fit for: $bmpnames", 'xlabel'=>'Year', 'bargraphs'=>array($lugraph, $bfgraph, $extrapgraph));

   $bfgraph = showGenericMultiBar($goutdir, $goutpath, $multibar, $debug);
   return $bfgraph;

}

function performLinearLU($listobject, $goutdir, $goutpath, $landuses, $allsegs, $srcyears, $targetyears, $scenarioid, $debug) {
   # create/show best fit table
   makeTempLanduse($listobject, 'scen_lrsegs', 'temp table templu', $landuses, $allsegs, $srcyears, $scenarioid, $debug);
   # fill in the gaps, so that we have all of our years in the resulting graph
   $listobject->querystring = "select min(thisyear) as minyr, max(thisyear) as maxyr from templu";
   $listobject->performQuery();
   $minyr = $listobject->getRecordValue(1,'minyr');
   $maxyr = $listobject->getRecordValue(1,'maxyr');
   $yrar = array($minyr, $maxyr);
   $tar = array();
   if (strlen($targetyears) > 0) {
      $tar = split(",", $targetyears);
   }
   $allyrs = array_merge($yrar, $tar);
   $loyr = min($allyrs);
   $hiyr = max($allyrs);
   $exyrs = '';
   $exdel = '';
   for ($j = $loyr; $j <= $hiyr; $j++) {
      $exyrs .= "$exdel" . $j;
      $exdel = ',';
   }
   #print("Years: $exyrs<br>");


   genericExtrap($listobject, $exyrs, 'templu', 'lubestfit', 'thisyear', 'landseg, riverseg, lrseg, luname', 'luarea', 0, 1, 0, '', 0, $debug, 1, 1);

   # get records for all input years
   $listobject->querystring = " select  ";
   $listobject->querystring .= "    CASE  ";
   $listobject->querystring .= "       WHEN b.thisyear IS NULL THEN a.thisyear ";
   $listobject->querystring .= "       ELSE b.thisyear ";
   $listobject->querystring .= "    END as thisyear, ";
   $listobject->querystring .= "    CASE  ";
   $listobject->querystring .= "       WHEN a.thisyear IS NULL THEN 0.0 ";
   $listobject->querystring .= "       ELSE sum(a.luarea) ";
   $listobject->querystring .= "    END as totalarea ";
   $listobject->querystring .= " from templu as a  ";
   $listobject->querystring .= " full join  ";
   $listobject->querystring .= " (select thisyear from lubestfit group by thisyear) as b ";
   $listobject->querystring .= " on (a.thisyear = b.thisyear) ";
   $listobject->querystring .= " group by a.thisyear, b.thisyear ";
   $listobject->querystring .= " order by b.thisyear ";
   #print("$listobject->querystring <br>");
   $listobject->performquery();
   #$listobject->showList();
   $lurecs = $listobject->queryrecords;

   # get all best-fit years
   $listobject->querystring = "select thisyear, sum(luarea) as totalarea from lubestfit group by thisyear order by thisyear";
   $listobject->performquery();
   $bfrecs = $listobject->queryrecords;

   # get ONLY requested Best-Fit years
   if (strlen($targetyears) > 0) {
      $listobject->querystring = " select  ";
      $listobject->querystring .= "    CASE  ";
      $listobject->querystring .= "       WHEN b.thisyear IS NULL THEN a.thisyear ";
      $listobject->querystring .= "       ELSE b.thisyear ";
      $listobject->querystring .= "    END as thisyear, ";
      $listobject->querystring .= "    CASE  ";
      $listobject->querystring .= "       WHEN a.thisyear IS NULL THEN 0.0 ";
      $listobject->querystring .= "       ELSE a.totalarea ";
      $listobject->querystring .= "    END as totalarea ";
      $listobject->querystring .= " from (select thisyear, sum(luarea) as totalarea from lubestfit where thisyear in ($targetyears) group by thisyear) as a  ";
      $listobject->querystring .= " full join  ";
      $listobject->querystring .= " (select thisyear from lubestfit group by thisyear) as b ";
      $listobject->querystring .= " on (a.thisyear = b.thisyear) ";
      $listobject->querystring .= " order by b.thisyear ";
      #print("$listobject->querystring <br>");
      $listobject->performquery();
      #$listobject->showList();
      $extraprecs = $listobject->queryrecords;
   }

   $lugraph = array();
   $lugraph['graphrecs'] = $lurecs;
   $lugraph['xcol'] = 'thisyear';
   $lugraph['ycol'] = 'totalarea';
   $lugraph['color'] = 'orange';
   $lugraph['ylegend'] = 'Historic';

   # a totally trasnparent (outline only) display of the best fit line
   $bfgraph = array();
   $bfgraph['graphrecs'] = $bfrecs;
   $bfgraph['xcol'] = 'thisyear';
   $bfgraph['ycol'] = 'totalarea';
   $bfgraph['color'] = 'brown';
   $bfgraph['ylegend'] = 'Best Fit';
   $bfgraph['alpha'] = 1.0;

   # selected records to extrapolate from the best fit in blue
   $extrapgraph = array();
   $extrapgraph['graphrecs'] = $extraprecs;
   $extrapgraph['xcol'] = 'thisyear';
   $extrapgraph['ycol'] = 'totalarea';
   $extrapgraph['color'] = 'blue';
   $extrapgraph['ylegend'] = 'Selected';
   $extrapgraph['alpha'] = 0.3;
   $multibar = array('title'=>"Historic versus Best Fit for $lus", 'xlabel'=>'Year', 'bargraphs'=>array($lugraph, $bfgraph, $extrapgraph));

   $bfgraph = showGenericMultiBar($goutdir, $goutpath, $multibar, $debug);
   return $bfgraph;

}



function distributeLandUsePct($listobject, $subsheds, $thisyear, $chgpct, $srclus, $destlus, $scenarioid, $projectid, $debug) {

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subcond = " scen_lrsegs.lrseg in ( $sslist) ";
      $bsubcond = " b.lrseg in ($sslist) ";
      $csubcond = " c.lrseg in ($sslist) ";
   } else {
      $subcond = ' 1 = 1 ';
      $bsubcond = ' 1 = 1 ';
      $csubcond = ' 1 = 1 ';
   }

   $listobject->queryrecords = array();

   # insert new bmp results

   $listobject->querystring = " update scen_lrsegs set luarea = luarea + ($chgpct * b.eligarea * luarea / c.totalarea) ";
   # this gets total source area
   $listobject->querystring .= " from  ";
   $listobject->querystring .= "    ( select b.landseg, b.riverseg, b.lrseg, b.thisyear, sum(b.luarea) as eligarea ";
   $listobject->querystring .= "      from scen_lrsegs as b ";
   $listobject->querystring .= "      where b.scenarioid = $scenarioid ";
   $listobject->querystring .= "         and b.thisyear = $thisyear ";
   $listobject->querystring .= "         and $bsubcond ";
   $listobject->querystring .= "         and b.luname in ( $srclus ) ";
   $listobject->querystring .= "      group by b.landseg, b.riverseg, b.lrseg, b.thisyear ";
   $listobject->querystring .= "    ) as b, ";
   # this gets total destinations area
   $listobject->querystring .= "    ( select c.landseg, c.riverseg, c.lrseg, c.thisyear, sum(c.luarea) as totalarea ";
   $listobject->querystring .= "      from scen_lrsegs as c ";
   $listobject->querystring .= "      where c.scenarioid = $scenarioid ";
   $listobject->querystring .= "         and c.thisyear = $thisyear ";
   $listobject->querystring .= "         and $csubcond ";
   $listobject->querystring .= "         and c.luname in ( $destlus ) ";
   $listobject->querystring .= "         and c.luarea > 0 ";
   $listobject->querystring .= "      group by c.landseg, c.riverseg, c.lrseg, c.thisyear ";
   $listobject->querystring .= "    ) as c ";
   $listobject->querystring .= " WHERE scen_lrsegs.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and scen_lrsegs.thisyear = $thisyear ";
   $listobject->querystring .= "    and $subcond ";
   $listobject->querystring .= "    and scen_lrsegs.luname in ( $destlus ) ";
   $listobject->querystring .= "    and scen_lrsegs.landseg = b.landseg ";
   $listobject->querystring .= "    AND scen_lrsegs.landseg = c.landseg ";
   $listobject->querystring .= "    AND scen_lrsegs.riverseg = b.riverseg ";
   $listobject->querystring .= "    AND scen_lrsegs.riverseg = c.riverseg ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();


   # decrement source lus
   $listobject->querystring = " update scen_lrsegs set luarea = (1.0 - $chgpct) * luarea ";
   $listobject->querystring .= " WHERE scenarioid = $scenarioid ";
   $listobject->querystring .= "    and thisyear = $thisyear ";
   $listobject->querystring .= "    and $subcond ";
   $listobject->querystring .= "    and luname in ( $srclus ) ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

}
?>