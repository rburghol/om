<?php

$setfunctionsloaded = 1;
if (!isset($listfloaded)) {
   include("functions/listfunctions.php");
}

function getSetHistory($userid, $numrecs) {

   $ownsql = "create temporary table ownwo select workoutid, owner from ";
   $ownsql .= "workout where owner = $userid";
   $ownquery = mysql_query($ownsql);

   $setsql = "create temporary table ownset select sets.setid, ";
   $setsql .= "sets.createdate, sets.descr from sets, mapworkoutsets, ";
   $setsql .= "ownwo where ownwo.workoutid = mapworkoutsets.workoutid ";
   $setsql .= "and sets.setid = mapworkoutsets.setid ";
   $setsql .= "order by sets.createdate desc limit $numrecs";
   $setquery = mysql_query($setsql);
   #print("$setsql<br>");
}

function compareBlogPerms($listobject, $uid, $bloggerid, $blogperms = 4) {
   $listobject->querystring = "select a.teamid, a.usertype, b.teamid as bteamid ";
   $listobject->querystring .= " from users as a, users as b ";
   $listobject->querystring .= " where a.userid = $uid ";
   $listobject->querystring .= "    and b.userid = $bloggerid ";
  # print("$listobject->querystring<br>");
   $listobject->performQuery();

   $viewteam = $listobject->getRecordValue(1,'teamid');
   $blogteam = $listobject->getRecordValue(1,'bteamid');
   $viewertype = $listobject->getRecordValue(1,'usertype');

   # default perms = 0
   $perms = 0;

   switch ($blogperms) {
      case 1:
      # coach only - usertype 2 is athlete, 1 and 3 are
      if ( ($viewteam == $blogteam) and ( ($viewertype == 1) or ($viewertype == 3) ) ) {
         $perms = 1;
      }

      break;

      case 2:
      # coach and teammates
      if ($viewteam == $blogteam) {
         $perms = 1;
      }
      break;

      case 3:
      # Viewable by all
      $perms = 1;
      break;

      case 4:
      # private blog - only viewable by author
      if ($uid == $bloggerid) {
         $perms = 1;
      }
      break;

      default:
      $perms = 0;
      break;
   }

   return $perms;
}

function getWorkoutPerms($listobject, $workoutid, $userid, $ugroup) {

   $listobject->querystring = "select usergroup, groupperms, ownerperms, ";
   $listobject->querystring .= " publicperms, OWNER  from workout ";
   $listobject->querystring .= " where workoutid = $workoutid ";
   $listobject->performQuery();
   $workoutrow = $listobject->queryrecords[0];

   # check permissions
   $ownerid = $workoutrow["OWNER"];
   $usergroup = $workoutrow["usergroup"];
   $groupperms = $workoutrow["groupperms"];
   $ownerperms = $workoutrow["ownerperms"];
   $publicperms = $workoutrow["publicperms"];

   # Perm Sub-routine
   $perms = 0;
   if ($userid == $ownerid) {
      # owner perms
      $perms = $perms | $ownerperms;
   }
   if ($ugroup == $usergroup) {
      $perms = $perms | $groupperms;
   }
   $perms = $perms | $publicperms;

  # print_r($workoutrow);

   return $perms;
}

function listsets($setids,$currentworkoutid,$printable) {
#print("showing sets $setids");

$ctotal = 0;

$getsetssql = "select * from sets where SETID in ($setids) order by SETORDER";
$getsetquery = mysql_query($getsetssql);

#print("$getsetssql");

while ($recordrow = mysql_fetch_array($getsetquery)) {

   $title = $recordrow["TITLE"];
   $es = $recordrow["ENERGYSYSTEM"];
   $di = $recordrow["DISTANCE"];
   $gr = $recordrow["SUBGROUP"];
   $ca = $recordrow["CATEGORY"];
   $de = $recordrow["DESCR"];
   $ty = $recordrow["SETTYPE"];
   $so = $recordrow["SETORDER"];
   $setid = $recordrow["SETID"];
   $sp = $recordrow["SETPARAMS"];
   $rp = urlencode("workoutbody.php");

   if (!(isset($setnum))) {
      $setnum = 1;
      print("<table width=100% class='workout'><tr>");

      $infstring = "&nbsp;";


      if (!($printable)) {
         print("<td valign=bottom><b>Action</b></td>\n");
         $infstring = "Set Info";
      }
      if ($ty <> 3) {
         print("<td valign=bottom><b>$infstring</b></td>\n<td valign=bottom><b>Group</b></td>\n<td valign=bottom><b>En. Sys.</b></td>\n<td valign=bottom><b>Dist.</b></td>\n</tr>\n");
      } else {
         print("<td valign=bottom colspan=4><b>Circuit Set</b></td>\n</tr>\n");
      }
   }
   print("<tr bgcolor='$bgcolor'>");

   if (!$printable) {
   print("<td valign=top align=right>\n");
   #print("<tr><td valign=top>\n");
   print("<form name='SET$setnum' action='workouteditor.php' target='workout'>\n");
   print("<input type=hidden name='editview' value='1'>\n");
   print("<input type=hidden name='setid' value='$setid'>\n");
   print("<input type=hidden name='setnum' value='$setnum'>\n");
   print("<input type=hidden name='setorder' value='$so'>\n");
   print("<input type=hidden name='formtarget' value='workout'>\n");
   print("<input type=hidden name='currentworkoutid' value='$currentworkoutid'>\n");
   print("<select name='setaction' onchange=\"submit();\">\n");
   print("<option value='0'>---</option>\n");
   print("<option value='1'>Edit</option>\n");
   print("<option value='2'>Del</option>\n");
   print("<option value='3'>MvUp</option>\n");
   print("<option value='4'>MvDn</option>\n");
   if ($ty == 3) {
      print("<option value='5'>App</option>\n");
   }
   print("</select>\n</form>\n");
   if ($ty == 3) {
      print("<br>|<br>$sp x |<br>|");
   }
   print("</td>\n");
   }

   switch ($ty) {
      case 1:
      list($reps,$dist,$interval) = split(";",$sp);
      $total = $reps * $dist;
      $di = $total;
      print("\n<td valign=top>$reps x $dist on $interval &nbsp;&nbsp;&nbsp;$de</td>\n");
      break;

      case 2:
      list($start,$end,$increment,$interval) = split(";",$sp);
      print("\n<td valign=top>$start to $end by $increment's on $interval<br>$de</td>");
      if (strlen($increment) == 0) {$increment = $start;}
     if ($start < $end) {
        $steps = ($end - $start) / $increment;
        $total = 0;
        for ($i=1;$i <= $steps;$i++) {
           $total += $i * $increment + $start;
        }
        $total += $start;
     } else {
        $steps = ($start - $end) / $increment;
        $total = 0;
        for ($i=1;$i <= $steps;$i++) {
           $total += $i * $increment + $end;
        }
        $total += $end;
      }
      $di = $total;
      break;

      case 3:
      # circuit set. Must find all sets claiming to be children
      $reps = $sp;
      $childrensql = "select * from setcontainer where PARENTSET = $setid";
      $childrenquery = mysql_query($childrensql);

      print("\n<td colspan=4 valign=top><table width=100% class='workout'><tr>\n");
      print("\n<td align=left valign=top>\n");

      #print("Sub sets go here. Sub sets go here. Sub sets go here. Sub sets go here. Sub sets go here.");

      $setdelim = "";
      while ($childrenrow = mysql_fetch_array($childrenquery)) {
         $subsetid = $childrenrow["CHILDSET"];
         $subsets .= "$setdelim$subsetid";
         $setdelim = ",";

      }

      if (mysql_num_rows($childrenquery) > 0) {

         $cyards = listsets($subsets,$currentworkoutid,$printable);

      }

      #print("&nbsp;</td></tr></table></td>\n");
      print("</tr></table></td></tr></table>\n");
      $total = $cyards * intval($reps);
      break;

      case 4:
      list($lstart,$peak,$lend,$lincrement,$linterval) = split(";",$sp);
      if (strlen($lincrement) == 0) {$lincrement = $lstart;}
      # going up
      if ($lstart < $peak) {
         $steps = ($peak - $lstart) / $lincrement;
         $total = 0;
         for ($i=1;$i <= $steps;$i++) {
            $total += $i * $lincrement + $lstart;
            #print("$lincrement|$total,");
         }
         $total += $lstart;
      } else {
         $steps = ($lstart - $lend) / $lincrement;
         $total = 0;
         for ($i=1;$i <= $steps;$i++) {
            $total += $i * $lincrement;
         }
         $total += $lend;
      }
      # going down
      if ($lend > $peak) {
         $steps = ($lend - $peak) / $lincrement;
         for ($i=1;$i <= $steps;$i++) {
            $total += $i * $lincrement + $peak;
         }
      } else {
         $steps = ($peak - $lend) / $lincrement;
         for ($i=$steps;$i >= 1;$i--) {
            $total += $i * $lincrement;
         }
      }
      $di = $total;
      $cval = "$lstart;$peak;$lend;$lincrement;$interval";
      print("\n<td valign=top>$lstart to $peak to $lend, by $lincrement's on $interval<br>$de</td>");
      break;

   }
   $workoutdistance += $total;

   print("<td valign=top>");
   listList($cname,"subgroup","SUBGROUPNAME","SUBGROUPID",$gr);
   print("</td><td valign=top>");
   list($listtable,$listcolumn,$listpkcol) = split(":",$custom1);
   listList($cname,"energysystem","SYSNAME","SYSID",$es);
   print("</td><td valign=top>$di</td>\n");

}
return $workoutdistance;

} /* end function listsets($setids) */


