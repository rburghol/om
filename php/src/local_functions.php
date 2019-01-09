<?php


function showRemaining($listobject, $storetable, $debug) {
   $listobject->querystring = "  select pollutanttype, sum(annualapplied) ";
   $listobject->querystring .= " FROM $storetable ";
   $listobject->querystring .= " GROUP BY pollutanttype ";
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   print("Amount remaining in $storetable <br>");
   $listobject->showList();


   $listobject->querystring = "  select a.sourcename, b.sourceid, b.pollutanttype, sum(b.annualapplied) ";
   #$listobject->querystring .= " FROM $storetable as b ";
   $listobject->querystring .= " FROM $storetable as b, sources as a ";
   $listobject->querystring .= " WHERE a.sourceid = b.sourceid ";
   #$listobject->querystring .= " GROUP BY b.pollutanttype, b.sourceid ";
   $listobject->querystring .= " GROUP BY b.pollutanttype, a.sourcename, b.sourceid ";
   $debug = 1;
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   print("Amount remaining in $storetable (by source) <br>");
   $listobject->showList();

}

function calcAllLoads ($listobject, $projectid, $subsheds, $polltypes, $thisyear, $legume_rate, $legume_nut, $def_nm_planbase, $defopttarg, $defmaxtarg, $vwaste_storage_lutype, $scenarioid, $debug) {

   $nappfactor = 1.35; # Nitrogen app rate multiplier (should pass these in soon)
   $pappfactor = 1.0; # Phosphorus app rate multiplier


   print("<br>Creating Working Tables for the year $thisyear.<br>");
   #$debug = 4;

   # the 1 as the last input variable is the decision to "hack" nutrient management or not.
   # This will not be necessary later
   # as we decide on nutrient management rates, but currently needs to be done.
   # We can set it to 0, or not include it
   # at all if we do NOT wish to hack NM rates.
   # The "hacked" nutrient management rate application multiplier is set in the
   # functions makeTempWorkTables.
   # this has no effect if using the routine makeTempWorkFromScen
   $hacknm = 1;

   # old method of working table creation for model input generation
   #makeTempWorkTables($listobject, $projectid, $overwrite, $subsheds, $polltypes, $thisyear, $scenarioid, $debug, $hacknm);
   # new method, uses LRSeg info as base for land use, and obtains crop uptake info from the inputyields table
   makeTempWorkFromScen($listobject, $projectid, $subsheds, $thisyear, $scenarioid, $def_nm_planbase, $defopttarg, $defmaxtarg, $debug);

   #$listobject->querystring = "  select * from workdistro ";
   #$listobject->performQuery();
   #$listobject->showList();

   #$debug = 0;

   # create table of all stored manure loads (ag waste)
   # this routine implicitly calls makeMonApplied which handles all
   # direct loads. Direct loads are defined as loads that go directly onto a destination
   #    land use, i.e., the land is loaded with their monthly percents. However, this ALSO
   #    achieves the effect of putting storage-loaded loads into their respective storage bins
   #    as these storage bins are considered to be "virtual land-uses"(ie. non-storage)
   # This also implicitly calls the routine to make the table sourcepollprod, the production table.
   # need to explicitly create and pass this table on, so that the loads will
   # be subtracted after nm application, to go to the generic, non-nutrient managed
   # landuses
   # use spreadid 6, could be 5 or 6 since both call the stored ag waste land use - named 'Waste_Storage' currently
   print("Calculating storage loading.<br>");
   $manurestoretable = makeGenericStored($listobject, $projectid, 'manure', 6, 1, 1, $debug);
   #showRemaining($listobject, $manurestoretable, $debug);

  # $listobject->querystring = "select * from manure_stored ";
  # $listobject->performQuery();
  # $listobject->showList();

   # transport manure
   transportManure($listobject, $scenarioid, $thisyear, $subsheds, $thisdate, $manurestoretable, $debug);

  # $listobject->querystring = "select * from manure_stored ";
  # $listobject->performQuery();
  # $listobject->showList();

   # archive the manure stored into a scenario table
   $thisdate = date('r',time());
   archiveManureStored($listobject, $scenarioid, $thisyear, $subsheds, $thisdate, $manurestoretable, $debug);


   # create totals tables using the only thing that has been thus far applied, the monapplied
   # table of directly deposited loads
   $loadtables = array('monapplied');
   # sum up all loads thus far applied, (1 flag says overwrite table)
   # the resulting tables:
   #   allmonapplied - one entry for each source, subshed and landuse with loads given
   #                   in average load per day
   #   sumallmonapplied - one entry for subshed and landuse with loads given
   #                   in average load per day
   sumAllMonAppliedLoads2($listobject, $projectid,  1, $loadtables, $debug);
   # now create a variety of tables that show applied loads on a per acre basis
   # including the table 'montotal' which shows the total per month-acre
   # and also includes an annual summary table
   createMonPerUnitArea($listobject, $projectid, 1, $debug);

   # call for Cattle Virtual Pasture, generic distro, this is the equivalent
   # to direct applications, but allows for a crop need application on livestock stream
   # access areas. If done with a standard direct distribution scheme, stream access areas
   # would recieve increasing levels of loads as BMPs turned stream access landuse
   # back into riparian corridor. Thus, this allows us to specify the rate of application
   # onto LAXS areas relative to Pasture (or any other land use for that matter).
   # spreadid = 10,
   # the storetable is calculated WITHOUT including volatilization losses
   print("Calculating pasture loading.<br>");
   $pasturestore = makeGenericStored($listobject, $projectid, 'vpasture', 10, 0, 1, $debug);
   #showRemaining($listobject, $pasturestore, $debug);
   createGenericCropNeed($listobject, $projectid, 1, $nappfactor, 'worksublu', 'worklanduses', 'vpasture', 10, 0, 'montotal', $pasturestore, 1, $debug);
   # create totals tables
   #showRemaining($listobject, $pasturestore, $debug);

   # call for manure nutrient management applications
   # spreadid = 5, nutrient management crop need manure spreading
   print("Calculating Nutrient management manure loading.<br>");
   # this loading entails assuming that nothing has been applied onto pasture land
   # because the amount of application allowed on pasture is irrespective of the amount
   # deposited by grazing animals.
   # therefore, a custom applied table must be created
   # so far, only 'monapplied' and 'vpasture_applied' have been created. 'monapplied'
   # contains the directly excreted loads  and 'vpasture_applied' has those
   # excreted by pastured cattle in a special distro
   # therefore, we do not yet update the montotal table to include them.
   # limiting nutrient - this should later be done in the crop need script
   #$manurelimit = 2; # P-based
   #$manurelimit = 1; # N-based
   # query plans dynamically - do P-based first, then N-based, then other if they exist
   $listobject->querystring = " select nm_planbase from worksublu where nm_planbase in (select typeid from pollutanttype where master_constit = 2) group by nm_planbase ";
   $listobject->performQuery();
   $pnmrecs = $listobject->queryrecords;
   $listobject->querystring = " select nm_planbase from worksublu where nm_planbase in (select typeid from pollutanttype where master_constit = 1) group by nm_planbase ";
   $listobject->performQuery();
   $nnmrecs = $listobject->queryrecords;
   $listobject->querystring = " select nm_planbase from worksublu where nm_planbase in (select typeid from pollutanttype where master_constit not in (1,2) ) group by nm_planbase ";
   $listobject->performQuery();
   $onmrecs = $listobject->queryrecords;
   $nmrecs = array_merge($pnmrecs,$nnmrecs,$onmrecs);
   $nmloadtables = array();

   foreach ($nmrecs as $thisplan) {
      $manurelimit = $thisplan['nm_planbase'];
      print("&nbsp;&nbsp;&nbsp;Applying plans based on $manurelimit <br>");
      $listobject->querystring = "  create temp table ws$manurelimit as ";
      $listobject->querystring .= " select * from worksublu ";
      $listobject->querystring .= " where nm_planbase = $manurelimit ";
      $listobject->performQuery();
      createGenericCropNeed($listobject, $projectid, $manurelimit, $nappfactor, "ws$manurelimit", 'worklanduses', "nmmanure$manurelimit", 5, 1, 'montotal', $manurestoretable, 1, $debug);
      #showRemaining($listobject, $manurestoretable, $debug);
      array_push($nmloadtables, "nmmanure$manurelimit" . '_applied');
   }

   # create totals tables now using all tables (as opposed to previous step where we ignored
   # directly deposited loads)
   $loadtables = $nmloadtables;
   # sum up all loads thus far applied, (0 flag says don't overwrite table, resulting in
   # this table being appended to the the existing data
   sumAllMonAppliedLoads2($listobject, $projectid,  0, $loadtables, $debug);
   createMonPerUnitArea($listobject, $projectid, 1, $debug);


   # since pasture will be eligible for app under non-nutrient management, we still
   # do not include the vpasture applications
   # call for manure applications without nutrient management


   # spreadid = 14, Daily Haul crop need manure spreading
   print("Calculating Daily Haul manure loading.<br>");
   # added for limiting maximum application above optimum on non-NM lus
   # changed limit rate to 1.0
   createGenericCropNeed($listobject, $projectid, 1, $nappfactor, 'worksublu', 'worklanduses', 'dailyhaul', 14, 1, 'montotal', $manurestoretable, 1, $debug);
   #showRemaining($listobject, $manurestoretable, $debug);


   print("Calculating non-nutrient management manure loading.<br>");
   # spreadid = 6, non-nutrient management crop need manure spreading
   # added for limiting maximum application above optimum on non-NM lus
   # changed limit rate to 1.0
   createGenericCropNeed($listobject, $projectid, 1, $nappfactor, 'worksublu', 'worklanduses', 'manure', 6, 1, 'montotal', $manurestoretable, 1, $debug);
   #showRemaining($listobject, $manurestoretable, $debug);


   print("Calculating manure disposal loading.<br>");
   # put any remaining manure onto pasture, or other "last-resort" nutrient recipients
   # this is spreadid = 12, limitrate = 0 (currently 0, but may limit in the future)
   createGenericCropNeed($listobject, $projectid, 1, $nappfactor, 'worksublu', 'worklanduses', 'excessmanure', 12, 0, 'montotal', $manurestoretable, 1, $debug);
   #showRemaining($listobject, $manurestoretable, $debug);

   # now, all the fancy footwork is done, so we will create a summary table that
   # includes everything that has been done so far.
   $otherloadtables = array('manure_applied', 'monapplied', 'vpasture_applied', 'excessmanure_applied', 'dailyhaul_applied');
   $loadtables = array_merge($otherloadtables, $nmloadtables);
   # sum up all loads thus far applied, (1 flag says overwrite table)
   sumAllMonAppliedLoads2($listobject, $projectid,  1, $loadtables, $debug);
   createMonPerUnitArea($listobject, $projectid, 1, $debug);


   # create table of all stored ag nitrogen fertilizer loads
   # need to explicitly create and pass this table on, so that the loads will
   # be subtracted after nm application, to go to the generic, non-nutrient managed
   # landuses
   $storetable = makeGenericStored($listobject, $projectid, 'agnm', 8, 1, 1, $debug);


   # These distros use limitrate = 2; which should fill it to capacity, and not above
   # this is a deaprture from the earlier kludge of figuring out how much each
   # watershed should need in chemical fert and then adding that as a source,
   # now, if there is ANY chemical fertilizer in the watershed, all receiving landuses
   # will be filled to optimal capacity (if they need any that is)
   # call for ag nutrient management Nitrogen fertilizer applications
   print("Calculating nutrient management nitrogen fertilizer loading.<br>");
   createGenericCropNeed($listobject, $projectid, 1, $nappfactor, 'worksublu', 'worklanduses', 'agnm', 11, 2, 'montotal', $storetable, 1, $debug);

   # now call for fertilizer phosphorous NM applications
   print("Calculating NM phosphorus loading.<br>");
   createGenericCropNeed($listobject, $projectid, 2, $pappfactor, 'worksublu', 'worklanduses', 'agpm', 11, 2, 'montotal', $storetable, 1, $debug);


   $loadtables = array('agnm_applied', 'agpm_applied');
   # create totals tables - updates montotal table to reflect new applications
   # add in agnm and agpm to existing totals
   sumAllMonAppliedLoads2($listobject, $projectid,  0, $loadtables, $debug);
   createMonPerUnitArea($listobject, $projectid, 1, $debug);

   # do all non-nutrient management chemical fertilizer applications for N and P
   print("Calculating non NM nitrogen loading.<br>");
   createGenericCropNeed($listobject, $projectid, 1, $nappfactor, 'worksublu', 'worklanduses', 'agn', 8, 2, 'montotal', $storetable, 1, $debug);


   print("Calculating non-NM phosphorus loading.<br>");
   createGenericCropNeed($listobject, $projectid, 2, $pappfactor, 'worksublu', 'worklanduses', 'agp', 8, 2, 'montotal', $storetable, 1, $debug);


   # create totals tables - updates montotal table to reflect new applications
   $loadtables = array('agn_applied', 'agp_applied');

   sumAllMonAppliedLoads2($listobject, $projectid, 0, $loadtables, $debug);
   # create totals tables
   createMonPerUnitArea($listobject, $projectid, 1, $debug);

   # create  table with remaining nutrient assimilation capacity for nutrient managed lands
   createRemainingCapacity($listobject, $projectid, 'afterag', 'montotal', 'worksublu', 'worklanduses', 0, $debug);

   # add urban fertilizer applications
   # we call this without passing it a store table ($storetable = '')
   # this makes the createGenericCropNeed routine automatically call the createGenericStored routine for this spreadid,
   # and this defaults to counting volatilization/dieoff losses.
   print("Calculating urban nitrogen fertilizer loading.<br>");
   createGenericCropNeed($listobject, $projectid, 1, $nappfactor, 'worksublu', 'worklanduses', 'urbn', 7, 2, '', '', 0, $debug);
   print("Calculating urban phosphorus fertilizer loading.<br>");
   createGenericCropNeed($listobject, $projectid, 2, $pappfactor, 'worksublu', 'worklanduses', 'urbp', 7, 2, '', '', 0, $debug);

   # add septic distro - distribution 9
   print("Calculating septic loading.<br>");
   # we call this without passing it a store table ($storetable = '')
   # this makes the createGenericCropNeed routine automatically call the createGenericStored routine for this spreadid,
   # and this defaults to counting volatilization/dieoff losses.
   createGenericCropNeed($listobject, $projectid, 1, $nappfactor, 'worksublu', 'worklanduses', 'septicn', 9, 0, '', '', 0, $debug);

   $listobject->querystring = "create temp table urbtotal as select * from urbn_monapp";
   $listobject->performQuery();

   $listobject->querystring = "insert into urbtotal select * from urbp_monapp";
   $listobject->performQuery();



   # add urban fertilizer applications to the existing load summary table
   # only pass new tables to this function, if the overwrite tag is set to 0
   # use this format to include septic loads
   $loadtables = array('urbn_applied', 'urbp_applied', 'septicn_applied');

   # this will NOT pass in septic loads, which are not really suposed to be in there anyhow, since they are modeled as direct
   # to stream loadings.
   #$loadtables = array('urbn_applied', 'urbp_applied');

   sumAllMonAppliedLoads2($listobject, $projectid, 0, $loadtables, $debug);


/*
   $listobject->querystring = "select * from septicn_applied";
   $listobject->performQuery();
   $listobject->showList();

   $listobject->querystring = "select * from allmonapplied";
   $listobject->performQuery();
   $listobject->showList();

   $listobject->querystring = "select * from sourceperunitarea";
   $listobject->performQuery();
   $listobject->showList();
*/

   # create legume table
   # this is not currently integrated, so it will only be used in the
   # archiving of scenario data, and subsequent prodcution of model input files,
   # but should ultimately become part of the
   # set of tables that is summed in the sum all mon applied areas for reporting and analysis
   createLegumeTab($listobject, $projectid, $scenarioid, $legume_rate, $legume_nut, 'legume_fix', $debug);

   # custom total stored table, accepts tables to sum as input
   $loadtables = array('manure_stored', 'urbn_stored', 'urbp_stored', 'agnm_stored', 'septicn_stored');
   sumAllStored($listobject, $projectid, 1, $loadtables, $debug);

   # normalize on a per acre basis
   createMonPerUnitArea($listobject, $projectid, 1, $debug);

   totalNonStored($listobject, $projectid, $overwrite, $debug);

}


