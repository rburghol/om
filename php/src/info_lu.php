<html>
<body>
<?php

   include('./config.php');

   if (isset($_GET['projectid'])) {
      $projectid = $_GET['projectid'];
   } else {
      print("No Project ID selected.<br>");
      die;
   }


   $as = $listobject->adminsetuparray['bmp_subtypes'];
   $listobject->querystring = "select hspflu, landuse from landuses where projectid = $projectid and hspflu <> '' and hspflu is not null";
   $listobject->performQuery();
   $listobject->tablename = 'landuses';
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