/* begin function deleteSet */

function deleteSet($db,$dbname,$setid) {

   mysql_select_db($dbname,$db);

   $delsql = "delete from sets where setid = $setid";
   $delquery = mysql_query($delsql);

   $delsql = "delete from mapworkoutsets where setid = $setid";
   $delquery = mysql_query($delsql);

   $delsql = "delete from setcontainer where childset = $setid";
   $delquery = mysql_query($delsql);

} /* end function deleteSet() */


function getDefaultGroup($listobject, $uid, $teamid) {
   # gets the default group to use when creating a new workout
   $defaultgroup = getUserPrefs2($listobject, $uid,'defaultgroup',-1);
   if ($defaultgroup == -1) {
      # value not set, insert one for this user
      $listobject->querystring = "select  a.userid, a.groupid, max(b.groupid) as firstgroup ";
      $listobject->querystring .= " from users as a left outer join maingroup as b ";
      $listobject->querystring .= " on ( b.teamid = $teamid";
      $listobject->querystring .= " and a.teamid = $teamid";
      $listobject->querystring .= " and a.userid = $uid ) ";
      $listobject->querystring .= " where a.userid = $uid ";
      $listobject->querystring .= " group by a.userid, a.groupid ";
      $listobject->performQuery();
      $firstgroup = $listobject->getRecordValue(1,'firstgroup');
      $groupid = $listobject->getRecordValue(1,'groupid');
      setUserPrefs2($listobject, $uid, 'defaultgroup', $firstgroup, 1);
      $defaultgroup = $firstgroup;
   }

   return $defaultgroup;
}

function getUserPrefs2($listobject, $uid, $prefname, $currentvalue) {

   $listobject->querystring = "select prefval from preferences where prefname = '$prefname' and userid = $uid";
   $listobject->performQuery();

   $prefval = $listobject->getRecordValue(1,'prefval');
   if ( ($prefval == '')or ($prefval == $listobject->norecorderror) ) {
      $prefval = $currentvalue;
   }

   return $prefval;

} /* end function getUserPrefs() */

function getUserPrefs($db, $dbname, $uid, $prefname, $currentvalue) {

   mysql_select_db($dbname,$db);

   $prefsql = "select prefval from preferences where prefname = '$prefname' and userid = $uid";
   $prefquery = mysql_query($prefsql);

   $prefrow = mysql_fetch_array($prefquery);
   $prefval = $prefrow['prefval'];
   if ($prefval == '') {
      $prefval = $currentvalue;
   }

   return $prefval;

} /* end function getUserPrefs() */


function setUserPrefs2($listobject, $uid, $prefname, $currentvalue, $newpref = 0) {

   if ($newpref) {
      $listobject->querystring = "delete from preferences where userid = $uid and prefname = '$prefname'";
      $listobject->performQuery();
      $listobject->querystring = "insert into preferences (userid,prefname,prefval) values ($uid, '$prefname', '$currentvalue')";
   } else {
      $listobject->querystring = "update preferences set prefval = '$currentvalue' where prefname = '$prefname' and userid = $uid";
   }
   $listobject->performQuery();

} /* end function getUserPrefs() */

function setUserPrefs($db, $dbname, $uid, $prefname, $currentvalue) {

   mysql_select_db($dbname,$db);

   $prefsql = "update preferences set prefval = '$currentvalue' where prefname = '$prefname' and userid = $uid";
   $prefquery = mysql_query($prefsql);

} /* end function getUserPrefs() */

