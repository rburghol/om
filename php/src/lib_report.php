<?php




/* *************************************************** */
/* ***********     Reporting Functions    ************ */
/* *************************************************** */

function projectModeledEOF($listobject, $projectid, $scenarioid, $subsheds, $thisyear, $debug) {

   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
      $bsubshedcond = " b.lrseg in ($sslist) ";
   } else {
      $subshedcond = ' (1 = 1) ';
      $bsubshedcond = ' (1 = 1) ';
   }

   $listobject->querystring = "  select a.constit, sum(a.eof_target * b.luarea) AS eof_total, ";
   $listobject->querystring .= "    sum(a.eof_target * b.luarea)/2000.0 AS eof_tons, ";
   $listobject->querystring .= "    sum(b.luarea) AS luarea ";
   $listobject->querystring .= " from eof_model_total as a, scen_lrsegs as b ";
   $listobject->querystring .= " where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and a.landseg = b.landseg ";
   $listobject->querystring .= "    and a.luname = b.luname ";
   $listobject->querystring .= "    and b.thisyear = $thisyear ";
   $listobject->querystring .= "    and $bsubshedcond ";
   $listobject->querystring .= " group by a.constit ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   return $listobject->queryrecords;

}

function projectModeledEOFGroups($listobject, $projectid, $scenarioid, $subsheds, $thisyear, $constit, $debug) {

   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
      $bsubshedcond = " b.lrseg in ($sslist) ";
   } else {
      $subshedcond = ' (1 = 1) ';
      $bsubshedcond = ' (1 = 1) ';
   }

   if (strlen($constit) > 0) {
      $constitlist = "'" . join("','", split(',', $constit)) . "'";
      $constitcond = " a.constit in ($constitlist) ";
   } else {
      $constitcond = " ( 1 = 1) ";
   }


   $listobject->querystring = "  select a.constit, d.lutypename, sum(a.eof_target * b.luarea) AS eof_total, ";
   $listobject->querystring .= "    sum(a.eof_target * b.luarea)/2000.0 AS eof_tons, ";
   $listobject->querystring .= "    sum(b.luarea) AS luarea ";
   $listobject->querystring .= " from eof_model_total as a, scen_lrsegs as b, landuses as c, major_lutype as d ";
   $listobject->querystring .= " where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and c.projectid = $projectid ";
   $listobject->querystring .= "    and a.landseg = b.landseg ";
   $listobject->querystring .= "    and a.luname = b.luname ";
   $listobject->querystring .= "    and b.luname = c.hspflu ";
   $listobject->querystring .= "    and d.lutype = c.major_lutype ";
   $listobject->querystring .= "    and b.thisyear = $thisyear ";
   $listobject->querystring .= "    and $constitcond ";
   $listobject->querystring .= "    and $bsubshedcond ";
   $listobject->querystring .= " group by a.constit, d.lutypename ";
   $listobject->querystring .= " ORDER by a.constit, d.lutypename ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   return $listobject->queryrecords;

}

function showModeledDelivered($listobject, $projectid, $scenarioid, $subsheds, $thisyear, $constit, $debug) {

   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
      $asubshedcond = " a.lrseg in ($sslist) ";
      $bsubshedcond = " b.lrseg in ($sslist) ";
   } else {
      $subshedcond = ' (1 = 1) ';
      $asubshedcond = ' (1 = 1) ';
      $bsubshedcond = ' (1 = 1) ';
   }

   if (strlen($constit) > 0) {
      $constitlist = join(",", split(',', $constit));
      $constitcond = " constit = $constit ";
      $aconstitcond = " a.constit = $constit ";
      $econstitcond = " e.constit = $constit ";
   } else {
      $constitcond = " ( 1 = 1) ";
      $aconstitcond = " ( 1 = 1) ";
      $econstitcond = " ( 1 = 1) ";
   }


   $listobject->querystring = "  select c.landuse as luname, a.constit as numconstit, ";
   $listobject->querystring .= "    sum(e.eos) AS eos_total, ";
   $listobject->querystring .= "    sum(e.eos)/2000.0 AS eos_tons, ";
   $listobject->querystring .= "    sum(a.delivered) AS delivered_total, ";
   $listobject->querystring .= "    sum(a.delivered)/2000.0 AS delivered_tons, ";
   $listobject->querystring .= "    sum(e.luarea) as luarea, ";
   $listobject->querystring .= "    sum(e.eos)/sum(e.luarea) as eos_perarea, ";
   $listobject->querystring .= "    sum(a.delivered)/sum(e.luarea) as del_perarea ";
   $listobject->querystring .= " from scen_model_delivered as a left outer join scen_model_eos as e ";
   $listobject->querystring .= " on ( e.lrseg = a.lrseg ";
   $listobject->querystring .= "        and e.scenarioid = $scenarioid ";
   $listobject->querystring .= "        and $econstitcond ";
   $listobject->querystring .= "        and a.luname = e.luname ";
   $listobject->querystring .= "        and e.luarea > 0 ) ";
   $listobject->querystring .= "    ";
   $listobject->querystring .= " left join landuses as c ";
   $listobject->querystring .= " on ( c.projectid = $projectid ";
   $listobject->querystring .= "        and a.luname = c.hspflu ";
   $listobject->querystring .= "        and e.luname = c.hspflu ) ";
   $listobject->querystring .= " where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and $asubshedcond ";
   $listobject->querystring .= "    and $aconstitcond ";
   $listobject->querystring .= " GROUP BY a.constit, c.landuse ";
   $listobject->querystring .= " ORDER BY a.constit, c.landuse ";

   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   return $listobject->queryrecords;

}


function showModeledDeliveredGroups($listobject, $projectid, $scenarioid, $subsheds, $thisyear, $constit, $debug) {

   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
      $asubshedcond = " a.lrseg in ($sslist) ";
      $bsubshedcond = " b.lrseg in ($sslist) ";
   } else {
      $subshedcond = ' (1 = 1) ';
      $asubshedcond = ' (1 = 1) ';
      $bsubshedcond = ' (1 = 1) ';
   }

   if (strlen($constit) > 0) {
      $constitlist = join(",", split(',', $constit));
      $constitcond = " constit = $constit ";
      $aconstitcond = " a.constit = $constit ";
      $econstitcond = " e.constit = $constit ";
   } else {
      $constitcond = " ( 1 = 1) ";
      $aconstitcond = " ( 1 = 1) ";
      $econstitcond = " ( 1 = 1) ";
   }


   $listobject->querystring = "( select d.lutypename, a.constit as numconstit, ";
   $listobject->querystring .= "    sum(a.eos) AS eos_total, ";
   $listobject->querystring .= "    sum(a.eos)/2000.0 AS eos_tons, ";
   $listobject->querystring .= "    sum(e.delivered) AS delivered_total, ";
   $listobject->querystring .= "    sum(e.delivered)/2000.0 AS delivered_tons ";
   $listobject->querystring .= " from scen_model_eos as a left join landuses as c ";
   $listobject->querystring .= " on ( c.projectid = $projectid ";
   $listobject->querystring .= "        and a.luname = c.hspflu ) ";
   $listobject->querystring .= " left outer join scen_model_delivered as e ";
   $listobject->querystring .= " on ( e.lrseg = a.lrseg ";
   $listobject->querystring .= "        and e.scenarioid = $scenarioid ";
   $listobject->querystring .= "        and $econstitcond ";
   $listobject->querystring .= "        and e.luname = c.hspflu ";
   $listobject->querystring .= "        and e.luarea > 0 ) ";
   $listobject->querystring .= "    ";
   $listobject->querystring .= " left join major_lutype as d ";
   $listobject->querystring .= " on ( d.lutype = c.major_lutype ) ";
   $listobject->querystring .= " where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and $asubshedcond ";
   $listobject->querystring .= "    and $aconstitcond ";
   $listobject->querystring .= " group by a.constit, d.lutypename ";
   $listobject->querystring .= " ORDER by a.constit, d.lutypename ";


   $listobject->querystring .= " ) UNION ( ";
   $listobject->querystring .= " SELECT d.classname, a.constit as numconstit, e.eos_total, e.eos_tons, ";
   $listobject->querystring .= "     a.delivered_total, a.delivered_tons ";
   $listobject->querystring .= " FROM ";
   $listobject->querystring .= "    (select e.luname, e.constit, ";
   $listobject->querystring .= "        sum(e.eos ) AS eos_total, ";
   $listobject->querystring .= "        sum(e.eos )/2000.0 AS eos_tons ";
   $listobject->querystring .= "     from scen_modelps_eos as e, ";
   $listobject->querystring .= "     (select riverseg from lrseg_info as b ";
   $listobject->querystring .= "        where $bsubshedcond  ";
   $listobject->querystring .= "        and b.projectid = $projectid ";
   $listobject->querystring .= "     group by riverseg ) as b ";
   $listobject->querystring .= "     where e.riverseg = b.riverseg ";
   $listobject->querystring .= "        and e.scenarioid = $scenarioid ";
   $listobject->querystring .= "        and $econstitcond ";
   $listobject->querystring .= "     group by e.luname, e.constit ";
   $listobject->querystring .= "    ) as e ";
   $listobject->querystring .= " left outer join ";
   $listobject->querystring .= "    (select a.luname, a.constit, ";
   $listobject->querystring .= "        sum(a.delivered ) AS delivered_total, ";
   $listobject->querystring .= "        sum(a.delivered )/2000.0 AS delivered_tons ";
   $listobject->querystring .= "     from scen_modelps_delivered as a, ";
   $listobject->querystring .= "     (select riverseg from lrseg_info as b ";
   $listobject->querystring .= "        where $bsubshedcond  ";
   $listobject->querystring .= "        and b.projectid = $projectid ";
   $listobject->querystring .= "     group by riverseg ) as b ";
   $listobject->querystring .= "     where a.riverseg = b.riverseg ";
   $listobject->querystring .= "        and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "        and $aconstitcond ";
   $listobject->querystring .= "     group by a.luname, a.constit ";
   $listobject->querystring .= "    ) as a ";
   $listobject->querystring .= " on ( a.luname = e.luname ) ";
   $listobject->querystring .= " left join sourceclass as d ";
   $listobject->querystring .= " on ( e.luname = d.shortname ) ";
   $listobject->querystring .= " ORDER by d.classname ";
   $listobject->querystring .= " ) ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   return $listobject->queryrecords;

}



function showModeledNPSDetail($listobject, $projectid, $scenarioid, $subsheds, $thisyear, $constit, $debug) {

   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
      $asubshedcond = " a.lrseg in ($sslist) ";
      $bsubshedcond = " b.lrseg in ($sslist) ";
   } else {
      $subshedcond = ' (1 = 1) ';
      $asubshedcond = ' (1 = 1) ';
      $bsubshedcond = ' (1 = 1) ';
   }

   if (strlen($constit) > 0) {
      $constitlist = join(",", split(',', $constit));
      $constitcond = " constit = $constit ";
      $aconstitcond = " a.constit = $constit ";
      $econstitcond = " e.constit = $constit ";
   } else {
      $constitcond = " ( 1 = 1) ";
      $aconstitcond = " ( 1 = 1) ";
      $econstitcond = " ( 1 = 1) ";
   }


   $listobject->querystring = "  select a.lrseg, a.landseg, a.riverseg, c.landuse as luname, a.constit as numconstit, ";
   $listobject->querystring .= "    e.eos AS eos_total, ";
   $listobject->querystring .= "    e.eos/2000.0 AS eos_tons, ";
   $listobject->querystring .= "    a.delivered AS delivered_total, ";
   $listobject->querystring .= "    a.delivered/2000.0 AS delivered_tons, ";
   $listobject->querystring .= "    e.luarea as luarea, ";
   $listobject->querystring .= "    e.eos/e.luarea as eos_perarea, ";
   $listobject->querystring .= "    a.delivered/e.luarea as del_perarea ";
   $listobject->querystring .= " from scen_model_delivered as a left outer join scen_model_eos as e ";
   $listobject->querystring .= " on ( e.lrseg = a.lrseg ";
   $listobject->querystring .= "        and e.scenarioid = $scenarioid ";
   $listobject->querystring .= "        and $econstitcond ";
   $listobject->querystring .= "        and a.luname = e.luname ";
   $listobject->querystring .= "        and e.luarea > 0 ) ";
   $listobject->querystring .= "    ";
   $listobject->querystring .= " left join landuses as c ";
   $listobject->querystring .= " on ( c.projectid = $projectid ";
   $listobject->querystring .= "        and a.luname = c.hspflu ";
   $listobject->querystring .= "        and c.hspflu <> '' ";
   $listobject->querystring .= "        and c.hspflu is not null ";
   $listobject->querystring .= "        and e.luname = c.hspflu ) ";
   $listobject->querystring .= " where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and $asubshedcond ";
   $listobject->querystring .= "    and $aconstitcond ";
   $listobject->querystring .= " ORDER BY a.lrseg, a.constit, c.landuse ";

   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   return $listobject->queryrecords;

}


function showModeledPSDetail($listobject, $projectid, $scenarioid, $subsheds, $thisyear, $constit, $debug) {

   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
      $asubshedcond = " a.lrseg in ($sslist) ";
      $bsubshedcond = " b.lrseg in ($sslist) ";
      $esubshedcond = " e.lrseg in ($sslist) ";
   } else {
      $subshedcond = ' (1 = 1) ';
      $asubshedcond = ' (1 = 1) ';
      $bsubshedcond = ' (1 = 1) ';
      $esubshedcond = ' (1 = 1) ';
   }

   if (strlen($constit) > 0) {
      $constitlist = join(",", split(',', $constit));
      $constitcond = " constit = $constit ";
      $aconstitcond = " a.constit = $constit ";
      $econstitcond = " e.constit = $constit ";
   } else {
      $constitcond = " ( 1 = 1) ";
      $aconstitcond = " ( 1 = 1) ";
      $econstitcond = " ( 1 = 1) ";
   }


   $listobject->querystring = " SELECT e.lrseg, e.landseg, e.riverseg, e.luname, d.classname, ";
   $listobject->querystring .= "     a.constit as numconstit, e.eos, e.eos/2000.0 AS eos_tons, ";
   $listobject->querystring .= "     a.delivered, a.delivered/2000.0 AS delivered_tons ";
   $listobject->querystring .= " FROM scen_modelps_eos as e ";
   $listobject->querystring .= " left outer join scen_modelps_delivered as a ";
   $listobject->querystring .= " on ( a.luname = e.luname ";
   $listobject->querystring .= "     and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "     and $asubshedcond ";
   $listobject->querystring .= "     and $aconstitcond ";
   $listobject->querystring .= "     and a.lrseg = e.lrseg ) ";
   $listobject->querystring .= " left join sourceclass as d ";
   $listobject->querystring .= " on ( e.luname = d.shortname ) ";
   $listobject->querystring .= " where e.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and $esubshedcond ";
   $listobject->querystring .= "    and $econstitcond ";
   $listobject->querystring .= " ORDER by e.lrseg, d.classname ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   return $listobject->queryrecords;

}

function showPredictedDeliveredAll($listobject, $projectid, $scenarioid, $subsheds, $thisyear, $constit, $debug) {

   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
      $asubshedcond = " a.lrseg in ($sslist) ";
      $bsubshedcond = " b.lrseg in ($sslist) ";
   } else {
      $subshedcond = ' (1 = 1) ';
      $asubshedcond = ' (1 = 1) ';
      $bsubshedcond = ' (1 = 1) ';
   }

   if (strlen($constit) > 0) {
      $constitlist = join(",", split(',', $constit));
      $constitcond = " constit in ( $constit ) ";
      $aconstitcond = " a.constit in ( $constit ) ";
      $econstitcond = " e.constit in ( $constit ) ";
   } else {
      $constitcond = " constit in ( 1,2 ) ";
      $aconstitcond = " a.constit in ( 1,2 ) ";
      $econstitcond = " e.constit in ( 1,2 ) ";
   }


   $listobject->querystring = " select a.constit as constit, ";
   $listobject->querystring .= "    a.eos, (a.eos/2000.0) as eos_tons, ";
   $listobject->querystring .= "    b.delivered, (b.delivered/2000.0) as delivered_tons ";
   $listobject->querystring .= " from ";
   $listobject->querystring .= " ( select a.constit, sum(a.eos * b.luarea / a.luarea) AS eos ";
   $listobject->querystring .= "    from scen_model_eos as a, scen_lrsegs as b ";
   $listobject->querystring .= "    where b.thisyear = $thisyear ";
   $listobject->querystring .= "        and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "        and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "        and $asubshedcond ";
   $listobject->querystring .= "        and $bsubshedcond ";
   $listobject->querystring .= "        and $aconstitcond ";
   $listobject->querystring .= "        and a.lrseg = b.lrseg ";
   $listobject->querystring .= "        and a.luarea > 0 ";
   $listobject->querystring .= "        and a.luname = b.luname ";
   $listobject->querystring .= "    group by a.constit ";
   $listobject->querystring .= " ) as a, ";
   $listobject->querystring .= " ( select a.constit, sum(a.delivered * b.luarea / a.luarea) AS delivered ";
   $listobject->querystring .= "    from scen_model_delivered as a, scen_lrsegs as b ";
   $listobject->querystring .= "    where b.thisyear = $thisyear ";
   $listobject->querystring .= "        and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "        and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "        and $asubshedcond ";
   $listobject->querystring .= "        and $bsubshedcond ";
   $listobject->querystring .= "        and $aconstitcond ";
   $listobject->querystring .= "        and a.lrseg = b.lrseg ";
   $listobject->querystring .= "        and a.luarea > 0 ";
   $listobject->querystring .= "        and a.luname = b.luname ";
   $listobject->querystring .= "    group by a.constit ";
   $listobject->querystring .= " ) as b ";
   $listobject->querystring .= " where a.constit = b.constit ";
   $listobject->querystring .= " order by a.constit ";

   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   return $listobject->queryrecords;

}