function createLegumeTab($listobject, $projectid, $scenarioid, $legume_rate, $legume_nut, $tabname, $debug) {


   if ($listobject->tableExists("$tabname") ) {
      $listobject->querystring = "drop table $tabname ";
      $listobject->performQuery();
   }

   # creates a table of legume credits
   $listobject->querystring = "create temp table $tabname as ";
   $listobject->querystring .= " select e.subshedid, ";
   $listobject->querystring .= " 0.0 as annualapplied, ";
   $listobject->querystring .= " a.legume_n, ";
   $listobject->querystring .= " b.luname, d.shortname as constituent, ";
   # Max Total leguminous N at optimal rate = a.legume_n
   # actual amount that legume could fix = a.legume_n - (e.annualapplied - a.optn)
   $listobject->querystring .= " CASE ";
   $listobject->querystring .= "    WHEN (e.annualapplied > a.optn) THEN ";
   $listobject->querystring .= "       b.JAN*( a.legume_n - (e.annualapplied - a.optn) ) ";
   $listobject->querystring .= "    ELSE b.JAN*a.legume_n ";
   $listobject->querystring .= " END ";
   $listobject->querystring .= " as JAN,";
   $listobject->querystring .= " CASE ";
   $listobject->querystring .= "    WHEN (e.annualapplied > a.optn) THEN ";
   $listobject->querystring .= "      b.FEB*( a.legume_n - (e.annualapplied - a.optn) )  ";
   $listobject->querystring .= "    ELSE b.FEB*a.legume_n ";
   $listobject->querystring .= " END ";
   $listobject->querystring .= " as FEB,";
   $listobject->querystring .= " CASE ";
   $listobject->querystring .= "    WHEN (e.annualapplied > a.optn) THEN ";
   $listobject->querystring .= "      b.MAR*( a.legume_n - (e.annualapplied - a.optn) )  ";
   $listobject->querystring .= "    ELSE b.MAR*a.legume_n ";
   $listobject->querystring .= " END ";
   $listobject->querystring .= " as MAR,";
   $listobject->querystring .= " CASE ";
   $listobject->querystring .= "    WHEN (e.annualapplied > a.optn) THEN ";
   $listobject->querystring .= "      b.APR*( a.legume_n - (e.annualapplied - a.optn) )  ";
   $listobject->querystring .= "    ELSE b.APR*a.legume_n ";
   $listobject->querystring .= " END ";
   $listobject->querystring .= " as APR,";
   $listobject->querystring .= " CASE ";
   $listobject->querystring .= "    WHEN (e.annualapplied > a.optn) THEN ";
   $listobject->querystring .= "      b.MAY*( a.legume_n - (e.annualapplied - a.optn) )  ";
   $listobject->querystring .= "    ELSE b.MAY*a.legume_n ";
   $listobject->querystring .= " END ";
   $listobject->querystring .= " as MAY,";
   $listobject->querystring .= " CASE ";
   $listobject->querystring .= "    WHEN (e.annualapplied > a.optn) THEN ";
   $listobject->querystring .= "      b.JUN*( a.legume_n - (e.annualapplied - a.optn) )  ";
   $listobject->querystring .= "    ELSE b.JUN*a.legume_n ";
   $listobject->querystring .= " END ";
   $listobject->querystring .= " as JUN,";
   $listobject->querystring .= " CASE ";
   $listobject->querystring .= "    WHEN (e.annualapplied > a.optn) THEN ";
   $listobject->querystring .= "      b.JUL*( a.legume_n - (e.annualapplied - a.optn) )  ";
   $listobject->querystring .= "    ELSE b.JUL*a.legume_n ";
   $listobject->querystring .= " END ";
   $listobject->querystring .= " as JUL,";
   $listobject->querystring .= " CASE ";
   $listobject->querystring .= "    WHEN (e.annualapplied > a.optn) THEN ";
   $listobject->querystring .= "      b.AUG*( a.legume_n - (e.annualapplied - a.optn) )  ";
   $listobject->querystring .= "    ELSE b.AUG*a.legume_n ";
   $listobject->querystring .= " END ";
   $listobject->querystring .= " as AUG,";
   $listobject->querystring .= " CASE ";
   $listobject->querystring .= "    WHEN (e.annualapplied > a.optn) THEN ";
   $listobject->querystring .= "      b.SEP*( a.legume_n - (e.annualapplied - a.optn) )  ";
   $listobject->querystring .= "    ELSE b.SEP*a.legume_n ";
   $listobject->querystring .= " END ";
   $listobject->querystring .= " as SEP,";
   $listobject->querystring .= " CASE ";
   $listobject->querystring .= "    WHEN (e.annualapplied > a.optn) THEN ";
   $listobject->querystring .= "      b.OCT*( a.legume_n - (e.annualapplied - a.optn) )  ";
   $listobject->querystring .= "    ELSE b.OCT*a.legume_n ";
   $listobject->querystring .= " END ";
   $listobject->querystring .= " as OCT,";
   $listobject->querystring .= " CASE ";
   $listobject->querystring .= "    WHEN (e.annualapplied > a.optn) THEN ";
   $listobject->querystring .= "      b.NOV*( a.legume_n - (e.annualapplied - a.optn) )  ";
   $listobject->querystring .= "    ELSE b.NOV*a.legume_n ";
   $listobject->querystring .= " END ";
   $listobject->querystring .= " as NOV,";
   $listobject->querystring .= " CASE ";
   $listobject->querystring .= "    WHEN (e.annualapplied > a.optn) THEN ";
   $listobject->querystring .= "      b.DEC*( a.legume_n - (e.annualapplied - a.optn) )  ";
   $listobject->querystring .= "    ELSE b.DEC*a.legume_n ";
   $listobject->querystring .= " END ";
   $listobject->querystring .= " as DEC";

   $listobject->querystring .= " from worksublu as a, pollutanttype as d, ";
   $listobject->querystring .= "  montotal as e, local_apply as b ";
   $listobject->querystring .= "  where b.subshedid = e.subshedid ";
   $listobject->querystring .= "     and b.luname = e.luname ";
   $listobject->querystring .= "     and b.luname = a.luname ";
   $listobject->querystring .= "     and b.thisyear = a.thisyear ";
   $listobject->querystring .= "     and b.subshedid = a.subshedid ";
   $listobject->querystring .= "     and b.scenarioid = $scenarioid ";
   # select for legume fixation curve type
   $listobject->querystring .= "     and b.source_type = 'n_fix' ";
   # use type $legume_nut, Anhydrous Ammonium, or NH3
   $listobject->querystring .= "     and d.typeid in ( $legume_nut ) ";
   # base comparison against
   $listobject->querystring .= "     and e.pollutanttype = 1 ";
   # revised 4/11/2007 - RWB
   $listobject->querystring .= "     and ( a.legume_n - (e.annualapplied - a.optn) ) > 0 ";
   #$listobject->querystring .= "     and e.nrate > 0 ";
   $listobject->querystring .= "     and a.legume_n > 0 ";
   $listobject->querystring .= " order by e.subshedid, b.luname ";
   $listobject->performQuery();
   if ($debug) { print("$listobject->querystring <br>"); }


   # total it all up, and set the non reported to -9
   $listobject->querystring = " update $tabname set ";
   $listobject->querystring .= " annualapplied = -9, ";
   $listobject->querystring .= " JAN = -9, ";
   $listobject->querystring .= " FEB = -9, ";
   $listobject->querystring .= " MAR = -9,";
   $listobject->querystring .= " APR = -9,";
   $listobject->querystring .= " MAY = -9,";
   $listobject->querystring .= " JUN = -9,";
   $listobject->querystring .= " JUL = -9,";
   $listobject->querystring .= " AUG = -9,";
   $listobject->querystring .= " SEP = -9,";
   $listobject->querystring .= " OCT = -9,";
   $listobject->querystring .= " NOV = -9,";
   $listobject->querystring .= " DEC = -9";
   $listobject->querystring .= " from worksublu as a ";
   $listobject->querystring .= " where ";
   $listobject->querystring .= "    ( $tabname.subshedid = a.subshedid ";
   $listobject->querystring .= "      and $tabname.luname = a.luname ";
   $listobject->querystring .= "      and a.luarea <= 0";
   $listobject->querystring .= "    ) or ( ";
   $listobject->querystring .= "         $tabname.JAN is null";
   $listobject->querystring .= "    ) ";
   if ($debug) { print("$listobject->querystring <br>"); }
   $listobject->performQuery();

   $listobject->querystring = "update $tabname set annualapplied = ";
   $listobject->querystring .= " (JAN + FEB + MAR + APR + MAY + JUN + ";
   $listobject->querystring .= " JUL + AUG + SEP + OCT + NOV + DEC )";
   $listobject->querystring .= " where (JAN + FEB + MAR + APR + MAY + JUN + ";
   $listobject->querystring .= " JUL + AUG + SEP + OCT + NOV + DEC ) > 0 ";
   if ($debug) { print("$listobject->querystring <br>"); }
   $listobject->performQuery();

/*
   $listobject->querystring = " select * from montotal ";
   $listobject->querystring .= " ";
   $listobject->performQuery();
   $listobject->showList();

   $listobject->querystring = " select * from legume_fix ";
   $listobject->querystring .= " ";
   $listobject->performQuery();
   if ($debug) { print("$listobject->querystring <br>"); }
   $listobject->showList();
*/

}

   function makeCBLandRiverTables($listobject, $projectid, $subsheds, $polltypes, $thisyear, $overwrite, $debug) {

      print("Creating LRSeg Input Table.<br>");

      $listobject->querystring = " create TEMP TABLE workcomposite as ";
      $listobject->querystring .= " select * from lucomposite ";
      $listobject->querystring .= " where projectid = $projectid ";
      $listobject->querystring .= "    and subshedid in ($subsheds) ";
      $listobject->performQuery();

#      print("$listobject->querystring<br>");
      # interpolate the landuse acreages for the selected subsheds from the composite table
      compositeInterp($listobject, $projectid, $thisyear, 'workcomposite', 'cbcomposite', $overwrite, $debug);

      # multiply the nutrient management percentages to split up the composite segs
      $listobject->querystring = "insert into cbcomposite (subshedid, luname, riverseg, landseg, luarea, thisyear) ";
      $listobject->querystring .= " select a.subshedid, a.nmluname, a.riverseg,";
      $listobject->querystring .= "  a.landseg, (a.luarea * a.pct_nm), a.thisyear ";
      $listobject->querystring .= "  from cbcomposite as a ";
      $listobject->querystring .= "  where a.pct_nm >= 0 and a.nmluname <> ''";
      $listobject->querystring .= "  and a.nmluname is not null";
      $listobject->performQuery();
      #print("$listobject->querystring <br>");

/*
      $listobject->querystring = "  select * from cbcomposite";
      $listobject->performQuery();
      $listobject->showList();
      #print("$listobject->querystring <br>");
*/

      $listobject->querystring = "update cbcomposite set luarea = luarea * (1.0 - pct_nm) ";
      $listobject->querystring .= " where pct_nm >= 0 and nmluname <> '' ";
      $listobject->querystring .= " and nmluname is not null ";
      $listobject->performQuery();
/*
      $listobject->querystring = "  select * from cbcomposite";
      $listobject->performQuery();
      $listobject->showList();
      #print("$listobject->querystring <br>");
*/

      # create a table of only distinct land segs
      $listobject->querystring = " create TEMP TABLE worklandsegs as ";
      $listobject->querystring .= " select subshedid, landseg, luname, sum(luarea) as luarea from cbcomposite ";
      $listobject->querystring .= " group by subshedid, landseg, luname ";
      $listobject->performQuery();

   }





   function compositeInterp($listobject, $projectid, $thisyear, $sstable, $outtable, $overwrite, $debug) {

      $nextsublu = $outtable . '_next';
      $prevsublu = $outtable . '_prev';
      $interpsublu = $outtable . '_interp';
      $subludpdy = $outtable . '_dpdy';

      if ($listobject->tableExists("$outtable") ) {
         if (!$overwrite) {
          print("<b>Warning:</b>Table $outtable already exists. Will not overwrite<br>");
          return;
         } else {
          $listobject->querystring = "drop table $outtable ";
          $listobject->performQuery();
          $listobject->querystring = "drop table $nextsublu ";
          $listobject->performQuery();
          $listobject->querystring = "drop table $prevsublu ";
          $listobject->performQuery();
          $listobject->querystring = "drop table $interpsublu ";
          $listobject->performQuery();
          $listobject->querystring = "drop table $subludpdy ";
          $listobject->performQuery();
         }
      }

      # copysubshed is the subshed info from this projectid

      $listobject->querystring = " CREATE TEMP TABLE $nextsublu AS ";
      $listobject->querystring .= " SELECT subshedid, luname, -888.88 as luarea, ";
      $listobject->querystring .= " -888.88 as pct_nm, riverseg, landseg, ";
      $listobject->querystring .= " min(thisyear) AS nextyear ";
      $listobject->querystring .= " FROM $sstable ";
      $listobject->querystring .= " WHERE thisyear > $thisyear  ";
      $listobject->querystring .= " GROUP BY subshedid, luname, riverseg, landseg ";
      $listobject->performQuery();
#      print("$listobject->querystring<br>");

      # create  template for  previous year table
      $listobject->querystring = " CREATE TEMP TABLE $prevsublu AS ";
      $listobject->querystring .= " SELECT subshedid, luname, -888.88 as luarea, ";
      $listobject->querystring .= " -888.88 as pct_nm, riverseg, landseg, ";
      $listobject->querystring .= " max(thisyear) AS prevyear";
      $listobject->querystring .= " FROM $sstable";
      $listobject->querystring .= " WHERE thisyear < $thisyear ";
      $listobject->querystring .= " GROUP BY subshedid, luname, riverseg, landseg";
      $listobject->performQuery();
#      print("$listobject->querystring<br>");

      $listobject->querystring = " CREATE TEMP TABLE $interpsublu AS";
      $listobject->querystring .= " SELECT subshedid, nmluname, luname, -888.88 as luarea, ";
      $listobject->querystring .= " -888.88 as pct_nm, riverseg, landseg, ";
      $listobject->querystring .= " $thisyear as thisyear ";
      $listobject->querystring .= " FROM $sstable ";
      $listobject->querystring .= " GROUP BY subshedid, luname, nmluname, riverseg, landseg";
      $listobject->performQuery();
#      print("$listobject->querystring<br>");

      # set values for previous reported year
      $listobject->querystring = " UPDATE $nextsublu ";
      $listobject->querystring .= " SET luarea = $sstable.luarea, ";
      $listobject->querystring .= "    pct_nm = $sstable.pct_nm ";
      $listobject->querystring .= " WHERE ($sstable.thisyear=$nextsublu.nextyear) ";
      $listobject->querystring .= "    AND ($sstable.luname=$nextsublu.luname)  ";
      $listobject->querystring .= "    AND ($sstable.subshedid=$nextsublu.subshedid)";
      $listobject->querystring .= "    AND ($sstable.landseg=$nextsublu.landseg)";
      $listobject->querystring .= "    AND ($sstable.riverseg=$nextsublu.riverseg)";
      $listobject->querystring .= "    AND ($sstable.luarea is not null)";
      $listobject->performQuery();
#      print("$listobject->querystring<br>");

      # set values for next reported year
      $listobject->querystring = " UPDATE $prevsublu ";
      $listobject->querystring .= " SET luarea = $sstable.luarea, ";
      $listobject->querystring .= "    pct_nm = $sstable.pct_nm ";
      $listobject->querystring .= " WHERE ($sstable.thisyear = $prevsublu.prevyear)  ";
      $listobject->querystring .= "    AND ($sstable.luname = $prevsublu.luname)";
      $listobject->querystring .= "    AND ($sstable.subshedid = $prevsublu.subshedid)";
      $listobject->querystring .= "    AND ($sstable.landseg=$prevsublu.landseg)";
      $listobject->querystring .= "    AND ($sstable.riverseg=$prevsublu.riverseg)";
      $listobject->querystring .= "    AND ($sstable.luarea is not null)";
      $listobject->performQuery();
#      print("$listobject->querystring<br>");

      # calculate change in area, ncapacity, and pcapacity per year between previous and next
      $listobject->querystring = " CREATE TEMP TABLE $subludpdy AS ";
      $listobject->querystring .= " SELECT a.subshedid, a.luname, a.prevyear, ";
      $listobject->querystring .= " $thisyear as thisyear, a.landseg, a.riverseg, ";
      $listobject->querystring .= " a.luarea + ($thisyear - a.prevyear) * (((b.luarea-a.luarea)) / ((b.nextyear-a.prevyear)::float8)) AS luarea, ";
      # do this because interpolating the percent nutrient management from one year to the next
      # goofs up the total for nutrient management acreage -- use only the last reported value
      # could do like we do when interpolating the census, i.e., estimate these components by interpolating, then
      # scaling the interpolated value so that the sum total equals the reported county total.
      # or better yet, we can totally bag the whole idea of percentage of nutrient management in the context of
      # counties, so that we simply pass in an acreage for the nutrient management land uses. Then, the landseg value
      # becomes the most important.
      $listobject->querystring .= " a.pct_nm AS pct_nm ";
      $listobject->querystring .= " FROM $prevsublu AS a, $nextsublu AS b";
      $listobject->querystring .= " WHERE a.subshedid=b.subshedid ";
      $listobject->querystring .= "    And a.luname=b.luname ";
      $listobject->querystring .= "    AND (a.landseg=b.landseg)";
      $listobject->querystring .= "    AND (a.riverseg=b.riverseg)";
      $listobject->querystring .= "    and a.luarea <> -888.88";
      $listobject->querystring .= "    and b.luarea <> -888.88";
      $listobject->performQuery();
#      print("$listobject->querystring<br>");

      # if the year is an exact match, grab the corresponding entry
      $listobject->querystring = " UPDATE $interpsublu SET luarea = $sstable.luarea, ";
      $listobject->querystring .= "    pct_nm = $sstable.pct_nm ";
      $listobject->querystring .= " WHERE $interpsublu.subshedid = $sstable.subshedid ";
      $listobject->querystring .= " AND $interpsublu.luname = $sstable.luname ";
      $listobject->querystring .= " AND $interpsublu.landseg = $sstable.landseg ";
      $listobject->querystring .= " AND $interpsublu.riverseg = $sstable.riverseg ";
      $listobject->querystring .= " AND $interpsublu.thisyear = $sstable.thisyear";
      $listobject->performQuery();
#      print("$listobject->querystring<br>");

      # otherwise, grab from the interpolation table
      $listobject->querystring = " UPDATE $interpsublu SET luarea = $subludpdy.luarea, ";
      $listobject->querystring .= "    pct_nm = $subludpdy.pct_nm ";
      $listobject->querystring .= " WHERE $interpsublu.subshedid = $subludpdy.subshedid ";
      $listobject->querystring .= " AND $interpsublu.luname = $subludpdy.luname ";
      $listobject->querystring .= " AND $interpsublu.riverseg = $subludpdy.riverseg ";
      $listobject->querystring .= " AND $interpsublu.landseg = $subludpdy.landseg ";
      $listobject->querystring .= " AND $interpsublu.luarea = -888.88 ";
      $listobject->performQuery();
#      print("$listobject->querystring<br>");

      # if still not set, must be out of date range, so try the lower table
      # planbase = 1 unless it is reported this year, or an earlier year,
      # do not assume that planbase changes unless instructed
      $listobject->querystring = " UPDATE $interpsublu set luarea = $prevsublu.luarea, ";
      $listobject->querystring .= "    pct_nm = $prevsublu.pct_nm ";
      $listobject->querystring .= " WHERE $interpsublu.luarea = -888.88 ";
      $listobject->querystring .= " AND $interpsublu.luname = $prevsublu.luname ";
      $listobject->querystring .= " AND $interpsublu.subshedid = $prevsublu.subshedid";
      $listobject->querystring .= " AND $interpsublu.riverseg = $prevsublu.riverseg ";
      $listobject->querystring .= " AND $interpsublu.landseg = $prevsublu.landseg ";
      $listobject->querystring .= " AND $prevsublu.luarea <> -888.88";
      $listobject->performQuery();
#      print("$listobject->querystring<br>");

      # now try the upper table
      $listobject->querystring = " UPDATE $interpsublu set luarea = $nextsublu.luarea, ";
      $listobject->querystring .= "    pct_nm = $nextsublu.pct_nm ";
      $listobject->querystring .= " WHERE $interpsublu.luarea = -888.88 ";
      $listobject->querystring .= " AND $interpsublu.luname = $nextsublu.luname ";
      $listobject->querystring .= " AND $interpsublu.subshedid = $nextsublu.subshedid";
      $listobject->querystring .= " AND $interpsublu.riverseg = $nextsublu.riverseg ";
      $listobject->querystring .= " AND $interpsublu.landseg = $nextsublu.landseg ";
      $listobject->querystring .= " AND $nextsublu.luarea <> -888.88";
      $listobject->performQuery();
#      print("$listobject->querystring<br>");

      # create an out table, insure that each entry has a unique sublu, since these are
      # not referenced in any way, they are OK to be generated here on the fly
      $listobject->querystring = " create temp table $outtable ( ";
      $listobject->querystring .= " subshedid varchar(64), sublu SERIAL, ";
      $listobject->querystring .= " luarea float8, pct_nm float8, thisyear integer, ";
      $listobject->querystring .= " luname varchar(255), landseg varchar(64),  ";
      $listobject->querystring .= " nmluname varchar(255), riverseg varchar(64) )";
      $listobject->performQuery();
      $listobject->querystring = " insert into $outtable (luname, nmluname, luarea, pct_nm, thisyear, subshedid, landseg, riverseg) select luname, nmluname, luarea, pct_nm, thisyear, subshedid, landseg, riverseg from $interpsublu";
      $listobject->performQuery();
#      print("$listobject->querystring<br>");

   }



