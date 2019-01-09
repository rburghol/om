<?php

   include("config.php");
   $elementid = 260;
   $arrOutput = array();
   if ($elementid > 0) {
      $command = "/usr/bin/php -f $basepath/test_modelrun.php $elementid";
      print("Trying: $command > /dev/null & <br>");
      $forkout = exec( "$command > /dev/null &", $arrOutput );
      print("Forked: $forkout <br>");
   } else {
      print("Can't run this, no element.");
   }

?>