/* begin function addSet($db,$dbtable,$iscircuit,$parentid) */

function addSet($db,$dbname,$iscircuit,$parentid) {

   mysql_select_db($dbname,$db);

   /* $iscircuit 0 or 1 */
   if ($iscircuit) {
      $mtable = "setcontainer";
      $pcol = "PARENTSET";
      $ccol = "CHILDSET";
   } else {
      $mtable = "mapworkoutsets";
      $pcol = "WORKOUTID";
      $ccol = "SETID";
   }

   $countsql = "select $ccol from $mtable where $pcol = $parentid";
   $countquery = mysql_query($countsql);
   $rowcount = mysql_num_rows($countquery);
   $lastset = $rowcount + 1;
   $now = date('Y-m-d h:i:s');
   $newsetsql = "insert into sets(DESCR,SETTYPE,SETORDER,createdate) values('',7,$lastset,'$now')";
   #print($newsetsql);
   $newsetquery = mysql_query($newsetsql);

   $setid = mysql_insert_id();
   /* mapping code */
   $setmapsql = "insert into $mtable($pcol,$ccol) values($parentid,$setid)";
   $setmapquery = mysql_query($setmapsql);

   #print($setaddsql);
   mysql_query($setaddsql);

   return $setid;

} /* end function addSet() */


function printSetHeader($printable) {

   if ($printable) {
      $infstring = "&nbsp;";
   } else {
      $infstring = "Action";
   }

   # printas out a 5 column header
   print("<tr><td valign=bottom><b>$infstring</b></td>\n<td valign=bottom align=center><b>Set Info</b></td><td valign=bottom><b>Group</b></td>\n<td valign=bottom><b>En. Sys.</b></td>\n<td valign=bottom><b>Dist.</b></td>\n</tr>\n");

}

function duplicateSet($db,$dbname,$setid) {

# create temporary table setstuff select title,energysystem,category,subgroup,setstring,descr,setparams,settype from sets where setid = 650;
#insert into sets (title,energysystem,category,subgroup,setstring,descr,setparams,settype) select * from setstuff;
}

function saveSetToBookmarks($dbname,$db,$uid,$saveid) {

   # save set into this users favourites pile
   mysql_select_db($dbname,$db);
   $savesetsql = "insert into bookmarks(userid,setid) values($uid,$saveid)";
   $savesetquery = mysql_query($savesetsql);

} /* end saveSetToBookmarks */

function addToFavourites($dbname,$db,$uid,$setid) {


   $workoutsql = "select workoutid from workout where workouttype = 2 and owner = $uid and comments = 'favourites'";
   $workoutquery = mysql_query($workoutsql);
   $war = mysql_fetch_array($workoutquery);
   $workoutid = $war['workoutid'];

   # save set into this users favourites pile

   $newsetid = copySet($dbname,$db,$setid,$workoutid,0);

   return $newsetid;

} /* end saveSetToBookmarks */

function copySet($dbname,$db,$setid,$newworkoutid,$isincircuit) {

   # $iscircuit - should only be set to true if this is a subset member being copied during a
   #              full circuit copy. The circuit parent set MUST not be set

   mysql_select_db($dbname,$db);
   # copy workout into temporary table
   $savesetsql = "create temporary table settemp (setid integer, TITLE varchar(128), ENERGYSYSTEM varchar(60), CATEGORY varchar(60), SUBGROUP varchar(60), TYPE varchar(60), SETSTRING varchar(255), DESCR BLOB, SETPARAMS varchar(255), SETNUMBER INTEGER, SETORDER INTEGER, SETTYPE INTEGER, DISTANCE DOUBLE, TIMESECONDS INTEGER, CIRCUIT INTEGER)";
   $savesetquery = mysql_query($savesetsql);
   #print("$savesetsql <br>");
   # copy workout into temporary table
   $savesetsql = "insert into settemp (setid, TITLE , ENERGYSYSTEM, CATEGORY, SUBGROUP, TYPE, SETSTRING , DESCR, SETPARAMS, SETNUMBER, SETORDER, SETTYPE, DISTANCE, TIMESECONDS, CIRCUIT) select b.setid, b.TITLE, b.ENERGYSYSTEM, b.CATEGORY, b.SUBGROUP, b.TYPE, b.SETSTRING, b.DESCR, b.SETPARAMS, b.SETNUMBER, b.SETORDER, b.SETTYPE, b.DISTANCE, b.TIMESECONDS, b.CIRCUIT from sets as b where b.setid = $setid";
   $savesetquery = mysql_query($savesetsql);
   #print("$savesetsql <br>");

   # create new, blank set in destination workout
   $newsetid = addSet($db,$dbname,$isincircuit,$newworkoutid);
   $savesetsql = "update sets, settemp set sets.TITLE = settemp.TITLE, sets.ENERGYSYSTEM = settemp.ENERGYSYSTEM, sets.CATEGORY = settemp.CATEGORY, sets.SUBGROUP = settemp.SUBGROUP, sets.TYPE = settemp.TYPE, sets.SETSTRING = settemp.SETSTRING, sets.DESCR = settemp.DESCR, sets.SETPARAMS = settemp.SETPARAMS, sets.SETNUMBER = settemp.SETNUMBER, sets.SETTYPE = settemp.SETTYPE, sets.DISTANCE = settemp.DISTANCE, sets.TIMESECONDS = settemp.TIMESECONDS, sets.CIRCUIT = settemp.CIRCUIT where sets.setid = $newsetid and settemp.setid = $setid";
   $savesetquery = mysql_query($savesetsql);
   #print("$savesetsql <br>");

   # clean up after yourself
   $insertsetsql = "drop table settemp";
   $insertsetquery = mysql_query($insertsetsql);

   # create copies of any subsets (if this is a circuit)
   $subsetsql = "select CHILDSET from setcontainer where PARENTSET = $setid";
   $subsetquery = mysql_query($subsetsql);
   while ($thissubrow = mysql_fetch_array($subsetquery) ) {
      $subsetid = $thissubrow["CHILDSET"];
      $newsubsetid = copySet($dbname,$db,$subsetid,$newsetid,1);
   }

   return $newsetid;

}

function getWorkoutID($db,$dbname,$uid,$workouttype,$workoutcomments) {

   mysql_select_db($dbname,$db);
   $insertsetsql = "select workoutid from workout where owner = $uid and workouttype = $workouttype and COMMENTS = '$workoutcomments'";
   $insertsetquery = mysql_query($insertsetsql);
   $thisrow = mysql_fetch_array($insertsetquery);


   $thisid = $thisrow['workoutid'];

   #print("$insertsetsql - $thisid<br>");
   return $thisid;
}

