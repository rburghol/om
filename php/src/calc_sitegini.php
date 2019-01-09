<?php


# test partial monthly values

include('./config.php');

// now, conenct the db to the va_hydro db, which has the necessary R functions
$dbname = 'va_hydro';
$dbuser = 'usgs_rt';
$dbpass = 'r0bert_rw';
$connstring = "host=$dbip dbname=$dbname user=$dbuser password=$dbpass";
$dbconn = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);
$listobject->connstring = $connstring;
$listobject->dbconn = $dbconn;

if (isset($_POST['submit'])) {
   $sitelist = $_POST['sitelist'];
   $startdate = $_POST['startdate'];
   $enddate = $_POST['enddate'];
   $mindays = $_POST['mindays'];
   $selectmonths = $_POST['selectmonths'];
}

# Opequon Creek: 01615000
# North River, Burketown: 01622000
# SF Shenandoah, Front Royal: 01631000
# NF Shenandoah at Strasburg: 01634000
# Smith Creek at new Market: 01632900
# Goose Creek, Leesburg: 01644000
# Cedar Run, Catlett: 01656000
# SF Quantico, Independence: 01658500
# Rappahanock, Remington: 01664000
# Rapidan, 01667500 RAPIDAN RIVER NEAR CULPEPER
# 01673800 PO RIVER NEAR SPOTSYLVANIA, VA

print("<form action='calc_sitegini.php' method=post>");
print("<br><b>Site ID:</b> ");
showWidthTextField('sitelist', $sitelist, 12);
print("<br><b>Start Date:</b> ");
showWidthTextField('startdate', $startdate, 12);
print("<br><b>End Date:</b> ");
showWidthTextField('enddate', $enddate, 12);
print("<br> Minimum Number of Days to include in analysis: ");
showWidthTextField('mindays', $mindays, 12);
print("<br> Do Monthly Analysis of select months (csv): ");
showWidthTextField('selectmonths', $selectmonths, 12);
print("<br> Retrieve all gages in database? (over-rides site list)");
showCheckBox('allgages', 1, $_POST['allgages'], $onclick='', 0, 0);
print("<br> ");
showSubmitButton('submit','submit');
print("</form>");

$debug = 0;
$projectid = 3;
$siteinfocode = 3;
$stype = 1;

