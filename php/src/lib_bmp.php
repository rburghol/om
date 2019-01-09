<?php

function setupBMPTablesForVortex($listobject, $scenarioid, $projectid, $debug = 0) {

   $listobject->querystring = "delete from bmp_global_eff where scenarioid = $scenarioid ";
   $listobject->performQuery();
   if ($debug) { print("$listobject->querystring ;<br>"); }

   # all of these union'ed queries assume that the maximum value of efficiency that a bmp is mapped to
   # for a given land use is its actual efficiency. So the user should be careful to not have general
   # bmp categories that are applicable to multiple land uses that have an efficiency value that is higher
   # than any single land use that the BMP may affect.
   # insert values for nitrogen

   $listobject->querystring = "insert into bmp_global_eff (scenarioid, bmpname, pollutantid, efftype, luname, efficiency, affected_area) ";
   $listobject->querystring .= " select $scenarioid, a.bmp_name, c.typeid, b.efftype, d.luname, max(b.neffic), max(b.naffect_area) ";
   $listobject->querystring .= " from bmp_types as a, bmp_subtypes as b, pollutanttype as c, map_landuse_bmp as d ";
   $listobject->querystring .= " where a.typeid = b.typeid ";
   $listobject->querystring .= " and b.bmpname = d.bmpname ";
   $listobject->querystring .= " and b.neffic <> -1 ";
   $listobject->querystring .= " and c.shortname = 'totn' ";
   $listobject->querystring .= " and a.projectid = $projectid ";
   $listobject->querystring .= " and b.projectid = $projectid ";
   $listobject->querystring .= " and d.projectid = $projectid ";
   # don't include buffer-type, since they are regional
   $listobject->querystring .= " and a.typeid not in ( ";
   $listobject->querystring .= "    select typeid from bmp_types where bmp_name in ('fb', 'gb', 'wp') and projectid = $projectid ";
   $listobject->querystring .= "    ) ";
   # only do multiplicative and additive
   $listobject->querystring .= " and b.efftype in (1,2,6,7) ";
   $listobject->querystring .= " group by a.bmp_name, c.typeid, b.efftype, d.luname ";
   $listobject->performQuery();
   if ($debug) { print("$listobject->querystring ;<br>"); }

   # insert values for phosphorus
   $listobject->querystring = "insert into bmp_global_eff (scenarioid, bmpname, pollutantid, efftype, luname, efficiency, affected_area) ";
   $listobject->querystring .= " select $scenarioid, a.bmp_name, c.typeid, b.efftype, d.luname, max(b.peffic), max(b.paffect_area) ";
   $listobject->querystring .= " from bmp_types as a, bmp_subtypes as b, pollutanttype as c, map_landuse_bmp as d ";
   $listobject->querystring .= " where a.typeid = b.typeid ";
   $listobject->querystring .= " and b.bmpname = d.bmpname ";
   $listobject->querystring .= " and b.peffic <> -1 ";
   $listobject->querystring .= " and c.shortname = 'totp' ";
   $listobject->querystring .= " and a.projectid = $projectid ";
   $listobject->querystring .= " and b.projectid = $projectid ";
   $listobject->querystring .= " and d.projectid = $projectid ";
   # don't include buffer-type, since they are regional
   $listobject->querystring .= " and a.typeid not in ( ";
   $listobject->querystring .= "    select typeid from bmp_types where bmp_name in ('fb', 'gb', 'wp') and projectid = $projectid ";
   $listobject->querystring .= "    ) ";
   # only do multiplicative and additive (& those that are LU change  and Mult/add)
   $listobject->querystring .= " and b.efftype in (1,2,6,7) ";
   $listobject->querystring .= " group by a.bmp_name, c.typeid, b.efftype, d.luname ";
   $listobject->performQuery();

   # insert values for tss
   $listobject->querystring = "insert into bmp_global_eff (scenarioid, bmpname, pollutantid, efftype, luname, efficiency, affected_area) ";
   $listobject->querystring .= " select $scenarioid, a.bmp_name, c.typeid, b.efftype, d.luname, max(b.seffic), max(b.saffect_area) ";
   $listobject->querystring .= " from bmp_types as a, bmp_subtypes as b, pollutanttype as c, map_landuse_bmp as d ";
   $listobject->querystring .= " where a.typeid = b.typeid ";
   $listobject->querystring .= " and b.bmpname = d.bmpname ";
   $listobject->querystring .= " and b.seffic <> -1 ";
   $listobject->querystring .= " and c.shortname = 'tss' ";
   $listobject->querystring .= " and a.projectid = $projectid ";
   $listobject->querystring .= " and b.projectid = $projectid ";
   $listobject->querystring .= " and d.projectid = $projectid ";
   # don't include buffer-type, since they are regional
   $listobject->querystring .= " and a.typeid not in ( ";
   $listobject->querystring .= "    select typeid from bmp_types where bmp_name in ('fb', 'gb', 'wp') and projectid = $projectid ";
   $listobject->querystring .= "    ) ";
   # only do multiplicative and additive
   $listobject->querystring .= " and b.efftype in (1,2,6,7) ";
   $listobject->querystring .= " group by a.bmp_name, c.typeid, b.efftype, d.luname ";
   $listobject->performQuery();


   # now, take all of these values and store them in a single table for the project
   # takes default values for BMP efficiency for ones that do not vary
   # by region. The vortex wants a copy of each bmp tagged to state_seg,
   # so we create a copy of the values for each stateseg/lu/bmp combo

   # clear old values
   $listobject->querystring = " delete from proj_bmp_efficiencies ";
   $listobject->querystring .= " where projectid = $projectid ";
   if ($debug) { print("$listobject->querystring ;<br>"); }
   $listobject->performQuery();

   # start with bmps that do not vary by region
   # add in a value for null (-1) stateseg
   $listobject->querystring = " insert into proj_bmp_efficiencies (projectid, stseg, luname, bmpname, ";
   $listobject->querystring .= "    efficiency, affected_area, vortexname, constit ) ";
   $listobject->querystring .= " (select $projectid, a.stseg, b.luname, b.bmpname, ";
   $listobject->querystring .= " b.efficiency, b.affected_area, c.vortexname, c.typeid ";
   $listobject->querystring .= " from ";
   $listobject->querystring .= "    ( ";
   $listobject->querystring .= "       (select a.stseg from subshedinfo as a ";
   $listobject->querystring .= "       where a.projectid = $projectid ";
   $listobject->querystring .= "          and a.stseg is not null ";
   $listobject->querystring .= "       group by stseg)  ";
   $listobject->querystring .= "       UNION ( ";
   $listobject->querystring .= "          select '-1' as stseg  ";
   $listobject->querystring .= "       )  ";
   $listobject->querystring .= "    )as a, ";
   $listobject->querystring .= "    bmp_global_eff as b, pollutanttype as c  ";
   $listobject->querystring .= " where b.scenarioid = $scenarioid";
   $listobject->querystring .= "    and c.typeid = b.pollutantid ";
   # add in bmps which vary by region (currently only buffers)
   $listobject->querystring .= " ) UNION (";
   $listobject->querystring .= " select $projectid, b.stseg, b.luname, b.bmpname, ";
   $listobject->querystring .= " b.efficiency, a.affected_area, c.vortexname, c.typeid ";
   $listobject->querystring .= " FROM pollutanttype as c, bmp_types as d,  ";
   $listobject->querystring .= " (  ";
   $listobject->querystring .= "    ( select typeid, 1 as pollutantid, max(naffect_area) as affected_area  ";
   $listobject->querystring .= "      from bmp_subtypes as a  ";
   $listobject->querystring .= "      where projectid = $projectid  ";
   $listobject->querystring .= "      group by typeid ";
   $listobject->querystring .= "    )  ";
   $listobject->querystring .= "    UNION  ";
   $listobject->querystring .= "    ( select typeid, 2 as pollutantid, max(paffect_area) as affected_area  ";
   $listobject->querystring .= "      from bmp_subtypes as a  ";
   $listobject->querystring .= "      where projectid = $projectid ";
   $listobject->querystring .= "      group by typeid ";
   $listobject->querystring .= "    )  ";
   $listobject->querystring .= "    UNION  ";
   $listobject->querystring .= "    ( select typeid, 8 as pollutantid, max(saffect_area) as affected_area  ";
   $listobject->querystring .= "      from bmp_subtypes as a  ";
   $listobject->querystring .= "      where projectid = $projectid ";
   $listobject->querystring .= "      group by typeid ";
   $listobject->querystring .= "    )  ";
   $listobject->querystring .= " ) as a, ";
   $listobject->querystring .= " (select a.stseg, a.bmpname, a.pollutantid, a.efftype, a.efficiency, ";
   $listobject->querystring .= "    b.luname from ";
   $listobject->querystring .= "    ( ";
   # get forest buffer values
   $listobject->querystring .= "       ( select a.stseg, 'fb' as bmpname, 1 as pollutantid, 1 as efftype, ";
   $listobject->querystring .= "            a.fb_tn as efficiency ";
   $listobject->querystring .= "    from buffer_effic as a ) ";
   $listobject->querystring .= "       UNION ";
   $listobject->querystring .= "       ( select a.stseg, 'fb' as bmpname, 2 as pollutantid, 1 as efftype, ";
   $listobject->querystring .= "       a.fb_tp as efficiency ";
   $listobject->querystring .= "    from buffer_effic as a ) ";
   $listobject->querystring .= "       UNION  ";
   $listobject->querystring .= "       ( select a.stseg, 'fb' as bmpname, 8 as pollutantid, 1 as efftype, ";
   $listobject->querystring .= "       a.fb_tss as efficiency ";
   $listobject->querystring .= "    from buffer_effic as a ) ";
   $listobject->querystring .= "       UNION ";
   # get grass buffer values
   $listobject->querystring .= "       ( select a.stseg, 'gb' as bmpname, 1 as pollutantid, 1 as efftype, ";
   $listobject->querystring .= "       a.gb_tn as efficiency ";
   $listobject->querystring .= "    from buffer_effic as a ) ";
   $listobject->querystring .= "       UNION ";
   $listobject->querystring .= "       ( select a.stseg, 'gb' as bmpname, 2 as pollutantid, 1 as efftype, ";
   $listobject->querystring .= "       a.gb_tp as efficiency ";
   $listobject->querystring .= "    from buffer_effic as a ) ";
   $listobject->querystring .= "       UNION ";
   $listobject->querystring .= "       ( select a.stseg, 'gb' as bmpname, 8 as pollutantid, 1 as efftype, ";
   $listobject->querystring .= "       a.gb_tss as efficiency ";
   $listobject->querystring .= "    from buffer_effic as a ) ";
   $listobject->querystring .= "    ) as a, ";
   $listobject->querystring .= "    (select hspflu as luname from landuses ";
   $listobject->querystring .= "          where projectid = $projectid ";
   $listobject->querystring .= "        and landusetype in (2, 3, 9, 13, 14) ";
   $listobject->querystring .= "    ) as b ";
   $listobject->querystring .= " ) as b  ";
   $listobject->querystring .= " where d.projectid = $projectid  ";
   $listobject->querystring .= "    and a.typeid = d.typeid  ";
   $listobject->querystring .= "    and b.bmpname = d.bmp_name  ";
   $listobject->querystring .= "    and b.pollutantid = a.pollutantid ";
   $listobject->querystring .= "    and b.pollutantid = c.typeid ";

   # add in minimum values for regionally varying bmps for any bmps with a stateseg = -1 (unknown or unassigned)
   # do not use values for stateseg 8900 since that is the segment describing open water
   $listobject->querystring .= " ) UNION (";
   $listobject->querystring .= " select $projectid, b.stseg, b.luname, b.bmpname, ";
   $listobject->querystring .= " b.efficiency, a.affected_area, c.vortexname, c.typeid ";
   $listobject->querystring .= " FROM pollutanttype as c, bmp_types as d,  ";
   $listobject->querystring .= " (  ";
   $listobject->querystring .= "    ( select typeid, 1 as pollutantid, max(naffect_area) as affected_area  ";
   $listobject->querystring .= "      from bmp_subtypes as a  ";
   $listobject->querystring .= "      where projectid = $projectid  ";
   $listobject->querystring .= "      group by typeid ";
   $listobject->querystring .= "    )  ";
   $listobject->querystring .= "    UNION  ";
   $listobject->querystring .= "    ( select typeid, 2 as pollutantid, max(paffect_area) as affected_area  ";
   $listobject->querystring .= "      from bmp_subtypes as a  ";
   $listobject->querystring .= "      where projectid = $projectid ";
   $listobject->querystring .= "      group by typeid ";
   $listobject->querystring .= "    )  ";
   $listobject->querystring .= "    UNION  ";
   $listobject->querystring .= "    ( select typeid, 8 as pollutantid, max(saffect_area) as affected_area  ";
   $listobject->querystring .= "      from bmp_subtypes as a  ";
   $listobject->querystring .= "      where projectid = $projectid ";
   $listobject->querystring .= "      group by typeid ";
   $listobject->querystring .= "    )  ";
   $listobject->querystring .= " ) as a, ";
   $listobject->querystring .= " (select a.stseg, a.bmpname, a.pollutantid, a.efftype, a.efficiency, ";
   $listobject->querystring .= "    b.luname from ";
   $listobject->querystring .= "    ( ";
   # get forest buffer
   $listobject->querystring .= "       ( select '-1' as stseg, 'fb' as bmpname, 1 as pollutantid, 1 as efftype, ";
   $listobject->querystring .= "            min(a.fb_tn) as efficiency ";
   $listobject->querystring .= "         from buffer_effic as a  ";
   $listobject->querystring .= "         where geo_abbrev <> 'h2o' ";
   $listobject->querystring .= "       ) ";
   $listobject->querystring .= "       UNION ";
   $listobject->querystring .= "       ( select '-1' as stseg, 'fb' as bmpname, 2 as pollutantid, 1 as efftype, ";
   $listobject->querystring .= "       min(a.fb_tp) as efficiency ";
   $listobject->querystring .= "         from buffer_effic as a  ";
   $listobject->querystring .= "         where geo_abbrev <> 'h2o' ";
   $listobject->querystring .= "       ) ";
   $listobject->querystring .= "       UNION ";
   $listobject->querystring .= "       ( select '-1' as stseg, 'fb' as bmpname, 8 as pollutantid, 1 as efftype, ";
   $listobject->querystring .= "       min(a.fb_tss) as efficiency ";
   $listobject->querystring .= "         from buffer_effic as a  ";
   $listobject->querystring .= "         where geo_abbrev <> 'h2o' ";
   $listobject->querystring .= "       ) ";
   $listobject->querystring .= "       UNION ";
   # get grass buffer
   $listobject->querystring .= "       ( select '-1' as stseg, 'gb' as bmpname, 1 as pollutantid, 1 as efftype, ";
   $listobject->querystring .= "       min(a.gb_tn) as efficiency ";
   $listobject->querystring .= "         from buffer_effic as a  ";
   $listobject->querystring .= "         where geo_abbrev <> 'h2o' ";
   $listobject->querystring .= "       ) ";
   $listobject->querystring .= "       UNION ";
   $listobject->querystring .= "       ( select '-1' as stseg, 'gb' as bmpname, 2 as pollutantid, 1 as efftype, ";
   $listobject->querystring .= "       min(a.gb_tp) as efficiency ";
   $listobject->querystring .= "         from buffer_effic as a  ";
   $listobject->querystring .= "         where geo_abbrev <> 'h2o' ";
   $listobject->querystring .= "       ) ";
   $listobject->querystring .= "       UNION ";
   $listobject->querystring .= "       ( select '-1' as stseg, 'gb' as bmpname, 8 as pollutantid, 1 as efftype, ";
   $listobject->querystring .= "       min(a.gb_tss) as efficiency ";
   $listobject->querystring .= "         from buffer_effic as a  ";
   $listobject->querystring .= "         where geo_abbrev <> 'h2o' ";
   $listobject->querystring .= "       ) ";
   $listobject->querystring .= "    ) as a, ";
   $listobject->querystring .= "    (select hspflu as luname from landuses ";
   $listobject->querystring .= "          where projectid = $projectid ";
   $listobject->querystring .= "        and landusetype in (2, 3, 9, 13, 14) ";
   $listobject->querystring .= "    ) as b ";
   $listobject->querystring .= " ) as b  ";
   $listobject->querystring .= " where d.projectid = $projectid  ";
   $listobject->querystring .= "    and a.typeid = d.typeid  ";
   $listobject->querystring .= "    and b.bmpname = d.bmp_name  ";
   $listobject->querystring .= "    and b.pollutantid = a.pollutantid ";
   $listobject->querystring .= "    and b.pollutantid = c.typeid ";
   $listobject->querystring .= " ) ";
   if ($debug) { print("$listobject->querystring ;<br>"); }
   $listobject->performQuery();

}


