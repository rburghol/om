<?php

   function archiveRunData ($listobject, $projectid, $runid, $thisyear, $subsheds, $thisdate) {

   if (strlen( $subsheds) > 0) {
      $slist = join("','", split(',', $subsheds));
      $ssclause = "subshedid in ('$slist')";
      $assclause = "a.subshedid in ('$slist')";
      $bssclause = "b.subshedid in ('$slist')";
   } else {
      $ssclause = ' (1 = 1) ';
      $assclause = ' (1 = 1) ';
      $bssclause = ' (1 = 1) ';
   }

    # archive all calculated loads in a monperunitarea table
    $unarchsql = " delete from scen_monperunitarea where scenarioid = $runid ";
    $unarchsql .= " and $ssclause and thisyear = $thisyear and projectid = $projectid";

    $listobject->querystring = $unarchsql;
    #print("$listobject->querystring<br>");
    $listobject->performQuery();


    print("Archiving Monthly Total Application Rates<br>");
    $listobject->querystring = $archivesql;
    #print("$listobject->querystring<br>");
    $listobject->performQuery();

    # insert nitrogen
    $archivesql = "insert into scen_monperunitarea ( scenarioid, thisyear, projectid, rundate, ";
    $archivesql .= " subshedid, sublu, luname, luarea, landuseid, pollutanttype, ";
    $archivesql .= " apprate, uptake, max_app, opt_app, ";
    $archivesql .= " cap, annualapplied, JAN, FEB, MAR, APR, ";
    $archivesql .= " MAY, JUN, JUL, AUG, SEP, OCT, NOV, DEC, legume ) ";
    $archivesql .= " select $runid as scenarioid, $thisyear, ";
    $archivesql .= " $projectid as projectid, '$thisdate' as rundate, ";
    $archivesql .= " subshedid, sublu, luname, luarea, landuseid, pollutanttype, ";
    $archivesql .= " nrate, uptake_n, maxn, optn, ";
    $archivesql .= " ncap, annualapplied, JAN, FEB, MAR, APR, ";
    $archivesql .= " MAY, JUN, JUL, AUG, SEP, OCT, NOV, DEC, 0.0 from montotal ";
    $archivesql .= " where $ssclause ";
    $archivesql .= " and pollutanttype in (select typeid from pollutanttype where master_constit = 1) ";
    $archivesql .= " order by subshedid ";
    $listobject->querystring = $archivesql;
    #print("$listobject->querystring<br>");
    $listobject->performQuery();

    # insert P
    $archivesql = "insert into scen_monperunitarea ( scenarioid, thisyear, projectid, rundate, ";
    $archivesql .= " subshedid, sublu, luname, luarea, landuseid, pollutanttype, ";
    $archivesql .= " apprate, uptake, max_app, opt_app, ";
    $archivesql .= " cap, annualapplied, JAN, FEB, MAR, APR, ";
    $archivesql .= " MAY, JUN, JUL, AUG, SEP, OCT, NOV, DEC, legume ) ";
    $archivesql .= " select $runid as scenarioid, $thisyear, ";
    $archivesql .= " $projectid as projectid, '$thisdate' as rundate, ";
    $archivesql .= " subshedid, sublu, luname, luarea, landuseid, pollutanttype, ";
    $archivesql .= " prate, uptake_p, maxp, optp, ";
    $archivesql .= " pcap, annualapplied, JAN, FEB, MAR, APR, ";
    $archivesql .= " MAY, JUN, JUL, AUG, SEP, OCT, NOV, DEC, 0.0 from montotal ";
    $archivesql .= " where $ssclause ";
    $archivesql .= " and pollutanttype in (select typeid from pollutanttype where master_constit = 2) ";
    $archivesql .= " order by subshedid ";
    $listobject->querystring = $archivesql;
    #print("$listobject->querystring<br>");
    $listobject->performQuery();

    # insert alfalfa nitrogen uptake, this is bad, sorta
    # insert an entry for total N (1) and Organic N (3)
    # we set the legume equal to the uptake_n, which is good, since it will probably overfix, but some years
    # we will not get the optimum uptake, so this should balance out.
    # First, insert an entry for total N (1)
    $archivesql = "insert into scen_monperunitarea ( scenarioid, thisyear, projectid, rundate, ";
    $archivesql .= " subshedid, sublu, luname, luarea, landuseid, pollutanttype, ";
    $archivesql .= " apprate, uptake, max_app, opt_app, ";
    $archivesql .= " cap, annualapplied, legume, JAN, FEB, MAR, APR, ";
    $archivesql .= " MAY, JUN, JUL, AUG, SEP, OCT, NOV, DEC) ";
    $archivesql .= " select $runid as scenarioid, $thisyear, ";
    $archivesql .= " $projectid as projectid, '$thisdate' as rundate, ";
    # insert pollutant type of 1 for nitrogen
    $archivesql .= " subshedid, sublu, luname, luarea, landuseid, 1, ";
    $archivesql .= " nrate, uptake_n, maxn, optn, ";
    # set annunal applied to 0.0, and legume equal to plant uptake of nitrogen
    $archivesql .= " ncap, 0.0, uptake_n, 0.0, 0.0, 0.0, 0.0, ";
    $archivesql .= " 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0 from montotal ";
    $archivesql .= " where $ssclause ";
    # have to grab the entry made for total phosphorus cause there is not one for alfalfa
    $archivesql .= " and pollutanttype = 2 ";
    $archivesql .= " and luname in ( 'alf', 'nal') ";
    $archivesql .= " order by subshedid ";

    $listobject->querystring = $archivesql;
    #print("$listobject->querystring<br>");
    $listobject->performQuery();

    # Then, insert an entry for Organic N (3)
    $archivesql = "insert into scen_monperunitarea ( scenarioid, thisyear, projectid, rundate, ";
    $archivesql .= " subshedid, sublu, luname, luarea, landuseid, pollutanttype, ";
    $archivesql .= " apprate, uptake, max_app, opt_app, ";
    $archivesql .= " cap, annualapplied, legume, JAN, FEB, MAR, APR, ";
    $archivesql .= " MAY, JUN, JUL, AUG, SEP, OCT, NOV, DEC) ";
    $archivesql .= " select $runid as scenarioid, $thisyear, ";
    $archivesql .= " $projectid as projectid, '$thisdate' as rundate, ";
    # insert pollutant type of 1 for nitrogen
    $archivesql .= " subshedid, sublu, luname, luarea, landuseid, 3, ";
    $archivesql .= " nrate, uptake_n, maxn, optn, ";
    # set annunal applied to 0.0, and legume equal to plant uptake of nitrogen
    $archivesql .= " ncap, 0.0, uptake_n, 0.0, 0.0, 0.0, 0.0, ";
    $archivesql .= " 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0 from montotal ";
    $archivesql .= " where $ssclause ";
    # have to grab the entry made for phosphorus cause there is not one for alfalfa
    $archivesql .= " and pollutanttype = 2 ";
    $archivesql .= " and luname in ( 'alf', 'nal') ";
    $archivesql .= " order by subshedid ";

    $listobject->querystring = $archivesql;
    #print("$listobject->querystring<br>");
    $listobject->performQuery();


    $fixsql = "update scen_monperunitarea set legume = a.annualapplied ";
    $fixsql .= " from legume_fix as a ";
    $fixsql .= " where scen_monperunitarea.subshedid = a.subshedid";
    $fixsql .= "    and scen_monperunitarea.projectid = $projectid";
    $fixsql .= "    and scen_monperunitarea.thisyear = $thisyear ";
    $fixsql .= "    and scen_monperunitarea.scenarioid = $runid";
    $fixsql .= "    and ( scen_monperunitarea.pollutanttype = 1";
    $fixsql .= "         or scen_monperunitarea.pollutanttype = 7 )";
    $fixsql .= "    and scen_monperunitarea.luname = a.luname ";

    print("Appending Fixation Rates Total App Table<br>");
    $listobject->querystring = $fixsql;
    #print("$listobject->querystring<br>");
    $listobject->performQuery();




    # archive all calculated loads in a sourceperunitarea table
    $unarchsql = " delete from scen_sourceperunitarea where scenarioid = '$runid' ";
    $unarchsql .= " and $ssclause and thisyear = $thisyear and projectid = $projectid";

    $archivesql = "insert into scen_sourceperunitarea ( scenarioid, thisyear, projectid, rundate, ";
    $archivesql .= " subshedid, sublu, luname, luarea, landuseid, pollutanttype, ";
    $archivesql .= " sourceid, sourceclass, spreadid, ";
    $archivesql .= " annualapplied, JAN, FEB, MAR, APR, ";
    $archivesql .= " MAY, JUN, JUL, AUG, SEP, OCT, NOV, DEC ) ";
    $archivesql .= " select $runid as scenarioid, $thisyear, ";
    $archivesql .= " $projectid as projectid, '$thisdate' as rundate, ";
    $archivesql .= " subshedid, sublu, luname, luarea, landuseid, pollutanttype, ";
    $archivesql .= " sourceid, sourceclass, spreadid, ";
    $archivesql .= " annualapplied, JAN, FEB, MAR, APR, ";
    $archivesql .= " MAY, JUN, JUL, AUG, SEP, OCT, NOV, DEC from monsourceperunitarea ";
    $archivesql .= " where $ssclause ";
    $archivesql .= " order by subshedid ";


    $listobject->querystring = $unarchsql;
    #print("$listobject->querystring<br>");
    $listobject->performQuery();
    print("Archiving Monthly Total Application Rates by Source<br>");
    $listobject->querystring = $archivesql;
    #print("$listobject->querystring<br>");
    $listobject->performQuery();

    # archive information relating to the populations and general productions of sources
    archiveSourceInfo($listobject, $projectid, $runid, $thisyear, $subsheds, $thisdate);

    # archive the legume fixation table
    $unarchsql = " delete from scen_legume_n where scenarioid = $runid ";
    $unarchsql .= " and $ssclause and thisyear = $thisyear and projectid = $projectid";

    $archivesql = "insert into scen_legume_n ( subshedid, luname, ";
    $archivesql .= " projectid, scenarioid, rundate, thisyear, constit, pollutanttype, ";
    $archivesql .= " totaln, legume_n, jan, feb, mar, apr, may, jun, ";
    $archivesql .= " jul, aug, sep, oct, nov, dec ) ";
    $archivesql .= " select subshedid, luname, ";
    $archivesql .= " $projectid as projectid, $runid as scenarioid, ";
    $archivesql .= " '$thisdate'::timestamp as rundate, $thisyear, constituent, a.typeid, ";
    $archivesql .= " annualapplied, legume_n, jan, feb, mar, apr, may, jun, ";
    $archivesql .= " jul, aug, sep, oct, nov, dec ";
    $archivesql .= "    from legume_fix, pollutanttype as a ";
    $archivesql .= " where $ssclause ";
    $archivesql .= " and legume_fix.constituent = a.shortname ";
    $archivesql .= "    order by subshedid";

    print("Archiving Legume Values<br>");
    $listobject->querystring = $unarchsql;
#   print("$listobject->querystring<br>");
    $listobject->performQuery();
    $listobject->querystring = $archivesql;
#   print("$listobject->querystring<br>");
    $listobject->performQuery();


    # archive the septic loadings
    $unarchsql = " delete from scen_septic where scenarioid = $runid ";
    $unarchsql .= " and $ssclause and thisyear = $thisyear and projectid = $projectid";

    $archivesql = "insert into scen_septic ( subshedid, landseg, riverseg, thisyear, ";
    $archivesql .= " pollutanttype, septicload, rundate, projectid, scenarioid) ";
    $archivesql .= " select a.subshedid, a.landseg, a.riverseg, a.thisyear, b.pollutanttype, ";
    $archivesql .= " (a.luarea * b.annualapplied ) as septicload, ";
    $archivesql .= " '$thisdate'::timestamp as rundate, $projectid, $runid ";
    $archivesql .= "   from scen_lrsegs as a, sourceperunitarea as b ";
    $archivesql .= " where $assclause ";
    $archivesql .= " and a.luname = b.luname ";
    $archivesql .= " and a.thisyear = $thisyear ";
    $archivesql .= " and a.scenarioid = $runid ";
    $archivesql .= " and b.sourceclass = 10 ";
    $archivesql .= " and a.riverseg <> '' and a.riverseg <> 'unknown' ";
    $archivesql .= " and a.subshedid = b.subshedid ";
    $archivesql .= "    order by a.landseg";

    print("Archiving Septic Values<br>");
    $listobject->querystring = $unarchsql;
#    print("$listobject->querystring<br>");
    $listobject->performQuery();
    $listobject->querystring = $archivesql;
#    print("$listobject->querystring<br>");
    $listobject->performQuery();



    # archive the MONTHLY distributions
    $unarchsql = " delete from scen_monthlydistro where scenarioid = $runid ";
    $unarchsql .= " and $ssclause and thisyear = $thisyear ";

    $archivesql = "insert into scen_monthlydistro ( scenarioid,  distroid,  spreadid,  sourceid, ";
    $archivesql .= " sourcetype, subshedid,  luname,  jan,  feb,  mar,   ";
    $archivesql .= " apr,  may,  jun,  jul,  aug,  sep,  oct,   ";
    $archivesql .= " nov,  dec,  pct_need, thisyear, rundate ) ";
    $archivesql .= " select $runid,  a.distroid,  a.spreadid,  a.sourceid, ";
    $archivesql .= " a.sourcetype, a.subshedid,  b.hspflu,  a.jan,  a.feb, a. mar, ";
    $archivesql .= " a.apr,  a.may,  a.jun,  a.jul,  a.aug,  a.sep,  a.oct, ";
    $archivesql .= " a.nov,  a.dec,  a.pct_need, $thisyear, '$thisdate'::timestamp as rundate ";
    $archivesql .= "   from workdistro as a, worklanduses as b ";
    $archivesql .= " where $assclause ";
    $archivesql .= " and a.landuseid = b.landuseid ";
    $archivesql .= "    order by a.subshedid ";

    print("Archiving Monthly Distributions<br>");
    $listobject->querystring = $unarchsql;
#    print("$listobject->querystring<br>");
    $listobject->performQuery();
    $listobject->querystring = $archivesql;
#    print("$listobject->querystring<br>");
    $listobject->performQuery();


    print("Archiving Sub-watershed Information<br>");
    archiveSubsheds($listobject, $projectid, $runid, $thisyear, $subsheds, $thisdate, 'worksublu');
    print("&nbsp;%nbsp;&nbsp; Archiving Crop Information<br>");
    archiveCropYields($listobject, $projectid, $runid, $thisyear, $subsheds, $thisdate,'interpyields');



/*
    # archive the balances loadings
    $unarchsql = " delete from scen_eof_balance where scenarioid = $runid ";
    $unarchsql .= " and $ssclause and projectid = $projectid";

    $archivesql = "insert into scen_eof_balance(projectid, scenarioid, subshedid, ";
    $archivesql .= " luname, constit, total_in, max_uptake, balance ) ";
    $archivesql .= " select projectid, scenarioid, subshedid, luname, ";
    $archivesql .= " 'totn' as constit, avg(annualapplied + legume) as total_in, ";
    $archivesql .= " avg(uptake_n) as max_uptake, ";
    $archivesql .= " CASE ";
    $archivesql .= "    WHEN avg(annualapplied + legume - uptake_n) <= 0 THEN 0.0 ";
    $archivesql .= "    WHEN avg(annualapplied + legume) is null THEN 0.0 ";
    $archivesql .= "    ELSE avg(annualapplied + legume - uptake_n) ";
    $archivesql .= " END ";
    $archivesql .= " from scen_monperunitarea ";
    $archivesql .= " where pollutanttype = 1 ";
    $archivesql .= "   and scenarioid = 1 ";
    $archivesql .= "   and projectid = 65 ";
    # temporary kludge
    $archivesql .= "   and thisyear in (1982,1987, 1992, 1997, 2002) ";
    $archivesql .= " group by projectid, scenarioid, subshedid, luname ";

    print("Archiving EOF Values<br>");
    $listobject->querystring = $unarchsql;
#    print("$listobject->querystring<br>");
    $listobject->performQuery();
    $listobject->querystring = $archivesql;
#    print("$listobject->querystring<br>");
    $listobject->performQuery();

*/
   # calculate and store the crop nutrient application-uptake index
    print("Calculating and Storing Crop Nutrient Application-Uptake Distance Index (audindex)<br>");
   calculateApplicationUptakeDistanceIndex($listobject, $runid, $thisyear, 1, $subsheds);

}