function getBasinLoads($listobject, $projectid,$pollutants, $gids, $scenarioid, $makecsv, $outdir, $outurl, $theseyears, $seglist='') {

    $manuresourceclasses = '2, 6, 9, 11';
    $fertsourceclasses = '8';
    if (strlen($gids) == 0) {
       print("You must enter either a group name or a list of segments to report");
       return;
    }
    $groups = array();
    $listobject->querystring = "select groupname, seglist, monstation, area2d(the_geom) as shapearea from seggroups where projectid = $projectid and gid in ($gids) order by groupname";

    if (strlen($theseyears) > 0) {
       $yearclause = " and thisyear  in ($theseyears) ";
       $ayearclause = " and a.thisyear  in ($theseyears) ";
       $byearclause = " and b.thisyear  in ($theseyears) ";
    } else {
        $yearclause = '';
    }

    $listobject->performQuery();
    #print("$listobject->querystring <br>");

    $groups = $listobject->queryrecords;

    # add in manually entered segments
    if (strlen($seglist) > 0) {
       array_push($groups, array('seglist'=>$seglist,'groupname'=>'Custom Group','shapearea'=>1.0, 'monstation'=>'Custom') );
    }

    foreach ($groups as $thisgroup) {

       $seglist = $thisgroup['seglist'];
       $riversegs = "'" . join("','", split(",", $thisgroup['seglist'])) . "'";
       $gname = $thisgroup['groupname'];
       $shapearea = $thisgroup['shapearea'];
       $monstation = $thisgroup['monstation'];

       print("Summarizing Data for $gname <br>");

       $listobject->querystring = "select a.staid, a.thisyear, a.pollutanttype, a.scenarioid,";
       $listobject->querystring .= "    a.total_ag, a.ag_area, a.ag_perac, a.total_agmanure, ";
       $listobject->querystring .= "    a.agmanure_perac, a.total_agfert, a.agfert_perac, ";
       $listobject->querystring .= "    a.total_aglegume, a.aglegume_perac, a.total_urb, ";
       $listobject->querystring .= "    a.urb_area, a.urb_perac, a.total_tons, ";
       $listobject->querystring .= "    a.total_perac, a.total_area, a.ps_tons, a.septic_tons, ";
       $listobject->querystring .= "    a.total_sewer, a.sewer_lbs, ";
       $listobject->querystring .= "    (a.ps_tons / a.total_sewer) as sewer_atten,  ";
       $listobject->querystring .= "    a.atm_perac, a.atm_tons, ";
       $listobject->querystring .= "   (a.atm_tons - (a.atm_perac * (a.ag_area + a.urb_area) / 2000.0)) as atm_for, ";
       $listobject->querystring .= "    (b.bmp_total / 2000.0) as bmp_tons, bmp_pct ";
       $listobject->querystring .= " from scen_seggroups as a ";
       $listobject->querystring .= "    left outer join ";
       $listobject->querystring .= "    scen_seggroup_bmp as b";
       $listobject->querystring .= " on ( a.staid = b.staid ";
       $listobject->querystring .= "    and a.thisyear = b.thisyear ";
       $listobject->querystring .= "    and a.pollutanttype = b.pollutanttype ";
       $listobject->querystring .= "    and a.scenarioid = b.scenarioid ";
       $listobject->querystring .= " ) ";
       $listobject->querystring .= " where a.staid = '$monstation' ";
       $listobject->querystring .= $ayearclause;
       $listobject->querystring .= " and a.scenarioid = $scenarioid ";
       if (strlen($pollutants) > 0) {
          $listobject->querystring .= " and a.pollutanttype in ($pollutants) ";
       }
       $listobject->querystring .= " order by a.thisyear, a.pollutanttype ";

       #print("$listobject->querystring<br><br>");
       $listobject->performQuery();
       $listobject->tablename = 'seggroups';

       if ($makecsv) {
          $pname = ereg_replace("[^a-z^A-Z^0-9]",'',$gname);
          $colnames = array(array_keys($listobject->queryrecords[0]));
          $filename = "basintotals_$pname.$scenarioid.csv";
          putDelimitedFile("$outdir/$filename", $colnames, ',',1,'unix');
          putDelimitedFile("$outdir/$filename", $listobject->queryrecords, ',',0,'unix');
          print("<a href='$outurl/$filename'>Download Basin Totals</a><br>");
       } else {
          $listobject->showlist();
       }

       print("<table width=400><tr><td width=400>Segment includes river segments: <br> <font size=-2>$riversegs</size> </td></tr></table>");

    }
 }


