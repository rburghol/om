<?php

function importLanduseFile($projectid, $scenarioid, $listobject, $filename, $src, $replaceall, $format, $debug) {

   $colinfo = array(
      'luname'=>array('required'=>1, 'type'=>'varchar(8)'),
      'luarea'=>array('required'=>1, 'type'=>'float8'),
      'thisyear'=>array('required'=>1, 'type'=>'float8'),
      'subshedid'=>array('required'=>1, 'type'=>'varchar(16)'),
      'landseg'=>array('required'=>1, 'type'=>'varchar(16)'),
      'riverseg'=>array('required'=>1, 'type'=>'varchar(24)'),
      'scenarioid'=>array('required'=>0, 'type'=>'integer')
   );

   $tblname = 'tmp_lrsegs';

   if ($format == 'column') {
      $colinfo['luname']['required'] = 0;
      $colinfo['luarea']['required'] = 0;
   }



   switch ($format) {

      case 'column':
         # this is either a columnar formatted landuse input, or it is an error
         # test for columns other than the expected, which we assume are
         # so, we create the table, then add the luname and luarea columns
         # which would not have come in during columnar import
         print("<br>Parsing $filename<br>");
         # call this routine with $createonly = 1, just create the table, do not import data
         $createonly = 1;
         $info = parseCSVToFormat($listobject, $colinfo, "$filename", $tblname, 1, ',', $createonly, $debug);
         if ($debug) {
            print_r($info);
         }
         $j = $info['number'];
         $listobject->querystring = $info['insql'];
         if ($debug) {
            print("$listobject->querystring ; <br>");
         }
         $listobject->performQuery();
         $listobject->querystring = "alter table $tblname add column luname varchar(12) ";
         if ($debug) {
            print("$listobject->querystring ; <br>");
         }
         $listobject->performQuery();
         $listobject->querystring = "alter table $tblname add column luarea float8 ";
         if ($debug) {
            print("$listobject->querystring ; <br>");
         }
         $listobject->performQuery();

         $cinfo = createDBFromCSV($listobject, $filename, 'tmp_colimp', 64, 1, $debug);

         if ($debug) {
            print_r($info['columns']);
         }
         if (!$info['error']) {
            foreach ($info['columns'] as $thiscol) {
               if (!in_array($thiscol, array_keys($colinfo)) ) {
                  # not one of the expected columns, must be a land use name
                  $listobject->querystring = "  insert into $tblname (thisyear, subshedid, luname, landseg, riverseg, luarea) ";
                  $listobject->querystring .= " select thisyear, subshedid, '$thiscol', landseg, riverseg, \"$thiscol\" ";
                  $listobject->querystring .= " FROM tmp_colimp ";
                  if ($debug) {
                     print("$listobject->querystring ; <br>");
                  }
                  $listobject->performQuery();
                  $j++;
               }
            }
         }
         /*
         if ($debug) {
            $listobject->querystring = "select * from tmp_colimp ";
            $listobject->performQuery();
            $listobject->showList();
            $listobject->querystring = "select * from $tblname ";
            $listobject->performQuery();
            $listobject->showList();
         }
         */

      break;

      case 'row':
         print("<br>Parsing $filename<br>");
         $info = parseCSVToFormat($listobject, $colinfo, "$filename", $tblname, 1, ',', 0, $debug);
         #print_r($info);
         $j = $info['number'];

      break;
   }

   if ($info['error']) {
      $errmsg = $info['errmsg'];
      print("<br><b>The following error(s) occured during import:</b> <br> $errmsg<br>");
      return;
   }

   print("$j land-use records retrieved from file.<br>");

   if (!$replaceall) {
      # delete - and import -
      # assumes that the imported file contains all landuses that are to be updated. Other existing landuses
      # not explicitly imported will remain.
      print("Clearing old LRSEG records.<br>");
      $listobject->querystring = "DELETE FROM scen_lrsegs ";
      $listobject->querystring .= " where subshedid = $tblname.subshedid ";
      $listobject->querystring .= "    and landseg = $tblname.landseg ";
      $listobject->querystring .= "    and riverseg = $tblname.riverseg ";
      $listobject->querystring .= "    and scenarioid = $scenarioid ";
      $listobject->querystring .= "    and projectid = $projectid ";
      $listobject->querystring .= "    and thisyear = $tblname.thisyear ";
      $listobject->querystring .= "    and luname = $tblname.luname ";
      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();

      # insert new ones
      $thisdate = date('r',time());
      print("Inserting new LRSEG records.<br>");
      $listobject->querystring = "insert into scen_lrsegs (scenarioid, projectid, thisyear, subshedid, luname, ";
      $listobject->querystring .= " landseg, riverseg, luarea, lrseg, src_citation, rundate) ";
      $listobject->querystring .= " select $scenarioid, $projectid, thisyear, subshedid, luname, landseg, riverseg, luarea, ";
      $listobject->querystring .= " riverseg || landseg, $src, '$thisdate'::timestamp ";
      $listobject->querystring .= " from $tblname ";
      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();
   } else {
      # delete - and import -
      # assumes that the imported file contains all non-zero landuses. Other existing landuses
      # not explicitly imported will be DELETED.
      print("Clearing old LRSEG records.<br>");
      $listobject->querystring = "DELETE FROM scen_lrsegs ";
      $listobject->querystring .= " where subshedid = $tblname.subshedid ";
      $listobject->querystring .= "    and landseg = $tblname.landseg ";
      $listobject->querystring .= "    and riverseg = $tblname.riverseg ";
      $listobject->querystring .= "    and scenarioid = $scenarioid ";
      $listobject->querystring .= "    and projectid = $projectid ";
      $listobject->querystring .= "    and thisyear = $tblname.thisyear ";
      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();

      print("Clearing BMP Land-Use transformations.<br>");
      $listobject->querystring = " DELETE FROM scen_bmp_luchghist ";
      $listobject->querystring .= " where lrseg = $tblname.riverseg || $tblname.landseg ";
      $listobject->querystring .= "    and thisyear = $tblname.thisyear ";
      $listobject->querystring .= "    and scenarioid = $scenarioid ";
      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();

      # insert new ones
      $thisdate = date('r',time());
      print("Inserting new LRSEG records.<br>");
      $listobject->querystring = "insert into scen_lrsegs (scenarioid, projectid, thisyear, subshedid, luname, ";
      $listobject->querystring .= " landseg, riverseg, luarea, lrseg, rundate) ";
      $listobject->querystring .= " select $scenarioid, $projectid, b.thisyear, b.subshedid, b.luname, ";
      $listobject->querystring .= "    b.landseg, b.riverseg, ";
      $listobject->querystring .= "    CASE ";
      $listobject->querystring .= "       WHEN a.luarea IS NULL THEN 0.0 ";
      $listobject->querystring .= "       ELSE a.luarea";
      $listobject->querystring .= "    END as luarea, ";
      $listobject->querystring .= " b.lrseg, '$thisdate'::timestamp ";
      $listobject->querystring .= " from ";
      $listobject->querystring .= " (select c.thisyear, a.subshedid, a.landseg, a.riverseg, a.lrseg, b.hspflu as luname ";
      $listobject->querystring .= "  from (select thisyear from $tblname group by thisyear) as c left outer join ";
      $listobject->querystring .= "   lucomposite as a ";
      $listobject->querystring .= "  on (1 = 1) ";
      $listobject->querystring .= "  full join landuses as b ";
      $listobject->querystring .= "  on (1 = 1) ";
      $listobject->querystring .= "  where lrseg in (select riverseg || landseg from $tblname group by riverseg, landseg ) ";
      $listobject->querystring .= "     and b.hspflu is not null ";
      $listobject->querystring .= "     and b.hspflu <> '' ";
      $listobject->querystring .= "     and a.projectid = $projectid ";
      $listobject->querystring .= "     and b.projectid = $projectid ";
      $listobject->querystring .= "  group by c.thisyear, a.subshedid, a.landseg, a.riverseg, a.lrseg, b.hspflu ";
      $listobject->querystring .= "  ) as b ";
      $listobject->querystring .= " left outer join $tblname as a ";
      $listobject->querystring .= " on ( a.riverseg || a.landseg = b.lrseg ";
      $listobject->querystring .= "    and a.luname = b.luname ) ";
      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();
   }

   return $tblname;

}