function archiveLRSegs($listobject, $projectid, $runid, $thisyear, $subsheds, $thisdate, $srctable = 'cbcomposite') {

   if (strlen($subsheds) > 0) {
      $slist = join("','", split(',', $subsheds));
      $ssclause = "subshedid in ('$slist')";
   } else {
      $ssclause = '1 = 1';
   }

   # archive the lrseg features
   $unarchsql = " delete from scen_lrsegs where scenarioid = $runid ";
   $unarchsql .= " and $ssclause and thisyear = $thisyear ";


   $archivesql = "insert into scen_lrsegs ( subshedid, luname, riverseg, ";
   $archivesql .= " landseg, luarea, lrseg, ";
   $archivesql .= " projectid, scenarioid, rundate, thisyear) ";
   $archivesql .= " select subshedid, luname, riverseg, ";
   $archivesql .= " landseg, luarea, (riverseg || landseg), ";
   $archivesql .= " $projectid as projectid, $runid as scenarioid, ";
   $archivesql .= " '$thisdate'::timestamp as rundate, $thisyear as thisyear ";
   $archivesql .= "    from $srctable ";
   $archivesql .= " where $ssclause ";
   $archivesql .= "    order by subshedid";

   print("Archiving LRSeg Values<br>");
   $listobject->querystring = $unarchsql;
 #     print("$listobject->querystring<br>");
   $listobject->performQuery();
   $listobject->querystring = $archivesql;
  #    print("$listobject->querystring<br>");
   $listobject->performQuery();
}