function getBasinLanduse($listobject, $projectid,$pollutants, $gids, $scenarioid, $makecsv, $outdir, $outurl, $theseyears, $seglist='') {

 $manuresourceclasses = '2, 6, 9, 11';
 $fertsourceclasses = '8';

 if ( (strlen($gids) == 0) and ($seglist == '')) {
    print("You must enter either a group name or a list of segments to report");
    return;
 }
 $groups = array();
 if (strlen($gids) > 0) {
    $listobject->querystring = "select groupname, seglist, monstation, area2d(the_geom) as shapearea from seggroups where projectid = $projectid and gid in ($gids) order by groupname";
    $listobject->performQuery();
    # print("$listobject->querystring <br>");

    $groups = $listobject->queryrecords;
 } else {
    $groups = array();
 }

 if (strlen($theseyears) > 0) {
    $yearclause = " and thisyear  in ($theseyears) ";
 } else {
     $yearclause = '';
 }

 # add in manually entered segments
 if (strlen($seglist) > 0) {
    array_push($groups, array('seglist'=>$seglist,'groupname'=>'Custom Group','shapearea'=>1.0, 'monstation'=>'Custom') );
 }

   foreach ($groups as $thisgroup) {

      $seglist = $thisgroup['seglist'];
      $riversegs = "'" . join("','", split(",", $thisgroup['seglist'])) . "'";
      $gname = $thisgroup['groupname'];
      $shapearea = $thisgroup['shapearea'];
      $monstation = $thisgroup['monstation'];
      #print_r($thisgroup);

      print("Summarizing Land-Use Data for $gname <br>");
      if ( ($listobject->tableExists('scenseg_lu')) ) {

         $listobject->querystring = "drop table scenseg_lu";
         $listobject->performQuery();
      }

      $ssluquery = " select a.staid, a.thisyear, a.luname, a.luarea, ";
      $ssluquery .= " b.shorttype as lutype";
      $ssluquery .= " from scen_seggrouplu as a, lutype as b, ";
      $ssluquery .= "    landuses as c ";
      $ssluquery .= " where a.staid = '$monstation' ";
      $ssluquery .= "    $yearclause ";
      $ssluquery .= "    and a.scenarioid = $scenarioid ";
      $ssluquery .= "    and a.projectid = $projectid ";
      $ssluquery .= "    and b.typeid = c.landusetype ";
      $ssluquery .= "    and a.luname = c.hspflu ";
      $ssluquery .= "    and c.projectid = $projectid ";
      $ssluquery .= " order by a.thisyear, a.luname ";

      $listobject->querystring = $ssluquery;
      $listobject->performQuery();

      print("$listobject->querystring <br>");

      if ($makecsv) {
         $pname = ereg_replace("[^a-z^A-Z^0-9]",'',$gname);
         $colnames = array(array_keys($listobject->queryrecords[0]));
         $filename = "lus_$pname.$scenarioid.csv";
         putDelimitedFile("$outdir/$filename", $colnames, ',',1,'unix');
         putDelimitedFile("$outdir/$filename", $listobject->queryrecords, ',',0,'unix');
         print("<a href='$outurl/$filename'>Download Basin Land Use</a><br>");
      } else {
         $listobject->tablename = '';
         $listobject->showlist();
      }

      $crossquery = doGenericCrossTab ($listobject, "( $ssluquery ) as scenseglu ", 'staid,thisyear', 'lutype', 'luarea');
      $listobject->querystring = $crossquery;
    #  print("$listobject->querystring <br>");
      $listobject->performQuery();
      $listobject->tablename = '';

      if ($makecsv) {
         $pname = ereg_replace("[^a-z^A-Z^0-9]",'',$gname);
         $colnames = array(array_keys($listobject->queryrecords[0]));
         $filename = "lu_cross_$pname.$scenarioid.csv";
         putDelimitedFile("$outdir/$filename", $colnames, ',',1,'unix');
         putDelimitedFile("$outdir/$filename", $listobject->queryrecords, ',',0,'unix');
         print("<a href='$outurl/$filename'>Download Basin Land Use Cross-tab</a><br>");
      } else {
         $listobject->showlist();
      }

   }
}


 function calcBasinLoads($listobject, $projectid,$pollutants, $gids, $scenarioid, $makecsv, $outdir, $outurl, $theseyears, $seglist='') {

   # $manuresourceclasses = '2, 6, 9, 11';
    $manuresourceclasses = '2, 5, 6';
    $fertsourceclasses = '8';
    $ag_lutypes = '1';
    $urb_lutypes = '2';
    $sewersourceclasses = '4';
    $allagclasses = $fertsourceclasses . "," . $manuresourceclasses;

       if ( (strlen($gids) == 0) and ($seglist == '')) {
          print("You must enter either a group name or a list of segments to report");
          return;
       }
       $groups = array();
       if (strlen($gids) > 0) {
          $listobject->querystring = "select groupname, seglist, monstation, area2d(the_geom) as shapearea from seggroups where projectid = $projectid and gid in ($gids) order by groupname";
          $listobject->performQuery();
          # print("$listobject->querystring <br>");

          $groups = $listobject->queryrecords;
       } else {
          $groups = array();
       }

       if (strlen($theseyears) > 0) {
          $yearclause = " and a.thisyear in ($theseyears) ";
          $dyclause = " and thisyear in ($theseyears) ";
       } else {
           $yearclause = '';
          $dyclause = '';
       }


    # add in manually entered segments
    if (strlen($seglist) > 0) {
       array_push($groups, array('seglist'=>$seglist,'groupname'=>'Custom Group','shapearea'=>1.0, 'monstation'=>'Custom') );
    }

    foreach ($groups as $thisgroup) {

       $seglist = $thisgroup['seglist'];
       $riversegs = "'" . join("','", split(",", $thisgroup['seglist'])) . "'";
       $gname = $thisgroup['groupname'];
       $shapearea = $thisgroup['shapearea'];
       $monstation = $thisgroup['monstation'];

       print("Summarizing Data for $gname - $seglist <br>");
       if ( !($listobject->tableExists('scen_seggroups')) ) {
          $listobject->querystring = "create table scen_seggroups as ";
       } else {
          $listobject->querystring = "delete from scen_seggroups ";
          $listobject->querystring .= "where scenarioid = '$scenarioid' ";
          $listobject->querystring .= "   and staid = '$monstation' ";
          $listobject->querystring .= "   and projectid = $projectid ";
          $listobject->querystring .= "   and pollutanttype in ($pollutants) ";
          $listobject->querystring .= $dyclause;
          #print("$listobject->querystring <br>");
          print("Deleting Old Records <br>");
          $listobject->performQuery();

          $listobject->querystring = "insert into scen_seggroups (projectid, ";
          $listobject->querystring .= " staid, thisyear, pollutanttype, pollname, scenarioid,";
          $listobject->querystring .= " total_ag, ag_area, ag_perac, total_agmanure, ";
          $listobject->querystring .= " agmanure_perac, total_agfert, agfert_perac, ";
          $listobject->querystring .= " total_aglegume, aglegume_perac, ";
          $listobject->querystring .= " total_urb, urb_area, urb_perac, total_tons, ";
          $listobject->querystring .= " total_perac, total_area, ps_tons, septic_tons, total_sewer, sewer_lbs, ";
          $listobject->querystring .= " atm_tons, atm_perac ) ";
       }

       $listobject->querystring .= "select $projectid,'$monstation'::varchar(64) as staid, ";
       $listobject->querystring .= " h.thisyear, h.typeid, h.shortname, ";
       $listobject->querystring .= " $scenarioid, ";
       $listobject->querystring .= " c.total_ag, i.ag_area, ";
       $listobject->querystring .= " c.ag_lbs/i.ag_area, a.total_agmanure, ";
       $listobject->querystring .= " a.agmanure_lbs/i.ag_area, b.total_agfert, b.agfert_lbs/i.ag_area, ";
       $listobject->querystring .= " c.ag_legume, c.aglegume_lbs/i.ag_area, ";
       $listobject->querystring .= " d.total_urb, i.urb_area, ";
       $listobject->querystring .= " d.urb_perac, e.total_tons, ";
       $listobject->querystring .= " e.total_perac, (e.total_tons*2000.0/e.total_perac), ";
       $listobject->querystring .= " f.ps_tons, g.septic_tons, j.total_sewer, j.sewer_lbs, ";
       $listobject->querystring .= " k.atm_tons, k.atm_perac ";
       $listobject->querystring .= " from ( ";


       # pollutant id
       $listobject->querystring .= "select a.thisyear, b.typeid, b.shortname ";
       $listobject->querystring .= " from scen_lrsegs as a, pollutanttype as b ";
       $listobject->querystring .= " where b.typeid in ($pollutants) ";
       $listobject->querystring .= "    and a.scenarioid = $scenarioid ";
       $listobject->querystring .= $yearclause;
       $listobject->querystring .= " group by a.thisyear, b.typeid, b.shortname ";

       $listobject->querystring .= " ) as h left outer join (";


       # ag land use, urban land use
       $listobject->querystring .= "select a.thisyear, a.ag_area, b.urb_area from ";
       $listobject->querystring .= "    (select a.thisyear, sum(a.luarea) as ag_area ";
       $listobject->querystring .= "    from scen_lrsegs as a, landuses as c ";
       $listobject->querystring .= "    where a.riverseg in ($riversegs) ";
       $listobject->querystring .= "    and a.luname = c.hspflu ";
       $listobject->querystring .= "    and c.major_lutype in ($ag_lutypes) ";
       $listobject->querystring .= $yearclause;
       $listobject->querystring .= "    and a.scenarioid = $scenarioid ";
       $listobject->querystring .= "    and c.projectid = $projectid ";
       $listobject->querystring .= "    group by a.thisyear ";
       $listobject->querystring .= "    order by a.thisyear ";
       $listobject->querystring .= " ) as a, " ;
       $listobject->querystring .= "    (select a.thisyear, sum(a.luarea) as urb_area ";
       $listobject->querystring .= "    from scen_lrsegs as a, landuses as c ";
       $listobject->querystring .= "    where a.riverseg in ($riversegs) ";
       $listobject->querystring .= "    and a.luname = c.hspflu ";
       $listobject->querystring .= "    and c.major_lutype in ($urb_lutypes) ";
       $listobject->querystring .= $yearclause;
       $listobject->querystring .= "    and a.scenarioid = $scenarioid ";
       $listobject->querystring .= "    and c.projectid = $projectid ";
       $listobject->querystring .= "    group by a.thisyear ";
       $listobject->querystring .= "    order by a.thisyear ";
       $listobject->querystring .= " ) as b ";
       $listobject->querystring .= " where a.thisyear = b.thisyear ";

       $listobject->querystring .= " ) as i on (i.thisyear = h.thisyear ) left outer join ( ";


       # ag manure NPS inputs (targets all land uses other than urban)
       $listobject->querystring .= "select a.thisyear, ";
       $listobject->querystring .= " a.pollutanttype, ";
       $listobject->querystring .= " (sum(b.luarea * a.annualapplied))/2000.0";
       $listobject->querystring .= "  as total_agmanure, ";
       $listobject->querystring .= " (sum(b.luarea * a.annualapplied)) ";
       $listobject->querystring .= "  as agmanure_lbs ";
       $listobject->querystring .= " from scen_sourceperunitarea as a, scen_lrsegs as b, landuses as c ";
       $listobject->querystring .= " where b.riverseg in ($riversegs) ";
       $listobject->querystring .= " and a.luname = b.luname ";
       $listobject->querystring .= " and a.luname = c.hspflu ";
       $listobject->querystring .= " and c.major_lutype = 1 ";
       $listobject->querystring .= " and a.sourceclass in ($manuresourceclasses) ";
       $listobject->querystring .= " and a.subshedid = b.subshedid ";
       $listobject->querystring .= " and a.thisyear = b.thisyear";
       $listobject->querystring .= $yearclause;
       $listobject->querystring .= " and a.scenarioid = $scenarioid ";
       $listobject->querystring .= " and b.scenarioid = $scenarioid ";
       $listobject->querystring .= " and c.projectid = $projectid ";
       $listobject->querystring .= " and a.pollutanttype in ($pollutants) ";
       $listobject->querystring .= " group by a.thisyear, a.pollutanttype";
       $listobject->querystring .= " order by a.pollutanttype, a.thisyear";

       $listobject->querystring .= " ) as a on (a.pollutanttype = h.typeid and a.thisyear = h.thisyear ) left outer join ( ";

       # ag Fertilizer NPS inputs (targets all land uses other than urban)
       $listobject->querystring .= "select a.thisyear, ";
       $listobject->querystring .= " a.pollutanttype, ";
       $listobject->querystring .= " (sum(b.luarea * a.annualapplied))/2000.0";
       $listobject->querystring .= "  as total_agfert, ";
       $listobject->querystring .= " (sum(b.luarea * a.annualapplied))";
       $listobject->querystring .= "  as agfert_lbs ";
       $listobject->querystring .= " from scen_sourceperunitarea as a, scen_lrsegs as b, landuses as c ";
       $listobject->querystring .= " where b.riverseg in ($riversegs) ";
       $listobject->querystring .= " and a.luname = b.luname ";
       $listobject->querystring .= " and a.luname = c.hspflu ";
       $listobject->querystring .= " and c.major_lutype = 1 ";
       $listobject->querystring .= " and a.sourceclass in ($fertsourceclasses) ";
       $listobject->querystring .= " and a.subshedid = b.subshedid ";
       $listobject->querystring .= " and a.thisyear = b.thisyear";
       $listobject->querystring .= $yearclause;
       $listobject->querystring .= " and a.scenarioid = $scenarioid ";
       $listobject->querystring .= " and b.scenarioid = $scenarioid ";
       $listobject->querystring .= " and c.projectid = $projectid ";
       $listobject->querystring .= " and a.pollutanttype in ($pollutants) ";
       $listobject->querystring .= " group by a.thisyear, a.pollutanttype";
       $listobject->querystring .= " order by a.pollutanttype, a.thisyear";

       $listobject->querystring .= " ) as b on (b.pollutanttype = h.typeid and b.thisyear = h.thisyear ) left outer join ( ";

       # ALL Ag NPS inputs (targets all land uses other than urban, both manure and fertilizer)
       $listobject->querystring .= "select a.thisyear, ";
       $listobject->querystring .= " a.pollutanttype, ";
       $listobject->querystring .= " (sum(b.luarea * a.annualapplied))/2000.0";
       $listobject->querystring .= "  as total_ag, ";
       $listobject->querystring .= " (sum(b.luarea * a.annualapplied))";
       $listobject->querystring .= "  as ag_lbs, ";
       $listobject->querystring .= " (sum(b.luarea * a.legume))/2000.0";
       $listobject->querystring .= "  as ag_legume, ";
       $listobject->querystring .= " (sum(b.luarea * a.legume))";
       $listobject->querystring .= "  as aglegume_lbs ";
       $listobject->querystring .= " from scen_monperunitarea as a, scen_lrsegs as b, landuses as c ";
       $listobject->querystring .= " where b.riverseg in ($riversegs) ";
       $listobject->querystring .= " and a.luname = b.luname ";
       $listobject->querystring .= " and a.luname = c.hspflu ";
       $listobject->querystring .= " and c.major_lutype = 1 ";
       $listobject->querystring .= " and a.subshedid = b.subshedid ";
       $listobject->querystring .= " and a.thisyear = b.thisyear";
       $listobject->querystring .= $yearclause;
       $listobject->querystring .= " and a.scenarioid = $scenarioid ";
       $listobject->querystring .= " and b.scenarioid = $scenarioid ";
       $listobject->querystring .= " and c.projectid = $projectid ";
       $listobject->querystring .= " and a.pollutanttype in ($pollutants) ";
       $listobject->querystring .= " group by a.thisyear, a.pollutanttype";
       $listobject->querystring .= " order by a.pollutanttype, a.thisyear";

       $listobject->querystring .= " ) as c on (c.pollutanttype = h.typeid and c.thisyear = h.thisyear) left outer join ( ";


       # urban NPS inputs (targets all pervious urban)
       $listobject->querystring .= "select a.thisyear, ";
       $listobject->querystring .= " a.pollutanttype, ";
       $listobject->querystring .= " (sum(b.luarea * a.annualapplied))/2000.0";
       $listobject->querystring .= "  as total_urb, ";
       $listobject->querystring .= " (sum(b.luarea * a.annualapplied))/sum(b.luarea)";
       $listobject->querystring .= "  as urb_perac ";
       $listobject->querystring .= " from scen_monperunitarea as a, scen_lrsegs as b, landuses as c ";
       $listobject->querystring .= " where b.riverseg in ($riversegs) ";
       $listobject->querystring .= " and a.luname = b.luname ";
       $listobject->querystring .= " and a.luname = c.hspflu ";
       $listobject->querystring .= " and c.landusetype = 1 ";
       $listobject->querystring .= " and a.subshedid = b.subshedid ";
       $listobject->querystring .= " and a.thisyear = b.thisyear";
       $listobject->querystring .= $yearclause;
       $listobject->querystring .= " and a.scenarioid = $scenarioid ";
       $listobject->querystring .= " and b.scenarioid = $scenarioid ";
       $listobject->querystring .= " and c.projectid = $projectid ";
       $listobject->querystring .= " and a.pollutanttype in ($pollutants) ";
       $listobject->querystring .= " group by a.thisyear, a.pollutanttype";
       $listobject->querystring .= " order by a.pollutanttype, a.thisyear";

       $listobject->querystring .= " ) as d on (d.pollutanttype = h.typeid and d.thisyear = h.thisyear ) left outer join (";

       # all NPS inputs (ag combined with urban)
       $listobject->querystring .= "select a.thisyear, ";
       $listobject->querystring .= " a.pollutanttype, ";
       $listobject->querystring .= " (sum(b.luarea * a.annualapplied))/2000.0";
       $listobject->querystring .= "  as total_tons, ";
       $listobject->querystring .= "(sum(b.luarea * a.annualapplied))/sum(b.luarea)";
       $listobject->querystring .= "  as total_perac ";
       $listobject->querystring .= " from scen_monperunitarea as a, scen_lrsegs as b ";
       $listobject->querystring .= " where b.riverseg in ($riversegs) ";
       $listobject->querystring .= " and a.luname = b.luname ";
       $listobject->querystring .= " and a.subshedid = b.subshedid ";
       $listobject->querystring .= " and a.thisyear = b.thisyear";
       $listobject->querystring .= $yearclause;
       $listobject->querystring .= " and a.scenarioid = $scenarioid ";
       $listobject->querystring .= " and b.scenarioid = $scenarioid ";
       $listobject->querystring .= " and a.pollutanttype in ($pollutants) ";
       $listobject->querystring .= " group by a.thisyear, a.pollutanttype";
       $listobject->querystring .= " order by a.pollutanttype, a.thisyear";

       $listobject->querystring .= " ) as e on (e.pollutanttype = h.typeid and e.thisyear = h.thisyear ) left outer join (";

       # point source inputs
       $listobject->querystring .= "select a.thisyear, ";
       $listobject->querystring .= " a.constit as pollutanttype, sum(a.constit_mass)/2000.0 as ps_tons ";
       $listobject->querystring .= " from scen_pointsource as a ";
       $listobject->querystring .= " where a.riverseg in ($riversegs) ";
       $listobject->querystring .= "    and a.constit in ($pollutants) ";
       $listobject->querystring .= "    and a.scenarioid = $scenarioid ";
       $listobject->querystring .= $yearclause;
       $listobject->querystring .= " group by a.thisyear, a.constit";
       $listobject->querystring .= " order by a.constit, a.thisyear";

       $listobject->querystring .= " ) as f on (f.pollutanttype = h.typeid and f.thisyear = h.thisyear ) left outer join (";

       # septic inputs
       $listobject->querystring .= "select a.thisyear, ";
       $listobject->querystring .= " a.pollutanttype, (sum(a.septicload * 365.0))/2000.0 as septic_tons ";
       $listobject->querystring .= " from scen_septic as a ";
       $listobject->querystring .= " where a.riverseg in ($riversegs) ";
       $listobject->querystring .= "    and a.scenarioid = '$scenarioid' ";
       $listobject->querystring .= "    and a.projectid = $projectid ";
       $listobject->querystring .= "    and a.pollutanttype in ($pollutants) ";
       $listobject->querystring .= $yearclause;
       $listobject->querystring .= " group by a.thisyear, a.pollutanttype";
       $listobject->querystring .= " order by a.pollutanttype, a.thisyear";

       $listobject->querystring .= " ) as g on (g.pollutanttype = h.typeid and g.thisyear = h.thisyear ) left outer join ( ";

       # ag Fertilizer NPS inputs (targets all land uses other than urban)
       $listobject->querystring .= "select a.thisyear, ";
       $listobject->querystring .= " a.pollutanttype, ";
       $listobject->querystring .= " (sum(b.luarea * a.annualapplied))/2000.0";
       $listobject->querystring .= "  as total_sewer, ";
       $listobject->querystring .= " (sum(b.luarea * a.annualapplied))";
       $listobject->querystring .= "  as sewer_lbs ";
       $listobject->querystring .= " from scen_sourceperunitarea as a, scen_lrsegs as b ";
       $listobject->querystring .= " where b.riverseg in ($riversegs) ";
       $listobject->querystring .= " and a.luname = b.luname ";
       $listobject->querystring .= " and a.sourceclass in ($sewersourceclasses) ";
       $listobject->querystring .= " and a.subshedid = b.subshedid ";
       $listobject->querystring .= " and a.thisyear = b.thisyear";
       $listobject->querystring .= $yearclause;
       $listobject->querystring .= " and a.scenarioid = $scenarioid ";
       $listobject->querystring .= " and b.scenarioid = $scenarioid ";
       $listobject->querystring .= " and a.pollutanttype in ($pollutants) ";
       $listobject->querystring .= " group by a.thisyear, a.pollutanttype";
       $listobject->querystring .= " order by a.pollutanttype, a.thisyear";

       $listobject->querystring .= " ) as j on (b.pollutanttype = j.pollutanttype and b.thisyear = j.thisyear ) left outer join (";

       # atmonspheric deposition inputs
       $listobject->querystring .= "select a.thisyear, ";
       $listobject->querystring .= " a.constit, sum(b.luarea * a.dep)/2000.0 as atm_tons, ";
       $listobject->querystring .= " (sum(b.luarea * a.dep))/sum(b.luarea) as atm_perac ";
       $listobject->querystring .= " from scen_lrseg_atmosdep as a, scen_lrsegs as b ";
       $listobject->querystring .= " where a.riverseg in ($riversegs) ";
       $listobject->querystring .= "    and b.riverseg = a.riverseg ";
       $listobject->querystring .= "    and b.landseg = a.landseg ";
       $listobject->querystring .= "    and b.thisyear = a.thisyear ";
       $listobject->querystring .= "    and a.scenarioid = $scenarioid ";
       $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
       $listobject->querystring .= "    and a.constit in ($pollutants) ";
       $listobject->querystring .= $yearclause;
       $listobject->querystring .= " group by a.thisyear, a.constit";
       $listobject->querystring .= " order by a.constit, a.thisyear";

       $listobject->querystring .= " ) as k on (k.constit = h.typeid and k.thisyear = h.thisyear ) ";

      print("Archiving New Records <br>");
       #print("$listobject->querystring<br>");
       $listobject->performQuery();

     #

    }
  }


