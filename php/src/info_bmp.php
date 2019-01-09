<html>
<body>
<?php

   include('./config.php');

   if (isset($_GET['bmpid'])) {
      $bmpid = $_GET['bmpid'];
   } else {
      print("No BMP ID selected.<br>");
      die;
   }


   $as = $listobject->adminsetuparray['bmp_subtypes'];
   $listobject->querystring = "select * from bmp_subtypes where bmpid = $bmpid";
   $listobject->performQuery();
   $listobject->tablename = 'bmp_subtypes';
   $thisrec = $listobject->queryrecords[0];
   #$listobject->debug = 1;
   if ($usertype == 10) {
      showFormVars($listobject,$thisrec,$as, 1, 0, $debug, 0);
   } else {
      $listobject->showList();
   }

?>
</body>
</html>