function archiveSubsheds($listobject, $projectid, $runid, $thisyear, $subsheds, $thisdate, $srctable = 'worksublu') {

   if (strlen ($subsheds) > 0) {
      $sscount = count(split(',', $subsheds));
      print("Total Subsheds: $sscount <br>");
      $slist = join("','", split(',', $subsheds));
      $ssclause = "subshedid in ('$slist')";
   } else {
      $ssclause = '1 = 1';
   }

   # archive the lrseg features
   $unarchsql = " delete from scen_subsheds where scenarioid = $runid ";
   $unarchsql .= " and $ssclause and thisyear = $thisyear and projectid = $projectid";

   $archivesql = "insert into scen_subsheds ( subshedid, luname, luarea, uptake_n, ";
   $archivesql .= " mean_needn, mean_needp, mean_uptn, mean_uptp, n_urratio, p_urratio, ";
   $archivesql .= " uptake_p, optn, optp, maxn, maxp, nrate, prate, maxnrate, maxprate, ";
   $archivesql .= " projectid, scenarioid, rundate, thisyear) ";
   $archivesql .= " select subshedid, luname, luarea, uptake_n, ";
   $archivesql .= " mean_needn, mean_needp, mean_uptn, mean_uptp, n_urratio, p_urratio, ";
   $archivesql .= " uptake_p, optn, optp, maxn, maxp, nrate, prate, maxnrate, maxprate, ";
   $archivesql .= " $projectid as projectid, $runid as scenarioid, ";
   $archivesql .= " '$thisdate'::timestamp as rundate, $thisyear as thisyear ";
   $archivesql .= "    from $srctable ";
   $archivesql .= " where $ssclause ";
   $archivesql .= "    order by subshedid";

   print("Archiving Subshed Values for $thisyear<br>");
   $listobject->querystring = $unarchsql;
   #   print("$listobject->querystring<br>");
   $listobject->performQuery();
   $listobject->querystring = $archivesql;
    #  print("$listobject->querystring<br>");
   $listobject->performQuery();
}