function calcBasinBMPEffects($listobject, $projectid,$pollutants, $gids, $scenarioid, $theseyears, $seglist, $debug) {

   if ( (strlen($gids) == 0) and ($seglist == '')) {
      print("You must enter either a group name or a list of segments to report");
      return;
   }
   $groups = array();
   if (strlen($gids) > 0) {
      $listobject->querystring = "select groupname, seglist, monstation, area2d(the_geom) as shapearea from seggroups where projectid = $projectid and gid in ($gids) order by groupname";
      $listobject->performQuery();
      # print("$listobject->querystring <br>");

      $groups = $listobject->queryrecords;
   } else {
      $groups = array();
   }

   if (strlen($theseyears) > 0) {
      $yearclause = " and a.thisyear in ($theseyears) ";
      $byearclause = " and b.thisyear in ($theseyears) ";
      $dyclause = " and thisyear in ($theseyears) ";
   } else {
      $yearclause = '';
      $dyclause = '';
   }


    # add in manually entered segments
    if (strlen($seglist) > 0) {
       array_push($groups, array('seglist'=>$seglist,'groupname'=>'Custom Group','shapearea'=>1.0, 'monstation'=>'Custom') );
    }

   foreach ($groups as $thisgroup) {

      $seglist = $thisgroup['seglist'];
      $riversegs = "'" . join("','", split(",", $thisgroup['seglist'])) . "'";
      $gname = $thisgroup['groupname'];
      $shapearea = $thisgroup['shapearea'];
      $monstation = $thisgroup['monstation'];

      print("Delete Old BMP Effects for $gname <br>");
      $listobject->querystring = "  delete from scen_seggroup_bmp ";
      $listobject->querystring .= " where scenarioid = $scenarioid  ";
      $listobject->querystring .= "    and staid = '$monstation' ";
      $listobject->querystring .= "    $dyclause ";
      $listobject->querystring .= "    and pollutanttype in ($pollutants) ";
      if ($debug) { print("<br>$listobject->querystring ;<br>"); }
      $listobject->performQuery();
      $split = $listobject->startSplit();
      if ($debug) { print("Query Time: $split<br>"); }


      print("Summarizing BMP Effects for $gname $thisyear <br>");

      $listobject->querystring = "  insert into scen_seggroup_bmp (scenarioid, staid, pollutanttype, ";
      $listobject->querystring .= "    thisyear, bmp_total, bmp_pct ) ";
      $listobject->querystring .= " select $scenarioid, '$monstation'::varchar(64) as staid, ";
      $listobject->querystring .= "    b.constit, a.thisyear, ";
      $listobject->querystring .= "    round(sum( (1.0 - b.passthru) * c.eof_target * a.luarea))  ";
      $listobject->querystring .= "       as bmp_lbs, ";
      $listobject->querystring .= "    round( (sum( (1.0 - b.passthru) * c.eof_target * a.luarea) ";
      $listobject->querystring .= "    /sum(c.eof_target * a.luarea))::numeric,4) ";
      $listobject->querystring .= "       as bmp_pct  ";
      $listobject->querystring .= " from scen_lrsegs as a, scen_masslinks as b,  ";
      $listobject->querystring .= "    eof_subshed_out as c, pollutanttype as d  ";
      $listobject->querystring .= " where a.subshedid = c.subshedid  ";
      $listobject->querystring .= "    and b.lrseg = a.lrseg   ";
      $listobject->querystring .= "    and a.scenarioid = $scenarioid  ";
      $listobject->querystring .= "    and c.scenarioid = $scenarioid  ";
      $listobject->querystring .= "    and b.scenarioid = $scenarioid  ";
      $listobject->querystring .= "    and a.thisyear = b.thisyear  ";
      $listobject->querystring .= "    and a.riverseg in ( $riversegs ) ";
      $listobject->querystring .= "    $yearclause ";
      $listobject->querystring .= "    $byearclause ";
      $listobject->querystring .= "    and a.luname = b.luname  ";
      $listobject->querystring .= "    and a.luname = c.luname  ";
      $listobject->querystring .= "    and b.constit = d.typeid  ";
      $listobject->querystring .= "    and d.shortname = c.constit  ";
      $listobject->querystring .= "    and b.constit in ($pollutants) ";
      $listobject->querystring .= " group by a.thisyear, b.constit ";
      $listobject->startSplit();
      if ($debug) { print("<br>$listobject->querystring ;<br>"); }
      $listobject->performQuery();
      $split = $listobject->startSplit();
      if ($debug) { print("Query Time: $split<br>"); }

   }
}



