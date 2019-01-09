<?php


/* *************************************************** */
/* ***********     SourcePop Functions    ************ */
/* *************************************************** */


function lsrGroupPop($listobject, $subsheds, $srcyears, $targetyears, $sources, $scenarioid, $projectid, $debug) {

   # project the entire groups totals (set negative to zero)
   # then project the individual subshed units (set negative to zero)
   # then scale the individual projections based on the ratio of the
   # (group projection) / (the sum of the subshed units)
   # currently this operates on the scale of subshed, not lrseg
   # later it may be adapted to this finer lrseg scale.

   # create basic conditional clauses
   if (count($sources) > 0) {
      $srclist = "'" . join("','", $sources) . "'";
      $srccond = " sourceid in ($srclist) ";
      $asrccond = " a.sourceid in ($srclist) ";
      $bsrccond = " b.sourceid in ($srclist) ";
      $csrccond = " c.sourceid in ($srclist) ";
   } else {
      $srccond = ' (1 = 1) ';
      $asrccond = ' (1 = 1) ';
      $bsrccond = ' (1 = 1) ';
      $csrccond = ' (1 = 1) ';
   }
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $lrsegcond = " lrseg in ($sslist) ";
      $alrsegcond = " a.lrseg in ($sslist) ";
      $subshedcond = " a.lrseg in ($sslist) ";
   } else {
      $lrsegcond = ' (1 = 1) ';
      $alrsegcond = ' (1 = 1) ';
      $subshedcond = ' (1 = 1) ';
   }
   if (strlen($srcyears) > 0) {
      $yrcond = " thisyear in ($srcyears) ";
      $ayrcond = " a.thisyear in ($srcyears) ";
      $cyrcond = " c.thisyear in ($srcyears) ";
   } else {
      $yrcond = ' (1 = 1) ';
      $ayrcond = ' (1 = 1) ';
      $cyrcond = ' (1 = 1) ';
   }

   # creates a temp table with our data
   # combine sources - using the column poplink. This column is set to be equal to the source id, unless it
   # is something like phytase version of another creature.
   $listobject->querystring = "  select a.thisyear, a.subshedid, b.poplink as sourceid, sum(a.sourcepop) as actualpop, ";
   $listobject->querystring .= "    sum(a.sourcepop * b.avgweight / c.auweight) as aucount ";
   $listobject->querystring .= " into temp table tmp_srcpop ";
   $listobject->querystring .= " from scen_sources as b,";
   $listobject->querystring .= "    scen_sourcepops as a,";
   $listobject->querystring .= "    scen_sourceloadtype as c";
   $listobject->querystring .= " where a.subshedid in ";
   $listobject->querystring .= "    ( select subshedid from scen_lrsegs ";
   $listobject->querystring .= "      where scenarioid = $scenarioid ";
   $listobject->querystring .= "         and $yrcond ";
   $listobject->querystring .= "         and $lrsegcond ";
   $listobject->querystring .= "      group by subshedid ";
   $listobject->querystring .= "    )";
   $listobject->querystring .= "    and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and c.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and $ayrcond ";
   $listobject->querystring .= "    and $asrccond ";
   $listobject->querystring .= "    and a.sourceid = b.sourceid";
   $listobject->querystring .= "    and c.typeid = b.typeid";
   # combine sources
   $listobject->querystring .= " group by a.thisyear, b.poplink, a.subshedid ";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();
   if ($debug) {
      $listobject->querystring = "select * from tmp_srcpop";
      $listobject->performQuery();
      $listobject->showList();
   }

   # now, get a list of years to facilitate use and visualization of the extrapolation output
   $listobject->querystring = "select min(thisyear) as minyr, max(thisyear) as maxyr from tmp_srcpop";
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
   if ($debug) {print("Years: $exyrs<br>");}

   # summarizes the groups data
   $listobject->querystring = "create temp table tmp_grouppop as ";
   # combine sources
   #$listobject->querystring .= " select thisyear, sum(actualpop) as totalpop, sum(aucount) as totalau ";
   # dont combine sources
   $listobject->querystring .= " select thisyear, sourceid, sum(actualpop) as totalpop, ";
   $listobject->querystring .= "    sum(aucount) as totalau ";
   $listobject->querystring .= " from tmp_srcpop ";
   $listobject->querystring .= " group by thisyear, sourceid ";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();
   #$listobject->showList();
   if ($debug) {
      $listobject->querystring = "select * from tmp_grouppop";
      $listobject->performQuery();
      $listobject->showList();
   }

   # create the best fit table for the group as a whole
   genericExtrap($listobject, $exyrs, 'tmp_grouppop', 'tmp_grpextrap', 'thisyear', 'sourceid', 'totalpop', 0.0, 1, 0, 0, 0, $debug, 1, 1);


   $mktemp = 1;
   # create the best fit table for each individual subshed
   genericExtrap($listobject, $exyrs, 'tmp_srcpop', 'tmp_srcextrap', 'thisyear', 'subshedid,sourceid', 'actualpop', 0.0, $mktemp, 0, 0, 0, $debug, 1, 1);

   # now eliminate any < 0 values
   $listobject->querystring = "update tmp_grpextrap set totalpop = 0 where totalpop < 0.0 ";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();

   # insure that no negative populations are used
   $listobject->querystring = "update tmp_srcextrap set actualpop = 0 where actualpop < 0.0 ";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();


   # now scale the subshed table to be equal to the group total
   $listobject->querystring = "";

   $listobject->querystring = "update tmp_srcextrap ";
   $listobject->querystring .= " set actualpop = a.popscale * b.actualpop ";
   $listobject->querystring .= " from  ";
   # calulate the ratio of individual predictions to the group predictions
   # and scale each individual prediction such that they add up to the group total

   $listobject->querystring .= " ( select a.thisyear, a.sourceid, ";
   $listobject->querystring .= "   CASE ";
   $listobject->querystring .= "      WHEN a.projpop > 0 THEN (b.totalpop/a.projpop) ";
   $listobject->querystring .= "      ELSE 0.0 ";
   $listobject->querystring .= "   END as popscale ";
   $listobject->querystring .= "   from ";
   $listobject->querystring .= "    (select thisyear, sourceid, sum(actualpop) as projpop ";
   $listobject->querystring .= "     from tmp_srcextrap  ";
   $listobject->querystring .= "     group by thisyear, sourceid ";
   $listobject->querystring .= "     ) as a, ";
   $listobject->querystring .= "     tmp_grpextrap as b ";
   $listobject->querystring .= "     where a.sourceid = b.sourceid ";
   $listobject->querystring .= "        and a.thisyear = b.thisyear ";
   $listobject->querystring .= " ) as a, tmp_srcextrap as b ";
   $listobject->querystring .= " where tmp_srcextrap.sourceid = a.sourceid ";
   $listobject->querystring .= "    and a.sourceid = b.sourceid ";
   $listobject->querystring .= "    and a.thisyear = b.thisyear ";
   $listobject->querystring .= "    and tmp_srcextrap.subshedid = b.subshedid ";
   $listobject->querystring .= "    and tmp_srcextrap.thisyear = a.thisyear ";

   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();


   # creates a temp table with our data
   # combine sources - using the column poplink. This column is set to be equal to the source id, unless it
   # is something like phytase version of another creature.
   $listobject->querystring = "  select a.thisyear, a.subshedid, a.sourceid, ";
   $listobject->querystring .= "    sum(a.actualpop * b.avgweight / c.auweight) as aucount ";
   $listobject->querystring .= " into temp table tmp_srcauextrap ";
   $listobject->querystring .= " from scen_sources as b,";
   $listobject->querystring .= "    tmp_srcextrap as a,";
   $listobject->querystring .= "    scen_sourceloadtype as c";
   $listobject->querystring .= " where b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and c.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and a.sourceid = b.sourceid ";
   $listobject->querystring .= "    and c.typeid = b.typeid ";
   # combine sources
   $listobject->querystring .= " group by a.thisyear, a.sourceid, a.subshedid ";
   $listobject->querystring .= " order by a.thisyear, a.sourceid, a.subshedid ";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();
   if ($debug) {
      $listobject->querystring = "select * from tmp_srcauextrap";
      $listobject->performQuery();
      $listobject->showList();
   }

   # creates a temp table with our data
   # combine sources - using the column poplink. This column is set to be equal to the source id, unless it
   # is something like phytase version of another creature.
   $listobject->querystring = "  select a.thisyear, a.sourceid, ";
   $listobject->querystring .= "    sum(aucount) as totalau ";
   $listobject->querystring .= " into temp table tmp_grpauextrap ";
   $listobject->querystring .= " from tmp_srcauextrap as a ";
   # combine sources
   $listobject->querystring .= " group by a.thisyear, a.sourceid ";
   $listobject->querystring .= " order by a.thisyear, a.sourceid ";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();
   if ($debug) {
      $listobject->querystring = "select * from tmp_grpauextrap";
      $listobject->performQuery();
      $listobject->showList();
   }


}