function importSourceFile($projectid, $scenarioid, $listobject, $filename, $src, $replaceall, $format, $debug) {

   $colinfo = array(
      'sourcename'=>array('required'=>1, 'type'=>'varchar(32)'),
      'sourcepop'=>array('required'=>1, 'type'=>'float8'),
      'thisyear'=>array('required'=>1, 'type'=>'float8'),
      'subshedid'=>array('required'=>1, 'type'=>'varchar(16)'),
      'scenarioid'=>array('required'=>0, 'type'=>'integer')
   );

   $tblname = 'tmp_sources';

   if ($format == 'column') {
      $colinfo['sourcename']['required'] = 0;
      $colinfo['sourcepop']['required'] = 0;
   }



   switch ($format) {

      case 'column':
         # this is either a columnar formatted landuse input, or it is an error
         # test for columns other than the expected, which we assume are
         # so, we create the table, then add the luname and luarea columns
         # which would not have come in during columnar import
         print("<br>Parsing $filename<br>");
         # call this routine with $createonly = 1, just create the table, do not import data
         $createonly = 1;
         $info = parseCSVToFormat($listobject, $colinfo, "$filename", $tblname, 1, ',', $createonly, $debug);
         if ($debug) {
            print_r($info);
         }
         $j = $info['number'];
         $listobject->querystring = $info['insql'];
         if ($debug) {
            print("$listobject->querystring ; <br>");
         }
         $listobject->performQuery();
         $listobject->querystring = "alter table $tblname add column sourcename varchar(32) ";
         if ($debug) {
            print("$listobject->querystring ; <br>");
         }
         $listobject->performQuery();
         $listobject->querystring = "alter table $tblname add column sourcepop float8 ";
         if ($debug) {
            print("$listobject->querystring ; <br>");
         }
         $listobject->performQuery();

         $cinfo = createDBFromCSV($listobject, $filename, 'tmp_colimp', 64, 1, $debug);

         if ($debug) {
            print_r($info['columns']);
         }
         if (!$info['error']) {
            foreach ($info['columns'] as $thiscol) {
               if (!in_array($thiscol, array_keys($colinfo)) ) {
                  # not one of the expected columns, must be a land use name
                  $listobject->querystring = "  insert into $tblname (thisyear, subshedid, sourcename, subshedid, sourcepop) ";
                  $listobject->querystring .= " select thisyear, subshedid, '$thiscol', subshedid, \"$thiscol\" ";
                  $listobject->querystring .= " FROM tmp_colimp ";
                  if ($debug) {
                     print("$listobject->querystring ; <br>");
                  }
                  $listobject->performQuery();
                  $j++;
               }
            }
         }
         /*
         if ($debug) {
            $listobject->querystring = "select * from tmp_colimp ";
            $listobject->performQuery();
            $listobject->showList();
            $listobject->querystring = "select * from $tblname ";
            $listobject->performQuery();
            $listobject->showList();
         }
         */

      break;

      case 'row':
         print("<br>Parsing $filename<br>");
         $info = parseCSVToFormat($listobject, $colinfo, "$filename", $tblname, 1, ',', 0, $debug);
         #print_r($info);
         $j = $info['number'];

      break;
   }

   if ($info['error']) {
      $errmsg = $info['errmsg'];
      print("<br><b>The following error(s) occured during import:</b> <br> $errmsg<br>");
      return;
   }

   print("$j land-use records retrieved from file.<br>");

   if (!$replaceall) {
      # delete - and import -
      # assumes that the imported file contains all sources that are to be updated. Other existing sources
      # not explicitly imported will remain.

      print("Clearing old Source records.<br>");
      $listobject->querystring = " DELETE FROM scen_sourcepops ";
      $listobject->querystring .= " where scen_sourcepops.subshedid = $tblname.subshedid ";
      $listobject->querystring .= "    and scen_sourcepops.scenarioid = $scenarioid ";
      $listobject->querystring .= "    and scen_sources.sourcename = $tblname.sourcename ";
      $listobject->querystring .= "    and scen_sources.scenarioid = $scenarioid ";
      $listobject->querystring .= "    and scen_sourcepops.sourceid = scen_sources.sourceid  ";
      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();

      # insert new ones
      $thisdate = date('r',time());
      print("Inserting new Source records.<br>");
      $listobject->querystring = "insert into scen_sourcepops ( scenarioid, subshedid, sourceid, sourcepop, ";
      $listobject->querystring .= "   src_citation, thisyear, rundate ) ";
      $listobject->querystring .= " select $scenarioid, a.subshedid, b.sourceid, a.sourcepop, ";
      $listobject->querystring .= "   $src, a.thisyear, '$thisdate'::timestamp ";
      $listobject->querystring .= " from $tblname as a, scen_sources as b ";
      $listobject->querystring .= " where b.sourcename = a.sourcename ";
      $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();
   } else {
      # delete - and import -
      # assumes that the imported file contains all sources. Other existing sources
      # not explicitly imported will be DELETED.
      print("Clearing old LRSEG records.<br>");
      $listobject->querystring = " DELETE FROM scen_sourcepops ";
      $listobject->querystring .= " where subshedid = $tblname.subshedid ";
      $listobject->querystring .= "    and scenarioid = $scenarioid ";
      $listobject->querystring .= "    and thisyear = $tblname.thisyear ";
      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();

      $thisdate = date('r',time());
      print("Inserting new Source records.<br>");
      $listobject->querystring = "insert into scen_sourcepops ( scenarioid, subshedid, sourceid, sourcepop, ";
      $listobject->querystring .= "   src_citation, thisyear, rundate ) ";
      $listobject->querystring .= " select $scenarioid, a.subshedid, b.sourceid, a.sourcepop, ";
      $listobject->querystring .= "   $src, a.thisyear, '$thisdate'::timestamp ";
      $listobject->querystring .= " from $tblname as a, scen_sources as b ";
      $listobject->querystring .= " where b.sourcename = a.sourcename ";
      $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();
   }

   return $tblname;

}