function getBasinPointSources($listobject, $projectid,$pollutants, $groupname, $scenario) {

    if ($groupname == '') {
       $listobject->querystring = "select groupname, seglist, monstation, area2d(the_geom) as shapearea from seggroups where projectid = $projectid order by groupname";
    } else {
       $listobject->querystring = "select groupname, seglist, monstation, area2d(the_geom) as shapearea from seggroups where projectid = $projectid and groupname in ($groupname) order by groupname";
    }

#   $listobject->querystring = "select groupname, seglist from seggroups where projectid = $projectid order by groupname";
    $listobject->performQuery();
   # print("$listobject->querystring <br>");

    $groups = $listobject->queryrecords;

    foreach ($groups as $thisgroup) {

       $seglist = $thisgroup['seglist'];
       $riversegs = "'" . join("','", split(",", $thisgroup['seglist'])) . "'";
       $gname = $thisgroup['groupname'];
       $shapearea = $thisgroup['shapearea'];
       $monstation = $thisgroup['monstation'];

       $listobject->querystring = "select a.thisyear, '$gname' as groupname, ";
       $listobject->querystring .= " b.typeid as pollutanttype, (sum(a.load * 365.0))/2000.0 as ps_tons ";
       $listobject->querystring .= " from psdata as a, pollutanttype as b, ps_signif_dd as c, seggroups as d ";
       $listobject->querystring .= " where  c.the_geom && d.the_geom";
       $listobject->querystring .= " and contains(d.the_geom, c.the_geom) ";
       $listobject->querystring .= " and a.parameter = b.shortname ";
       $listobject->querystring .= " and a.npdes = c.npdes ";
       $listobject->querystring .= " and d.groupname = '$gname' ";
       $listobject->querystring .= " and b.typeid in ($pollutants) ";
       $listobject->querystring .= " group by a.thisyear, d.groupname, b.typeid";
       $listobject->querystring .= " order by d.groupname, b.typeid, a.thisyear";

       print("$listobject->querystring<br><br>");
       $listobject->performQuery();
       $listobject->tablename = 'seggroups';
       $listobject->showList();

       #return $listobject->queryrecords;

    }
}