function projectPopForLanduse($listobject, $subsheds, $srcyears, $targetyears, $sources, $scenarioid, $projectid, $debug) {

   # this is specifically for use with  the land use projection feature
   # and will not give accurate results for population interpolations
   # project the entire groups totals (set negative to zero)
   # then project the individual subshed units (set negative to zero)
   # then scale the individual projections based on the ratio of the
   # (group projection) / (the sum of the subshed units)
   # currently this operates on the scale of subshed, not lrseg
   # later it may be adapted to this finer lrseg scale.

   # create basic conditional clauses
   if (count($sources) > 0) {
      $srclist = "'" . join("','", $sources) . "'";
      $srccond = " sourceid in ($srclist) ";
      $asrccond = " a.sourceid in ($srclist) ";
      $bsrccond = " b.sourceid in ($srclist) ";
      $csrccond = " c.sourceid in ($srclist) ";
   } else {
      $srccond = ' (1 = 1) ';
      $asrccond = ' (1 = 1) ';
      $bsrccond = ' (1 = 1) ';
      $csrccond = ' (1 = 1) ';
   }
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $lrsegcond = " lrseg in ($sslist) ";
      $alrsegcond = " a.lrseg in ($sslist) ";
      $subshedcond = " a.lrseg in ($sslist) ";
   } else {
      $lrsegcond = ' (1 = 1) ';
      $alrsegcond = ' (1 = 1) ';
      $subshedcond = ' (1 = 1) ';
   }
   if (strlen($srcyears) > 0) {
      $yrcond = " thisyear in ($srcyears) ";
      $ayrcond = " a.thisyear in ($srcyears) ";
      $cyrcond = " c.thisyear in ($srcyears) ";
   } else {
      $yrcond = ' (1 = 1) ';
      $ayrcond = ' (1 = 1) ';
      $cyrcond = ' (1 = 1) ';
   }

   # creates a temp table with our data
   # combine sources - using the column linkpop. This column is set to be equal to the source id, unless it
   # is something like phytase version of another creature.
   $listobject->querystring = "  select a.thisyear, a.subshedid, b.poplink as sourceid, sum(a.sourcepop) as actualpop, ";
   $listobject->querystring .= "    sum(a.sourcepop * b.avgweight / c.auweight) as aucount ";
   $listobject->querystring .= " into temp table tmp_srcpop ";
   $listobject->querystring .= " from scen_sources as b,";
   $listobject->querystring .= "    scen_sourcepops as a,";
   $listobject->querystring .= "    scen_sourceloadtype as c";
   $listobject->querystring .= " where a.subshedid in ";
   $listobject->querystring .= "    ( select subshedid from scen_lrsegs ";
   $listobject->querystring .= "      where $scenarioid = $scenarioid ";
   $listobject->querystring .= "         and $yrcond ";
   $listobject->querystring .= "         and $lrsegcond ";
   $listobject->querystring .= "      group by subshedid ";
   $listobject->querystring .= "    )";
   $listobject->querystring .= "    and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and c.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and $ayrcond ";
   $listobject->querystring .= "    and $asrccond ";
   $listobject->querystring .= "    and a.sourceid = b.sourceid";
   $listobject->querystring .= "    and c.typeid = b.typeid";
   # combine sources
   $listobject->querystring .= " group by a.thisyear, b.poplink, a.subshedid ";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();
   if ($debug) {
      $listobject->querystring = "select * from tmp_srcpop";
      $listobject->performQuery();
      $listobject->showList();
   }

   # now, get a list of years to facilitate use and visualization of the extrapolation output
   $listobject->querystring = "select min(thisyear) as minyr, max(thisyear) as maxyr from tmp_srcpop";
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
   if ($debug) {print("Years: $exyrs<br>");}

   # summarizes the groups data
   $listobject->querystring = "create temp table tmp_grouppop as ";
   # combine sources
   #$listobject->querystring .= " select thisyear, sum(actualpop) as totalpop, sum(aucount) as totalau ";
   # dont combine sources
   $listobject->querystring .= " select thisyear, sourceid, sum(actualpop) as totalpop, ";
   $listobject->querystring .= "    sum(aucount) as totalau ";
   $listobject->querystring .= " from tmp_srcpop ";
   $listobject->querystring .= " group by thisyear, sourceid ";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();
   #$listobject->showList();

   # create the best fit table for the group as a whole
   genericExtrap($listobject, $exyrs, 'tmp_grouppop', 'tmp_grpextrap', 'thisyear', 'sourceid', 'totalpop', 0.0, 1, 0, 0, 0, $debug, 1, 1);
   genericExtrap($listobject, $exyrs, 'tmp_grouppop', 'tmp_grpauextrap', 'thisyear', 'sourceid', 'totalau', 0.0, 1, 0, 0, 0, $debug, 1, 1);


   # create the best fit table for each individual subshed
   genericExtrap($listobject, $exyrs, 'tmp_srcpop', 'tmp_srcextrap', 'thisyear', 'subshedid,sourceid', 'actualpop', 0.0, 1, 0, 0, 0, $debug, 1, 1);
   genericExtrap($listobject, $exyrs, 'tmp_srcpop', 'tmp_srcauextrap', 'thisyear', 'subshedid,sourceid', 'aucount', 0.0, 1, 0, 0, 0, $debug, 1, 1);

   # now eliminate any < 0 values
   $listobject->querystring = "update tmp_grpextrap set totalpop = 0 where totalpop < 0.0 ";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();

   $listobject->querystring = "update tmp_srcextrap set actualpop = 0 where actualpop < 0.0 ";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();


   # now scale the subshed table to be equal to the group total
   $listobject->querystring = "";

   $listobject->querystring = "update tmp_srcextrap ";
   $listobject->querystring .= " set actualpop = a.popscale * b.actualpop ";
   $listobject->querystring .= " from  ";
   # calulate the ratio of individual predictions to the group predictions
   # and scale each individual prediction such that they add up to the group total

   $listobject->querystring .= " ( select a.thisyear, a.sourceid, ";
   $listobject->querystring .= "   CASE ";
   $listobject->querystring .= "      WHEN a.projpop > 0 THEN (b.totalpop/a.projpop) ";
   $listobject->querystring .= "      ELSE 0.0 ";
   $listobject->querystring .= "   END as popscale ";
   $listobject->querystring .= "   from ";
   $listobject->querystring .= "    (select thisyear, sourceid, sum(actualpop) as projpop ";
   $listobject->querystring .= "     from tmp_srcextrap  ";
   $listobject->querystring .= "     group by thisyear, sourceid ";
   $listobject->querystring .= "     ) as a, ";
   $listobject->querystring .= "     tmp_grpextrap as b ";
   $listobject->querystring .= "     where a.sourceid = b.sourceid ";
   $listobject->querystring .= "        and a.thisyear = b.thisyear ";
   $listobject->querystring .= " ) as a, tmp_srcextrap as b ";
   $listobject->querystring .= " where tmp_srcextrap.sourceid = a.sourceid ";
   $listobject->querystring .= "    and a.sourceid = b.sourceid ";
   $listobject->querystring .= "    and a.thisyear = b.thisyear ";
   $listobject->querystring .= "    and tmp_srcextrap.subshedid = b.subshedid ";
   $listobject->querystring .= "    and tmp_srcextrap.thisyear = a.thisyear ";

   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();
   $listobject->showList();


}

function getGroupSourceTotals($listobject, $luname, $subsheds, $pollutants, $scenarioid, $thisyear, $debug) {

   # queries for the lrseg sources in the given group
   # if no sourceids are passed in, it assumes you want all, group by sourceid
   # assemble input variables into conditions
   # $maketemp - passing a name for a table in here directs us to create a table
   if (count($sources) > 0) {
      $srclist = "'" . join("','", $sources) . "'";
      $bsrccond = " b.sourceid in ($srclist) ";
      $csrccond = " c.sourceid in ($srclist) ";
   } else {
      $bsrccond = ' 1 = 1 ';
      $csrccond = ' 1 = 1 ';
   }
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $bsubshedcond = " b.lrseg in ($sslist) ";
   } else {
      $bsubshedcond = ' 1 = 1 ';
   }

   if (strlen($pollutants) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $apollcond = " a.pollutanttype in ($pollutants) ";
   } else {
      $apollcond = ' (1 = 1) ';
   }

   if (strlen($thisyear) > 0) {
      $yrcond = " a.thisyear in ($thisyear) ";
      $byrcond = " b.thisyear in ($thisyear) ";
      $cyrcond = " c.thisyear in ($thisyear) ";
   } else {
      $yrcond = ' (1 = 1) ';
      $byrcond = ' (1 = 1) ';
      $cyrcond = ' (1 = 1) ';
   }


   if (strlen($luname) > 0) {
      $llist = "'" . join("','", split(",", $luname)) . "'";
      $lucond = " luname in ($llist) ";
      $alucond = " a.luname in ($llist) ";
      $blucond = " b.luname in ($llist) ";
      $clucond = " c.luname in ($llist) ";
      $formlu = join(", ", split(",", $luname));
   } else {
      $lucond = ' (1 = 1) ';
      $alucond = ' (1 = 1) ';
      $blucond = ' (1 = 1) ';
      $clucond = ' (1 = 1) ';
      $formlu = $luname;
   }

   $split = $listobject->startsplit();
   $listobject->querystring = " select a.sourceid as srcid, d.sourcename, a.pollutanttype, ";
   $listobject->querystring .= "    a.thisyear,  sum(b.luarea * a.annualapplied) as srcapp, ";
   $listobject->querystring .= "     e.totalapp, ";
   $listobject->querystring .= "    sum(b.luarea*a.annualapplied) / e.totalapp as srcpct ";
   $listobject->querystring .= " from scen_sourceperunitarea as a, scen_lrsegs as b, ";
   $listobject->querystring .= "    sources as c, sourceloadtype as d, ";
   $listobject->querystring .= "    ( ";
   $listobject->querystring .= "    select a.pollutanttype, ";
   $listobject->querystring .= "       a.thisyear,  sum(b.luarea * a.annualapplied) as totalapp ";
   $listobject->querystring .= "    from scen_sourceperunitarea as a, scen_lrsegs as b ";
   $listobject->querystring .= "    WHERE a.scenarioid = $scenarioid ";
   $listobject->querystring .= "       AND b.scenarioid = $scenarioid ";
   $listobject->querystring .= "       AND a.subshedid = b.subshedid ";
   $listobject->querystring .= "       AND $bsubshedcond ";
   $listobject->querystring .= "       AND $yrcond ";
   $listobject->querystring .= "       AND $byrcond ";
   $listobject->querystring .= "       AND a.luname = b.luname ";
   $listobject->querystring .= "       AND $alucond ";
   $listobject->querystring .= "       AND $blucond ";
   $listobject->querystring .= "       AND $apollcond ";
   $listobject->querystring .= "    GROUP BY a.thisyear, a.pollutanttype ";
   $listobject->querystring .= "    ) as e ";
   $listobject->querystring .= " WHERE c.typeid = d.typeid ";
   $listobject->querystring .= "    AND a.sourceid = c.sourceid ";
   $listobject->querystring .= "    AND a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    AND b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    AND a.subshedid = b.subshedid ";
   $listobject->querystring .= "    AND $bsubshedcond ";
   $listobject->querystring .= "    AND a.thisyear = e.thisyear ";
   $listobject->querystring .= "    AND $yrcond ";
   $listobject->querystring .= "    AND $byrcond ";
   $listobject->querystring .= "    AND a.luname = b.luname ";
   $listobject->querystring .= "    AND $alucond ";
   $listobject->querystring .= "    AND $blucond ";
   $listobject->querystring .= "    AND a.pollutanttype = e.pollutanttype ";
   $listobject->querystring .= "    AND $apollcond ";
   $listobject->querystring .= " GROUP BY a.sourceid, d.sourcename, ";
   $listobject->querystring .= "    a.thisyear, a.pollutanttype, e.totalapp ";
   $listobject->querystring .= " ORDER BY  a.thisyear, a.pollutanttype, srcpct DESC ";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }
   #$listobject->showList();

   return $listobject->queryrecords;
}