function showPredictedDelivered($listobject, $projectid, $scenarioid, $subsheds, $thisyear, $constit, $debug) {

   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
      $asubshedcond = " a.lrseg in ($sslist) ";
      $bsubshedcond = " b.lrseg in ($sslist) ";
   } else {
      $subshedcond = ' (1 = 1) ';
      $asubshedcond = ' (1 = 1) ';
      $bsubshedcond = ' (1 = 1) ';
   }

   if (strlen($constit) > 0) {
      $constitlist = join(",", split(',', $constit));
      $constitcond = " constit = $constit ";
      $aconstitcond = " a.constit = $constit ";
      $econstitcond = " e.constit = $constit ";
   } else {
      $constitcond = " ( 1 = 1) ";
      $aconstitcond = " ( 1 = 1) ";
      $econstitcond = " ( 1 = 1) ";
   }


   $listobject->querystring = "  select c.landuse as luname, a.constit as numconstit, ";
   $listobject->querystring .= "    sum(e.eos * b.luarea / e.luarea) AS eos_total, ";
   $listobject->querystring .= "    sum(e.eos * b.luarea / e.luarea)/2000.0 AS eos_tons, ";
   $listobject->querystring .= "    sum(a.delivered * b.luarea / a.luarea) AS delivered_total, ";
   $listobject->querystring .= "    sum(a.delivered * b.luarea / a.luarea)/2000.0 AS delivered_tons, ";
   $listobject->querystring .= "    sum(b.luarea) as luarea ";
   $listobject->querystring .= " from scen_model_delivered as a left join scen_lrsegs as b ";
   $listobject->querystring .= " on ( a.lrseg = b.lrseg ";
   $listobject->querystring .= "        and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "        and $bsubshedcond ";
   $listobject->querystring .= "        and b.thisyear = $thisyear ";
   $listobject->querystring .= "        and a.luarea > 0 ";
   $listobject->querystring .= "        and a.luname = b.luname ) ";
   $listobject->querystring .= " left outer join scen_model_eos as e ";
   $listobject->querystring .= " on ( e.lrseg = b.lrseg ";
   $listobject->querystring .= "        and e.scenarioid = $scenarioid ";
   $listobject->querystring .= "        and $econstitcond ";
   $listobject->querystring .= "        and a.luname = e.luname ";
   $listobject->querystring .= "        and e.luarea > 0 ";
   $listobject->querystring .= "        and e.luname = b.luname ) ";
   $listobject->querystring .= "    ";
   $listobject->querystring .= " left join landuses as c ";
   $listobject->querystring .= " on ( c.projectid = $projectid ";
   $listobject->querystring .= "        and a.luname = c.hspflu ";
   $listobject->querystring .= "        and e.luname = c.hspflu ";
   $listobject->querystring .= "        and b.luname = c.hspflu ) ";
   $listobject->querystring .= " where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and $asubshedcond ";
   $listobject->querystring .= "    and $aconstitcond ";
   $listobject->querystring .= " GROUP BY a.constit, c.landuse ";
   $listobject->querystring .= " ORDER BY a.constit, c.landuse ";

   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   return $listobject->queryrecords;

}

function showPredictedDeliveredGroups($listobject, $projectid, $scenarioid, $subsheds, $thisyear, $constit, $debug) {

   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
      $asubshedcond = " a.lrseg in ($sslist) ";
      $bsubshedcond = " b.lrseg in ($sslist) ";
   } else {
      $subshedcond = ' (1 = 1) ';
      $asubshedcond = ' (1 = 1) ';
      $bsubshedcond = ' (1 = 1) ';
   }

   if (strlen($constit) > 0) {
      $constitlist = join(",", split(',', $constit));
      $constitcond = " constit = $constit ";
      $aconstitcond = " a.constit = $constit ";
      $econstitcond = " e.constit = $constit ";
   } else {
      $constitcond = " ( 1 = 1) ";
      $aconstitcond = " ( 1 = 1) ";
      $econstitcond = " ( 1 = 1) ";
   }


   $listobject->querystring = "( select d.lutypename, a.constit as numconstit, ";
   $listobject->querystring .= "    sum(e.eos * b.luarea / e.luarea) AS eos_total, ";
   $listobject->querystring .= "    sum(e.eos * b.luarea / e.luarea)/sum(b.luarea) AS eos_perarea, ";
   $listobject->querystring .= "    sum(e.eos * b.luarea / e.luarea)/2000.0 AS eos_tons, ";
   $listobject->querystring .= "    sum(a.delivered * b.luarea / a.luarea) AS delivered_total, ";
   $listobject->querystring .= "    sum(a.delivered * b.luarea / a.luarea)/sum(b.luarea) AS del_perarea, ";
   $listobject->querystring .= "    sum(a.delivered * b.luarea / a.luarea)/2000.0 AS delivered_tons ";
   $listobject->querystring .= " from scen_model_delivered as a left join scen_lrsegs as b ";
   $listobject->querystring .= " on ( a.lrseg = b.lrseg ";
   $listobject->querystring .= "        and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "        and b.thisyear = $thisyear ";
   $listobject->querystring .= "        and a.luarea > 0 ";
   $listobject->querystring .= "        and a.luname = b.luname ) ";
   $listobject->querystring .= " left join landuses as c ";
   $listobject->querystring .= " on ( c.projectid = $projectid ";
   $listobject->querystring .= "        and a.luname = c.hspflu ";
   $listobject->querystring .= "        and b.luname = c.hspflu ) ";
   $listobject->querystring .= " left outer join scen_model_eos as e ";
   $listobject->querystring .= " on ( e.lrseg = b.lrseg ";
   $listobject->querystring .= "        and e.scenarioid = $scenarioid ";
   $listobject->querystring .= "        and $econstitcond ";
   $listobject->querystring .= "        and e.luname = c.hspflu ";
   $listobject->querystring .= "        and e.luarea > 0 ) ";
   $listobject->querystring .= "    ";
   $listobject->querystring .= " left join major_lutype as d ";
   $listobject->querystring .= " on ( d.lutype = c.major_lutype ) ";
   $listobject->querystring .= " where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and $bsubshedcond ";
   $listobject->querystring .= "    and $asubshedcond ";
   $listobject->querystring .= "    and $aconstitcond ";
   $listobject->querystring .= " group by a.constit, d.lutypename ";
   $listobject->querystring .= " ORDER by a.constit, d.lutypename ";

   $listobject->querystring .= " ) UNION ( ";
   $listobject->querystring .= " SELECT d.classname, a.constit as numconstit, e.eos_total, 0.0 as eos_perarea, e.eos_tons, ";
   $listobject->querystring .= "     a.delivered_total, 0.0 as del_perarea, a.delivered_tons ";
   $listobject->querystring .= " FROM ";
   $listobject->querystring .= "    (select a.luname, a.constit, ";
   $listobject->querystring .= "        sum(a.delivered ) AS delivered_total, ";
   $listobject->querystring .= "        sum(a.delivered )/2000.0 AS delivered_tons ";
   $listobject->querystring .= "     from scen_modelps_delivered as a, ";
   $listobject->querystring .= "     (select riverseg from scen_lrsegs as b ";
   $listobject->querystring .= "        where $bsubshedcond  ";
   $listobject->querystring .= "        and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "        and b.thisyear = $thisyear ";
   $listobject->querystring .= "     group by riverseg ) as b ";
   $listobject->querystring .= "     where a.riverseg = b.riverseg ";
   $listobject->querystring .= "        and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "        and $aconstitcond ";
   $listobject->querystring .= "     group by a.luname, a.constit ";
   $listobject->querystring .= "    ) as a ";
   $listobject->querystring .= " left outer join ";
   $listobject->querystring .= "    (select e.luname, e.constit, ";
   $listobject->querystring .= "        sum(e.eos ) AS eos_total, ";
   $listobject->querystring .= "        sum(e.eos )/2000.0 AS eos_tons ";
   $listobject->querystring .= "     from scen_modelps_eos as e, ";
   $listobject->querystring .= "     (select riverseg from scen_lrsegs as b ";
   $listobject->querystring .= "        where $bsubshedcond  ";
   $listobject->querystring .= "        and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "        and b.thisyear = $thisyear ";
   $listobject->querystring .= "     group by riverseg ) as b ";
   $listobject->querystring .= "     where e.riverseg = b.riverseg ";
   $listobject->querystring .= "        and e.scenarioid = $scenarioid ";
   $listobject->querystring .= "        and $econstitcond ";
   $listobject->querystring .= "     group by e.luname, e.constit ";
   $listobject->querystring .= "    ) as e ";
   $listobject->querystring .= " on ( a.luname = e.luname ) ";
   $listobject->querystring .= " left join sourceclass as d ";
   $listobject->querystring .= " on ( a.luname = d.shortname ) ";
   $listobject->querystring .= " ORDER by d.classname ";
   $listobject->querystring .= " ) ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   return $listobject->queryrecords;

}


function reportLRSegBMPArea($listobject, $bmpids, $landuses, $subsheds, $scenarioid, $debug) {

   if ($bmpids == '') {


   $listobject->querystring = "select sum(bmparea) as totalarea ";
   $listobject->querystring .= " from scen_lrseg_bmps  ";
   $listobject->querystring .= " where ( ( bmpid in ($bmpids)) or ( '$bmpids' = '-1' ) ) ";
   $listobject->querystring .= "    and ( (luname in ($landuses)) or ( '$landuses' = '-1' ) ) ";
   $listobject->querystring .= "    and scenarioid = $scenarioid ";
}


}

function getHistLUArea($listobject, $subsheds, $scenarioid, $landuses, $thisyear, $debug) {

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   if (count($landuses) > 0) {
      $lulist = "'" . join("','", $landuses) . "'";
      $lucond = " luname in ($lulist) ";
      $alucond = " a.luname in ($lulist) ";
   } else {
      $lucond = ' 1 = 1 ';
      $alucond = ' 1 = 1 ';
   }
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
      $asubshedcond = " a.lrseg in ($sslist) ";
   } else {
      $subshedcond = ' (1 = 1) ';
      $asubshedcond = ' (1 = 1) ';
   }
   if (strlen($thisyear) > 0) {
      # optimize for speed
      $yrcond = " thisyear = $thisyear ";
      $ayrcond = " a.thisyear = $thisyear ";
      $byrcond = " b.thisyear = $thisyear ";
   } else {
      $yrcond = ' (1 = 1) ';
      $ayrcond = ' (1 = 1) ';
      $byrcond = ' (1 = 1) ';
   }

   $listobject->querystring = "select min(thisyear) as minyr, max(thisyear) as maxyr from scen_lrsegs ";
   $listobject->querystring .= " where scenarioid = $scenarioid ";
   $listobject->querystring .= "    and $yrcond ";
   $listobject->performQuery();
   $minyr = $listobject->getRecordValue(1,'minyr');
   $maxyr = $listobject->getRecordValue(1,'maxyr');
   $allyrs = array($minyr, $maxyr);

   $loyr = min($allyrs);
   $hiyr = max($allyrs);
   if ($loyr == '') {
      $loyr = $hiyr;
   }
   $exyrs = '';
   $exdel = '';

   $listobject->querystring = "create temp table tyears (thisyear integer) ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();

   for ($j = $loyr; $j <= $hiyr; $j++) {
      $listobject->querystring = " insert into tyears(thisyear) values ($j) ";
      if ($debug) { print("<br>$listobject->querystring ; <br>"); }
      $listobject->performQuery();
   }

   $listobject->queryrecords = array();

   $listobject->querystring = "  select b.thisyear, ";
   $listobject->querystring .= "    sum(a.luarea) AS luarea ";
   $listobject->querystring .= " from tyears as b ";
   $listobject->querystring .= " left outer join scen_lrsegs as a ";
   $listobject->querystring .= " on (a.thisyear = b.thisyear ";
   $listobject->querystring .= "    and $alucond ";
   $listobject->querystring .= "    and $asubshedcond ";
   $listobject->querystring .= "    and a.scenarioid = $scenarioid ";
   $listobject->querystring .= " ) ";
   $listobject->querystring .= " group by b.thisyear ";
   $listobject->querystring .= " ORDER by b.thisyear ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   return $listobject->queryrecords;

}


function getLRClassApplications($listobject, $subsheds, $scenarioid, $polltype, $thisyear, $debug) {

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   if (count($landuses) > 0) {
      $lulist = "'" . join("','", $landuses) . "'";
      $lucond = " luname in ($lulist) ";
   } else {
      $lucond = ' 1 = 1 ';
   }
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
      $bsubshedcond = " b.lrseg in ($sslist) ";
   } else {
      $subshedcond = ' (1 = 1) ';
      $bsubshedcond = ' (1 = 1) ';
   }
   if (strlen($thisyear) > 0) {
      # optimize for speed
      $yrcond = " thisyear = $thisyear ";
      $ayrcond = " a.thisyear = $thisyear ";
      $byrcond = " b.thisyear = $thisyear ";
   } else {
      $yrcond = ' (1 = 1) ';
      $ayrcond = ' (1 = 1) ';
      $byrcond = ' (1 = 1) ';
   }

   if (strlen($polltype) > 0) {
      $pollrcond = " thisyear in ($thisyear) ";
      $apollcond = " a.pollutanttype in ($polltype) ";
      $bpollcond = " b.pollutanttype in ($polltype) ";
   } else {
      $pollrcond = ' (1 = 1) ';
      $apollrcond = ' (1 = 1) ';
      $bpollrcond = ' (1 = 1) ';
   }

   $listobject->queryrecords = array();

   $listobject->querystring = "  select a.pollutanttype, a.sourceclass, ";
   $listobject->querystring .= "    sum(a.annualapplied * b.luarea) AS totalapplied ";
   $listobject->querystring .= " from scen_sourceperunitarea as a, scen_lrsegs as b ";
   $listobject->querystring .= " where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and $ayrcond ";
   $listobject->querystring .= "    and $byrcond ";
   $listobject->querystring .= "    and a.subshedid = b.subshedid ";
   $listobject->querystring .= "    and a.luname = b.luname ";
   $listobject->querystring .= "    and a.pollutanttype in ($polltype) ";
   $listobject->querystring .= "    and $bsubshedcond ";
   $listobject->querystring .= " group by a.pollutanttype, a.sourceclass ";
   $listobject->querystring .= " ORDER by a.pollutanttype, a.sourceclass ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   return $listobject->queryrecords;

}

function getSourceProduction($listobject, $subsheds, $scenarioid, $polltype, $thisyear, $debug) {

   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
      $bsubshedcond = " b.lrseg in ($sslist) ";
   } else {
      $subshedcond = ' (1 = 1) ';
      $bsubshedcond = ' (1 = 1) ';
   }
   if (strlen($thisyear) > 0) {
      # optimize for speed
      $yrcond = " thisyear = $thisyear ";
      $ayrcond = " a.thisyear = $thisyear ";
      $byrcond = " b.thisyear = $thisyear ";
   } else {
      $yrcond = ' (1 = 1) ';
      $ayrcond = ' (1 = 1) ';
      $byrcond = ' (1 = 1) ';
   }

   if (strlen($polltype) > 0) {
      $pollrcond = " thisyear in ($thisyear) ";
      $apollcond = " a.pollutanttype in ($polltype) ";
      $bpollcond = " b.pollutanttype in ($polltype) ";
   } else {
      $pollrcond = ' (1 = 1) ';
      $apollrcond = ' (1 = 1) ';
      $bpollrcond = ' (1 = 1) ';
   }

   $listobject->querystring = " select trim(trailing ',' from concat_agg(subshedid || ',')) as subshedids ";
   $listobject->querystring .= " from ";
   $listobject->querystring .= " ( ";
   $listobject->querystring .= "    select subshedid from scen_lrsegs ";
   $listobject->querystring .= "    where scenarioid = $scenarioid ";
   $listobject->querystring .= "       and $yrcond ";
   $listobject->querystring .= "       and $subshedcond ";
   $listobject->querystring .= "    group by subshedid ) as foo ";
   if ($debug) { print("$listobject->querystring ; <BR>"); }
   $listobject->performQuery();
   $subshedids = $listobject->getRecordValue(1,'subshedids');
   allLoadsSummarized($listobject, $projectid, 1, $debug, $thisyear, $scenarioid, $subshedids);

   $listobject->queryrecords = array();

   $listobject->querystring = "  select a.subshedid, a.sourcename, a.pollutanttype, a.sourceclass, ";
   $listobject->querystring .= "    sum(a.annualproduced) AS totalproduced, ";
   $listobject->querystring .= "    sum(a.annualvolatilized) AS totalvolatilized, ";
   $listobject->querystring .= "    sum(a.annualdieoff) AS totalattenuated, ";
   $listobject->querystring .= "    sum(a.annualapplied) AS totalapplied ";
   $listobject->querystring .= " from all_loads as a ";
   $listobject->querystring .= " where a.pollutanttype in ($polltype) ";
   $listobject->querystring .= " group by a.subshedid, a.sourcename, a.pollutanttype, a.sourceclass ";
   $listobject->querystring .= " ORDER by a.subshedid, a.sourcename, a.pollutanttype, a.sourceclass ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();

   return $listobject->queryrecords;

}