function copyFromFavourites($dbname,$db,$uid,$setid,$workoutid) {

   # insert a set into this users workout
   $newsetid = copySet($dbname,$db,$setid,$workoutid,0);

   return $newsetid;

} /* end saveSetToBookmarks */

function showSet($dbobj,$dbname,$recordrow,$printable,$setnum,$currentworkoutid,$hide,$perms, $outputoptions = 1, $showged = 1, $showsetnum = 0) {

   mysql_select_db($dbname,$dbobj);

   $formout = '';

   $title = $recordrow["TITLE"];
   $es = $recordrow["ENERGYSYSTEM"];
   $di = $recordrow["DISTANCE"];
   $gr = $recordrow["SUBGROUP"];
   $ca = $recordrow["CATEGORY"];
   $de = $recordrow["DESCR"];
   $ty = $recordrow["SETTYPE"];
   $so = $recordrow["SETORDER"];
   $setid = $recordrow["SETID"];
   $sp = $recordrow["SETPARAMS"];
   $rp = urlencode("workoutbody.php");

   $it = $recordrow["intervaltype"];
   $iname = getMySQLSubSelectList ($dbname,$dbobj,'intervaltype','shortname',"intid = $it");
   $iname = str_replace("'",'',$iname);
   # assume that it is an interval, unless otherwise,
   # only show the interval type if it is of type base, or rest, etc.
   if ($it == 1) {
      $iname = '';
   }
   if (!$hide) { print("\n<tr>\n");}


# output the first column, has action menu if printable, OR nbsp,
# also has circuit reps if this is a circuit set
   if (($ty > 0)) {

      if (!$hide) { print("\n<td valign=top align=right>\n"); }

      if ( (!$printable) and (!$hide) and (!($perms == 4)) ) {

        print("<form name='SET$setnum' action='workouteditor.php' target='workout'>\n");
        print("<input type=hidden name='editview' value='1'>\n");
        print("<input type=hidden name='setid' value='$setid'>\n");
        print("<input type=hidden name='setnum' value='$setnum'>\n");
        print("<input type=hidden name='setorder' value='$so'>\n");
        print("<input type=hidden name='formtarget' value='workout'>\n");
        print("<input type=hidden name='currentworkoutid' value='$currentworkoutid'>\n");
        print("<select name='setaction' onchange=\"submit();\">\n");
        print("<option value='0'>---</option>\n");
        print("<option value='1'>Edit</option>\n");
        print("<option value='2'>Del</option>\n");
        print("<option value='3'>MvUp</option>\n");
        print("<option value='4'>MvDn</option>\n");
        if ($ty == 3) {
          print("<option value='5'>App</option>\n");
        }
        print("<option value='10'>MkFav</option>\n");
        print("<option value='11'>MkTest</option>\n");
        print("</select>\n</form>\n");
      }

      if ($printable and ($ty <> 3) and (!$hide) and ($perms <> 4)) {
        print("&nbsp;\n");
     }

     if ($perms == 4) {
        #print("\n<td valign=top align=right>\n");
        # read-only, show option to save this set to favourites table
             # show link to Best of SoulSwimmer
if (($showged == 1) ) {
             showRadio("addtofavourites$setid", $setid, 0);
             print("<a href='./workouteditor.php?currentworkoutid=$currentworkoutid&setaction=15&setid=$setid'>BOSS</a>");
}
         #print("</td>\n");

     }

     if (!$hide) { print("</td>\n"); }

      #$printable = 0;
   }
   switch ($ty) {
      case 1:
      list($reps,$dist,$interval) = split(";",$sp);

      $total = $reps * $dist;
      $di = $total;
      $tt = $reps * (timeToSeconds($interval));

      if (!$hide) {
         print("\n<td valign=top>$reps x $dist on $interval $iname&nbsp;&nbsp;&nbsp;$de</td>\n");
         #print("\n<td valign=top><table class='workout'><tr><td>$reps x $dist on $interval</td><td>$de</td></tr></table>\n");
      }
      break;

      case 2:
      list($start,$end,$increment,$interval) = split(";",$sp);
      if (!$hide) {
         print("\n<td valign=top>$start to $end by $increment's on $interval $iname<br>$de</td>");
         #print("\n<td valign=top><table class='workout'><tr><td>$start to $end by $increment's on $interval</td><td>$de</td></tr></table>\n");
      }
      if (strlen($increment) == 0) {$increment = $start;}
     if ($start < $end) {
        $steps = ($end - $start) / $increment;
        $total = 0;
        for ($i=1;$i <= $steps;$i++) {
           $total += $i * $increment + $start;
        }
        $total += $start;
     } else {
        $steps = ($start - $end) / $increment;
        $total = 0;
        for ($i=1;$i <= $steps;$i++) {
           $total += $i * $increment + $end;
        }
        $total += $end;
      }
      $di = $total;
      break;

      case 3:
      # circuit set. Must find all sets claiming to be children
      $reps = $sp;
      $subsets = '';
      $childrensql = "select * from setcontainer where PARENTSET = $setid";
      $childrenquery = mysql_query($childrensql);
#print("$childrensql<br>");
      $thissetno = 1;
      $cyards = 0;

      $setdelim = "";
      if (!$hide) {
         print("<td valign=bottom align=left><b>Circuit:</b>$de</td>");
         print("<td valign=top>");
         listList($cname,"subgroup","SUBGROUPNAME","SUBGROUPID",$gr);
         print("</td><td valign=top>");
      }
      list($listtable,$listcolumn,$listpkcol) = split(":",$custom1);
      while ($childrenrow = mysql_fetch_array($childrenquery)) {
         $subsetid = $childrenrow["CHILDSET"];
         $subsets .= "$setdelim$subsetid";
         $setdelim = ",";
      }
      $numcrows = count(split(',',$subsets));
      if (!$hide) {
         listList($cname,"energysystem","SYSNAME","SYSID",$es);
         print("</tr>");
         print("<tr valgin=top><td align=right width=5% valign=top>|<br>");
         for ($ic = 1;$ic < $numcrows; $ic++) {
            if ($ic == intval(($numcrows/2) + 0.5) ) {
               print("$sp x |<br>");
            } else {
               print("|<br>|<br>");
            }
         }
         print("|");
         print("</td><td colspan=4 valign=top align=left><table width=100% class='circuit'>");
         printSetHeader($printable);
      }
      $subsetquery = mysql_query("select * from sets where setid in ($subsets) order by setorder");
     while ($subsetrow = mysql_fetch_array($subsetquery)) {

         $cyards += showSet($dbobj,$dname,$subsetrow,$printable,$thissetno,$currentworkoutid,$hide,$perms);
         $thissetno++;
      }

      $total = $cyards * intval($reps);

      if (!$hide) {

         print("<tr><td colspan=5 valign=top align=left>Circuit Total: $total</td></tr>");
         print("</table></td>\n");
      }
      break;

      case 4:
      list($lstart,$peak,$lend,$lincrement,$linterval) = split(";",$sp);
      if (strlen($lincrement) == 0) {$lincrement = $lstart;}
      # going up
      if ($lstart < $peak) {
         $steps = ($peak - $lstart) / $lincrement;
         $total = 0;
         for ($i=1;$i <= $steps;$i++) {
            $total += $i * $lincrement + $lstart;
            #print("$lincrement|$total,");
         }
         $total += $lstart;
      } else {
         $steps = ($lstart - $lend) / $lincrement;
         $total = 0;
         for ($i=1;$i <= $steps;$i++) {
            $total += $i * $lincrement;
         }
         $total += $lend;
      }
      # going down
      if ($lend > $peak) {
         $steps = ($lend - $peak) / $lincrement;
         for ($i=1;$i <= $steps;$i++) {
            $total += $i * $lincrement + $peak;
         }
      } else {
         $steps = ($peak - $lend) / $lincrement;
         for ($i=$steps;$i >= 1;$i--) {
            $total += $i * $lincrement;
         }
      }
      $di = $total;
      $cval = "$lstart;$peak;$lend;$lincrement;$interval";


      if (!$hide) {
         print("\n<td valign=top>$lstart to $peak to $lend, by $lincrement's on $linterval $iname<br>$de</td>");
      }
/*
#  This will indent the set description, but for now,
#  this screws up formatting a little, as the interval often gets pushed to the second line
      if (!$hide) {
        print("\n<td valign=top><table class='workout'><tr><td>$lstart to $peak to $lend, by $lincrement's on $linterval $iname</td><td>$de</td></tr></table>\n");
     }
*/
      break;

      case 5:
         if (!$hide) {
            print("<td colspan=4 valign=top align=left><b>$de</b></td>");
         }
      break;

      case 6:
         if (!$hide) {
            print("<td colspan=4 valign=top align=left><b>$de</b></td>");
         }
      break;

      case 7:
         list($thisdist, $setobject) = parseBlockSetString($de);
         if (!$hide) {
            print("\n<td valign=top class='workout'><table class='workout'><tr>\n");
         }
         $formout .= "<table class='workout'><tr>\n";
         $reps = $setobject['reps'];
         $cdesc = $setobject['descr'];
         $formout .= "<td colspan=2><b>$cdesc</b></td></tr><tr>\n";
         if (!$hide) {
            if ( strlen(rtrim(ltrim($cdesc))) > 0) {
               print("<td colspan=2><b>$cdesc</b></td></tr><tr>\n");
            }
         }
         $sc = count($setobject['subsets']);
         if ($reps > 1) {
            $cwi = strlen("$reps x |") * 6;
            if (!$hide) {
               print("<td align=right width=$cwi>");
            }
            $formout .= "<td align=right width=$cwi>";
            for ($kk = 1; $kk <= ($sc/2.0); $kk++) {
               $frstr .= "|<br>";
            }
            $outstr .= "$frstr$reps x |<br>$frstr</td>";
            if (!$hide) {
               print("$frstr$reps x |<br>$frstr</td>");
            }
            $formout .= $outstr;
         }
         if (!$hide) {
            print("\n<td valign=top><table valign=top>");
         }
         $formout .= "\n<td valign=top><table valign=top class='workout'>";
         foreach($setobject['subsets'] as $thisset) {
            $tp = $thisset['setparams'];
            $td = $thisset['descr'];
            $tg = $thisset['group'];
            $tz = $thisset['zone'];
            $sst = $thisset['total'];
            $step = $thisset['step'];
            $tht = $thisset['totaltime'];
            if ($sst > 0) {
               $sto = '';
               $stc = '';
            } else {
               $sto = '<b>';
               $stc = '</b>';
            }
            if (!$hide) {
               print("<tr><td>$sto$tp &nbsp; $td$stc </td></tr>");
            }
            $formout .= "<tr><td>$sto$tp &nbsp; $td$stc </td></tr>";
            $tt += 1.0 * $tht;
         }
         if (!$hide) {
            print("\n</table></td>");
         }
         $formout .= "\n</table></td></tr></table>";

         if (!$hide) {
            print("\n</tr></table></td>");
         }

         $total = $setobject['total'];
         $di = $total;
      break;
   }
   $setnum++;
   $workoutdistance += $total;
   if (!(($ty == 3) or ($ty == 5) or ($ty == 6)) and (!$hide) and ($showged == 1) ) {
      print("<td valign=top>");
      listList($cname,"subgroup","SUBGROUPNAME","SUBGROUPID",$gr);
      print("</td><td valign=top>");
      list($listtable,$listcolumn,$listpkcol) = split(":",$custom1);
      listList($cname,"energysystem","SYSNAME","SYSID",$es);
      $tft = secondsToTime($tt, 2);
      print("</td><td valign=top>$di ($tft)</td>");
   }

   if (!$hide) { print("</tr>"); }

   switch ($outputoptions) {

      case "2":
      $output = array();
      $output['distance'] = $workoutdistance;
      $output['totaltime'] = $tt;
      $output['descr'] = $de;
      $output['setnumber'] = $so;
      $output['formatted'] = $formout;

      break;


      default:
      $output = $workoutdistance;
      break;
   }

   return $output;

} /* end showSet */