function getGroupSources($listobject, $sources, $tracerpoll, $subsheds, $scenarioid, $thisyear, $debug) {

   # queries for the lrseg sources in the given group
   # if no sourceids are passed in, it assumes you want all, group by sourceid
   # assemble input variables into conditions
   # $maketemp - passing a name for a table in here directs us to create a table

   # $tracerpoll = the id of the constituent to use to dissagregate population
   # 12 is the ID of the general tracer currently, a good candidate
   if (count($sources) > 0) {
      $srclist = "'" . join("','", $sources) . "'";
      $bsrccond = " b.sourceid in ($srclist) ";
      $csrccond = " c.sourceid in ($srclist) ";
   } else {
      $bsrccond = ' 1 = 1 ';
      $csrccond = ' 1 = 1 ';
   }
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " a.lrseg in ($sslist) ";
      $csubshedcond = " c.lrseg in ($sslist) ";
   } else {
      $subshedcond = ' 1 = 1 ';
      $csubshedcond = ' (1 = 1) ';
   }
   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear in ($thisyear) ";
      $ayrcond = " a.thisyear in ($thisyear) ";
      $byrcond = " b.thisyear in ($thisyear) ";
      $cyrcond = " c.thisyear in ($thisyear) ";
   } else {
      $yrcond = ' (1 = 1) ';
      $byrcond = ' (1 = 1) ';
      $cyrcond = ' (1 = 1) ';
   }

   $listobject->queryrecords = array();
   $split = $listobject->startsplit();

   # summary for group
   $listobject->querystring = "  select b.sourcename, a.thisyear, ";
   $listobject->querystring .= "    sum(b.actualpop::float8 * c.luarea * a.annualapplied ";
   $listobject->querystring .= "       / b.annualpollutant) as sourcepop, ";
   $listobject->querystring .= "    sum(b.aucount::float8 * c.luarea * a.annualapplied ";
   $listobject->querystring .= "       / b.annualpollutant) as aus, ";
   $listobject->querystring .= "    sum(c.luarea * a.annualapplied ";
   $listobject->querystring .= "       / b.annualpollutant) as pop_pct,  ";
   $listobject->querystring .= "    sum(c.luarea * a.annualapplied) as waste_production  ";
   $listobject->querystring .= " from scen_sourceperunitarea as a, scen_lrsegs as c, ";
   $listobject->querystring .= "    scen_sourcepollprod as b ";
   $listobject->querystring .= " where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and c.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and a.thisyear = $thisyear ";
   $listobject->querystring .= "    and b.thisyear = $thisyear ";
   $listobject->querystring .= "    and c.thisyear = $thisyear ";
   $listobject->querystring .= "    and a.pollutanttype = $tracerpoll ";
   $listobject->querystring .= "    and b.pollutanttype = $tracerpoll ";
   $listobject->querystring .= "    and $csubshedcond ";
   $listobject->querystring .= "    and a.subshedid = b.subshedid ";
   $listobject->querystring .= "    and a.subshedid = c.subshedid ";
   $listobject->querystring .= "    and a.luname = c.luname ";
   $listobject->querystring .= "    and a.sourceid = b.sourceid ";
   $listobject->querystring .= "    and b.annualpollutant > 0 ";
   $listobject->querystring .= " group by b.sourcename, a.thisyear ";
   $listobject->querystring .= " order by b.sourcename ";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }
   #$listobject->showList();
   return $listobject->queryrecords;

}


function getSegmentSources($listobject, $sources, $tracerpoll, $subsheds, $scenarioid, $thisyear, $sumby, $debug) {

   # queries for the lrseg sources in the given group
   # if no sourceids are passed in, it assumes you want all, group by sourceid
   # assemble input variables into conditions
   # $maketemp - passing a name for a table in here directs us to create a table

   switch($sumby) {
      case 1:
      # subshedid (local political boundary)
      $groupcol = 'c.subshedid';
      break;

      case 2:
      # lrseg (hydrologic/political boundary)
      $groupcol = 'c.lrseg';
      break;

      case 3:
      # lrseg (hydrologic boundary)
      $groupcol = 'c.riverseg';
      break;

      default:
      # subshedid (local political boundary)
      $groupcol = 'c.subshedid';
      break;
   }

   # $tracerpoll = the id of the constituent to use to dissagregate population
   # 12 is the ID of the general tracer currently, a good candidate
   if (count($sources) > 0) {
      $srclist = "'" . join("','", $sources) . "'";
      $bsrccond = " b.sourceid in ($srclist) ";
      $csrccond = " c.sourceid in ($srclist) ";
   } else {
      $bsrccond = ' 1 = 1 ';
      $csrccond = ' 1 = 1 ';
   }
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " a.lrseg in ($sslist) ";
      $csubshedcond = " c.lrseg in ($sslist) ";
   } else {
      $subshedcond = ' 1 = 1 ';
      $csubshedcond = ' (1 = 1) ';
   }
   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear in ($thisyear) ";
      $ayrcond = " a.thisyear in ($thisyear) ";
      $byrcond = " b.thisyear in ($thisyear) ";
      $cyrcond = " c.thisyear in ($thisyear) ";
      $dyrcond = " d.thisyear in ($thisyear) ";
   } else {
      $yrcond = ' (1 = 1) ';
      $ayrcond = ' (1 = 1) ';
      $byrcond = ' (1 = 1) ';
      $cyrcond = ' (1 = 1) ';
      $dyrcond = ' (1 = 1) ';
   }

   $listobject->queryrecords = array();
   $split = $listobject->startsplit();

   # summary for group
   $listobject->querystring = "  select $groupcol, a.thisyear, b.sourcename, d.src_citation, ";
   $listobject->querystring .= "    sum(b.actualpop::float8 * c.luarea * a.annualapplied ";
   $listobject->querystring .= "       / b.annualpollutant) as sourcepop, ";
   $listobject->querystring .= "    sum(b.aucount::float8 * c.luarea * a.annualapplied ";
   $listobject->querystring .= "       / b.annualpollutant) as aus, ";
   $listobject->querystring .= "    sum(c.luarea * a.annualapplied ";
   $listobject->querystring .= "       / b.annualpollutant) as pop_pct,  ";
   $listobject->querystring .= "    sum(c.luarea * a.annualapplied) as waste_production  ";
   $listobject->querystring .= " from scen_sourceperunitarea as a, scen_lrsegs as c, ";
   $listobject->querystring .= "    scen_sourcepollprod as b, scen_sourcepops as d ";
   $listobject->querystring .= " where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and c.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and d.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and a.thisyear = $thisyear ";
   $listobject->querystring .= "    and b.thisyear = $thisyear ";
   $listobject->querystring .= "    and c.thisyear = $thisyear ";
   $listobject->querystring .= "    and d.thisyear = $thisyear ";
   $listobject->querystring .= "    and a.pollutanttype = $tracerpoll ";
   $listobject->querystring .= "    and b.pollutanttype = $tracerpoll ";
   $listobject->querystring .= "    and $csubshedcond ";
   $listobject->querystring .= "    and a.subshedid = b.subshedid ";
   $listobject->querystring .= "    and a.subshedid = c.subshedid ";
   $listobject->querystring .= "    and a.subshedid = d.subshedid ";
   $listobject->querystring .= "    and a.luname = c.luname ";
   $listobject->querystring .= "    and a.sourceid = b.sourceid ";
   $listobject->querystring .= "    and a.sourceid = d.sourceid ";
   $listobject->querystring .= "    and b.annualpollutant > 0 ";
   $listobject->querystring .= " group by $groupcol, a.thisyear, b.sourcename, d.src_citation ";
   $listobject->querystring .= " order by a.thisyear, $groupcol, b.sourcename ";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }
   #$listobject->showList();
   return $listobject->queryrecords;

}


