<?php

   function makeTempWorkFromScen ($listobject, $projectid, $subsheds, $thisyear, $scenarioid, $def_nm_planbase, $defopttarg, $defmaxtarg, $debug) {
      # stashes the  contents of current projects working tables in
      # temporary tables in order  to optimize for  speed,
      # simplify the readabililty of sub-queries
      # eliminate the need for the use of projectid matches

       $prevdebug = $listobject->debug;
       $listobject->debug = $debug;

       $sublucolumns = "sublu, luname, nmluname, luarea, subshedid, thisyear, maxp, maxn, total_n, total_p, pct_nm, nrate, prate, nm_planbase, legume_n, uptake_n, uptake_p, optn, optp, maxnrate, maxprate, projectid";


      if (strlen($subsheds) > 0) {
         $subshedlist = "'" . join("','", split(',', $subsheds)) . "'";
         $ssc = " subshedid in ($subshedlist)";
         $yssc = " inputyields.subshedid in ($subshedlist)";
         $assc = " a.subshedid in ($subshedlist)";
      } else {
         $ssc = '(1 = 1)';
         $yssc = '(1 = 1)';
         $assc = '(1 = 1)';
      }

      # get n fixation curves
      $error = setUpYieldTables($listobject, $scenarioid, $thisyear, $subsheds, $def_nm_planbase, $defopttarg, $defmaxtarg, $debug);

      if ($error) {
         print("<b>Error:</b> No yield records are available for the selected area/year.  You must create yield records for these segments. Yield records are created/uploaded in the land use modeule.<br>");
         return $error;
      }

      print("&nbsp;&nbsp;&nbsp;&nbsp; Creating Base Land-Use/Crop Info table <br>");
      # create an out table, insure that each entry has a unique sublu, since these are
      # not referenced in any way, they are OK to be generated here on the fly
      $listobject->querystring = " create temp table worksublu ( scenarioid integer,";
      $listobject->querystring .= " subshedid varchar(64), sublu SERIAL, legume_n float8, ";
      $listobject->querystring .= " luarea float8, total_n float8, total_p float8, ";
      $listobject->querystring .= " luname varchar(255), nmluname varchar(255), nm_planbase integer, ";
      $listobject->querystring .= " nrate float8, prate float8, ";
      $listobject->querystring .= " optn float8, optp float8, ";
      $listobject->querystring .= " n_urratio float8, p_urratio float8, ";
      $listobject->querystring .= " maxnrate float8, maxprate float8, ";
      $listobject->querystring .= " uptake_n float8, uptake_p float8, ";
      $listobject->querystring .= " mean_needn float8, mean_needp float8, mean_uptn float8, mean_uptp float8, ";
      $listobject->querystring .= " thisyear integer, projectid integer, ";
      $listobject->querystring .= " maxn float8, maxp float8, pct_nm float8 )";
      $listobject->performQuery();
      #print("$listobject->querystring ; <br>");

      #$listobject->querystring = "create temp table rawsublu as ";
      # do not interpolate
      $listobject->querystring = " insert into worksublu (scenarioid, subshedid, luname, luarea,  ";
      $listobject->querystring .= "      thisyear, nmluname, maxp, maxn, total_n, total_p, pct_nm, nrate, ";
      $listobject->querystring .= "      prate, nm_planbase, legume_n, uptake_n, uptake_p, ";
      $listobject->querystring .= "      mean_needn, mean_needp, mean_uptn, mean_uptp, ";
      $listobject->querystring .= "      optn, optp, maxnrate, maxprate, n_urratio, p_urratio, projectid) ";
      $listobject->querystring .= "   select a.scenarioid, a.subshedid, a.luname, a.luarea, ";
      $listobject->querystring .= "      a.thisyear, ''::varchar(6) as nmluname, ";
      $listobject->querystring .= "      b.maxp, b.maxn, b.total_n, b.total_p, 0.0 as pct_nm, b.nrate, ";
      $listobject->querystring .= "      b.prate, b.nm_planbase, b.legume_n, b.uptake_n, b.uptake_p, ";
      $listobject->querystring .= "      b.mean_needn, b.mean_needp, b.mean_uptn, b.mean_uptp, ";
      $listobject->querystring .= "      b.optn, b.optp, b.maxnrate, b.maxprate, b.n_urratio, b.p_urratio, ";
      $listobject->querystring .= "      $projectid::integer as projectid ";
      $listobject->querystring .= "   from  ";
      $listobject->querystring .= "   ( select scenarioid, subshedid, luname, thisyear, sum(luarea) as luarea ";
      $listobject->querystring .= "     from scen_lrsegs ";
      $listobject->querystring .= "     WHERE $ssc ";
      $listobject->querystring .= "        and scenarioid = $scenarioid ";
      # use this year only
      $listobject->querystring .= "        and thisyear = $thisyear ";
      $listobject->querystring .= "     GROUP by scenarioid, subshedid, luname, thisyear ";
      # old - did not look to interpolate between years, missed some data
      #$listobject->querystring .= "   ) as a left outer join inputyields as b ";
      # new - looks to interpolate between years, missed some data
      $listobject->querystring .= "   ) as a left outer join interpyields as b ";
      $listobject->querystring .= "   ON ( b.scenarioid = $scenarioid ";
      $listobject->querystring .= "      and a.subshedid = b.subshedid ";
      $listobject->querystring .= "      and a.luname = b.luname ";
      $listobject->querystring .= "      and a.thisyear = b.thisyear ) ";
      $listobject->performQuery();
      #print("$listobject->querystring ; <br>");

      if ($debug) { print("$listobject->querystring ; <br>"); }


      # no longer do this, since the user can control interpolation from the interface
      #print("&nbsp;&nbsp;&nbsp;&nbsp; Interpolating Base Land-Use/Crop Info table for $thisyear <br>");
      #subluInterp($listobject, $projectid, $thisyear, 'rawsublu', 'worksublu', $debug, 0);

      /*
      $listobject->querystring = "select sum(luarea) from worksublu ";
      $listobject->performQuery();
      $listobject->showList();
      */


      $listobject->querystring = "select count(*) as numsublu from worksublu";
      $listobject->performQuery();
      $numsublu = $listobject->getRecordValue(1,'numsublu');
      if ($numsublu == 0) {
         print("There are no subwatershed parameters for the requested subwatershed ID(s)");
         die;
      }
      if ($debug) { print("$listobject->querystring ; <br>"); }

      print("&nbsp;&nbsp;&nbsp;&nbsp; Creating Miscellanious Working Tables <br>");
      # create a column for parent landuse, for use in derived landuses such as
      # acres under nutrient mangament. These parent landuseids (planduseid) will be
      # used to connect these new derived landuses to pre-existing distributions
      $listobject->querystring = "create temp table worklanduses as ";
      $listobject->querystring .= "   select *, landuseid as planduseid from landuses where ";
      $listobject->querystring .= "   projectid = $projectid ";
      if ($debug) { print(" $listobject->querystring ; <br>"); }
      $listobject->performQuery();

/* # No longer Used
      $listobject->querystring = "create temp table worksublumult as ";
      $listobject->querystring .= "   select luname, nmluname, luarea, subshedid, maxp, maxn, total_n, total_p, pct_nm, nrate, prate, nm_planbase, legume_n, uptake_n, uptake_p, optn, optp, maxnrate, maxprate from subluparams where ";
      $listobject->querystring .= "   projectid = $projectid ";
      $listobject->querystring .= "   $ssc";
      $listobject->querystring .= "   and paramtype = 'multiplier'";
      $listobject->performQuery();
*/

      $listobject->querystring = "create temp table worksubluoffset as ";
      $listobject->querystring .= "   select $sublucolumns from subluparams where ";
      $listobject->querystring .= "   projectid = $projectid ";
      $listobject->querystring .= "   AND $ssc";
      $listobject->querystring .= "   and paramtype = 'offset'";
      $listobject->performQuery();


      print("&nbsp;&nbsp;&nbsp;&nbsp; Creating Distribution table <br>");
      $listobject->querystring = "create temp table workdistro as ";
      $listobject->querystring .= "   select a.subshedid, b.distroid, b.spreadid, b.sourceid, b.sourcetype, ";
      $listobject->querystring .= "      b.landuseid, b.distroname, b.jan, b.feb, b.mar, b.apr, b.may, b.jun, ";
      $listobject->querystring .= "      b.jul, b.aug, b.sep, b.oct, b.nov, b.dec, b.pct_need ";
      $listobject->querystring .= "   from (select subshedid from worksublu group by subshedid) as a, monthlydistro as b ";
      $listobject->querystring .= "   where b.projectid = $projectid ";
      if ($debug) { print(" $listobject->querystring ; <br>"); }
      $listobject->performQuery();


      # now, update any custom distributions that have been passed in via local_apply table
      # this should be enhanced soon to include the interpolation of values in the local_apply table
      # between years. As it is, this will only affect the values if an exact match on "thisyear" exists
      # in the local_apply table.
      print(" Retrieving application distributions. <br>");
      $listobject->querystring = "select count(*) as numsublu from local_apply";
      $listobject->querystring .= "    where thisyear = $thisyear ";
      $listobject->querystring .= "    and scenarioid = $scenarioid ";
      $listobject->querystring .= "    and $ssc ";
      $listobject->performQuery();
      $numsublu = $listobject->getRecordValue(1,'numsublu');
      if ($numsublu == 0) {
         print("There are no distribution parameters for the requested subwatershed ID(s)");
         die;
      }
     # applyInterp($listobject, $scenarioid, $thisyear, $subsheds, 'workapply', $debug);
/*
      $listobject->querystring = "select * from workapply where subshedid = '51165'";
      print(" $listobject->querystring ; <br>");
      $listobject->performQuery();
      $listobject->showList();
*/

      $listobject->querystring = " update workdistro set ";
      $listobject->querystring .= "    jan = a.jan, ";
      $listobject->querystring .= "    feb = a.feb, ";
      $listobject->querystring .= "    mar = a.mar, ";
      $listobject->querystring .= "    apr = a.apr, ";
      $listobject->querystring .= "    may = a.may, ";
      $listobject->querystring .= "    jun = a.jun, ";
      $listobject->querystring .= "    jul = a.jul, ";
      $listobject->querystring .= "    aug = a.aug, ";
      $listobject->querystring .= "    sep = a.sep, ";
      $listobject->querystring .= "    oct = a.oct, ";
      $listobject->querystring .= "    nov = a.nov, ";
      $listobject->querystring .= "    dec = a.dec, ";
      $listobject->querystring .= "    pct_need = a.need_pct ";
      # using local applications table
      $listobject->querystring .= " from local_apply as a, ";
      # not using interpolated local applications table
      # $listobject->querystring .= " from workapply as a, ";
      $listobject->querystring .= "    worklanduses as b, spreadtype as c ";
      $listobject->querystring .= " where workdistro.subshedid = a.subshedid ";
      $listobject->querystring .= "    and workdistro.landuseid = b.landuseid ";
      $listobject->querystring .= "    and workdistro.spreadid = c.spreadid ";
      $listobject->querystring .= "    and a.source_type = c.shortname ";
      $listobject->querystring .= "    and a.luname = b.hspflu ";
      $listobject->querystring .= "    and a.thisyear = $thisyear ";
      $listobject->querystring .= "    and a.scenarioid = $scenarioid ";
      $listobject->querystring .= "    and $assc ";
      if ($debug) { print(" $listobject->querystring ; <br>"); }
      $listobject->performQuery();


      print("&nbsp;&nbsp;&nbsp;&nbsp; Copying Source Tables <br>");
/* # No longer Used
      $listobject->querystring = "create temp table workdistrolu as ";
      $listobject->querystring .= "   select a.spreadid, b.hspflu as luname ";
      $listobject->querystring .= " from workdistro as a, worklanduses as b  ";
      $listobject->querystring .= "   where b.landuseid = a.landuseid ";
      $listobject->querystring .= "      and a.spreadid not in ( 2, 3, 4, 9 ) ";
      $listobject->querystring .= "   group by a.spreadid, b.hspflu ";
      $listobject->performQuery();
*/

      $listobject->querystring = "create temp table worksourcepolls as ";
      $listobject->querystring .= "   select * from sourcepollutants where ";
      $listobject->querystring .= "   projectid = $projectid ";
      $listobject->performQuery();

      $listobject->querystring = "create temp table worksources as ";
      $listobject->querystring .= "   select * from sources where ";
      $listobject->querystring .= "   projectid = $projectid ";
      $listobject->performQuery();

      $listobject->querystring = "create temp table worksourcetype as ";
      $listobject->querystring .= "   select * from sourceloadtype where ";
      $listobject->querystring .= "   projectid = $projectid ";
      $listobject->performQuery();

      # create a subshed population table, for the selected year
      # if this year is not an exact year, use the numerical mean of bounding years
      # if the selected year is outside of the date range, for one or more
      # sources, use the closest year


      #$subshedcolumns = "subshedid, sourceid, thisyear as popyear, sourcepop, src_citation";
      print("&nbsp;&nbsp;&nbsp;&nbsp; Copying Source Population Table <br>");
      #$listobject->querystring = "create temp table copysubsheds as ";
      # do not interpolate sources, assume the user has done this manually, or using interface routines
      $subshedcolumns = "subshedid, sourceid, thisyear, sourcepop, src_citation";
      $listobject->querystring = "create temp table worksubshed as ";
      $listobject->querystring .= "   select $subshedcolumns from scen_sourcepops where ";
      $listobject->querystring .= "   scenarioid = $scenarioid ";
      $listobject->querystring .= "   and thisyear = $thisyear ";
      $listobject->querystring .= "   AND $ssc ";
      if ($debug) { print(" $listobject->querystring ; <br>"); }
      $listobject->performQuery();
      #popInterp($listobject, $projectid, $thisyear, 'copysubsheds', $debug);


       $listobject->debug = $prevdebug;

   }