function distributeBMP($listobject, $bmpname, $subsheds, $thisyear, $bmparea, $scenarioid, $projectid, $debug) {

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $bmpsubcond = " (riverseg || landseg) in ( $sslist) ";
      $lusubcond = " c.lrseg in ($sslist) ";
   } else {
      $bmpsubcond = ' 1 = 1 ';
      $lusubcond = ' 1 = 1 ';
   }

   $listobject->queryrecords = array();

   # delete the old bmp results for this bmp
   $listobject->querystring = " delete from scen_lrseg_bmps ";
   $listobject->querystring .= " where scenarioid = $scenarioid ";
   $listobject->querystring .= "    and thisyear = $thisyear ";
   $listobject->querystring .= "    and bmpname = '$bmpname' ";
   $listobject->querystring .= "    and $bmpsubcond ";
   $listobject->performQuery();


   # insert new bmp results

   $listobject->querystring = " insert into scen_lrseg_bmps (scenarioid, landseg, ";
   $listobject->querystring .= "    riverseg, lrseg, thisyear, bmpname, bmparea ) ";
   $listobject->querystring .= " select $scenarioid, b.landseg, b.riverseg, ";
   $listobject->querystring .= "    b.lrseg, $thisyear as thisyear, b.bmpname, ";
   $listobject->querystring .= " CASE  ";
   $listobject->querystring .= "    WHEN a.totalarea <= 0.0 then 0.0 ";
   $listobject->querystring .= "    ELSE $bmparea * b.eligarea / a.totalarea  ";
   $listobject->querystring .= " END as bmparea ";
   # this gets each eligible lrsegs area
   $listobject->querystring .= " from ( ";
   $listobject->querystring .= "   select b.bmpname, c.landseg, c.riverseg, c.lrseg, ";
   $listobject->querystring .= "      sum(c.luarea) as eligarea ";
   $listobject->querystring .= "   from bmp_subtypes as b, scen_lrsegs as c, map_landuse_bmp as d ";
   $listobject->querystring .= "   where b.projectid = $projectid ";
   $listobject->querystring .= "      and d.projectid = $projectid ";
   $listobject->querystring .= "      and c.scenarioid = $scenarioid ";
   $listobject->querystring .= "      and $lusubcond ";
   $listobject->querystring .= "      and c.thisyear = $thisyear ";
   $listobject->querystring .= "      and b.bmpname = '$bmpname' ";
   $listobject->querystring .= "      and c.luname = d.luname ";
   $listobject->querystring .= "      and b.bmpname = d.bmpname ";
   $listobject->querystring .= "   group by c.landseg, c.riverseg, c.lrseg, b.bmpname ";
   $listobject->querystring .= " ) as b left join ";
   # this gets each eligible lrsegs area
   $listobject->querystring .= " ( select b.bmpname, sum(c.luarea) as totalarea  ";
   $listobject->querystring .= "   from bmp_subtypes as b, scen_lrsegs as c, map_landuse_bmp as d ";
   $listobject->querystring .= "   where b.projectid = $projectid ";
   $listobject->querystring .= "      and d.projectid = $projectid ";
   $listobject->querystring .= "      and c.scenarioid = $scenarioid ";
   $listobject->querystring .= "      and $lusubcond ";
   $listobject->querystring .= "      and c.thisyear = $thisyear ";
   $listobject->querystring .= "      and b.bmpname = '$bmpname' ";
   $listobject->querystring .= "      and c.luname = d.luname ";
   $listobject->querystring .= "      and b.bmpname = d.bmpname ";
   $listobject->querystring .= "   group by b.bmpname ";
   $listobject->querystring .= " ) as a ";
   $listobject->querystring .= " on (a.bmpname = b.bmpname) ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

}


function DistributeMultiSegBMPS($listobject, $scenarioid, $thisyear, $bmpid, $debug) {

   # this does the raw distribution of multi-segment bmps, not ready to go yet,
   # since many of the table names are still in the MS SQL Server format
   # bmpid value of -1 means do all
   $listobject->querystring = "delete from wrk_histbmps ";
   $listobject->querystring .= "where scenarioid = $scenarioid ";
   $listobject->querystring .= "   and thisyear = $thisyear ";
   $listobject->querystring .= "   and ( (bmpid = $bmpid) or ($bmpid = -1) ) ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

   $listobject->querystring = "insert into wrk_histbmps ( thisyear, bmpid, bmpacres, msid, scenarioid ) ";
   $listobject->querystring .= "   select a.thisyear, c.bmpid, a.bmpacres, b.msid, $scenarioid ";
   $listobject->querystring .= "   from WRK_MULTISEG_BMPS as a, WRK_MULTISEG as b, wrk_bmp_subtypes as c ";
   $listobject->querystring .= "   where a.coseg = b.coseg ";
   $listobject->querystring .= "      and a.stseg = b.stseg ";
   $listobject->querystring .= "      and a.thisyear = $thisyear ";
   $listobject->querystring .= "      and a.seg = b.seg ";
   $listobject->querystring .= "      and a.cofips = b.cofips ";
   $listobject->querystring .= "      and a.stabbrev = b.stabbrev ";
   $listobject->querystring .= "      and a.fipsab = b.fipsab ";
   $listobject->querystring .= "      and a.catcode2 = b.catcode2 ";
   $listobject->querystring .= "      and a.huc = b.huc ";
   $listobject->querystring .= "      and a.bmpname = c.bmpname ";
   $listobject->querystring .= "      and ( (c.bmpid = $bmpid) or ($bmpid = -1) ) ";
   $listobject->querystring .= "      and a.bmpacres > 0 ";
   $listobject->querystring .= "      and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "      and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "      and c.scenarioid = $scenarioid ";
   $listobject->querystring .= "   order by a.thisyear, c.bmpid ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

   $listobject->querystring = "delete from WRK_LANDSEGELIG where scenarioid = $scenarioid ";
   $listobject->querystring .= "   and thisyear = $thisyear ";
   $listobject->querystring .= "   and ( (bmpid = $bmpid) or ($bmpid = -1) ) ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

   $listobject->querystring = "insert into WRK_LANDSEGELIG (scenarioid, subshedid, thisyear, bmpid, elig_area) ";
   $listobject->querystring .= "   select $scenarioid, a.co_seg, a.thisyear, ";
   $listobject->querystring .= "      c.bmpid, sum(a.acres) as elig_area ";
   $listobject->querystring .= "   from ";
   $listobject->querystring .= "      (select a.co_seg, a.thisyear, b.landuse as luname, a.acres ";
   $listobject->querystring .= "         from WRK_LANDUSE as a, TAB_LANDUSE as b, TAB_SCENARIO as c ";
   $listobject->querystring .= "         where a.scenarioid =$scenarioid ";
   $listobject->querystring .= "            and a.landuse_id = b.landuse_id ";
   $listobject->querystring .= "            and c.scenarioid =$scenarioid ";
   $listobject->querystring .= "            and a.thisyear =$thisyear ";
   $listobject->querystring .= "      ) as a, ";
   $listobject->querystring .= "      (select bmpname, luname from tab_map_landuse_bmp ";
   $listobject->querystring .= "         where scenarioid =$scenarioid ";
   $listobject->querystring .= "      ) as b, ";
   $listobject->querystring .= "      WRK_bmp_subtypes as c ";
   $listobject->querystring .= "   where a.luname = b.luname ";
   $listobject->querystring .= "   and b.bmpname = c.bmpname ";
   $listobject->querystring .= "   and c.scenarioid = $scenarioid ";
   $listobject->querystring .= "   and ( (c.bmpid = $bmpid) or ($bmpid = -1) ) ";
   $listobject->querystring .= "   group by a.thisyear, a.co_seg, c.bmpid ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();


   $listobject->querystring = "delete from wrk_overlap_eligible ";
   $listobject->querystring .= "where scenarioid = $scenarioid ";
   $listobject->querystring .= "   and thisyear = $thisyear ";
   $listobject->querystring .= "      and ( (bmpid = $bmpid) or ($bmpid = -1) ) ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();


   $listobject->querystring = "insert into wrk_overlap_eligible (histid, msid, subshedid, bmpid, thisyear, ";
   $listobject->querystring .= "   bmpacres, elig_area, pct_cont, ms_pct, bmpname, scenarioid) ";
   $listobject->querystring .= "   select a.histid, a.msid, b.subshedid, b.bmpid, b.thisyear, a.bmpacres, ";
   $listobject->querystring .= "      (b.elig_area * e.pct_cont) as elig_area , e.pct_cont, e.pct_cov, c.bmpname, $scenarioid ";
   $listobject->querystring .= "   from wrk_histbmps as a, WRK_LANDSEGELIG as b, wrk_bmp_subtypes as c, wrk_spatial_overlap as e ";
   $listobject->querystring .= "   where a.thisyear = $thisyear ";
   $listobject->querystring .= "      and b.thisyear = $thisyear ";
   $listobject->querystring .= "      and e.msid = a.msid ";
   $listobject->querystring .= "      and e.lrseg = b.subshedid ";
   $listobject->querystring .= "      and e.pct_cont > 0.0 ";
   $listobject->querystring .= "      and b.elig_area > 0.0 ";
   $listobject->querystring .= "      and a.bmpid = c.bmpid ";
   $listobject->querystring .= "      and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "      and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "      and c.scenarioid = $scenarioid ";
   $listobject->querystring .= "      and e.scenarioid = $scenarioid ";
   $listobject->querystring .= "      and a.bmpid = b.bmpid ";
   $listobject->querystring .= "      and ( (c.bmpid = $bmpid) or ($bmpid = -1) ) ";
   $listobject->querystring .= "   order by b.thisyear, b.bmpid ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();


   $listobject->querystring = "delete from wrk_multiseg_eligible ";
   $listobject->querystring .= "where scenarioid = $scenarioid ";
   $listobject->querystring .= "   and thisyear = $thisyear ";
   $listobject->querystring .= "      and ( (bmpid = $bmpid) or ($bmpid = -1) ) ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

   $listobject->querystring = "insert into wrk_multiseg_eligible ( histid, msid, bmpid, thisyear,  ";
   $listobject->querystring .= "   bmp_acres, elig_area, pct_cov, scenarioid ) ";
   $listobject->querystring .= "select histid, msid, bmpid, thisyear, bmpacres as bmp_acres, ";
   $listobject->querystring .= "   sum(elig_area) as elig_area, sum(ms_pct) as pct_cov, $scenarioid ";
   $listobject->querystring .= "from wrk_overlap_eligible ";
   $listobject->querystring .= "where scenarioid = $scenarioid ";
   $listobject->querystring .= "   and thisyear = $thisyear ";
   $listobject->querystring .= "   and ( (bmpid = $bmpid) or ($bmpid = -1) ) ";
   $listobject->querystring .= "group by msid, histid, bmpid, bmpacres, thisyear ";
   $listobject->querystring .= "order by thisyear, bmpid ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

   $listobject->querystring = "delete from wrk_distributed_bmps ";
   $listobject->querystring .= "where scenarioid = $scenarioid ";
   $listobject->querystring .= "   AND thisyear = $thisyear ";
   $listobject->querystring .= "   AND ( (bmpid = $bmpid) or ($bmpid = -1) ) ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

   $listobject->querystring = "insert into wrk_distributed_bmps (subshedid, bmpid, thisyear, bmp_area,  ";
   $listobject->querystring .= "   bmp_avail, bmp_pct, elig_area, total_bmp, scenarioid) ";
   $listobject->querystring .= "select b.subshedid, b.bmpid, b.thisyear,  ";
   $listobject->querystring .= "   sum ( ( a.pct_cov * a.bmp_acres  * b.elig_area / a.elig_area ) ) as bmp_area, ";
   $listobject->querystring .= "   convert(float,0.0) as bmp_avail, 0.0, ";
   $listobject->querystring .= "   sum(b.elig_area) as elig_area, max(a.bmp_acres) as total_bmp, $scenarioid ";
   $listobject->querystring .= "from wrk_multiseg_eligible as a, wrk_overlap_eligible as b ";
   $listobject->querystring .= "where a.thisyear = $thisyear ";
   $listobject->querystring .= "   and a.bmpid = b.bmpid ";
   $listobject->querystring .= "   and ( (a.bmpid = $bmpid) or ($bmpid = -1) ) ";
   $listobject->querystring .= "   and a.msid = b.msid ";
   $listobject->querystring .= "   and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "   and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "   and b.thisyear = $thisyear ";
   $listobject->querystring .= "group by b.subshedid, b.bmpid, b.thisyear ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

   # now just clean up some odds and ends
   $listobject->querystring = "update wrk_distributed_bmps set bmp_area = 0.0 ";
   $listobject->querystring .= "where bmp_area < 0.001 ";
   $listobject->querystring .= "   and scenarioid = $scenarioid ";
   $listobject->querystring .= "   and thisyear = $thisyear ";
   $listobject->querystring .= "   and ( (bmpid = $bmpid) or ($bmpid = -1) ) ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

   $listobject->querystring = "update wrk_distributed_bmps set bmpname = a.bmpname ";
   $listobject->querystring .= "from wrk_bmp_subtypes as a ";
   $listobject->querystring .= "where a.bmpid = wrk_distributed_bmps.bmpid ";
   $listobject->querystring .= "   and ( (a.bmpid = $bmpid) or ($bmpid = -1) ) ";
   $listobject->querystring .= "   and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "   and wrk_distributed_bmps.thisyear = $thisyear ";
   $listobject->querystring .= "   and wrk_distributed_bmps.scenarioid = $scenarioid ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

   $listobject->querystring = "update wrk_distributed_bmps set bmp_avail = (elig_area - bmp_area) ";
   $listobject->querystring .= "where scenarioid = $scenarioid ";
   $listobject->querystring .= "   and thisyear = $thisyear ";
   $listobject->querystring .= "   and ( (bmpid = $bmpid) or ($bmpid = -1) ) ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

   $listobject->querystring = "update wrk_distributed_bmps set bmp_pct = (bmp_area / elig_area) ";
   $listobject->querystring .= "where elig_area > 0 ";
   $listobject->querystring .= "   and scenarioid = $scenarioid ";
   $listobject->querystring .= "   and thisyear = $thisyear ";
   $listobject->querystring .= "   and ( (bmpid = $bmpid) or ($bmpid = -1) ) ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

   #now copy these to the NOLU table
   $listobject->querystring = "DELETE from WRK_SUBSHED_BMP_NOLU ";
   $listobject->querystring .= "WHERE scenarioid = $scenarioid ";
   $listobject->querystring .= "   and thisyear = $thisyear ";
   $listobject->querystring .= "   and bmpname in ";
   $listobject->querystring .= "      (SELECT bmpname ";
   $listobject->querystring .= "       FROM wrk_bmp_subtypes ";
   $listobject->querystring .= "       WHERE ( (bmpid = $bmpid) or ($bmpid = -1) ) ";
   $listobject->querystring .= "          AND scenarioid = $scenarioid ";
   $listobject->querystring .= "      ) ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();


   $listobject->querystring = "INSERT INTO WRK_SUBSHED_BMP_NOLU (scenarioid, thisyear, subshedid, bmpname, bmparea) ";
   $listobject->querystring .= "select scenarioid, thisyear, subshedid, bmpname, bmp_area ";
   $listobject->querystring .= "FROM wrk_distributed_bmps ";
   $listobject->querystring .= "WHERE scenarioid = $scenarioid ";
   $listobject->querystring .= "   and ( (bmpid = $bmpid) or ($bmpid = -1) ) ";
   $listobject->querystring .= "   and thisyear = $thisyear ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

}