function parseSet($dbobj, $setid) {

   # deprecated
   # parseSet($dbobj,$dbname,$setid) {
   # mysql_select_db($dbname,$dbobj);

   # $setsql = "select * from sets where setid = $setid";
   # $setquery = mysql_query($setsql);
   # $recordrow = mysql_fetch_array($setquery);

   $dbobj->querystring = "select * from sets where setid = $setid";
   $dbobj->performQuery();
   $recordrow = $dbobj->queryrecords[0];

   $setobject = array();
   $subsets = array();

   $title = $recordrow["TITLE"];
   $es = $recordrow["ENERGYSYSTEM"];
   $di = $recordrow["DISTANCE"];
   $gr = $recordrow["SUBGROUP"];
   $ca = $recordrow["CATEGORY"];
   $de = $recordrow["DESCR"];
   $ty = $recordrow["SETTYPE"];
   $so = $recordrow["SETORDER"];
   $setid = $recordrow["SETID"];
   $sp = $recordrow["SETPARAMS"];

# output the first column, has action menu if printable, OR nbsp,
# also has circuit reps if this is a circuit set

   switch ($ty) {
      case 1:
      list($reps,$dist,$interval) = split(";",$sp);
      $total = $reps * $dist;
      $di = $total;
      $setparams = "$reps x $dist on $interval $iname";
      break;

      case 2:
      list($start,$end,$increment,$interval) = split(";",$sp);

      if (strlen($increment) == 0) {$increment = $start;}

     if ($start < $end) {
        $steps = ($end - $start) / $increment;
        $total = 0;
        for ($i=1;$i <= $steps;$i++) {
           $total += $i * $increment + $start;
        }
        $total += $start;
        $dist = $end;
     } else {
        $steps = ($start - $end) / $increment;
        $total = 0;
        for ($i=1;$i <= $steps;$i++) {
           $total += $i * $increment + $end;
        }
        $total += $end;
        $dist = $start;
      }

      $setparams = "$start to $end by $increment on $interval $iname";

      $di = $total;
      break;

      case 3:
      # circuit set. Must find all sets claiming to be children
      $reps = $sp;
      $subsets = array();
      $childrensql = "select childset from setcontainer where PARENTSET = $setid";
      $childrenquery = mysql_query($childrensql);

      $setparams = "$reps x {<ul>";
      $dist = 0;

      while ($childrenrow = mysql_fetch_array($childrenquery)) {

         $thisid = $childrenrow['childset'];
         # deprecated
         #$thissub = parseSet($dbobj,$dname,$thisid);
         $thissub = parseSet($dbobj, $thisid);
         if ($dist < $thissub['distance']) {
            $dist = $thissub['distance'];
         }
         $subparams = $thissub['setparams'];
         $subdescr = $thissub['descr'];
         $cyards += $thissub['total'];
         array_push($subsets, $thissub);
         $setparams .= "<li> $subparams - $subdescr ";
      }

      $total = $cyards * intval($reps);
      $setparams .= "</ul>}";

      break;

      case 4:
      list($lstart,$peak,$lend,$lincrement,$linterval) = split(";",$sp);
      if (strlen($lincrement) == 0) {$lincrement = $lstart;}
      # going up
      if ($lstart < $peak) {
         $steps = ($peak - $lstart) / $lincrement;
         $total = 0;
         for ($i=1;$i <= $steps;$i++) {
            $total += $i * $lincrement + $lstart;
            #print("$lincrement|$total,");
         }
         $total += $lstart;
         $dist = $peak;
      } else {
         $dist = $start;
         $steps = ($lstart - $lend) / $lincrement;
         $total = 0;
         for ($i=1;$i <= $steps;$i++) {
            $total += $i * $lincrement;
         }
         $total += $lend;
      }
      # going down
      if ($lend > $peak) {
         $steps = ($lend - $peak) / $lincrement;
         for ($i=1;$i <= $steps;$i++) {
            $total += $i * $lincrement + $peak;
         }
      } else {
         $steps = ($peak - $lend) / $lincrement;
         for ($i=$steps;$i >= 1;$i--) {
            $total += $i * $lincrement;
         }
      }
      $di = $total;
      $setparams = "$lstart to $peak to $lend by $lincrement on $interval $iname";

      break;

      case 7:
      # natural language syntax set parser call
      list($total, $textrows) = parseBlockSetString($testset);
      $setparams = join('<br>', $textrows);
      break;

   }
   $setnum++;

   $setobject['setparams'] = $setparams;
   $setobject['descr'] = $de;
   $setobject['subsets'] = $subsets;
   $setobject['type'] = $ty;
   $setobject['total'] = $total;
   # distance of repeats, only really valid for non-ladder
   # default is to longest repeat of the set
   $setobject['distance'] = $dist;

   return $setobject;

} /* end parseSet */