if (isset($_POST['submit'])) {
   $sites = split(',', $sitelist);
   $dateobj = new DateTime($startdate);
   $startyear = $dateobj->format('Y');
   $dateobj = new DateTime($enddate);
   $endyear = $dateobj->format('Y');
   
   if (isset($_POST['allgages'])) {
      $listobject->querystring = "  select pointname ";
      $listobject->querystring .= " from proj_points ";
      $listobject->querystring .= " where projectid = $projectid ";
      $listobject->querystring .= "    and pointtype = 1 "; 
      if ($debug) { 
         print("$listobject->querystring ; <br>");
      }
      $listobject->performQuery();
      $sites = array();
      $sno = 0;
      foreach ($listobject->queryrecords as $thisrec) {
         array_push($sites, $thisrec['pointname']);
         $sno += 1;
      }
      print("Found $sno sites in database.<br>");
   }
   
   $outfile = "$outdir/gini" . $sno . ".data";
   $moutfile = "$outdir/gini_month" . $sno . ".data";
   $colnames = array('gage','ginicoeff');
   putDelimitedFile("$outfile",$colnames,"\t",1,'unix');
   $colnames = array('gage','month','year','ginicoeff');
   putDelimitedFile("$moutfile",$colnames,"\t",1,'unix');
   print("Storing data in $outfile <br");

   foreach ($sites as $siteno) {
      $usgsobj = new USGSGageObject;
      $usgsobj->listobject = $listobject;
      $usgsobj->name = $siteno;
      $usgsobj->staid = $siteno;
      $usgsobj->startdate = $startdate;
      $usgsobj->enddate = $enddate;
      //$usgsobj->max_memory_values = 10;
      $listobject->show = 1;
      
      print("Retrieving data for $siteno <br>");
      $usgsobj->init();
      #print_r($usgsobj->tsvalues);
      #$listobject->debug = 1;
      $usgsobj->tsvalues2listobject(array('thisdate','Qout'));
      $tbl = $usgsobj->db_cache_name;
      
      $listobject->querystring = " select count(*) as numrecs from $tbl ";
      if ($debug) { 
         print("$listobject->querystring ; <br>");
         $listobject->showList();
      }
      $listobject->performQuery();
      $numrecs = $listobject->getRecordValue(1,'numrecs');
         
      if ($debug) {
         $listobject->querystring = "  select * from $tbl ";
         print("$listobject->querystring ; <br>");
         $listobject->performQuery();
         $listobject->showList();
      }
      
      if ($numrecs >= $mindays) {
      
         # screen for tidal influence (negative flow values)
         $listobject->querystring = "  select count(*) as numneg from $tbl ";
         $listobject->querystring .= " where Qout is not null and Qout < 0 ";
         if ($debug) { 
            print("$listobject->querystring ; <br>");
         }
         $listobject->performQuery();
         $numneg = $listobject->getRecordValue(1,'numneg');
         
         if ($numneg == 0) {
            print("Clearing old stats from local database for $siteno.<br>");
            $listobject->querystring = "  delete from stats_site_period ";
            $listobject->querystring .= " where site_no = '$siteno' ";
            $listobject->querystring .= "    and startdate = '$startdate' ";
            $listobject->querystring .= "    and enddate = '$enddate' ";
            $listobject->querystring .= "    and datatype = 'gini' ";
            if ($debug) { 
               print("$listobject->querystring ; <br>"); 
            }
            $listobject->performQuery();

            print("Calculating Gini values for $siteno <br>");

            $listobject->querystring = "  select '$siteno' as siteno, wateryear, gini(array_accum(\"Qout\")), ";
            $listobject->querystring .= " avg(\"Qout\") as Qmean  ";
            $listobject->querystring .= " from (  ";
            $listobject->querystring .= "   select  ";
            $listobject->querystring .= "      CASE  ";
            $listobject->querystring .= "         WHEN extract(month from thisdate) < 10 THEN (extract(year from thisdate) - 1)  ";
            $listobject->querystring .= "         ELSE extract(year from thisdate)  ";
            $listobject->querystring .= "      END as wateryear,  ";
            $listobject->querystring .= "      \"Qout\" ";
            $listobject->querystring .= "   from $tbl  ";
            $listobject->querystring .= "   where \"Qout\" is not null  ";
            $listobject->querystring .= " ) as foo ";
            $listobject->querystring .= " group by wateryear "; 
            $listobject->querystring .= " order by wateryear ";
            if ($debug) { 
               print("$listobject->querystring ; <br>"); 
            }
            $listobject->performQuery();
            $listobject->showlist();
            $ginirecs = $listobject->queryrecords;

            $listobject->querystring = "  select '$siteno' as siteno, extract(year from thisdate) as thisyear, ";
            $listobject->querystring .= "    extract(month from thisdate) as thismonth, gini(array_accum(\"Qout\")), ";
            $listobject->querystring .= "    avg(\"Qout\") as Qmean  ";
            $listobject->querystring .= " from $tbl ";
            if (strlen($selectmonths) > 0) {
               $listobject->querystring .= " where extract(month from thisdate) in ($selectmonths) ";
            }
            $listobject->querystring .= "group by thisyear, thismonth "; 
            $listobject->querystring .= "order by thismonth, thisyear ";
            if ($debug) { 
               print("$listobject->querystring ; <br>"); 
            }
            $listobject->performQuery();
            $listobject->showlist();

            $listobject->querystring = " select count(*) as numvalid from ";
            $listobject->querystring .= " ( select '$siteno' as siteno, gini(array_accum(\"Qout\")) as gini ";
            $listobject->querystring .= "   from (  ";
            $listobject->querystring .= "     select  ";
            $listobject->querystring .= "        CASE  ";
            $listobject->querystring .= "           WHEN extract(month from thisdate) < 10 THEN (extract(year from thisdate) - 1)  ";
            $listobject->querystring .= "           ELSE extract(year from thisdate)  ";
            $listobject->querystring .= "        END as wateryear,  ";
            $listobject->querystring .= "        \"Qout\" ";
            $listobject->querystring .= "     from $tbl  ";
            $listobject->querystring .= "     where \"Qout\" is not null  ";
            $listobject->querystring .= "   ) as foo ";
            $listobject->querystring .= "   group by wateryear "; 
            $listobject->querystring .= "   order by wateryear ";
            $listobject->querystring .= " ) as foo ";
            $listobject->querystring .= " where gini <> 'NaN' ";
            if ($debug) { 
               print("$listobject->querystring ; <br>"); 
            }
            $listobject->performQuery();
            $numvalid = $listobject->getRecordValue(1,'numvalid');
            print("Found $numvalid records<br>");
            if ($numvalid == ($endyear - $startyear)) {
               print("Appending values to $outfile<br>");
               # format for output if records exist for each year in the dataset
               $outarr = nestArraySprintf("%s\t%s\t%1.6f", $ginirecs);
               #print_r($outarr);

               putArrayToFilePlatform("$outfile", $outarr,0,'unix');
            }

            $listobject->querystring = "select gini(array_accum(\"Qout\")) as gini, count(*), avg(\"Qout\") ";
            $listobject->querystring .= " from $tbl  ";
            $listobject->querystring .= " where \"Qout\" is not null  ";
            $listobject->performQuery();
            //if ($debug) { 
               print("$listobject->querystring ; <br>"); 
            //}
            $gini = $listobject->getRecordValue(1,'gini');
            $listobject->showlist();
            
            if ($debug) { 
               $listobject->showList();
            }
            
         }
      }
   }
}

?>