function deleteBMPType($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $debug) {

   /*
   # emulates vortex proc: usp_BMPdistributeSubTypes
   */
   # disperses BMPs imported at subshed scale without specific land use recipient,
   # to be distributed across all possible landuses according to eligible area
   # these are "subtype" bmps, which have a more explicit mapped land use relationship,
   # but these are mapped to the more general BMP categories
   # where the efficiencies are established.

   # $typeid - allows us to choose a specific type. If it is '' or -1, we will assume all
   if (!(strlen($typeid) > 0)) {
      $typeid = -1;
   }

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subcond = " lrseg in ($sslist) ";
   } else {
      $subcond = ' 1 = 1 ';
   }

   $listobject->querystring = "DELETE FROM scen_bmp_data ";
   $listobject->querystring .= " WHERE $subcond ";
   $listobject->querystring .= " and ( (typeid = $typeid) or ($typeid = -1) ) ";
   $listobject->querystring .= " and scenarioid = $scenarioid ";
   $listobject->querystring .= " and thisyear = $thisyear ";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();

}

function distributeBMPsToLU($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $debug) {

   /*
   # emulates vortex proc: usp_BMPdistributeSubTypes
   */
   # disperses BMPs imported at subshed scale without specific land use recipient,
   # to be distributed across all possible landuses according to eligible area
   # these are "subtype" bmps, which have a more explicit mapped land use relationship,
   # but these are mapped to the more general BMP categories
   # where the efficiencies are established.
   # This routine processes these sub-type BMPs in order of the number of affected land uses,
   # therefore, the most explicit bmp/lu relationships will be processed first, insuring
   # maximum implementation of submitted BMPs in and LRSEG.

   # test for temp tables
   #if (!($listobject->tableExists('tmp_lrsegs'))) {
   #   tempBMPTables($listobject, $typeid, $subsheds, $scenarioid, $thisyear, $debug);
   #}

   # $typeid - allows us to choose a specific type. If it is '' or -1, we will assume all
   if (!(strlen($typeid) > 0)) {
      $typeid = -1;
   }

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $lrcond = " lrseg in ($sslist) ";
      $alrcond = " a.lrseg in ($sslist) ";
      $blrcond = " b.lrseg in ($sslist) ";
   } else {
      $lrcond = ' (1 = 1) ';
      $alrcond = ' (1 = 1) ';
      $blrcond = ' (1 = 1) ';
   }


   $theseyears = split(',', $thisyear);

   foreach($theseyears as $thisyear) {
      # delete any existing BMPs
      deleteBMPType($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $debug);

      $listobject->querystring = "select bmpname, typeid, numlus ";
      $listobject->querystring .= " from ";
      $listobject->querystring .= "    (select a.bmpname, a.typeid, count(b.*) as numlus";
      $listobject->querystring .= "     FROM bmp_subtypes as a, map_landuse_bmp as b ";
      $listobject->querystring .= "     where a.projectid = $projectid ";
      $listobject->querystring .= "        AND a.typeid = $typeid ";
      $listobject->querystring .= "        AND b.projectid = $projectid ";
      $listobject->querystring .= "        AND a.bmpname = b.bmpname ";
      # $typeid - allows us to choose a specific type. If it is -1, we will assume all
      $listobject->querystring .= "        AND ( (a.typeid = $typeid) or ($typeid = -1 ) )";
      $listobject->querystring .= "     GROUP BY a.bmpname, a.typeid ) as foo ";
      $listobject->querystring .= " ORDER BY typeid, numlus, bmpname ";

      if ($debug) {
         $listobject->startSplit();
         print("<br>$listobject->querystring ;<br>");
      }
      $listobject->performQuery();
      $sts = $listobject->queryrecords;

      if ($debug) {
         $split = $listobject->startSplit();
         print("Query Time: $split<br>");
      }

      foreach ($sts as $thisst) {
         $stname = $thisst['bmpname'];
         # loops from highly specified BMPS (i.e., bmp specifies only 1 or 2 land uses) to more general
         $insertq = "INSERT INTO scen_bmp_data (scenarioid, thisyear, lrseg, landseg, riverseg, ";
         $insertq .= "   subshedid, luname, ";
         $insertq .= "   typeid, bmpname, value_submitted, value_implemented ) ";

         $insertq .= " SELECT $scenarioid, $thisyear, a.lrseg, a.landseg, a.riverseg, a.subshedid, a.luname, ";
         # merges sub-types into overall type
         #$insertq .= "   a.typeid, a.bmp_name, sum(a.value_submitted ), 0.0 ";
         # retains sub-type information, but still keyed on typeid which links to overall type
         $insertq .= "   a.typeid, a.bmpname, sum(a.value_submitted ), 0.0 ";
         $insertq .= " FROM ( ";


         $dataq = " SELECT a.lrseg, a.landseg, a.riverseg, a.subshedid, a.luname, b.bmpname, ";
         $dataq .= "      b.bmp_name, b.typeid, sum( a.eligarea ) as luremains, ";
         $dataq .= "      sum(c.elig_area) as subshedremains, sum(b.bmparea) as bmparea, ";
         # ( LU-BMP-AREA-ELIGIBLE / BMP-GROUP-AREA-ELIGIBLE ) * (THIS-BMP-AREA)
         $dataq .= "      sum( (a.eligarea / c.elig_area) * b.bmparea) as value_submitted ";
         $dataq .= "   FROM  ";
         # sub-query A
         #-- subshed/lu/area eligible area
         $dataq .= "      (select a.lrseg, a.landseg, a.riverseg, a.subshedid, a.luname, ";
         $dataq .= "          CASE ";
         $dataq .= "             WHEN a.luarea > b.grouptotal THEN (a.luarea - b.grouptotal) ";
         $dataq .= "             WHEN b.grouptotal is NULL THEN a.luarea ";
         $dataq .= "             ELSE 0.0 ";
         $dataq .= "          END as eligarea";
         $dataq .= "       FROM scen_lrsegs as a left outer join ";
         $dataq .= "          (";
         $dataq .= "             select lrseg, luname, sum(value_submitted) as grouptotal ";
         $dataq .= "             from scen_bmp_data as b ";
         $dataq .= "             WHERE b.scenarioid = $scenarioid ";
         $dataq .= "             and $blrcond ";
         $dataq .= "             and b.typeid = $typeid ";
         $dataq .= "             and b.thisyear = $thisyear ";
         $dataq .= "           group by lrseg, luname ";
         $dataq .= "           ) as b ";
         $dataq .= "       ON a.luname = b.luname ";
         $dataq .= "          and a.lrseg = b.lrseg ";
         $dataq .= "       WHERE a.thisyear = $thisyear ";
         $dataq .= "          and a.scenarioid = $scenarioid ";
         $dataq .= "          and $alrcond ";
         # screen for land uses to speed up the query
         $dataq .= "          and a.luname in (select a.luname ";
         $dataq .= "                           from map_landuse_bmp as a, bmp_subtypes as c ";
         $dataq .= "                           where a.bmpname = c.bmpname ";
         $dataq .= "                              and c.typeid = $typeid ";
         $dataq .= "                              and c.projectid = $projectid ";
         $dataq .= "                              and a.projectid = $projectid ";
         $dataq .= "                           group by a.luname ";
         $dataq .= "                          ) ";
         $dataq .= "       group by a.lrseg, a.landseg, a.riverseg, a.subshedid,  ";
         $dataq .= "          a.luname, a.luarea, b.grouptotal ";
         $dataq .= "      ) as a,     ";

         # sub-query B
         #-- submitted BMPs by subtype, subshed
         # we convert any input units into the proper bmp units here, such as the conversion from
         # feet of riparian buffer to acres of riparian buffer
         $dataq .= "      (select a.lrseg, a.bmpname, c.bmp_name, c.typeid, (b.conversion * a.bmparea) as bmparea ";
         $dataq .= "       FROM scen_lrseg_bmps as a, bmp_subtypes as b, bmp_types as c ";
         $dataq .= "       WHERE a.thisyear = $thisyear ";
         $dataq .= "          and $alrcond ";
         $dataq .= "          and a.scenarioid = $scenarioid ";
         $dataq .= "          and b.typeid = c.typeid ";
         # screen for single sub-type bmp
         $dataq .= "          and b.bmpname = '$stname' ";
         $dataq .= "          and b.projectid = $projectid ";
         $dataq .= "          and c.projectid = $projectid ";
         $dataq .= "          and a.bmpname = '$stname' ";
         # added hoping to speed things up a bit
         $dataq .= "          and a.bmparea > 0 ";
         $dataq .= "      ) as b, ";
         # sub-query C
         #-- eligible lands by subtype - (i.e. those not already occuppied)
         $dataq .= "      ( select a.lrseg, a.typeid, a.bmpname, ";
         $dataq .= "           CASE ";
         $dataq .= "              WHEN b.bmparea is null THEN a.elig_area ";
         $dataq .= "              WHEN (a.elig_area - b.bmparea) > 0 THEN (a.elig_area - b.bmparea)";
         $dataq .= "              ELSE 0.0 ";
         $dataq .= "           END as elig_area ";
         $dataq .= "        FROM ";
         $dataq .= "         ( select a.lrseg, b.typeid, b.bmpname, sum(a.luarea) as elig_area ";
         $dataq .= "           from scen_lrsegs as a, bmp_subtypes as b, ";
         $dataq .= "              map_landuse_bmp as d ";
         $dataq .= "           where d.luname = a.luname ";
         # added to remove this criteria from the bottom where clause - will it speed things up?
         $dataq .= "              and a.luarea > 0 ";
         $dataq .= "              and a.thisyear = $thisyear ";
         $dataq .= "              and a.scenarioid = $scenarioid ";
         $dataq .= "              and $alrcond ";
         $dataq .= "              and d.bmpname = '$stname' ";
         $dataq .= "              and b.bmpname = '$stname' ";
         $dataq .= "              and b.projectid = $projectid ";
         $dataq .= "              and d.projectid = $projectid ";
         # screen for single sub-type bmp
         # this looks for ALL BMPs of this typeid, but ONLY the Land Uses associated with the current sub-type
         $dataq .= "              and b.bmpname = '$stname' ";
         $dataq .= "           group by a.lrseg, b.typeid, b.bmpname ";
         $dataq .= "         ) as a left outer join ";
         $dataq .= "         ( select a.lrseg, sum(a.value_submitted) as bmparea ";
         $dataq .= "           from scen_bmp_data as a, map_landuse_bmp as d ";
         $dataq .= "           where d.luname = a.luname ";
         $dataq .= "              and a.scenarioid = $scenarioid ";
         $dataq .= "              and a.thisyear = $thisyear ";
         $dataq .= "              and $alrcond ";
         # added to remove this criteria from the bottom where clause - will it speed things up?
         $dataq .= "              and a.value_submitted > 0 ";
         $dataq .= "              and a.typeid = $typeid ";
         $dataq .= "              and d.projectid = $projectid ";
         # screen for single sub-type bmp land use
         $dataq .= "              and d.bmpname = '$stname' ";
         $dataq .= "           group by a.lrseg ";
         $dataq .= "         ) as b ";
         $dataq .= "         ON (a.lrseg = b.lrseg) ";
         $dataq .= "      ) as c, ";
         # sub-query D
         #-- map landuses to bmp subtypes
         $dataq .= "      (select bmpname, luname  ";
         $dataq .= "       FROM map_landuse_bmp ";
         $dataq .= "       WHERE projectid = $projectid ";
         $dataq .= "          AND bmpname = '$stname' ";
         $dataq .= "      ) as d ";
         $dataq .= "   WHERE a.lrseg = b.lrseg ";
         $dataq .= "      and a.lrseg = c.lrseg ";
         $dataq .= "      and a.luname = d.luname ";
         $dataq .= "   GROUP BY a.lrseg, a.landseg, a.riverseg, a.subshedid, a.luname, ";
         $dataq .= "      b.bmpname, b.bmp_name, b.typeid ";

         $insertq .= $dataq;
         $insertq .= ") as a ";
         # retains sub-type information, but still keyed on typeid which links to overall type
         $insertq .= "GROUP BY a.lrseg, a.landseg, a.riverseg, a.subshedid, a.luname, a.typeid, a.bmpname";

         if ($debug) {
            $listobject->querystring = $dataq;
            print("<br>$listobject->querystring ;<br>");
           # $listobject->performQuery();
           # $listobject->showList();
         }

         $listobject->querystring = $insertq;

         if ($debug) {
            $listobject->startSplit();
            print("<br>$listobject->querystring ;<br>");
         }
         $listobject->performQuery();

         if ($debug) {
            $split = $listobject->startSplit();
            print("Query Time: $split<br>");
         }
      }
   }

}


