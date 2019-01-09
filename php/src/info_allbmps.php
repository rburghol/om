<html>
<body>
<?php

   include('./config.php');

   if (isset($_GET['projectid'])) {
      $projectid = $_GET['projectid'];
      $debug = $_GET['debug'];
   } else {
      print("No Project ID selected.<br>");
      die;
   }


   $as = $listobject->adminsetuparray['bmp_subtypes'];
   $listobject->querystring = "  select a.bmp_desc, b.landuse, d.pollutantname, ";
   $listobject->querystring .= "    min(c.efficiency) as loweff, max(c.efficiency) as hieff ";
   $listobject->querystring .= " from bmp_types as a, landuses as b, proj_bmp_efficiencies as c, ";
   $listobject->querystring .= "    pollutanttype as d, subshedinfo as e ";
   $listobject->querystring .= " where a.projectid = $projectid ";
   $listobject->querystring .= "    and b.projectid = $projectid ";
   $listobject->querystring .= "    and c.projectid = $projectid ";
   $listobject->querystring .= "    and a.bmp_name = c.bmpname ";
   $listobject->querystring .= "    and b.hspflu = c.luname ";
   $listobject->querystring .= "    and d.typeid = c.constit ";
   $listobject->querystring .= "    and e.stseg = c.stseg ";
   $listobject->querystring .= " group by a.bmp_desc, b.landuse, d.pollutantname ";
   $listobject->querystring .= " order by a.bmp_desc, b.landuse, d.pollutantname ";
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   $listobject->tablename = 'bmp_effic';
   $thisrec = $listobject->queryrecords[0];
   #$listobject->debug = 1;
   if ($usertype == 10) {
      showFormVars($listobject,$thisrec,$as, 1, 0, $debug, 0);
   } else {
      $listobject->showList();
   }


   $as = $listobject->adminsetuparray['bmp_subtypes'];
   $listobject->querystring = "  select a.bmp_desc, b.bmp_group, b.bmp_order ";
   $listobject->querystring .= " from bmp_types as a, proj_bmp_order as b ";
   $listobject->querystring .= " where a.projectid = $projectid ";
   $listobject->querystring .= "    and b.projectid = $projectid ";
   $listobject->querystring .= "    and a.typeid = b.typeid ";
   $listobject->querystring .= " order by b.bmp_group, b.bmp_order, a.bmp_desc ";
   if ($debug) { print("$listobject->querystring ; <br>"); }
   $listobject->performQuery();
   $listobject->tablename = 'bmp_group';
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