function getGroupStoredSources($listobject, $sources, $tracerpoll, $constit, $subsheds, $scenarioid, $thisyear, $debug) {

   # queries for the lrseg sources in the given group
   # if no sourceids are passed in, it assumes you want all, group by sourceid
   # assemble input variables into conditions
   # $maketemp - passing a name for a table in here directs us to create a table

   # $tracerpoll = the id of the constituent to use to dissagregate population
   # 12 is the ID of the general tracer currently, a good candidate
   if (count($sources) > 0) {
      $srclist = "'" . join("','", $sources) . "'";
      $bsrccond = " b.sourceid in ($srclist) ";
      $csrccond = " c.sourceid in ($srclist) ";
   } else {
      $bsrccond = ' 1 = 1 ';
      $csrccond = ' 1 = 1 ';
   }
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " a.lrseg in ($sslist) ";
      $csubshedcond = " c.lrseg in ($sslist) ";
   } else {
      $subshedcond = ' 1 = 1 ';
      $csubshedcond = ' (1 = 1) ';
   }
   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear in ($thisyear) ";
      $ayrcond = " a.thisyear in ($thisyear) ";
      $byrcond = " b.thisyear in ($thisyear) ";
      $cyrcond = " c.thisyear in ($thisyear) ";
   } else {
      $yrcond = ' (1 = 1) ';
      $byrcond = ' (1 = 1) ';
      $cyrcond = ' (1 = 1) ';
   }

   $listobject->queryrecords = array();
   $split = $listobject->startsplit();

   # summary for group
   $listobject->querystring = "  select b.sourcename, d.constit, ";
   $listobject->querystring .= "    sum( d.annualstored ";
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as polltotal, ";
   $listobject->querystring .= "    sum( d.JAN ";
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as JAN, ";
   $listobject->querystring .= "    sum( d.FEB ";
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as FEB, ";
   $listobject->querystring .= "    sum( d.MAR ";
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as MAR, ";
   $listobject->querystring .= "    sum( d.APR ";
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as APR, ";
   $listobject->querystring .= "    sum( d.MAY ";
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as MAY, ";
   $listobject->querystring .= "    sum( d.JUN ";
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as JUN, ";
   $listobject->querystring .= "    sum( d.JUL ";
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as JUL, ";
   $listobject->querystring .= "    sum( d.AUG ";
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as AUG, ";
   $listobject->querystring .= "    sum( d.SEP ";
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as SEP, ";
   $listobject->querystring .= "    sum( d.OCT ";
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as OCT, ";
   $listobject->querystring .= "    sum( d.NOV ";
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as NOV, ";
   $listobject->querystring .= "    sum( d.DEC ";
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as DEV, ";
   $listobject->querystring .= "    sum(b.actualpop::float8 * c.luarea * a.annualapplied ";
   $listobject->querystring .= "       / b.annualpollutant) as sourcepop, ";
   $listobject->querystring .= "    sum(c.luarea * a.annualapplied / b.annualpollutant) as pop_pct  ";
   $listobject->querystring .= " from scen_sourceperunitarea as a, scen_lrsegs as c, scen_sourcepollprod as b, ";
   $listobject->querystring .= "    scen_storedloads as d ";
   $listobject->querystring .= " where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and c.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and d.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and a.thisyear = $thisyear ";
   $listobject->querystring .= "    and b.thisyear = $thisyear ";
   $listobject->querystring .= "    and c.thisyear = $thisyear ";
   $listobject->querystring .= "    and d.thisyear = $thisyear ";
   $listobject->querystring .= "    and a.pollutanttype = $tracerpoll ";
   $listobject->querystring .= "    and b.pollutanttype = $tracerpoll ";
   $listobject->querystring .= "    and d.constit = $constit ";
   $listobject->querystring .= "    and $csubshedcond ";
   $listobject->querystring .= "    and a.subshedid = b.subshedid ";
   $listobject->querystring .= "    and a.subshedid = c.subshedid ";
   $listobject->querystring .= "    and a.subshedid = d.subshedid ";
   $listobject->querystring .= "    and a.luname = c.luname ";
   $listobject->querystring .= "    and a.sourceid = b.sourceid ";
   $listobject->querystring .= "    and a.sourceid = d.sourceid ";
   $listobject->querystring .= "    and b.annualpollutant > 0 ";
   $listobject->querystring .= " group by b.sourcename, d.constit ";
   $listobject->querystring .= " order by d.constit, b.sourcename ";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }
   #$listobject->showList();
   return $listobject->queryrecords;

}


function getLRSegStored($listobject, $sources, $spreadtypes, $tracerpoll, $constit, $subsheds, $scenarioid, $thisyear, $debug) {

   # queries for the lrseg sources in the given group
   # if no sourceids are passed in, it assumes you want all, group by sourceid
   # assemble input variables into conditions
   # $maketemp - passing a name for a table in here directs us to create a table

   # $tracerpoll = the id of the constituent to use to dissagregate population
   # 12 is the ID of the general tracer currently, a good candidate
   if (count($sources) > 0) {
      $srclist = "'" . join("','", $sources) . "'";
      $bsrccond = " b.sourceid in ($srclist) ";
      $csrccond = " c.sourceid in ($srclist) ";
   } else {
      $bsrccond = ' 1 = 1 ';
      $csrccond = ' 1 = 1 ';
   }
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " a.lrseg in ($sslist) ";
      $csubshedcond = " c.lrseg in ($sslist) ";
   } else {
      $subshedcond = ' 1 = 1 ';
      $csubshedcond = ' (1 = 1) ';
   }
   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear in ($thisyear) ";
      $ayrcond = " a.thisyear in ($thisyear) ";
      $byrcond = " b.thisyear in ($thisyear) ";
      $cyrcond = " c.thisyear in ($thisyear) ";
   } else {
      $yrcond = ' (1 = 1) ';
      $byrcond = ' (1 = 1) ';
      $cyrcond = ' (1 = 1) ';
   }
   if (strlen($constit) > 0) {
      $dconstitcond = " d.constit in ($constit) ";
   } else {
      $dconstitcond = ' (1 = 1) ';
   }
   if (strlen($spreadtypes) > 0) {
      $dspreadcond = " d.spreadid in ($spreadtypes) ";
   } else {
      $dspreadcond = ' 1 = 1 ';
   }

   $listobject->queryrecords = array();
   $split = $listobject->startsplit();

   # summary for group
   $listobject->querystring = "  select c.lrseg, d.thisyear, e.shortname as constit, ";
   $listobject->querystring .= "    sum( d.annualstored ";
   # uses the tracer to relate point of application with point of generation.
   # thus the annualapplied is a per unit area measure. Multiply that by the luare
   # gives the amount that is applied in that lrseg-lu, . Divviding yththat by the annual pollutant
   # which is the total tracer produced gives the fraction of amnure that goes to this lu-lrseg.
   # Then, finally, multiplying this fraction by the total stored of the constituent in question
   # gives us the amount stored of this constit by lrseg-lu.
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as polltotal, ";
   $listobject->querystring .= "    sum( d.JAN ";
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as JAN, ";
   $listobject->querystring .= "    sum( d.FEB ";
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as FEB, ";
   $listobject->querystring .= "    sum( d.MAR ";
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as MAR, ";
   $listobject->querystring .= "    sum( d.APR ";
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as APR, ";
   $listobject->querystring .= "    sum( d.MAY ";
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as MAY, ";
   $listobject->querystring .= "    sum( d.JUN ";
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as JUN, ";
   $listobject->querystring .= "    sum( d.JUL ";
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as JUL, ";
   $listobject->querystring .= "    sum( d.AUG ";
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as AUG, ";
   $listobject->querystring .= "    sum( d.SEP ";
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as SEP, ";
   $listobject->querystring .= "    sum( d.OCT ";
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as OCT, ";
   $listobject->querystring .= "    sum( d.NOV ";
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as NOV, ";
   $listobject->querystring .= "    sum( d.DEC ";
   $listobject->querystring .= "       * (c.luarea * a.annualapplied / b.annualpollutant)) as DEC ";
   $listobject->querystring .= " from scen_sourceperunitarea as a, scen_lrsegs as c, ";
   $listobject->querystring .= "    scen_sourcepollprod as b, pollutanttype as e, ";
   $listobject->querystring .= "    scen_storedloads as d ";
   $listobject->querystring .= " where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and c.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and d.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and a.thisyear = $thisyear ";
   $listobject->querystring .= "    and b.thisyear = $thisyear ";
   $listobject->querystring .= "    and c.thisyear = $thisyear ";
   $listobject->querystring .= "    and d.thisyear = $thisyear ";
   $listobject->querystring .= "    and $csubshedcond ";
   $listobject->querystring .= "    and a.pollutanttype = $tracerpoll ";
   $listobject->querystring .= "    and b.pollutanttype = $tracerpoll ";
   $listobject->querystring .= "    and $dconstitcond ";
   $listobject->querystring .= "    and a.subshedid = b.subshedid ";
   $listobject->querystring .= "    and a.subshedid = c.subshedid ";
   $listobject->querystring .= "    and a.subshedid = d.subshedid ";
   $listobject->querystring .= "    and a.luname = c.luname ";
   $listobject->querystring .= "    and a.sourceid = b.sourceid ";
   $listobject->querystring .= "    and a.sourceid = d.sourceid ";
   $listobject->querystring .= "    and e.typeid = d.constit ";
   $listobject->querystring .= "    and b.annualpollutant > 0 ";
   $listobject->querystring .= " group by c.lrseg, e.shortname, d.thisyear ";
   $listobject->querystring .= " order by e.shortname, c.lrseg ";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }
   #$listobject->showList();
   return $listobject->queryrecords;

}

function getLRSegApplied($listobject, $spreadtypes, $tracerpoll, $constit, $subsheds, $scenarioid, $thisyear, $debug) {

   # queries for the lrseg sources in the given group
   # if no sourceids are passed in, it assumes you want all, group by sourceid
   # assemble input variables into conditions
   # $maketemp - passing a name for a table in here directs us to create a table

   # $tracerpoll = the id of the constituent to use to dissagregate population
   # 12 is the ID of the general tracer currently, a good candidate
   if (strlen($spreadtypes) > 0) {
      $aspreadcond = " a.spreadid in ($spreadtypes) ";
      $bspreadcond = " b.spreadid in ($spreadtypes) ";
      $cspreadcond = " c.spreadid in ($spreadtypes) ";
   } else {
      $aspreadcond = ' 1 = 1 ';
      $bspreadcond = ' 1 = 1 ';
      $cspreadcond = ' 1 = 1 ';
   }
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " a.lrseg in ($sslist) ";
      $csubshedcond = " c.lrseg in ($sslist) ";
   } else {
      $subshedcond = ' 1 = 1 ';
      $csubshedcond = ' (1 = 1) ';
   }
   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear in ($thisyear) ";
      $ayrcond = " a.thisyear in ($thisyear) ";
      $byrcond = " b.thisyear in ($thisyear) ";
      $cyrcond = " c.thisyear in ($thisyear) ";
   } else {
      $yrcond = ' (1 = 1) ';
      $byrcond = ' (1 = 1) ';
      $cyrcond = ' (1 = 1) ';
   }
   if (strlen($constit) > 0) {
      $aconstitcond = " a.pollutanttype in ($constit) ";
   } else {
      $aconstitcond = ' (1 = 1) ';
   }

   $listobject->queryrecords = array();
   $split = $listobject->startsplit();

   # summary for group
   $listobject->querystring = "  select c.lrseg, a.thisyear, b.shortname as constit, ";
   $listobject->querystring .= "    sum( c.luarea * a.annualapplied) as polltotal, ";
   $listobject->querystring .= "    sum( c.luarea * a.JAN ) as JAN, ";
   $listobject->querystring .= "    sum( c.luarea * a.FEB ) as FEB, ";
   $listobject->querystring .= "    sum( c.luarea * a.MAR ) as MAR, ";
   $listobject->querystring .= "    sum( c.luarea * a.APR ) as APR, ";
   $listobject->querystring .= "    sum( c.luarea * a.MAY ) as MAY, ";
   $listobject->querystring .= "    sum( c.luarea * a.JUN ) as JUN, ";
   $listobject->querystring .= "    sum( c.luarea * a.JUL ) as JUL, ";
   $listobject->querystring .= "    sum( c.luarea * a.AUG ) as AUG, ";
   $listobject->querystring .= "    sum( c.luarea * a.SEP ) as SEP, ";
   $listobject->querystring .= "    sum( c.luarea * a.OCT ) as OCT, ";
   $listobject->querystring .= "    sum( c.luarea * a.NOV ) as NOV, ";
   $listobject->querystring .= "    sum( c.luarea * a.DEC ) as DEC ";
   $listobject->querystring .= " from scen_sourceperunitarea as a, scen_lrsegs as c, pollutanttype as b ";
   $listobject->querystring .= " where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and c.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and a.thisyear = $thisyear ";
   $listobject->querystring .= "    and c.thisyear = $thisyear ";
   $listobject->querystring .= "    and $csubshedcond ";
   $listobject->querystring .= "    and $aconstitcond ";
   $listobject->querystring .= "    and $aspreadcond ";
   $listobject->querystring .= "    and a.subshedid = c.subshedid ";
   $listobject->querystring .= "    and a.luname = c.luname ";
   $listobject->querystring .= "    and a.pollutanttype = b.typeid ";
   $listobject->querystring .= " group by c.lrseg, b.shortname, a.thisyear ";
   $listobject->querystring .= " order by b.shortname, c.lrseg ";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }
   #$listobject->showList();
   return $listobject->queryrecords;

}

