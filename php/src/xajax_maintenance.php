<?php

# xajax based library - watersupply

require("xajax_maintenance.common.php");
include_once("./lib_admin.php");

$xajax->processRequest();

function showUserAddForm($formValues) {
   $objResponse = new xajaxResponse();
   $controlHTML = userAddForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   return $objResponse;
}

function showLogins($formValues) {
   $objResponse = new xajaxResponse();
   $controlHTML = doShowLogins($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   return $objResponse;
}

function showChangePasswordForm($formValues) {
   $objResponse = new xajaxResponse();
   $controlHTML = changePasswordForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   return $objResponse;
}

function showUserAddResult($formValues) {
   $objResponse = new xajaxResponse();
   $controlHTML = userAddForm($formValues);
   $innerHTML = userAddResult($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   $objResponse->assign("workspace","innerHTML",$innerHTML);
   return $objResponse;
}

function showCreateDomainForm($formValues) {
   $objResponse = new xajaxResponse();
   $controlHTML = createDomainForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   return $objResponse;
}
function showGroupCopyForm($formValues) {
   $objResponse = new xajaxResponse();
   $controlHTML = groupCopyForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   return $objResponse;
}

function showGroupCopyResult($formValues) {
   $objResponse = new xajaxResponse();
   $controlHTML = groupCopyForm($formValues);
   $innerHTML = groupCopyResult($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   $objResponse->assign("workspace","innerHTML",$innerHTML);
   return $objResponse;
}

function showCreateUserGroupForm($formValues) {
   $objResponse = new xajaxResponse();
   $controlHTML = createUserGroupForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   return $objResponse;
}

function showCreateUserGroupResult($formValues) {
   $objResponse = new xajaxResponse();
   $controlHTML = createUserGroupResult($formValues);
   $controlHTML .= createUserGroupForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   return $objResponse;
}

function showEditUserGroupForm($formValues) {
   $objResponse = new xajaxResponse();
   $controlHTML = editUserGroupForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   return $objResponse;
}

function showEditUserGroupResult($formValues) {
   $objResponse = new xajaxResponse();
   $controlHTML = editUserGroupResult($formValues);
   $controlHTML .= editUserGroupForm($formValues);
   $objResponse->assign("controlpanel","innerHTML",$controlHTML);
   return $objResponse;
}


function createUserGroupForm($formValues) {
   global $usertype, $projectid, $userid, $listobject;

   $controlHTML = '';

   if (isset($formValues['newgroupname']) ) {
      $newgroupname = $formValues['newgroupname'];
   }

   $controlHTML .= "<form id=control name=control>";

   $controlHTML .= "<br><b>Group Name: </b>";
   $controlHTML .= showWidthTextField('newgroupname', $newgroupname, 64, '', 1);
   $controlHTML .= "<br><b>Users to Include in this group: </b><BR>";
   $gids = join(',', $formValues['groupusers'][0]);
   $controlHTML .= showMultiList2($listobject, 'groupusers', 'users', 'userid', 'username', $gids, '', 'username', 1, 6, 1);
   $controlHTML .= "<br>" . showGenericButton('creategroup','Create Group', "xajax_showCreateUserGroupResult(xajax.getFormValues(\"control\"))", 1);

   $controlHTML .= "</form>";

   return $controlHTML;

}

function createUserGroupResult($formValues) {
   global $usertype, $projectid, $userid, $listobject;

   $controlHTML = '';

   if (isset($formValues['newgroupname']) ) {
      $newgroupname = ltrim(rtrim($formValues['newgroupname']));
      $groupusers = $formValues['groupusers'][0];
   } else {
      return "<b>Error:</b> No information submitted.";
   }

   if (strlen($newgroupname) == 0) {
      return "<b>Error:</b> Group Name must be non-blank.";
   }

   $listobject->querystring = " select count(*) as numdups from groups where groupname = '$newgroupname'";
   $listobject->performQuery();
   $numdups = $listobject->getRecordValue(1,'numdups');
   if ($numdups > 0) {
      return "<b>Error:</b> A group already exists with the name '$newgroupname'.  Please choose another name.";
   }
   $listobject->querystring = " insert into groups (ownerid, groupname) values ($userid, '$newgroupname')";
   if ($debug) {
      $controlHTML .= $listobject->querystring . " ;<br>";
   }
   $listobject->performQuery();

   $listobject->querystring = " select groupid from groups where groupname = '$newgroupname'";
   if ($debug) {
      $controlHTML .= $listobject->querystring . " ;<br>";
   }
   $listobject->performQuery();
   $groupid = $listobject->getRecordValue(1,'groupid');
   if ($groupid > 0) {
      $controlHTML .= "Group added successfully.<br>";
   } else {
      return "<b>Error:</b> There was a problem adding this group. Contact the system administrator if this problem persists.";
   }

   foreach ($groupusers as $thisuser) {
      $listobject->querystring = " insert into mapusergroups (groupid, userid) values ($groupid, $thisuser)";
      if ($debug) {
         $controlHTML .= $listobject->querystring . " ;<br>";
      }
      $listobject->performQuery();
   }

   if (!in_array($groupusers, $userid)) {
      $listobject->querystring = " insert into mapusergroups (groupid, userid) values ($groupid, $userid)";
      if ($debug) {
         $controlHTML .= $listobject->querystring . " ;<br>";
      }
      $listobject->performQuery();
   }

   return $controlHTML;

}

function editUserGroupForm($formValues) {
   global $usertype, $projectid, $userid, $listobject;

   $controlHTML = '';

   if (isset($formValues['modifygroup']) ) {
      $modifygroup = $formValues['modifygroup'];
      $groupid = $formValues['groupid'];
      $actiontype = $formValues['actiontype'];
      $disabled = 0;
      if ($modifygroup == 1) {
         $newgroupname = $formValues['newgroupname'];
         $gids = join(',', $formValues['groupusers'][0]);
         $listobject->performQuery();
         if ( ($actiontype == 'delete') and (count($listobject->queryrecords) == 0) ){
            $groupid = '';
            $disabled = 1;
         }
      } else {
         $listobject->querystring = " select * from groups where groupid = $groupid ";
         if ($debug) {
            $controlHTML .= $listobject->querystring . " ;<br>";
         }
         $listobject->performQuery();
         $newgroupname = $listobject->getRecordValue(1,'groupname');
         $listobject->querystring = " select userid from mapusergroups where groupid = $groupid ";
         if ($debug) {
            $controlHTML .= $listobject->querystring . " ;<br>";
         }
         $listobject->performQuery();
         $theserecs = $listobject->queryrecords;
         $gids = '';
         $gdel = '';
         foreach($theserecs as $thisrec) {
            $gids .= $gdel . $thisrec['userid'];
            $gdel = ',';
         }
      }
   } else {
      $disabled = 1; # grey out form fields, since we have no selected group
   }

   $controlHTML .= "<form id=control name=control>";

   $groupsql = " ( select * from groups where ownerid = $userid ) as foo ";

   $controlHTML .= showActiveList($listobject, 'groupid', $groupsql, 'groupname', 'groupid', '', $groupid, "document.forms[\"control\"].elements.modifygroup.value=0;  xajax_showEditUserGroupForm(xajax.getFormValues(\"control\"))", '', $debug, 1, 0);

   $controlHTML .= "<br><b>Group Name: </b>";
   $controlHTML .= showWidthTextField('newgroupname', $newgroupname, 64, '', 1, $disabled);
   $controlHTML .= showHiddenField('modifygroup', $modifygroup, 1);
   $controlHTML .= showHiddenField('actiontype', 'save', 1);
   $controlHTML .= "<br><b>Users to Include in this group: </b><BR>";
   $controlHTML .= showMultiList2($listobject, 'groupusers', 'users', 'userid', 'username', $gids, '', 'username', $debug, 6, 1, $disabled);
   $controlHTML .= "<br>" . showGenericButton('editgroup','Save Group', "document.forms[\"control\"].elements.modifygroup.value=1; xajax_showEditUserGroupResult(xajax.getFormValues(\"control\"))", 1, $disabled);
   $controlHTML .= " | " . showGenericButton('deletegroup','Delete Group', "document.forms[\"control\"].elements.modifygroup.value=1; document.forms[\"control\"].elements.actiontype.value=\"delete\"; xajax_showEditUserGroupResult(xajax.getFormValues(\"control\"))", 1, $disabled);

   $controlHTML .= "</form>";

   return $controlHTML;

}


function editUserGroupResult($formValues) {
   global $usertype, $projectid, $userid, $listobject;

   $controlHTML = '';

   if (isset($formValues['modifygroup']) ) {
      $modifygroup = $formValues['modifygroup'];
      $actiontype = $formValues['actiontype'];
      $groupid = $formValues['groupid'];
      if ($modifygroup == 1) {
         $newgroupname = $formValues['newgroupname'];
         $groupusers = $formValues['groupusers'][0];
      } else {
         return '';
      }
   }

   if (strlen($newgroupname) == 0) {
      return "<b>Error:</b> Group Name must be non-blank.";
   }

   $controlHTML .= "Action requested: $actiontype <br>";

   $listobject->querystring = " select count(*) as numdups from groups where groupname = '$newgroupname' and groupid <> $groupid";
   $listobject->performQuery();
   $numdups = $listobject->getRecordValue(1,'numdups');
   if ($numdups > 0) {
      return "<b>Error:</b> A group already exists with the name '$newgroupname'.  Please choose another name.";
   }

   if ($actiontype <> 'delete') {
      $controlHTML .= "Saving Changes to group $newgroupname <br>";
      $listobject->querystring = " update groups set groupname = '$newgroupname' where groupid = $groupid ";
      if ($debug) {
         $controlHTML .= $listobject->querystring . " ;<br>";
      }
      $listobject->performQuery();

      $listobject->querystring = " delete from mapusergroups where groupid = $groupid ";
      if ($debug) {
         $controlHTML .= $listobject->querystring . " ;<br>";
      }
      $listobject->performQuery();

      if (!in_array($groupusers, $userid)) {
         # make sure user does not try to delete their own base group
         $listobject->querystring = " select count(*) as numbase from users where userid = $userid and groupid = $groupid";
         $listobject->performQuery();
         $numbase = $listobject->getRecordValue(1,'numbase');
         if ($numbase > 0) {
            # just add this user back into the base group
            array_push($groupusers, $userid);
         }
      }

      foreach ($groupusers as $thisuser) {
         $listobject->querystring = " insert into mapusergroups (groupid, userid) values ($groupid, $thisuser)";
         if ($debug) {
            $controlHTML .= $listobject->querystring . " ;<br>";
         }
         $listobject->performQuery();
      }
   } else {
      # make sure user does not try to delete their own base group
      $listobject->querystring = " select count(*) as numbase from users where userid = $userid and groupid = $groupid";
      $listobject->performQuery();
      $numdups = $listobject->getRecordValue(1,'numbase');
      if ($numdups > 0) {
         return "<b>Error:</b> You cannot remove your personal group. ";
      }

      $controlHTML .= "Deleting group $newgroupname <br>";
      $listobject->querystring = " delete from groups where groupid = $groupid ";
      if ($debug) {
         $controlHTML .= $listobject->querystring . " ;<br>";
      }
      $listobject->performQuery();

      $listobject->querystring = " delete from mapusergroups where groupid = $groupid ";
      if ($debug) {
         $controlHTML .= $listobject->querystring . " ;<br>";
      }
      $listobject->performQuery();
   }

   return $controlHTML;

}

function groupCopyForm($formValues) {
   global $usertype, $projectid, $userid, $listobject;

   $controlHTML = '';

   if (isset($formValues['copyuser']) ) {
      $copyuser = $formValues['copyuser'];
      $projectid = $formValues['projectid'];
      $currentgroup = $formValues['currentgroup'];
      $lreditlist = $formValues['lreditlist'];
   }

   $controlHTML .= "<form id=control name=control>";


   if ($usertype == 1) {

      $controlHTML .= "<b>Groups to Copy: </b><BR>";
      $gids = join(',', $formValues['copygroups'][0]);
      $controlHTML .= showMultiList2($listobject, 'copygroups', 'proj_seggroups', 'gid', 'groupname', $gids, "projectid = $projectid and ownerid = $userid", 'groupname', 1, 6, 1);
      $controlHTML .= "<b>Users to copy to: </b><BR>";
      $controlHTML .= showList($listobject, 'copyuser', 'users', 'lastname,firstname', 'userid', "userid <> $userid", $copyuser, $debug, 1);
      $controlHTML .= showGenericButton('showlogins','Copy Groups', "xajax_showGroupCopyResult(xajax.getFormValues(\"control\"))", 1);
      $controlHTML .= showHiddenField('projectid', $projectid, 1);
      $controlHTML .= showHiddenField('currentgroup', $currentgroup, 1);
      $controlHTML .= showHiddenField('lreditlist', $lreditlist, 1);

   }
   $controlHTML .= "</form>";

   return $controlHTML;

}

function groupCopyResult($formValues) {
   global $usertype, $projectid, $userid, $listobject;

   $innerHTML = '';

   if (isset($formValues['copyuser'])) {
      $copyuser = $formValues['copyuser'];
      if (isset($formValues['copygroups'])) {
         $gids = join(',', $formValues['copygroups'][0]);
         copyGroups($listobject, $projectid, $userid, $copyuser, $gids, $debug, 1);
         $innerHTML .= "Groups $gids copied to $userid.<br>";
      }
   }

   return $innerHTML;
}

function userAddForm($formValues){

   global $listobject;
   $controlHTML = '';

   #$controlHTML = "Test User Form";

   #return $controlHTML;

   if (isset($formValues['newusername']) ) {
      $newusername = $formValues['newusername'];
      $newfirstname = $formValues['newfirstname'];
      $newlastname = $formValues['newlastname'];
      $newuserpass = $formValues['newuserpass'];
      $newusertype = $formValues['newusertype'];
      $newgroupid = $formValues['newgroupid'];
      $defaultproject = $formValues['defaultproject'];
      $newuseremail = $formValues['newuseremail'];
   }

   $controlHTML .= "<form id=control name=control>";
   $controlHTML .= "<b>New User Information: </b>";
   $controlHTML .= "<br><b>First Name: </b>";
   $controlHTML .= showWidthTextField('newfirstname', $newfirstname, 10, '', 1);
   $controlHTML .= "<br><b>Last Name: </b>";
   $controlHTML .= showWidthTextField('newlastname', $newlastname, 10, '', 1);
   $controlHTML .= "<br><b>User Name: </b>";
   $controlHTML .= showWidthTextField('newusername', $newusername, 10, '', 1);
   $controlHTML .= "<br><b>Email: </b>";
   $controlHTML .= showWidthTextField('newuseremail', $newuseremail, 10, '', 1);
   $controlHTML .= "<br><b>Password: </b>";
   $controlHTML .= showWidthTextField('newuserpass', $newuserpass, 10, '', 1);
   $controlHTML .= "<br><b>Default Group: </b>";
   $controlHTML .= showList($listobject, 'newgroupid', 'groups', 'groupname', 'groupid', "", $newgroupid, $debug, 1);
   $controlHTML .= "<br><b>Default Project: </b>";
   $controlHTML .= showList($listobject, 'defaultproject', 'project', 'projectname', 'projectid', "", $defaultproject, $debug, 1);
   $controlHTML .= "<br><b>User Type: </b>";
   $controlHTML .= showList($listobject, 'newusertype', 'usertype', 'typename', 'typeid', "", $newusertype, $debug, 1);
   $controlHTML .= "<br>";

   $controlHTML .= showGenericButton('adduser', 'Add This User', "xajax_showUserAddResult(xajax.getFormValues(\"control\"))", 1);
   $controlHTML .= "</form>";

   return $controlHTML;
}

function userAddResult($formValues){

   global $listobject, $debug, $baseurl, $adminemail;
   $innerHTML = '';

   if (isset($formValues['newusername']) ) {
      $newusername = $formValues['newusername'];
      $newfirstname = $formValues['newfirstname'];
      $newlastname = $formValues['newlastname'];
      $newuserpass = $formValues['newuserpass'];
      $newusertype = $formValues['newusertype'];
      $newgroupid = $formValues['newgroupid'];
      $defaultproject = $formValues['defaultproject'];
      $newuseremail = $formValues['newuseremail'];
   } else {
      $innerHTML .= "<b>Error: </b> You must supply a user name.<br>";
      return $innerHTML;
   }
   $newuserid = addUser($listobject, $newusername, $newfirstname, $newlastname, $newuserpass, $newusertype, $newgroupid, $defaultproject, $newuseremail, $debug, 1);

   if ($newuserid > 0) {
      # set-up user directories
      $userin = $indir . '/users/user' . $newuserid;
      $userout = $outdir . '/users/user' . $newuserid;
      mkdir("$userin", 0755);
      mkdir("$userout", 0755);
      $listobject->querystring = "  update users set indir = 'user$newuserid' ";
      $listobject->querystring .= " where userid = $newuserid ";
      $listobject->performQuery();

      # copy default set of groups
      copyGroups($listobject, $defaultproject, -1, $newuserid, -1, $debug, 1);
      $listobject->querystring = "  select username, firstname, lastname, userpass ";
      $listobject->querystring .= " from users ";
      $listobject->querystring .= " where userid = $newuserid ";
      $listobject->performQuery();

      $recs = $listobject->queryrecords;

      foreach ($recs as $thisrec) {
         $un = $thisrec['username'];
         $fn = $thisrec['firstname'];
         $ln = $thisrec['lastname'];
         $up = $thisrec['userpass'];

         $innerHTML .= "<hr><br>$fn $ln, <br>";
         $innerHTML .= "Welcome to the Vortex Project. Your account has been added with the following information: <br>";
         $innerHTML .= "Uname: $un <br>";
         $innerHTML .= "Pword: $up <br>";
         $innerHTML .= "Site URL: $baseurl/login.php <br>";
         $innerHTML .= "Please email any questions you may have to site administrator: $adminemail, ";
      }
   } else {
      $innerHTML .= "<b>Error: </b> User Addition failed. Make sure all drop down boxes are selected.<br>";
      return $innerHTML;
   }

   return $innerHTML;
}


function changePasswordForm($formValues){

   global $listobject, $userid;
   $controlHTML = '';

   if (isset($formValues['newpassword']) ) {
      $newpassword = $formValues['newpassword'];
      $newpassword_again = $formValues['newpassword_again'];
      $oldpassword = $formValues['oldpassword'];
   }

   if (isset($formValues['changepw']) ) {
      if ($newpassword <> $newpassword_again) {
         # confirmation of password fails
         $controlHTML .= " New passwords do not match, please enter your new password again. ";
         $newpassword = '';
         $newpassword_again = '';
      } else {
         # a password is entered, try this
         $listobject->querystring = "  select count(*) as numrecs ";
         $listobject->querystring .= " from users ";
         $listobject->querystring .= " where userid = $userid ";
         $listobject->querystring .= "    and userpass = '$oldpassword' ";
         if ($debug) {
            $controlHTML .= "$listobject->querystring ; <br>";
         }
         $listobject->performQuery();
         $checkpass = $listobject->getRecordValue(1,'numrecs');

         if (!$checkpass) {
            $controlHTML .= " Current Password is incorrect.  Please enter current your password again. ";
         } else {
            # go ahead and change the password
            $listobject->querystring = "  update users set userpass = '$newpassword' ";
            $listobject->querystring .= " where userid = $userid ";
            $listobject->querystring .= "    and userpass = '$oldpassword' ";
            if ($debug) {
               $controlHTML .= "$listobject->querystring ; <br>";
            }
            $listobject->performQuery();
            $_SESSION['userpass'] = $newpassword;
            $controlHTML .= "Password changed. <br>";
         }
      }
   }

   $controlHTML .= "<form id=control name=control>";
   $controlHTML .= "<b>Enter Current Password: </b>";
   $controlHTML .= showWidthPasswordField('oldpassword', $oldpassword, 10, '', 1);
   $controlHTML .= "<br><b>Enter New Password: </b>";
   $controlHTML .= showWidthPasswordField('newpassword', $newpassword, 10, '', 1);
   $controlHTML .= "<br><b>Confirm New Password: </b>";
   $controlHTML .= showWidthPasswordField('newpassword_again', $newpassword_again, 10, '', 1);

   $controlHTML .= showGenericButton('changepw', 'Change Password', "xajax_showChangePasswordForm(xajax.getFormValues(\"control\"))", 1);
   $controlHTML .= "</form>";

   return $controlHTML;
}

function createDomainForm($formValues) {
   global $listobject, $userid, $userinfo;

   $groupid = $userinfo['defaultgroup'];
   #$debug = 1;

   $innerHTML = '';
   $projectid = $formValues['projectid'];

   if (isset($formValues['scenarioname'])) {
      $scenarioname = $formValues['scenarioname'];
      $shortname = $formValues['shortname'];
      $otherscen = $formValues['otherscen'];
      $src_scenario = $formValues['src_scenario'];
      $groupid = $formValues['groupid'];
      $operms = $formValues['operms'];
      $gperms = $formValues['gperms'];
      $pperms = $formValues['pperms'];
      $newdomain = createDomain($listobject, $projectid, $userid, $scenarioname, $shortname, $groupid, $operms, $pperms, $gperms, 1, 1);
      if ($newdomain['error']) {
         $innerHTML .= $newdomain['error_msg'];
         $innerHTML .= $newdomain['debug'];
      } else {
         $newscenarioid = $newdomain['scenarioid'];
         $innerHTML .= $newdomain['message'];
      }
   } else {
      $operms = 7;
      $gperms = 4;
      $pperms = 0;
   }

   $innerHTML .= "<h3>Create a New Scenario</h3>";
   $innerHTML .= "<br>This will create a new model domain. ";
   $innerHTML .= "$rdmesg";
   $innerHTML .= "<form id=control name=control>";
   $innerHTML .= "<br><b>Domain Name: </b> ";
   $innerHTML .= showWidthTextField('scenarioname', $scenarioname, 30, '', 1);
   $innerHTML .= "<br><b>Domain Short Name (abbrev. for model input files,less than 12 chars): </b> ";
   $innerHTML .= showWidthTextField('shortname', $shortname, 10, '', 1);
   $innerHTML .= showHiddenField('projectid',$projectid, 1);
   /*
   // currently disabled
   $innerHTML .= "<br><b>Import From Another Domain?</b> ";
   $innerHTML .= "<br> (False will create an empty domain): ";
   $innerHTML .= showTFListType('otherscen',$otherscen,1, 'submit()', 1);
   $innerHTML .= " Domain to import from: ";
   $innerHTML .= showViewableScenarioList($listobject, $projectid, $src_scenario, $userid, $usergroupids, 'src_scenario', '', 'submit()', $debug, 1);
   */
   $innerHTML .= "<br>";
   $innerHTML .= "<b>Select a Group for this Domain: </b>";
   $innerHTML .= showList($listobject, 'groupid', 'groups', 'groupname', 'groupid', "groupid in (select groupid from mapusergroups where userid = $userid)", $groupid, $debug, 1);
   $innerHTML .= "<br>";
   $innerHTML .= "<b>Set Owner Permisssions for this Domain: </b>";
   $innerHTML .= showList($listobject, 'operms', 'perms', 'permdesc', 'permno', '', $operms, $debug, 1);
   $innerHTML .= "<br>";
   $innerHTML .= "<b>Set Group Permisssions for this Domain: </b>";
   $innerHTML .= showList($listobject, 'gperms', 'perms', 'permdesc', 'permno', '', $gperms, $debug, 1);
   $innerHTML .= "<br>";
   $innerHTML .= "<b>Set Public Permisssions for this Scenario: </b>";
   $innerHTML .= showList($listobject, 'pperms', 'perms', 'permdesc', 'permno', '', $pperms, $debug, 1);
   $innerHTML .= "<br>";
   $innerHTML .= showGenericButton('createscenario','Create Domain', "xajax_showCreateDomainForm(xajax.getFormValues(\"control\"))", 1);
   $innerHTML .= "</form> ";

   return $innerHTML;

}


function createProjectForm($formValues) {
   global $listobject, $userid;


}

function createProjectResult($formValues) {
   global $listobject, $userid;



}


function doShowLogins($formValues) {
   global $usertype, $listobject, $scriptname, $debug, $projectid, $scenarioid, $seglist;
   $listobject->show = 0;
   $innerHTML = '';

   if ($usertype == 1) {
      if (!isset($formValues['numdays'])) {
         $numdays = 0;
      } else {
         $numdays = $formValues['numdays'];
         $seglist = $formValues['seglist'];
      }
      $innerHTML .= "Logins for the last $numdays days: <br>";
      $innerHTML .= "<form id=control name=control>";
      $innerHTML .= showHiddenField('projectid', $projectid, 1);
      $innerHTML .= showHiddenField('scenarioid', $scenarioid, 1);
      $innerHTML .= showHiddenField('seglist', $seglist, 1);

      $innerHTML .= showRadioButton('numdays', '0', $numdays, '', 1, 0);
      $innerHTML .= "Show Todays Logins <br>";
      $innerHTML .= showRadioButton('numdays', '3', $numdays, '', 1, 0);
      $innerHTML .= "Show Last 3 days Logins <br>";
      $innerHTML .= showRadioButton('numdays', '7', $numdays, '', 1, 0);
      $innerHTML .= "Show The Last Weeks Logins <br>";
      $innerHTML .= showGenericButton('showlogins','Show Logins', "xajax_showLogins(xajax.getFormValues(\"control\"))", 1);
      $innerHTML .= "</form> ";

      $dayonly = date('Y-m-d', time());
      $listobject->querystring = "  select a.username, b.* ";
      $listobject->querystring .= " from loginlog as b, users as a ";
      $listobject->querystring .= " where a.userid = b.userid ";
      $listobject->querystring .= "    and thisdate >= ( '$dayonly'::timestamp + '-$numdays days') ";
      $listobject->querystring .= " order by thisdate DESC ";
      if ($debug) {
         $innerHTML .= " $listobject->querystring ; <br>";
      }
      $listobject->performQuery();
      $listobject->showList();
      $innerHTML .= $listobject->outstring;
   } else {
      $innerHTML .= "<b>Error: </b> Unauthorized Access.<br>";
   }
   return $innerHTML;
}


function deleteDomainForm($formValues) {

   # this function needs:
   # lreditlist - lrsegs to edit
   # listobject
   # bmpname
   # year
   # scenarioid
   # projectid

   if ( !(strlen($viewyear ) > 0) ) {
      if (strlen($thisyear) > 0) {
         $viewyear = $thisyear;
      } else {
         $viewyear = date('Y');
      }
   }

   $delid = $_POST['delid'];
   $delyear = $_POST['delyear'];
   if (isset($_POST['deletescenario']) and ($delid > 0) ) {
      $sperms = getScenarioPerms($listobject, $delid, $userid, $usergroupids, 1);
      if ( ($sperms & 7) == 7) {
         # execute bit must be set to delete
         deleteScenario($listobject, $delid, $delyear);
      } else {
         print("This scenariod may not be deleted. Permissions must be set to RWX (7) for deletion.<br>");
      }
   }
   print("<b>Choose a scenario to delete: </b>");
   showViewableScenarioList($listobject, $projectid, $delid, $userid, $usergroupids, 'delid', "projectid = $projectid and ownerid = $userid", '', $debug);
   print("<br><b>Select only a specific year(s)?: </b>");
   showWidthTextField('delyear', $delyear, 10);
   print("<br>");
   showSubmitButton('deletescenario','Delete This Scenario');

}



function sendWelcomeForm($formValues) {

   # this function needs:
   # lreditlist - lrsegs to edit
   # listobject
   # bmpname
   # year
   # scenarioid
   # projectid



   $listobject->querystring = "  select username, firstname, lastname, userpass ";
   $listobject->querystring .= " from users ";
   $listobject->performQuery();

   $recs = $listobject->queryrecords;

   foreach ($recs as $thisrec) {
      $un = $thisrec['username'];
      $fn = $thisrec['firstname'];
      $ln = $thisrec['lastname'];
      $up = $thisrec['userpass'];

      print("<hr><br>$fn $ln, <br>");
      print("Welcome to the Vortex Project. Your account has been added with the following information: <br>");
      print("Uname: $un <br>");
      print("Pword: $up <br>");
      print("Site URL: $baseurl/login.php <br>");
      print("Please email any questions you may have to site administrator: $adminemail, ");
      print(" or to the Vortex Listserv at vortex@listserv.chesapeakebay.net <br>");
   }

}
?>