/* BEGIN function editSet() */

function
editSet($db,$dbname,$recid,$invars) {

/*

function
editSet($db,$dbname,$recid,$reps,$yards,$interval,$intervaltype,$lstart,$peak,$lend, $lincrement,$settype, $setstring, $setorder, $subgroup, $descr,$energysystem) {

*/

#print_r($invars);

# custom action for field PARAMS
#error_reporting(2);

$reps = $invars['reps'];
$yards = $invars['yards'];
$interval = $invars['interval'];
$intervaltype = $invars['intervaltype'];
$lstart = $invars['lstart'];
$peak = $invars['peak'];
$lend = $invars['lend'];
$lincrement = $invars['lincrement'];
$settype = $invars['settype'];
$setstring = $invars['setstring'];
$setorder = $invars['setorder'];
$subgroup = $invars['subgroup'];
#add slashes to allow quotes and such in descriptors
$descr = addslashes($invars['descr']);
$energysystem = $invars['energysystem'];
$makeresultfile = $invars['makeresultfile'];
$numresults = $invars['numresults'];
if (!($numresults > 0)) { $numresults = 0; }


mysql_select_db($dbname,$db);


   switch ($settype) {
      case 1:
      $total = $reps * $yards;
      $cval = "$reps;$yards;$interval";
      break;

      case 2:
      if(isset($lstart)) {
         if (strlen($lincrement) == 0) {$lincrement = $lstart;}
         if ($lstart < $lend) {
            $steps = ($lend - $lstart) / $lincrement;
            $total = 0;
            for ($i=1;$i <= $steps;$i++) {
               $total += $i * $lincrement;
            }
            $total += $lstart;
         } else {
            $steps = ($lstart - $lend) / $lincrement;
            $total = 0;
            for ($i=1;$i <= $steps;$i++) {
               $total += $i * $lincrement;
            }
               $total += $lend;
            }
         }

      $cval = "$lstart;$lend;$lincrement;$interval";
      break;

      case 3:
      # Circuit Set.
      # form variable reps must be present in submitting form.
      # indicates number of times to repeat the set
      $cval = $reps;
      break;

      case 4:
      if(isset($lstart)) {
         if (strlen($lincrement) == 0) {$lincrement = $lstart;}
         # going up
         if ($lstart < $peak) {
            $steps = ($lend - $lstart) / $lincrement;
            $total = 0;
            for ($i=1;$i <= $steps;$i++) {
               $total += $i * $lincrement + $lstart;
            }
            $total += $lstart;
         } else {
            $steps = ($lstart - $lend) / $lincrement;
            $total = 0;
            for ($i=1;$i <= $steps;$i++) {
               $total += $i * $lincrement;
            }
            $total += $lend;
         }
         # going down
         if ($lend > $peak) {
            $steps = ($lend - $peak) / $lincrement;
            $total = 0;
            for ($i=1;$i <= $steps;$i++) {
               $total += $i * $lincrement + $peak;
            }
         } else {
            $steps = ($peak - $lend) / $lincrement;
            $total = 0;
            for ($i=1;$i <= $steps;$i++) {
               $total += $i * $lincrement;
            }
         }
         $total += $peak;
      }
      $cval = "$lstart;$peak;$lend;$lincrement;$interval";
      break;

      case 5:
      # Comment String
      $cval = '';
      $total = 0;
      break;

      case 6:
      # Drylands
      $cval = '';
      $total = 0;
      break;

      case 7:
      # set syntax type
      list($thisdist, $setobject) = parseBlockSetString($descr);
      $total = $thisdist;
      print("Set Yards = $total <br>");;
      break;
   }

   # until we have time integrated, this will have to do
   $totaltime = 0;

   # check that parameters are all intiialized
   if (strlen($totaltime) == 0) {$totaltime = 0;}
   if (strlen($energysystem) == 0) {$energysystem = 0;}
   if (strlen($setorder) == 0) {$setorder = 0;}
   if (strlen($settype) == 0) {$settype = 0;}
   if (strlen($subgroup) == 0) {$subgroup = 0;}
   if (strlen($total) == 0) {$total = 0;}
   if (strlen($intervaltype) == 0) {$intervaltype = 1;}

   $setupdatesql = "update sets set setparams = '$cval', timeseconds = $totaltime, energysystem = $energysystem, descr = '$descr', setorder = $setorder, settype = $settype, intervaltype = $intervaltype, subgroup = $subgroup, distance = $total, makeresultfile = $makeresultfile, numresults = $numresults where setid = $recid";
#   print("$setupdatesql <br>");
   $updatequery = mysql_query($setupdatesql);

} /* end function editSet() */