function archiveManureStored($listobject, $scenarioid, $thisyear, $subsheds, $thisdate, $srctable = 'manure_stored', $debug = 0) {

   if (strlen ($subsheds) > 0) {
      $sscount = count(split(',', $subsheds));
      print("Total Subsheds: $sscount <br>");
      $slist = join("','", split(',', $subsheds));
      $ssclause = "subshedid in ('$slist')";
   } else {
      $ssclause = '1 = 1';
   }

   # archive the lrseg features
   $unarchsql = " delete from scen_storedloads where scenarioid = $scenarioid ";
   $unarchsql .= " and $ssclause and thisyear = $thisyear ";

   $archivesql = " insert into scen_storedloads ( scenarioid, thisyear, subshedid , landuseid, sourceid, ";
   $archivesql .= "   spreadid, constit, annualproduction, vconst, annualstored, ";
   $archivesql .= "   JAN, FEB, MAR, APR, MAY, JUN, ";
   $archivesql .= "   JUL, AUG, SEP, OCT, NOV, DEC, ";
   $archivesql .= "   annualdieoff, annualvolatilized, annualapplied, thisdate ) ";
   $archivesql .= " select $scenarioid, $thisyear, subshedid , landuseid, sourceid, ";
   $archivesql .= "   spreadid, pollutanttype, annualproduction, vconst, annualstored, ";
   $archivesql .= "   JAN, FEB, MAR, APR, MAY, JUN, ";
   $archivesql .= "   JUL, AUG, SEP, OCT, NOV, DEC, ";
   $archivesql .= "   annualdieoff, annualvolatilized, annualapplied, '$thisdate'::timestamp ";
   $archivesql .= "    from $srctable ";
   $archivesql .= " where $ssclause ";
   $archivesql .= "    order by subshedid";

   print("Archiving Stored Loads for $thisyear<br>");
   $listobject->querystring = $unarchsql;
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   $listobject->querystring = $archivesql;
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $listobject->performQuery();
}

