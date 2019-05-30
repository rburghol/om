<?php

include("./config.php");
$debug = 0;
// p52
//$basedir = "/opt/model/p52";
//$sc = 'p52An';
//$scid = 2;
//$startyear = 0;
// p52icprb
//$basedir = "/opt/model/p52icprb";
//$sc = 'p52NLR';
//$scid = 3;
// p5.3
$basedir = "/opt/model/p53/p532c-sova";
$sc = 'p53cal';
$scid = 4;
$startyear = 0; // if resuming an import set to last year fully imported

$content = file_get_contents("$basedir/config/control/river/$sc" . ".con");
$rdir = "$basedir/input/scenario/river/land_use";
$regex = array(
   'land use'=> '/LAND USE(.*?)END LAND USE/s'
);

foreach ($regex as $key => $thisreg) {
   preg_match_all( $thisreg, $content, $matches );
   $block = $matches[1][0];
   switch ($key) {
      case 'land use':
         $lines = split("\n", $block);
         foreach ($lines as $thisline) {
            if ( (strpos($thisline,'***') === false) and (ltrim(rtrim($thisline)) <> '')) {
               list($year, $mo, $day, $fileroot) = split(" ", $thisline);
               echo "$year - $mo - $day $fileroot \n";
               $file = "$rdir/land_use_$fileroot" . ".csv";
               echo "$file\n";
               if ($year < $startyear) {
                  print("Skipping $year < $startyear <br>\n");
               } else {
                  $kount = 0;
                  $lurows = readDelimitedFile( $file, ',', 1, -1);
                  foreach ($lurows as $thisrow) {
                     $rseg = $thisrow['riverseg'];
                     $lseg = $thisrow['landseg'];
                     foreach (array_keys($thisrow) as $thiskey) {
                        if (!in_array($thiskey, array('landseg','riverseg'))) {
                           // must be a land use name
                           $kount++;
                           $luname = $thiskey;
                           $luarea = $thisrow[$thiskey];
                           $locinfo = getModelLocation($listobject, $scid, 'lrseg', $rseg, $lseg, $luname, 1);
                           if ($locinfo['status'] == 1) {
                              $locid = $locinfo['location_id'];
                              $start = $year . '-07-01';
                              // we don't put an end time in here, because it is really assemd to be a snapshot
                              // for interpolation
                              //$end = ($year + 1) . '-06-30';
                              $listobject->querystring = " delete from cbp_scenario_param_temporal ";
                              $listobject->querystring .= " where scenarioid = $scid ";
                              $listobject->querystring .= " and location_id = $locid ";
                              $listobject->querystring .= " and param_group = 'SCHEMATIC' ";
                              $listobject->querystring .= " and param_block = 'SCHEMATIC' ";
                              $listobject->querystring .= " and param_name = 'AREA' ";
                              $listobject->querystring .= " and starttime = '$start' ";
                              if ($debug) {
                                 print("$listobject->querystring ; <br>");
                              }
                              $listobject->performQuery();
                              
                              $listobject->querystring = " insert into cbp_scenario_param_temporal ( scenarioid, ";
                              $listobject->querystring .= "    location_id, param_group, param_block, param_name, ";
                              $listobject->querystring .= "    starttime, thisvalue) ";
                              $listobject->querystring .= " values ($scid, $locid, 'SCHEMATIC', 'SCHEMATIC', 'AREA', ";
                              $listobject->querystring .= "    '$start', $luarea) ";
                              if ($debug) {
                                 print("$listobject->querystring ; <br>");
                              }
                              $listobject->performQuery();
                           } else {
                              print("Failed to add $scid, 'lrseg', $rseg, $lseg, $luname = $luarea to database <br> ");
                           }
                        }
                     }
                  }
                  print("Added $kount records for $year \n");
               }
            }
         }
      break;
   }

}

?>