function getWeeklyTotal($db,$dbname,$weekending, $intervaldays,$groups, $uid) {

   mysql_select_db($dbname,$db);

   $totalsql = "select MAINGROUP, sum(distance) as totaldistance from ";
   if (strlen($groups)) {
      $totalsql .= "workout where (MAINGROUP in ($groups) or owner = $uid)";
   } else {
      $totalsql .= "workout where owner = $uid ";
   }
   $totalsql .= "and workoutdate <= '$weekending' ";
   $totalsql .= "and workoutdate >= DATE_SUB('$weekending', INTERVAL $intervaldays DAY)";
   $totalsql .= " group by MAINGROUP ";
   $totalquery = mysql_query($totalsql);

 #  print("$totalsql<br>");
   $weekarr = array();

   while ( $totalarr = mysql_fetch_array($totalquery) ) {
#print_r($totalarr);
      $weeklytotal = $totalarr['totaldistance'];
      if ($weeklytotal == '') { $weeklytotal = 0; }
      $groupid = $totalarr['MAINGROUP'];
#      if ($groupid == '') { $groupid = 0; }
      array_push($weekarr, array("groupid" => $groupid, "total" => $weeklytotal));
  }


   return $weekarr;

} /* end function getWeeklyTotal */


function getSwimmerWeeklyTotal($listobject, $requestor, $weekending, $intervaldays, $groups, $uids, $uclass, $numtoget, $course=1) {

   # $requestor = the uid of the user calling this function

   $listobject->querystring = "set @rank := 0 ";
   $listobject->performQuery();

   $totalsql = "select (@rank := @rank +1) as rank, ";
   $totalsql .= "    CASE  ";
   $totalsql .= "       WHEN a.stats_perms = 2 THEN concat(substring(a.fname,1, 1), substring(a.lname,1, 1))";
   $totalsql .= "       WHEN a.stats_perms = 3 THEN concat(a.fname, ' ',a.lname) ";
   $totalsql .= "       WHEN a.stats_perms = 4 THEN a.username ";
   $totalsql .= "    END as name, ";
   $totalsql .= "    d.abbrev, sum(b.distance*f.factor) as totaldistance ";
   $totalsql .= " from users as a, workout as b, mapattendance as c, ";
   $totalsql .= "    team as d, users as e, conversions as f ";
   $totalsql .= " WHERE e.userid = $requestor ";
   $totalsql .= "    and a.username <> 'guest' ";
   $totalsql .= "    and ( (a.stats_perms > 1) or (e.teamid = a.teamid) ) ";
   if (strlen($groups) and ($groups <> '-1') ) {
      $totalsql .= " and a.MAINGROUP in ($groups) ";
   }
   if (strlen($uids) and ($uids <> '-1') ) {
      $totalsql .= " and a.userid in ( $uids ) ";
   }
   if (strlen($uclass) and ($uclass <> '-1') ) {
      $totalsql .= " and a.swimmerclass in ( $uclass ) ";
   }
   $totalsql .= "and b.workoutid = c.workoutid ";
   $totalsql .= "and a.teamid = d.teamid ";
   $totalsql .= "and a.userid = c.athleteid ";
   $totalsql .= "and b.workoutdate <= '$weekending' ";
   $totalsql .= "and b.workoutdate >= DATE_SUB('$weekending', INTERVAL $intervaldays DAY)";
   $totalsql .= "    and b.course = f.srcid ";
   $totalsql .= "    and f.destid = $course ";
   $totalsql .= " group by a.fname, a.lname, d.abbrev ";
   $totalsql .= " order by totaldistance DESC ";
   if ($numtoget > 0) {
      $totalsql .= " LIMIT $numtoget ";
   }

   #print("$totalsql<br>");
#   if ($requestor == 1) { print("$totalsql<br>"); }
   $listobject->querystring = $totalsql;
   $listobject->performQuery();

   $results = $listobject->queryrecords;

   return $results;

} /* end function getSwimmerWeeklyTotal */



function getTeamCoaches($teamid) {
   $csql = "select userid from users where teamid = $teamid";
   $cq = mysql_query($csql);
   $cdel = '';
   $clist = '';
   while ($thiscoach = mysql_fetch_array($cq)) {
      $cid = $thiscoach['userid'];
      $clist .= "$cdel $cid";
      $cdel = ',';
   }

   return $clist;
}

function getUserGroups($teamid, $userid) {
   $csql = "select groupid from maingroup where teamid = $teamid";
   $csql .= " or ownerid = $userid";
   $cq = mysql_query($csql);
   $cdel = '';
   $clist = '';
   while ($thisgroup = mysql_fetch_array($cq)) {
      $cid = $thisgroup['groupid'];
      $clist .= "$cdel $cid";
      $cdel = ',';
   }

   return $clist;
}



