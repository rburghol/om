<?php

include('./config_create.php');

#  Create An Account


# chekc if a login has already been processed, as evidenced by the presence of the Captcha code
session_start();
if(($_SESSION['security_code'] == $_POST['security_code']) && (!empty($_SESSION['security_code'])) ) {
   // Insert you code for processing the form here, e.g emailing the submission, entering it into a database.
   unset($_SESSION['security_code']);

   $done = 0;
   while (!$done) {
      $mf = 0;
      # check to see that required fields are filled out
      $required = array('email','uname');
      foreach ($required as $thisfield) {
         if (!strlen($_POST[$thisfield])) {
            print("<b>Error:</b> $thisfield is required.<br>");
            $mf++;
         }
      }
      if ($mf > 0) {
         print("<b>Error:</b> You have $mf missing fields.  Please fill out all fields marked with an '*'.<br>");
         break;
      }

      $pw1 = $_POST['pw1'];
      $pw2 = $_POST['pw2'];
      $fname = $_POST['fname'];
      $lname = $_POST['lname'];
      $uname = $_POST['uname'];
      $email = $_POST['email'];

      # check to see that passwords match
      if (!($pw1 == $pw2)) {
         print("<b>Error:</b> Your passwords do not match.  Please re-enter your passwords.<br>");
         $pw1 = '';
         $pw2 = '';
         break;
      }

      # check to see that passwords are non-blank
      if ($pw1 == '') {
         print("<b>Error:</b> Your password can not be an empty string.  Please re-enter your password.<br>");
         $pw1 = '';
         $pw2 = '';
         break;
      }

      # check to see that passwords are not an SQL injection attempt, or do not contain illegal chars
      $pwsan = sanitize($pw1, SQL);
      if ($pw1 <> $pwsan) {
         print("<b>Error:</b> Your password contain illegal characters (note: single quotes not allowed).  Please re-enter your password.<br>");
         $pw1 = '';
         $pw2 = '';
         break;
      }

      # OK, if we have made it this far, we will check to see if there are duplicate names or accounts
      $listobject->querystring = " select count(*) as numdups from users where username = '$uname'";
      $listobject->performQuery();
      $numdups = $listobject->getRecordValue(1,'numdups');
      if ($numdups > 0) {
         print("<b>Error:</b> There is already a user with that name.  Please choose a new name<br>");
         $uname = '';
         break;
      }

      # OK, all criteria have passed.  Time to Insert the user and then log them in.
      $listobject->querystring = "  insert into users (username, usertype, defaultproject, firstname, lastname, ";
      $listobject->querystring .= "    userpass, email, groupid) ";
      $listobject->querystring .= " values ('$uname', $defutype, $defuproj, '$fname', '$lname', ";
      $listobject->querystring .= "    '$pw1', '$email', $defgid ) ";
      $listobject->performQuery();

      $listobject->querystring = " select count(*) as numdups from users where username = '$uname'";
      $listobject->performQuery();
      $numdups = $listobject->getRecordValue(1,'numdups');
      if (!($numdups > 0)) {
         print("<b>Error:</b> Something bad happened.  You could not be added. A message has been sent to the administrator.  You can try again if you like<br>");
         $uname = '';
         break;
      }

      $listobject->querystring = " select userid from users where username = '$uname'";
      $listobject->performQuery();
      $userid = $listobject->getRecordValue(1,'userid');

      # OK, all criteria have passed.  Time to Insert the user and then log them in.
      $listobject->querystring = "  insert into groups (groupname, ownerid) ";
      $listobject->querystring .= " values ('$uname Group', $userid) ";
      $listobject->performQuery();

      $listobject->querystring = " select groupid from groups where groupname = '$uname Group'";
      $listobject->performQuery();
      $groupid = $listobject->getRecordValue(1,'groupid');

      # Now, set their base groupid to their own private group
      $listobject->querystring = "  update users set groupid = $groupid where userid = $userid ";
      $listobject->performQuery();

      # add an entry into the group table for this user as group "User"
      $listobject->querystring = "  insert into mapusergroups (userid, groupid) ";
      $listobject->querystring .= " values ($userid,2) ";
      $listobject->performQuery();

      # add an entry into the group table for this users own group
      $listobject->querystring = "  insert into mapusergroups (userid, groupid) ";
      $listobject->querystring .= " values ($userid,$groupid) ";
      $listobject->performQuery();

      # now add a scenario for this user
      $listobject->querystring = "  insert into scenario (scenario, projectid, shortname, ownerid, groupid, ";
      $listobject->querystring .= "    operms, gperms, pperms ) ";
      $listobject->querystring .= " values ('$uname Models', $defuproj, 'mod$uname', $userid, $groupid, ";
      $listobject->querystring .= "    7, 4, 0 ) ";
      $listobject->performQuery();

      $listobject->querystring = " select scenarioid from scenario where ownerid = $userid ";
      $listobject->performQuery();
      $defscenario = $listobject->getRecordValue(1,'scenarioid');

      $listobject->querystring = "update users set defscenario = $defscenario where userid = $userid ";
      $listobject->performQuery();

      # OK.  All seems to be in order.  Now, we should go ahead and have this person log in.
      print("<h3>Congratulations! Account creation successful.  Please log in.</h3><br>");
      $username = $uname;
      include('./logout.php');

      # this just lets us get out when we need to
      $done = 1;
   }

} else {
   // Insert your code for showing an error message here
   if (isset($_POST['action'])) {

      $pw1 = $_POST['pw1'];
      $pw2 = $_POST['pw2'];
      $fname = $_POST['fname'];
      $lname = $_POST['lname'];
      $uname = $_POST['uname'];
      $email = $_POST['email'];
      # this is an actual submission, so print the error since we were missing the code
      print("<b>Error:</b> You entered an incorrect verification code.<br>");
   } else {
      # otherwise, we are coming to this page for the first time, so just print out the form and relax (do nothing here)
   }
}

# print out the form
print("<h3>Create an Account</h3>");
print("<b>Notice:</b> <i>Mandatory Fields are indicated with an asterisk (*)</i><br>");
print("<form action='create_account.php' method=post>");
print("<input type='hidden' name='action' value='create_account'>");
#print("<input type='text' name='action' value='create_account' columns=32>");
print("<b>First name: </b><input type='text' name='fname' value='$fname' columns=32><br>");
print("<b>Last name: </b><input type='text' name='lname' value='$lname' columns=32><br>");
print("<b>* Username: </b><input type='text' name='uname' value='$uname' columns=32><br>");
print("<b>* Email: </b><input type='text' name='email' value='$email' columns=32><br>");
print("<b>* Password: </b><input type='password' name='pw1' value='$pw1' columns=16><br>");
print("<b>* Password (again): </b><input type='password' name='pw2' value='$pw2' columns=16><br>");
print("<b>Security Code:</b><br>");
print("<img src='captchaSecurityImages.php' />");
print("<input id='security_code' name='security_code' type='text' /><i>Input Text From Image at Left</i> <br>");
print("<input name='submit' type='submit' value='Create Account' />");

print("</form>");


?>