function getSubshedGroupSources($listobject, $sources, $subsheds, $scenarioid, $thisyear, $debug) {

   # queries for the lrseg sources in the given group
   # if no sourceids are passed in, it assumes you want all, group by sourceid
   # assemble input variables into conditions
   # $maketemp - passing a name for a table in here directs us to create a table
   if (count($sources) > 0) {
      $srclist = "'" . join("','", $sources) . "'";
      $asrccond = " a.sourceid in ($srclist) ";
   } else {
      $asrccond = ' 1 = 1 ';
   }
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $asubcond = " a.subshedid in ";
      $asubcond .= "   (select subshedid ";
      $asubcond .= "    from scen_lrsegs ";
      $asubcond .= "    where lrseg in ($sslist) ";
      $asubcond .= "       and scenarioid = $scenarioid ";
      $asubcond .= "    group by subshedid )";
   } else {
      $asubcond = ' 1 = 1 ';
   }

   if (strlen($thisyear) > 0) {
      $ayrcond = " thisyear in ($thisyear) ";
   } else {
      $ayrcond = ' (1 = 1) ';
   }

   $listobject->queryrecords = array();
   $split = $listobject->startsplit();

### still under development, must unite landuse, sources and subsheds
   $listobject->querystring = " select c.sourceclass, a.thisyear, b.sourcename, a.sourceid, b.typeid, ";
   $listobject->querystring .= "    sum(a.sourcepop) as sourcepop, ";
   $listobject->querystring .= "    sum(a.sourcepop * b.avgweight / c.auweight ) as aus ";
   $listobject->querystring .= " from scen_sourcepops as a, scen_sources as b, sourceloadtype as c ";
   $listobject->querystring .= " where a.sourceid = b.sourceid ";
   $listobject->querystring .= "    and b.typeid = c.typeid ";
   $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and b.avgweight > 0 ";
   $listobject->querystring .= "    and $ayrcond ";
   $listobject->querystring .= "    and $asubcond ";
   $listobject->querystring .= "    and $asrccond ";
   $listobject->querystring .= " group by c.sourceclass, a.thisyear, b.sourcename, a.sourceid, b.typeid ";
   $listobject->querystring .= " order by c.sourceclass ";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }
   #$listobject->showList();
   return $listobject->queryrecords;

}

function addTransportRecord($listobject, $scenarioid, $projectid, $sourceid, $constit, $subshedid, $dest_subshedid, $binid, $thisyear, $amount, $debug) {

   $listobject->querystring = "  insert into scen_source_transport (scenarioid, sourceid, thisyear, ";
   $listobject->querystring .= "    subshedid, dest_subshedid, constit, storage_bin, amount ) ";
   $listobject->querystring .= " values ($scenarioid, $sourceid, $thisyear, '$subshedid', '$dest_subshedid', ";
   $listobject->querystring .= "    $constit, $binid, $amount ) ";
   if ($debug) { print(" $listobject->querystring ; <br>"); }
   $listobject->performQuery();
}

function updateTransportRecord($listobject, $scenarioid, $projectid, $trecs, $debug) {

   # records to delete, records to update
   $dkeys = array_keys($trecs['delete_transfer']);
   $ukeys = array_keys($trecs['edit_transfer']);

   foreach ($dkeys as $thiskey) {
      $sstid = $trecs['sstid'][$thiskey];
      $listobject->querystring = "  delete from scen_source_transport ";
      $listobject->querystring .= " where scenarioid = $scenarioid ";
      $listobject->querystring .= "    and sstid = $sstid ";
      if ($debug) { print("$listobject->querystring ; <br>"); }
      $listobject->performQuery();
      print("Source Transfer $sstid Deleted.<br>");
   }

   foreach ($ukeys as $thiskey) {
      $subshedid = $trecs['subshedid'][$thiskey];
      $dest_subshedid = $trecs['dest_subshedid'][$thiskey];
      $thisyear = $trecs['thisyear'][$thiskey];
      $sourceid = $trecs['sourceid'][$thiskey];
      $constit = $trecs['constit'][$thiskey];
      $storage_bin = $trecs['storage_bin'][$thiskey];
      $amount = $trecs['amount'][$thiskey];
      $sstid = $trecs['sstid'][$thiskey];
      $listobject->querystring = "  update scen_source_transport set subshedid = '$subshedid', ";
      $listobject->querystring .= "    dest_subshedid = '$dest_subshedid', thisyear = $thisyear, ";
      $listobject->querystring .= "    sourceid = $sourceid, constit = $constit, storage_bin = $storage_bin,";
      $listobject->querystring .= "    amount = $amount ";
      $listobject->querystring .= " where scenarioid = $scenarioid ";
      $listobject->querystring .= "    and sstid = $sstid ";
      if ($debug) { print("$listobject->querystring ; <br>"); }
      $listobject->performQuery();
   }
}

function getSubshedGroupTransportSources($listobject, $sources, $subsheds, $scenarioid, $thisyear, $debug) {

   # queries for the lrseg sources in the given group
   # if no sourceids are passed in, it assumes you want all, group by sourceid
   # assemble input variables into conditions
   # $maketemp - passing a name for a table in here directs us to create a table
   if (count($sources) > 0) {
      $srclist = "'" . join("','", $sources) . "'";
      $asrccond = " a.sourceid in ($srclist) ";
   } else {
      $asrccond = ' 1 = 1 ';
   }
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $asubcond = " a.subshedid in ";
      $asubcond .= "   (select subshedid ";
      $asubcond .= "    from scen_lrsegs ";
      $asubcond .= "    where lrseg in ($sslist) ";
      $asubcond .= "       and scenarioid = $scenarioid ";
      $asubcond .= "    group by subshedid )";
   } else {
      $asubcond = ' 1 = 1 ';
   }

   if (strlen($thisyear) > 0) {
      $ayrcond = " thisyear in ($thisyear) ";
   } else {
      $ayrcond = ' (1 = 1) ';
   }

   $listobject->queryrecords = array();
   $split = $listobject->startsplit();

### still under development, must unite landuse, sources and subsheds
   $listobject->querystring = " select a.sourceid, a.thisyear, b.sourcename, a.subshedid, a.dest_subshedid, ";
   $listobject->querystring .= "    a.amount, a.storage_bin, a.constit, a.sstid ";
   $listobject->querystring .= " from scen_source_transport as a, scen_sources as b ";
   $listobject->querystring .= " where a.sourceid = b.sourceid ";
   $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and $ayrcond ";
   $listobject->querystring .= "    and $asubcond ";
   $listobject->querystring .= "    and $asrccond ";
   $listobject->querystring .= " order by a.subshedid, b.sourcename ";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }
   #$listobject->showList();
   return $listobject->queryrecords;

}

function projectGroupPopLandUse($listobject, $inpops, $debug) {

   # coefficients from regression equations
   # these coefficients are currently hard-wired, but in the near future
   # I will use the regression functions in phpmath to do a dynamic
   # this array contains the intercept ( [0] ) and the 3 coeffciients ( [1-3] )
   $bmhay = array(12942.43147, -0.000807733, 0.365637892, 1.265073293);
   $bmrow = array(18487.53477, 0.005256855, -0.446222007, 2.122007084);
   $bmpas = array(10149.64605, -0.001060167, 1.999122487, -1.460934939);

   # input populations
   $dairypop = $inpops['dairy_heiffers'];
   $broilerpop = $inpops['broilers'];
   $beefpop = $inpops['beef_heiffers'];

   $x['hay'] = $bmhay[0] + $bmhay[1]*$broilerpop + $bmhay[2]*$beefpop + $bmhay[3]*$dairypop;
   $x['pas'] = $bmpas[0] + $bmpas[1]*$broilerpop + $bmpas[2]*$beefpop + $bmpas[3]*$dairypop;
   $x['row'] = $bmrow[0] + $bmrow[1]*$broilerpop + $bmrow[2]*$beefpop + $bmrow[3]*$dairypop;
   print("$bmhay[0] + $bmhay[1]*$broilerpop + $bmhay[2]*$beefpop + $bmhay[3]*$dairypop <br>");
   return $x;
}