function getLRClassProduction($listobject, $subsheds, $scenarioid, $polltype, $thisyear, $debug) {

   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
      $bsubshedcond = " b.lrseg in ($sslist) ";
   } else {
      $subshedcond = ' (1 = 1) ';
      $bsubshedcond = ' (1 = 1) ';
   }
   if (strlen($thisyear) > 0) {
      # optimize for speed
      $yrcond = " thisyear = $thisyear ";
      $ayrcond = " a.thisyear = $thisyear ";
      $byrcond = " b.thisyear = $thisyear ";
   } else {
      $yrcond = ' (1 = 1) ';
      $ayrcond = ' (1 = 1) ';
      $byrcond = ' (1 = 1) ';
   }

   if (strlen($polltype) > 0) {
      $pollrcond = " thisyear in ($thisyear) ";
      $apollcond = " a.pollutanttype in ($polltype) ";
      $bpollcond = " b.pollutanttype in ($polltype) ";
   } else {
      $pollrcond = ' (1 = 1) ';
      $apollrcond = ' (1 = 1) ';
      $bpollrcond = ' (1 = 1) ';
   }

   $listobject->querystring = " select trim(trailing ',' from concat_agg(subshedid || ',')) as subshedids ";
   $listobject->querystring .= " from ";
   $listobject->querystring .= " ( ";
   $listobject->querystring .= "    select subshedid from scen_lrsegs ";
   $listobject->querystring .= "    where scenarioid = $scenarioid ";
   $listobject->querystring .= "       and $yrcond ";
   $listobject->querystring .= "       and $subshedcond ";
   $listobject->querystring .= "    group by subshedid ) as foo ";
   if ($debug) { print("$listobject->querystring ; <BR>"); }
   $listobject->performQuery();
   $subshedids = $listobject->getRecordValue(1,'subshedids');
   allLoadsSummarized($listobject, $projectid, 1, $debug, $thisyear, $scenarioid, $subshedids);

   $listobject->queryrecords = array();

   $listobject->querystring = "  select a.pollutanttype, a.sourceclass, ";
   $listobject->querystring .= "    sum(a.annualproduced) AS totalproduced, ";
   $listobject->querystring .= "    sum(a.annualvolatilized) AS totalvolatilized, ";
   $listobject->querystring .= "    sum(a.annualdieoff) AS totalattenuated, ";
   $listobject->querystring .= "    sum(a.annualapplied) AS totalapplied ";
   $listobject->querystring .= " from all_loads as a ";
   $listobject->querystring .= " where a.pollutanttype in ($polltype) ";
   $listobject->querystring .= " group by a.pollutanttype, a.sourceclass ";
   $listobject->querystring .= " ORDER by a.pollutanttype, a.sourceclass ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   return $listobject->queryrecords;

}

function showProductionDetails($listobject, $projectid, $scenarioid, $subsheds, $polltype, $thisyear, $debug) {

   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
      $bsubshedcond = " b.lrseg in ($sslist) ";
   } else {
      $subshedcond = ' (1 = 1) ';
      $bsubshedcond = ' (1 = 1) ';
   }
   if (strlen($thisyear) > 0) {
      # optimize for speed
      $yrcond = " thisyear = $thisyear ";
      $ayrcond = " a.thisyear = $thisyear ";
      $byrcond = " b.thisyear = $thisyear ";
   } else {
      $yrcond = ' (1 = 1) ';
      $ayrcond = ' (1 = 1) ';
      $byrcond = ' (1 = 1) ';
   }

   if (strlen($polltype) > 0) {
      $pollrcond = " thisyear in ($thisyear) ";
      $apollcond = " a.pollutanttype in ($polltype) ";
      $bpollcond = " b.pollutanttype in ($polltype) ";
   } else {
      $pollrcond = ' (1 = 1) ';
      $apollrcond = ' (1 = 1) ';
      $bpollrcond = ' (1 = 1) ';
   }

   $listobject->querystring = " select trim(trailing ',' from concat_agg(subshedid || ',')) as subshedids ";
   $listobject->querystring .= " from ";
   $listobject->querystring .= " ( ";
   $listobject->querystring .= "    select subshedid from scen_lrsegs ";
   $listobject->querystring .= "    where scenarioid = $scenarioid ";
   $listobject->querystring .= "       and $yrcond ";
   $listobject->querystring .= "       and $subshedcond ";
   $listobject->querystring .= "    group by subshedid ) as foo ";
   if ($debug) { print("$listobject->querystring ; <BR>"); }
   $listobject->performQuery();
   $subshedids = $listobject->getRecordValue(1,'subshedids');
   allLoadsSummarized($listobject, $projectid, 1, $debug, $thisyear, $scenarioid, $subshedids);

   $listobject->queryrecords = array();

   $listobject->querystring = "  select c.pollutantname, a.subshedid, a.thisyear, b.sourcename, ";
   $listobject->querystring .= "    sum(a.annualproduced) AS totalproduced, ";
   $listobject->querystring .= "    sum(a.annualvolatilized) AS totalvolatilized, ";
   $listobject->querystring .= "    sum(a.annualdieoff) AS totalattenuated, ";
   $listobject->querystring .= "    sum(a.annualapplied) AS totalapplied ";
   $listobject->querystring .= " from all_loads as a, scen_sources as b, pollutanttype as c ";
   $listobject->querystring .= " where a.pollutanttype in ($polltype) ";
   $listobject->querystring .= "    and a.sourceid = b.sourceid ";
   $listobject->querystring .= "    and c.typeid = a.pollutanttype ";
   $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
   $listobject->querystring .= " group by c.pollutantname, a.thisyear, a.subshedid, b.sourcename ";
   $listobject->querystring .= " ORDER by a.subshedid, c.pollutantname, b.sourcename ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   return $listobject->queryrecords;

}


function getCropLandUses($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $cropname, $debug) {

   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
      $bsubshedcond = " b.lrseg in ($sslist) ";
   } else {
      $subshedcond = ' (1 = 1) ';
      $bsubshedcond = ' (1 = 1) ';
   }
   if (strlen($thisyear) > 0) {
      # optimize for speed
      $yrcond = " thisyear = $thisyear ";
      $ayrcond = " a.thisyear = $thisyear ";
      $byrcond = " b.thisyear = $thisyear ";
   } else {
      $yrcond = ' (1 = 1) ';
      $ayrcond = ' (1 = 1) ';
      $byrcond = ' (1 = 1) ';
   }

   if (strlen($cropname) > 0) {
      # optimize for speed
      $cropcond = " cropname = '$cropname' ";
   } else {
      $cropcond = ' (1 = 1) ';
   }

   $listobject->querystring = " select trim(trailing ',' from concat_agg(subshedid || ',')) as subshedids ";
   $listobject->querystring .= " from ";
   $listobject->querystring .= " ( ";
   $listobject->querystring .= "    select subshedid from scen_lrsegs ";
   $listobject->querystring .= "    where scenarioid = $scenarioid ";
   $listobject->querystring .= "       and $yrcond ";
   $listobject->querystring .= "       and $subshedcond ";
   $listobject->querystring .= "    group by subshedid ) as foo ";
   if ($debug) { print("$listobject->querystring ; <BR>"); }
   $listobject->performQuery();
   $subshedids = $listobject->getRecordValue(1,'subshedids');

   if (strlen($subshedids) > 0) {
      $subshedcond = " subshedid in ($subshedids) ";
   } else {
      $subshedcond = ' (1 = 1) ';
   }
   $listobject->queryrecords = array();

   $listobject->querystring = "  select luname ";
   $listobject->querystring .= " from scen_crops ";
   $listobject->querystring .= " where $subshedcond ";
   $listobject->querystring .= "    and scenarioid = $scenarioid ";
   $listobject->querystring .= "    and $yrcond ";
   $listobject->querystring .= "    and $cropcond ";
   $listobject->querystring .= " group by luname ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   return $listobject->queryrecords;

}


function showLUCropNeed($listobject, $projectid, $scenarioid, $subsheds, $thisyear, $debug) {

   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
      $bsubshedcond = " b.lrseg in ($sslist) ";
   } else {
      $subshedcond = ' (1 = 1) ';
      $bsubshedcond = ' (1 = 1) ';
   }
   if (strlen($thisyear) > 0) {
      # optimize for speed
      $yrcond = " thisyear = $thisyear ";
      $ayrcond = " a.thisyear = $thisyear ";
      $byrcond = " b.thisyear = $thisyear ";
   } else {
      $yrcond = ' (1 = 1) ';
      $ayrcond = ' (1 = 1) ';
      $byrcond = ' (1 = 1) ';
   }

   if (strlen($polltype) > 0) {
      $pollrcond = " thisyear in ($thisyear) ";
      $apollcond = " a.pollutanttype in ($polltype) ";
      $bpollcond = " b.pollutanttype in ($polltype) ";
   } else {
      $pollrcond = ' (1 = 1) ';
      $apollrcond = ' (1 = 1) ';
      $bpollrcond = ' (1 = 1) ';
   }

   $listobject->querystring = " select trim(trailing ',' from concat_agg(subshedid || ',')) as subshedids ";
   $listobject->querystring .= " from ";
   $listobject->querystring .= " ( ";
   $listobject->querystring .= "    select subshedid from scen_lrsegs ";
   $listobject->querystring .= "    where scenarioid = $scenarioid ";
   $listobject->querystring .= "       and $yrcond ";
   $listobject->querystring .= "       and $subshedcond ";
   $listobject->querystring .= "    group by subshedid ) as foo ";
   if ($debug) { print("$listobject->querystring ; <BR>"); }
   $listobject->performQuery();
   $subshedids = $listobject->getRecordValue(1,'subshedids');

   if (strlen($subshedids) > 0) {
      $subshedcond = " subshedid in ($subshedids) ";
   } else {
      $subshedcond = ' (1 = 1) ';
   }
   $listobject->queryrecords = array();

   $listobject->querystring = "  select  thisyear, subshedid, luname, luarea, ";
   $listobject->querystring .= "    uptake_n, uptake_p, optn, optp, maxn, maxp, ";
   $listobject->querystring .= "    nrate, prate, maxnrate, maxprate ";
   $listobject->querystring .= " from scen_subsheds ";
   $listobject->querystring .= " where $subshedcond ";
   $listobject->querystring .= "    and $yrcond ";
   $listobject->querystring .= " ORDER by thisyear, subshedid, luname ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   return $listobject->queryrecords;

}


function getTransportNeeded($listobject, $subsheds, $scenarioid, $thisyear, $debug) {

# looks for spreadid 12, which is the disposal ID onto landuses of last resort
# some time later this may change, but for now it should do.

   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subshedcond = " lrseg in ($sslist) ";
      $bsubshedcond = " b.lrseg in ($sslist) ";
   } else {
      $subshedcond = ' (1 = 1) ';
      $bsubshedcond = ' (1 = 1) ';
   }
   if (strlen($thisyear) > 0) {
      # optimize for speed
      $yrcond = " thisyear = $thisyear ";
      $ayrcond = " a.thisyear = $thisyear ";
      $byrcond = " b.thisyear = $thisyear ";
   } else {
      $yrcond = ' (1 = 1) ';
      $ayrcond = ' (1 = 1) ';
      $byrcond = ' (1 = 1) ';
   }

   if (strlen($polltype) > 0) {
      $pollcond = " pollutanttype in ($polltype) ";
      $apollcond = " a.pollutanttype in ($polltype) ";
      $bpollcond = " b.pollutanttype in ($polltype) ";
   } else {
      $pollrcond = ' (1 = 1) ';
      $apollrcond = ' (1 = 1) ';
      $bpollrcond = ' (1 = 1) ';
   }


   $listobject->querystring = "  select a.spreadid, a.pollutanttype, ";
   $listobject->querystring .= "    sum(b.luarea * a.annualapplied) as total_lbs, ";
   $listobject->querystring .= "    sum(b.luarea * a.annualapplied/2000.0) as total_tons ";
   $listobject->querystring .= " from scen_sourceperunitarea as a, scen_lrsegs as b ";
   $listobject->querystring .= " where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and a.pollutanttype in (1,2,12) ";
   $listobject->querystring .= "    and a.spreadid = 12 ";
   $listobject->querystring .= "    and $bsubshedcond ";
   $listobject->querystring .= "    and b.subshedid = a.subshedid ";
   $listobject->querystring .= "    and b.luname = a.luname ";
   $listobject->querystring .= "    and $ayrcond ";
   $listobject->querystring .= "    and $byrcond ";
   $listobject->querystring .= " group by a.spreadid, a.pollutanttype ";
   $listobject->querystring .= " order by a.spreadid ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   return $listobject->queryrecords;

}


function getLRLanduse($listobject, $landuses, $subsheds, $scenarioid, $thisyear, $debug) {

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
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

   $listobject->queryrecords = array();

   $listobject->querystring = "select oid, thisyear, landseg, riverseg, lrseg, luname, luarea ";
   $listobject->querystring .= " from scen_lrsegs  ";
   $listobject->querystring .= " where scenarioid = $scenarioid ";
   $listobject->querystring .= "    and $lucond ";
   $listobject->querystring .= "    and $subshedcond ";
   $listobject->querystring .= "    and $yrcond ";
   $listobject->querystring .= " order by lrseg, luname, thisyear ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();

   return $listobject->queryrecords;

}

function getGroupLUArea($listobject, $landuses, $subsheds, $scenarioid, $thisyear, $debug) {

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   # $maketemp - passing a name for a table in here directs us to create a table
   if (count($landuses) > 0) {
      $lulist = "'" . join("','", $landuses) . "'";
      $alucond = " a.luname in ($lulist) ";
   } else {
      $alucond = ' 1 = 1 ';
   }
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $asubshedcond = " a.lrseg in ($sslist) ";
   } else {
      $asubshedcond = ' 1 = 1 ';
   }
   if (strlen($thisyear) > 0) {
      $ayrcond = " a.thisyear in ($thisyear) ";
   } else {
      $ayrcond = ' 1 = 1 ';
   }

   $listobject->queryrecords = array();


   $listobject->querystring = "select a.projectid, a.thisyear, a.luname, b.major_lutype, sum(a.luarea) as luarea ";
   $listobject->querystring .= " from scen_lrsegs as a, landuses as b ";
   $listobject->querystring .= " where scenarioid = $scenarioid ";
   $listobject->querystring .= "    and $alucond ";
   $listobject->querystring .= "    and $asubshedcond ";
   $listobject->querystring .= "    and $ayrcond ";
   $listobject->querystring .= "    and a.luname = b.hspflu ";
   $listobject->querystring .= "    and b.projectid = a.projectid ";
   $listobject->querystring .= " group by a.projectid, a.thisyear, a.luname, b.major_lutype ";
   $listobject->querystring .= " order by a.thisyear, a.luname ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();

   return $listobject->queryrecords;

}

function getGroupArea($listobject, $landuses, $subsheds, $scenarioid, $thisyear, $debug) {

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   # $maketemp - passing a name for a table in here directs us to create a table
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

   $listobject->queryrecords = array();


   $listobject->querystring = "select thisyear, sum(luarea) as totalarea ";
   $listobject->querystring .= " from scen_lrsegs ";
   $listobject->querystring .= " where scenarioid = $scenarioid ";
   $listobject->querystring .= "    and $lucond ";
   $listobject->querystring .= "    and $subshedcond ";
   $listobject->querystring .= "    and $yrcond ";
   $listobject->querystring .= " group by thisyear ";
   $listobject->querystring .= " order by thisyear ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();

   return $listobject->queryrecords;

}

