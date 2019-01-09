<html>
<body>
<?php

   include('./config.php');

   if (isset($_GET['topicname'])) {
      $topicname = $_GET['topicname'];
   } else {
      print("No topic selected.<br>");
      die;
   }

   print("<h3>Vortex Help</h3>");

   $listobject->querystring = "select * from helptopics where topicname = '$topicname'";
   $listobject->performQuery();
   $listobject->tablename = '';
   $helptext = $listobject->getRecordValue(1,'helptext');
   print("$helptext");

?>
</body>
</html>