function archiveSourceInfo($listobject, $projectid, $runid, $thisyear, $subsheds, $thisdate) {

   if (strlen ($subsheds) > 0) {
      $sscount = count(split(',', $subsheds));
      print("Total Subsheds: $sscount <br>");
      $slist = join("','", split(',', $subsheds));
      $ssclause = "subshedid in ('$slist')";
   } else {
      $ssclause = '1 = 1';
   }

    # archive the current sources
    $unarchsql = " delete from scen_sources where scenarioid = $runid ";
    $unarchsql .= " and projectid = $projectid";

    $archivesql = "insert into scen_sources ( sourceid, projectid, scenarioid, ";
    $archivesql .= " rundate, typeid, poplink, ";
    $archivesql .= " distrotype, sourcename, avgweight, parentid, inheritmode, src_citation )";
    $archivesql .= " select sourceid, $projectid, $runid, ";
    $archivesql .= " '$thisdate'::timestamp, typeid, poplink, ";
    $archivesql .= " distrotype, sourcename, avgweight, parentid, inheritmode, src_citation ";
    $archivesql .= "    from worksources ";
    $archivesql .= "    order by sourceid";

    print("Archiving Source Definitions<br>");
    $listobject->querystring = $unarchsql;
#   print("$listobject->querystring<br>");
    $listobject->performQuery();
    $listobject->querystring = $archivesql;
#   print("$listobject->querystring<br>");
    $listobject->performQuery();

    # archive the current sources
    $unarchsql = " delete from scen_sourceloadtype where scenarioid = $runid ";
    $unarchsql .= " and projectid = $projectid";

    $archivesql = "insert into scen_sourceloadtype ( scenarioid, typeid, sourcename, auweight, pollutantprod, ";
    $archivesql .= "  produnits, pollutantconc, storagedieoff, concunits, conv, convunits,  ";
    $archivesql .= "  projectid, sourceclass, starttime, duration, directfraction, avgweight, parentid, ";
    $archivesql .= "  inheritmode, comments, rundate ) ";
    $archivesql .= " select $runid, typeid, sourcename, auweight, pollutantprod, ";
    $archivesql .= "  produnits, pollutantconc, storagedieoff, concunits, conv, convunits,  ";
    $archivesql .= "  $projectid, sourceclass, starttime, duration, directfraction, avgweight, parentid, ";
    $archivesql .= "  inheritmode, comments, '$thisdate'::timestamp ";
    $archivesql .= "    from worksourcetype ";

    print("Archiving Source Types<br>");
    $listobject->querystring = $unarchsql;
#   print("$listobject->querystring<br>");
    $listobject->performQuery();
    $listobject->querystring = $archivesql;
#   print("$listobject->querystring<br>");
    $listobject->performQuery();

    # archive the current pollutant production characteristics
    $unarchsql = " delete from scen_sourcepollprod where scenarioid = $runid ";
    $unarchsql .= " and $ssclause and thisyear = $thisyear and projectid = $projectid";

    $archivesql = "insert into scen_sourcepollprod ( sourceid, projectid, scenarioid, ";
    $archivesql .= " rundate, thisyear, ";
    $archivesql .= " sourcename, sourcetypeid, pollutanttype, typeid, pollutantconc, ";
    $archivesql .= " conv, convunits, actualpop, aucount, sourceclass, ";
    $archivesql .= " volatilization, storagedieoff, annualproduction, ";
    $archivesql .= " subshedid, produnits, concunits, annualpollutant, ";
    $archivesql .= " JAN, FEB, MAR, APR, MAY, JUN, JUL, AUG, SEP, OCT, NOV, DEC )";
    $archivesql .= " select sourceid, $projectid as projectid, $runid as scenarioid, ";
    $archivesql .= " '$thisdate'::timestamp as rundate, $thisyear as thisyear, ";
    $archivesql .= " sourcename, sourcetypeid, pollutanttype, typeid, pollutantconc, ";
    $archivesql .= " conv, convunits, actualpop, aucount, sourceclass, ";
    $archivesql .= " volatilization, storagedieoff, annualproduction, ";
    $archivesql .= " subshedid, produnits, concunits, annualpollutant, ";
    $archivesql .= " JAN, FEB, MAR, APR, MAY, JUN, JUL, AUG, SEP, OCT, NOV, DEC ";
    $archivesql .= "    from sourcepollprod ";
    $archivesql .= " where $ssclause ";

    print("Archiving Source Production Values<br>");
    $listobject->querystring = $unarchsql;
#   print("$listobject->querystring<br>");
    $listobject->performQuery();
    $listobject->querystring = $archivesql;
#   print("$listobject->querystring<br>");
    $listobject->performQuery();

    # archive the current source pollutantts
    $unarchsql = " delete from scen_sourcepollutants where scenarioid = $runid ";
    $unarchsql .= " and projectid = $projectid";

    $archivesql = "insert into scen_sourcepollutants ( scenarioid, typeid, sourcetypeid, ";
    $archivesql .= "   pollutantconc, storagedieoff, volatilization, concunits, conv, convunits, ";
    $archivesql .= "   projectid, pollutanttype, starttime, duration, directfraction, comments, ";
    $archivesql .= "   parentid, inheritmode, rundate ) ";
    $archivesql .= " select $runid, typeid, sourcetypeid, ";
    $archivesql .= "   pollutantconc, storagedieoff, volatilization, concunits, conv, convunits, ";
    $archivesql .= "   $projectid, pollutanttype, starttime, duration, directfraction, comments, ";
    $archivesql .= "   parentid, inheritmode, '$thisdate'::timestamp ";
    $archivesql .= "    from worksourcepolls ";

    print("Archiving Source Constituent Types<br>");
    $listobject->querystring = $unarchsql;
#   print("$listobject->querystring<br>");
    $listobject->performQuery();
    $listobject->querystring = $archivesql;
#   print("$listobject->querystring<br>");
    $listobject->performQuery();

    # archive the current source pollutantts
    $unarchsql = " delete from scen_monsubproduction where scenarioid = $runid ";
    $unarchsql .= " and $ssclause and thisyear = $thisyear";

    $archivesql = "insert into scen_monsubproduction ( scenarioid, sourceid, avgweight, sourcename, ";
    $archivesql .= "   distrotype, subshedid, popdens, actualpop, pollutantprod, produnits, conv, ";
    $archivesql .= "   auweight, typeid, sourceclass, starttime, duration, aucount, meanpopfact, annualproduction, ";
    $archivesql .= "   jan, feb, mar, apr, may, jun, jul, aug, sep, oct, nov, dec, thisyear, ";
    $archivesql .= "   rundate ) ";
    $archivesql .= " select $runid, sourceid, avgweight, sourcename, ";
    $archivesql .= "   distrotype, subshedid, popdens, actualpop, pollutantprod, produnits, conv, ";
    $archivesql .= "   auweight, typeid, sourceclass, starttime, duration, aucount, meanpopfact, annualproduction, ";
    $archivesql .= "   jan, feb, mar, apr, may, jun, jul, aug, sep, oct, nov, dec, $thisyear, '$thisdate'::timestamp ";
    $archivesql .= "    from monsubproduction ";
    $archivesql .= " where $ssclause ";

    print("Archiving Monthly Source Production Values<br>");
    $listobject->querystring = $unarchsql;
#   print("$listobject->querystring<br>");
    $listobject->performQuery();
    $listobject->querystring = $archivesql;
#   print("$listobject->querystring<br>");
    $listobject->performQuery();

    # archive the current source pollutantts
    $unarchsql = " delete from scen_sourcepops where scenarioid = $runid ";
    $unarchsql .= " and $ssclause and thisyear = $thisyear";

    $archivesql = "insert into scen_sourcepops ( scenarioid, subshedid, sourceid, sourcepop, ";
    $archivesql .= "   src_citation, thisyear, rundate ) ";
    $archivesql .= " select $runid,  subshedid, sourceid, sourcepop, ";
    $archivesql .= "   src_citation, $thisyear, '$thisdate'::timestamp ";
    $archivesql .= "    from worksubshed ";
    $archivesql .= " where $ssclause ";

    print("Archiving Source Population Values<br>");
    $listobject->querystring = $unarchsql;
#   print("$listobject->querystring<br>");
    $listobject->performQuery();
    $listobject->querystring = $archivesql;
#   print("$listobject->querystring<br>");
    $listobject->performQuery();


}