function distributeBMPsToLU2($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $debug) {

   /*
   # emulates vortex proc: usp_BMPdistributeSubTypes
   */
   # disperses BMPs imported at subshed scale without specific land use recipient,
   # to be distributed across all possible landuses according to eligible area
   # these are "subtype" bmps, which have a more explicit mapped land use relationship,
   # but these are mapped to the more general BMP categories
   # where the efficiencies are established.
   # This routine processes these sub-type BMPs in order of the number of affected land uses,
   # therefore, the most explicit bmp/lu relationships will be processed first, insuring
   # maximum implementation of submitted BMPs in and LRSEG.

   # test for temp tables
   #if (!($listobject->tableExists('tmp_lrsegs'))) {
   #   tempBMPTables($listobject, $typeid, $subsheds, $scenarioid, $thisyear, $debug);
   #}

   # $typeid - allows us to choose a specific type. If it is '' or -1, we will assume all
   if (!(strlen($typeid) > 0)) {
      $typeid = -1;
   }

   # need to consider all BMPs in this group, not just this type for calculating remaining area

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $lrcond = " lrseg in ($sslist) ";
      $alrcond = " a.lrseg in ($sslist) ";
      $blrcond = " b.lrseg in ($sslist) ";
   } else {
      $lrcond = ' (1 = 1) ';
      $alrcond = ' (1 = 1) ';
      $blrcond = ' (1 = 1) ';
   }


   $theseyears = split(',', $thisyear);

   foreach($theseyears as $thisyear) {
      # delete any existing BMPs
      deleteBMPType($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $debug);

      $listobject->querystring = "select bmpname, typeid, numlus, bmp_group ";
      $listobject->querystring .= " from ";
      $listobject->querystring .= "    (select a.bmpname, a.typeid, c.bmp_group, count(b.*) as numlus";
      $listobject->querystring .= "     FROM bmp_subtypes as a, map_landuse_bmp as b, proj_bmp_order as c ";
      $listobject->querystring .= "     where a.projectid = $projectid ";
      $listobject->querystring .= "        AND a.typeid = $typeid ";
      $listobject->querystring .= "        AND b.projectid = $projectid ";
      $listobject->querystring .= "        AND c.projectid = $projectid ";
      $listobject->querystring .= "        AND a.bmpname = b.bmpname ";
      $listobject->querystring .= "        AND a.typeid = c.typeid ";
      # $typeid - allows us to choose a specific type. If it is -1, we will assume all
      $listobject->querystring .= "        AND ( (a.typeid = $typeid) or ($typeid = -1 ) )";
      $listobject->querystring .= "     GROUP BY a.bmpname, a.typeid, c.bmp_group ) as foo ";
      $listobject->querystring .= " ORDER BY typeid, numlus, bmpname ";

      if ($debug) {
         $listobject->startSplit();
         print("<br>$listobject->querystring ;<br>");
      }
      $listobject->performQuery();
      $sts = $listobject->queryrecords;

      if ($debug) {
         $split = $listobject->startSplit();
         print("Query Time: $split<br>");
      }

      foreach ($sts as $thisst) {
         $bmpname = $thisst['bmpname'];
         $bmp_group = $thisst['bmp_group'];
         # loops from highly specified BMPS (i.e., bmp specifies only 1 or 2 land uses) to more general

         $headq = "INSERT INTO scen_bmp_data (scenarioid, thisyear, lrseg, landseg, riverseg, ";
         $headq .= "   subshedid, luname, ";
         $headq .= "   typeid, bmpname, value_submitted, value_implemented ) ";

         $selq = " SELECT $scenarioid as scenarioid, $thisyear as thisyear, a.lrseg, ";
         $selq .= "   a.landseg, a.riverseg, a.subshedid, a.luname, ";
         # retains sub-type information, but still keyed on typeid which links to overall type
         $selq .= "   a.typeid, a.bmpname, ";
         $selq .= "   ( (a.lu_total_eligible - a.lu_occupied) / (a.total_eligible - a.occupied) ) * a.bmparea as bmpsubmit, ";
         # blank value for implemented, that is taken care of at next step
         $selq .= "   0.0 as val_imp ";
         $selq .= " FROM ( ";

         $insertq = " SELECT e.lrseg, c.landseg, c.riverseg, c.subshedid, e.luname, a.bmpname, ";
         $insertq .= "      a.bmp_name, a.typeid,  ";
         $insertq .= "      a.bmparea,  ";
         $insertq .= "      CASE ";
         $insertq .= "         WHEN b.occupied IS NULL then 0.0 ";
         $insertq .= "         ELSE b.occupied ";
         $insertq .= "      END AS occupied,  ";
         $insertq .= "      c.total_eligible,  ";
         $insertq .= "      CASE ";
         $insertq .= "         WHEN d.lu_occupied IS NULL then 0.0 ";
         $insertq .= "         ELSE d.lu_occupied ";
         $insertq .= "      END AS lu_occupied,  ";
         $insertq .= "      e.lu_total_eligible ";
         $insertq .= "   FROM  ";

         ################################
         # START - SUB-QUERY E
         #    the land-uses and area eligible for this specific BMP
         #      - grouped by landuse
         ################################
         $insertq .= "      (select lrseg, luname, SUM(luarea) as lu_total_eligible ";
         $insertq .= "       FROM scen_lrsegs ";
         $insertq .= "       WHERE thisyear = $thisyear ";
         $insertq .= "          and $lrcond ";
         $insertq .= "          and scenarioid = $scenarioid ";
         $insertq .= "          and luname in (SELECT luname ";
         $insertq .= "                         FROM map_landuse_bmp ";
         $insertq .= "                         WHERE projectid = $projectid ";
         $insertq .= "                            AND bmpname = '$bmpname' ";
         $insertq .= "                         ) ";
         $insertq .= "       GROUP BY lrseg, luname ";
         $insertq .= "      ) as e left outer join ";
         ################################
         # END - SUB-QUERY E
         ################################

         ################################
         # START - SUB-QUERY A
         #    the bmps being submitted
         ################################
         $insertq .= "      (select a.lrseg, a.bmpname, c.bmp_name, c.typeid, (b.conversion * a.bmparea) as bmparea ";
         $insertq .= "       FROM scen_lrseg_bmps as a, bmp_subtypes as b, bmp_types as c ";
         $insertq .= "       WHERE a.thisyear = $thisyear ";
         $insertq .= "          and $alrcond ";
         $insertq .= "          and a.scenarioid = $scenarioid ";
         $insertq .= "          and b.typeid = c.typeid ";
         # screen for single sub-type bmp
         $insertq .= "          and b.bmpname = '$bmpname' ";
         $insertq .= "          and b.projectid = $projectid ";
         $insertq .= "          and c.projectid = $projectid ";
         $insertq .= "          and a.bmpname = '$bmpname' ";
         # added hoping to speed things up a bit
         $insertq .= "          and a.bmparea > 0 ";
         $insertq .= "      ) as a ";
         $insertq .= "   ON ( e.lrseg = a.lrseg ) ";
         $insertq .= "   left outer join  ";
         ################################
         # END - SUB-QUERY A
         ################################

         ################################
         # START - SUB-QUERY B
         #    the area occupied by this groups already submitted BMPs
         #      - on land uses eligible for this specific BMP
         ################################
         $insertq .= "      (select lrseg, SUM(value_implemented) as occupied ";
         $insertq .= "       FROM scen_bmp_data ";
         $insertq .= "       WHERE thisyear = $thisyear ";
         $insertq .= "          and $lrcond ";
         $insertq .= "          and scenarioid = $scenarioid ";
         $insertq .= "          and typeid in (SELECT typeid ";
         $insertq .= "                         FROM proj_bmp_order ";
         $insertq .= "                         WHERE projectid = $projectid ";
         $insertq .= "                            AND bmp_group = $bmp_group ";
         $insertq .= "                         ) ";
         $insertq .= "          and luname in (SELECT luname ";
         $insertq .= "                         FROM map_landuse_bmp ";
         $insertq .= "                         WHERE projectid = $projectid ";
         $insertq .= "                            AND bmpname = '$bmpname' ";
         $insertq .= "                         ) ";
         $insertq .= "       GROUP BY lrseg ";
         $insertq .= "      ) as b ";
         $insertq .= "   ON ( e.lrseg = b.lrseg ) ";
         $insertq .= "   left outer join  ";
         ################################
         # END - SUB-QUERY B
         ################################

         ################################
         # START - SUB-QUERY C
         #    the area eligible for this specific BMP
         ################################
         $insertq .= "      (select lrseg, landseg, riverseg, subshedid, SUM(luarea) as total_eligible ";
         $insertq .= "       FROM scen_lrsegs ";
         $insertq .= "       WHERE thisyear = $thisyear ";
         $insertq .= "          and $lrcond ";
         $insertq .= "          and scenarioid = $scenarioid ";
         $insertq .= "          and luname in (SELECT luname ";
         $insertq .= "                         FROM map_landuse_bmp ";
         $insertq .= "                         WHERE projectid = $projectid ";
         $insertq .= "                            AND bmpname = '$bmpname' ";
         $insertq .= "                         ) ";
         $insertq .= "       GROUP BY lrseg, landseg, riverseg, subshedid ";
         $insertq .= "      ) as c ";
         $insertq .= "   ON ( e.lrseg = c.lrseg ) ";
         $insertq .= "   left outer join  ";
         ################################
         # END - SUB-QUERY C
         ################################

         ################################
         # START - SUB-QUERY D
         #    the area occupied by this groups already submitted BMPs
         #      - on land uses eligible for this specific BMP
         #      - grouped by landuse
         ################################
         $insertq .= "      (select lrseg, luname, SUM(value_implemented) as lu_occupied ";
         $insertq .= "       FROM scen_bmp_data ";
         $insertq .= "       WHERE thisyear = $thisyear ";
         $insertq .= "          and $lrcond ";
         $insertq .= "          and scenarioid = $scenarioid ";
         $insertq .= "          and typeid in (SELECT typeid ";
         $insertq .= "                         FROM proj_bmp_order ";
         $insertq .= "                         WHERE projectid = $projectid ";
         $insertq .= "                            AND bmp_group = $bmp_group ";
         $insertq .= "                         ) ";
         $insertq .= "          and luname in (SELECT luname ";
         $insertq .= "                         FROM map_landuse_bmp ";
         $insertq .= "                         WHERE projectid = $projectid ";
         $insertq .= "                            AND bmpname = '$bmpname' ";
         $insertq .= "                         ) ";
         $insertq .= "       GROUP BY lrseg, luname ";
         $insertq .= "      ) as d ";

         $insertq .= "   ON ( e.lrseg = d.lrseg ";
         $insertq .= "      and d.luname = e.luname ) ";
         ################################
         # END - SUB-QUERY D
         ################################

         $insertq .= "   WHERE a.bmparea > 0 ";
         $insertq .= "   ORDER BY a.lrseg, e.luname ";

         $tailq = " ) AS a ";
         $tailq .= " WHERE ( lu_total_eligible > lu_occupied ) ";
         $tailq .= "    AND ( total_eligible > occupied ) ";

/*
         if ($bmpname == 'fba') {
            #$listobject->querystring = $selq . $insertq . $tailq;
            $listobject->querystring = $insertq;
            print("<br>$listobject->querystring ;<br>");
            $listobject->performQuery();
            $listobject->tablename = '';
            $listobject->showList();
         }
*/

         $listobject->querystring = $headq . $selq . $insertq . $tailq;

         if ($debug) {
            $listobject->startSplit();
            print("<br>$listobject->querystring ;<br>");
         }

         $listobject->performQuery();

         if ($debug) {
            $split = $listobject->startSplit();
            print("Query Time: $split<br>");
            $listobject->showList();
         }
      }
   }

}


function distributeBMPsToLU3($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $debug) {

   /*
   # emulates vortex proc: usp_BMPdistributeSubTypes
   */
   # disperses BMPs imported at subshed scale without specific land use recipient,
   # to be distributed across all possible landuses according to eligible area
   # these are "subtype" bmps, which have a more explicit mapped land use relationship,
   # but these are mapped to the more general BMP categories
   # where the efficiencies are established.
   # This routine processes these sub-type BMPs in order of the number of affected land uses,
   # therefore, the most explicit bmp/lu relationships will be processed first, insuring
   # maximum implementation of submitted BMPs in and LRSEG.

   # test for temp tables
   #if (!($listobject->tableExists('tmp_lrsegs'))) {
   #   tempBMPTables($listobject, $typeid, $subsheds, $scenarioid, $thisyear, $debug);
   #}

   # $typeid - allows us to choose a specific type. If it is '' or -1, we will assume all
   if (!(strlen($typeid) > 0)) {
      $typeid = -1;
   }

   # need to consider all BMPs in this group, not just this type for calculating remaining area

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $lrcond = " lrseg in ($sslist) ";
      $alrcond = " a.lrseg in ($sslist) ";
      $blrcond = " b.lrseg in ($sslist) ";
   } else {
      $lrcond = ' (1 = 1) ';
      $alrcond = ' (1 = 1) ';
      $blrcond = ' (1 = 1) ';
   }


   $theseyears = split(',', $thisyear);

   foreach($theseyears as $thisyear) {
      # delete any existing BMPs
      deleteBMPType($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $debug);

      $listobject->querystring = "select bmpname, typeid, numlus, bmp_group ";
      $listobject->querystring .= " from ";
      $listobject->querystring .= "    (select a.bmpname, a.typeid, c.bmp_group, count(b.*) as numlus";
      $listobject->querystring .= "     FROM bmp_subtypes as a, map_landuse_bmp as b, proj_bmp_order as c ";
      $listobject->querystring .= "     where a.projectid = $projectid ";
      $listobject->querystring .= "        AND a.typeid = $typeid ";
      $listobject->querystring .= "        AND b.projectid = $projectid ";
      $listobject->querystring .= "        AND c.projectid = $projectid ";
      $listobject->querystring .= "        AND a.bmpname = b.bmpname ";
      $listobject->querystring .= "        AND a.typeid = c.typeid ";
      # $typeid - allows us to choose a specific type. If it is -1, we will assume all
      $listobject->querystring .= "        AND ( (a.typeid = $typeid) or ($typeid = -1 ) )";
      $listobject->querystring .= "     GROUP BY a.bmpname, a.typeid, c.bmp_group ) as foo ";
      $listobject->querystring .= " ORDER BY typeid, numlus, bmpname ";

      if ($debug) {
         $listobject->startSplit();
         print("<br>$listobject->querystring ;<br>");
      }
      $listobject->performQuery();
      $sts = $listobject->queryrecords;

      if ($debug) {
         $split = $listobject->startSplit();
         print("Query Time: $split<br>");
      }

      foreach ($sts as $thisst) {
         $bmpname = $thisst['bmpname'];
         $bmp_group = $thisst['bmp_group'];
         # loops from highly specified BMPS (i.e., bmp specifies only 1 or 2 land uses) to more general

         $insertq = "INSERT INTO scen_bmp_data (scenarioid, thisyear, lrseg, landseg, riverseg, ";
         $insertq .= "   subshedid, luname, ";
         $insertq .= "   typeid, bmpname, value_submitted, value_implemented ) ";

         $insertq .= " SELECT $scenarioid, $thisyear, a.lrseg, a.landseg, a.riverseg, a.subshedid, a.luname, ";
         # retains sub-type information, but still keyed on typeid which links to overall type
         $insertq .= "   a.typeid, a.bmpname, ";
         $insertq .= "   ( (a.lu_total_eligible - a.lu_occupied) / (a.total_eligible - a.occupied) ) * a.bmparea, ";
         # blank value for implemented, that is taken care of at next step
         $insertq .= "   0.0 ";

         $insertq .= " FROM ( ";
         $insertq .= " SELECT e.lrseg, c.landseg, c.riverseg, c.subshedid, e.luname, a.bmpname, ";
         $insertq .= "      a.bmp_name, a.typeid,  ";
         $insertq .= "      a.bmparea,  ";
         $insertq .= "      CASE ";
         $insertq .= "         WHEN b.occupied IS NULL then 0.0 ";
         $insertq .= "         ELSE b.occupied ";
         $insertq .= "      END AS occupied,  ";
         $insertq .= "      c.total_eligible,  ";
         $insertq .= "      CASE ";
         $insertq .= "         WHEN d.lu_occupied IS NULL then 0.0 ";
         $insertq .= "         ELSE d.lu_occupied ";
         $insertq .= "      END AS lu_occupied,  ";
         $insertq .= "      e.lu_total_eligible ";
         $insertq .= "   FROM  ";

         ################################
         # START - SUB-QUERY A
         #    the bmps being submitted
         ################################
         $insertq .= "      (select a.lrseg, a.bmpname, c.bmp_name, c.typeid, (b.conversion * a.bmparea) as bmparea ";
         $insertq .= "       FROM scen_lrseg_bmps as a, bmp_subtypes as b, bmp_types as c ";
         $insertq .= "       WHERE a.thisyear = $thisyear ";
         $insertq .= "          and $alrcond ";
         $insertq .= "          and a.scenarioid = $scenarioid ";
         $insertq .= "          and b.typeid = c.typeid ";
         # screen for single sub-type bmp
         $insertq .= "          and b.bmpname = '$bmpname' ";
         $insertq .= "          and b.projectid = $projectid ";
         $insertq .= "          and c.projectid = $projectid ";
         $insertq .= "          and a.bmpname = '$bmpname' ";
         # added hoping to speed things up a bit
         $insertq .= "          and a.bmparea > 0 ";
         $insertq .= "      ) as a ";
         $insertq .= "   left outer join  ";
         ################################
         # END - SUB-QUERY A
         ################################

         ################################
         # START - SUB-QUERY E
         #    the area eligible for this specific BMP
         #      - grouped by landuse
         ################################
         $insertq .= "      (select lrseg, luname, SUM(luarea) as lu_total_eligible ";
         $insertq .= "       FROM scen_lrsegs ";
         $insertq .= "       WHERE thisyear = $thisyear ";
         $insertq .= "          and $lrcond ";
         $insertq .= "          and scenarioid = $scenarioid ";
         $insertq .= "          and luname in (SELECT luname ";
         $insertq .= "                         FROM map_landuse_bmp ";
         $insertq .= "                         WHERE projectid = $projectid ";
         $insertq .= "                            AND bmpname = '$bmpname' ";
         $insertq .= "                         ) ";
         $insertq .= "       GROUP BY lrseg, luname ";
         $insertq .= "      ) as e ";
         $insertq .= "   ON ( e.lrseg = a.lrseg ) ";
         $insertq .= "   left outer join  ";
         ################################
         # END - SUB-QUERY E
         ################################

         ################################
         # START - SUB-QUERY B
         #    the area occupied by this groups already submitted BMPs
         #      - on land uses eligible for this specific BMP
         ################################
         $insertq .= "      (select lrseg, SUM(value_submitted) as occupied ";
         $insertq .= "       FROM scen_bmp_data ";
         $insertq .= "       WHERE thisyear = $thisyear ";
         $insertq .= "          and $lrcond ";
         $insertq .= "          and scenarioid = $scenarioid ";
         $insertq .= "          and typeid in (SELECT typeid ";
         $insertq .= "                         FROM proj_bmp_order ";
         $insertq .= "                         WHERE projectid = $projectid ";
         $insertq .= "                            AND bmp_group = $bmp_group ";
         $insertq .= "                         ) ";
         $insertq .= "          and luname in (SELECT luname ";
         $insertq .= "                         FROM map_landuse_bmp ";
         $insertq .= "                         WHERE projectid = $projectid ";
         $insertq .= "                            AND bmpname = '$bmpname' ";
         $insertq .= "                         ) ";
         $insertq .= "       GROUP BY lrseg ";
         $insertq .= "      ) as b ";
         $insertq .= "   ON ( a.lrseg = b.lrseg ) ";
         $insertq .= "   left outer join  ";
         ################################
         # END - SUB-QUERY B
         ################################

         ################################
         # START - SUB-QUERY C
         #    the area eligible for this specific BMP
         ################################
         $insertq .= "      (select lrseg, landseg, riverseg, subshedid, SUM(luarea) as total_eligible ";
         $insertq .= "       FROM scen_lrsegs ";
         $insertq .= "       WHERE thisyear = $thisyear ";
         $insertq .= "          and $lrcond ";
         $insertq .= "          and scenarioid = $scenarioid ";
         $insertq .= "          and luname in (SELECT luname ";
         $insertq .= "                         FROM map_landuse_bmp ";
         $insertq .= "                         WHERE projectid = $projectid ";
         $insertq .= "                            AND bmpname = '$bmpname' ";
         $insertq .= "                         ) ";
         $insertq .= "       GROUP BY lrseg, landseg, riverseg, subshedid ";
         $insertq .= "      ) as c ";
         $insertq .= "   ON ( a.lrseg = c.lrseg ) ";
         $insertq .= "   left outer join  ";
         ################################
         # END - SUB-QUERY C
         ################################

         ################################
         # START - SUB-QUERY D
         #    the area occupied by this groups already submitted BMPs
         #      - on land uses eligible for this specific BMP
         #      - grouped by landuse
         ################################
         $insertq .= "      (select lrseg, luname, SUM(value_submitted) as lu_occupied ";
         $insertq .= "       FROM scen_bmp_data ";
         $insertq .= "       WHERE thisyear = $thisyear ";
         $insertq .= "          and $lrcond ";
         $insertq .= "          and scenarioid = $scenarioid ";
         $insertq .= "          and typeid in (SELECT typeid ";
         $insertq .= "                         FROM proj_bmp_order ";
         $insertq .= "                         WHERE projectid = $projectid ";
         $insertq .= "                            AND bmp_group = $bmp_group ";
         $insertq .= "                         ) ";
         $insertq .= "          and luname in (SELECT luname ";
         $insertq .= "                         FROM map_landuse_bmp ";
         $insertq .= "                         WHERE projectid = $projectid ";
         $insertq .= "                            AND bmpname = '$bmpname' ";
         $insertq .= "                         ) ";
         $insertq .= "       GROUP BY lrseg, luname ";
         $insertq .= "      ) as d ";

         $insertq .= "   ON ( a.lrseg = d.lrseg ";
         $insertq .= "      and d.luname = e.luname ) ";
         ################################
         # END - SUB-QUERY D
         ################################

         $insertq .= "   ORDER BY a.lrseg, e.luname ";
         $insertq .= " ) AS a ";
         $insertq .= " WHERE ( lu_total_eligible >= lu_occupied ) ";
         $insertq .= "    AND lu_total_eligible is not null ";

         $listobject->querystring = $insertq;

         if ($debug) {
            $listobject->startSplit();
            print("<br>$listobject->querystring ;<br>");
         }

         $listobject->performQuery();

         if ($debug) {
            $split = $listobject->startSplit();
            print("Query Time: $split<br>");
            $listobject->showList();
         }
      }
   }

}