function getGroupCropCurves($listobject, $projectid, $scenarioid, $subsheds, $cropname, $debug) {

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

   # retrieve crop records with submitted data
   $listobject->querystring = "  select a.cropname, a.curvetype, a.source_type, ";
   $listobject->querystring .= "    min(a.model_plant) as model_plant, ";
   $listobject->querystring .= "    min(a.plantdate) as plantdate, ";
   $listobject->querystring .= "    min(a.harvestdate) as harvestdate, avg(a.need_pct) as need_pct,  ";
   $listobject->querystring .= "    avg(a.jan) as jan, avg(a.feb) as feb, avg(a.mar) as mar,  ";
   $listobject->querystring .= "    avg(a.apr) as apr, avg(a.may) as may, avg(a.jun) as jun,  ";
   $listobject->querystring .= "    avg(a.jul) as jul, avg(a.aug) as aug, avg(a.sep) as sep,  ";
   $listobject->querystring .= "    avg(a.oct) as oct, avg(a.nov) as nov, avg(a.dec) as dec  ";
   $listobject->querystring .= " from scen_crop_curves as a ";
   $listobject->querystring .= " where a.scenarioid = $scenarioid  ";
   $listobject->querystring .= "    and $asubshedcond ";
   $listobject->querystring .= "    and a.cropname = '$cropname' ";
   $listobject->querystring .= " group by a.cropname, a.curvetype, a.source_type ";
   #$listobject->querystring .= "    a.model_plant, a.plantdate, a.harvestdate ";
   $listobject->querystring .= " order by a.curvetype, a.source_type ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();
   return $listobject->queryrecords;

}


function getGroupCrops($listobject, $projectid, $scenarioid, $subsheds, $thisyear, $landuses, $debug) {

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

   # retrieve crop records with submitted data
   $listobject->querystring = " select a.cropname, sum(a.croparea) as croparea, ";
   $listobject->querystring .= "    CASE ";
   $listobject->querystring .= "       WHEN sum(a.croparea) = 0 THEN avg(b.high_yld) ";
   $listobject->querystring .= "       ELSE sum(a.croparea * b.high_yld) / sum(a.croparea) ";
   $listobject->querystring .= "    END as high_yld, ";
   $listobject->querystring .= "    CASE ";
   $listobject->querystring .= "       WHEN sum(a.croparea) = 0 THEN avg(b.mean_yld) ";
   $listobject->querystring .= "       ELSE sum(a.croparea * b.mean_yld) / sum(a.croparea) ";
   $listobject->querystring .= "    END as mean_yld, ";
   $listobject->querystring .= "    c.yld_units, avg(c.n_removal) as nper, avg(c.p_removal) as pper ";
   $listobject->querystring .= " from scen_crops as a, proj_cropyield as b, proj_crop_type as c ";
   $listobject->querystring .= " where a.scenarioid = $scenarioid  ";
   $listobject->querystring .= "    and $asubshedcond ";
   $listobject->querystring .= "    and $bsubshedcond ";
   $listobject->querystring .= "    and b.subshedid = a.subshedid ";
   $listobject->querystring .= "    and b.projectid = $projectid ";
   $listobject->querystring .= "    and b.cropname = a.cropname ";
   $listobject->querystring .= "    and c.projectid = $projectid ";
   $listobject->querystring .= "    and c.cropname = a.cropname ";
   $listobject->querystring .= "    and $yrcond ";
   $listobject->querystring .= "    and $lucond ";
   $listobject->querystring .= " group by a.cropname, c.yld_units ";
   $listobject->querystring .= " order by a.cropname ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();
   return $listobject->queryrecords;

}


function getGroupAppRates($listobject, $landuses, $subsheds, $scenarioid, $thisyear, $debug) {

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   # $maketemp - passing a name for a table in here directs us to create a table
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
      $asubshedcond = " a.lrseg in ($sslist) ";
   } else {
      $subshedcond = ' 1 = 1 ';
      $asubshedcond = ' 1 = 1 ';
      $bsubshedcond = ' 1 = 1 ';
   }
   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear in ($thisyear) ";
      $ayrcond = " a.thisyear in ($thisyear) ";
      $byrcond = " b.thisyear in ($thisyear) ";
   } else {
      $yrcond = ' 1 = 1 ';
      $ayrcond = ' 1 = 1 ';
      $byrcond = ' 1 = 1 ';
   }

   $listobject->querystring = "select a.thisyear, sum(a.luarea * b.nrate)/sum(a.luarea) as nrate, ";
   $listobject->querystring .= "    min(b.nrate) as min_nrate, max(b.nrate) as max_nrate, ";
   $listobject->querystring .= "    sum(a.luarea * b.prate)/sum(a.luarea) as prate, ";
   $listobject->querystring .= "    min(b.prate) as min_prate, max(b.prate) as max_prate, ";
   $listobject->querystring .= "    sum(a.luarea * b.maxnrate)/sum(a.luarea) as maxnrate, ";
   $listobject->querystring .= "    min(b.maxnrate) as min_maxnrate, max(b.maxnrate) as max_maxnrate, ";
   $listobject->querystring .= "    sum(a.luarea * b.maxprate)/sum(a.luarea) as maxprate, ";
   $listobject->querystring .= "    min(b.maxprate) as min_maxprate, max(b.maxprate) as max_maxprate, ";
   $listobject->querystring .= "    sum(a.luarea * b.mean_needn)/sum(a.luarea) as mean_needn, ";
   $listobject->querystring .= "    sum(a.luarea * b.mean_needp)/sum(a.luarea) as mean_needp, ";
   $listobject->querystring .= "    sum(a.luarea * b.mean_uptn)/sum(a.luarea) as mean_uptn, ";
   $listobject->querystring .= "    sum(a.luarea * b.mean_uptp)/sum(a.luarea) as mean_uptp, ";
   $listobject->querystring .= "    sum(a.luarea * b.targ_needn)/sum(a.luarea) as targ_needn, ";
   $listobject->querystring .= "    sum(a.luarea * b.targ_needp)/sum(a.luarea) as targ_needp, ";
   $listobject->querystring .= "    sum(a.luarea * b.targ_uptn)/sum(a.luarea) as targ_uptn, ";
   $listobject->querystring .= "    sum(a.luarea * b.targ_uptp)/sum(a.luarea) as targ_uptp, ";
   $listobject->querystring .= "    sum(a.luarea * b.high_needn)/sum(a.luarea) as high_needn, ";
   $listobject->querystring .= "    sum(a.luarea * b.high_needp)/sum(a.luarea) as high_needp, ";
   $listobject->querystring .= "    sum(a.luarea * b.high_uptn)/sum(a.luarea) as high_uptn, ";
   $listobject->querystring .= "    sum(a.luarea * b.high_uptp)/sum(a.luarea) as high_uptp, ";
   $listobject->querystring .= "    sum(a.luarea * b.uptake_n)/sum(a.luarea) as uptake_n, ";
   $listobject->querystring .= "    sum(a.luarea * b.uptake_p)/sum(a.luarea) as uptake_p, ";
   $listobject->querystring .= "    sum(a.luarea * b.dc_pct)/sum(a.luarea) as dc_pct, ";
   $listobject->querystring .= "    min(b.optyieldtarget) as optyieldtarget, ";
   $listobject->querystring .= "    min(b.optyieldtarget) as max_optyieldtarget, ";
   $listobject->querystring .= "    max(b.optyieldtarget) as max_optyieldtarget, ";
   $listobject->querystring .= "    min(b.maxyieldtarget) as maxyieldtarget, ";
   $listobject->querystring .= "    min(b.maxyieldtarget) as max_maxyieldtarget, ";
   $listobject->querystring .= "    max(b.maxyieldtarget) as max_maxyieldtarget, ";
   $listobject->querystring .= "    min(b.nm_planbase) as limconstit, ";
   $listobject->querystring .= "    min(b.dc_method) as dc_method, ";
   $listobject->querystring .= "    min(b.nm_planbase) as min_limconstit, max(b.nm_planbase) as max_limconstit ";
   $listobject->querystring .= " from scen_lrsegs as a, inputyields as b ";
   $listobject->querystring .= " where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and $alucond ";
   $listobject->querystring .= "    and $blucond ";
   $listobject->querystring .= "    and a.luname = b.luname ";
   $listobject->querystring .= "    and $asubshedcond ";
   $listobject->querystring .= "    and a.subshedid = b.subshedid ";
   $listobject->querystring .= "    and $ayrcond ";
   $listobject->querystring .= "    and $byrcond ";
   $listobject->querystring .= "    and a.thisyear = b.thisyear ";
   $listobject->querystring .= " group by a.thisyear ";
   $listobject->querystring .= " order by a.thisyear ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();

   if (count($listobject->queryrecords) == 0) {
      $listobject->querystring = "select a.thisyear, avg(b.nrate) as nrate, ";
      $listobject->querystring .= "    min(b.nrate) as min_nrate, max(b.nrate) as max_nrate, ";
      $listobject->querystring .= "    avg(b.prate) as prate, ";
      $listobject->querystring .= "    min(b.prate) as min_prate, max(b.prate) as max_prate, ";
      $listobject->querystring .= "    avg(b.maxnrate) as maxnrate, ";
      $listobject->querystring .= "    min(b.maxnrate) as min_maxnrate, max(b.maxnrate) as max_maxnrate, ";
      $listobject->querystring .= "    avg(b.maxprate) as maxprate, ";
      $listobject->querystring .= "    min(b.maxprate) as min_maxprate, max(b.maxprate) as max_maxprate, ";
      $listobject->querystring .= "    avg(b.mean_needn) as mean_needn, ";
      $listobject->querystring .= "    avg(b.mean_needp) as mean_needp, ";
      $listobject->querystring .= "    avg(b.mean_uptn) as mean_uptn, ";
      $listobject->querystring .= "    avg(b.mean_uptp) as mean_uptp, ";
      $listobject->querystring .= "    avg(b.targ_needn) as targ_needn, ";
      $listobject->querystring .= "    avg(b.targ_needp) as targ_needp, ";
      $listobject->querystring .= "    avg(b.targ_uptn) as targ_uptn, ";
      $listobject->querystring .= "    avg(b.targ_uptp) as targ_uptp, ";
      $listobject->querystring .= "    avg(b.high_needn) as high_needn, ";
      $listobject->querystring .= "    avg(b.high_needp) as high_needp, ";
      $listobject->querystring .= "    avg(b.high_uptn) as high_uptn, ";
      $listobject->querystring .= "    avg(b.high_uptp) as high_uptp, ";
      $listobject->querystring .= "    avg(b.uptake_n) as uptake_n, ";
      $listobject->querystring .= "    avg(b.uptake_p) as uptake_p, ";
      $listobject->querystring .= "    avg(b.dc_pct) as dc_pct, ";
      $listobject->querystring .= "    min(b.optyieldtarget) as optyieldtarget, ";
      $listobject->querystring .= "    min(b.optyieldtarget) as max_optyieldtarget, ";
      $listobject->querystring .= "    max(b.optyieldtarget) as max_optyieldtarget, ";
      $listobject->querystring .= "    min(b.maxyieldtarget) as maxyieldtarget, ";
      $listobject->querystring .= "    min(b.maxyieldtarget) as max_maxyieldtarget, ";
      $listobject->querystring .= "    max(b.maxyieldtarget) as max_maxyieldtarget, ";
      $listobject->querystring .= "    min(b.nm_planbase) as limconstit, ";
      $listobject->querystring .= "    min(b.dc_method) as dc_method, ";
      $listobject->querystring .= "    min(b.nm_planbase) as min_limconstit, max(b.nm_planbase) as max_limconstit ";
      $listobject->querystring .= " from scen_lrsegs as a, inputyields as b ";
      $listobject->querystring .= " where a.scenarioid = $scenarioid ";
      $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
      $listobject->querystring .= "    and $alucond ";
      $listobject->querystring .= "    and $blucond ";
      $listobject->querystring .= "    and a.luname = b.luname ";
      $listobject->querystring .= "    and $asubshedcond ";
      $listobject->querystring .= "    and a.subshedid = b.subshedid ";
      $listobject->querystring .= "    and $ayrcond ";
      $listobject->querystring .= "    and $byrcond ";
      $listobject->querystring .= "    and a.thisyear = b.thisyear ";
      $listobject->querystring .= " group by a.thisyear ";
      $listobject->querystring .= " order by a.thisyear ";
      if ($debug) { print("<br>$listobject->querystring ; <br>"); }
      $listobject->performQuery();
   }

   if (count($listobject->queryrecords) > 0) {
      $grouprates = $listobject->queryrecords[0];

      if ($grouprates['min_prate'] <> $grouprates['max_prate']) {
         $min_prate = $grouprates['min_prate'];
         $max_prate = $grouprates['max_prate'];
         $grouprates['prate_mesg'] = "P rates vary from $min_prate to $max_prate";
      }
      if ($grouprates['min_nrate'] <> $grouprates['max_nrate']) {
         $min_nrate = $grouprates['min_nrate'];
         $max_nrate = $grouprates['max_nrate'];
         $grouprates['nrate_mesg'] = "N rates vary from $min_nrate to $max_nrate";
      }
      if ($grouprates['min_limconstit'] <> $grouprates['max_limconstit']) {
         $min = $grouprates['min_limconstit'];
         $max = $grouprates['max_limconstit'];
         $grouprates['limconstit_mesg'] = "Notice: There are multiple limiting constituents. Updating will set all members of this group to the same limiting constituent.";
      }
      if ($grouprates['min_maxnrate'] <> $grouprates['max_maxnrate']) {
         $min = $grouprates['min_maxnrate'];
         $max = $grouprates['max_maxnrate'];
         $grouprates['maxnrate_mesg'] = "Notice: Max N rates vary from $min to $max. Updating will set all members of this group to the same max N rate.";
      }
      if ($grouprates['min_maxprate'] <> $grouprates['max_maxprate']) {
         $min = $grouprates['min_maxprate'];
         $max = $grouprates['max_maxprate'];
         $grouprates['maxprate_mesg'] = "Max P rates vary from $min to $max. Updating will set all members of this group to the same max P rate.";
      }
      if ($grouprates['min_optyieldtarget'] <> $grouprates['max_optyieldtarget']) {
         $min = $grouprates['min_optyieldtarget'];
         $max = $grouprates['max_optyieldtarget'];
         $grouprates['optyieldtarget_mesg'] = "Optimum Yield Targets vary from $min to $max. Updating will set all members of this group to the same optimum yield target.";
      }
      if ($grouprates['min_maxyieldtarget'] <> $grouprates['max_maxyieldtarget']) {
         $min = $grouprates['min_maxyieldtarget'];
         $max = $grouprates['max_maxyieldtarget'];
         $grouprates['maxyieldtarget_mesg'] = "Maximum Yield Targets vary from $min to $max. Updating will set all members of this group to the same maximum yield target.";
      }
   } else {
      $grouprates = array();
   }

   return $grouprates;

}


function tempBMPTables($listobject, $bmptype, $subsheds, $scenarioid, $thisyear, $debug) {

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $bmpsubcond = " lrseg in ( $sslist) ";
      $lusubcond = " c.lrseg in ($sslist) ";
   } else {
      $bmpsubcond = ' (1 = 1) ';
   }
   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear in ($thisyear) ";
      $ayrcond = " a.thisyear in ($thisyear) ";
   } else {
      $yrcond = ' (1 = 1) ';
      $ayrcond = ' (1 = 1) ';
   }

   ## create temp bmp tables for use in subsequent BMP summary queries
   $listobject->querystring = "select * ";
   $listobject->querystring .= " into temp tmp_lrseg_bmps ";
   $listobject->querystring .= " from scen_lrseg_bmps ";
   $listobject->querystring .= " where $bmpsubcond ";
   $listobject->querystring .= "    and $yrcond ";
   $listobject->querystring .= "    and scenarioid = $scenarioid ";
   $listobject->querystring .= " order by thisyear, lrseg ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $split = $listobject->startsplit();
   $listobject->performquery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }

   ## create temp bmp tables for use in subsequent BMP summary queries
   $listobject->querystring = "select * ";
   $listobject->querystring .= " into temp tmp_lrsegs ";
   $listobject->querystring .= " from scen_lrsegs ";
   $listobject->querystring .= " where $bmpsubcond ";
   $listobject->querystring .= "    and $yrcond ";
   $listobject->querystring .= "    and scenarioid = $scenarioid ";
   $listobject->querystring .= " order by thisyear, lrseg ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $split = $listobject->startsplit();
   $listobject->performquery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }

}

function clearTempBMPTables($listobject) {
   if ($listobject->tableExists('tmp_lrsegs')) {
      $listobject->querystring = "drop table tmp_lrsegs ";
      $listobject->performquery();
      $listobject->querystring = "drop table tmp_lrseg_bmps ";
      $listobject->performquery();
   }
}

