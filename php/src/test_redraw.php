<?php

   #include ('./xajax_config.php');
   $noajax = 1;
   $projectid = 3;
   include_once("xajax_modeling.element.php");
   error_reporting('E_ALL');
   $elementid = 345;
   $innerHTML = '';
   print("Loading model $elementid<br>");
   $thisobresult = unSerializeModelObject($elementid);
   print("model loaded.<br>");
   $thismodel = $thisobresult['object'];
   # calls the reDraw method of a model, which simply passes the message down to any contained objects that have a 
   # reDraw method, to load their data from cache, and reDraw.
   #$thismodel->debugmode = 1;
   #$thismodel->cascadedebug = 1;
   #$thismodel->setDebug(1);
   #$thismodel->reDraw();
   foreach ($thismodel->components as $thiscomp) {
      # check for graph output
      print("Loading $nm <br>");
      $nm = $thiscomp->name;
      $log = $thiscomp->logtable;
      if (method_exists($thiscomp, 'reDraw')) {
         $thiscomp->debugmode = 2;
         $thiscomp->debug = 1;
         print("Redrawing $nm <br>");
         $thiscomp->reDraw();
         print("<img src=$thiscomp->imgurl><br>");
      }
      print("Info for $nm <br>");
      print_r($log);
   }

   #$innerHTML = 'Managed to unserialize the object!!!';


?>