function distributeBMPsToLU4($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $debug) {

   /*
   # emulates vortex proc: usp_BMPdistributeSubTypes
   */
   # disperses BMPs imported at subshed scale without specific land use recipient,
   # to be distributed across all possible landuses according to eligible area
   # these are "subtype" bmps, which have a more explicit mapped land use relationship,
   # but these are mapped to the more general BMP categories
   # where the efficiencies are established.
   # This routine processes these sub-type BMPs in order of the number of affected land uses,
   # therefore, the most explicit bmp/lu relationships will be processed first, insuring
   # maximum implementation of submitted BMPs in and LRSEG.

   # test for temp tables
   #if (!($listobject->tableExists('tmp_lrsegs'))) {
   #   tempBMPTables($listobject, $typeid, $subsheds, $scenarioid, $thisyear, $debug);
   #}

   # $typeid - allows us to choose a specific type. If it is '' or -1, we will assume all
   if (!(strlen($typeid) > 0)) {
      $typeid = -1;
   }

   # need to consider all BMPs in this group, not just this type for calculating remaining area

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $lrcond = " lrseg in ($sslist) ";
      $alrcond = " a.lrseg in ($sslist) ";
      $blrcond = " b.lrseg in ($sslist) ";
      $clrcond = " c.lrseg in ($sslist) ";
      $dlrcond = " d.lrseg in ($sslist) ";
      $elrcond = " e.lrseg in ($sslist) ";
   } else {
      $lrcond = ' (1 = 1) ';
      $alrcond = ' (1 = 1) ';
      $blrcond = ' (1 = 1) ';
      $clrcond = ' (1 = 1) ';
      $dlrcond = ' (1 = 1) ';
      $elrcond = ' (1 = 1) ';
   }


   $theseyears = split(',', $thisyear);

   foreach($theseyears as $thisyear) {
      # delete any existing BMPs
      deleteBMPType($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $debug);

      $listobject->querystring = "select bmpname, typeid, numlus, bmp_group ";
      $listobject->querystring .= " from ";
      $listobject->querystring .= "    (select a.bmpname, a.typeid, c.bmp_group, count(b.*) as numlus";
      $listobject->querystring .= "     FROM bmp_subtypes as a, map_landuse_bmp as b, proj_bmp_order as c ";
      $listobject->querystring .= "     where a.projectid = $projectid ";
      $listobject->querystring .= "        AND a.typeid = $typeid ";
      $listobject->querystring .= "        AND b.projectid = $projectid ";
      $listobject->querystring .= "        AND c.projectid = $projectid ";
      $listobject->querystring .= "        AND a.bmpname = b.bmpname ";
      $listobject->querystring .= "        AND a.typeid = c.typeid ";
      # $typeid - allows us to choose a specific type. If it is -1, we will assume all
      $listobject->querystring .= "        AND ( (a.typeid = $typeid) or ($typeid = -1 ) )";
      $listobject->querystring .= "     GROUP BY a.bmpname, a.typeid, c.bmp_group ) as foo ";
      $listobject->querystring .= " ORDER BY typeid, numlus, bmpname ";

      if ($debug) {
         $listobject->startSplit();
         print("<br>$listobject->querystring ;<br>");
      }
      $listobject->performQuery();
      $sts = $listobject->queryrecords;

      if ($debug) {
         $split = $listobject->startSplit();
         print("Query Time: $split<br>");
      }

      foreach ($sts as $thisst) {
         $bmpname = $thisst['bmpname'];
         $bmp_group = $thisst['bmp_group'];
         # loops from highly specified BMPS (i.e., bmp specifies only 1 or 2 land uses) to more general

         $insertq = "INSERT INTO scen_bmp_data (scenarioid, thisyear, lrseg, landseg, riverseg, ";
         $insertq .= "   subshedid, luname, ";
         $insertq .= "   typeid, bmpname, value_submitted, value_implemented ) ";

         $insertq .= " SELECT $scenarioid, $thisyear, a.lrseg, a.landseg, a.riverseg, a.subshedid, a.luname, ";
         # retains sub-type information, but still keyed on typeid which links to overall type
         $insertq .= "   a.typeid, a.bmpname, ";
         $insertq .= "   ( (a.lu_total_eligible - a.lu_occupied) / (a.total_eligible - a.occupied) ) * a.bmparea, ";
         # blank value for implemented, that is taken care of at next step
         $insertq .= "   0.0 ";

         $insertq .= " FROM ( ";
         $insertq .= " SELECT a.lrseg, c.landseg, c.riverseg, c.subshedid, e.luname, a.bmpname, ";
         $insertq .= "      a.bmp_name, a.typeid,  ";
         $insertq .= "      a.bmparea,  ";
         $insertq .= "      CASE ";
         $insertq .= "         WHEN SUM(b.value_submitted) IS NULL then 0.0 ";
         $insertq .= "         ELSE SUM(b.value_submitted) ";
         $insertq .= "      END AS occupied,  ";
         $insertq .= "      c.total_eligible,  ";
         $insertq .= "      CASE ";
         $insertq .= "         WHEN d.lu_occupied IS NULL then 0.0 ";
         $insertq .= "         ELSE d.lu_occupied ";
         $insertq .= "      END AS lu_occupied,  ";
         $insertq .= "      SUM(e.luarea) as lu_total_eligible ";
         $insertq .= "   FROM  ";

         ################################
         # START - SUB-QUERY A
         #    the bmps being submitted
         ################################
         $insertq .= "      (select a.lrseg, a.bmpname, c.bmp_name, c.typeid, (b.conversion * a.bmparea) as bmparea ";
         $insertq .= "       FROM scen_lrseg_bmps as a, bmp_subtypes as b, bmp_types as c ";
         $insertq .= "       WHERE a.thisyear = $thisyear ";
         $insertq .= "          and $alrcond ";
         $insertq .= "          and a.scenarioid = $scenarioid ";
         $insertq .= "          and b.typeid = c.typeid ";
         # screen for single sub-type bmp
         $insertq .= "          and b.bmpname = '$bmpname' ";
         $insertq .= "          and b.projectid = $projectid ";
         $insertq .= "          and c.projectid = $projectid ";
         $insertq .= "          and a.bmpname = '$bmpname' ";
         # added hoping to speed things up a bit
         $insertq .= "          and a.bmparea > 0 ";
         $insertq .= "      ) as a ";
         $insertq .= "   left outer join  ";
         ################################
         # END - SUB-QUERY A
         ################################

         ################################
         # START - SUB-QUERY E
         #    the area eligible for this specific BMP
         #      - grouped by landuse
         ################################
         $insertq .= "   scen_lrsegs as e ";
         $insertq .= "   ON ( e.lrseg = a.lrseg ";
         $insertq .= "          AND e.thisyear = $thisyear ";
         $insertq .= "          and $elrcond ";
         $insertq .= "          and e.scenarioid = $scenarioid ";
         $insertq .= "          and e.luname in (SELECT luname ";
         $insertq .= "                         FROM map_landuse_bmp ";
         $insertq .= "                         WHERE projectid = $projectid ";
         $insertq .= "                            AND bmpname = '$bmpname' ";
         $insertq .= "                         ) ";
         $insertq .= "      ) ";
         $insertq .= "   left outer join  ";
         ################################
         # END - SUB-QUERY E
         ################################

         ################################
         # START - SUB-QUERY B
         #    the area occupied by this groups already submitted BMPs
         #      - on land uses eligible for this specific BMP
         ################################
         $insertq .= "   scen_bmp_data as b ";
         $insertq .= "   ON ( a.lrseg = b.lrseg ";
         $insertq .= "          AND b.thisyear = $thisyear ";
         $insertq .= "          AND b.scenarioid = $scenarioid ";
         $insertq .= "          AND $blrcond ) ";
         $insertq .= "          and b.typeid in (SELECT typeid ";
         $insertq .= "                         FROM proj_bmp_order ";
         $insertq .= "                         WHERE projectid = $projectid ";
         $insertq .= "                            AND bmp_group = $bmp_group ";
         $insertq .= "                         ) ";
         $insertq .= "          and b.luname in (SELECT luname ";
         $insertq .= "                         FROM map_landuse_bmp ";
         $insertq .= "                         WHERE projectid = $projectid ";
         $insertq .= "                            AND bmpname = '$bmpname' ";
         $insertq .= "                         ) ";
         $insertq .= "   left outer join  ";
         ################################
         # END - SUB-QUERY B
         ################################

         ################################
         # START - SUB-QUERY C
         #    the area eligible for this specific BMP
         ################################
         $insertq .= "      (select lrseg, landseg, riverseg, subshedid, SUM(luarea) as total_eligible ";
         $insertq .= "       FROM scen_lrsegs ";
         $insertq .= "       WHERE thisyear = $thisyear ";
         $insertq .= "          and $lrcond ";
         $insertq .= "          and scenarioid = $scenarioid ";
         $insertq .= "          and luname in (SELECT luname ";
         $insertq .= "                         FROM map_landuse_bmp ";
         $insertq .= "                         WHERE projectid = $projectid ";
         $insertq .= "                            AND bmpname = '$bmpname' ";
         $insertq .= "                         ) ";
         $insertq .= "       GROUP BY lrseg, landseg, riverseg, subshedid ";
         $insertq .= "      ) as c ";
         $insertq .= "   ON ( a.lrseg = c.lrseg ) ";
         $insertq .= "   left outer join  ";
         ################################
         # END - SUB-QUERY C
         ################################

         ################################
         # START - SUB-QUERY D
         #    the area occupied by this groups already submitted BMPs
         #      - on land uses eligible for this specific BMP
         #      - grouped by landuse
         ################################
         $insertq .= "      (select lrseg, luname, SUM(value_submitted) as lu_occupied ";
         $insertq .= "       FROM scen_bmp_data ";
         $insertq .= "       WHERE thisyear = $thisyear ";
         $insertq .= "          and $lrcond ";
         $insertq .= "          and scenarioid = $scenarioid ";
         $insertq .= "          and typeid in (SELECT typeid ";
         $insertq .= "                         FROM proj_bmp_order ";
         $insertq .= "                         WHERE projectid = $projectid ";
         $insertq .= "                            AND bmp_group = $bmp_group ";
         $insertq .= "                         ) ";
         $insertq .= "          and luname in (SELECT luname ";
         $insertq .= "                         FROM map_landuse_bmp ";
         $insertq .= "                         WHERE projectid = $projectid ";
         $insertq .= "                            AND bmpname = '$bmpname' ";
         $insertq .= "                         ) ";
         $insertq .= "       GROUP BY lrseg, luname ";
         $insertq .= "      ) as d ";

         $insertq .= "   ON ( a.lrseg = d.lrseg ";
         $insertq .= "      and d.luname = e.luname ) ";
         ################################
         # END - SUB-QUERY D
         ################################
         $insertq .= " GROUP BY a.lrseg, c.landseg, c.riverseg, c.subshedid, e.luname, a.bmpname, ";
         $insertq .= "      a.bmp_name, a.typeid,  ";
         $insertq .= "      a.bmparea, d.lu_occupied, c.total_eligible ";

         $insertq .= "   ORDER BY a.lrseg, e.luname ";
         $insertq .= " ) AS a ";
         $insertq .= " WHERE ( lu_total_eligible >= lu_occupied ) ";
         $insertq .= "    AND lu_total_eligible is not null ";

         $listobject->querystring = $insertq;

         if ($debug) {
            $listobject->startSplit();
            print("<br>$listobject->querystring ;<br>");
         }

         $listobject->performQuery();

         if ($debug) {
            $split = $listobject->startSplit();
            print("Query Time: $split<br>");
            $listobject->showList();
         }
      }
   }

}


function clearEfficImplementation($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $debug) {
   # $typeid - allows us to choose a specific type. If it is '' or -1, we will assume all
   if (!(strlen($typeid) > 0)) {
      $typeid = -1;
   }
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subcond = " lrseg in ($sslist) ";
   } else {
      $subcond = ' 1 = 1 ';
   }

   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear in ($thisyear) ";
   } else {
      $yrcond = ' 1 = 1 ';
   }

   $listobject->querystring = " SELECT typeid from bmp_types  ";
   $listobject->querystring .= " WHERE projectid = $projectid ";
   $listobject->querystring .= "    AND ( (typeid = $typeid) or ($typeid = -1 ) ) ";
   $listobject->querystring .= "    AND efftype in (1,2) ";


   if ($debug) {
      $listobject->startSplit();
      print("<br>$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   if ($debug) {
      $split = $listobject->startSplit();
      print("Query Time: $split<br>");
   }

   $ebmps = $listobject->queryrecords;

   foreach ($ebmps as $thisrec) {
      $thistype = $thisrec['typeid'];
      clearBMPImplementation($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $thistype, $debug);
   }
}