function getOneYearLandUseLRMasslinks($listobject, $luname, $subsheds, $thisyear, $scenarioid, $projectid, $debug) {

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $alrsegcond = " a.lrseg in ( $sslist) ";
      $blrsegcond = " b.lrseg in ($sslist) ";
   } else {
      $alrsegcond = ' ( 1 = 1 )';
      $blrsegcond = ' ( 1 = 1 )';
   }
   if (strlen($thisyear) > 0) {
      # assuems a single year as input, this speeds up processing
      $ayrcond = " a.thisyear = $thisyear ";
      $byrcond = " b.thisyear = $thisyear ";
   } else {
      $errtext .= "<b>Error: </b>You must enter one land use.<br>";
      $error = 1;
   }

   if (strlen($luname) > 0) {
      $alucond = " a.luname = '$luname' ";
      $blucond = " b.luname = '$luname' ";
   } else {
      $errtext .= "<b>Error: </b>You must enter one land use.<br>";
      $error = 1;
   }

   $listobject->queryrecords = array();

   $split = $listobject->startsplit();
   $listobject->querystring = "  select a.luname, sum(a.luarea) as luarea, ";
   $listobject->querystring .= "    b.constit, sum(b.passthru * a.luarea)/sum(a.luarea) as passthru ";
   $listobject->querystring .= " FROM scen_lrsegs as a, scen_masslinks as b ";
   $listobject->querystring .= " WHERE $alrsegcond ";
   $listobject->querystring .= "    AND $blrsegcond ";
   $listobject->querystring .= "    AND $ayrcond ";
   $listobject->querystring .= "    AND $byrcond ";
   $listobject->querystring .= "    AND $alucond ";
   $listobject->querystring .= "    AND $blucond ";
   $listobject->querystring .= "    AND a.lrseg = b.lrseg ";
   $listobject->querystring .= "    AND a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    AND b.scenarioid = $scenarioid ";
   $listobject->querystring .= " group by a.luname, b.constit ";
   $listobject->querystring .= " order by b.constit, passthru ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }

   return $listobject->queryrecords;

}

function getOneYearLandUseLRBmps($listobject, $luname, $subsheds, $thisyear, $scenarioid, $projectid, $debug) {

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $alrsegcond = " a.lrseg in ( $sslist) ";
      $blrsegcond = " b.lrseg in ($sslist) ";
   } else {
      $alrsegcond = ' ( 1 = 1 )';
      $blrsegcond = ' ( 1 = 1 )';
   }
   if (strlen($thisyear) > 0) {
      # assuems a single year as input, this speeds up processing
      $ayrcond = " a.thisyear = $thisyear ";
      $byrcond = " b.thisyear = $thisyear ";
   } else {
      $errtext .= "<b>Error: </b>You must enter one land use.<br>";
      $error = 1;
   }

   if (strlen($luname) > 0) {
      $alucond = " a.luname = '$luname' ";
      $blucond = " b.luname = '$luname' ";
   } else {
      $errtext .= "<b>Error: </b>You must enter one land use.<br>";
      $error = 1;
   }

   $listobject->queryrecords = array();

   $split = $listobject->startsplit();
   $listobject->querystring = "  select c.bmp_desc, d.efftype, a.luname, sum(a.luarea) as luarea, b.typeid, ";
   $listobject->querystring .= "    b.constit, sum(b.area_submitted) as area_submitted, ";
   $listobject->querystring .= "    sum(b.area_implemented) as area_implemented, ";
   $listobject->querystring .= "    sum(b.imp_eff * a.luarea)/sum(a.luarea) as imp_eff, ";
   $listobject->querystring .= "    sum(b.base_effic * a.luarea)/sum(a.luarea) as base_effic ";
   $listobject->querystring .= " FROM scen_lrsegs as a, scen_bmp_area_effic as b, ";
   $listobject->querystring .= "    bmp_types as c, bmp_efftype as d ";
   $listobject->querystring .= " WHERE $alrsegcond ";
   $listobject->querystring .= "    AND $blrsegcond ";
   $listobject->querystring .= "    AND $ayrcond ";
   $listobject->querystring .= "    AND $byrcond ";
   $listobject->querystring .= "    AND $alucond ";
   $listobject->querystring .= "    AND $blucond ";
   $listobject->querystring .= "    AND a.lrseg = b.lrseg ";
   $listobject->querystring .= "    AND b.typeid = c.typeid ";
   $listobject->querystring .= "    AND b.efftype = d.effid ";
   $listobject->querystring .= "    AND a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    AND b.scenarioid = $scenarioid ";
   $listobject->querystring .= " group by c.bmp_desc, d.efftype, a.luname, b.typeid, b.constit ";
   $listobject->querystring .= " order by b.constit, c.bmp_desc ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }

   return $listobject->queryrecords;

}


function getMasslinks($listobject, $scenarioid, $segments, $landuses, $years, $constits, $debug) {

   # assemble input variables into conditions
   if (count($segments) > 0) {
      $sslist = "'" . join("','", $segments) . "'";
      $subcond = " lrseg in ($sslist) ";
      $asubcond = " a.lrseg in ($sslist) ";
   } else {
      $subcond = ' (1 = 1) ';
      $asubcond = ' (1 = 1) ';
   }

   if (count($landuses) > 0) {
      $lulist = "'" . join("','", $landuses) . "'";
      $lucond = " luname in ($lulist) ";
      $alucond = " a.luname in ($lulist) ";
   } else {
      $lucond = ' (1 = 1) ';
      $alucond = ' (1 = 1) ';
   }

   if (count($years) > 0) {
      $yrlist = "'" . join(",", $years) . "'";
      $yrcond = " thisyear in ($yrlist) ";
      $ayrcond = " a.thisyear in ($yrlist) ";
   } else {
      $yrcond = ' (1 = 1) ';
      $ayrcond = ' (1 = 1) ';
   }

   if (count($constits) > 0) {
      $conlist = join(",", $constits);
      $concond = " constit in ($conlist) ";
      $aconcond = " a.constit in ($conlist) ";
   } else {
      $concond = ' (1 = 1) ';
      $aconcond = ' (1 = 1) ';
   }

   $listobject->querystring = "  select a.lrseg, a.thisyear, a.luname, b.shortname as constit, a.passthru ";
   $listobject->querystring .= " from scen_masslinks as a, pollutanttype as b ";
   $listobject->querystring .= " WHERE scenarioid = $scenarioid ";
   $listobject->querystring .= "    AND $alucond ";
   $listobject->querystring .= "    AND $asubcond ";
   $listobject->querystring .= "    AND $ayrcond ";
   $listobject->querystring .= "    AND $aconcond ";
   $listobject->querystring .= "    AND a.constit = b.typeid ";
   $listobject->querystring .= " ORDER BY a.thisyear, b.shortname, a.lrseg, a.luname ";

   if ($debug) {
      print("$listobject->querystring ; <br>");
   }
   $listobject->performQuery();

   return $listobject->queryrecords;
}


function getBMPAreaEffic($listobject, $scenarioid, $segments, $landuses, $years, $constits, $debug) {

   # assemble input variables into conditions
   if (count($segments) > 0) {
      $sslist = "'" . join("','", $segments) . "'";
      $subcond = " lrseg in ($sslist) ";
      $asubcond = " a.lrseg in ($sslist) ";
   } else {
      $subcond = ' (1 = 1) ';
      $asubcond = ' (1 = 1) ';
   }

   if (count($landuses) > 0) {
      $lulist = "'" . join("','", $landuses) . "'";
      $lucond = " luname in ($lulist) ";
      $alucond = " a.luname in ($lulist) ";
   } else {
      $lucond = ' (1 = 1) ';
      $alucond = ' (1 = 1) ';
   }

   if (count($years) > 0) {
      $yrlist = join(",", $years);
      $yrcond = " thisyear in ($yrlist) ";
      $ayrcond = " a.thisyear in ($yrlist) ";
   } else {
      $yrcond = ' (1 = 1) ';
      $ayrcond = ' (1 = 1) ';
   }

   if (count($constits) > 0) {
      $conlist = join(",", $constits) ;
      $concond = " constit in ($conlist) ";
      $aconcond = " a.constit in ($conlist) ";
   } else {
      $concond = ' (1 = 1) ';
      $aconcond = ' (1 = 1) ';
   }

   $listobject->querystring = "  select a.lrseg, a.thisyear, a.luname, c.bmp_desc, b.shortname as constit, ";
   $listobject->querystring .= "   a.area_submitted, a.area_implemented, a.imp_eff, a.base_effic ";
   $listobject->querystring .= " from scen_bmp_area_effic as a, pollutanttype as b, bmp_types as c ";
   $listobject->querystring .= " WHERE scenarioid = $scenarioid ";
   $listobject->querystring .= "    AND $alucond ";
   $listobject->querystring .= "    AND $asubcond ";
   $listobject->querystring .= "    AND $ayrcond ";
   $listobject->querystring .= "    AND $aconcond ";
   $listobject->querystring .= "    AND a.constit = b.typeid ";
   $listobject->querystring .= "    AND a.typeid = c.typeid ";
   $listobject->querystring .= " ORDER BY a.thisyear, b.shortname, a.lrseg, a.luname ";

   if ($debug) {
      print("$listobject->querystring ; <br>");
   }
   $listobject->performQuery();

   return $listobject->queryrecords;
}

function getLUChangeBMPs($listobject, $projectid, $scenarioid, $segments, $landuses, $years, $constits, $debug) {

   # assemble input variables into conditions
   if (count($segments) > 0) {
      $sslist = "'" . join("','", $segments) . "'";
      $subcond = " lrseg in ($sslist) ";
      $asubcond = " a.lrseg in ($sslist) ";
      $bsubcond = " b.lrseg in ($sslist) ";
   } else {
      $subcond = ' (1 = 1) ';
      $asubcond = ' (1 = 1) ';
      $bsubcond = ' (1 = 1) ';
   }

   if (count($landuses) > 0) {
      $lulist = "'" . join("','", $landuses) . "'";
      $lucond = " luname in ($lulist) ";
      $alucond = " a.luname in ($lulist) ";
      $blucond = " b.srclu in ($lulist) ";
   } else {
      $lucond = ' (1 = 1) ';
      $alucond = ' (1 = 1) ';
      $blucond = ' (1 = 1) ';
   }

   if (count($years) > 0) {
      $yrlist = join(",", $years);
      $yrcond = " thisyear in ($yrlist) ";
      $ayrcond = " a.thisyear in ($yrlist) ";
      $byrcond = " b.thisyear in ($yrlist) ";
   } else {
      $yrcond = ' (1 = 1) ';
      $ayrcond = ' (1 = 1) ';
      $byrcond = ' (1 = 1) ';
   }

   $listobject->querystring = "  select b.lrseg, b.thisyear, b.srclu, b.destlu, c.bmpname, c.bmptext, ";
   $listobject->querystring .= "   b.submit_area as value_submitted, b.chgarea as value_implemented ";
   $listobject->querystring .= " from bmp_subtypes as c, scen_bmp_luchghist as b ";
   $listobject->querystring .= "     ";
   $listobject->querystring .= " WHERE b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    AND $byrcond ";
   $listobject->querystring .= "    AND $blucond ";
   $listobject->querystring .= "    AND $bsubcond ";
   $listobject->querystring .= "    AND c.projectid = $projectid ";
   $listobject->querystring .= "    AND b.bmpname = c.bmpname ";
   $listobject->querystring .= " ORDER BY b.thisyear, b.lrseg, c.bmptext, b.srclu ";

   if ($debug) {
      print("$listobject->querystring ; <br>");
   }
   $listobject->performQuery();

   return $listobject->queryrecords;
}

function getIndividualAppliedBMPs($listobject, $projectid, $scenarioid, $segments, $landuses, $years, $constits, $debug) {

   # assemble input variables into conditions
   if (count($segments) > 0) {
      $sslist = "'" . join("','", $segments) . "'";
      $subcond = " lrseg in ($sslist) ";
      $asubcond = " a.lrseg in ($sslist) ";
   } else {
      $subcond = ' (1 = 1) ';
      $asubcond = ' (1 = 1) ';
   }

   if (count($landuses) > 0) {
      $lulist = "'" . join("','", $landuses) . "'";
      $lucond = " luname in ($lulist) ";
      $alucond = " a.luname in ($lulist) ";
   } else {
      $lucond = ' (1 = 1) ';
      $alucond = ' (1 = 1) ';
   }

   if (count($years) > 0) {
      $yrlist = join(",", $years);
      $yrcond = " thisyear in ($yrlist) ";
      $ayrcond = " a.thisyear in ($yrlist) ";
   } else {
      $yrcond = ' (1 = 1) ';
      $ayrcond = ' (1 = 1) ';
   }

   if (count($constits) > 0) {
      $conlist = join(",", $constits) ;
      $concond = " constit in ($conlist) ";
      $aconcond = " a.constit in ($conlist) ";
   } else {
      $concond = ' (1 = 1) ';
      $aconcond = ' (1 = 1) ';
   }

   $listobject->querystring = "  select a.lrseg, a.thisyear, a.luname, c.bmpname, c.bmptext, ";
   $listobject->querystring .= "   a.value_submitted, a.value_implemented ";
   $listobject->querystring .= " from scen_bmp_data as a, bmp_subtypes as c ";
   $listobject->querystring .= "     ";
   $listobject->querystring .= " WHERE a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    AND $ayrcond ";
   $listobject->querystring .= "    AND $alucond ";
   $listobject->querystring .= "    AND $asubcond ";
   $listobject->querystring .= "    AND c.projectid = $projectid ";
   $listobject->querystring .= "    AND a.bmpname = c.bmpname ";
   $listobject->querystring .= " ORDER BY a.thisyear, a.lrseg, c.bmptext, a.luname ";

   if ($debug) {
      print("$listobject->querystring ; <br>");
   }
   $listobject->performQuery();

   return $listobject->queryrecords;
}

function getLRBmps($listobject, $bmptype, $subsheds, $thisyear, $scenarioid, $projectid, $debug) {

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $bmpsubcond = " a.lrseg in ( $sslist) ";
      $lusubcond = " c.lrseg in ($sslist) ";
   } else {
      $bmpsubcond = ' (1 = 1) ';
      $lusubcond = ' (1 = 1) ';
   }
   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear in ($thisyear) ";
      $ayrcond = " a.thisyear in ($thisyear) ";
      $cyrcond = " c.thisyear in ($thisyear) ";
   } else {
      $yrcond = ' (1 = 1) ';
      $ayrcond = ' (1 = 1) ';
      $cyrcond = ' (1 = 1) ';
   }


   $listobject->queryrecords = array();

   $split = $listobject->startsplit();
   $listobject->querystring = "select $projectid as projectid, b.bmpid, a.scenarioid, b.thisyear,  ";
   $listobject->querystring .= " b.bmpname, b.bmptext, ";
   $listobject->querystring .= " CASE  ";
   $listobject->querystring .= "    WHEN d.bmparea is null then b.eligarea ";
   $listobject->querystring .= "    ELSE b.eligarea + d.bmparea ";
   $listobject->querystring .= " END as eligarea, ";
   $listobject->querystring .= " CASE  ";
   $listobject->querystring .= "    WHEN a.bmparea is null then 0.0 ";
   $listobject->querystring .= "    ELSE a.bmparea ";
   $listobject->querystring .= " END as bmparea, ";
   $listobject->querystring .= " CASE  ";
   $listobject->querystring .= "    WHEN a.bmpsubbed is null then 0.0 ";
   $listobject->querystring .= "    ELSE a.bmpsubbed ";
   $listobject->querystring .= " END as bmpsubbed, ";
   $listobject->querystring .= " 1 as editlink ";
   $listobject->querystring .= " from ( select c.thisyear, b.bmpid, b.bmpname, ";
   $listobject->querystring .= "      b.bmptext, sum(c.luarea) as eligarea  ";
   $listobject->querystring .= "   from bmp_subtypes as b, scen_lrsegs as c, map_landuse_bmp as d ";
   $listobject->querystring .= "   where b.projectid = $projectid ";
   $listobject->querystring .= "      and d.projectid = $projectid ";
   $listobject->querystring .= "      and c.scenarioid = $scenarioid ";
   $listobject->querystring .= "      and $lusubcond ";
   $listobject->querystring .= "      and $yrcond ";
   $listobject->querystring .= "      and b.typeid = $bmptype ";
   $listobject->querystring .= "      and c.luname = d.luname ";
   $listobject->querystring .= "      and b.bmpname = d.bmpname ";
   $listobject->querystring .= "   group by b.bmpname, b.bmpid, b.bmptext, c.thisyear ";
   $listobject->querystring .= " ) as b left outer join ";
   $listobject->querystring .= " ( select a.scenarioid, a.bmpname, a.thisyear, sum(b.conversion * a.bmparea) as bmparea, ";
   $listobject->querystring .= "      sum(a.bmparea) as bmpsubbed ";
   $listobject->querystring .= "   from scen_lrseg_bmps as a, bmp_subtypes as b ";
   $listobject->querystring .= "   where $yrcond ";
   $listobject->querystring .= "      and a.bmpname = b.bmpname ";
   $listobject->querystring .= "      and b.typeid = $bmptype ";
   $listobject->querystring .= "      and $bmpsubcond ";
   $listobject->querystring .= "      and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "   group by a.scenarioid, a.bmpname, a.thisyear ";
   $listobject->querystring .= "  ) as a ";
   $listobject->querystring .= " on ( a.bmpname = b.bmpname ";
   $listobject->querystring .= "        and a.thisyear = b.thisyear ";
   $listobject->querystring .= "    ) ";
   $listobject->querystring .= " left outer join ";
   $listobject->querystring .= "    ( select c.thisyear, c.bmpname, sum(c.chgarea) as bmparea ";
   $listobject->querystring .= "      from scen_bmp_luchghist as c, bmp_subtypes as b ";
   $listobject->querystring .= "        where b.typeid = $bmptype ";
   $listobject->querystring .= "           and b.projectid = $projectid ";
   $listobject->querystring .= "           and c.scenarioid = $scenarioid ";
   $listobject->querystring .= "           and $lusubcond ";
   $listobject->querystring .= "           and $cyrcond ";
   $listobject->querystring .= "           and c.bmpname = b.bmpname ";
   $listobject->querystring .= "      group by c.thisyear, c.bmpname ";
   $listobject->querystring .= " ) as d ";
   $listobject->querystring .= " on ( a.bmpname = d.bmpname ";
   $listobject->querystring .= "         and a.thisyear = d.thisyear ";
   $listobject->querystring .= "    ) ";
   $listobject->querystring .= " order by b.bmptext ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }

   return $listobject->queryrecords;

}


