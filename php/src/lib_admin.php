<?php


# user management

function addUser($listobject, $username, $firstname, $lastname, $userpass, $usertype, $groupid, $defaultproject, $newuseremail, $debug) {

   $listobject->querystring = " insert into users (username, firstname, lastname, userpass, usertype, defaultproject, email) ";
   $listobject->querystring .= " values ('$username', '$firstname', '$lastname', '$userpass', $usertype, $defaultproject, '$newuseremail') ";
   if ($debug) { print("$listobject->querystring ; "); }
   $listobject->performQuery();

   $listobject->querystring = " select userid from users where username = '$username' ";
   if ($debug) { print("$listobject->querystring ; "); }
   $listobject->performQuery();
   $userid = $listobject->getRecordValue(1,'userid');

   if ($userid > 0) {
      $listobject->querystring = " insert into mapusergroups (userid, groupid) ";
      $listobject->querystring .= " values ($userid, $groupid) ";
      if ($debug) { print("$listobject->querystring ; "); }
      $listobject->performQuery();
   }

   return $userid;

}

function copyGroups($listobject, $projectid, $srcuserid, $destuserid, $gids, $debug) {

   $listobject->querystring = " insert into proj_seggroups (projectid, the_geom, area, groupname, ownerid) ";
   $listobject->querystring .= " select projectid, the_geom, area, groupname, $destuserid ";
   $listobject->querystring .= " from proj_seggroups ";
   $listobject->querystring .= " where ownerid = $srcuserid ";
   $listobject->querystring .= "    and projectid = $projectid ";
   $listobject->querystring .= "    and ( (gid in ($gids)) ";
   if (count(split(',', $gids)) <= 1) {
      $listobject->querystring .= "       or ($gids = -1) ";
   }
   $listobject->querystring .= "    ) ";
   if ($debug) { print("$listobject->querystring ; "); }
   $listobject->performQuery();

}

function createDomain($listobject, $projectid, $userid, $scenarioname, $shortname, $groupid, $operms, $pperms, $gperms, $debug, $silent = 0) {
   # check permissions
   $retvals = array();
   $retvals['error'] = 0;
   $retvals['error_msg'] = '';
   $retvals['debug'] = '';
   $retvals['message'] = '';

   $listobject->querystring = "  select count(*) as numdups ";
   $listobject->querystring .= " from scenario ";
   $listobject->querystring .= " where scenario = '$scenarioname' ";
   $listobject->querystring .= "    and projectid = $projectid ";
   if ($debug) {
      $retvals['debug'] .= "DEBUG: $listobject->querystring ; <br>";
   }
   $listobject->performQuery();
   $numdups = $listobject->getRecordValue(1,'numdups');
   if ($numdups > 0) {
      $retvals['error'] = 1;
      $retvals['error_msg'] .= "<b>Error:</b> A domain already exists with the name $scenarioname .  Please choose another name.<br>";
   } else {
      $listobject->querystring = "  insert into scenario (projectid, ownerid, scenario, ";
      $listobject->querystring .= "    shortname, groupid, operms, gperms, pperms) ";
      $listobject->querystring .= " values ($projectid, $userid, '$scenarioname', ";
      $listobject->querystring .= "    '$shortname', $groupid, $operms, $gperms, $pperms) ";
      if ($debug) {
         $retvals['debug'] .= "DEBUG: $listobject->querystring ; <br>";
      }
      $listobject->performQuery();

      $listobject->querystring = "  select scenarioid, count(*) as numrecs ";
      $listobject->querystring .= " from scenario ";
      $listobject->querystring .= " where scenario = '$scenarioname' ";
      $listobject->querystring .= "    and projectid = $projectid ";
      $listobject->querystring .= " group by scenarioid ";
      if ($debug) {
         $retvals['debug'] .= "DEBUG: $listobject->querystring ; <br>";
      }
      $listobject->performQuery();
      $scenarioid = $listobject->getRecordValue(1,'scenarioid');
      $numrecs = $listobject->getRecordValue(1,'numrecs');
      if (!($numrecs > 0)) {
         $retvals['error'] = 1;
         $retvals['error_msg'] .= "<b>Error:</b> Domain Creation Failed. <br>";
      } else {
         $retvals['message'] .= "<b>Notice</b> Domain $scenarioname created successfully.<br>";
      }

   }

   return $retvals;

}


