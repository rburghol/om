<html>
<body>
<h3>Total Sources Generated in The project</h3>

<?php

   # set up db connection
   include('config.php');
   #include('qa_functions.php');
   #include('ms_config.php');
   /* also loads the following variables:
      $libpath - directory containing library files
      $indir - directory for files to be read
      $outdir - directory for files to be written
   */


   # start a master timer
   $timer->startSplit();

   if (isset($_GET["projectid"])) {
      $projectid = $_GET["projectid"];
   } else {
      $projectid = -1;
   }

   $groupings = array();
   if (isset($_POST["groupings"])) {
      $groupings = $_POST['groupings'];
   }

   if (isset($_POST["projectid"])) {
      $projectid = $_POST["projectid"];
      $showbreakdown = $_POST["showbreakdown"];
      $showapp = $_POST['showapp'];
      $showprod = $_POST['showprod'];
      $donm = $_POST['donm'];
      $actiontype = $_POST['actiontype'];
      $subshedids = $_POST['subshedids'];
      $showyears = $_POST['showyears'];
      $makecsv = $_POST['makecsv'];
      $scenarioid = $_POST['scenarioid'];
   }

   if ($showyears == '') {
      $showyears = 1992;
   }

   if (count($groupings) > 0) {
      $grouplist = join(',',$_POST['groupings']);
   }

   $projname = $projinfo['projectname'];
   print("<form action='$scriptname' method=POST>");
   showHiddenField('projectid',$projectid);
   showHiddenField('actiontype',1);
   print("<b>Enter Desired Subwatersheds:</b>");
   showWidthTextField('subshedids',$subshedids,48);
   print("<br><b>Enter Year:</b>");
   showWidthTextField('showyears',$showyears,12);
   print("<br><b>Or Select Subshed Groups</b>: ");
   showMultiList2($listobject, 'groupings', 'groupings', 'subwatersheds', 'groupname', '', "projectid = $projectid", 'groupname', $debug);
   print("<br><b>Present Tables as downloadable csv's? </b>");
   print("<br><b>Select a scenario:</b>");
   showList($listobject, 'scenarioid', 'scenario', 'scenario', 'scenarioid', "projectid = $projectid", $scenarioid, $debug);
   showTFListType('makecsv',$makecsv,1);
   showSubmitButton('submit','Calculate Loads');
   print("</form>");

   if ( ($projectid <> -1) and ($actiontype)) {

      $subsheds = '';
      $ssdel = '';

      if (strlen($grouplist) > 0) {
         $subsheds .= $grouplist;
         $ssdel = ',';
      }
      if (strlen($subshedids) > 0) {
         $subsheds .= "$ssdel" . "$subshedids";
      }

      if ( (strlen($subshedids) > 0) or ($grouplist == '') ) {
         array_push($groupings, $subshedids);
      }

      $ssclause = '';
      if (count(split(',', $subsheds)) > 0) {
         $slist = join("','", split(',', $subsheds));
         $ssclause = "and subshedid in ('$slist')";
      }

      $outfile = "$outdir/test.accum.txt";
      if ($debug) {
         print("Outfile name: $outfile <br>");
      }

      ###############################################
      # miscellanious setup custom code here:
      ###############################################

      ###############################################
      # end miscellanious setup custom code
      ###############################################



      if (count(split(',', $showyears)) > 0) {
         $yearar = split(',', $showyears);
      } else {
         $yearar = array($showyears);
      }

      foreach ($yearar as $thisyear) {

         print("<hr><h3>Testing for $thisyear:</h3><br>");

         $i = 0;

         foreach ($groupings as $thissubgroup) {

            $subwatersheds = $thissubgroup;

            $i++;

            if ( (strlen($subwatersheds) > 0) ) {
               $wc = "where subshedid in ($subwatersheds)";
               $cwc = "where a.subshedid in ($subwatersheds)";
               $nwc = " subshedid in ($subwatersheds)";
               $yssc = " inputyields.subshedid in ($subwatersheds)";
            } else {
               $wc = '';
               $cwc = '';
               $nwc = ' 1 = 1 ';
               $yssc = ' 1 = 1 ';
            }

            $listobject->querystring = "select groupname from groupings where projectid = $projectid and subwatersheds = '$subwatersheds'";
            print("$listobject->querystring<br>");
            $listobject->performQuery();
            $groupname = $listobject->getRecordValue(1,'groupname');


            ###############################################
            # custom sub-queries or formatting goes here
            ###############################################

            $typeid = 1; #nutrient management
            $subsheds = split(",", $subwatersheds);
            print("Interpolating yield values.<br>");

            $yieldkeys = 'subshedid,luname,scenarioid,projectid,nm_planbase';
            $yieldvals = 'maxn,maxp,total_acres,legume_n,uptake_n,uptake_p,total_n,total_p,nrate,prate,optn,optp,maxnrate,maxprate,mean_uptn,mean_uptp,n_urratio,p_urratio';
            $extrawhere = " inputyields.scenarioid = $scenarioid and $yssc ";

            genericMultiInterp($listobject, $thisyear, 'inputyields', 'interpyields', 'thisyear', $yieldkeys, $yieldvals, 3, 0.0, 1, -99999, $debug, $extrawhere);

            $listobject->querystring = "  delete from inputyields ";
            $listobject->querystring .= " where scenarioid = $scenarioid ";
            $listobject->querystring .= "    and $nwc ";
            $listobject->querystring .= "    and thisyear = $thisyear ";
            print("$listobject->querystring<br>");
            $listobject->performQuery();

            $listobject->querystring = "  insert into inputyields (subshedid,luname, scenarioid, ";
            $listobject->querystring .= "    projectid, nm_planbase, ";
            $listobject->querystring .= "    thisyear, maxn,maxp, total_acres,legume_n, ";
            $listobject->querystring .= "    uptake_n,uptake_p,total_n,total_p,nrate,";
            $listobject->querystring .= "    prate,optn, optp,maxnrate,maxprate, ";
            $listobject->querystring .= "    mean_uptn, mean_uptp,n_urratio,p_urratio) ";
            $listobject->querystring .= " select subshedid,luname, $scenarioid, ";
            $listobject->querystring .= "    projectid, nm_planbase, ";
            $listobject->querystring .= "    thisyear, maxn,maxp, total_acres,legume_n, ";
            $listobject->querystring .= "    uptake_n,uptake_p,total_n,total_p,nrate,";
            $listobject->querystring .= "    prate,optn, optp,maxnrate,maxprate, ";
            $listobject->querystring .= "    mean_uptn, mean_uptp,n_urratio,p_urratio ";
            $listobject->querystring .= " from interpyields ";
            $listobject->querystring .= " where scenarioid = $scenarioid ";
            $listobject->querystring .= "    and $nwc ";
            $listobject->querystring .= "    and thisyear = $thisyear ";
            print("$listobject->querystring<br>");
            $listobject->performQuery();


/*
            uptakeInterp($listobject, $scenarioid, $thisyear, $subwatersheds, 'uptakeinterp', $debug);

            #$listobject->querystring = "select * from uptakeinterp where luname = 'nal' order by stcofips";
            #print("$listobject->querystring<br>");
            #$listobject->performQuery();
            #$listobject->showList();

            $listobject->querystring = "  delete from cb_uptake ";
            $listobject->querystring .= " where scenarioid = $scenarioid ";
            $listobject->querystring .= "    and $nwc ";
            $listobject->querystring .= "    and thisyear = $thisyear ";
            print("$listobject->querystring<br>");
            $listobject->performQuery();

            $listobject->querystring = "  insert into cb_uptake (stcofips, scenarioid, luname, ";
            $listobject->querystring .= "    thisyear, cb_region, jan, feb, mar, apr, may, jun, ";
            $listobject->querystring .= "    jul, aug, sep, oct, nov, dec) ";
            $listobject->querystring .= " select stcofips, scenarioid, luname, thisyear, ";
            $listobject->querystring .= "    cb_region, jan, feb, mar, apr, may, jun, jul, aug, ";
            $listobject->querystring .= "    sep, oct, nov, dec ";
            $listobject->querystring .= " from uptakeinterp ";
            $listobject->querystring .= " where scenarioid = $scenarioid ";
            $listobject->querystring .= "    and $nwc ";
            $listobject->querystring .= "    and thisyear = $thisyear ";
            print("$listobject->querystring<br>");
            $listobject->performQuery();
*/

            ###############################################
            # end custom code
            ###############################################



         }
      }
      $totaltime = $timer->startSplit();
      print("Total Time of Execution: $totaltime seconds");

   }

?>

</body>

</html>