function getLRBmps2($listobject, $bmptype, $subsheds, $thisyear, $scenarioid, $projectid, $debug) {

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $bmpsubcond = " a.lrseg in ( $sslist) ";
      $lusubcond = " c.lrseg in ($sslist) ";
   } else {
      $bmpsubcond = ' 1 = 1 ';
   }
   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear in ($thisyear) ";
      $ayrcond = " a.thisyear in ($thisyear) ";
      $cyrcond = " c.thisyear in ($thisyear) ";
   } else {
      $yrcond = ' (1 = 1) ';
      $ayrcond = ' (1 = 1) ';
      $cyrcond = ' (1 = 1) ';
   }


   $listobject->queryrecords = array();

   $split = $listobject->startsplit();
   $listobject->querystring = "select $projectid as projectid, a.scenarioid, b.thisyear,  ";
   $listobject->querystring .= " b.bmpname, b.bmptext, ";
   $listobject->querystring .= " CASE  ";
   $listobject->querystring .= "    WHEN d.bmparea is null then b.eligarea ";
   $listobject->querystring .= "    WHEN (b.eligarea is null) and (d.bmparea is not null) then d.bmparea ";
   $listobject->querystring .= "    WHEN (b.eligarea is null) and (d.bmparea is null) then 0.0 ";
   $listobject->querystring .= "    ELSE b.eligarea + d.bmparea ";
   $listobject->querystring .= " END as eligarea, ";
   $listobject->querystring .= " CASE  ";
   $listobject->querystring .= "    WHEN a.bmparea is null then 0.0 ";
   $listobject->querystring .= "    ELSE a.bmparea ";
   $listobject->querystring .= " END as bmparea, ";
   $listobject->querystring .= " CASE  ";
   $listobject->querystring .= "    WHEN a.bmpsubbed is null then 0.0 ";
   $listobject->querystring .= "    ELSE a.bmpsubbed ";
   $listobject->querystring .= " END as bmpsubbed, ";
   $listobject->querystring .= " 1 as editlink ";
   $listobject->querystring .= " from ( select c.thisyear, b.bmpname, b.bmptext, sum(c.luarea) as eligarea  ";
   $listobject->querystring .= "   from bmp_subtypes as b, scen_lrsegs as c, map_landuse_bmp as d ";
   $listobject->querystring .= "   where b.projectid = $projectid ";
   $listobject->querystring .= "      and d.projectid = $projectid ";
   $listobject->querystring .= "      and c.scenarioid = $scenarioid ";
   $listobject->querystring .= "      and $lusubcond ";
   $listobject->querystring .= "      and $yrcond ";
   $listobject->querystring .= "      and b.typeid = $bmptype ";
   $listobject->querystring .= "      and c.luname = d.luname ";
   $listobject->querystring .= "      and b.bmpname = d.bmpname ";
   $listobject->querystring .= "   group by b.bmpname, b.bmptext, c.thisyear ";
   $listobject->querystring .= " ) as b left outer join ";
   $listobject->querystring .= " ( select a.scenarioid, a.bmpname, a.thisyear,  ";
   $listobject->querystring .= "      sum(b.conversion * a.bmparea) as bmparea, ";
   $listobject->querystring .= "      sum(a.bmparea) as bmpsubbed ";
   $listobject->querystring .= "   from scen_lrseg_bmps as a, bmp_subtypes as b ";
   $listobject->querystring .= "   where $yrcond ";
   $listobject->querystring .= "      and a.bmpname = b.bmpname ";
   $listobject->querystring .= "      and b.typeid = $bmptype ";
   $listobject->querystring .= "      and $bmpsubcond ";
   $listobject->querystring .= "      and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "   group by a.scenarioid, a.bmpname, a.thisyear ";
   $listobject->querystring .= " ) as a ";
   $listobject->querystring .= " on ( a.bmpname = b.bmpname ";
   $listobject->querystring .= "        and a.thisyear = b.thisyear ";
   $listobject->querystring .= "    ) ";
   $listobject->querystring .= " left outer join ";
   $listobject->querystring .= "    ( select c.thisyear, c.bmpname, sum(c.chgarea) as bmparea ";
   $listobject->querystring .= "      from scen_bmp_luchghist as c, bmp_subtypes as b ";
   $listobject->querystring .= "        where b.typeid = $bmptype ";
   $listobject->querystring .= "           and b.projectid = $projectid ";
   $listobject->querystring .= "           and c.scenarioid = $scenarioid ";
   $listobject->querystring .= "           and $lusubcond ";
   $listobject->querystring .= "           and $cyrcond ";
   $listobject->querystring .= "           and c.bmpname = b.bmpname ";
   $listobject->querystring .= "      group by c.thisyear, c.bmpname ";
   $listobject->querystring .= " ) as d ";
   $listobject->querystring .= " on ( a.bmpname = d.bmpname ";
   $listobject->querystring .= "         and a.thisyear = d.thisyear ";
   $listobject->querystring .= "    ) ";
   $listobject->querystring .= " order by b.bmptext ";
   if ($debug) { print("<br>$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }

   return $listobject->queryrecords;

}

function getOneLRBmps($listobject, $bmpname, $subsheds, $thisyear, $scenarioid, $projectid, $conv, $debug) {


   #$conv = true or false (1/0) to convert BMP input units to area

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $bmpsubcond = " a.lrseg in ( $sslist) ";
      $lusubcond = " c.lrseg in ($sslist) ";
   } else {
      $bmpsubcond = ' 1 = 1 ';
      $lusubcond = ' 1 = 1 ';
   }
   if (strlen($thisyear) > 0) {
      $ayrcond = " a.thisyear in ($thisyear) ";
      $cyrcond = " c.thisyear in ($thisyear) ";
   } else {
      $ayrcond = " (1 = 1) ";
      $cyrcond = " (1 = 1) ";
   }


   $listobject->queryrecords = array();

   $listobject->querystring = " select $projectid as projectid, a.scenarioid, b.thisyear,  ";
   $listobject->querystring .= " b.typeid, b.bmpname, b.bmptext, ";
   $listobject->querystring .= " CASE  ";
   $listobject->querystring .= "    WHEN ( b.efftype <> 3) then b.eligarea ";
   $listobject->querystring .= "    WHEN ( (b.efftype = 3) and d.bmparea is null ) then b.eligarea ";
   # modified 9/25/2006 to look in the lu-change-history table for applied lu-change BMPs
   $listobject->querystring .= "    WHEN ( (b.efftype = 3) and d.bmparea is not null ) then d.bmparea + b.eligarea ";
   $listobject->querystring .= "    ELSE a.bmparea ";
   $listobject->querystring .= " END as eligarea, ";
   $listobject->querystring .= " CASE  ";
   $listobject->querystring .= "    WHEN a.bmparea is null then 0.0 ";
   $listobject->querystring .= "    ELSE a.bmparea ";
   $listobject->querystring .= " END as bmparea ";
   $listobject->querystring .= " from ( select c.thisyear, b.typeid, b.bmpname, b.bmptext, b.efftype, sum(c.luarea) as eligarea  ";
   $listobject->querystring .= "   from bmp_subtypes as b, scen_lrsegs as c, ";
   $listobject->querystring .= "      ( select luname from map_landuse_bmp ";
   $listobject->querystring .= "        WHERE projectid = $projectid ";
   $listobject->querystring .= "           AND bmpname = '$bmpname' ";
   $listobject->querystring .= "        GROUP by luname ";
   $listobject->querystring .= "      ) as d ";
   $listobject->querystring .= "   where b.projectid = $projectid ";
   $listobject->querystring .= "      and c.scenarioid = $scenarioid ";
   $listobject->querystring .= "      and $cyrcond ";
   $listobject->querystring .= "      and $lusubcond ";
   $listobject->querystring .= "      and b.bmpname = '$bmpname' ";
   $listobject->querystring .= "      and c.luname = d.luname ";
   $listobject->querystring .= "   group by c.thisyear, b.bmpname, b.bmptext, b.typeid, b.efftype ";
   $listobject->querystring .= " ) as b left outer join ";
   $listobject->querystring .= " (select a.scenarioid, a.thisyear, a.bmpname, sum( ";
   if ($conv) {
      $listobject->querystring .= "   b.conversion * ";
   }
   $listobject->querystring .= "   a.bmparea) as bmparea ";
   $listobject->querystring .= "   from scen_lrseg_bmps as a, bmp_subtypes as b ";
   $listobject->querystring .= "   where $ayrcond ";
   $listobject->querystring .= "      and b.projectid = $projectid ";
   $listobject->querystring .= "      and a.bmpname = b.bmpname ";
   $listobject->querystring .= "      and b.bmpname = '$bmpname' ";
   $listobject->querystring .= "      and $bmpsubcond ";
   $listobject->querystring .= "      and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "   group by a.thisyear, a.scenarioid, a.bmpname ";
   $listobject->querystring .= "  ) as a ";
   $listobject->querystring .= " on ( a.bmpname = b.bmpname ";
   $listobject->querystring .= "         and a.thisyear = b.thisyear ";
   $listobject->querystring .= "    ) ";
   $listobject->querystring .= " left outer join ";
   $listobject->querystring .= "    ( select c.thisyear, c.bmpname, sum(c.chgarea) as bmparea ";
   $listobject->querystring .= "      from scen_bmp_luchghist as c ";
   $listobject->querystring .= "        where c.bmpname = '$bmpname' ";
   $listobject->querystring .= "           and c.scenarioid = $scenarioid ";
   $listobject->querystring .= "           and $lusubcond ";
   $listobject->querystring .= "           and $cyrcond ";
   $listobject->querystring .= "      group by c.thisyear, c.bmpname ";
   $listobject->querystring .= " ) as d ";
   $listobject->querystring .= " on ( a.bmpname = d.bmpname ";
   $listobject->querystring .= "         and a.thisyear = d.thisyear ";
   $listobject->querystring .= "    ) ";
   #$listobject->querystring .= " group by b.thisyear, a.scenarioid, b.typeid, b.bmpname, b.bmptext ";
   $listobject->querystring .= " order by b.thisyear, b.bmptext ";
   if ($debug) {
      print("<br>$listobject->querystring ; <br>");
   }
   $listobject->performQuery();

   return $listobject->queryrecords;

}

function getBMPGroupEffic($listobject, $bmptypeid, $subsheds, $thisyear, $scenarioid, $projectid, $debug) {

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subcond = " lrseg in ( $sslist) ";
      $bmpsubcond = " a.lrseg in ( $sslist) ";
      $lusubcond = " c.lrseg in ($sslist) ";
   } else {
      $subcond = ' ( 1 = 1 ) ';
      $bmpsubcond = ' ( 1 = 1 ) ';
      $lusubcond = ' ( 1 = 1 ) ';
   }
   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear in ($thisyear) ";
      $ayrcond = " a.thisyear in ($thisyear) ";
      $cyrcond = " c.thisyear in ($thisyear) ";
   } else {
      $ayrcond = " (1 = 1) ";
      $cyrcond = " (1 = 1) ";
   }

   # make a cross-tab of the efficiencies
   $listobject->querystring = "select a.thisyear, a.typeid, sum(a.imp_eff * c.luarea) as wgted, b.pollutantname ";
   $listobject->querystring .= " from scen_bmp_area_effic as a, pollutanttype as b, scen_lrsegs as c ";
   $listobject->querystring .= " where $bmpsubcond ";
   $listobject->querystring .= "    and $lusubcond ";
   $listobject->querystring .= "    and $ayrcond ";
   $listobject->querystring .= "    and $cyrcond ";
   $listobject->querystring .= "    and a.lrseg = c.lrseg ";
   $listobject->querystring .= "    and a.thisyear = c.thisyear ";
   $listobject->querystring .= "    and a.luname = c.luname ";
   $listobject->querystring .= "    and a.constit = b.typeid ";
   $listobject->querystring .= "    and a.typeid = $bmptypeid ";
   $listobject->querystring .= "    and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and c.scenarioid = $scenarioid ";
   $listobject->querystring .= " group by a.thisyear, a.typeid, a.constit, b.pollutantname ";
   if ($debug) {
      print("<br>$listobject->querystring ; <br>");
   }
   $listobject->startsplit();
   $listobject->performQuery();
   #$listobject->showList();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }

   $qr = $listobject->queryrecords;

   return $qr;

}