function showViewableScenarioList($listobject, $projectid, $scenarioid, $userid, $usergroupids, $fieldname, $extrawhere, $onchange, $debug, $silent=0) {

   $scenclause = "projectid = $projectid and ( (ownerid = $userid  and operms >= 4) ";
   $scenclause .= " or ( groupid in ($usergroupids) and gperms >= 4 ) ";
   $scenclause .= " or (pperms >= 4) ) ";
   if (strlen(ltrim(rtrim($extrawhere))) > 0 ) {
      $scenclause .= " and $extrawhere ";
   }
   #$debug = 1;
   #showList($listobject, $fieldname, 'scenario', 'scenario', 'scenarioid', $scenclause, $scenarioid, $debug);
   # change to reference activeList, which means that when the selection is changed, an action is performed
   $innerHTML = showActiveList($listobject, $fieldname, 'scenario', 'scenario', 'scenarioid', $scenclause, $scenarioid, $onchange, 'scenario', $debug, 1);

   if (!$silent) {
      print $innerHTML;
   } else {
      return $innerHTML;
   }
}


function getScenarioPerms($listobject, $scenarioid, $userid, $usergroupids, $debug) {

   $listobject->querystring = "  select groupid, ownerid, gperms, operms, pperms ";
   $listobject->querystring .= " from scenario ";
   $listobject->querystring .= " where scenarioid = $scenarioid ";
   if ($debug) {
      print("DEBUG: $listobject->querystring ; <br>");
   }
   $listobject->performQuery();
   $srow = $listobject->queryrecords[0];

   # check permissions
   $ownerid = $srow["ownerid"];
   $usergroup = $srow["groupid"];
   $groupperms = $srow["gperms"];
   $ownerperms = $srow["operms"];
   $publicperms = $srow["pperms"];

   # Perm Sub-routine
   $perms = 0;
   if ($userid == $ownerid) {
      # owner perms
      $perms = $perms | $ownerperms;
   }
   $ugs = preg_split("[\,]", $usergroupids);
   if (in_array($usergroup, $ugs) ) {
      $perms = $perms | $groupperms;
   }
   $perms = $perms | $publicperms;

  # print_r($srow);

   return $perms;
}


function getProjElementPerms($listobject, $projectid, $elementid, $userid, $usergroupids, $debug) {

   $listobject->querystring = "  select groupid, ownerid, gperms, operms, pperms ";
   $listobject->querystring .= " from proj_element ";
   $listobject->querystring .= " where projectid = $projectid ";
   $listobject->querystring .= "    and elementid = $elementid ";
   #return $listobject->querystring;
   #if ($debug) {
   #   print("DEBUG: $listobject->querystring ; <br>");
   #}
   $listobject->performQuery();
   $srow = $listobject->queryrecords[0];

   # check permissions
   $ownerid = $srow["ownerid"];
   $usergroup = $srow["groupid"];
   $groupperms = $srow["gperms"];
   $ownerperms = $srow["operms"];
   $publicperms = $srow["pperms"];

   # Perm Sub-routine
   $perms = 0;
   if ($userid == $ownerid) {
      # owner perms
      $perms = $perms | $ownerperms;
   }
   if (in_array($usergroup, split(',', $usergroupids)) ) {
      $perms = $perms | $groupperms;
   }
   $perms = $perms | $publicperms;

  # print_r($srow);

   return $perms;
}

function getScenElementPerms($listobject, $elementid, $userid, $usergroupids, $debug) {

   $listobject->querystring = "  select groupid, ownerid, gperms, operms, pperms ";
   $listobject->querystring .= " from scen_model_element ";
   $listobject->querystring .= " where elementid = $elementid ";
   if ($debug) {
      error_log("DEBUG: $listobject->querystring ; <br>");
   }
   #return $listobject->querystring;
   $listobject->performQuery();
   $srow = $listobject->queryrecords[0];
   #error_log("DEBUG: Perms : " . print_r($srow,1));

   # check permissions
   $ownerid = $srow["ownerid"];
   $usergroup = $srow["groupid"];
   $groupperms = $srow["gperms"];
   $ownerperms = $srow["operms"];
   $publicperms = $srow["pperms"];

   # Perm Sub-routine
   $perms = 0;
   if ($userid == $ownerid) {
      # owner perms
      $perms = $perms | $ownerperms;
   }
   #return "User id $userid, Owner Perms = $perms";
   #error_log("User id $userid, Owner Perms = $perms");

   if (in_array($usergroup, explode(',', $usergroupids)) ) {
      $perms = $perms | $groupperms;
      #error_log("Group id $usergroup, Group Perms = $groupperms");
   }
   $perms = $perms | $publicperms;

   #error_log("Public Perms = $publicperms, Final Perms: $perms");

  # print_r($srow);

   return $perms;
}

/*
function createProject($userid, $projname, $basinfile = '', $groupfile = '', $pointfile = '') {
   # check the users group, can they create a new project?
   # add entry to project table
   # create a copy of the proj_mapN.map for the new project

}
*/

?>