function setUpYieldTables($listobject, $scenarioid, $thisyear, $subsheds, $def_nm_planbase, $defopttarg, $defmaxtarg, $debug) {

   if (strlen($subsheds) > 0) {
      $subshedlist = "'" . join("','", split(',', $subsheds)) . "'";
      $ssc = " subshedid in ($subshedlist)";
      $yssc = " inputyields.subshedid in ($subshedlist)";
      $assc = " a.subshedid in ($subshedlist)";
   } else {
      $ssc = '(1 = 1)';
      $yssc = '(1 = 1)';
      $assc = '(1 = 1)';
   }

   # get n fixation curves
   # not yet implemented, these must already exist for the year needed
   /*
   $yieldkeys = 'subshedid,luname,scenarioid,projectid,nm_planbase';
   $yieldvals = 'maxn,maxp,total_acres,legume_n,uptake_n,uptake_p,total_n,total_p,nrate,prate,optn,optp,maxnrate,maxprate,mean_uptn,mean_uptp,n_urratio,p_urratio';
   $extrawhere = " inputyields.scenarioid = $scenarioid and $yssc ";

   genericMultiInterp($listobject, $thisyear, 'inputyields', 'interpyields', 'thisyear', $yieldkeys, $yieldvals, 3, 0.0, 1, -99999, $debug, $extrawhere);
   */
   # check for yield records
   $listobject->querystring = " select count(*) as numrecs from inputyields ";
   $listobject->querystring .= " where scenarioid = $scenarioid ";
   $listobject->querystring .= "    and $ssc ";
   $listobject->querystring .= "    and thisyear = $thisyear ";
   $listobject->performQuery();
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $numrecs = $listobject->getRecordValue(1,'numrecs');
   if ($numrecs == 0) {
      $error = 1;
      return;
   } else {
      $error = 0;
      # create temp table with yields
      $listobject->querystring = " select * into temp table interpyields from inputyields ";
      $listobject->querystring .= " where scenarioid = $scenarioid ";
      $listobject->querystring .= "    and $ssc ";
      $listobject->querystring .= "    and thisyear = $thisyear ";
      if ($debug) { print("$listobject->querystring ; <br>"); }
      $listobject->performQuery();
      return $error;
   }

   /*

      $yieldkeys = 'subshedid,luname,scenarioid,projectid';
      $yieldvals = 'maxn,maxp,total_acres,legume_n,uptake_n,uptake_p,total_n,total_p,';
      $yieldvals .= 'nrate,prate,optn,optp,maxnrate,maxprate';
      $yieldvals .= ',mean_uptn,mean_uptp,n_urratio,p_urratio,mean_needn,mean_needp,';
      $yieldvals .= 'dc_pct,n_fix,high_uptp,';
      $yieldvals .= 'high_uptn,high_needp,high_needn,targ_needn,targ_needp,';
      $yieldvals .= 'targ_uptp,targ_uptn,nm_planbase,optyieldtarget,maxyieldtarget';
      $extrawhere = " inputyields.scenarioid = $scenarioid and $yssc ";

      # uses inputyields table directly
      #genericMultiInterp($listobject, $thisyear, 'inputyields', 'interpyields', 'thisyear', $yieldkeys, $yieldvals, 3, 0.0, 1, -99999, $debug, $extrawhere);
      # uses temporary table
      genericMultiInterp($listobject, $thisyear, 'tmp_yields', 'interpyields', 'thisyear', $yieldkeys, $yieldvals, 3, 0.0, 1, -99999, $debug, $extrawhere);

      # nm_planbase should not be interpolated, since it is an integer. It should be set to the most recent
      # reported value.
      # expects 2 tables to be created by genericMultiInterp(): interpyields_prev, interpyields_next
      # first, reset all
      $listobject->querystring = " update interpyields set nm_planbase = -99999, optyieldtarget = -99999, maxyieldtarget = -99999 ";
      $listobject->performQuery();
      if ($debug) { print("$listobject->querystring ; <br>"); }

      # next copy exact matches
      $listobject->querystring = " update interpyields set nm_planbase = a.nm_planbase, ";
      $listobject->querystring .= "    optyieldtarget = a.optyieldtarget, ";
      $listobject->querystring .= "    maxyieldtarget = a.maxyieldtarget ";
      # uses inputyields table directly
      #$listobject->querystring .= " from inputyields as a ";
      # uses temporary table
      $listobject->querystring .= " from tmp_yields as a ";
      $listobject->querystring .= " where interpyields.nm_planbase = -99999 ";
      $listobject->querystring .= "    and interpyields.subshedid = a.subshedid ";
      $listobject->querystring .= "    and interpyields.luname = a.luname ";
      $listobject->querystring .= "    and a.nm_planbase <> -99999 ";
      $listobject->querystring .= "    and a.scenarioid = $scenarioid ";
      $listobject->querystring .= "    and a.thisyear = $thisyear ";
      $listobject->performQuery();
      if ($debug) { print("$listobject->querystring ; <br>"); }

      # then, copy previous years
      $listobject->querystring = " update interpyields set nm_planbase = a.nm_planbase, ";
      $listobject->querystring .= "    optyieldtarget = a.optyieldtarget,  ";
      $listobject->querystring .= "    maxyieldtarget = a.maxyieldtarget ";
      $listobject->querystring .= " from interpyields_prev as a ";
      $listobject->querystring .= " where interpyields.nm_planbase = -99999 ";
      $listobject->querystring .= "    and interpyields.subshedid = a.subshedid ";
      $listobject->querystring .= "    and interpyields.luname = a.luname ";
      $listobject->querystring .= "    and a.nm_planbase <> -99999 ";
      $listobject->performQuery();
      # debug
      if ($debug) { print("$listobject->querystring ; <br>"); }

      # then, copy following years
      $listobject->querystring = " update interpyields set nm_planbase = a.nm_planbase, ";
      $listobject->querystring .= "    optyieldtarget = a.optyieldtarget, ";
      $listobject->querystring .= "    maxyieldtarget = a.maxyieldtarget ";
      $listobject->querystring .= " from interpyields_next as a ";
      $listobject->querystring .= " where interpyields.nm_planbase = -99999 ";
      $listobject->querystring .= "    and interpyields.subshedid = a.subshedid ";
      $listobject->querystring .= "    and interpyields.luname = a.luname ";
      $listobject->querystring .= "    and a.nm_planbase <> -99999 ";
      $listobject->performQuery();
      # debug
      if ($debug) { print("$listobject->querystring ; <br>"); }

      # finally, set to default value
      $listobject->querystring = " update interpyields set nm_planbase = $def_nm_planbase, ";
      $listobject->querystring .= "    optyieldtarget = $defopttarg, ";
      $listobject->querystring .= "    maxyieldtarget = $defmaxtarg ";
      $listobject->querystring .= " where nm_planbase = -99999 ";
      $listobject->performQuery();
      # debug
      if ($debug) { print("$listobject->querystring ; <br>"); }
   }
   */
}