function importBMPFile($projectid, $scenarioid, $listobject, $segments, $filename, $src, $replaceall, $format, $bmpres, $debug) {

   $colinfo = array(
      'coseg'=>array('required'=>0, 'type'=>'varchar(24)'),
      'stseg'=>array('required'=>0, 'type'=>'varchar(24)'),
      'seg'=>array('required'=>0, 'type'=>'varchar(24)'),
      'landseg'=>array('required'=>0, 'type'=>'varchar(24)'),
      'riverseg'=>array('required'=>0, 'type'=>'varchar(24)'),
      'catcode2'=>array('required'=>0, 'type'=>'varchar(24)'),
      'stabbrev'=>array('required'=>0, 'type'=>'varchar(24)'),
      'cofips'=>array('required'=>0, 'type'=>'varchar(24)'),
      'fipsab'=>array('required'=>0, 'type'=>'varchar(24)'),
      'huc'=>array('required'=>0, 'type'=>'varchar(24)'),
      'bmpname'=>array('required'=>1, 'type'=>'varchar(8)'),
      'bmparea'=>array('required'=>1, 'type'=>'float8'),
      'thisyear'=>array('required'=>1, 'type'=>'float8')
   );

   switch ($bmpres) {
      # turn on the required columns based on the bmp input resolution
      case 'none':
         # none means that we are simply importing a number, and distributing it across the eligible
         # land uses in the selected segments
         $disabled = 0;
      break;

      case 'stseg':
         # stseg - phase 4 state segment (stseg, seg, stabbrev [ others = -1])
         $colinfo['stseg']['required'] = 1;
         $colinfo['seg']['required'] = 1;
         $colinfo['stabbrev']['required'] = 1;
         $disabled = 1;
      break;

      case 'cofips':
         # cofips - county level (cofips, stabbrev [ others = -1])
         $colinfo['cofips']['required'] = 1;
         $disabled = 1;
      break;

      case 'coseg':
         # coseg - phase 4 state segment (coseg, stseg, seg, stabbrev, cofips [ others = -1])
         $colinfo['coseg']['required'] = 1;
         $disabled = 1;
      break;

      case 'catcode':
         #catcode - phase 5 CATCODE2 (catcode2 [ others = -1])
         $colinfo['catcode2']['required'] = 1;
         $disabled = 1;
      break;

      case 'catstate':
         #catstate - phase 5 CATCODE2, ST level (catcode2, stabbrev - basically, state-river segment)
         $colinfo['catcode2']['required'] = 1;
         $colinfo['stabbrev']['required'] = 1;
         $disabled = 1;
      break;

      case 'catfips':
         #catfips - phase 5 CATCODE2, ST level (catcode2, fipsab)
         $colinfo['catcode2']['required'] = 1;
         $colinfo['fipsab']['required'] = 1;
         $disabled = 0;
      break;

      case 'dehuc':
         # Delaware psuedo-HUC level
         $colinfo['huc']['required'] = 1;
         $disabled = 1;
      break;

      case 'lrseg':
         # Land River Segment
         $colinfo['landseg']['required'] = 1;
         $colinfo['riverseg']['required'] = 1;
         $disabled = 1;
      break;
   }

   $tblname = 'tmp_bmps';

   switch ($format) {

      case 'column':
         # this is either a columnar formatted landuse input, or it is an error
         # test for columns other than the expected, which we assume are
         # so, we create the table, then add the luname and luarea columns
         # which would not have come in during columnar import
         print("<br>Parsing $filename<br>");
         # call this routine with $createonly = 1, just create the table, do not import data
         $createonly = 1;
         # will add these later, so do not require them
         $colinfo['bmpname']['required'] = 0;
         $colinfo['bmparea']['required'] = 0;
         $info = parseCSVToFormat($listobject, $colinfo, "$filename", $tblname, 1, ',', $createonly, $debug);
         if ($debug) {
            print_r($info);
         }
         $j = $info['number'];
         $keycols = $info['keycols'];
         $listobject->querystring = $info['insql'];
         if ($debug) {
            print("$listobject->querystring ; <br>");
         }
         $listobject->performQuery();
         $listobject->querystring = "alter table $tblname add column bmpname varchar(12) ";
         if ($debug) {
            print("$listobject->querystring ; <br>");
         }
         $listobject->performQuery();
         $listobject->querystring = "alter table $tblname add column bmparea float8 ";
         if ($debug) {
            print("$listobject->querystring ; <br>");
         }
         $listobject->performQuery();

         $cinfo = createDBFromCSV($listobject, $filename, 'tmp_colimp', 64, 1, $debug);

         if ($debug) {
            print_r($info['columns']);
         }
         if (!$info['error']) {
            foreach ($info['columns'] as $thiscol) {
               if (!in_array($thiscol, array_keys($colinfo)) ) {
                  # not one of the expected columns, must be a land use name
                  /*
                  # NOW HARD_WIRED TO WANT ONLY LAND/RIVER SEG INPUTS!!!!!!
                  $listobject->querystring = "  insert into $tblname (thisyear, bmpname, landseg, riverseg, bmparea) ";
                  $listobject->querystring .= " select thisyear, '$thiscol', landseg, riverseg, \"$thiscol\" ";
                  $listobject->querystring .= " FROM tmp_colimp ";
                  */
                  $listobject->querystring = "  insert into $tblname ($keycols, bmparea, bmpname) ";
                  $listobject->querystring .= " select $keycols, \"$thiscol\", '$thiscol' ";
                  $listobject->querystring .= " FROM tmp_colimp ";
                  if ($debug) {
                     print("$listobject->querystring ; <br>");
                  }
                  $listobject->performQuery();
                  $j++;
               }
            }
         }
         /*
         if ($debug) {
            $listobject->querystring = "select * from tmp_colimp ";
            $listobject->performQuery();
            $listobject->showList();
            $listobject->querystring = "select * from $tblname ";
            $listobject->performQuery();
            $listobject->showList();
         }
         */

      break;

      case 'row':
         print("<br>Parsing $filename<br>");
         $info = parseCSVToFormat($listobject, $colinfo, "$filename", $tblname, 1, ',', 0, $debug);
         #print_r($info);
         $j = $info['number'];

      break;
   }

   if ($info['error']) {
      $errmsg = $info['errmsg'];
      print("<br><b>The following error(s) occured during import:</b> <br> $errmsg<br>");
      return;
   }

   print("$j BMP records retrieved from file.<br> Importing into scenario table.");

   switch($bmpres) {

      case 'none':
         $listobject->querystring = " select thisyear, bmpname, bmparea from $tblname ";
         if ($debug) {
            print("$listobject->querystring ;<br>");
         }
         $listobject->performQuery();
         $bmprecs = $listobject->queryrecords;

         foreach ($bmprecs as $thisrec) {
            $bmpname = $thisrec['bmpname'];
            $bmparea = $thisrec['bmparea'];
            $thisyear = $thisrec['thisyear'];
            print("Distributing $bmpname, $thisyear, $bmparea acres.<br>");
            distributeBMP($listobject, $bmpname, $segments, $thisyear, $bmparea, $scenarioid, $projectid, $debug);
         }
      break;

      default:
         # just import, later, when multiple resolutions are handled, we will add extra steps here
         if (!$replaceall) {
            # delete - and import -
            # assumes that the imported file contains all bmps that are to be updated. Other existing landuses
            # not explicitly imported will remain.
            print("Clearing old LRSEG records.<br>");
            $listobject->querystring = "DELETE FROM scen_lrseg_bmps ";
            $listobject->querystring .= " where landseg = $tblname.landseg ";
            $listobject->querystring .= "    and riverseg = $tblname.riverseg ";
            $listobject->querystring .= "    and scenarioid = $scenarioid ";
            $listobject->querystring .= "    and thisyear = $tblname.thisyear ";
            $listobject->querystring .= "    and bmpname = $tblname.bmpname ";
            if ($debug) {
               print("$listobject->querystring ;<br>");
            }
            $listobject->performQuery();

            # insert new ones
            $thisdate = date('r',time());
            print("Inserting new LRSEG BMP records.<br>");
            $listobject->querystring = "insert into scen_lrseg_bmps (scenarioid, thisyear, landseg, riverseg, ";
            $listobject->querystring .= " bmpname, bmparea, lrseg, src_citation ) ";
            $listobject->querystring .= " select $scenarioid, thisyear, landseg, riverseg, bmpname, bmparea, ";
            $listobject->querystring .= " riverseg || landseg, $src ";
            $listobject->querystring .= " from $tblname ";
            if ($debug) {
               print("$listobject->querystring ;<br>");
            }
            $listobject->performQuery();
         } else {
            # delete - and import -
            # assumes that the imported file contains all non-zero landuses. Other existing landuses
            # not explicitly imported will be DELETED.
            print("Clearing old LRSEG records.<br>");
            $listobject->querystring = "DELETE FROM scen_lrseg_bmps ";
            $listobject->querystring .= " where landseg = $tblname.landseg ";
            $listobject->querystring .= "    and riverseg = $tblname.riverseg ";
            $listobject->querystring .= "    and scenarioid = $scenarioid ";
            $listobject->querystring .= "    and thisyear = $tblname.thisyear ";
            if ($debug) {
               print("$listobject->querystring ;<br>");
            }
            $listobject->performQuery();

            print("Clearing BMP Land-Use transformations.<br>");
            $listobject->querystring = " DELETE FROM scen_bmp_luchghist ";
            $listobject->querystring .= " where lrseg = $tblname.riverseg || $tblname.landseg ";
            $listobject->querystring .= "    and thisyear = $tblname.thisyear ";
            $listobject->querystring .= "    and scenarioid = $scenarioid ";
            if ($debug) {
               print("$listobject->querystring ;<br>");
            }
            $listobject->performQuery();

            # insert new ones

            print("Inserting new LRSEG BMP records.<br>");
            $listobject->querystring = "insert into scen_lrseg_bmps (scenarioid, thisyear, bmpname, ";
            $listobject->querystring .= " landseg, riverseg, bmparea, lrseg, src_citation ) ";
            $listobject->querystring .= " select $scenarioid, thisyear, bmpname, landseg, riverseg, bmparea, ";
            $listobject->querystring .= " riverseg || landseg, $src ";
            $listobject->querystring .= " from $tblname ";
            if ($debug) {
               print("$listobject->querystring ;<br>");
            }
            $listobject->performQuery();
         }
      break;

   }

   return $tblname;

}