function projectPopLandUse($listobject, $lastyear, $futureyear, $scenarioid, $debug) {

   # coefficients from regression equations
   # these coefficients are currently hard-wired, but in the near future
   # I will use the regression functions in phpmath to do a dynamic
   # this array contains the intercept ( [0] ) and the 3 coeffciients ( [1-3] )
   $bmh = array(12942.43147, -0.000807733, 0.365637892, 1.265073293);
   $bmr = array(18487.53477, 0.005256855, -0.446222007, 2.122007084);
   $bmp = array(10149.64605, -0.001060167, 1.999122487, -1.460934939);

   $split = $listobject->startsplit();
### still under development, must unit landuse, sources and subsheds
   $listobject->querystring = " create temp table tmp_landreg as ";
   $listobject->querystring .= " select a.subshedid, ";
   $listobject->querystring .= "   $bmh[0] + $bmh[1]*a.pop + $bmh[2]*b.pop + $bmh[3]*c.pop as haylast, ";
   $listobject->querystring .= "   $bmh[0] + $bmp[1]*a.pop + $bmp[2]*b.pop + $bmp[3]*c.pop as paslast, ";
   $listobject->querystring .= "   $bmh[0] + $bmr[1]*a.pop + $bmr[2]*b.pop + $bmr[3]*c.pop as rowlast, ";
   $listobject->querystring .= "   $bmh[0] + $bmh[1]*d.pop + $bmh[2]*e.pop + $bmh[3]*f.pop as hayfuture, ";
   $listobject->querystring .= "   $bmh[0] + $bmp[1]*d.pop + $bmp[2]*e.pop + $bmp[3]*f.pop as pasfuture, ";
   $listobject->querystring .= "   $bmh[0] + $bmr[1]*d.pop + $bmr[2]*e.pop + $bmr[3]*f.pop as rowfuture ";
   $listobject->querystring .= " from ";
   $listobject->querystring .= " (select a.subshedid, a.actualpop as pop ";
   $listobject->querystring .= "  from tmp_srcpop as a, sources as b ";
   $listobject->querystring .= "  where a.sourceid = b.sourceid ";
   $listobject->querystring .= "     and b.sourcename = 'broilers' ";
   $listobject->querystring .= "     and a.thisyear = $lastyear ";
   $listobject->querystring .= " ) as a, ";
   $listobject->querystring .= " (select a.subshedid, a.actualpop as pop ";
   $listobject->querystring .= "  from tmp_srcpop as a, sources as b ";
   $listobject->querystring .= "  where a.sourceid = b.sourceid ";
   $listobject->querystring .= "     and b.sourcename = 'beef_heiffers' ";
   $listobject->querystring .= "     and a.thisyear = $lastyear ";
   $listobject->querystring .= " ) as b, ";
   $listobject->querystring .= " (select a.subshedid, a.actualpop as pop ";
   $listobject->querystring .= "  from tmp_srcpop as a, sources as b ";
   $listobject->querystring .= "  where a.sourceid = b.sourceid ";
   $listobject->querystring .= "     and b.sourcename = 'dairy_heiffers' ";
   $listobject->querystring .= "     and a.thisyear = $lastyear ";
   $listobject->querystring .= " ) as c, ";
   $listobject->querystring .= " (select a.subshedid, a.actualpop as pop ";
   $listobject->querystring .= "  from tmp_srcextrap as a, sources as b ";
   $listobject->querystring .= "  where a.sourceid = b.sourceid ";
   $listobject->querystring .= "     and b.sourcename = 'broilers' ";
   $listobject->querystring .= "     and a.thisyear = $futureyear ";
   $listobject->querystring .= " ) as d, ";
   $listobject->querystring .= " (select a.subshedid, a.actualpop as pop ";
   $listobject->querystring .= "  from tmp_srcextrap as a, sources as b ";
   $listobject->querystring .= "  where a.sourceid = b.sourceid ";
   $listobject->querystring .= "     and b.sourcename = 'beef_heiffers' ";
   $listobject->querystring .= "     and a.thisyear = $futureyear ";
   $listobject->querystring .= " ) as e, ";
   $listobject->querystring .= " (select a.subshedid, a.actualpop as pop ";
   $listobject->querystring .= "  from tmp_srcextrap as a, sources as b ";
   $listobject->querystring .= "  where a.sourceid = b.sourceid ";
   $listobject->querystring .= "     and b.sourcename = 'dairy_heiffers' ";
   $listobject->querystring .= "     and a.thisyear = $futureyear ";
   $listobject->querystring .= " ) as f ";
   $listobject->querystring .= " where a.subshedid = b.subshedid ";
   $listobject->querystring .= "    and a.subshedid = c.subshedid";
   $listobject->querystring .= "    and a.subshedid = d.subshedid";
   $listobject->querystring .= "    and a.subshedid = e.subshedid";
   $listobject->querystring .= "    and a.subshedid = f.subshedid";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) {
      print("Query Time: $split<br>");
      $listobject->querystring = " select * from tmp_landreg ";
      $listobject->performQuery();
      $listobject->showList();
   }

   $listobject->querystring = " create temp table tmp_sumlandreg as ";
   $listobject->querystring .= " select sum(haylast) as haylast,  ";
   $listobject->querystring .= "    sum(paslast) as paslast, ";
   $listobject->querystring .= "    sum(rowlast) as rowlast, ";
   $listobject->querystring .= "    sum(hayfuture) as hayfuture, ";
   $listobject->querystring .= "    sum(pasfuture) as pasfuture, ";
   $listobject->querystring .= "    sum(rowfuture) as rowfuture ";
   $listobject->querystring .= " from tmp_landreg ";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }
   if ($debug) {
      $listobject->querystring = " select * from tmp_sumlandreg ";
      $listobject->performQuery();
      $listobject->showList();
   }

}