function createInputFiles ($listobject, $scenarioid, $outdir, $projectid, $theseyears, $subsheds) {

   $subsheds = '';
   $ssdel = '';
   $thisdate = date('r',time());


   if (strlen($grouplist) > 0) {
      $subsheds .= $grouplist;
      $ssdel = ',';
   }

   if (strlen($pollutants) > 0) {
      $pollclause = " b.pollutanttype in ($pollutants) ";
      $bpollclause = " b.typeid in ($pollutants) ";
      $polllist = " d.typeid in ($pollutants)  ";
   } else {
      $pollclause = ' (1 = 1) ';
      $polllist = ' (1 = 1) ';
      $bpollclause = " (1 = 1) ";
   }

   if (strlen($subshedids) > 0) {
      $subsheds .= "$ssdel" . "$subshedids";
   }


   if ( (strlen($subshedids) > 0) or ($grouplist == '') ) {
      array_push($groupings, $subshedids);
   }

   $ssclause = '';
   $assclause = '';
   if (count(split(',', $subsheds)) > 0) {
      $slist = join("','", split(',', $subsheds));
      $ssclause = "and subshedid in ('$slist')";
      $assclause = "and a.subshedid in ('$slist')";
   }

   $outfile = "$outdir/test.accum.txt";
   if ($debug) {
      print("Outfile name: $outfile <br>");
   }

   if (count(split(',', $showyears)) > 0) {
      $yearar = split(',', $showyears);
   } else {
      $yearar = array($showyears);
   }

   $listobject->querystring = "select * from scenario where scenarioid = $runid";
   $listobject->performQuery();
   $runname = $listobject->getRecordValue(1,'shortname');

   foreach ($yearar as $thisyear) {

      print("<hr><h3>Model Input Files by Group:</h3><br><br>");

      foreach ($groupings as $thissubgroup) {


         $subwatersheds = $thissubgroup;

         if (strlen($subwatersheds) > 0) {
            $wc = "where subshedid in ($subwatersheds)";
            $nwc = " subshedid in ($subwatersheds)";
            $anwc = " a.subshedid in ($subwatersheds)";
         } else {
            $wc = '';
            $nwc = ' 1 = 1 ';
            $anwc = ' 1 = 1 ';
         }

         $listobject->querystring = "select groupname from groupings where projectid = $projectid and subwatersheds = '$subwatersheds'";
         $listobject->performQuery();
         $groupname = $listobject->getRecordValue(1,'groupname');

         print("<hr><b>$groupname </b><br>");


         if ($listobject->tableExists("temp_lrsegs") ) {

            $listobject->querystring = " drop table temp_lrsegs ";
            $listobject->performQuery();

            $listobject->querystring = " drop table temp_scensource ";
            $listobject->performQuery();

         }

         print("Querying segment list for $groupname <br>");
         $debug = 0;
         # this should speed up some querying since the matching of sub-watersheds is a laborious process
         $listobject->querystring = "create temp table temp_lrsegs as select a.thisyear, ";
         $listobject->querystring .= "    a.lrseg, a.landseg, a.riverseg, ";
         $listobject->querystring .= "    a.subshedid, b.hspflu as luname,  ";
         $listobject->querystring .= "    CASE ";
         $listobject->querystring .= "       WHEN sum(a.luarea) is null THEN 0.0 ";
         $listobject->querystring .= "       ELSE sum(a.luarea) ";
         $listobject->querystring .= "    END as luarea ";
         $listobject->querystring .= " from scen_lrsegs as a left outer join landuses as b ";
         $listobject->querystring .= " on (a.luname = b.hspflu and b.hspflu <> '' and b.hspflu is not null) ";
         $listobject->querystring .= " where scenarioid = $runid ";
         $listobject->querystring .= " and $anwc ";
         $listobject->querystring .= " and a.thisyear = $thisyear  ";
         $listobject->querystring .= "  group by a.thisyear, a.lrseg, a.landseg, a.riverseg, a.subshedid, b.hspflu ";
         if ($debug) { print("$listobject->querystring ; <br>"); }
         $listobject->performQuery();

         $listobject->querystring = "  create temp table temp_landsegs as ";
         $listobject->querystring .= " select a.thisyear, a.landseg, ";
         $listobject->querystring .= "    a.subshedid, b.hspflu as luname,  ";
         $listobject->querystring .= "    CASE ";
         $listobject->querystring .= "       WHEN sum(a.luarea) is null THEN 0.0 ";
         $listobject->querystring .= "       ELSE sum(a.luarea) ";
         $listobject->querystring .= "    END as luarea ";
         $listobject->querystring .= " from scen_lrsegs as a left outer join landuses as b ";
         $listobject->querystring .= " on (a.luname = b.hspflu and b.hspflu <> '' and b.hspflu is not null) ";
         $listobject->querystring .= " where scenarioid = $runid ";
         $listobject->querystring .= " and $anwc ";
         $listobject->querystring .= " and a.thisyear = $thisyear  ";
         $listobject->querystring .= "  group by a.thisyear, a.landseg, a.subshedid, b.hspflu ";
         if ($debug) { print("$listobject->querystring ; <br>"); }
         print("$listobject->querystring<br>");
         $listobject->performQuery();

         print("Querying sources for $groupname <br>");
         # this should speed up some querying since the matching of sub-watersheds is a laborious process
         $listobject->querystring = "create temp table temp_scensource as select * ";
         $listobject->querystring .= " from scen_sourceperunitarea ";
         $listobject->querystring .= " where scenarioid = $runid ";
         $listobject->querystring .= " and $nwc ";
         $listobject->querystring .= " and thisyear = $thisyear ";
         if ($debug) { print("$listobject->querystring ; <br>"); }
         $listobject->performQuery();
         $debug = 0;


         if ($makeseptic) {

            makeSepticInputFiles($listobject, $projectid, $scenarioid, $thisyear, $allsegs, $constits, $debug);

         }



         if ($makemanure) {
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


            $debug = 0;
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
            if (!$loadwater) {
               # don't include water landuses in this data set
               $listobject->querystring .= "    AND a.luname not in (select hspflu from landuses where landusetype = $watertype and projectid = $projectid) ";
            }
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
            $listobject->querystring .= " where $polllist ";
            $listobject->querystring .= "    and d.typeid in ( ";
            $listobject->querystring .= "       select pollutanttype from scen_monperunitarea ";
            $listobject->querystring .= "       group by pollutanttype ) ";
            # bad bad bad - only takes constituents for which at least
            # one subshed in the query has received a constituent

            $listobject->querystring .= "    and a.luname not in (select hspflu from landuses where landusetype = $watertype) ";

            # only get landuses that COULD receive manure applications
            # this includes land uses that have zero acreage currently
            $listobject->querystring .= "      and a.luname in ( select b.hspflu ";
            $listobject->querystring .= "      from monthlydistro as a, landuses as b  ";
            $listobject->querystring .= "      where b.landuseid = a.landuseid ";
            # manure spreading distros
            $listobject->querystring .= "      and a.spreadid in ( $spread_manure ) ";
            $listobject->querystring .= "      and a.projectid = $projectid ";
            $listobject->querystring .= "      and b.projectid = $projectid ";
            $listobject->querystring .= "      group by b.hspflu ) ";

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

            if ($hayadjust <> 1.0) {
               # manual adjustment of hay inputs - this is a hack - RWB
               $listobject->querystring = " update manure_out set JAN = JAN * $hayadjust, ";
               $listobject->querystring .= " FEB = FEB * $hayadjust, ";
               $listobject->querystring .= " MAR = MAR * $hayadjust,";
               $listobject->querystring .= " APR = APR * $hayadjust,";
               $listobject->querystring .= " MAY = MAY * $hayadjust,";
               $listobject->querystring .= " JUN = JUN * $hayadjust,";
               $listobject->querystring .= " JUL = JUL * $hayadjust,";
               $listobject->querystring .= " AUG = AUG * $hayadjust,";
               $listobject->querystring .= " SEP = SEP * $hayadjust,";
               $listobject->querystring .= " OCT = OCT * $hayadjust,";
               $listobject->querystring .= " NOV = NOV * $hayadjust,";
               $listobject->querystring .= " DEC = DEC * $hayadjust";
               $listobject->querystring .= " from temp_lrsegs as a ";
               $listobject->querystring .= " where a.luname in ('hyw', 'nhy') ";
               $listobject->querystring .= "   and manure_out.lseg = a.landseg ";
               $listobject->querystring .= "   and manure_out.lu = a.luname ";
               $listobject->querystring .= "   and a.luarea > 0 ";
               #print("$listobject->querystring ; <br>");
               $listobject->performQuery();
            }

            if ($debug) { print("$listobject->querystring ; <br>"); }
            /*
            $listobject->querystring = " select * from manure_out where lu = 'nhi' ";
            $listobject->performQuery();
            $listobject->showList();
            */

            $listobject->querystring = " select lseg, lu, constituent, jan, feb, mar, apr, may, jun, jul, aug, sep, oct, nov, dec from manure_out ";
            $listobject->querystring .= " order by lseg, lu, constituent ";
            if ($debug) { print("$listobject->querystring ; <br>"); }
            $listobject->performQuery();
            $debug = 0;


            # format for output
            $outarr = nestArraySprintf("%6s,%3s,%4s,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f", $listobject->queryrecords);

            #print_r($outarr);

            print("<b>Manure for $thisyear</b><br>");
            if ($makecsv) {
               $colnames = array(array_keys($listobject->queryrecords[0]));

               $filename = "manure_$runname" . "_$thisyear.csv";
               putDelimitedFile("$outdir/$filename",$colnames,",",1,'unix');

               putArrayToFilePlatform("$outdir/$filename", $outarr,0,'unix');
               print("<a href='$outurl/$filename'>Download NPS Manure Input table for $thisyear</a><br>");
            } else {
               $listobject->showlist();
            }
         } /* end Manure */

         if ($dofert) {

            # now do the fertilizer apps

            if ($listobject->tableExists("fert_out") ) {
               $listobject->querystring = "drop table fert_out ";
               $listobject->performQuery();
               $listobject->querystring = "drop table fert_defaults ";
               $listobject->performQuery();
            }


            $listobject->querystring = "create temp table fert_defaults as ";
            $listobject->querystring .= "       select a.landseg, a.subshedid, a.luname, ";
            $listobject->querystring .= "          CASE  ";
            $listobject->querystring .= "             WHEN a.luarea = 0 THEN $nullval.0  ";
            $listobject->querystring .= "             ELSE 0.0  ";
            $listobject->querystring .= "          END as defval,  ";
            $listobject->querystring .= "          b.typeid as pollutanttype ";
            $listobject->querystring .= "       from temp_landsegs as a, pollutanttype as b ";
            $listobject->querystring .= "       where $bpollclause ";
            $listobject->querystring .= "          and a.luname in  ";
            $listobject->querystring .= "          ( select luname from scen_monthlydistro ";
            $listobject->querystring .= "            where scenarioid = $runid ";
            $listobject->querystring .= "               and spreadid in (7,11,8) ";
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
#              print("$listobject->querystring ; <br>");
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
#              print("$listobject->querystring ; <br>");
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
#               print("$listobject->querystring ; <br>");
            $listobject->performQuery();

            if ($hayadjust <> 1.0) {
               # manual adjustment of hay inputs - this is a hack - RWB
               $listobject->querystring = " update fert_out set JAN = JAN * $hayadjust, ";
               $listobject->querystring .= " FEB = FEB * $hayadjust, ";
               $listobject->querystring .= " MAR = MAR * $hayadjust,";
               $listobject->querystring .= " APR = APR * $hayadjust,";
               $listobject->querystring .= " MAY = MAY * $hayadjust,";
               $listobject->querystring .= " JUN = JUN * $hayadjust,";
               $listobject->querystring .= " JUL = JUL * $hayadjust,";
               $listobject->querystring .= " AUG = AUG * $hayadjust,";
               $listobject->querystring .= " SEP = SEP * $hayadjust,";
               $listobject->querystring .= " OCT = OCT * $hayadjust,";
               $listobject->querystring .= " NOV = NOV * $hayadjust,";
               $listobject->querystring .= " DEC = DEC * $hayadjust";
               $listobject->querystring .= " from temp_landsegs as a ";
               $listobject->querystring .= " where a.luname in ('hyw', 'nhy') ";
               $listobject->querystring .= "   and fert_out.lseg = a.landseg ";
               $listobject->querystring .= "   and fert_out.lu = a.luname ";
               $listobject->querystring .= "   and a.luarea > 0 ";
               $listobject->performQuery();
            }

            if ($hackurban) {

               if (in_array($thisyear, array_keys($urbanfert) ) ) {
                  $urbfert = $urbanfert[$thisyear];

                  # manual adjustment of urban inputs - this is a hack - RWB
                  $listobject->querystring = " update fert_out set JAN = 0.0, ";
                  $listobject->querystring .= " FEB =0.0, ";
                  $listobject->querystring .= " MAR = 0.35 * $urbfert * $urbannh,";
                  $listobject->querystring .= " APR = 0.35 * $urbfert * $urbannh,";
                  $listobject->querystring .= " MAY = 0.0,";
                  $listobject->querystring .= " JUN = 0.0,";
                  $listobject->querystring .= " JUL = 0.0,";
                  $listobject->querystring .= " AUG = 0.0,";
                  $listobject->querystring .= " SEP = 0.0,";
                  $listobject->querystring .= " OCT = 0.3 * $urbfert * $urbannh,";
                  $listobject->querystring .= " NOV = 0.0,";
                  $listobject->querystring .= " DEC = 0.0";
                  $listobject->querystring .= " from temp_landsegs as a ";
                  $listobject->querystring .= " where a.luname in ('puh', 'pul') ";
                  $listobject->querystring .= "   and fert_out.lseg = a.landseg ";
                  $listobject->querystring .= "   and fert_out.lu = a.luname ";
                  $listobject->querystring .= "   and fert_out.constituent = 'nh3n' ";
                  $listobject->querystring .= "   and a.luarea > 0 ";
                  $listobject->performQuery();

                  # manual adjustment of hay inputs - this is a hack - RWB
                  $listobject->querystring = " update fert_out set JAN = 0.0, ";
                  $listobject->querystring .= " FEB =0.0, ";
                  $listobject->querystring .= " MAR = 0.35 * $urbfert * $urbanno,";
                  $listobject->querystring .= " APR = 0.35 * $urbfert * $urbanno,";
                  $listobject->querystring .= " MAY = 0.0,";
                  $listobject->querystring .= " JUN = 0.0,";
                  $listobject->querystring .= " JUL = 0.0,";
                  $listobject->querystring .= " AUG = 0.0,";
                  $listobject->querystring .= " SEP = 0.0,";
                  $listobject->querystring .= " OCT = 0.3 * $urbfert * $urbanno,";
                  $listobject->querystring .= " NOV = 0.0,";
                  $listobject->querystring .= " DEC = 0.0";
                  $listobject->querystring .= " from temp_landsegs as a ";
                  $listobject->querystring .= " where a.luname in ('puh', 'pul') ";
                  $listobject->querystring .= "   and fert_out.lseg = a.landseg ";
                  $listobject->querystring .= "   and fert_out.lu = a.luname ";
                  $listobject->querystring .= "   and fert_out.constituent = 'no3n' ";
                  $listobject->querystring .= "   and a.luarea > 0 ";
                  $listobject->performQuery();
               }
            }

            # alfalfa adjust - HSPF has a bad habit of changing the output files if there are zero
            # applications on a land use, and since alfalfa may at times receive nitrogen inputs
            # this little hack is introduced
            $listobject->querystring = " update fert_out set ";
            $listobject->querystring .= " JAN = JAN + 0.01 ";
            $listobject->querystring .= " WHERE lu in ('nal', 'alf')";
            $listobject->performQuery();
            if ($debug) {print("$listobject->querystring ; <br>"); }

            # now, format for output
            $listobject->querystring = " select lseg, lu, constituent, jan, feb, mar, apr, may, jun, jul, aug,  ";
            $listobject->querystring .= " sep, oct, nov, dec from fert_out ";
            $listobject->querystring .= " order by lseg, lu, constituent ";
            $listobject->performQuery();
#               print("$listobject->querystring ; <br>");

            $outarr = nestArraySprintf("%6s,%3s,%4s,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f", $listobject->queryrecords);

            print("<b>Fertilizer for $thisyear</b><br>");
            if ($makecsv) {
               $colnames = array(array_keys($listobject->queryrecords[0]));

               $filename = "fert_$runname" . "_$thisyear.csv";
               putDelimitedFile("$outdir/$filename",$colnames,",",1,'unix');
               putArrayToFilePlatform("$outdir/$filename", $outarr,0,'unix');
               print("<a href='$outurl/$filename'>Download NPS Fertilizer Input table for $thisyear</a><br>");
            } else {
               $listobject->showlist();
            }



         } /* end do fert */

         if ($legume) {

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
            $listobject->querystring .= "             WHEN a.luarea = 0 THEN $nullval.0  ";
            $listobject->querystring .= "             ELSE 0.0  ";
            $listobject->querystring .= "          END as defval,  ";
            $listobject->querystring .= "          b.typeid as pollutanttype, ";
            $listobject->querystring .= "          b.shortname ";
            $listobject->querystring .= "       from temp_landsegs as a, pollutanttype as b ";
            $listobject->querystring .= "       where $bpollclause ";
            $listobject->querystring .= "          and b.typeid in ($legume_nut) ";
            $listobject->querystring .= "          and a.luname in  ";
            $listobject->querystring .= "          ( select luname from n_fixation ";
            $listobject->querystring .= "            where scenarioid = $runid ";
            $listobject->querystring .= "            group by luname ";
            $listobject->querystring .= "          ) ";
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
            $listobject->querystring .= "    scen_legume_n as b ";
            $listobject->querystring .= "    on ( ";
            $listobject->querystring .= "       a.subshedid = b.subshedid ";
            $listobject->querystring .= "       and a.luname = b.luname ";
            $listobject->querystring .= "       and a.shortname = b.constit ";
            $listobject->querystring .= "       and b.scenarioid = $runid ";
            $listobject->querystring .= "       and b.thisyear = $thisyear ";
            $listobject->querystring .= "    ) left outer join pollutanttype as c ";
            $listobject->querystring .= "    on ( ";
            $listobject->querystring .= "       a.pollutanttype = c.typeid ";
            $listobject->querystring .= "    )  ";
            $listobject->querystring .= "group by a.landseg, a.luname, a.defval, c.shortname";
            $listobject->querystring .= " order by a.landseg, a.luname, c.shortname ";
            $listobject->performQuery();
#              print("$listobject->querystring ; <br>");


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
            $listobject->performQuery();
#               print("$listobject->querystring ; <br>");

            if ($hayadjust <> 1.0) {
               # manual adjustment of hay inputs - this is a hack - RWB
               $listobject->querystring = " update legume_out set JAN = JAN * $hayadjust, ";
               $listobject->querystring .= " FEB = FEB * $hayadjust, ";
               $listobject->querystring .= " MAR = MAR * $hayadjust,";
               $listobject->querystring .= " APR = APR * $hayadjust,";
               $listobject->querystring .= " MAY = MAY * $hayadjust,";
               $listobject->querystring .= " JUN = JUN * $hayadjust,";
               $listobject->querystring .= " JUL = JUL * $hayadjust,";
               $listobject->querystring .= " AUG = AUG * $hayadjust,";
               $listobject->querystring .= " SEP = SEP * $hayadjust,";
               $listobject->querystring .= " OCT = OCT * $hayadjust,";
               $listobject->querystring .= " NOV = NOV * $hayadjust,";
               $listobject->querystring .= " DEC = DEC * $hayadjust";
               $listobject->querystring .= " from temp_landsegs as a ";
               $listobject->querystring .= " where a.luname in ('hyw', 'nhy') ";
               $listobject->querystring .= "   and legume_out.lseg = a.landseg ";
               $listobject->querystring .= "   and legume_out.lu = a.luname ";
               $listobject->querystring .= "   and a.luarea > 0 ";
               $listobject->performQuery();
            }

            $listobject->querystring = "select lseg, lu, constituent, jan, feb, mar, apr, may, jun, jul, aug, sep, oct, nov, dec from legume_out order by lseg, lu";
            $listobject->performQuery();


            $outarr = nestArraySprintf("%6s,%3s,%4s,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f,%3.2f", $listobject->queryrecords);


#               $listobject->querystring = "select * from legume_out order by lseg, lu";
#               $listobject->performQuery();


            print("<b>Maximum Legume Fixed N for $thisyear</b><br>");
            if ($makecsv) {
               $colnames = array(array_keys($listobject->queryrecords[0]));

               $filename = "legume_$runname" . "_$thisyear.csv";
               putDelimitedFile("$outdir/$filename",$colnames,",",1,'unix');

               putArrayToFilePlatform("$outdir/$filename", $outarr,0,'unix');
               print("<a href='$outurl/$filename'>Download NPS Legume Input table for $thisyear</a><br>");
            } else {
               $listobject->showlist();
            }

         } /* end make legume nps inputs */




         if ($makelu) {

            if ($listobject->tableExists("model_inputlu") ) {
               $listobject->querystring = "drop table model_inputlu ";
               $listobject->performQuery();
            }

            # use crosstab function on a temp table excerpted from the scen table

            $listobject->querystring = "create temp table model_inputlu as select riverseg, ";
            $listobject->querystring .= " landseg, luname, luarea from scen_lrsegs where $nwc ";
            #$listobject->querystring .= " and riverseg <> '' and riverseg <> 'unknown' ";
            $listobject->querystring .= " and scenarioid = $runid ";
            $listobject->querystring .= " and projectid = $projectid ";
            $listobject->querystring .= " and thisyear = $thisyear ";
            $listobject->performQuery();


            $listobject->querystring = doGenericCrossTab ($listobject, 'model_inputlu', 'riverseg, landseg', 'luname', 'luarea');
           # print("$listobject->querystring ; <br>");
            $listobject->performQuery();

            print("<b>Landuse for $thisyear</b><br>");
            if ($makecsv) {
               $colnames = array(array_keys($listobject->queryrecords[0]));

               $filename = "land_use_$runname" . "_$thisyear.csv";
               putDelimitedFile("$outdir/$filename",$colnames,",",1,'unix');

               putDelimitedFile("$outdir/$filename", $listobject->queryrecords, ',',0,'unix');
               print("<a href='$outurl/$filename'>Download Land Use table</a><br>");
            } else {
               $listobject->showlist();
            }

         }

         if ($makeuptakecurves) {

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
            $listobject->querystring .= " and a.luname not in ('pas', 'trp', 'npa') ";
            $listobject->querystring .= " and b.hspflu = a.luname ";
            $listobject->querystring .= " and a.scenarioid = $runid ";
            $listobject->querystring .= " and a.thisyear = $thisyear ";
            $listobject->querystring .= " and b.projectid = $projectid ";
            $listobject->performQuery();

            $listobject->querystring = "update lu_uptakes set jan = a.jan, feb = a.feb, ";
            $listobject->querystring .= " mar = a.mar, apr = a.apr, may = a.may, jun = a.jun, ";
            $listobject->querystring .= " jul = a.jul, aug = a.aug, sep = a.sep, oct = a.oct, ";
            $listobject->querystring .= " nov = a.nov, dec = a.dec ";
            $listobject->querystring .= " from cb_uptake as a ";
            $listobject->querystring .= " where lu_uptakes.subshedid = a.stcofips ";
            $listobject->querystring .= " and a.scenarioid = $runid ";
            $listobject->querystring .= " and lu_uptakes.luname = a.luname ";
            $listobject->querystring .= " and a.thisyear = $thisyear";
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
            $listobject->querystring .= " and a.scenarioid = $runid ";
            $listobject->querystring .= " and a.thisyear = $thisyear ";
            $listobject->performQuery();

            if ($makecsv) {
               # temporarily disabled
               #print("<a href='$outurl/$runid.$thisyear.tar.gz'>All outfiles as a single tar</a><hr>");
            }


            $listobject->querystring = "( select a.landseg as lseg, a.luname as lu, 'nitr' as constituent, b.jan, b.feb, b.mar, b.apr, b.may, b.jun, b.jul, b.aug, b.sep, b.oct, b.nov, b.dec from temp_lrsegs as a, lu_uptakes as b where a.subshedid = b.subshedid and a.luname = b.luname order by a.subshedid )";
            $listobject->querystring .= " UNION (select a.landseg as lseg, a.luname as lu, 'phos' as constituent, b.jan, b.feb, b.mar, b.apr, b.may, b.jun, b.jul, b.aug, b.sep, b.oct, b.nov, b.dec from temp_lrsegs as a, lu_uptakes as b where a.subshedid = b.subshedid and a.luname = b.luname order by a.subshedid) ";
            $listobject->performQuery();

            $outarr = nestArraySprintf("%3s,%3s,%4s,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f", $listobject->queryrecords);

            #print_r($outarr);
            #print("<br>");

            #print("$listobject->querystring<br>");

            if ($makecsv and (count($listobject->queryrecords) > 0) ) {

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

            } else {
               if (count($listobject->queryrecords) > 0) {
                  $listobject->showlist();

                  print("Uptake Mass Balance:<br>");
                  # show summary of uptakes, to make sure that all are equal to 1.0
                  $listobject->querystring = "select luname, max(jan + feb + mar + apr+ may+ jun+ jul+ aug+ sep+ oct+ nov+ dec) as maxuptake, min(jan+ feb+ mar+ apr+ may+ jun+ jul+ aug+ sep+ oct+ nov+ dec) as minuptake from lu_uptakes where 1 = 1 and (a.jan+ a.feb+ a.mar+ a.apr+ a.may+ a.jun+ a.jul+ a.aug+ a.sep+ a.oct+ a.nov+ a.dec) <> ( $nullval * 12.0) $ssclause group by luname";
                  $listobject->performQuery();
                  $listobject->showList();
               }
            }


            # append the fils to the output tar
            # temporarily disabled
            if ($makecsv) {
               #$tar->create($outfiles) or die ("Could not create tar archive<br>");
            }
         }


         if ($makecanopycurves) {

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
            print("$listobject->querystring<br>");
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
            print("$listobject->querystring<br>");
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
            print("$listobject->querystring<br>");
            $listobject->performQuery();

            $listobject->querystring = "  select luname ";
            $listobject->querystring .= " from local_apply ";
            $listobject->querystring .= " where scenarioid = $scenarioid ";
            $listobject->querystring .= "    and curvetype in ( $res_canopy ) ";
            $listobject->querystring .= " group by luname ";
            print("$listobject->querystring<br>");
            $listobject->performQuery();
            $lrecs = $listobject->queryrecords;

            foreach ($lrecs as $lus) {
               $ln = $lus['luname'];

               $listobject->querystring = "  select * ";
               $listobject->querystring .= " from tmp_cover ";
               $listobject->querystring .= " where luname = '$ln' ";
               $listobject->querystring .= " order by land ";
               $listobject->performQuery();

               $outarr = nestArraySprintf("%3s,%3s,%4s,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f,%1.4f", $listobject->queryrecords);

               #print_r($outarr);
               #print("<br>");

               #print("$cropobject->querystring<br>");

               if ($makecsv and (count($listobject->queryrecords) > 0) ) {

                  $colnames = array(join(",",array_keys($listobject->queryrecords[0])));
                  $filename = "land_cover_$ln" . "_v5sed_$thisyear.csv";
                  putArrayToFilePlatform("$outdir/$filename", $colnames,1,'unix');
                  putArrayToFilePlatform("$outdir/$filename", $outarr,0,'unix');
                  print("<a href='$outurl/$filename'>Cover Curve for $ln $thisyear</a><br>");
                  array_push($outfiles, "$outdir/$filename");

               }
            }


            # append the fils to the output tar
            # temporarily disabled
            if ($makecsv) {
               #$tar->create($outfiles) or die ("Could not create tar archive<br>");
            }
         }


         if ($needonly) {
            # crop need tables - Annual total uptake

            if ($listobject->tableExists("totalneed_out") ) {
               $listobject->querystring = "drop table totalneed_out ";
               $listobject->performQuery();
            }

            print("<b>Total uptake rate per month/year for </b> $groupname ($subwatersheds):<br>");

            $listobject->querystring = " create temp table totalneed_out as ";
            $listobject->querystring .= " ( select a.landseg as lseg, a.luname as lu, a.luarea,";
            $listobject->querystring .= " 'nitr' as constituent, b.uptake_n as max_uptake ";
            $listobject->querystring .= " from (select subshedid, landseg, luname, max(luarea) as luarea ";
            $listobject->querystring .= "    from temp_lrsegs as a ";
            $listobject->querystring .= "    group by subshedid, landseg, luname ";
            $listobject->querystring .= " ) as a left outer join ";
            $listobject->querystring .= " scen_subsheds as b ";
            $listobject->querystring .= " on ( a.subshedid = b.subshedid ";
            $listobject->querystring .= "    and a.luname = b.luname ";
            $listobject->querystring .= "    and b.scenarioid = $runid ";
            $listobject->querystring .= "    and b.thisyear = $thisyear ";
            $listobject->querystring .= " and b.projectid = $projectid ) ";
            $listobject->querystring .= " order by a.landseg ";
            $listobject->querystring .= " ) UNION ";
            $listobject->querystring .= " (select a.landseg as lseg, a.luname as lu, a.luarea, ";
            $listobject->querystring .= "  'phos' as constituent, uptake_p as max_uptake ";
            $listobject->querystring .= "  from ";
            $listobject->querystring .= "     (select subshedid, landseg, luname, ";
            $listobject->querystring .= "       max(luarea) as luarea ";
            $listobject->querystring .= "     from temp_lrsegs as a ";
            $listobject->querystring .= "     group by subshedid, landseg, luname ";
            $listobject->querystring .= "     ) as a left outer join ";
            $listobject->querystring .= " scen_subsheds as b ";
            $listobject->querystring .= " on ( a.subshedid = b.subshedid ";
            $listobject->querystring .= "    and a.luname = b.luname ";
            $listobject->querystring .= "    and b.scenarioid = $runid ";
            $listobject->querystring .= "    and b.thisyear = $thisyear ";
            $listobject->querystring .= " and b.projectid = $projectid ) ";
            $listobject->querystring .= " order by a.landseg )";
          #  print("$listobject->querystring ; <br>");


            $listobject->performQuery();

            $listobject->querystring = " update totalneed_out set max_uptake = $nullval ";
            $listobject->querystring .= " where luarea <= 0 or max_uptake is null";
            $listobject->performQuery();

            $listobject->querystring = " select lseg, lu, constituent, max_uptake from totalneed_out ";
            $listobject->querystring .= " where lu in ( select hspflu from landuses  ";
            $listobject->querystring .= " where landusetype in ($crop_lutypes) ";
            $listobject->querystring .= "    and projectid = $projectid ";
            $listobject->querystring .= "    and hspflu not in ('pas', 'trp', 'npa') ) ";
            $listobject->querystring .= " order by lseg ";

            $listobject->performQuery();


           # print("$listobject->querystring ; <br>");

           # $listobject->performQuery();

            $outarr = $listobject->queryrecords;

            if ($makecsv) {
               $colnames = array(array_keys($outarr[0]));
               $pname = ereg_replace("[^a-z^A-Z^0-9]",'',$groupname);
               $filename = "max_uptake" . "_$runname" . "_$thisyear.csv";
               putDelimitedFile("$outdir/$filename", $colnames, ',',1,'unix');
               putDelimitedFile("$outdir/$filename", $outarr, ',',0,'unix');
               print("<a href='$outurl/$filename'>Download Maximum Uptake File for $thisyear</a><br>");
            } else {
               $listobject->showlist();
            }


         }

         if ($makeuptakeappsum) {
            # creates a table comparing applied loads versus uptake

            # custom mon applied table, accepts tables to sum as input, pass all tables except
            # for septic table, since it is weighted by low intensity, but actually applied
            # to water.
            $loadtables = array('monapplied', 'vpasture_applied', 'manure_applied', 'nmmanure_applied', 'excessmanure_applied', 'urbn_applied', 'urbp_applied', 'agn_applied', 'agnm_applied', 'agp_applied', 'agpm_applied');
            sumAllMonAppliedLoads2($listobject, $projectid, 1, $loadtables, $debug);
            # normalize on a per acre basis
            createMonPerUnitArea($listobject, $projectid, 1, $debug);

            if ($listobject->tableExists('compareapps') ) {
               $listobject->querystring = " drop table compareapps ";
               $listobject->performQuery();
            }

            # create app comparison table starting with nitrogen
            $listobject->querystring = " create temp table compareapps as ";
            $listobject->querystring .= " select b.subshedid, ";
            $listobject->querystring .= " b.luname, b.pollutanttype, ";
            $listobject->querystring .= " d.shortname as constituent, ";
            $listobject->querystring .= " b.annualapplied, a.uptake_n as cropneed, ";
         # old, now calculated for legume adjustments
            $listobject->querystring .= " a.optn as optapp , ";
         # new, shows the total crop need including presumed legume fixation
            $listobject->querystring .= " (a.nrate * a.uptake_n) as legapp, ";
            $listobject->querystring .= " a.nrate as optrate, ";
            $listobject->querystring .= " b.luarea, 0.0 as legume_n, 0.0 as excess,";
            $listobject->querystring .= " 0.0 as leg_total,";
            $listobject->querystring .= " 0.0 as legume_pct_app, ";
            $listobject->querystring .= " 0.0 as total_pct_app ";
            $listobject->querystring .= " from worksublu as a, montotal as b,  ";
            $listobject->querystring .= "     pollutanttype as d ";
            $listobject->querystring .= " where b.pollutanttype = 1 ";
            $listobject->querystring .= "  and a.subshedid = b.subshedid";
            $listobject->querystring .= "  and a.luname = b.luname ";
            $listobject->querystring .= "  and d.typeid = 1 ";
            $listobject->querystring .= " and a.uptake_n > 0 ";
            $listobject->performQuery();

            if ($listobject->tableExists('legume_sum') ) {
               $listobject->querystring = " update compareapps set legume_n = ";
               $listobject->querystring .= " a.annualapplied  ";
               $listobject->querystring .= " from legume_sum as a  ";
               $listobject->querystring .= " where compareapps.subshedid = a.subshedid ";
               $listobject->querystring .= "    and compareapps.luname = a.lu ";
               $listobject->performQuery();
            }

            # scale to actual optimal crop need
            $listobject->querystring = " update compareapps set optapp = ";
            $listobject->querystring .= " optapp / optrate ";
            $listobject->querystring .= " where optrate > 0 ";
            $listobject->performQuery();

            # scale to actual optimal crop need
            $listobject->querystring = " update compareapps set leg_total = ";
            $listobject->querystring .= " annualapplied + legume_n ";
            $listobject->performQuery();

            $listobject->querystring = " update compareapps set excess = ";
            $listobject->querystring .= " (annualapplied + legume_n - cropneed), ";
            $listobject->querystring .= " legume_pct_app = ";
            $listobject->querystring .= " 100.0*(annualapplied + legume_n)/cropneed, ";
            $listobject->querystring .= " total_pct_app = ";
            $listobject->querystring .= " 100.0*annualapplied/optapp ";
            $listobject->querystring .= " where optapp > 0  ";
            $listobject->querystring .= "    and cropneed > 0  ";
            $listobject->performQuery();

            # now insert phosphorus
            $listobject->querystring = "  insert into compareapps (subshedid, luname, ";
            $listobject->querystring .= "  constituent, annualapplied, cropneed, ";
            $listobject->querystring .= "  luarea, excess, total_pct_app, optapp, ";
            $listobject->querystring .= "  legapp, optrate, pollutanttype ) ";
            $listobject->querystring .= "  select b.subshedid, b.luname, d.shortname, ";
            $listobject->querystring .= " b.annualapplied, a.uptake_p, b.luarea,  ";
            $listobject->querystring .= " (b.annualapplied - a.uptake_p), ";
            $listobject->querystring .= " 100*(b.annualapplied)/a.uptake_p,  ";
            # inserts optp twice, since legapp is the same as optapp for phosphorus
            $listobject->querystring .= " a.optp, a.optp, a.prate, b.pollutanttype ";
            $listobject->querystring .= " from montotal as b,  ";
            $listobject->querystring .= "      worksublu as a, pollutanttype as d ";
            $listobject->querystring .= " where b.pollutanttype = 2 and ";
            $listobject->querystring .= " a.subshedid = b.subshedid ";
            $listobject->querystring .= " and a.luname = b.luname and d.typeid = 2 ";
            $listobject->querystring .= " and a.uptake_p > 0 ";
            $listobject->performQuery();
#            print("$listobject->querystring ; <br>");


            $listobject->querystring = "select luname from compareapps group by luname";
            $listobject->performQuery();

            $alllanduses = $listobject->queryrecords;


            if ($listobject->tableExists('comparecrosstab') ) {

               $listobject->querystring = " drop table comparecrosstab ";
               $listobject->performQuery();

               $listobject->querystring = " create temp table comparecrosstab ";
               $listobject->querystring .= " as select subshedid from compareapps ";
               $listobject->querystring .= " group by subshedid ";
               $listobject->performQuery();

            } else {

               $listobject->querystring = " create temp table comparecrosstab ";
               $listobject->querystring .= " as select subshedid from compareapps ";
               $listobject->querystring .= " group by subshedid ";
               $listobject->performQuery();

            }

            foreach ($alllanduses as $thislanduse) {

               $luname = $thislanduse['luname'];

               #print("Adding $luname ... <br>");

               $listobject->querystring = " alter table comparecrosstab ";
               $listobject->querystring .= " add column area_$luname float8 ";
               $listobject->performQuery();

               $listobject->querystring = " alter table comparecrosstab ";
               $listobject->querystring .= " add column optn_$luname float8 ";
               $listobject->performQuery();

               $listobject->querystring = " alter table comparecrosstab ";
               $listobject->querystring .= " add column legn_$luname float8 ";
               $listobject->performQuery();

               $listobject->querystring = " alter table comparecrosstab ";
               $listobject->querystring .= " add column legnpct_$luname float8 ";
               $listobject->performQuery();

               $listobject->querystring = " alter table comparecrosstab ";
               $listobject->querystring .= " add column appn_$luname float8 ";
               $listobject->performQuery();

               $listobject->querystring = " alter table comparecrosstab ";
               $listobject->querystring .= " add column npct_$luname float8 ";
               $listobject->performQuery();

               $listobject->querystring = " alter table comparecrosstab ";
               $listobject->querystring .= " add column nrate_$luname float8 ";
               $listobject->performQuery();

               $listobject->querystring = " alter table comparecrosstab ";
               $listobject->querystring .= " add column optp_$luname float8 ";
               $listobject->performQuery();

               $listobject->querystring = " alter table comparecrosstab ";
               $listobject->querystring .= " add column appp_$luname float8 ";
               $listobject->performQuery();

               $listobject->querystring = " alter table comparecrosstab ";
               $listobject->querystring .= " add column ppct_$luname float8 ";
               $listobject->performQuery();

               $listobject->querystring = " alter table comparecrosstab ";
               $listobject->querystring .= " add column prate_$luname float8 ";
               $listobject->performQuery();

               $listobject->querystring = " update comparecrosstab ";
               $listobject->querystring .= " set optn_$luname = a.optapp, ";
               $listobject->querystring .= "  area_$luname = a.luarea, ";
               $listobject->querystring .= "  appn_$luname = a.annualapplied, ";
               # DONT count legume n
               $listobject->querystring .= "  npct_$luname =  a.total_pct_app, ";
               # DO count legume n
               $listobject->querystring .= "  legn_$luname =  a.legapp, ";
               $listobject->querystring .= "  legnpct_$luname =  a.legume_pct_app, ";
               $listobject->querystring .= "  nrate_$luname =  a.optrate ";
               $listobject->querystring .= " from compareapps as a ";
               $listobject->querystring .= "    where a.pollutanttype = 1 ";
               $listobject->querystring .= "       and a.subshedid = comparecrosstab.subshedid ";
               $listobject->querystring .= "       and a.luname = '$luname' ";
               $listobject->performQuery();

#                  print("$listobject->querystring<br>");

               $listobject->querystring = " update comparecrosstab ";
               $listobject->querystring .= " set optp_$luname =  a.optapp, ";
               $listobject->querystring .= "  appp_$luname =  a.annualapplied, ";
               $listobject->querystring .= "  ppct_$luname =  a.total_pct_app, ";
               $listobject->querystring .= "  prate_$luname =  a.optrate ";
               $listobject->querystring .= " from compareapps as a ";
               $listobject->querystring .= "    where a.pollutanttype = 2 ";
               $listobject->querystring .= "       and a.subshedid = comparecrosstab.subshedid ";
               $listobject->querystring .= "       and a.luname = '$luname' ";
               $listobject->performQuery();

            }

            $listobject->querystring = " select * from comparecrosstab ";
            $listobject->querystring .= " order by subshedid ";
            $listobject->performQuery();

            if ($makecsv and (count($listobject->queryrecords) > 0)) {
               $colnames = array(array_keys($listobject->queryrecords[0]));
               $fname = "$outdir/app_vs_need" . "$runid" . "_$thisyear.txt";
               $flink = "$outurl/app_vs_need" . "$runid" . "_$thisyear.txt";
               putDelimitedFile($fname, $colnames, ',',1,'unix');
               putDelimitedFile($fname, $listobject->queryrecords, ',',0,'unix');
               print("<a href='$flink'>Download Comparison Table</a><br>");
            } else {
               $listobject->showlist();
            }

         }



         if ($domasslinks) {

            # now do mass-links



            print("<b>Generating Masslinks for $thisyear</b><br>");
            # now, format for output
            $listobject->querystring = " select a.riverseg as rseg, a.landseg as lseg, c.luname, b.vortexname as constit,  ";
            $listobject->querystring .= "    c.passthru as masslink ";
            $listobject->querystring .= " from temp_lrsegs as a, pollutanttype as b, scen_masslinks as c ";
            $listobject->querystring .= " where c.scenarioid = $runid ";
            $listobject->querystring .= "    and a.lrseg = c.lrseg ";
            $listobject->querystring .= "    and c.thisyear = $thisyear ";
            $listobject->querystring .= "    and c.luname = a.luname ";
            $listobject->querystring .= "    and b.typeid = c.constit ";
            $listobject->querystring .= "    and $bpollclause ";
            $listobject->querystring .= " order by a.riverseg, a.landseg, c.luname, b.vortexname ";
            $listobject->performQuery();
            print("$listobject->querystring ; <br>");

            $outarr = nestArraySprintf("%16s,%6s,%4s,%4s,%3.6f", $listobject->queryrecords);

            if ($makecsv) {
               $colnames = array(array_keys($listobject->queryrecords[0]));

               $filename = "bmp_$runname" . "_$thisyear.csv";
               putDelimitedFile("$outdir/$filename",$colnames,",",1,'unix');
               putArrayToFilePlatform("$outdir/$filename", $outarr,0,'unix');
               print("<a href='$outurl/$filename'>Download Mass-link Input table for $thisyear</a><br>");
            } else {
               $listobject->showlist();
            }



         } /* end do mass-links */



      } /* end group loop */
   } /* end multi-year loop */
}


