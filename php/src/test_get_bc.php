<html>
<body>

<?php


# set up db connection
$noajax = 1;
$projectid = 3;
$scid = 20;

error_reporting(E_ALL);
include_once('xajax_modeling.element.php');
include_once('lib_verify.php');

if (isset($_GET['elementid'])) {
   $elid = $_GET['elementid'];
} else {
   $elid = $argv[1];
}
$bvar = '';
if (isset($_GET['bvar'])) {
   $bvar = $_GET['bvar'];
}
$bclass = '';
if (isset($_GET['class'])) {
   $bclass = $_GET['class'];
}

$phtml = '';
$chtml = '';


// this gets all broadcasts, so that we can include links to see the details for them
$p_casts = getBroadCasts($elid, 'parent', '', '');
$pclasses = array_keys($p_casts);
$c_casts = getAllHubBroadCasts($listobject, $elid, 'child', '', '');
//print_r($c_casts);
$allvars = extract_arrayvalue($c_casts, 'broadcast_varname');
//print("<br>contents of broadcast_varname <br>");
//print_r($allvars);
//print("<hr>");
$ubvars = array_unique($allvars);
if ($bvar == '') {
   $bclass = $c_casts[0]['broadcast_class'];
   $bvar = $c_casts[0]['broadcast_varname'];
}

if ( (count($c_casts) == 0) and (count($p_casts) == 0)) {
   $info = 'No Broadcast Classes Found';
} else {

   $ename = getElementName($listobject, $elid);
   $phtml .= "<table>";
   $pid = getElementContainer($listobject, $elid);
   
   print("<b><i>Getting broadcast objects for $ename ($elid) .</i></b><br>\n");
   if ($pid > 0) {
      $pname = getElementName($listobject, $elid);
      $phtml .= "<tr><td><b>Parent Broadcasts</b><br>";
      $phtml .= print_r($pclasses,1) . "<br>";
      $phtml .= "<a href='$scriptname?elementid=$pid'>Show Broadcasts for Parent Element - $pname ($pid)</a>";
      $phtml .= "</td></tr>";
   }
   $phtml .= "<tr><td>";
   // show all broadcast variables, from this parent, as well as all children
   $phtml .= "<br><b>Broadcast variables: </b><br>";
   $vdel = '';
   foreach ($ubvars as $thisvar) {
      if ($thisvar == $bvar) {
         $phtml .= $vdel . " $thisvar ";
      } else {
         $phtml .= $vdel . " <a href='$scriptname?elementid=$elid&bvar=$thisvar'>$thisvar</a> ";
      }
      $vdel = '|';
   }
   $phtml .= "<br>";
   // BEGIN selected child mappings
   $childcasts = getAllHubBroadCasts($listobject, $elid, 'child', $bclass, $bvar);
   $phtml .= "Target Class: $bclass<br>Target Variable: $bvar <br>";
   //$phtml .= print_r($childcasts,1);
   foreach ($childcasts as $thiscast) {
      $rw = $thiscast['broadcast_mode'];
      $localvar = $thiscast['local_varname'];
      $cid = $thiscast['elementid'];
      if ($rw == 'read') {
         $in = "->";
         $out = '';
      } else {
         $in = "";
         $out = '->';
      }
      $elemname = getElementName($listobject, $cid);
      $casts[$rw] .= "<li>$elemname ($in$localvar$out)";
   }
   $phtml .="<table border=1><tr>";
   $phtml .="<td valign=top>Read $bvar <ul>" . $casts['read'] . "</ul></td>";
   $phtml .="<td valign=top>Write $bvar <ul>" . $casts['cast'] . "</ul></td>";
   $phtml .="</tr></table>";
   
   // end selected child mappings
   $phtml .= "</td></tr></table>";
   
/*
   $cclasses = array_keys($c_casts);
   $chtml .= "<table><tr><td>";
   //$chtml .= print_r($cclasses,1);
   $childrecs = getChildComponentType($listobject, $elid);
   $chtml .= "<table>";
   foreach ($childrecs as $thisrec) {
      $cid = $thisrec['elementid'];
      $cname = $thisrec['elemname'];
      //$child_casts = getBroadCasts($cid, 'parent', '', '');
      $chtml .= "<tr><td> Child $cname ($cid) <bt>";
      $chtml .= "<a href='$scriptname?elementid=$cid'>Show Broadcasts for Element - $cname ($cid)</a>";
      $chtml .= "</td></tr>";
   }
   $chtml .= "</table>";
   $chtml .= "</tr></table>";
*/

   $chtml .= "</td></tr></table>";
   
   $info = $phtml .= "<br>";
   $info .= "Object $elid " . getElementName($listobject, $elid) . "<br>";
   $info .= $chtml;
} 

print("$info");
print("Finished.  Saved $i items.<br>");

?>

</body>
</html>