function showWorkoutSets($listobject, $uid, $usergroup, $currentworkoutid, $viewmode) {
   # set up interface rows and columns
   $perms = getWorkoutPerms($listobject, $currentworkoutid, $uid, $usergroup);

   # until full migration to set mapping, keep this:
   $ordersql = "select a.* ";
   $ordersql .= " from sets as a, workout as b, mapworkoutsets as c ";
   $ordersql .= " where a.setid = c.setid ";
   $ordersql .= "    and c.workoutid = b.workoutid";
   $ordersql .= "    and c.workoutid = $currentworkoutid";
   $ordersql .= " order by a.setorder";
   $listobject->querystring = $ordersql;
   #  print("$listobject->querystring <br>");
   $listobject->performQuery();
   $setlist = $listobject->queryrecords;

   print("<table width=100% class='workout'>");

   foreach ($setlist as $thisset) {
      $thisid = $thisset['SETID'];

      $workoutprops = showSet($listobject->dbconn, $listobject->dbname, $thisset, $printable, $setnum, $currentworkoutid, 1, $perms, 2, 2, 0);
      $workoutdistance += $workoutprops['distance'];
      $workouttime += $workoutprops['totaltime'];
      $descr = $workoutprops['formatted'];
      $sn = $workoutprops['setnumber'];
      print("<tr><td valign=top><b>Set #$sn:<br></b></td><td valign=top><div id='set$thisid'>$descr</div></td>");
      print("\n</tr>\n");
      $setnum++;
   }

   # if user has write privileges update the workout distance
   if ($perms & 2) {
      $distancesql = "update workout set distance = $workoutdistance where workoutid = $currentworkoutid";
      $distancequery = mysql_query($distancesql);
   }
   print("<input type=hidden name='SETS'>");

   print("</table>");
   $tft = secondsToTime($workouttime, 2);
   print("<br><b>Total:</b> $workoutdistance $workoutunits ($tft)");
}


function showWorkoutSetsAjax($listobject, $uid, $usergroup, $currentworkoutid, $viewmode) {
   # set up interface rows and columns
   $perms = getWorkoutPerms($listobject, $currentworkoutid, $uid, $usergroup);
   
   # until full migration to set mapping, keep this:
   $ordersql = "select a.* ";
   $ordersql .= " from sets as a, workout as b, mapworkoutsets as c ";
   $ordersql .= " where a.setid = c.setid ";
   $ordersql .= "    and c.workoutid = b.workoutid";
   $ordersql .= "    and c.workoutid = $currentworkoutid";
   $ordersql .= " order by a.setorder";
   $listobject->querystring = $ordersql;
   #  print("$listobject->querystring <br>");
   $listobject->performQuery();
   $setlist = $listobject->queryrecords;

   print("<table width=100% class='workout'>");

   foreach ($setlist as $thisset) {
      $thisid = $thisset['SETID'];

      $workoutprops = showSet($listobject->dbconn, $listobject->dbname, $thisset, $printable, $setnum, $currentworkoutid, 1, $perms, 2, 2, 0);
      $workoutdistance += $workoutprops['distance'];
      $workouttime += $workoutprops['totaltime'];
      $descr = $workoutprops['formatted'];
      $src = $workoutprops['descr'];
      $sn = $workoutprops['setnumber'];
      print("<tr><td valign=top><b>Set #$sn:</b>");
      print("<a id=\"setbutton$thisid\" style=\"text-decoration: underline ; color: red\" onclick=\"showHide(['setedit$thisid'], ['set$thisid'])\">Edit</a>");
      print("</td><td valign=top><div id='set$thisid' style=\" display: ''\">$descr</div>");
      print("<div id='setedit$thisid' style=\" display: none\">");
      print("<textarea name=\"settext$thisid\" rows=\"4\" cols=\"50\" wrap=\"virtual\"> $src</textarea>");
      print("<br><a id=\"saveset$thisid\" style=\"text-decoration: underline ; color: red\" class=\"\" onclick=\"showHide(['set$thisid'],['setedit$thisid'])\">Save</a>");
      print("</div></td>");
      print("\n</tr>\n");
      $setnum++;
   }

   # if user has write privileges update the workout distance
   if ($perms & 2) {
      $distancesql = "update workout set distance = $workoutdistance where workoutid = $currentworkoutid";
      $distancequery = mysql_query($distancesql);
   }
   print("<input type=hidden name='SETS'>");

   print("</table>");
   $tft = secondsToTime($workouttime, 2);
   print("<br><b>Total:</b> $workoutdistance $workoutunits ($tft)");
}

function showWorkoutSetsSOAP($listobject, $uid, $usergroup, $currentworkoutid, $viewmode) {
   # set up interface rows and columns
   $perms = getWorkoutPerms($listobject, $currentworkoutid, $uid, $usergroup);
   # until full migration to set mapping, keep this:
   $ordersql = "select a.* ";
   $ordersql .= " from sets as a, workout as b, mapworkoutsets as c ";
   $ordersql .= " where a.setid = c.setid ";
   $ordersql .= "    and c.workoutid = b.workoutid";
   $ordersql .= "    and c.workoutid = $currentworkoutid";
   $ordersql .= " order by a.setorder";
   $listobject->querystring = $ordersql;
   #  print("$listobject->querystring <br>");
   $listobject->performQuery();
   $setlist = $listobject->queryrecords;

   print("<table width=100% class='workout'>");

   foreach ($setlist as $thisset) {
      $thisid = $thisset['SETID'];
      $workoutprops = showSet($listobject->dbconn, $listobject->dbname, $thisset, $printable, $setnum, $currentworkoutid, 1, $perms, 2, 2, 0);
      $workoutdistance += $workoutprops['distance'];
      $workouttime += $workoutprops['totaltime'];
      $descr = $workoutprops['formatted'];
      $sn = $workoutprops['setnumber'];
      print("<tr><td valign=top><b>Set #$sn:<br><a href='' id='editset$thisid'>Edit</a></b></td><td valign=top><div id='set$thisid'>$descr</div></td>");
      print("\n</tr>\n");
      $setnum++;
   }

   # if user has write privileges update the workout distance
   if ($perms & 2) {
      $distancesql = "update workout set distance = $workoutdistance where workoutid = $currentworkoutid";
      $distancequery = mysql_query($distancesql);
   }
   print("<input type=hidden name='SETS'>");

   print("</table>");
   $tft = secondsToTime($workouttime, 2);
   print("<br><b>Total:</b> $workoutdistance $workoutunits ($tft)");
}

?>