function applyInterp($listobject, $scenarioid, $thisyear, $subsheds, $outtable, $debug) {
   # copysubshed is the subshed info from this scenarioid

   $valcols = 'jan,feb,mar,apr,may,jun,jul,aug,sep,oct,nov,dec,need_pct';
   $valtables = array();

   $keycols = 'luname,source_type,subshedid,scenarioid';

   if (strlen($subsheds) > 0) {
      $subshedlist = "'" . join("','", split(',', $subsheds)) . "'";
      $ssc = " local_apply.subshedid in ($subshedlist)";
   } else {
      $ssc = '(1 = 1)';
   }
   $extrawhere = " local_apply.scenarioid = $scenarioid and $ssc ";

   genericMultiInterp($listobject, $thisyear, 'local_apply', $outtable, 'thisyear', $keycols, $valcols, 3, 0.0, 1, -99999, $debug, $extrawhere);

}

function uptakeInterp($listobject, $scenarioid, $thisyear, $subsheds, $outtable, $debug) {
   # copysubshed is the subshed info from this scenarioid

   $valcols = 'jan,feb,mar,apr,may,jun,jul,aug,sep,oct,nov,dec';
   $valtables = array();

   $keycols = 'luname,cb_region,stcofips,scenarioid';

   if (strlen($subsheds) > 0) {
      $subshedlist = "'" . join("','", split(',', $subsheds)) . "'";
      $ssc = " cb_uptake.stcofips in ($subshedlist)";
   } else {
      $ssc = '(1 = 1)';
   }
   $extrawhere = " cb_uptake.scenarioid = $scenarioid and $ssc ";

   genericMultiInterp($listobject, $thisyear, 'cb_uptake', $outtable, 'thisyear', $keycols, $valcols, 3, 0.0, 1, -99999, $debug, $extrawhere);

}