function updatePopLandUse($listobject, $lastyear, $futureyear, $minresist, $maxresist, $scenarioid, $debug) {

   # this takes the results fo projected needed land use, and creates a resistance value
   # for input to the SLUETH model
   #

   # store the results into a scenario table
   $listobject->querystring = " delete from scen_lrpopproject ";
   $listobject->querystring .= " where scenarioid = $scenarioid ";
   $listobject->querystring .= "    and thisyear = $futureyear ";
   $listobject->querystring .= "    and subshedid in ( ";
   $listobject->querystring .= "       select subshedid from tmp_landreg group by subshedid ";
   $listobject->querystring .= "    ) ";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }
   $listobject->querystring = " insert into scen_lrpopproject ";
   $listobject->querystring .= " ( scenarioid, subshedid, thisyear, lastyear, haylast, ";
   $listobject->querystring .= "      paslast, rowlast, hayfuture, pasfuture,  rowfuture, ";
   $listobject->querystring .= "      hayslope, passlope,  rowslope ) ";
   $listobject->querystring .= " select $scenarioid, subshedid, $futureyear, $lastyear, haylast, ";
   $listobject->querystring .= "      paslast,rowlast, hayfuture, pasfuture, rowfuture, ";
   $listobject->querystring .= "      (hayfuture - haylast)/abs(haylast),  ";
   $listobject->querystring .= "      (pasfuture - paslast) / abs(paslast),  ";
   $listobject->querystring .= "      (rowfuture - rowlast) / abs(rowlast) ";
   $listobject->querystring .= " from tmp_landreg ";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }

   # calculate mid-points and such
   $midpoint = ($minresist + $maxresist) / 2.0;
   $middist = ($maxresist - $minresist) / 2.0;

   $listobject->querystring = " update scen_lrpopproject ";
   $listobject->querystring .= " set weightedag = ( (passlope * paslast ) + ";
   $listobject->querystring .= "                    (hayslope * haylast ) + ";
   $listobject->querystring .= "                    (rowslope * rowlast ) ) /  ";
   $listobject->querystring .= "                    ( paslast + haylast + rowlast ) , ";
   $listobject->querystring .= " agresist = $midpoint + $middist * ";
   $listobject->querystring .= "                 ( (passlope * paslast ) + ";
   $listobject->querystring .= "                    (hayslope * haylast ) + ";
   $listobject->querystring .= "                    (rowslope * rowlast ) ) /  ";
   $listobject->querystring .= "                    ( paslast + haylast + rowlast ) ";
   $listobject->querystring .= " where scenarioid = $scenarioid ";
   $listobject->querystring .= "    and thisyear = $futureyear ";
   $listobject->querystring .= "    and subshedid in ( ";
   $listobject->querystring .= "       select subshedid from tmp_landreg group by subshedid ";
   $listobject->querystring .= "    ) ";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }

   $listobject->querystring = " update scen_lrpopproject ";
   $listobject->querystring .= " set agresist = ";
   $listobject->querystring .= "    CASE ";
   $listobject->querystring .= "       WHEN agresist < $minresist THEN $minresist ";
   $listobject->querystring .= "       WHEN agresist > $maxresist THEN $maxresist ";
   $listobject->querystring .= "    END ";
   $listobject->querystring .= " WHERE ( agresist < $minresist ) ";
   $listobject->querystring .= "    OR ( agresist > $maxresist ) ";
   $listobject->querystring .= "    AND scenarioid = $scenarioid ";
   $listobject->querystring .= "    AND thisyear = $futureyear ";
   $listobject->querystring .= "    AND subshedid in ( ";
   $listobject->querystring .= "       select subshedid from tmp_landreg group by subshedid ";
   $listobject->querystring .= "    ) ";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }

   $listobject->querystring = "  update scen_lrpopproject ";
   $listobject->querystring .= " set rowarea = a.luarea, ";
   $listobject->querystring .= "    pasarea = b.luarea, ";
   $listobject->querystring .= "    hayarea = c.luarea ";
   $listobject->querystring .= " FROM  ";
   $listobject->querystring .= "    (select scenarioid, thisyear, subshedid, sum(a.luarea) as luarea ";
   $listobject->querystring .= "     from scen_lrsegs as a, landuses as b ";
   $listobject->querystring .= "     where a.luname = b.hspflu ";
   $listobject->querystring .= "        and a.projectid = b.projectid ";
   $listobject->querystring .= "        and b.landcover = 1 ";
   $listobject->querystring .= "        and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "        and a.thisyear = $lastyear ";
   $listobject->querystring .= "     group by scenarioid, thisyear, subshedid ";
   $listobject->querystring .= "     ) as a, ";
   $listobject->querystring .= "    (select scenarioid, thisyear, subshedid, sum(a.luarea) as luarea ";
   $listobject->querystring .= "     from scen_lrsegs as a, landuses as b ";
   $listobject->querystring .= "     where a.luname = b.hspflu ";
   $listobject->querystring .= "        and a.projectid = b.projectid ";
   $listobject->querystring .= "        and b.landcover = 3 ";
   $listobject->querystring .= "        and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "        and a.thisyear = $lastyear ";
   $listobject->querystring .= "     group by scenarioid, thisyear, subshedid ";
   $listobject->querystring .= "     ) as b, ";
   $listobject->querystring .= "    (select scenarioid, thisyear, subshedid, sum(a.luarea) as luarea ";
   $listobject->querystring .= "     from scen_lrsegs as a, landuses as b ";
   $listobject->querystring .= "     where a.luname = b.hspflu ";
   $listobject->querystring .= "        and a.projectid = b.projectid ";
   $listobject->querystring .= "        and b.landcover = 3 ";
   $listobject->querystring .= "        and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "        and a.thisyear = $lastyear ";
   $listobject->querystring .= "     group by scenarioid, thisyear, subshedid ";
   $listobject->querystring .= "     ) as c ";
   $listobject->querystring .= " WHERE scen_lrpopproject.scenarioid = $scenarioid ";
   $listobject->querystring .= "    AND scen_lrpopproject.subshedid = a.subshedid ";
   $listobject->querystring .= "    AND scen_lrpopproject.subshedid = b.subshedid ";
   $listobject->querystring .= "    AND scen_lrpopproject.subshedid = c.subshedid ";
   $listobject->querystring .= "    AND scen_lrpopproject.thisyear = $lastyear ";
   $listobject->querystring .= "    AND scen_lrpopproject.subshedid in ( ";
   $listobject->querystring .= "       select subshedid from tmp_landreg group by subshedid ";
   $listobject->querystring .= "    ) ";
   if ($debug) { print("<br>$listobject->querystring<br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }

}

function projectPopLandUseOld($listobject, $inpops, $debug) {
    # deprecated - this uses a more complicated method to extrapolate land use based on pops
   # coefficients from regression equations
   # these coefficients are currently hard-wired, but in the near future
   # I will use the regression functions in phpmath to do a dynamic
   # population regression for the selected area (must pass in subwatersheds)
   # vars = array( pasture, rowcrops, hay);
   $dairy = array(-0.016552756,0.086982767,0.306715063);
   $broiler = array(25.86555797,62.35392083,-52.03639919);
   $beef = array(0.377586945,0.142458621,0.149465703);

   # equation intercepts
   $dairyintercept = -4026.249511;
   $broilerintercept = -467402.2281;
   $beefintercept = -4439.105193;

   # input populations
   $dairypop = $inpops['dairy_heiffers'];
   $broilerpop = $inpops['broilers'];
   $beefpop = $inpops['beef_heiffers'];

   $dairyb = array($dairypop - $dairyintercept);
   $broilerb = array($broilerpop - $broilerintercept);
   $beefb = array($beefpop - $beefintercept);

   /*
   $avals = array($dairy,$swine,$broiler,$beef);
   $bvals = array($dairypop, $swinepop, $broilerpop, $beefpop);
   */
   $avals = array($dairy,$broiler,$beef);
   $bvals = array($dairyb, $broilerb, $beefb);

   $a = new Matrix($avals);
   $b = new Matrix($bvals);

   $ai = $a->inverse();

   $x = $ai->times($b);

   return $x;
}



function projectPopFromLandUse($listobject, $scenarioid, $projectid, $sources, $startyear, $endyear, $subsheds, $minpct, $debug) {

   # $minpct = the minimum percent of the base population allowed. i.e., the projected pop
   # cannot be smaller than $minpct of the base pop

   # create basic conditional clauses
   if (count($sources) > 0) {
      $srclist = "'" . join("','", $sources) . "'";
      $srccond = " sourceid in ($srclist) ";
      $asrccond = " a.sourceid in ($srclist) ";
      $bsrccond = " b.sourceid in ($srclist) ";
      $csrccond = " c.sourceid in ($srclist) ";
      $dsrccond = " d.poplink in ($srclist) ";
   } else {
      $srccond = ' (1 = 1) ';
      $asrccond = ' (1 = 1) ';
      $bsrccond = ' (1 = 1) ';
      $csrccond = ' (1 = 1) ';
      $dsrccond = ' (1 = 1) ';
   }
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $lrsegcond = " lrseg in ($sslist) ";
      $alrsegcond = " a.lrseg in ($sslist) ";
      $blrsegcond = " a.lrseg in ($sslist) ";
      $subshedcond = " a.lrseg in ($sslist) ";
   } else {
      $lrsegcond = ' (1 = 1) ';
      $alrsegcond = ' (1 = 1) ';
      $blrsegcond = ' (1 = 1) ';
      $subshedcond = ' (1 = 1) ';
   }
   if (strlen($startyear) > 0) {
      $yrcond = " thisyear = $startyear ";
      $ayrcond = " a.thisyear in ($startyear, $endyear) ";
      $byrcond = " b.thisyear in ($startyear, $endyear) ";
   } else {
      return;
   }


   # create regression
   $listobject->querystring = "  select b.thisyear, b.subshedid, a.sourceid, ";
   $listobject->querystring .= "    a.b ";
   $listobject->querystring .= "    + a.m_hay * b.hayarea ";
   $listobject->querystring .= "    + a.m_pas * c.pasarea ";
   $listobject->querystring .= "    + a.m_row * d.rowarea ";
   $listobject->querystring .= "    + a.m_urb * e.urbarea ";
   $listobject->querystring .= "    as regpop ";
   $listobject->querystring .= " into temp table tmp_regpop ";
   $listobject->querystring .= " from stat_pop_eq_f_of_land as a, ";
   $listobject->querystring .= "   ( ";
   $listobject->querystring .= "    select a.subshedid, a.thisyear, sum(a.luarea) as hayarea ";
   $listobject->querystring .= "    from scen_lrsegs as a, landuses as b ";
   $listobject->querystring .= "    where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "       and b.projectid = $projectid ";
   $listobject->querystring .= "       and b.hspflu = a.luname ";
   $listobject->querystring .= "       and b.landcover = 2 ";
   $listobject->querystring .= "       and $alrsegcond ";
   $listobject->querystring .= "       and $ayrcond ";
   $listobject->querystring .= "    group by a.subshedid, a.thisyear ";
   $listobject->querystring .= "   ) as b, ";
   $listobject->querystring .= "   ( ";
   $listobject->querystring .= "    select a.subshedid, a.thisyear, sum(a.luarea) as pasarea ";
   $listobject->querystring .= "    from scen_lrsegs as a, landuses as b ";
   $listobject->querystring .= "    where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "       and b.projectid = $projectid ";
   $listobject->querystring .= "       and b.hspflu = a.luname ";
   $listobject->querystring .= "       and b.landcover = 3 ";
   $listobject->querystring .= "       and $alrsegcond ";
   $listobject->querystring .= "       and $ayrcond ";
   $listobject->querystring .= "    group by a.subshedid, a.thisyear ";
   $listobject->querystring .= "   ) as c, ";
   $listobject->querystring .= "   ( ";
   $listobject->querystring .= "    select a.subshedid, a.thisyear, sum(a.luarea) as rowarea ";
   $listobject->querystring .= "    from scen_lrsegs as a, landuses as b ";
   $listobject->querystring .= "    where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "       and b.projectid = $projectid ";
   $listobject->querystring .= "       and b.hspflu = a.luname ";
   $listobject->querystring .= "       and b.landcover = 1 ";
   $listobject->querystring .= "       and $alrsegcond ";
   $listobject->querystring .= "       and $ayrcond ";
   $listobject->querystring .= "    group by a.subshedid, a.thisyear ";
   $listobject->querystring .= "   ) as d, ";
   $listobject->querystring .= "   ( ";
   $listobject->querystring .= "    select a.subshedid, a.thisyear, sum(a.luarea) as urbarea ";
   $listobject->querystring .= "    from scen_lrsegs as a, landuses as b ";
   $listobject->querystring .= "    where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "       and b.projectid = $projectid ";
   $listobject->querystring .= "       and b.hspflu = a.luname ";
   $listobject->querystring .= "       and b.major_lutype = 2 ";
   $listobject->querystring .= "       and $alrsegcond ";
   $listobject->querystring .= "       and $ayrcond ";
   $listobject->querystring .= "    group by a.subshedid, a.thisyear ";
   $listobject->querystring .= "   ) as e ";
   $listobject->querystring .= " where a.projectid = $projectid ";
   $listobject->querystring .= "    and b.subshedid = c.subshedid ";
   $listobject->querystring .= "    and b.subshedid = d.subshedid ";
   $listobject->querystring .= "    and b.subshedid = e.subshedid ";
   $listobject->querystring .= "    and b.thisyear = c.thisyear ";
   $listobject->querystring .= "    and b.thisyear = d.thisyear ";
   $listobject->querystring .= "    and b.thisyear = e.thisyear ";
   $listobject->querystring .= "    and $asrccond ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   if ($debug) {
      $listobject->querystring = "select * from tmp_regpop";
      $listobject->performQuery();
      $listobject->showList();
   }

   # calculate changes
   # combine sources - using the column linkpop. This column is set to be equal to the source id, unless it
   # is something like phytase version of another creature.
   $listobject->querystring = "  select b.thisyear, b.subshedid, d.sourcename, d.poplink as sourceid, ";
   $listobject->querystring .= "    (b.regpop - a.regpop)/abs(a.regpop) as pct_change, ";
   $listobject->querystring .= "    c.thisyear as baseyear,  ";
   $listobject->querystring .= "    c.sourcepop as base_pop,  ";
   $listobject->querystring .= "    (c.sourcepop * d.avgweight) / e.auweight as base_aucount,  ";
   $listobject->querystring .= "    ( c.sourcepop + ";
   $listobject->querystring .= "       c.sourcepop * (b.regpop - a.regpop)/abs(a.regpop) ) as pred_pop, ";
   $listobject->querystring .= "    ( c.sourcepop + ";
   $listobject->querystring .= "       c.sourcepop * (b.regpop - a.regpop)/abs(a.regpop)  ";
   $listobject->querystring .= "    ) * d.avgweight / e.auweight as pred_aucount ";
   $listobject->querystring .= " into temp table tmp_estpops ";
   $listobject->querystring .= " from tmp_regpop as a, tmp_regpop as b, scen_sourcepops as c, sources as d, ";
   $listobject->querystring .= "    scen_sourceloadtype as e ";
   $listobject->querystring .= " where a.thisyear = $startyear ";
   $listobject->querystring .= "    and b.thisyear = $endyear ";
   $listobject->querystring .= "    and c.thisyear = $startyear ";
   $listobject->querystring .= "    and b.subshedid = a.subshedid ";
   $listobject->querystring .= "    and b.subshedid = c.subshedid ";
   $listobject->querystring .= "    and b.sourceid = a.sourceid ";
   # get both linked populations and the selected population (such as broilers and phy_broilers)
   # since a separate regression is NOT used for base pops (broilers) and derived pops(phy_broilers)
   $listobject->querystring .= "    and b.sourceid = d.poplink ";
   $listobject->querystring .= "    and d.sourceid = c.sourceid ";
   $listobject->querystring .= "    and d.typeid = e.typeid ";
   # end linkage
   $listobject->querystring .= "    and c.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and e.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and d.projectid = $projectid ";
   $listobject->querystring .= "    and $csrccond ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();

   $listobject->querystring = "  update tmp_estpops set pred_aucount = $minpct * base_aucount, ";
   $listobject->querystring .= "    pred_pop = $minpct * base_pop ";
   $listobject->querystring .= " where pred_pop < $minpct * base_pop ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();

   if ($debug) {
      $listobject->querystring = "select * from tmp_estpops";
      $listobject->performQuery();
      $listobject->showList();
   }

}