function clearBMPImplementation($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $debug) {

   # $typeid - allows us to choose a specific type. If it is '' or -1, we will assume all
   if (!(strlen($typeid) > 0)) {
      $typeid = -1;
   }
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subcond = " lrseg in ($sslist) ";
   } else {
      $subcond = ' 1 = 1 ';
   }

   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear in ($thisyear) ";
   } else {
      $yrcond = ' 1 = 1 ';
   }

   $listobject->querystring = "UPDATE scen_bmp_data SET value_implemented = 0.0 ";
   $listobject->querystring .= " WHERE scenarioid = $scenarioid ";
   $listobject->querystring .= "    AND $subcond ";
   $listobject->querystring .= "    AND $yrcond ";
   $listobject->querystring .= "    AND ( (typeid = $typeid) or ($typeid = -1 ) ) ";


   if ($debug) {
      $listobject->startSplit();
      print("<br>$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   if ($debug) {
      $split = $listobject->startSplit();
      print("Query Time: $split<br>");
   }
}

function redistributeAllEfficBMPs($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $debug) {

   $listobject->querystring = "SELECT typeid from proj_bmp_order ";
   $listobject->querystring .= " WHERE projectid = $projectid ";
   $listobject->querystring .= "    AND bmp_group > 0 ";
   $listobject->querystring .= " ORDER BY bmp_group, bmp_order, typeid";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();
   $grprecs = $listobject->queryrecords;

   foreach ($grprecs as $thisgrp) {
      $typeid = $thisgrp['typeid'];
      distributeBMPsToLU2($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $debug);
   }
}

