
<?php
   # logout script

   include_once('./config.php');

?>

<?php

if (!$loggedin) {
   print("   <html>");
   print("<head>");
   print("<title>Online Water Supply Planning Tool</title>");
   print("</head>");
   print("<body>");
   print("<h3>Online Water Supply Planning Tool Login</h3>");
   $targ = $scriptname;
   if (strlen($_SERVER['QUERY_STRING']) > 0) {
      $targ .= '?' . $_SERVER['QUERY_STRING'];
   }
   //print("<form name='loginform' action='./login.php' method=post>");
   print("<form name='loginform' action='$targ' method=post>");
   print("     Username: <input type=text name='username' value='$username' length=20><br>");
   print("   Password: <input type=password name='userpass' length=20><br>");
   print("   <input type=hidden name='actiontype' value='login'>");
   #print("   <input type=hidden name='lastgroup' value='-1'>");
   #print("   <input type=hidden name='currentgroup' value='-2'>");
   print("   <input type=hidden name='target' value='$targ'>");
   print("   Login target = '$targ' <br>");
   //print("   <input type=hidden name='target' value='$scriptname'>");
   print("   <input type=submit name=submit value='submit'>");
   print("   </form>");
   print("<br>To create an account click <a href='./create_account.php'>here</a>.<br>");
   print("</body>");
   print("</html>");
}


if (!$loggedin) {
   die;
}
?>