function getOneLRBmpType($listobject, $bmptypeid, $subsheds, $thisyear, $scenarioid, $projectid, $debug) {

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subcond = " lrseg in ( $sslist) ";
      $bmpsubcond = " a.lrseg in ( $sslist) ";
      $lusubcond = " c.lrseg in ($sslist) ";
   } else {
      $subcond = ' ( 1 = 1 ) ';
      $bmpsubcond = ' ( 1 = 1 ) ';
      $lusubcond = ' ( 1 = 1 ) ';
   }
   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear in ($thisyear) ";
      $ayrcond = " a.thisyear in ($thisyear) ";
      $cyrcond = " c.thisyear in ($thisyear) ";
   } else {
      $ayrcond = " (1 = 1) ";
      $cyrcond = " (1 = 1) ";
   }

   $listobject->queryrecords = array();
   #$debug = 1;

   $split = $listobject->startsplit();

   $listobject->querystring = " select $projectid as projectid, a.scenarioid, b.thisyear,  ";
   $listobject->querystring .= " b.bmp_name, b.bmp_desc, ";
   # new - combines eligible and applied area for report of total eligible for lu change bmps (like nutrient management)
   $listobject->querystring .= " CASE  ";
   $listobject->querystring .= "    WHEN ( b.efftype not in ( 3, 6 , 7) ) then b.eligarea ";
   $listobject->querystring .= "    WHEN ( (b.efftype in ( 3, 6 , 7)) and d.bmparea is null ) then b.eligarea ";
   # modified 9/25/2006 to look in the lu-change-history table for applied lu-change BMPs
   $listobject->querystring .= "    WHEN ( (b.efftype in ( 3, 6 , 7)) and d.bmparea is not null ) then d.bmparea + b.eligarea ";
   $listobject->querystring .= "    ELSE a.bmparea ";
   $listobject->querystring .= " END as eligarea, ";
   # end - new - special case for LU-change only BMPs
   $listobject->querystring .= " CASE  ";
   $listobject->querystring .= "    WHEN a.bmparea is null then 0.0 ";
   $listobject->querystring .= "    ELSE a.bmparea ";
   $listobject->querystring .= " END as bmparea, ";
   $listobject->querystring .= " CASE  ";
   $listobject->querystring .= "    WHEN ( (b.efftype not in ( 3, 6 , 7)) and c.bmpimplemented is null ) then 0.0 ";
   $listobject->querystring .= "    WHEN ( (b.efftype in ( 3, 6 , 7)) and d.bmparea is null ) then 0.0 ";
   $listobject->querystring .= "    WHEN ( (b.efftype in ( 3, 6 , 7)) and d.bmparea is not null ) then d.bmparea ";
   $listobject->querystring .= "    ELSE c.bmpimplemented ";
   $listobject->querystring .= " END as bmpimplemented ";
   $listobject->querystring .= " from ";
   $listobject->querystring .= "    (select c.thisyear, d.bmp_name, d.bmp_desc, d.efftype, ";
   $listobject->querystring .= "        sum(c.luarea) as eligarea  ";
   $listobject->querystring .= "     from scen_lrsegs as c, ";
   $listobject->querystring .= "        (select a.bmp_name, a.bmp_desc, d.luname, a.efftype ";
   $listobject->querystring .= "         from bmp_types as a, bmp_subtypes as b, ";
   $listobject->querystring .= "            map_landuse_bmp as d ";
   $listobject->querystring .= "         where b.projectid = $projectid ";
   $listobject->querystring .= "            and d.projectid = $projectid ";
   $listobject->querystring .= "            and a.projectid = $projectid ";
   $listobject->querystring .= "            and b.typeid = $bmptypeid ";
   $listobject->querystring .= "            and a.typeid = $bmptypeid ";
   $listobject->querystring .= "            and b.bmpname = d.bmpname ";
   $listobject->querystring .= "         group by a.bmp_name, a.bmp_desc, d.luname, a.efftype ";
   $listobject->querystring .= "         ) as d ";
   $listobject->querystring .= "     where $cyrcond ";
   $listobject->querystring .= "        and c.luname = d.luname ";
   $listobject->querystring .= "        and c.scenarioid = $scenarioid ";
   $listobject->querystring .= "        and $lusubcond ";
   $listobject->querystring .= "     group by c.thisyear, d.bmp_name, d.bmp_desc, d.efftype ";
   $listobject->querystring .= " ) as b left outer join ";
   $listobject->querystring .= " (select a.scenarioid, a.thisyear, c.bmp_name, ";
   $listobject->querystring .= "      sum(c.conversion * a.bmparea) as bmparea ";
   $listobject->querystring .= "   from scen_lrseg_bmps as a, ";
   $listobject->querystring .= "      ( select c.bmp_name, b.bmpname, b.conversion ";
   $listobject->querystring .= "        from bmp_subtypes as b, bmp_types as c  ";
   $listobject->querystring .= "        where b.typeid = $bmptypeid ";
   $listobject->querystring .= "           and c.typeid = $bmptypeid ";
   $listobject->querystring .= "           and b.projectid = $projectid ";
   $listobject->querystring .= "           and c.projectid = $projectid ";
   $listobject->querystring .= "        group by c.bmp_name, b.bmpname, b.conversion ";
   $listobject->querystring .= "       ) as c ";
   $listobject->querystring .= "   where $ayrcond ";
   $listobject->querystring .= "      and a.bmpname = c.bmpname ";
   $listobject->querystring .= "      and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "      and $bmpsubcond ";
   $listobject->querystring .= "   group by a.thisyear, a.scenarioid, c.bmp_name ";
   $listobject->querystring .= "  ) as a ";
   $listobject->querystring .= " on ( a.bmp_name = b.bmp_name ";
   $listobject->querystring .= "         and a.thisyear = b.thisyear ";
   $listobject->querystring .= "    ) ";
   $listobject->querystring .= " left outer join ";
   $listobject->querystring .= "    ( select thisyear, sum(c.value_implemented) as bmpimplemented ";
   $listobject->querystring .= "      from scen_bmp_data as c ";
   $listobject->querystring .= "      where c.scenarioid = $scenarioid ";
   $listobject->querystring .= "         and $lusubcond ";
   $listobject->querystring .= "         and $cyrcond ";
   $listobject->querystring .= "         and c.typeid = $bmptypeid ";
   $listobject->querystring .= "      group by c.thisyear ";
   $listobject->querystring .= " ) as c ";
   $listobject->querystring .= " on ( a.thisyear = c.thisyear ";
   $listobject->querystring .= "    ) ";
   $listobject->querystring .= " left outer join ";
   $listobject->querystring .= "    ( select thisyear, sum(c.chgarea) as bmparea ";
   $listobject->querystring .= "      from scen_bmp_luchghist as c, bmp_subtypes as b ";
   $listobject->querystring .= "        where b.typeid = $bmptypeid ";
   $listobject->querystring .= "           and b.projectid = $projectid ";
   $listobject->querystring .= "           and c.scenarioid = $scenarioid ";
   $listobject->querystring .= "           and $lusubcond ";
   $listobject->querystring .= "           and $cyrcond ";
   $listobject->querystring .= "           and c.bmpname = b.bmpname ";
   $listobject->querystring .= "      group by c.thisyear ";
   $listobject->querystring .= " ) as d ";
   $listobject->querystring .= " on ( a.thisyear = d.thisyear ";
   $listobject->querystring .= "    ) ";
   $listobject->querystring .= " order by b.thisyear, b.bmp_desc ";

   if ($debug) {
      print("<br>$listobject->querystring ; <br>");
   }
   $listobject->performQuery();

   $qr = $listobject->queryrecords;
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }

   return $qr;

}