function archiveCropYields($listobject, $projectid, $runid, $thisyear, $subsheds, $thisdate, $srctable = 'interpyields') {

   if (strlen ($subsheds) > 0) {
      $sscount = count(split(',', $subsheds));
      print("Total Subsheds: $sscount <br>");
      $slist = join("','", split(',', $subsheds));
      $ssclause = "subshedid in ('$slist')";
   } else {
      $ssclause = '1 = 1';
   }

   # archive the lrseg features
   $unarchsql = " delete from inputyields where scenarioid = $runid ";
   $unarchsql .= " and $ssclause and thisyear = $thisyear and projectid = $projectid";

   $archivesql = "insert into inputyields ( subshedid, luname, scenarioid, projectid, nm_planbase, ";
   $archivesql .= "    maxn, maxp, total_acres, legume_n, uptake_n, uptake_p, total_n, total_p, ";
   $archivesql .= "    nrate, prate, optn, optp, maxnrate, maxprate, ";
   $archivesql .= "    mean_uptn, mean_uptp, n_urratio, p_urratio, mean_needn, mean_needp, rundate, ";
   $archivesql .= "    thisyear, dc_pct,n_fix,high_uptp,";
   $archivesql .= "    high_uptn,high_needp,high_needn,targ_needn,targ_needp,";
   $archivesql .= "    targ_uptp,targ_uptn,optyieldtarget,maxyieldtarget ) ";
   $archivesql .= " select  subshedid, luname, $runid, projectid, nm_planbase, ";
   $archivesql .= " maxn, maxp, total_acres, legume_n, uptake_n, uptake_p, total_n, total_p, ";
   $archivesql .= " nrate, prate, optn, optp, maxnrate, maxprate, ";
   $archivesql .= " mean_uptn, mean_uptp, n_urratio, p_urratio, mean_needn, mean_needp, ";
   $archivesql .= " '$thisdate'::timestamp as rundate, ";
   $archivesql .= "    $thisyear as thisyear, dc_pct,n_fix,high_uptp,";
   $archivesql .= "    high_uptn,high_needp,high_needn,targ_needn,targ_needp,";
   $archivesql .= "    targ_uptp,targ_uptn,optyieldtarget,maxyieldtarget ";
   $archivesql .= "    from $srctable ";
   $archivesql .= " where $ssclause ";
   $archivesql .= "    order by subshedid";

   print("Archiving Crop Yield Values for $thisyear<br>");
   $listobject->querystring = $unarchsql;
   #   print("$listobject->querystring<br>");
   $listobject->performQuery();
   $listobject->querystring = $archivesql;
   #   print("$listobject->querystring<br>");
   $listobject->performQuery();

}


?>