function calcBasinLanduse($listobject, $scenarioid, $theseyears, $gids, $seglist, $projectid ) {

    if ( (strlen($gids) == 0) and ($seglist == '')) {
       print("You must enter either a group name or a list of segments to report");
       return;
    }
    $groups = array();
    if (strlen($gids) > 0) {
       $listobject->querystring = "select groupname, seglist, monstation, area2d(the_geom) as shapearea from seggroups where projectid = $projectid and gid in ($gids) order by groupname";
       $listobject->performQuery();
      #  print("$listobject->querystring <br>");

       $groups = $listobject->queryrecords;
    } else {
       $groups = array();
    }

    # add in manually entered segments
    if (strlen($seglist) > 0) {
       array_push($groups, array('seglist'=>$seglist,'groupname'=>'Custom Group','shapearea'=>1.0, 'monstation'=>'Custom') );
    }

    foreach ($groups as $thisgroup) {

       $seglist = $thisgroup['seglist'];
       $riversegs = "'" . join("','", split(",", $thisgroup['seglist'])) . "'";
       $gname = $thisgroup['groupname'];
       $shapearea = $thisgroup['shapearea'];
       $monstation = $thisgroup['monstation'];

      if (strlen($theseyears) > 0) {
         $yearclause = " and thisyear in ($theseyears) ";
      } else {
         $yearclause = '';
      }

      if ( !($listobject->tableExists('scen_seggrouplu')) ) {
         $listobject->querystring = "create table scen_seggrouplu as ";
      } else {
         $listobject->querystring = "delete from scen_seggrouplu  ";
         $listobject->querystring .= "where scenarioid = '$scenarioid' ";
         $listobject->querystring .= "   and staid = '$monstation' ";
         $listobject->querystring .= "   and projectid = $projectid ";
         $listobject->querystring .= "   $yearclause ";
         $listobject->performQuery();

         $listobject->querystring = "insert into scen_seggrouplu  ";
         $listobject->querystring .= "(staid, thisyear, luname, luarea, scenarioid, projectid ) ";
      }

      print("Summarizing Land-Use Data for $gname - <font size=-2>$seglist</font> <br>");


      $listobject->querystring .= " select '$monstation'::varchar(64) as staid, ";
      $listobject->querystring .= " thisyear, luname, ";
      $listobject->querystring .= " sum(luarea) as luarea, ";
      $listobject->querystring .= " $scenarioid as scenarioid, $projectid ";
      $listobject->querystring .= " from scen_lrsegs ";
      $listobject->querystring .= " where riverseg in ($riversegs) ";
      $listobject->querystring .= "    $yearclause ";
      $listobject->querystring .= "    and scenarioid = $scenarioid ";
      $listobject->querystring .= " group by thisyear, luname ";
    #  print("$listobject->querystring<br>");
      $listobject->performQuery();
   }

}