function graphLandPopPops($listobject, $goutdir, $goutpath, $sources, $allsegs, $srcyears, $targetyears, $scenarioid, $debug) {

   $lus = join(", ", $sources);
   # create basic conditional clauses
   if (count($sources) > 0) {
      $srclist = "'" . join("','", $sources) . "'";
      $srccond = " sourceid in ($srclist) ";
      $asrccond = " a.sourceid in ($srclist) ";
   } else {
      $srccond = ' (1 = 1) ';
      $asrccond = ' (1 = 1) ';
   }


         $listobject->querystring = "  ( select baseyear as thisyear, sum(base_aucount) as thisaucount, 0.0 as pred_aucount ";
         $listobject->querystring .= "  from tmp_estpops ";
         $listobject->querystring .= "  group by baseyear ";
         $listobject->querystring .= "  order by baseyear ";
         $listobject->querystring .= " ) UNION ( ";
         $listobject->querystring .= "   select thisyear as thisyear, 0.0 as thisaucount, sum(pred_aucount) as pred_aucount ";
         $listobject->querystring .= "  from tmp_estpops ";
         $listobject->querystring .= "  group by thisyear ";
         $listobject->querystring .= "  order by thisyear ";
         $listobject->querystring .= " )";
         #print("<br>$listobject->querystring ; <br>");
         $listobject->performquery();
         #$listobject->showlist();



   # get records for all input years
   $listobject->querystring = " select  ";
   $listobject->querystring .= "    CASE  ";
   $listobject->querystring .= "       WHEN b.thisyear IS NULL THEN a.thisyear ";
   $listobject->querystring .= "       ELSE b.thisyear ";
   $listobject->querystring .= "    END as thisyear, ";
   $listobject->querystring .= "    CASE  ";
   $listobject->querystring .= "       WHEN a.thisyear IS NULL THEN 0.0 ";
   $listobject->querystring .= "       ELSE sum(a.totalau) ";
   $listobject->querystring .= "    END as totalau ";
   $listobject->querystring .= " from ";
   $listobject->querystring .= "    ( select thisyear, totalau ";
   $listobject->querystring .= "      from tmp_grouppop ";
   $listobject->querystring .= "      where $srccond ";
   $listobject->querystring .= "    ) as a  ";
   $listobject->querystring .= " full join  ";
   $listobject->querystring .= " (select thisyear from tmp_grpauextrap group by thisyear) as b ";
   $listobject->querystring .= " on (a.thisyear = b.thisyear) ";
   $listobject->querystring .= " group by a.thisyear, b.thisyear ";
   $listobject->querystring .= " order by b.thisyear ";
   if ($debug) { print("$listobject->querystring <br>"); }
   $listobject->performquery();
   #$listobject->showList();
   $lurecs = $listobject->queryrecords;

   # get all best-fit years
   $listobject->querystring = "select thisyear, sum(totalau) as totalau from tmp_grpauextrap group by thisyear order by thisyear";
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
      $listobject->querystring .= "       ELSE a.totalau ";
      $listobject->querystring .= "    END as totalau ";
      $listobject->querystring .= " from ";
      $listobject->querystring .= "    (select thisyear, sum(totalau) as totalau ";
      $listobject->querystring .= "     from tmp_grpauextrap ";
      $listobject->querystring .= "     where thisyear in ($targetyears) ";
      $listobject->querystring .= "        and $srccond ";
      $listobject->querystring .= "     group by thisyear";
      $listobject->querystring .= "    ) as a  ";
      $listobject->querystring .= " full join  ";
      $listobject->querystring .= " (select thisyear from tmp_grpauextrap group by thisyear) as b ";
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
   $lugraph['ycol'] = 'totalau';
   $lugraph['color'] = 'orange';
   $lugraph['ylegend'] = 'Historic';

   # a totally trasnparent (outline only) display of the best fit line
   $bfgraph = array();
   $bfgraph['graphrecs'] = $bfrecs;
   $bfgraph['xcol'] = 'thisyear';
   $bfgraph['ycol'] = 'totalau';
   $bfgraph['color'] = 'brown';
   $bfgraph['ylegend'] = 'Best Fit';
   $bfgraph['alpha'] = 1.0;

   # selected records to extrapolate from the best fit in blue
   $extrapgraph = array();
   $extrapgraph['graphrecs'] = $extraprecs;
   $extrapgraph['xcol'] = 'thisyear';
   $extrapgraph['ycol'] = 'totalau';
   $extrapgraph['color'] = 'blue';
   $extrapgraph['ylegend'] = 'Targeted';
   $extrapgraph['alpha'] = 0.3;
   $multibar = array('title'=>"Historic versus Best Fit for: $lus", 'xlabel'=>'Year', 'bargraphs'=>array($lugraph, $bfgraph, $extrapgraph));

   $bfgraph = showGenericMultiBar($goutdir, $goutpath, $multibar, $debug);
   return $bfgraph;

}


function graphBestFitPops($listobject, $goutdir, $goutpath, $sources, $allsegs, $srcyears, $targetyears, $scenarioid, $debug) {

   $lus = join(", ", $sources);
   # create basic conditional clauses
   if (count($sources) > 0) {
      $srclist = "'" . join("','", $sources) . "'";
      $srccond = " sourceid in ($srclist) ";
      $asrccond = " a.sourceid in ($srclist) ";
   } else {
      $srccond = ' (1 = 1) ';
      $asrccond = ' (1 = 1) ';
   }

   # get records for all input years
   $listobject->querystring = " select  ";
   $listobject->querystring .= "    CASE  ";
   $listobject->querystring .= "       WHEN b.thisyear IS NULL THEN a.thisyear ";
   $listobject->querystring .= "       ELSE b.thisyear ";
   $listobject->querystring .= "    END as thisyear, ";
   $listobject->querystring .= "    CASE  ";
   $listobject->querystring .= "       WHEN a.thisyear IS NULL THEN 0.0 ";
   $listobject->querystring .= "       ELSE sum(a.totalau) ";
   $listobject->querystring .= "    END as totalau ";
   $listobject->querystring .= " from ";
   $listobject->querystring .= "    ( select thisyear, totalau ";
   $listobject->querystring .= "      from tmp_grouppop ";
   $listobject->querystring .= "      where $srccond ";
   $listobject->querystring .= "    ) as a  ";
   $listobject->querystring .= " full join  ";
   $listobject->querystring .= " (select thisyear from tmp_grpauextrap group by thisyear) as b ";
   $listobject->querystring .= " on (a.thisyear = b.thisyear) ";
   $listobject->querystring .= " group by a.thisyear, b.thisyear ";
   $listobject->querystring .= " order by b.thisyear ";
   if ($debug) { print("$listobject->querystring <br>"); }
   $listobject->performquery();
   #$listobject->showList();
   $lurecs = $listobject->queryrecords;

   # get all best-fit years
   $listobject->querystring = "select thisyear, sum(totalau) as totalau from tmp_grpauextrap group by thisyear order by thisyear";
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
      $listobject->querystring .= "       ELSE a.totalau ";
      $listobject->querystring .= "    END as totalau ";
      $listobject->querystring .= " from ";
      $listobject->querystring .= "    (select thisyear, sum(totalau) as totalau ";
      $listobject->querystring .= "     from tmp_grpauextrap ";
      $listobject->querystring .= "     where thisyear in ($targetyears) ";
      $listobject->querystring .= "        and $srccond ";
      $listobject->querystring .= "     group by thisyear";
      $listobject->querystring .= "    ) as a  ";
      $listobject->querystring .= " full join  ";
      $listobject->querystring .= " (select thisyear from tmp_grpauextrap group by thisyear) as b ";
      $listobject->querystring .= " on (a.thisyear = b.thisyear) ";
      $listobject->querystring .= " order by b.thisyear ";
      if ($debug) { print("$listobject->querystring <br>"); }
      $listobject->performquery();
      if ($debug) { $listobject->showList(); }
      $extraprecs = $listobject->queryrecords;
   }

   $lugraph = array();
   $lugraph['graphrecs'] = $lurecs;
   $lugraph['xcol'] = 'thisyear';
   $lugraph['ycol'] = 'totalau';
   $lugraph['color'] = 'orange';
   $lugraph['ylegend'] = 'Historic';

   # a totally trasnparent (outline only) display of the best fit line
   $bfgraph = array();
   $bfgraph['graphrecs'] = $bfrecs;
   $bfgraph['xcol'] = 'thisyear';
   $bfgraph['ycol'] = 'totalau';
   $bfgraph['color'] = 'brown';
   $bfgraph['ylegend'] = 'Best Fit';
   $bfgraph['alpha'] = 1.0;

   # selected records to extrapolate from the best fit in blue
   $extrapgraph = array();
   $extrapgraph['graphrecs'] = $extraprecs;
   $extrapgraph['xcol'] = 'thisyear';
   $extrapgraph['ycol'] = 'totalau';
   $extrapgraph['color'] = 'blue';
   $extrapgraph['ylegend'] = 'Targeted';
   $extrapgraph['alpha'] = 0.3;
   $multibar = array('title'=>"Historic versus Best Fit for: $lus", 'xlabel'=>'Year', 'bargraphs'=>array($lugraph, $bfgraph, $extrapgraph));

   $bfgraph = showGenericMultiBar($goutdir, $goutpath, $multibar, $debug);
   return $bfgraph;

}

?>