function rollBackAllLUChangeBmps($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $debug) {

   $listobject->querystring = "SELECT a.*, b.hist_luchange from proj_bmp_order as a, bmp_types as b ";
   $listobject->querystring .= " WHERE a.projectid = $projectid ";
   $listobject->querystring .= "    AND b.projectid = $projectid ";
   $listobject->querystring .= "    AND b.typeid = a.typeid ";
   $listobject->querystring .= "    AND a.bmp_group = 0 ";
   $listobject->querystring .= " ORDER BY a.bmp_group, a.bmp_order, a.typeid ";
   if ($debug) { print("DEBUGGING: <br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();
   $grprecs = $listobject->queryrecords;

   # first clear all bmps
   foreach ($grprecs as $thisgrp) {
      $grpid = $thisgrp['bmp_group'];
      $typeid = $thisgrp['typeid'];
      $bmporder = $thisgrp['bmp_order'];

      rollBackLUBMPs($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $debug);
   }
}

function reImplementAllBMPs($listobject, $projectid, $scenarioid, $thisyear, $luyear, $subsheds, $doluchange, $cleartemps, $debug) {


   if ($cleartemps) {
      # part of a multiple year loop, need to clear temporary BMP tables
      if ($debug) {
         print("Cleaning up BMP Tables <br>");
      }
      clearTempBMPTables($listobject);
   }

   $listobject->querystring = "SELECT a.*, b.hist_luchange, b.bmp_desc, b.efftype ";
   $listobject->querystring .= " from proj_bmp_order as a, bmp_types as b ";
   $listobject->querystring .= " WHERE a.projectid = $projectid ";
   $listobject->querystring .= "    AND b.projectid = $projectid ";
   $listobject->querystring .= "    AND b.typeid = a.typeid ";
   if (!($doluchange)) {
      # screen for lu change only (include combos like buffers, but screen later to prevent lu change)
      $listobject->querystring .= "    AND b.efftype not in (3) ";
   }
   $listobject->querystring .= " ORDER BY a.bmp_group, a.bmp_order, a.typeid ";
   if ($debug) { print("DEBUGGING: <br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();
   $grprecs = $listobject->queryrecords;


   #print("Handling ");
   # first clear all bmps
   foreach ($grprecs as $thisgrp) {
      $grpid = $thisgrp['bmp_group'];
      $typeid = $thisgrp['typeid'];
      $bmporder = $thisgrp['bmp_order'];
      $bmp_desc = $thisgrp['bmp_desc'];
      $efftype = $thisgrp['efftype'];

      if ( (in_array($efftype, array(3, 6, 7)) ) and ( $doluchange ) ) {
         # roll-back any land use changes before distributing
         $totaltime = $listobject->startSplit();
         print("Rolling Back Land-Use Changes for $bmp_desc ... ");
         rollBackLUBMPs($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $debug);
         $totaltime = $listobject->startSplit();
         print("finished. Total Execution Time: $totaltime <br>");
      }
      print("Clearing Previous Implementation values for $bmp_desc ... ");
      $totaltime = $listobject->startSplit();
      # trying to speed this up, this is useless at this juncture. Should use the delete.
      #clearBMPImplementation($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $debug);
      deleteBMPType($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $debug);
      $totaltime = $listobject->startSplit();
      print("finished. Total Execution Time: $totaltime <br>");

   }


   if ($doluchange) {
      # we need to distribute and implement LU Change BMPs before any others are distributed
      # Now, Implement and perform LU Changes
      reset($grprecs);
      foreach ($grprecs as $thisgrp) {
         $grpid = $thisgrp['bmp_group'];
         $typeid = $thisgrp['typeid'];
         $bmporder = $thisgrp['bmp_order'];
         $hist_luchange = $thisgrp['hist_luchange'];
         $bmp_desc = $thisgrp['bmp_desc'];
         $efftype = $thisgrp['efftype'];

         if ( in_array($efftype, array(3, 6, 7)) ) {

            $totaltime = $listobject->startSplit();
            print("distributing $bmp_desc ... ");
            distributeBMPsToLU2($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $debug);
            $totaltime = $listobject->startSplit();
            print("finished. Total Execution Time: $totaltime <br>");

            print("implementing LU-Change BMP: $bmp_desc ... ");
            implementBMPs($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $grpid, $bmporder, $debug);
            $totaltime = $listobject->startSplit();
            print("finished. Total Execution Time: $totaltime <br>");
            # screens for BMPs set to disable historical lu changes. For example, buffers are
            # assumed to exist in the sattelite data set in terms of land use, although their efficiencies
            # are still aplied. Therefore, if the year that is being requested is less than the given
            # baseline year for land use ground truth, then this is not called.
            if ( (($thisyear > $luyear) or ($hist_luchange)) ) {
               print("Performing LU Change for $bmp_desc ... ");
               performLUChangeBMP($listobject, $projectid, $scenarioid, $subsheds, $typeid, $thisyear, $debug);
               $totaltime = $listobject->startSplit();
               print("finished. Total Execution Time: $totaltime <br>");
            }
         }
      }

      # Now, one last time, re-clear LU+Effic.
      reset($grprecs);
      foreach ($grprecs as $thisgrp) {
         $grpid = $thisgrp['bmp_group'];
         $typeid = $thisgrp['typeid'];
         $bmporder = $thisgrp['bmp_order'];
         $hist_luchange = $thisgrp['hist_luchange'];
         $bmp_desc = $thisgrp['bmp_desc'];
         $efftype = $thisgrp['efftype'];
         # if this is a lu+effic BMP, we need to Re-Clear it so that it is re-distributed in terms of its
         # application of efficiency, as the underlying land use may change based on other LU changes.
         # Ex: 100 acres of buffer, 1000 acres of NM, 2000 acres of crop land
         # first buffers convert 100 acres of crop land to forest. Original distribution puts buffers on non-NM land,
         # because there is NO NM land prior to application of NM BMP
         # second, 1000 of the remaining 1900 acres of crop land are made NM
         # finally, buffers are cleared and re-distributed.
         # LU-Change-Only BMPs are also re-cleared,
         # because we don't want them to compete for implementation purposes
         # LU Change could remain, if we could instruct them to be disregarded in the distribution
         # routine
         if ( in_array($efftype, array(3, 6, 7)) ) {
            print("RE-Clearing Previous Implementation values for $bmp_desc ... ");
            $totaltime = $listobject->startSplit();
            #clearBMPImplementation($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $debug);
            deleteBMPType($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $debug);
            $totaltime = $listobject->startSplit();
            print("finished. Total Execution Time: $totaltime <br>");
         }
      }
   }

   # now distribute non-LU Change BMPs
   reset($grprecs);
   foreach ($grprecs as $thisgrp) {
      $grpid = $thisgrp['bmp_group'];
      $typeid = $thisgrp['typeid'];
      $bmporder = $thisgrp['bmp_order'];
      $bmp_desc = $thisgrp['bmp_desc'];
      $efftype = $thisgrp['efftype'];

      if ( ($grpid <> 0) or ($efftype <> 3) ) {
         # this means, distribute only efficiency BMPs,
         # note, that this will RE-DISTRIBUTE lu+effic, such as buffers
         # because we want the efficiency values to be re-distributed properly
         # across remaining land uses, AFTER, LU Changes have been done
         $totaltime = $listobject->startSplit();
         print("distributing $bmp_desc ... ");
         distributeBMPsToLU2($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $debug);
         $totaltime = $listobject->startSplit();
         print("finished. Total Execution Time: $totaltime <br>");
         print("implementing $bmp_desc ... ");

         implementBMPs($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $grpid, $bmporder, $debug);
         $totaltime = $listobject->startSplit();
         print("finished. Total Execution Time: $totaltime <br>");
      }

   }

   print(" done. <br>");
}

function implementAllLUChangeBMPs($listobject, $projectid, $scenarioid, $thisyear, $luyear, $subsheds, $debug) {

   # ldebug - the debug level. If debug is 1 then sub-routines debug value will be 0, if > 1 then they will be set on
   # this can go infinitely down.
   if ($debug > 0 ) {
      $ldebug = $debug - 1;
   } else {
      $ldebug = 0;
   }

   $listobject->querystring = "SELECT a.*, b.hist_luchange from proj_bmp_order as a, bmp_types as b ";
   $listobject->querystring .= " WHERE a.projectid = $projectid ";
   $listobject->querystring .= "    AND b.projectid = $projectid ";
   $listobject->querystring .= "    AND b.efftype in (3, 6, 7) ";
   $listobject->querystring .= "    AND b.typeid = a.typeid ";
   $listobject->querystring .= " ORDER BY a.bmp_group, a.bmp_order, a.typeid ";
   if ($debug) { print("DEBUGGING: <br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();
   $grprecs = $listobject->queryrecords;

   # first clear all bmps
   foreach ($grprecs as $thisgrp) {
      $grpid = $thisgrp['bmp_group'];
      $typeid = $thisgrp['typeid'];
      $bmporder = $thisgrp['bmp_order'];

      if ($debug) {
         $listobject->startSplit();
      }
      rollBackLUBMPs($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $ldebug);
      if ($debug) {
         $split = $listobject->startSplit();
         print("<b>DEBUG:</b>Rolled Back, $split seconds.  Group: $grpid, Type: $typeid, Order: $bmporder -- <br>");
      }
      clearBMPImplementation($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $ldebug);
      if ($debug) {
         $split = $listobject->startSplit();
         print("<b>DEBUG:</b>Cleared Implementation, $split seconds.  Group: $grpid, Type: $typeid, Order: $bmporder -- <br>");
      }

   }

   # now distribute
   reset($grprecs);
   foreach ($grprecs as $thisgrp) {
      $grpid = $thisgrp['bmp_group'];
      $typeid = $thisgrp['typeid'];
      $bmporder = $thisgrp['bmp_order'];

      distributeBMPsToLU2($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $debug);
      if ($debug) {
         $split = $listobject->startSplit();
         print("<b>DEBUG:</b>Re-distributed, $split seconds.  Group: $grpid, Type: $typeid, Order: $bmporder -- <br>");
      }

   }

   # finally, re-implement
   reset($grprecs);
   foreach ($grprecs as $thisgrp) {
      $grpid = $thisgrp['bmp_group'];
      $typeid = $thisgrp['typeid'];
      $bmporder = $thisgrp['bmp_order'];
      $hist_luchange = $thisgrp['hist_luchange'];

      implementBMPs($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $grpid, $bmporder, $ldebug);
      if ($debug) {
         $split = $listobject->startSplit();
         print("<b>DEBUG:</b>Implemented, $split seconds.  Group: $grpid, Type: $typeid, Order: $bmporder -- <br>");
      }
      # screens for BMPs set to disable historical lu changes. For example, buffers are
      # assumed to exist in the sattelite data set in terms of land use, although their efficiencies
      # are still applied. Therefore, if the year that is being requested is less than the given
      # baseline year for land use ground truth, then this is not called.
      if ( (($thisyear >= $luyear) or ($hist_luchange)) ) {
         performLUChangeBMP($listobject, $projectid, $scenarioid, $subsheds, $typeid, $thisyear, $ldebug);
         if ($debug) {
            $split = $listobject->startSplit();
            print("<b>DEBUG:</b>LU Change Performed, $split seconds.  Group: $grpid, Type: $typeid, Order: $bmporder -- <br>");
         }
      }
   }
   #$totaltime = $timer->startSplit();
   #print("Finished Distributing BMPs. Total Execution Time: $totaltime <br>");
}

function implementAllEfficBMPs($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $debug) {

   $listobject->querystring = "SELECT * from proj_bmp_order ";
   $listobject->querystring .= " WHERE projectid = $projectid ";
   $listobject->querystring .= "    AND bmp_group > 0 ";
   $listobject->querystring .= " ORDER BY bmp_group, bmp_order, typeid";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();
   $grprecs = $listobject->queryrecords;

   foreach ($grprecs as $thisgrp) {
      $grpid = $thisgrp['bmp_group'];
      $typeid = $thisgrp['typeid'];
      $bmporder = $thisgrp['bmp_order'];

      if ($debug) {
         $listobject->startSplit();
      }
      implementBMPs($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $grpid, $bmporder, $debug);

   }
   if ($debug) {
      $split = $listobject->startSplit();
      print("Finished Distributing BMPs. Total Execution Time: $split <br>");
   }
}

function implementOneEfficGroup($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $debug) {

   # pass in a BMP typeid, must re-calculate for all bmps in that group (since they go in order of application priority)

   $listobject->querystring = "SELECT * from proj_bmp_order ";
   $listobject->querystring .= " WHERE projectid = $projectid ";
   $listobject->querystring .= "    AND bmp_group in ( ";
   $listobject->querystring .= "       select bmp_group from proj_bmp_order ";
   $listobject->querystring .= "       where typeid = $typeid ";
   $listobject->querystring .= "    ) ";
   $listobject->querystring .= " ORDER BY bmp_group, bmp_order, typeid";
   if ($debug) { print("<br>$listobject->querystring ;<br>"); }
   $listobject->performQuery();
   $grprecs = $listobject->queryrecords;

   foreach ($grprecs as $thisgrp) {
      $grpid = $thisgrp['bmp_group'];
      $typeid = $thisgrp['typeid'];
      $bmporder = $thisgrp['bmp_order'];

      implementBMPs($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $grpid, $bmporder, $debug);

   }
   #$totaltime = $timer->startSplit();
   #print("Finished Distributing BMPs. Total Execution Time: $totaltime <br>");
}

function implementBMPs($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $bmpgroup, $bmporder, $debug) {


   # calculates area available for implementation of given BMP.
   # multiple values of thisyear will be handled one at a time
   $theseyears = split(",", $thisyear);

   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subcond = " lrseg in ($sslist) ";
      $asubcond = " a.lrseg in ($sslist) ";
      $bsubcond = " b.lrseg in ($sslist) ";
   } else {
      $subcond = ' 1 = 1 ';
      $asubcond = ' 1 = 1 ';
      $bsubcond = ' 1 = 1 ';
   }

   # matches vortex proc:
   # usp_BmpDataUpdateEfficiencyValueImplemented
   # enhanced to allow user to only process a sub-set of watersheds
   # this references the concept of a "bmp_group" which means any set of bmps that compete for the same land use,
   # i.e., bmps that are mutually exclusive of one another on the same acre

   # this query strucyture assumes that there is only one instance of any bmp typeid for
   # every lrseg/lu combination. A more robust approach would not make this assumption
   # However, given that the distribution method indures this to be the case, this is not a big concern

   foreach($theseyears as $thisyear) {
      # This is not good, however, it assumes that ONLY ONE typeid in a group will be listed as BMPORDER = 1
      # setting this condition to -1 instead of 1 disables this first query and all goes to the next

      $listobject->querystring = "UPDATE scen_bmp_data ";
      $listobject->querystring .= " SET value_implemented = scen_bmp_data.value_submitted * a.impfactor ";
      $listobject->querystring .= " FROM   ";
      $listobject->querystring .= " (  select b.lrseg, b.luname, ";
      $listobject->querystring .= "       CASE ";
      $listobject->querystring .= "          WHEN b.remaining > c.trying THEN 1.0 ";
      $listobject->querystring .= "          WHEN b.remaining < c.trying THEN b.remaining/c.trying ";
      $listobject->querystring .= "          WHEN b.remaining = 0.0 THEN 0.0";
      $listobject->querystring .= "          ELSE 1.0";
      $listobject->querystring .= "       END as impfactor";
      $listobject->querystring .= "    FROM ( ";
      $listobject->querystring .= "       SELECT a.lrseg, a.luname, ";
      $listobject->querystring .= "          CASE ";
      $listobject->querystring .= "             WHEN b.alreadyapplied is null THEN a.luarea ";
      $listobject->querystring .= "             WHEN a.luarea >= b.alreadyapplied THEN (luarea - b.alreadyapplied) ";
      $listobject->querystring .= "             ELSE 0.0 ";
      $listobject->querystring .= "          END as remaining ";
      $listobject->querystring .= "       FROM scen_lrsegs AS a ";
      # GET ALREADY APPLIED from lower order same group as this BMP
      $listobject->querystring .= "       LEFT OUTER JOIN (  ";
      $listobject->querystring .= "          SELECT lrseg, luname, sum(value_implemented) as alreadyapplied ";
      $listobject->querystring .= "          FROM scen_bmp_data ";
      $listobject->querystring .= "          WHERE scenarioid = $scenarioid ";
      $listobject->querystring .= "             AND thisyear = $thisyear ";
      $listobject->querystring .= "             AND $subcond ";
      $listobject->querystring .= "             AND typeid in ( ";
      $listobject->querystring .= "                SELECT typeid ";
      $listobject->querystring .= "                FROM proj_bmp_order ";
      $listobject->querystring .= "                WHERE bmp_group = $bmpgroup ";
      $listobject->querystring .= "                   AND bmp_order <= $bmporder ";
      # dont double-count this one if somehow it was NOT zeroed before this process
      $listobject->querystring .= "                   AND typeid <> $typeid ";
      $listobject->querystring .= "                   AND projectid = $projectid ";
      $listobject->querystring .= "             ) ";
      $listobject->querystring .= "          GROUP BY lrseg, luname ";
      $listobject->querystring .= "          ) as b ";
      $listobject->querystring .= "       ON ( a.lrseg=b.lrseg ";
      $listobject->querystring .= "        AND a.luname=b.luname ) ";
      $listobject->querystring .= "    WHERE a.scenarioid = $scenarioid ";
      $listobject->querystring .= "       AND a.thisyear = $thisyear ";
      $listobject->querystring .= "       AND $asubcond ";
      $listobject->querystring .= "       AND a.luname in ";
      $listobject->querystring .= "          (select b.luname from bmp_subtypes as a, ";
      $listobject->querystring .= "           map_landuse_bmp as b ";
      $listobject->querystring .= "           where a.projectid = $projectid ";
      $listobject->querystring .= "              and b.projectid = $projectid ";
      $listobject->querystring .= "              and a.bmpname = b.bmpname ";
      $listobject->querystring .= "              and a.typeid = $typeid ";
      $listobject->querystring .= "           group by b.luname";
      $listobject->querystring .= "           ) ";
      $listobject->querystring .= "    ) AS b ";
      $listobject->querystring .= "    LEFT OUTER JOIN";
      $listobject->querystring .= "    ( SELECT lrseg, luname, typeid, sum(value_submitted) as trying ";
      $listobject->querystring .= "      FROM scen_bmp_data ";
      $listobject->querystring .= "      WHERE scenarioid = $scenarioid ";
      $listobject->querystring .= "          AND thisyear = $thisyear ";
      $listobject->querystring .= "          AND $subcond ";
      $listobject->querystring .= "          AND typeid = $typeid ";
      $listobject->querystring .= "      GROUP BY lrseg, luname, typeid ";
      $listobject->querystring .= "    ) AS c ";
      $listobject->querystring .= "    ON ( b.lrseg=c.lrseg ";
      $listobject->querystring .= "       AND b.luname=c.luname ) ";
      $listobject->querystring .= " ) as a ";
      $listobject->querystring .= " WHERE scen_bmp_data.scenarioid = $scenarioid ";
      $listobject->querystring .= "    AND scen_bmp_data.thisyear = $thisyear ";
      $listobject->querystring .= "    AND scen_bmp_data.lrseg = a.lrseg ";
      $listobject->querystring .= "    AND scen_bmp_data.typeid = $typeid ";
      $listobject->querystring .= "    AND scen_bmp_data.luname = a.luname ";


      if ($debug) {
         $listobject->startSplit();
         print("<br>$listobject->querystring ;<br>");
      }
      $listobject->performQuery();

      if ($debug) {
         $split = $listobject->startSplit();
         print("Query Time: $split<br>");
      }
   }
}

function calculateBMPEfficiencies($listobject, $projectid, $scenarioid, $subsheds, $typeid, $thisyear, $debug) {

/*
Procedure:  usp_WorkCalculateBMPEfficiencies
Author:      Robert W. Burgholzer, UMD, 5/16/2005
Date:
Purpose:
Result Set:
Return Codes:
*/   # queries for the lrseg landuses in the given
   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $asubcond = " a.lrseg in ($sslist) ";
      $subcond = " lrseg in ($sslist) ";
   } else {
      $subcond = ' (1 = 1) ';
      $asubcond = ' (1 = 1) ';
   }

   $listobject->querystring = " DELETE FROM  scen_bmp_area_effic ";
   $listobject->querystring .= " where scenarioid = $scenarioid ";
   # allows query to handle all or only ONe specific BMP
   $listobject->querystring .= "    and ( (typeid = $typeid) or ($typeid = -1) ) ";
   $listobject->querystring .= "    and thisyear = $thisyear ";
   $listobject->querystring .= "    and $subcond ";

   if ($debug) {
      print("<br>$listobject->querystring ;<br>");
      $listobject->startSplit();
   }
   $listobject->performQuery();

   if ($debug) {
      $split = $listobject->startSplit();
      print("Query Time: $split<br>");
   }


   # sets up the individual efficiencies for each multiplicative and additive BMP
   # the calculation of a single BMP factor occurs later
   $listobject->querystring = " INSERT INTO scen_bmp_area_effic (scenarioid, lrseg, ";
   $listobject->querystring .= "   thisyear, typeid, luname, area_submitted, ";
   $listobject->querystring .= "   area_implemented, constit, efftype, base_effic, imp_eff )  ";
   $listobject->querystring .= " SELECT $scenarioid, lrseg, $thisyear, typeid, ";
   $listobject->querystring .= "   luname, area_submitted, area_implemented,  ";
   $listobject->querystring .= "   constit, efftype, base_effic, eff ";
   $listobject->querystring .= " FROM  ";
   $listobject->querystring .= " ( SELECT a.lrseg, a.typeid, a.luname, ";
   $listobject->querystring .= "      SUM(a.value_submitted) as area_submitted, ";
   $listobject->querystring .= "      SUM(a.value_implemented) as area_implemented, ";
   $listobject->querystring .= "      e.constit, c.efftype, e.efficiency as base_effic, ";
   $listobject->querystring .= "      CASE ";
   $listobject->querystring .= "         WHEN d.luarea = 0 THEN 0 ";
   $listobject->querystring .= "         WHEN SUM(a.value_implemented) > d.luarea THEN e.efficiency ";
   $listobject->querystring .= "         WHEN e.efficiency * e.affected_area * ";
   $listobject->querystring .= "            SUM(a.value_implemented) <= d.luarea  ";
   $listobject->querystring .= "         THEN e.efficiency * e.affected_area * ";
   $listobject->querystring .= "            SUM(value_implemented)/d.luarea ";
   $listobject->querystring .= "      END AS eff, count(*) as numrecs ";
   $listobject->querystring .= "   FROM ";
   $listobject->querystring .= "   scen_bmp_data AS a  ";
   $listobject->querystring .= "   JOIN subshedinfo AS b ON ";
   $listobject->querystring .= "      a.subshedid=b.subshedid ";
   $listobject->querystring .= "      AND a.thisyear = $thisyear ";
   $listobject->querystring .= "      AND b.projectid = $projectid ";
   $listobject->querystring .= "   JOIN (select a.typeid, b.efftype, b.bmp_name as typename ";
   $listobject->querystring .= "         from bmp_subtypes AS a, bmp_types as b ";
   $listobject->querystring .= "         where a.projectid = $projectid ";
   $listobject->querystring .= "            and a.typeid = b.typeid ";
   $listobject->querystring .= "            and ( (a.typeid = $typeid) or ($typeid = -1) ) ";
   $listobject->querystring .= "         group by a.typeid, b.efftype, b.bmp_name ";
   $listobject->querystring .= "   ) AS c ";
   $listobject->querystring .= "      ON a.typeid=c.typeid ";
   $listobject->querystring .= "   RIGHT JOIN ( ";
   $listobject->querystring .= "      SELECT b.subshedid, a.lrseg, ";
   $listobject->querystring .= "      CASE ";
   $listobject->querystring .= "         WHEN b.stseg is null THEN '-1' ";
   $listobject->querystring .= "         ELSE b.stseg ";
   $listobject->querystring .= "      END as stseg, ";
   $listobject->querystring .= "      a.luname, SUM(a.luarea) AS luarea ";
   $listobject->querystring .= "      FROM scen_lrsegs AS a  ";
   $listobject->querystring .= "      JOIN subshedinfo AS b  ";
   $listobject->querystring .= "         ON a.subshedid=b.subshedid ";
   $listobject->querystring .= "      WHERE a.scenarioid = $scenarioid ";
   $listobject->querystring .= "         AND a.thisyear = $thisyear ";
   $listobject->querystring .= "         AND $asubcond ";
   $listobject->querystring .= "         AND b.projectid = $projectid ";
   $listobject->querystring .= "      GROUP BY b.subshedid, a.lrseg, b.stseg, a.luname ";
   $listobject->querystring .= "   ) AS d  ";
   $listobject->querystring .= "      ON b.stseg=d.stseg  ";
   $listobject->querystring .= "      AND b.subshedid=d.subshedid ";
   $listobject->querystring .= "      AND a.lrseg=d.lrseg  ";
   $listobject->querystring .= "      AND a.luname=d.luname ";
   $listobject->querystring .= "   JOIN proj_bmp_efficiencies AS e  ";
   $listobject->querystring .= "      ON c.typename=e.bmpname  ";
   $listobject->querystring .= "         AND a.luname=e.luname  ";
   $listobject->querystring .= "         AND b.stseg=e.stseg  ";
   $listobject->querystring .= "         AND e.projectid = $projectid ";
   # only choose bmps that are either 'Additive' or 'Multiplicative' (1 & 2)
   # Or, those which are both land use change and multiplicative/additive (6 & 7)
   $listobject->querystring .= "   WHERE a.scenarioid = $scenarioid ";
   $listobject->querystring .= "      AND a.thisyear = $thisyear ";
   $listobject->querystring .= "      AND ( (a.typeid = $typeid) or ($typeid = -1) ) ";
   $listobject->querystring .= "      AND $asubcond ";
   $listobject->querystring .= "      AND c.efftype in (1, 2, 6, 7) ";
   $listobject->querystring .= "   GROUP BY a.lrseg, a.typeid, a.luname, d.luarea, e.constit,  ";
   $listobject->querystring .= "      e.efficiency, c.efftype, e.affected_area ";
   $listobject->querystring .= ") AS A ";

   #$debug = 1;
   if ($debug) {
      $listobject->startSplit();
      print("<br>$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   if ($debug) {
      $split = $listobject->startSplit();
      print("<b>DEBUG:</b>Individual Efficiencies Calculated. Query Time: $split<br>");
   }

}


function createMasslinks($listobject, $projectid, $scenarioid, $subsheds, $thisyear, $debug) {

   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subcond = " lrseg in ($sslist) ";
      $asubcond = " a.lrseg in ($sslist) ";
   } else {
      $subcond = ' (1 = 1) ';
      $asubcond = ' (1 = 1) ';
   }

   # constants
   $raiseto = 10.0;
   $minvalue = 0.00001;

   switch ($listobject->dbtype) {
      case 'postgres':
         $logfunc = 'log';
      break;

      case 'mssql':
         $logfunc = 'log10';
      break;

      default:
         $logfunc = 'log';
      break;
   }

   $listobject->querystring = "  DELETE FROM scen_masslink_comps ";
   $listobject->querystring .= " WHERE scenarioid = $scenarioid ";
   $listobject->querystring .= "    AND thisyear = $thisyear ";
   $listobject->querystring .= "    AND $subcond ";

   if ($debug) {
      $listobject->startSplit();
      print("<br>$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   if ($debug) {
      $split = $listobject->startSplit();
      print("Query Time: $split<br>");
   }

   $listobject->querystring = "  DELETE FROM scen_masslinks ";
   $listobject->querystring .= " WHERE scenarioid = $scenarioid ";
   $listobject->querystring .= "    AND thisyear = $thisyear ";
   $listobject->querystring .= "    AND $subcond ";

   if ($debug) {
      $listobject->startSplit();
      print("<br>$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   if ($debug) {
      $split = $listobject->startSplit();
      print("Query Time: $split<br>");
   }

   # combine all additive BMPs into a single factor, and insert them into the component table
   # insert all multiplicative BMPs into the component table
   $listobject->querystring = "  INSERT INTO scen_masslink_comps (scenarioid, lrseg, thisyear,  ";
   $listobject->querystring .= "    typeid, luname, constit, passthru ) ";
   $listobject->querystring .= " ( select scenarioid, lrseg, thisyear, -256 as typeid,  ";
   $listobject->querystring .= "    luname, constit, ";
   $listobject->querystring .= "    CASE ";
   $listobject->querystring .= "       WHEN SUM(imp_eff) > 1 THEN 0.0 ";
   $listobject->querystring .= "       ELSE 1 - SUM(imp_eff) ";
   $listobject->querystring .= "    END AS passthru ";
   $listobject->querystring .= " FROM scen_bmp_area_effic ";
   $listobject->querystring .= " WHERE efftype in (2, 7 )  ";
   $listobject->querystring .= "    AND scenarioid = $scenarioid ";
   $listobject->querystring .= "    AND thisyear = $thisyear ";
   $listobject->querystring .= "    AND $subcond ";
   $listobject->querystring .= " GROUP BY scenarioid, lrseg, thisyear, luname, constit ";
   $listobject->querystring .= " ) UNION ( ";
   $listobject->querystring .= " select scenarioid, lrseg, thisyear, typeid, luname, constit, ";
   $listobject->querystring .= "    1.0 - imp_eff AS passthru ";
   $listobject->querystring .= " FROM scen_bmp_area_effic ";
   $listobject->querystring .= " WHERE efftype in (1, 6 ) ";
   $listobject->querystring .= "    AND scenarioid = $scenarioid ";
   $listobject->querystring .= "    AND thisyear = $thisyear ";
   $listobject->querystring .= "    AND $subcond ";
   $listobject->querystring .= " ) ";

   if ($debug) {
      $listobject->startSplit();
      print("<br>$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   if ($debug) {
      $split = $listobject->startSplit();
      print("Query Time: $split<br>");
   }


   $listobject->querystring = "  INSERT INTO scen_masslinks (scenarioid, subshedid, lrseg,  ";
   $listobject->querystring .= "    constit, luname, passthru, thisyear) ";
   $listobject->querystring .= "    SELECT a.scenarioid, a.subshedid, a.lrseg, a.constit, a.luname, ";
   $listobject->querystring .= "       CASE ";
   $listobject->querystring .= "          WHEN b.passthru is null THEN 1.0 ";
   $listobject->querystring .= "       ELSE ";
   $listobject->querystring .= "          b.passthru ";
   $listobject->querystring .= "       END ";
   $listobject->querystring .= "    as passthru, $thisyear ";
   $listobject->querystring .= "    from (select a.scenarioid, a.subshedid, a.lrseg, a.luname, b.constit ";
   $listobject->querystring .= "       from scen_lrsegs as a,  ";
   $listobject->querystring .= "          (select constit from proj_bmp_efficiencies as b ";
   $listobject->querystring .= "          where b.projectid = $projectid ";
   $listobject->querystring .= "          group by b.constit ";
   $listobject->querystring .= "          ) as b ";
   $listobject->querystring .= "       where a.scenarioid = $scenarioid ";
   $listobject->querystring .= "          and a.thisyear = $thisyear ";
   $listobject->querystring .= "          AND $asubcond ";
   $listobject->querystring .= "       group by a.scenarioid, a.subshedid, a.lrseg, a.luname, b.constit ";
   $listobject->querystring .= "       ) as a LEFT OUTER JOIN ";
   $listobject->querystring .= "       (select lrseg, luname, constit, ";
   $listobject->querystring .= "          CASE ";
   $listobject->querystring .= "             WHEN power($raiseto, sum($logfunc(passthru))) < $minvalue ";
   $listobject->querystring .= "                THEN $minvalue ";
   $listobject->querystring .= "             ELSE power($raiseto, sum($logfunc(passthru))) ";
   $listobject->querystring .= "          END as PASSTHRU ";
   $listobject->querystring .= "       from scen_masslink_comps ";
   $listobject->querystring .= "       where scenarioid = $scenarioid ";
   $listobject->querystring .= "          AND passthru > 0 ";
   $listobject->querystring .= "          AND thisyear = $thisyear ";
   $listobject->querystring .= "          AND $subcond ";
   $listobject->querystring .= "       group by lrseg, luname, constit ";
   $listobject->querystring .= "       ) as b ";
   $listobject->querystring .= "    ON a.lrseg = b.lrseg ";
   $listobject->querystring .= "       AND a.luname = b.luname ";
   $listobject->querystring .= "       AND a.constit = b.constit ";
   $listobject->querystring .= "    order by a.scenarioid, a.lrseg, a.luname, a.constit ";

   if ($debug) {
      $listobject->startSplit();
      print("<br>$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   if ($debug) {
      $split = $listobject->startSplit();
      print("Query Time: $split<br>");
   }


}

function performLUChangeBMP($listobject, $projectid, $scenarioid, $subsheds, $typeid, $thisyear, $debug) {

   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subcond = " lrseg in ($sslist) ";
      $asubcond = " a.lrseg in ($sslist) ";
      $bsubcond = " b.lrseg in ($sslist) ";
      $ssubcond = " scen_lrsegs.lrseg in ($sslist) ";
   } else {
      $subcond = ' (1 = 1) ';
      $asubcond = ' (1 = 1) ';
      $bsubcond = ' (1 = 1) ';
      $ssubcond = ' (1 = 1) ';
   }

   # store this transaction in the lu change history table for later rollback
   #-- add to the destination land use
   $listobject->querystring = " INSERT INTO scen_bmp_luchghist (scenarioid, thisyear, lrseg, bmpname, ";
   $listobject->querystring .= "    srclu, destlu, submit_area, chgarea ) ";
# debugging
 #  $listobject->querystring = "  ";
   $listobject->querystring .= " SELECT $scenarioid, $thisyear, lrseg, bmpname, src_lu, dest_lu, ";
   $listobject->querystring .= "    value_submitted, value_implemented ";
   $listobject->querystring .= " from  ";
   $listobject->querystring .= "    (  ";
   $listobject->querystring .= "    select a.lrseg, a.bmpname, a.value_submitted, a.value_implemented, c.dest_lu, c.src_lu  ";
   $listobject->querystring .= "    from scen_bmp_data as a,  ";
   $listobject->querystring .= "    ( ";
   # -- merge the transformation bmp codes into land uses
   $listobject->querystring .= "    select f.typeid, f.bmpname, b.src_lu, b.dest_lu  ";
   $listobject->querystring .= "    from map_bmplu_conv as b, bmp_subtypes as f ";
   # -- imported sub-type to imported type
   $listobject->querystring .= "       WHERE f.projectid = $projectid ";
   $listobject->querystring .= "       AND b.projectid = $projectid ";
   $listobject->querystring .= "       AND f.typeid = $typeid ";
   $listobject->querystring .= "       AND b.bmpid = f.bmpid ";
   $listobject->querystring .= "       AND b.dest_lu is not null ";
   $listobject->querystring .= "    GROUP BY f.typeid, f.bmpname, b.src_lu, b.dest_lu ";
   $listobject->querystring .= "    ) as c ";
   $listobject->querystring .= " WHERE a.luname = c.src_lu ";
   $listobject->querystring .= "    and a.bmpname = c.bmpname ";
   $listobject->querystring .= "    and a.typeid = $typeid ";
   $listobject->querystring .= "    and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and a.thisyear = $thisyear ";
   $listobject->querystring .= "    and $asubcond ";
   $listobject->querystring .= " ) as a ";

   if ($debug) {
      $listobject->startSplit();
      print("<br>$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   if ($debug) {
      $split = $listobject->startSplit();
      print("Query Time: $split<br>");
      $listobject->showList();
   }


   # add to the destination land use
   # assumes that all land uses are in the database, with 0.0 area if they are previously null.
   $listobject->querystring = " UPDATE scen_lrsegs set luarea = a.convertedarea ";
   $listobject->querystring .= " from  ( ";
   $listobject->querystring .= " SELECT b.lrseg, b.luname, (b.luarea + a.chgarea) as convertedarea ";
   $listobject->querystring .= " FROM scen_lrsegs as b, ";
   $listobject->querystring .= "    ( select a.lrseg, a.destlu, sum(a.chgarea) as chgarea ";
   $listobject->querystring .= "      from scen_bmp_luchghist as a, bmp_subtypes as c  ";
   $listobject->querystring .= "      WHERE a.scenarioid = $scenarioid ";
   $listobject->querystring .= "         and a.bmpname = c.bmpname ";
   $listobject->querystring .= "         and c.typeid = $typeid ";
   $listobject->querystring .= "         and a.thisyear = $thisyear ";
   $listobject->querystring .= "         and $asubcond ";
   $listobject->querystring .= "      GROUP BY a.lrseg, a.destlu ";
   $listobject->querystring .= "    ) as a ";
   $listobject->querystring .= " WHERE b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and b.thisyear = $thisyear ";
   $listobject->querystring .= "    and b.luname = a.destlu ";
   $listobject->querystring .= "    and b.lrseg = a.lrseg ";
   $listobject->querystring .= "    and $bsubcond ";
   $listobject->querystring .= " ) as a ";
   $listobject->querystring .= " where scen_lrsegs.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and scen_lrsegs.luname = a.luname ";
   $listobject->querystring .= "    and scen_lrsegs.lrseg = a.lrseg ";
   $listobject->querystring .= "    and scen_lrsegs.thisyear = $thisyear ";
   $listobject->querystring .= "    and $ssubcond ";

   if ($debug) {
      $listobject->startSplit();
      print("<br>$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   if ($debug) {
      $split = $listobject->startSplit();
      print("Query Time: $split<br>");
      $listobject->showList();
   }


   #-- subtract from the destination land use
   $listobject->querystring = " UPDATE scen_lrsegs set luarea = a.convertedarea ";
   $listobject->querystring .= " from ( ";
   $listobject->querystring .= " SELECT b.lrseg, b.luname, ";
   $listobject->querystring .= "    CASE ";
   $listobject->querystring .= "       WHEN (a.chgarea > b.luarea) THEN 0.0";
   $listobject->querystring .= "       ELSE (b.luarea - a.chgarea)";
   $listobject->querystring .= "    END as convertedarea ";
   $listobject->querystring .= " FROM scen_lrsegs as b, ";
   $listobject->querystring .= "    ( select a.lrseg, a.srclu, sum(a.chgarea) as chgarea ";
   $listobject->querystring .= "      from scen_bmp_luchghist as a, bmp_subtypes as c  ";
   $listobject->querystring .= "      WHERE a.scenarioid = $scenarioid ";
   $listobject->querystring .= "         and a.bmpname = c.bmpname ";
   $listobject->querystring .= "         and c.typeid = $typeid ";
   $listobject->querystring .= "         and a.thisyear = $thisyear ";
   $listobject->querystring .= "         and $asubcond ";
   $listobject->querystring .= "      GROUP BY a.lrseg, a.srclu ";
   $listobject->querystring .= "    ) as a ";
   $listobject->querystring .= " WHERE b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and b.thisyear = $thisyear ";
   $listobject->querystring .= "    and b.luname = a.srclu ";
   $listobject->querystring .= "    and b.lrseg = a.lrseg ";
   $listobject->querystring .= "    and $bsubcond ";
   $listobject->querystring .= " ) as a ";
   $listobject->querystring .= " where scen_lrsegs.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and scen_lrsegs.luname = a.luname ";
   $listobject->querystring .= "    and scen_lrsegs.lrseg = a.lrseg ";
   $listobject->querystring .= "    and scen_lrsegs.thisyear = $thisyear ";
   $listobject->querystring .= "    and $ssubcond ";

   if ($debug) {
      $listobject->startSplit();
      print("<br>$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   if ($debug) {
      $split = $listobject->startSplit();
      print("Query Time: $split<br>");
      $listobject->showList();
   }


}

function rollBackLUBMPs($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $debug) {

   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subcond = " lrseg in ($sslist) ";
      $asubcond = " a.lrseg in ($sslist) ";
      $bsubcond = " b.lrseg in ($sslist) ";
      $ssubcond = " scen_lrsegs.lrseg in ($sslist) ";
   } else {
      $subcond = ' (1 = 1) ';
      $asubcond = ' (1 = 1) ';
      $bsubcond = ' (1 = 1) ';
      $ssubcond = ' (1 = 1) ';
   }
   #-- add back to the source land use
   $listobject->querystring = " UPDATE scen_lrsegs set luarea = a.revertedarea ";
   $listobject->querystring .= " from  ( ";
   $listobject->querystring .= " SELECT b.lrseg, b.luname, (b.luarea + a.chgarea) as revertedarea ";
   $listobject->querystring .= " FROM scen_lrsegs as b, ";
   $listobject->querystring .= "    ( select a.lrseg, a.srclu, sum(a.chgarea) as chgarea ";
   $listobject->querystring .= "      from scen_bmp_luchghist as a, bmp_subtypes as c  ";
   $listobject->querystring .= "      WHERE a.scenarioid = $scenarioid ";
   $listobject->querystring .= "         and a.bmpname = c.bmpname ";
   $listobject->querystring .= "         and c.typeid = $typeid ";
   $listobject->querystring .= "         and a.thisyear = $thisyear ";
   $listobject->querystring .= "         and $asubcond ";
   $listobject->querystring .= "      GROUP BY a.lrseg, a.srclu ";
   $listobject->querystring .= "    ) as a ";
   $listobject->querystring .= " WHERE b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and b.thisyear = $thisyear ";
   $listobject->querystring .= "    and b.luname = a.srclu ";
   $listobject->querystring .= "    and b.lrseg = a.lrseg ";
   $listobject->querystring .= "    and $bsubcond ";
   $listobject->querystring .= " ) as a ";
   $listobject->querystring .= " where scen_lrsegs.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and scen_lrsegs.luname = a.luname ";
   $listobject->querystring .= "    and scen_lrsegs.lrseg = a.lrseg ";
   $listobject->querystring .= "    and scen_lrsegs.thisyear = $thisyear ";
   $listobject->querystring .= "    and $ssubcond ";

   if ($debug) {
      $listobject->startSplit();
      print("<br>$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   if ($debug) {
      $split = $listobject->startSplit();
      print("Query Time: $split<br>");
      $listobject->showList();
   }


   #-- subtract from the destination land use
   $listobject->querystring = " UPDATE scen_lrsegs set luarea = a.revertedarea ";
   $listobject->querystring .= " from ( ";
   $listobject->querystring .= " SELECT b.lrseg, b.luname, ";
   $listobject->querystring .= "    CASE ";
   $listobject->querystring .= "       WHEN (a.chgarea > b.luarea) THEN 0.0";
   $listobject->querystring .= "       ELSE (b.luarea - a.chgarea)";
   $listobject->querystring .= "    END as revertedarea ";
   $listobject->querystring .= " FROM scen_lrsegs as b, ";
   $listobject->querystring .= "    ( select a.lrseg, a.destlu, sum(a.chgarea) as chgarea ";
   $listobject->querystring .= "      from scen_bmp_luchghist as a, bmp_subtypes as c  ";
   $listobject->querystring .= "      WHERE a.scenarioid = $scenarioid ";
   $listobject->querystring .= "         and a.bmpname = c.bmpname ";
   $listobject->querystring .= "         and c.typeid = $typeid ";
   $listobject->querystring .= "         and a.thisyear = $thisyear ";
   $listobject->querystring .= "         and $asubcond ";
   $listobject->querystring .= "      GROUP BY a.lrseg, a.destlu ";
   $listobject->querystring .= "    ) as a ";
   $listobject->querystring .= " WHERE b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and b.thisyear = $thisyear ";
   $listobject->querystring .= "    and b.luname = a.destlu ";
   $listobject->querystring .= "    and b.lrseg = a.lrseg ";
   $listobject->querystring .= "    and $bsubcond ";
   $listobject->querystring .= " ) as a ";
   $listobject->querystring .= " where scen_lrsegs.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and scen_lrsegs.luname = a.luname ";
   $listobject->querystring .= "    and scen_lrsegs.lrseg = a.lrseg ";
   $listobject->querystring .= "    and scen_lrsegs.thisyear = $thisyear ";
   $listobject->querystring .= "    and $ssubcond ";

   if ($debug) {
      $listobject->startSplit();
      print("<br>$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   if ($debug) {
      $split = $listobject->startSplit();
      print("Query Time: $split<br>");
      $listobject->showList();
   }

   #  delete this record now that it is rolled back
   deleteLUCHangeBMPHistory($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $debug);


}

function deleteLUChangeBMPHistory($listobject, $projectid, $scenarioid, $thisyear, $subsheds, $typeid, $debug) {

   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subcond = " lrseg in ($sslist) ";
      $asubcond = " a.lrseg in ($sslist) ";
      $bsubcond = " b.lrseg in ($sslist) ";
      $ssubcond = " scen_lrsegs.lrseg in ($sslist) ";
   } else {
      $subcond = ' (1 = 1) ';
      $asubcond = ' (1 = 1) ';
      $bsubcond = ' (1 = 1) ';
      $ssubcond = ' (1 = 1) ';
   }

   #  delete this record now that it is rolled back
   $listobject->querystring = " DELETE FROM scen_bmp_luchghist  ";
   $listobject->querystring .= " WHERE scenarioid = $scenarioid ";
   $listobject->querystring .= "    and thisyear in ( $thisyear ) ";
   $listobject->querystring .= "    and $subcond ";
   $listobject->querystring .= "    and bmpname in ( ";
   $listobject->querystring .= "       select bmpname from bmp_subtypes where ( ( typeid = $typeid ) or ($typeid = -1) )";
   $listobject->querystring .= "          and projectid = $projectid ";
   $listobject->querystring .= "    ) ";

   if ($debug) {
      $listobject->startSplit();
      print("<br>$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   if ($debug) {
      $split = $listobject->startSplit();
      print("Query Time: $split<br>");
      $listobject->showList();
   }

}
?>