function showLandUseLoads ($listobject, $riversegs, $polltype, $projectid, $scenarioid, $thisyear, $debug) {

   # get the application rates and totals from different sources
   # reported in cross tab format
   if (strlen($polltype) > 0) {
      $pc = " and a.pollutanttype = $polltype ";
      $cpc = " and c.typeid = $polltype ";
   } else {
      print ("You must specify a single pollutatntype<br>");
      die;
   }
   if (strlen($riversegs) > 0) {
      $rc = " and a.riverseg in ($riversegs) ";
   } else {
      $rc = '';
   }

   #$qs = "( ";
   $qs = "create temp table sourcelu as  ";
   $qs .= " select a.subshedid, a.luname, a.sourcename, a.pollutanttype, a.luarea, ";
   $qs .= "    sum(b.annualapplied) as annualapplied ";
   $qs .= " from  ";
   $qs .= " ( select a.subshedid, a.luname, a.luarea, b.sourcename, c.typeid as pollutanttype ";
   $qs .= "  FROM scen_lrsegs as a, sources as b, pollutanttype as c, sourcepollutants as d ";
   $qs .= "  WHERE b.projectid = $projectid ";
   $qs .= "     and a.scenarioid = $scenarioid ";
   $qs .=       $rc;
   $qs .= "      and a.thisyear = $thisyear ";
   $qs .= "      and c.typeid = d.pollutanttype ";
   $qs .= "      and b.typeid = d.sourcetypeid ";
   $qs .=       $cpc;
   $qs .= " ) as a left outer join ";
   $qs .= " ( select a.subshedid, a.luname, b.sourcename, a.annualapplied, a.pollutanttype ";
   $qs .= "  from scen_sourceperunitarea as a, sources as b ";
   $qs .= "  where a.scenarioid = $scenarioid";
   $qs .= "      and a.thisyear = $thisyear ";
   $qs .= "      and b.sourceid = a.sourceid ";
   $qs .= $pc;
   $qs .= "      and a.subshedid in (select subshedid ";
   $qs .= "      from scen_lrsegs ";
   $qs .= "      where thisyear = 2002 ";
   $qs .= "         and scenarioid = $scenarioid";
   $qs .= "      group by subshedid ) ";
   $qs .= " ) as b on ";
   $qs .= "    ( a.subshedid = b.subshedid ";
   $qs .= "       and a.sourcename = b.sourcename ";
   $qs .= "       and a.pollutanttype = b.pollutanttype ";
   $qs .= "       and a.luname = b.luname ) ";
   $qs .= " group by a.subshedid, a.luname, a.sourcename, a.pollutanttype, a.luarea ";
   #$qs .= " ) as sourcelu ";
   print("$qs <br>");
   $listobject->querystring = $qs;
   $listobject->performQuery();

  # $ctq = doGenericCrossTab ($listobject, $qs, 'subshedid, luname, luarea, pollutanttype' , 'sourcename', 'annualapplied' );
   $ctq = doGenericCrossTab ($listobject, 'sourcelu', 'subshedid, luname, luarea, pollutanttype' , 'sourcename', 'annualapplied' );

   #print("$ctq <br>");

   $listobject->querystring = $ctq;
   $listobject->performQuery();
   $listobject->showList();


}



?>