function getGeneratedLoads($listobject, $scenarioid, $projectid, $thisyear, $subsheds, $debug) {

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

    $listobject->querystring = "create temp table all_loads as select a.subshedid, ";
    $listobject->querystring .= "a.sourceid, a.sourceclass, a.volatilization, a.storagedieoff, ";
    $listobject->querystring .= "a.sourcetypeid, a.pollutanttype, a.typeid, 0.0 as totalpop, ";
    $listobject->querystring .= " a.annualproduction, 0.0 as vconst, ";
    $listobject->querystring .= "(b.JAN*a.JAN + b.FEB*a.FEB + b.MAR*a.MAR ";
    $listobject->querystring .= " + b.APR*a.APR + b.MAY*a.MAY + b.JUN*a.JUN ";
    $listobject->querystring .= " + b.JUL*a.JUL + b.AUG*a.AUG + b.SEP*a.SEP ";
    $listobject->querystring .= " + b.OCT*a.OCT + b.NOV*a.NOV + b.DEC*a.DEC ) as annualproduced, ";
    $listobject->querystring .= "  0.0 as annualvolatilized, 0.0 as annualdieoff, 0.0 as annualapplied";
    $listobject->querystring .= "    from scen_sourcepollprod as a,";
    $listobject->querystring .= "       monthdays as b, lrseg_info as c ";
    $listobject->querystring .= "    where a.scenarioid = $scenarioid ";
    $listobject->querystring .= "       and a.thisyear = $thisyear ";
    $listobject->querystring .= "       and b.sourceclass = 4";
    $listobject->querystring .= "       $ssc ";

}
?>