function importBaseLanduse($projectid, $scenarioid, $src_scenario, $listobject, $subsheds, $thisyear, $debug) {

   # assemble input variables into conditions
   # this selects ALL SUBSHEDS that overlap with the selected lrsegs, since we need all areas
   # to accurately disaggregate manure and other sources
   if ($src_scenario > 0) {
      $ptab = 'scen_lrsegs';
      $scol = "scenarioid = $src_scenario";
   } else {
      $ptab = 'lucomposite';
      $scol = "projectid = $projectid";
   }
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $lrcond = " lrseg in ";
      $lrcond .= "   ( select lrseg from $ptab ";
      $lrcond .= "      where subshedid in ";
      $lrcond .= "      (select subshedid ";
      $lrcond .= "       from $ptab ";
      $lrcond .= "       where $scol ";
      $lrcond .= "          and thisyear in ($thisyear) ";
      $lrcond .= "          and lrseg in ($sslist) ";
      $lrcond .= "       group by subshedid )";
      $lrcond .= "    group by lrseg ";
      $lrcond .= "   )";

      $subcond = " subshedid in ";
      $subcond .= "   (select subshedid ";
      $subcond .= "    from $ptab ";
      $subcond .= "    where $scol ";
      $subcond .= "       and thisyear in ($thisyear) ";
      $subcond .= "       and lrseg in ($sslist) ";
      $subcond .= "    group by subshedid )";

      $asubcond = " a.subshedid in ";
      $asubcond .= "   (select subshedid ";
      $asubcond .= "    from $ptab ";
      $asubcond .= "    where $scol ";
      $asubcond .= "       and thisyear in ($thisyear) ";
      $asubcond .= "       and lrseg in ($sslist) ";
      $asubcond .= "    group by subshedid )";
   } else {
      $lrcond = ' (1 = 1) ';
      $subcond = ' (1 = 1) ';
      $asubcond = ' (1 = 1) ';
   }

   # delete - and import -
   # assumes that all land-uses should be represented.
   print("Clearing old LRSEG records.<br>");
   $listobject->querystring = " DELETE FROM scen_subsheds ";
   $listobject->querystring .= " where $subcond ";
   $listobject->querystring .= "    and thisyear in ( $thisyear) ";
   $listobject->querystring .= "    and scenarioid = $scenarioid ";
   if ($debug) {
      print("$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   # delete - and import -
   # assumes that all land-uses should be represented.
   print("Clearing old LRSEG records.<br>");
   $listobject->querystring = " DELETE FROM scen_lrsegs ";
   $listobject->querystring .= " where $subcond ";
   $listobject->querystring .= "    and thisyear in ( $thisyear) ";
   $listobject->querystring .= "    and scenarioid = $scenarioid ";
   if ($debug) {
      print("$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   print("Clearing BMP Land-Use transformations.<br>");
   $listobject->querystring = " DELETE FROM scen_bmp_luchghist ";
   $listobject->querystring .= " where $lrcond ";
   $listobject->querystring .= "    and thisyear in ( $thisyear) ";
   $listobject->querystring .= "    and scenarioid = $scenarioid ";
   if ($debug) {
      print("$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   # insert new ones
   $thisdate = date('r',time());
   print("Inserting new LRSEG records.<br>");
   $listobject->querystring = "insert into scen_lrsegs (scenarioid, projectid, thisyear, subshedid, luname, ";
   $listobject->querystring .= " landseg, riverseg, luarea, lrseg, rundate) ";
   $listobject->querystring .= " select $scenarioid, $projectid, b.thisyear, b.subshedid, b.luname, ";
   $listobject->querystring .= "    b.landseg, b.riverseg, ";
   $listobject->querystring .= "    CASE ";
   $listobject->querystring .= "       WHEN a.luarea IS NULL THEN 0.0 ";
   $listobject->querystring .= "       ELSE a.luarea";
   $listobject->querystring .= "    END as luarea, ";
   $listobject->querystring .= " b.lrseg, '$thisdate'::timestamp ";
   $listobject->querystring .= " from ";
   $listobject->querystring .= " (select a.thisyear, a.subshedid, a.landseg, a.riverseg, a.lrseg, b.hspflu as luname ";
   $listobject->querystring .= "  FROM $ptab as a left outer join landuses as b ";
   $listobject->querystring .= "  on (1 = 1) ";
   $listobject->querystring .= "  where $asubcond ";
   $listobject->querystring .= "     and b.hspflu is not null ";
   $listobject->querystring .= "     and b.hspflu <> '' ";
   if ($src_scenario > 0) {
      $listobject->querystring .= " AND a.scenarioid = $src_scenario ";
   } else {
      $listobject->querystring .= " AND a.projectid = $projectid ";
   }
   $listobject->querystring .= "     and a.thisyear in ( $thisyear) ";
   $listobject->querystring .= "     and b.projectid = $projectid ";
   $listobject->querystring .= "  group by a.thisyear, a.subshedid, a.landseg, a.riverseg, a.lrseg, b.hspflu ";
   $listobject->querystring .= "  order by a.thisyear, a.subshedid, a.landseg, a.riverseg, a.lrseg, b.hspflu ";
   $listobject->querystring .= "  ) as b ";
   $listobject->querystring .= " left outer join $ptab as a ";
   $listobject->querystring .= " on (a.lrseg = b.lrseg ";
   $listobject->querystring .= "    and a.luname = b.luname ";
   if ($src_scenario > 0) {
      $listobject->querystring .= " AND a.scenarioid = $src_scenario ";
   } else {
      $listobject->querystring .= " AND a.projectid = $projectid ";
   }
   $listobject->querystring .= "    AND a.thisyear in ($thisyear) ";
   $listobject->querystring .= "    and a.thisyear = b.thisyear ) ";
   if ($debug) {
      print("$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   # now, summarize by subwatershed in the scen_subsheds table
   $listobject->querystring = "insert into scen_subsheds (scenarioid, projectid, thisyear, subshedid, luname, ";
   $listobject->querystring .= " luarea, rundate) ";
   $listobject->querystring .= " select $scenarioid, $projectid, thisyear, subshedid, luname, ";
   $listobject->querystring .= "    sum(luarea), '$thisdate'::timestamp ";
   $listobject->querystring .= " from scen_lrsegs ";
   $listobject->querystring .= " where scenarioid = $scenarioid ";
   $listobject->querystring .= "    AND thisyear in ($thisyear) ";
   $listobject->querystring .= "    AND $subcond ";
   $listobject->querystring .= " group by thisyear, subshedid, luname ";
   if ($debug) {
      print("$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   return $tblname;

}


function importSourceDefs($projectid, $scenarioid, $listobject, $thisyear, $debug) {

   # imports source definitions

   # delete - and import -
   # this should ultimately be imported into the bmps_all table, but since we are mandating that
   # the only valid input resolution be lrseg (currently), it is OK to import directly into the
   # scen_lrseg_bmps table.
   $listobject->querystring = " DELETE FROM scen_sources ";
   $listobject->querystring .= " where scenarioid = $scenarioid ";
   if ($debug) {
      print("$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   # insert new ones
   $thisdate = date('r',time());
   $listobject->querystring = "insert into scen_sources (scenarioid, projectid, sourceid, ";
   $listobject->querystring .= " typeid, sourcename, ";
   $listobject->querystring .= " distrotype, avgweight, poplink, src_citation, rundate) ";
   $listobject->querystring .= " select $scenarioid, $projectid, sourceid, typeid, sourcename, ";
   $listobject->querystring .= " distrotype, avgweight, poplink, src_citation, '$thisdate'::timestamp  ";
   $listobject->querystring .= " from sources ";
   $listobject->querystring .= " where projectid = $projectid ";
   if ($debug) {
      print("$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   # insert new ones
   $thisdate = date('r',time());
   # archive the current sources
   $listobject->querystring = " delete from scen_sourceloadtype where scenarioid = $scenarioid ";
   if ($debug) {
      print("$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   $listobject->querystring = "insert into scen_sourceloadtype ( scenarioid, typeid, sourcename, auweight, pollutantprod, ";
   $listobject->querystring .= "    produnits, pollutantconc, storagedieoff, concunits, conv, convunits,  ";
   $listobject->querystring .= "    projectid, sourceclass, starttime, duration, directfraction, avgweight, parentid, ";
   $listobject->querystring .= "    inheritmode, comments, rundate ) ";
   $listobject->querystring .= " select $scenarioid, typeid, sourcename, auweight, pollutantprod, ";
   $listobject->querystring .= "    produnits, pollutantconc, storagedieoff, concunits, conv, convunits,  ";
   $listobject->querystring .= "    $projectid, sourceclass, starttime, duration, directfraction, avgweight, parentid, ";
   $listobject->querystring .= "    inheritmode, comments, '$thisdate'::timestamp ";
   $listobject->querystring .= " from sourceloadtype ";
   $listobject->querystring .= " WHERE projectid = $projectid ";
   if ($debug) {
      print("$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

}


function importBaseSources($projectid, $scenarioid, $src_scenario, $listobject, $subsheds, $thisyear, $sources, $debug) {

   # imports source populations, and distribution schemas
   # $src_scenario = the scenario to get data from. If this is -1, means get from project base table

   if ($scenarioid == $src_scenario) {
      print("<b>Error: </b> You cannot import from the same scenario. <br>");
   }

   if ($src_scenario > 0) {
      $yearcol = 'thisyear';
      $segtable = 'scen_lrsegs';
      $tableidcol = 'scenarioid';
      $tableid = $src_scenario;
   } else {
      $yearcol = 'popyear';
      $segtable = 'lucomposite';
      $tableidcol = 'projectid';
      $tableid = $projectid;
   }

   # assemble input variables into conditions
   if (count($sources) > 0) {
      $srclist = "'" . join("','", $sources) . "'";
      $srccond = " sourceid in ";
      $srccond .= "   ($srclist) ";
   } else {
      $srccond = ' (1 = 1) ';
   }
   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subcond = " subshedid in ";
      $subcond .= "   (select subshedid ";
      $subcond .= "    from $segtable ";
      $subcond .= "    where lrseg in ($sslist) ";
      $subcond .= "       and thisyear in ($thisyear) ";
      $subcond .= "       and $tableidcol = $tableid ";
      $subcond .= "    group by subshedid )";
   } else {
      $subcond = ' (1 = 1) ';
   }

   # delete - and import -
   # this should ultimately be imported into the bmps_all table, but since we are mandating that
   # the only valid input resolution be lrseg (currently), it is OK to import directly into the
   # scen_lrseg_bmps table.
   print("Clearing old Source records.<br>");
   $listobject->querystring = " DELETE FROM scen_sourcepops ";
   $listobject->querystring .= " where $subcond ";
   $listobject->querystring .= "    and scenarioid = $scenarioid ";
   $listobject->querystring .= "    and thisyear in ( $thisyear )";
   $listobject->querystring .= "    and $srccond ";
   if ($debug) {
      print("$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   # insert new ones
   $thisdate = date('r',time());
   print("Inserting new Source records.<br>");
   $listobject->querystring = "insert into scen_sourcepops ( scenarioid, subshedid, sourceid, sourcepop, ";
   $listobject->querystring .= "   src_citation, thisyear, rundate ) ";
   $listobject->querystring .= " select $scenarioid, subshedid, sourceid, sourcepop, ";
   $listobject->querystring .= "   src_citation, $yearcol, '$thisdate'::timestamp ";
   if ($src_scenario > 0) {
      $listobject->querystring .= " from scen_sourcepops ";
      $listobject->querystring .= " WHERE scenarioid = $src_scenario ";
   } else {
      $listobject->querystring .= " from subshed ";
      $listobject->querystring .= " WHERE projectid = $projectid ";
   }
   $listobject->querystring .= "    and $yearcol in ( $thisyear )";
   $listobject->querystring .= "    and $subcond ";
   $listobject->querystring .= "    and $srccond ";

   if ($debug) {
      print("$listobject->querystring ;<br>");
   }
   $listobject->performQuery();


   return $tblname;

}



function importBaseBMPs($projectid, $scenarioid, $src_scenario, $listobject, $subsheds, $thisyear, $debug) {

   # imports source populations, and distribution schemas
   # $src_scenario = the scenario to get data from. If this is -1, means get from project base table

   if ($scenarioid == $src_scenario) {
      print("<b>Error: </b> You cannot import from the same scenario. <br>");
   }

   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subcond = " lrseg in ($sslist) ";
   } else {
      $subcond = ' (1 = 1) ';
   }

   if (!($src_scenario > 0)) {
      print("<b>Notice: </b>No scenario selected, BMPs not Imported.");
   }

   # delete - and import -
   # this should ultimately be imported into the bmps_all table, but since we are mandating that
   # the only valid input resolution be lrseg (currently), it is OK to import directly into the
   # scen_lrseg_bmps table.
   print("Clearing old BMP records.<br>");
   $listobject->querystring = " DELETE FROM scen_lrseg_bmps ";
   $listobject->querystring .= " where $subcond ";
   $listobject->querystring .= "    and scenarioid = $scenarioid ";
   $listobject->querystring .= "    and thisyear in ( $thisyear )";
   if ($debug) {
      print("$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   # insert new ones
   $thisdate = date('r',time());
   print("Inserting new BMP records.<br>");
   if ($src_scenario > 0) {
      $listobject->querystring = "insert into scen_lrseg_bmps ( scenarioid, landseg, riverseg, lrseg, ";
      $listobject->querystring .= "   bmpid, bmpname, bmparea, thisyear, src_citation ) ";
      $listobject->querystring .= " select $scenarioid, landseg, riverseg, lrseg, ";
      $listobject->querystring .= "   bmpid, bmpname, bmparea, thisyear, src_citation ";
      $listobject->querystring .= " from scen_lrseg_bmps ";
      $listobject->querystring .= " WHERE scenarioid = $src_scenario ";
      $listobject->querystring .= "    and $subcond ";
      $listobject->querystring .= "    and thisyear in ( $thisyear )";

      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();

      # now get luchange history
      $listobject->querystring = "insert into scen_bmp_luchghist ( scenarioid, thisyear, lrseg, ";
      $listobject->querystring .= "   bmpname, srclu, destlu, chgarea ) ";
      $listobject->querystring .= " select $scenarioid, thisyear, lrseg, ";
      $listobject->querystring .= "   bmpname, srclu, destlu, chgarea ";
      $listobject->querystring .= " from scen_bmp_luchghist ";
      $listobject->querystring .= " WHERE scenarioid = $src_scenario ";
      $listobject->querystring .= "    and $subcond ";
      $listobject->querystring .= "    and thisyear in ( $thisyear )";

      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();

      # now get luchange history
      $listobject->querystring = "insert into scen_bmp_data ( projectid, scenarioid, subshedid, landseg, ";
      $listobject->querystring .= "   riverseg, typeid, thisyear, bmpname, lrseg, luname, value_submitted, ";
      $listobject->querystring .= "   value_implemented, eligarea ) ";
      $listobject->querystring .= " select $projectid, $scenarioid, subshedid, landseg, ";
      $listobject->querystring .= "   riverseg, typeid, thisyear, bmpname, lrseg, luname, value_submitted, ";
      $listobject->querystring .= "   value_implemented, eligarea ";
      $listobject->querystring .= " from scen_bmp_data ";
      $listobject->querystring .= " WHERE scenarioid = $src_scenario ";
      $listobject->querystring .= "    and $subcond ";
      $listobject->querystring .= "    and thisyear in ( $thisyear )";

      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();
   } else {
      print("No BMPs to import - choose another scenario to import from. <br>");
   }


   return $tblname;

}


function importCropData($projectid, $scenarioid, $src_scenario, $listobject, $subsheds, $thisyear, $debug) {

   # imports source populations, and distribution schemas
   # $src_scenario = the scenario to get data from. If this is -1, means get from project base table

   if ($scenarioid == $src_scenario) {
      print("<b>Error: </b> You cannot import from the same scenario. <br>");
   }


   if ($src_scenario > 0) {
      $popcol = 'thisyear';
      $segtable = 'scen_lrsegs';
      $tableidcol = 'scenarioid';
      $tableid = $src_scenario;
   } else {
      $popcol = 'popyear';
      $segtable = 'lucomposite';
      $tableidcol = 'projectid';
      $tableid = $projectid;
   }

   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subcond = " subshedid in ";
      $stcond = " stcofips in ";
      $scond = "   (select subshedid ";
      $scond .= "    from $segtable ";
      $scond .= "    where lrseg in ($sslist) ";
      $scond .= "       and thisyear in ($thisyear) ";
      $scond .= "       and $tableidcol = $tableid ";
      $scond .= "    group by subshedid )";
      $subcond .= $scond;
      $stcond .= $scond;
   } else {
      $subcond = ' (1 = 1) ';
      $stcond = ' (1 = 1) ';
   }


   if (!($src_scenario > 0)) {
      print("<b>Notice: </b>No scenario selected, importing project defaults.<br>");
   }

   # delete - and import -
   # this should ultimately be imported into the bmps_all table, but since we are mandating that
   # the only valid input resolution be lrseg (currently), it is OK to import directly into the
   # scen_lrseg_bmps table.
   print("Clearing old Yield records.<br>");
   $listobject->querystring = " DELETE FROM inputyields ";
   $listobject->querystring .= " where $subcond ";
   $listobject->querystring .= "    and scenarioid = $scenarioid ";
   $listobject->querystring .= "    and thisyear in ( $thisyear )";
   if ($debug) {
      print("$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   print("Clearing old uptake curve records.<br>");
   $listobject->querystring = " DELETE FROM cb_uptake ";
   $listobject->querystring .= " where $stcond ";
   $listobject->querystring .= "    and scenarioid = $scenarioid ";
   $listobject->querystring .= "    and thisyear in ( $thisyear )";
   if ($debug) {
      print("$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   print("Clearing old fixation curve records.<br>");
   $listobject->querystring = " DELETE FROM n_fixation ";
   $listobject->querystring .= " where $subcond ";
   $listobject->querystring .= "    and scenarioid = $scenarioid ";
   if ($debug) {
      print("$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   print("Clearing old crop records.<br>");
   $listobject->querystring = " DELETE FROM scen_crops ";
   $listobject->querystring .= " where $subcond ";
   $listobject->querystring .= "    and thisyear in ( $thisyear )";
   $listobject->querystring .= "    and scenarioid = $scenarioid ";
   if ($debug) {
      print("$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   # insert new ones
   $thisdate = date('r',time());
   print("Inserting new Crop records.<br>");
   if ($src_scenario > 0) {
      $listobject->querystring = "insert into inputyields ( scenarioid, projectid, subshedid, ";
      $listobject->querystring .= " thisyear, luname, nm_planbase, maxn, maxp,total_acres,";
      $listobject->querystring .= " legume_n,uptake_n,uptake_p,total_n,total_p,";
      $listobject->querystring .= " nrate,prate,optn,optp,maxnrate,maxprate,";
      $listobject->querystring .= " mean_uptn,mean_uptp,n_urratio,p_urratio, mean_needn, mean_needp, ";
      $listobject->querystring .= " targ_uptn, targ_uptp, high_needn, high_needp, ";
      $listobject->querystring .= " targ_needn, targ_needp, high_uptn, high_uptp, ";
      $listobject->querystring .= " dc_method, dc_pct, maxyieldtarget, optyieldtarget ) ";
      $listobject->querystring .= " select $scenarioid, $projectid,  subshedid, ";
      $listobject->querystring .= " thisyear, luname, nm_planbase, maxn, maxp,total_acres,";
      $listobject->querystring .= " legume_n, uptake_n, uptake_p, total_n, total_p,";
      $listobject->querystring .= " nrate, prate, optn, optp, maxnrate, maxprate,";
      $listobject->querystring .= " mean_uptn, mean_uptp, n_urratio, p_urratio, mean_needn, mean_needp, ";
      $listobject->querystring .= " targ_uptn, targ_uptp, high_needn, high_needp, ";
      $listobject->querystring .= " targ_needn, targ_needp, high_uptn, high_uptp, ";
      $listobject->querystring .= " dc_method, dc_pct, maxyieldtarget, optyieldtarget ";
      $listobject->querystring .= " from inputyields ";
      $listobject->querystring .= " WHERE scenarioid = $src_scenario ";
      $listobject->querystring .= "    and $subcond ";
      $listobject->querystring .= "    and thisyear in ( $thisyear )";

      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();


      $listobject->querystring = "insert into cb_uptake (thisyear, luname, cb_region, ";
      $listobject->querystring .= "    stcofips, scenarioid, jan, feb, mar, apr, may, jun, ";
      $listobject->querystring .= "    jul, aug, sep, oct, nov, dec ) ";
      $listobject->querystring .= " select thisyear, luname, cb_region, ";
      $listobject->querystring .= "    stcofips, $scenarioid, jan, feb, mar, apr, may, jun, ";
      $listobject->querystring .= "    jul, aug, sep, oct, nov, dec ";
      $listobject->querystring .= " from cb_uptake ";
      $listobject->querystring .= " WHERE scenarioid = $src_scenario ";
      $listobject->querystring .= "    and $stcond ";
      $listobject->querystring .= "    and thisyear in ( $thisyear )";

      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();

      # get fixation curves - these are a single for all years. Should change that later.
      $listobject->querystring = "  insert into n_fixation (projectid, scenarioid, subshedid, ";
      $listobject->querystring .= "    thisyear, luname, jan, feb, mar, apr, may, jun, jul, ";
      $listobject->querystring .= "    aug, sep, oct, nov, dec ) ";
      $listobject->querystring .= " select projectid, $scenarioid, subshedid, ";
      $listobject->querystring .= "    thisyear, luname, jan, feb, mar, apr, may, jun, jul, ";
      $listobject->querystring .= "    aug, sep, oct, nov, dec ";
      $listobject->querystring .= " from n_fixation ";
      $listobject->querystring .= " where scenarioid = $src_scenario ";
      $listobject->querystring .= "    and $subcond ";
      # currently, we do NOT screen for year because this value is null. In future
      # releases we will perhaps vary the fixation curve if we have miultiple legume
      # crops.

      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();

   } else {
      $listobject->querystring = "insert into inputyields ( scenarioid, projectid, subshedid, ";
      $listobject->querystring .= " thisyear, luname, nm_planbase, maxn, maxp,total_acres,";
      $listobject->querystring .= " legume_n,uptake_n,uptake_p,total_n,total_p,";
      $listobject->querystring .= " nrate,prate,optn,optp,maxnrate,maxprate,";
      $listobject->querystring .= " mean_uptn,mean_uptp,n_urratio,p_urratio, mean_needn, mean_needp, ";
      $listobject->querystring .= " targ_uptn, targ_uptp, high_needn, high_needp, ";
      $listobject->querystring .= " targ_needn, targ_needp, high_uptn, high_uptp, ";
      $listobject->querystring .= " dc_method, dc_pct, maxyieldtarget, optyieldtarget ) ";
      $listobject->querystring .= " select $scenarioid, $projectid,  subshedid, ";
      $listobject->querystring .= " thisyear, luname, nm_planbase, maxn, maxp,total_acres,";
      $listobject->querystring .= " legume_n, uptake_n, uptake_p, total_n, total_p,";
      $listobject->querystring .= " nrate, prate, optn, optp, maxnrate, maxprate,";
      $listobject->querystring .= " mean_uptn, mean_uptp, n_urratio, p_urratio, mean_needn, mean_needp, ";
      $listobject->querystring .= " targ_uptn, targ_uptp, high_needn, high_needp, ";
      $listobject->querystring .= " targ_needn, targ_needp, high_uptn, high_uptp, ";
      $listobject->querystring .= " dc_method, dc_pct, maxyieldtarget, optyieldtarget ";
      $listobject->querystring .= " from proj_inputyields ";
      $listobject->querystring .= " WHERE projectid = $projectid ";
      $listobject->querystring .= "    and $subcond ";
      $listobject->querystring .= "    and thisyear in ( $thisyear )";

      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();


      $listobject->querystring = "insert into cb_uptake (thisyear, luname, cb_region, ";
      $listobject->querystring .= "    stcofips, scenarioid, jan, feb, mar, apr, may, jun, ";
      $listobject->querystring .= "    jul, aug, sep, oct, nov, dec ) ";
      $listobject->querystring .= " select thisyear, luname, cb_region, ";
      $listobject->querystring .= "    stcofips, $scenarioid, jan, feb, mar, apr, may, jun, ";
      $listobject->querystring .= "    jul, aug, sep, oct, nov, dec ";
      $listobject->querystring .= " from proj_uptake ";
      $listobject->querystring .= " WHERE projectid = $projectid ";
      $listobject->querystring .= "    and $stcond ";
      $listobject->querystring .= "    and thisyear in ( $thisyear )";

      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();

      # get fixation curves - these are a single for all years. Should change that later.
      $listobject->querystring = "  insert into n_fixation (projectid, scenarioid, subshedid, ";
      $listobject->querystring .= "    thisyear, luname, jan, feb, mar, apr, may, jun, jul, ";
      $listobject->querystring .= "    aug, sep, oct, nov, dec ) ";
      $listobject->querystring .= " select projectid, $scenarioid, subshedid, ";
      $listobject->querystring .= "    thisyear, luname, jan, feb, mar, apr, may, jun, jul, ";
      $listobject->querystring .= "    aug, sep, oct, nov, dec ";
      $listobject->querystring .= " from proj_n_fixation ";
      $listobject->querystring .= " where projectid = $projectid ";
      $listobject->querystring .= "    and $subcond ";
      # currently, we do NOT screen for year because this value is null. In future
      # releases we will perhaps vary the fixation curve if we have miultiple legume
      # crops.

      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();

   }

}


function importPointSources($projectid, $scenarioid, $src_scenario, $listobject, $subsheds, $thisyear, $debug) {


   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear in ($thisyear) ";
      $ayrcond = " a.thisyear in ($thisyear) ";
      $byrcond = " b.thisyear in ($thisyear) ";
   } else {
      $yrcond = " 1 = 1 ";
      $ayrcond = " 1 = 1 ";
      $byrcond = " 1 = 1 ";
   }
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $lrcond = " riverseg || landseg in ($sslist) ";
      # get list of subsheds
      $listobject->querystring = " select subshedid from scen_lrsegs where  ";
      $listobject->querystring .= "   scenarioid = $scenarioid  ";
      $listobject->querystring .= "   and $lrcond  ";
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
      $lrcond = ' 1 = 1 ';
      $subshedcond = ' 1 = 1 ';
      $asubshedcond = ' 1 = 1 ';
      $bsubshedcond = ' 1 = 1 ';
      $isubshedcond = ' 1 = 1 ';
   }
   # copy point sources
   $listobject->querystring = "insert into scen_pointsource (projectid, scenarioid, landseg, riverseg, thisyear, ";
   $listobject->querystring .= " constit, flow, constit_mass ) ";
   $listobject->querystring .= " select projectid, $scenarioid, landseg, riverseg, thisyear, ";
   $listobject->querystring .= " constit, flow, constit_mass ";
   $listobject->querystring .= " from proj_pointsource ";
   $listobject->querystring .= " where projectid = $projectid ";
   $listobject->querystring .= "    and $lrcond ";
   $listobject->performQuery();
}

function importAtmosphericDeposition($projectid, $scenarioid, $src_scenario, $listobject, $subsheds, $thisyear, $debug) {


   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear in ($thisyear) ";
      $ayrcond = " a.thisyear in ($thisyear) ";
      $byrcond = " b.thisyear in ($thisyear) ";
   } else {
      $yrcond = " 1 = 1 ";
      $ayrcond = " 1 = 1 ";
      $byrcond = " 1 = 1 ";
   }
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $lrcond = " lrseg in ($sslist) ";
      # get list of subsheds
      $listobject->querystring = " select subshedid from scen_lrsegs where  ";
      $listobject->querystring .= "   scenarioid = $scenarioid  ";
      $listobject->querystring .= "   and $lrcond  ";
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
      $lrcond = ' 1 = 1 ';
      $subshedcond = ' 1 = 1 ';
      $asubshedcond = ' 1 = 1 ';
      $bsubshedcond = ' 1 = 1 ';
      $isubshedcond = ' 1 = 1 ';
   }

   # copy atmospheric deposition
   $listobject->querystring = "insert into scen_lrseg_atmosdep (projectid, scenarioid, lrseg, subshedid, landseg, ";
   $listobject->querystring .= "    riverseg, constit, thisyear, dep ) ";
   $listobject->querystring .= " select projectid, $scenarioid, lrseg, subshedid, landseg,  ";
   $listobject->querystring .= " riverseg, constit, thisyear, dep ";
   $listobject->querystring .= " from proj_atmosdep ";
   $listobject->querystring .= " where projectid = $projectid ";
   $listobject->querystring .= "    and $subshedcond ";
   $listobject->querystring .= "    and $yrcond ";
   $listobject->performQuery();

   # create subshed level total N summary of atmospheric deposition
   $listobject->querystring = "  insert into scen_subshed_atmosdep (scenarioid, subshedid, total_ndep, ";
   $listobject->querystring .= "     thisyear, src_citation) ";
   $listobject->querystring .= " select $scenarioid,  a.subshedid, ";
   $listobject->querystring .= "    sum(a.luarea*(b.dep))/sum(a.luarea)  ";
   $listobject->querystring .= "       as total_ndep, ";
   $listobject->querystring .= "    b.thisyear, 28 ";
   $listobject->querystring .= " from scen_lrsegs as a, scen_lrseg_atmosdep as b  ";
   $listobject->querystring .= " WHERE b.constit = 1 ";
   $listobject->querystring .= "    and a.lrseg = b.lrseg ";
   $listobject->querystring .= "    and a.thisyear = b.thisyear ";
   $listobject->querystring .= "    and a.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and b.scenarioid = $scenarioid ";
   $listobject->querystring .= "    and $ayrcond ";
   $listobject->querystring .= "    and $byrcond ";
   $listobject->querystring .= " GROUP by a.subshedid, b.thisyear ";
   $listobject->performQuery();
}

function importCropArea($projectid, $scenarioid, $src_scenario, $listobject, $subsheds, $thisyear, $debug) {

   # imports source populations, and distribution schemas
   # $src_scenario = the scenario to get data from. If this is -1, means get from project base table

   if ($scenarioid == $src_scenario) {
      print("<b>Error: </b> You cannot import from the same scenario. <br>");
   }


   if ($src_scenario > 0) {
      $popcol = 'thisyear';
      $segtable = 'scen_lrsegs';
      $tableidcol = 'scenarioid';
      $tableid = $src_scenario;
   } else {
      $popcol = 'popyear';
      $segtable = 'lucomposite';
      $tableidcol = 'projectid';
      $tableid = $projectid;
   }

   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subcond = " subshedid in ";
      $stcond = " stcofips in ";
      $scond = "   (select subshedid ";
      $scond .= "    from $segtable ";
      $scond .= "    where lrseg in ($sslist) ";
      $scond .= "       and thisyear in ($thisyear) ";
      $scond .= "       and $tableidcol = $tableid ";
      $scond .= "    group by subshedid )";
      $subcond .= $scond;
      $stcond .= $scond;
   } else {
      $subcond = ' (1 = 1) ';
      $stcond = ' (1 = 1) ';
   }


   if (!($src_scenario > 0)) {
      print("<b>Notice: </b>No scenario selected, importing project defaults.<br>");
   }

   print("Clearing old crop records.<br>");
   $listobject->querystring = " DELETE FROM scen_crops ";
   $listobject->querystring .= " where $subcond ";
   $listobject->querystring .= "    and thisyear in ( $thisyear )";
   $listobject->querystring .= "    and scenarioid = $scenarioid ";
   if ($debug) {
      print("$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   # insert new ones
   $thisdate = date('r',time());
   print("Inserting new Crop records.<br>");
   if ($src_scenario > 0) {

      #  get actual crop records that inputyields is based on
      $listobject->querystring = "  insert into scen_crops (scenarioid, projectid, subshedid, cropname, ";
      $listobject->querystring .= "    thisyear, croparea, luname) ";
      $listobject->querystring .= " select $scenarioid, projectid, subshedid, cropname, ";
      $listobject->querystring .= "    thisyear, croparea, luname ";
      $listobject->querystring .= " from scen_crops ";
      $listobject->querystring .= " where scenarioid = $src_scenario ";
      $listobject->querystring .= "    and $subcond ";
      $listobject->querystring .= "    and thisyear in ( $thisyear )";

      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();


   } else {

      #  get actual crop records that inputyields is based on
      $listobject->querystring = "  insert into scen_crops (scenarioid, projectid, subshedid, cropname, ";
      $listobject->querystring .= "    thisyear, croparea, luname) ";
      $listobject->querystring .= " select $scenarioid, projectid, subshedid, cropname, ";
      $listobject->querystring .= "    thisyear, croparea, luname ";
      $listobject->querystring .= " from proj_crops where projectid = $projectid ";
      $listobject->querystring .= "    and $subcond ";
      $listobject->querystring .= "    and thisyear in ( $thisyear )";

      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();

   }

}

function importCropCurves($projectid, $scenarioid, $src_scenario, $listobject, $subsheds, $thisyear, $debug) {

   # imports source populations, and distribution schemas
   # $src_scenario = the scenario to get data from. If this is -1, means get from project base table

   if ($scenarioid == $src_scenario) {
      print("<b>Error: </b> You cannot import from the same scenario. <br>");
   }

   if (strlen($thisyear) > 0) {
      $yrcond = " thisyear in ($thisyear) ";
      $ayrcond = " a.thisyear in ($thisyear) ";
      $byrcond = " b.thisyear in ($thisyear) ";
   } else {
      $yrcond = " 1 = 1 ";
      $ayrcond = " 1 = 1 ";
      $byrcond = " 1 = 1 ";
   }
   if ($src_scenario > 0) {
      $popcol = 'thisyear';
      $segtable = 'scen_lrsegs';
      $tableidcol = 'scenarioid';
      $tableid = $src_scenario;
   } else {
      $popcol = 'popyear';
      $segtable = 'lucomposite';
      $tableidcol = 'projectid';
      $tableid = $projectid;
   }

   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subcond = " subshedid in ";
      $stcond = " stcofips in ";
      $scond = "   (select subshedid ";
      $scond .= "    from $segtable ";
      $scond .= "    where lrseg in ($sslist) ";
      $scond .= "       and thisyear in ($thisyear) ";
      $scond .= "       and $tableidcol = $tableid ";
      $scond .= "    group by subshedid )";
      $subcond .= $scond;
      $stcond .= $scond;
   } else {
      $subcond = ' (1 = 1) ';
      $stcond = ' (1 = 1) ';
   }


   if (!($src_scenario > 0)) {
      print("<b>Notice: </b>No scenario selected, importing project defaults.<br>");
   }

   print("Clearing old crop records.<br>");
   $listobject->querystring = " DELETE FROM scen_crop_curves ";
   $listobject->querystring .= " where $subcond ";
   $listobject->querystring .= "    and scenarioid = $scenarioid ";
      $listobject->querystring .= "    and $yrcond ";
   if ($debug) {
      print("$listobject->querystring ;<br>");
   }
   $listobject->performQuery();
   $listobject->querystring = " DELETE FROM local_apply ";
   $listobject->querystring .= " where $subcond ";
   $listobject->querystring .= "    and scenarioid = $scenarioid ";
      $listobject->querystring .= "    and $yrcond ";
   if ($debug) {
      print("$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   # insert new ones
   $thisdate = date('r',time());
   print("Inserting new Crop records.<br>");
   if ($src_scenario > 0) {

      #  get actual crop records that inputyields is based on
      $listobject->querystring = "  insert into scen_crop_curves (scenarioid, projectid, cropname, subshedid, ";
      $listobject->querystring .= "    curvetype, source_type, need_pct, jan, feb,mar,apr, ";
      $listobject->querystring .= "    may, jun, jul, aug, sep, oct, nov, dec) ";
      $listobject->querystring .= " select $scenarioid, projectid, cropname, subshedid, ";
      $listobject->querystring .= "    curvetype, source_type, need_pct, jan, feb,mar,apr, ";
      $listobject->querystring .= "    may, jun, jul, aug, sep, oct, nov, dec ";
      $listobject->querystring .= " from scen_crop_curves ";
      $listobject->querystring .= " where scenarioid = $src_scenario ";
      $listobject->querystring .= "    and $subcond ";

      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();

      $listobject->querystring = "  insert into local_apply (scenarioid, projectid, luname, subshedid, thisyear, ";
      $listobject->querystring .= "    curvetype, source_type, need_pct, jan, feb,mar,apr, ";
      $listobject->querystring .= "    may, jun, jul, aug, sep, oct, nov, dec) ";
      $listobject->querystring .= " select $scenarioid, projectid, luname, subshedid, thisyear, ";
      $listobject->querystring .= "    curvetype, source_type, need_pct, jan, feb,mar,apr, ";
      $listobject->querystring .= "    may, jun, jul, aug, sep, oct, nov, dec ";
      $listobject->querystring .= " from local_apply ";
      $listobject->querystring .= " where scenarioid = $src_scenario ";
      $listobject->querystring .= "    and $subcond ";
      $listobject->querystring .= "    and $yrcond ";

      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();


   } else {

      #  get actual crop records that inputyields is based on
      $listobject->querystring = "  insert into scen_crop_curves (scenarioid, projectid, cropname, subshedid, ";
      $listobject->querystring .= "    curvetype, source_type, need_pct, jan, feb,mar,apr, ";
      $listobject->querystring .= "    may, jun, jul, aug, sep, oct, nov, dec) ";
      $listobject->querystring .= " select $scenarioid, projectid, cropname, subshedid, ";
      $listobject->querystring .= "    curvetype, source_type, need_pct, jan, feb,mar,apr, ";
      $listobject->querystring .= "    may, jun, jul, aug, sep, oct, nov, dec ";
      $listobject->querystring .= " from proj_crop_curves where projectid = $projectid ";
      $listobject->querystring .= "    and $subcond ";

      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();

      $listobject->querystring = "  insert into local_apply (scenarioid, projectid, luname, subshedid, thisyear, ";
      $listobject->querystring .= "    curvetype, source_type, need_pct, jan, feb,mar,apr, ";
      $listobject->querystring .= "    may, jun, jul, aug, sep, oct, nov, dec) ";
      $listobject->querystring .= " select $scenarioid, projectid, luname, subshedid, thisyear, ";
      $listobject->querystring .= "    curvetype, source_type, need_pct, jan, feb,mar,apr, ";
      $listobject->querystring .= "    may, jun, jul, aug, sep, oct, nov, dec ";
      $listobject->querystring .= " from proj_apply ";
      $listobject->querystring .= " where projectid = $projectid ";
      $listobject->querystring .= "    and $subcond ";
      $listobject->querystring .= "    and $yrcond ";

      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();

   }

}

function importBaseModelOutput($projectid, $scenarioid, $src_scenario, $listobject, $subsheds, $thisyear, $debug) {

   # imports the base model output from the source scenario
   # this enables projections to be made prior to running the model
   # $src_scenario = the scenario to get data from. If this is -1, means get from project base table

   if ($scenarioid == $src_scenario) {
      print("<b>Error: </b> You cannot import from the same scenario. <br>");
   }

   # assemble input variables into conditions
   if (count($subsheds) > 0) {
      $sslist = "'" . join("','", $subsheds) . "'";
      $subcond = " lrseg in ($sslist) ";
   } else {
      $subcond = " lrseg in ( select lrseg from scen_lrsegs where scenarioid = $scenarioid group by lrseg) ";
   }

   if (!($src_scenario > 0)) {
      print("<b>Notice: </b>No scenario selected, BMPs not Imported.");
   }

   # delete - and import -
   # this should ultimately be imported into the bmps_all table, but since we are mandating that
   # the only valid input resolution be lrseg (currently), it is OK to import directly into the
   # scen_lrseg_bmps table.
   print("Clearing old Model EOS/delivered records.<br>");
   $listobject->querystring = " DELETE FROM scen_model_delivered ";
   $listobject->querystring .= " where $subcond ";
   $listobject->querystring .= "    and scenarioid = $scenarioid ";
   if ($debug) {
      print("$listobject->querystring ;<br>");
   }
   $listobject->performQuery();
   $listobject->querystring = " DELETE FROM scen_model_eos ";
   $listobject->querystring .= " where $subcond ";
   $listobject->querystring .= "    and scenarioid = $scenarioid ";
   if ($debug) {
      print("$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   $listobject->querystring = " DELETE FROM scen_modelps_delivered ";
   $listobject->querystring .= " where $subcond ";
   $listobject->querystring .= "    and scenarioid = $scenarioid ";
   if ($debug) {
      print("$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   $listobject->querystring = " DELETE FROM scen_modelps_eos ";
   $listobject->querystring .= " where $subcond ";
   $listobject->querystring .= "    and scenarioid = $scenarioid ";
   if ($debug) {
      print("$listobject->querystring ;<br>");
   }
   $listobject->performQuery();

   # insert new ones
   $thisdate = date('r',time());
   print("Inserting new Model EOS/delivered records.<br>");
   if ($src_scenario > 0) {
      $listobject->querystring = "insert into scen_model_delivered ( scenarioid, landseg, riverseg, lrseg, ";
      $listobject->querystring .= "   constit, delivered, luarea, luname ) ";
      $listobject->querystring .= " select $scenarioid, landseg, riverseg, lrseg, ";
      $listobject->querystring .= "   constit, delivered, luarea, luname ";
      $listobject->querystring .= " from scen_model_delivered ";
      $listobject->querystring .= " WHERE scenarioid = $src_scenario ";
      $listobject->querystring .= "    and $subcond ";

      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();

      $listobject->querystring = "insert into scen_modelps_delivered (scenarioid, landseg, riverseg, lrseg, ";
      $listobject->querystring .= "   constit, delivered, luarea, luname ) ";
      $listobject->querystring .= " select $scenarioid, landseg, riverseg, lrseg, ";
      $listobject->querystring .= "   constit, delivered, luarea, luname ";
      $listobject->querystring .= " from scen_modelps_delivered ";
      $listobject->querystring .= " WHERE scenarioid = $src_scenario ";
      $listobject->querystring .= "    and $subcond ";

      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();

      $listobject->querystring = "insert into scen_model_eos ( scenarioid, landseg, riverseg, lrseg, ";
      $listobject->querystring .= "   constit, eos, luarea, luname ) ";
      $listobject->querystring .= " select $scenarioid, landseg, riverseg, lrseg, ";
      $listobject->querystring .= "   constit, eos, luarea, luname ";
      $listobject->querystring .= " from scen_model_eos ";
      $listobject->querystring .= " WHERE scenarioid = $src_scenario ";
      $listobject->querystring .= "    and $subcond ";

      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();

      $listobject->querystring = "insert into scen_modelps_eos ( scenarioid, landseg, riverseg, lrseg, ";
      $listobject->querystring .= "   constit, eos, luarea, luname ) ";
      $listobject->querystring .= " select $scenarioid, landseg, riverseg, lrseg, ";
      $listobject->querystring .= "   constit, eos, luarea, luname ";
      $listobject->querystring .= " from scen_modelps_eos ";
      $listobject->querystring .= " WHERE scenarioid = $src_scenario ";
      $listobject->querystring .= "    and $subcond ";

      if ($debug) {
         print("$listobject->querystring ;<br>");
      }
      $listobject->performQuery();

   } else {
      print("No Model EOS/delivered records imported. <br>");
   }


   return $tblname;

}
?>