function showEOFTargets($listobject, $projectid, $scenarioid, $thisyear, $luname, $subsheds, $constit, $debug) {

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $assclause = " a.lrseg in ( $sslist) ";
      $bssclause = " b.lrseg in ( $sslist) ";
   } else {
      $assclause = ' (1 = 1) ';
      $bssclause = ' (1 = 1) ';
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



   $listobject->startsplit();
   # new jon test to get forest and other non-applied land uses
   # Get app rate records for this group
   $listobject->querystring = "  SELECT b.pt, ";
   $listobject->querystring .= " CASE ";
   $listobject->querystring .= "    WHEN b.pt is null THEN 0.0 ";
   $listobject->querystring .= "    ELSE sum(b.meanbalance*a.luarea) / sum(a.luarea) ";
   $listobject->querystring .= " END as meanbalance, ";
   $listobject->querystring .= " CASE ";
   $listobject->querystring .= "    WHEN b.pt is null THEN 0.0 ";
   $listobject->querystring .= "    ELSE sum(b.mean_uptake*a.luarea) / sum(a.luarea) ";
   $listobject->querystring .= " END as mean_uptake, ";
   $listobject->querystring .= " CASE ";
   $listobject->querystring .= "    WHEN b.pt is null THEN 0.0 ";
   $listobject->querystring .= "    ELSE sum(b.total_in*a.luarea) / sum(a.luarea) ";
   $listobject->querystring .= " END as total_in, ";
   $listobject->querystring .= " CASE ";
   $listobject->querystring .= "    WHEN b.pt is null THEN 0.0 ";
   $listobject->querystring .= "    ELSE sum(b.max_uptake*a.luarea) / sum(a.luarea) ";
   $listobject->querystring .= " END as max_uptake, ";
   $listobject->querystring .= " CASE ";
   $listobject->querystring .= "    WHEN b.pt is null THEN 0.0 ";
   $listobject->querystring .= "    ELSE sum(b.minbalance*a.luarea) / sum(a.luarea) ";
   $listobject->querystring .= " END as minbalance, ";
   $listobject->querystring .= " CASE ";
   $listobject->querystring .= "    WHEN b.pt is null THEN 0.0 ";
   $listobject->querystring .= "    ELSE sum(b.eof_target*a.luarea) / sum(a.luarea) ";
   $listobject->querystring .= " END as eof_target ";
   $listobject->querystring .= " from scen_lrsegs as a left outer join gview_eof_targets as b ";
   $listobject->querystring .= "    ON ( a.scenarioid = $scenarioid ";
   $listobject->querystring .= "       AND a.luname = b.luname ";
   $listobject->querystring .= "       AND a.subshedid = b.subshedid ";
   $listobject->querystring .= "       AND $blucond ";
   $listobject->querystring .= "       AND b.pt in ($constit) ";
   $listobject->querystring .= "    ) ";
   $listobject->querystring .= " WHERE a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    AND a.thisyear in ( $thisyear ) ";
   $listobject->querystring .= "    AND $alucond ";
   $listobject->querystring .= "    AND $assclause ";
   $listobject->querystring .= " GROUP BY b.pt ";

   if ($debug) { print("$listobject->querystring ;<br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }
   $stats = $listobject->queryrecords;

   return $stats;

}


function showApplicationDetails($listobject, $projectid, $scenarioid, $thisyear, $luname, $subsheds, $constit, $debug) {

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $assclause = " a.subshedid in (select subshedid from scen_lrsegs ";
      $assclause .= " where scenarioid = $scenarioid ";
      $assclause .= " and thisyear in ($thisyear) ";
      $assclause .= " and lrseg in ( $sslist) group by subshedid ) ";
      $gssclause = " g.subshedid in (select subshedid from scen_lrsegs ";
      $gssclause .= " where scenarioid = $scenarioid ";
      $gssclause .= " and thisyear in ($thisyear) ";
      $gssclause .= " and lrseg in ( $sslist) group by subshedid) ";
   } else {
      $assclause = ' (1 = 1) ';
      $gssclause = ' (1 = 1) ';
   }

   if (strlen($luname) > 0) {
      $llist = "'" . join("','", split(",", $luname)) . "'";
      $lucond = " luname in ($llist) ";
      $alucond = " a.luname in ($llist) ";
      $glucond = " g.luname in ($llist) ";
      $formlu = join(", ", split(",", $luname));
   } else {
      $lucond = ' (1 = 1) ';
      $alucond = ' (1 = 1) ';
      $glucond = ' (1 = 1) ';
      $formlu = $luname;
   }

   if (strlen($constit) > 0) {
      $aconcond = " a.pollutanttype in ($constit) ";
      $econcond = " e.typeid in ($constit) ";
   } else {
      $aconcond = ' (1 = 1) ';
      $econcond = ' (1 = 1) ';
   }



   $listobject->startsplit();
   # new jon test to get forest and other non-applied land uses
   # Get app rate records for this group
   $listobject->querystring = "( ";
   $listobject->querystring .= " SELECT a.subshedid, a.luname, a.thisyear, b.sourcename, c.classname, d.spreadname, ";
   $listobject->querystring .= "    e.pollutantname, ";
   $listobject->querystring .= "    sum(a.annualapplied) as annualapplied, ";
   $listobject->querystring .= "    sum(a.jan) as jan, ";
   $listobject->querystring .= "    sum(a.feb) as feb, ";
   $listobject->querystring .= "    sum(a.mar) as mar, ";
   $listobject->querystring .= "    sum(a.apr) as apr, ";
   $listobject->querystring .= "    sum(a.may) as may, ";
   $listobject->querystring .= "    sum(a.jun) as jun, ";
   $listobject->querystring .= "    sum(a.jul) as jul, ";
   $listobject->querystring .= "    sum(a.aug) as aug, ";
   $listobject->querystring .= "    sum(a.sep) as sep, ";
   $listobject->querystring .= "    sum(a.oct) as oct, ";
   $listobject->querystring .= "    sum(a.nov) as nov, ";
   $listobject->querystring .= "    sum(a.dec) as dec ";
   $listobject->querystring .= " from scen_sourceperunitarea as a, sources as b, ";
   $listobject->querystring .= "    sourceclass as c, spreadtype as d, pollutanttype as e ";
   $listobject->querystring .= " WHERE a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    AND a.thisyear in ( $thisyear ) ";
   $listobject->querystring .= "    AND $alucond ";
   $listobject->querystring .= "    AND $aconcond ";
   $listobject->querystring .= "    AND $assclause ";
   $listobject->querystring .= "    AND a.sourceid = b.sourceid ";
   $listobject->querystring .= "    AND a.sourceclass = c.classid ";
   $listobject->querystring .= "    AND a.spreadid = d.spreadid ";
   $listobject->querystring .= "    AND a.pollutanttype = e.typeid ";
   $listobject->querystring .= " GROUP BY a.subshedid, a.luname, a.thisyear, b.sourcename, ";
   $listobject->querystring .= "    c.classname, d.spreadname, e.pollutantname ";

   $listobject->querystring .= " ) UNION ( ";

   $listobject->querystring .= "  SELECT g.subshedid, g.luname, g.thisyear, 'legume' as sourcename, ";
   $listobject->querystring .= "    'Legume Fixation' as classname, 'Legume Fixation/Crop Need' as spreadname, e.pollutantname, ";
   $listobject->querystring .= "    sum(g.totaln) as annualapplied, ";
   $listobject->querystring .= "    sum(g.jan) as jan, ";
   $listobject->querystring .= "    sum(g.feb) as feb, ";
   $listobject->querystring .= "    sum(g.mar) as mar, ";
   $listobject->querystring .= "    sum(g.apr) as apr, ";
   $listobject->querystring .= "    sum(g.may) as may, ";
   $listobject->querystring .= "    sum(g.jun) as jun, ";
   $listobject->querystring .= "    sum(g.jul) as jul, ";
   $listobject->querystring .= "    sum(g.aug) as aug, ";
   $listobject->querystring .= "    sum(g.sep) as sep, ";
   $listobject->querystring .= "    sum(g.oct) as oct, ";
   $listobject->querystring .= "    sum(g.nov) as nov, ";
   $listobject->querystring .= "    sum(g.dec) as dec ";
   $listobject->querystring .= " from scen_legume_n as g, pollutanttype as e ";
   $listobject->querystring .= " WHERE g.scenarioid = $scenarioid ";
   $listobject->querystring .= "    AND g.thisyear in ( $thisyear ) ";
   $listobject->querystring .= "    AND $glucond ";
   $listobject->querystring .= "    AND $econcond ";
   $listobject->querystring .= "    AND $gssclause ";
   $listobject->querystring .= "    AND g.constit = e.shortname ";
   $listobject->querystring .= " GROUP BY g.subshedid, g.luname, g.thisyear, e.pollutantname ";

   $listobject->querystring .= " )";

   if ($debug) { print("$listobject->querystring ;<br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }
   $stats = $listobject->queryrecords;
   return $stats;

}

function showApplicationStats($listobject, $projectid, $scenarioid, $thisyear, $luname, $subsheds, $constit, $cleanup, $debug) {

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $assclause = " a.lrseg in ( $sslist) ";
      $bssclause = " b.lrseg in ( $sslist) ";
   } else {
      $assclause = ' (1 = 1) ';
      $bssclause = ' (1 = 1) ';
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

   $listobject->startsplit();
   # new jon test to get forest and other non-applied land uses
   # Get app rate records for this group
   $listobject->querystring = " create temp table applyinfo_temp as  ";
   $listobject->querystring .= " select '$formlu'::varchar as luname, a.thisyear, ";
   $listobject->querystring .= "    min(a.total_pct) as minpct, ";
   $listobject->querystring .= "    max(total_pct) as maxpct, ";
   $listobject->querystring .= "    sum(b.luarea*a.total_pct)/sum(b.luarea) as awrate, ";
   # add in 0.00001 to avoid div by zero error
   $listobject->querystring .= "    avg(total_pct) as avgrate, ";
   $listobject->querystring .= "    sum(b.luarea) as totalarea, ";
   $listobject->querystring .= "    sum(b.luarea*(a.annualapplied+a.legume)) as total, ";
   $listobject->querystring .= "    sum(b.luarea*(a.annualapplied+a.legume))/sum(b.luarea) as perac, ";
   $listobject->querystring .= "    sum(b.luarea*a.legume)/sum(b.luarea) as legume, ";
   $listobject->querystring .= "    sum(b.luarea*c.eof_target)/sum(b.luarea) as eof_target ";
   $listobject->querystring .= " from scen_lrsegs as b left outer join gview_eof_targets as c ";
   $listobject->querystring .= "   on ( c.scenarioid = $scenarioid ";
   $listobject->querystring .= "      and a.subshedid = c.subshedid ";
   $listobject->querystring .= "      and $clucond ";
   $listobject->querystring .= "      and c.luname = b.luname ";
   $listobject->querystring .= "      and c.pt = $constit ";
   $listobject->querystring .= "   ) ";
   $listobject->querystring .= " left outer join gview_model_inputs as a ";
   $listobject->querystring .= "   on ( a.thisyear in ( $thisyear ) ";
   $listobject->querystring .= "      and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "      and a.thisyear = b.thisyear ";
   $listobject->querystring .= "      and a.subshedid = b.subshedid ";
   $listobject->querystring .= "      and $alucond ";
   $listobject->querystring .= "      and b.luname = a.luname ";
   $listobject->querystring .= "      and a.pt = $constit ";
   $listobject->querystring .= "   ) ";
   # END - modification to allow aggregate land use
   $listobject->querystring .= " where b.scenarioid = $scenarioid ";
   $listobject->querystring .= "   and b.thisyear in ( $thisyear ) ";
   $listobject->querystring .= "   and $blucond ";
   $listobject->querystring .= "   and $bssclause ";
   $listobject->querystring .= "   and b.luarea > 0 ";
   # START - modification to allow aggregate land use
   #$listobject->querystring .= "group by a.luname, a.thisyear ";
   # END - modification to allow aggregate land use
   $listobject->querystring .= "group by a.thisyear ";

   /*
   # Get app rate records for this group
   $listobject->querystring = " create temp table applyinfo_temp as  ";
   $listobject->querystring .= " select '$formlu'::varchar as luname, a.thisyear, ";
   $listobject->querystring .= "    min(a.total_pct) as minpct, max(total_pct) as maxpct, ";
   $listobject->querystring .= "    sum(b.luarea*a.total_pct)/sum(b.luarea) as awrate, ";
   # add in 0.00001 to avoid div by zero error
   $listobject->querystring .= "    avg(total_pct) as avgrate, ";
   $listobject->querystring .= "    sum(b.luarea) as totalarea, ";
   $listobject->querystring .= "    sum(b.luarea*(a.annualapplied+a.legume)) as total, ";
   $listobject->querystring .= "    sum(b.luarea*(a.annualapplied+a.legume))/sum(b.luarea) as perac, ";
   $listobject->querystring .= "    sum(b.luarea*a.legume)/sum(b.luarea) as legume, ";
   $listobject->querystring .= "    sum(b.luarea*c.eof_target)/sum(b.luarea) as eof_target ";
   $listobject->querystring .= " $fromwhere";
   $listobject->querystring .= " from gview_model_inputs as a, scen_lrsegs as b, gview_eof_targets as c ";
   $listobject->querystring .= " where a.thisyear in ( $thisyear ) ";
   $listobject->querystring .= "   and b.thisyear in ( $thisyear ) ";
   $listobject->querystring .= "   and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "   and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "   and c.scenarioid = $scenarioid ";
   $listobject->querystring .= "   and a.thisyear = b.thisyear ";
   $listobject->querystring .= "   and a.subshedid = b.subshedid ";
   $listobject->querystring .= "   and a.subshedid = c.subshedid ";
   # START - modification to allow aggregate land use
   #$listobject->querystring .= "   and a.luname = '$luname' ";
   #$listobject->querystring .= "   and b.luname = '$luname' ";
   #$listobject->querystring .= "   and c.luname = '$luname' ";
   $listobject->querystring .= "   and $alucond ";
   $listobject->querystring .= "   and $blucond ";
   $listobject->querystring .= "   and $clucond ";
   $listobject->querystring .= "   and b.luname = a.luname ";
   $listobject->querystring .= "   and c.luname = a.luname ";
   # END - modification to allow aggregate land use
   $listobject->querystring .= "   and b.luarea > 0 ";
   $listobject->querystring .= "   and a.pt = $constit ";
   $listobject->querystring .= "   and c.pt = $constit ";
   $listobject->querystring .= "   and $bssclause ";
   # START - modification to allow aggregate land use
   #$listobject->querystring .= "group by a.luname, a.thisyear ";
   # END - modification to allow aggregate land use
   $listobject->querystring .= "group by a.thisyear ";
   */
   if ($debug) { print("$listobject->querystring ;<br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }

   # Get manure vs. fert records for this group
   # reset fromwhere clause
   $listobject->querystring = " create temp table apptype_temp as ";
   $listobject->querystring .= " select '$formlu'::varchar as luname, a.thisyear,  ";
   # add in the 0.000001 so as to avoid a division by zero error for areas receiving no inputs
   $listobject->querystring .= "    sum(a.luarea * b.fertapp / (b.fertapp + b.nonfertapp ";
   $listobject->querystring .= "       + 0.000001))/sum(a.luarea) as fertpct, ";
   $listobject->querystring .= "    sum(a.luarea * b.max_uptake)/sum(a.luarea) as meanuptk, ";
   $listobject->querystring .= "    sum(a.luarea * b.nonfertapp / (b.fertapp + b.nonfertapp ";
   $listobject->querystring .= "       + 0.000001))/sum(a.luarea) as manurepct ";
   $listobject->querystring .= " from scen_lrsegs as a, scen_nutrient_components as b ";
   $listobject->querystring .= " where a.thisyear in ( $thisyear )";
   $listobject->querystring .= "   and b.thisyear in ( $thisyear ) ";
   $listobject->querystring .= "   and a.thisyear = b.thisyear ";
   $listobject->querystring .= "   and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "   and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "   and a.luarea > 0 ";
   # START - modification to allow aggregate land use
   #$listobject->querystring .= "   and a.luname = '$luname' ";
   #$listobject->querystring .= "   and b.luname = '$luname' ";
   $listobject->querystring .= "   and $alucond ";
   $listobject->querystring .= "   and $blucond ";
   $listobject->querystring .= "   and b.luname = a.luname ";
   # END - modification to allow aggregate land use
   $listobject->querystring .= "   and b.max_uptake is not null ";
   $listobject->querystring .= "   and b.pt = $constit ";
   $listobject->querystring .= "   and $assclause ";
   $listobject->querystring .= "   and a.subshedid = b.subshedid ";
   # START - modification to allow aggregate land use
   #$listobject->querystring .= "group by a.luname, a.thisyear ";
   # END - modification to allow aggregate land use
   $listobject->querystring .= "group by a.thisyear ";
   if ($debug) { print("$listobject->querystring ;<br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }

   $listobject->querystring = " create temp table applycol_temp as  ";
   $listobject->querystring .= " ( select luname, thisyear, 'Affected Area' as label, ";
   $listobject->querystring .= "      totalarea as thisvalue";
   $listobject->querystring .= "   from applyinfo_temp";
   $listobject->querystring .= " ) UNION  ";
   $listobject->querystring .= " ( select luname, thisyear, 'Total App (lbs)' as label, ";
   $listobject->querystring .= "      total as thisvalue";
   $listobject->querystring .= "   from applyinfo_temp";
   $listobject->querystring .= " ) UNION  ";
   $listobject->querystring .= " ( select luname, thisyear, 'Minimum Rate' as label, ";
   $listobject->querystring .= "      minpct as thisvalue";
   $listobject->querystring .= "   from applyinfo_temp";
   $listobject->querystring .= " ) UNION  ";
   $listobject->querystring .= " ( select luname, thisyear, 'Maximum Rate' as label, ";
   $listobject->querystring .= "      maxpct as thisvalue ";
   $listobject->querystring .= "   from applyinfo_temp";
   $listobject->querystring .= " ) UNION  ";
   $listobject->querystring .= " ( select luname, thisyear, 'Mean Rate' as label, ";
   $listobject->querystring .= "      awrate as thisvalue ";
   $listobject->querystring .= "   from applyinfo_temp";
   $listobject->querystring .= " ) UNION  ";
   $listobject->querystring .= " ( select luname, thisyear, 'AW Total (lbs/ac)' as label, ";
   $listobject->querystring .= "     perac as thisvalue ";
   $listobject->querystring .= "   from applyinfo_temp";
   $listobject->querystring .= " ) UNION  ";
   $listobject->querystring .= " ( select luname, thisyear, 'EOF Target (lbs/ac)' as label, ";
   $listobject->querystring .= "      eof_target as thisvalue ";
   $listobject->querystring .= "   from applyinfo_temp";
   $listobject->querystring .= " ) UNION  ";
   $listobject->querystring .= " ( select luname, thisyear, 'AW Legume (lbs/ac)' as label, ";
   $listobject->querystring .= "      legume as thisvalue ";
   $listobject->querystring .= "   from applyinfo_temp";
   $listobject->querystring .= " ) UNION ";
   $listobject->querystring .= " ( select luname, thisyear, '% Fertilizer' as label, fertpct ";
   $listobject->querystring .= "   from apptype_temp ";
   $listobject->querystring .= " ) UNION ";
   $listobject->querystring .= " ( select luname, thisyear, 'Max Uptake' as label, ";
   $listobject->querystring .= "      meanuptk ";
   $listobject->querystring .= "   from apptype_temp ";
   $listobject->querystring .= " ) UNION ";
   $listobject->querystring .= " ( select luname, thisyear, '% Manure' as label, manurepct ";
   $listobject->querystring .= "   from apptype_temp ";
   $listobject->querystring .= " ) ";

   $split = $listobject->startsplit();
   if ($debug) { print("$listobject->querystring ;<br>"); }
   $listobject->performQuery();
   if ($debug) { print("Query Time: $split<br>"); }

   $cc = doGenericCrossTab ($listobject, 'applycol_temp', 'luname, label', 'thisyear', 'thisvalue', 1);
   $listobject->querystring = $cc;
   if ($debug) { print("$listobject->querystring ;<br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }
   #if ($debug) { $listobject->showList(); }
   $stats = $listobject->queryrecords;
   if ($cleanup) {
      $listobject->querystring = " drop table apptype_temp ";
      $listobject->performQuery();
      $listobject->querystring = " drop table applycol_temp ";
      $listobject->performQuery();
      $listobject->querystring = " drop table applyinfo_temp ";
      $listobject->performQuery();
   }
   return $stats;

}


function getProjectedSources($listobject, $subsheds, $scenarioid, $sources, $thisyear, $debug) {

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
   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear in ( $thisyear ) ";
      $ayrcond = " a.thisyear in ( $thisyear ) ";
      $byrcond = " b.thisyear in ( $thisyear ) ";
   } else {
      $yrcond = " (1 = 1) ";
      $ayrcond = " (1 = 1) ";
      $byrcond = " (1 = 1) ";
   }

   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $asubshedcond = " subshedid in (select subshedid from scen_lrsegs ";
      $asubshedcond .= " where scenarioid = $scenarioid ";
      $asubshedcond .= "    and lrseg in ($sslist) ";
      $asubshedcond .= "    and $yrcond ";
      $asubshedcond .= " group by subshedid ) ";
   } else {
      $asubshedcond = ' (1 = 1) ';
   }

   $listobject->startsplit();
   # Get app rate records for this group
   $listobject->querystring = "  select a.subshedid, b.sourcename, c.sourcename as description, ";
   $listobject->querystring .= "    a.thisyear, a.lastyear, a.lastpop, a.lsr_pop, a.landpop, a.rsquare, ";
   $listobject->querystring .= "    ((a.rsquare * a.landpop) + ((1.0 - a.rsquare) * a.lsr_pop)) as luau_pop ";
   $listobject->querystring .= " from scen_landpop_predict as a, scen_sources as b, scen_sourceloadtype as c ";
   $listobject->querystring .= " where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and c.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and $ayrcond ";
   $listobject->querystring .= "    and $asubshedcond ";
   $listobject->querystring .= "    and a.sourceid = b.sourceid";
   $listobject->querystring .= "    and b.typeid = c.typeid ";
   $listobject->querystring .= " order by a.thisyear, a.subshedid, c.sourcename ";
   if ($debug) { print("$listobject->querystring ;<br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }

   return $listobject->queryrecords;

}

function showModelOutputStats($listobject, $projectid, $scenarioid, $targetyears, $luname, $subsheds, $constit, $modelscen, $cleanup, $debug) {

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions

   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $assclause = " a.lrseg in ( $sslist) ";
      $bssclause = " b.lrseg in ( $sslist) ";
   } else {
      $assclause = ' (1 = 1) ';
      $bssclause = ' (1 = 1) ';
   }

   $yearar = split(',', $targetyears);
   $minyear = $yearar[0];
   $maxyear = $yearar[count($yearar) - 1];

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

   $listobject->startsplit();
   # Get app rate records for this group
   $listobject->querystring = " create temp table modelinfo_temp as  ";
   $listobject->querystring .= " select '$formlu'::varchar as luname, 'Average'::varchar as thisyear, ";
   $listobject->querystring .= "    sum(b.luarea) as totalarea, ";
   $listobject->querystring .= "    min(a.annual_uptake) as minuptake, max(annual_uptake) as maxuptake, ";
   $listobject->querystring .= "    sum(b.luarea*a.annual_uptake)/sum(b.luarea) as meanuptake, ";
   $listobject->querystring .= "    min(a.annual_vol) as minvol, max(annual_vol) as maxvol, ";
   $listobject->querystring .= "    sum(b.luarea*a.annual_vol)/sum(b.luarea) as meanvol, ";
   $listobject->querystring .= "    min(a.annual_eof) as mineof, max(annual_eof) as maxeof, ";
   $listobject->querystring .= "    sum(b.luarea*a.annual_eof)/sum(b.luarea) as meaneof ";
   $listobject->querystring .= " from scen_model_uptake as a, scen_lrsegs as b, pollutanttype as c ";
   $listobject->querystring .= " where a.thisyear <= $maxyear ";
   $listobject->querystring .= "   and a.thisyear >= $minyear ";
   $listobject->querystring .= "   and b.thisyear <= $maxyear ";
   $listobject->querystring .= "   and b.thisyear >= $minyear ";
   $listobject->querystring .= "   and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "   and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "   and a.thisyear = b.thisyear ";
   $listobject->querystring .= "   and a.landseg = b.landseg ";
   # START - modification to allow aggregate land use
   #$listobject->querystring .= "   and a.luname = '$luname' ";
   #$listobject->querystring .= "   and b.luname = '$luname' ";
   $listobject->querystring .= "   and $alucond ";
   $listobject->querystring .= "   and $blucond ";
   # get proper model scenario
   $listobject->querystring .= "   and a.model_scen = '$modelscen' ";
   $listobject->querystring .= "   and b.luname = a.luname ";
   # END - modification to allow aggregate land use
   $listobject->querystring .= "   and b.luarea > 0 ";
   $listobject->querystring .= "   and c.typeid = $constit ";
   $listobject->querystring .= "   and c.shortname = a.constit ";
   $listobject->querystring .= "   and $bssclause ";
   # START - modification to allow aggregate land use
   #$listobject->querystring .= "group by a.luname, a.thisyear ";
   # END - modification to allow aggregate land use
   #$listobject->querystring .= "group by a.thisyear ";
   if ($debug) { print("$listobject->querystring ;<br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }


   $listobject->querystring = " create temp table modelcol_temp as  ";
   $listobject->querystring .= " ( select luname, thisyear, 'Modeled Area' as label, ";
   $listobject->querystring .= "      totalarea as thisvalue";
   $listobject->querystring .= "   from modelinfo_temp";
   $listobject->querystring .= " ) UNION  ";
   $listobject->querystring .= " ( select luname, thisyear, 'Max Modeled Uptake (lbs/acre)' as label, ";
   $listobject->querystring .= "      maxuptake as thisvalue";
   $listobject->querystring .= "   from modelinfo_temp";
   $listobject->querystring .= " ) UNION  ";
   $listobject->querystring .= " ( select luname, thisyear, 'Min Modeled Uptake (lbs/acre)' as label, ";
   $listobject->querystring .= "      minuptake as thisvalue";
   $listobject->querystring .= "   from modelinfo_temp";
   $listobject->querystring .= " ) UNION  ";
   $listobject->querystring .= " ( select luname, thisyear, 'Mean Modeled Uptake (lbs/acre)' as label, ";
   $listobject->querystring .= "      meanuptake as thisvalue";
   $listobject->querystring .= "   from modelinfo_temp";
   $listobject->querystring .= " ) UNION  ";
   $listobject->querystring .= " ( select luname, thisyear, 'Max Modeled EOF (lbs/acre)' as label, ";
   $listobject->querystring .= "      maxeof as thisvalue";
   $listobject->querystring .= "   from modelinfo_temp";
   $listobject->querystring .= " ) UNION  ";
   $listobject->querystring .= " ( select luname, thisyear, 'Min Modeled EOF (lbs/acre)' as label, ";
   $listobject->querystring .= "      mineof as thisvalue";
   $listobject->querystring .= "   from modelinfo_temp";
   $listobject->querystring .= " ) UNION  ";
   $listobject->querystring .= " ( select luname, thisyear, 'Mean Modeled EOF (lbs/acre)' as label, ";
   $listobject->querystring .= "      meaneof as thisvalue";
   $listobject->querystring .= "   from modelinfo_temp";
   $listobject->querystring .= " ) UNION  ";
   $listobject->querystring .= " ( select luname, thisyear, 'Max Modeled Volatilization (lbs/acre)' as label, ";
   $listobject->querystring .= "      maxvol as thisvalue";
   $listobject->querystring .= "   from modelinfo_temp";
   $listobject->querystring .= " ) UNION  ";
   $listobject->querystring .= " ( select luname, thisyear, 'Min Modeled Volatilization (lbs/acre)' as label, ";
   $listobject->querystring .= "      minvol as thisvalue";
   $listobject->querystring .= "   from modelinfo_temp";
   $listobject->querystring .= " ) UNION  ";
   $listobject->querystring .= " ( select luname, thisyear, 'Mean Modeled Volatilization (lbs/acre)' as label, ";
   $listobject->querystring .= "      meanvol as thisvalue";
   $listobject->querystring .= "   from modelinfo_temp";
   $listobject->querystring .= " ) ";
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }

   if ($debug) { print("$listobject->querystring ;<br>"); }
   $listobject->performQuery();

   $cc = doGenericCrossTab ($listobject, 'modelcol_temp', 'luname, label', 'thisyear', 'thisvalue', 1);
   $listobject->querystring = $cc;
   if ($debug) { print("$listobject->querystring ;<br>"); }
   $listobject->performQuery();
   $split = $listobject->startsplit();
   if ($debug) { print("Query Time: $split<br>"); }
   if ($debug) { $listobject->showList(); }
   $stats = $listobject->queryrecords;
   if ($cleanup) {
      $listobject->querystring = " drop table modelinfo_temp ";
      $listobject->performQuery();
      $listobject->querystring = " drop table modelcol_temp ";
      $listobject->performQuery();
   }
   return $stats;

}


?>