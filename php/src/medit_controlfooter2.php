<?php

$mapHTML = '';
$mapHTML .= "   <form action='$scriptname' method=post name=giswindow>";
$mapHTML .= "      <table>";
$mapHTML .= "         <tr align=center>";
$mapHTML .= "            <td valign=top>";
# get hidden variables
//include('./medit_layers.php');
$mapHTML .= $layerHTML;
$mapHTML .= showHiddenField('projectid',"$projectid",1);
$mapHTML .= showHiddenField('lastgroup',"$currentgroup",1);
$mapHTML .= showHiddenField('currentgroup',"$currentgroup",1);

###########################################
###    START map creation javascript    ###
###########################################
   $ol_javascript = '';
   $ol_javascript .= "<div id=\"map\"></div>";
   #load the google map API
   $ol_javascript .= "<script src='http://maps.google.com/maps?file=api&amp;v=2&amp;key=$gapikey'></script>";

   $ol_javascript .= "<script type='text/javascript'>";
   $ol_javascript .= "<!-- \n";
   $ol_javascript .= "var lon = -79.5;\n";
   $ol_javascript .= "var lat = 37.3;\n";
   $ol_javascript .= "var zoom = 7;\n";
   $ol_javascript .= "var map, layer;\n";
   
   # transform the map coords to OpenLayers
   /*
   $minx = $amap->extent->minx;
   $miny = $amap->extent->miny;
   $maxx = $amap->extent->maxx;
   $maxy = $amap->extent->maxy;
   */
   $minx = -83.6754150390625;
   $miny = 36.5407371520996;
   $maxx = -75.2422637939453;
   $maxy = 39.4660148620605;
   $boxstr = "transform(setsrid(polygonize(makeBOX2d(makepoint($minx,$miny), makepoint($maxx,$maxy))),4326),900913)";
   $listobject->querystring = "select xmin(tg), ymin(tg), xmax(tg), ymax(tg) from (select $boxstr as tg) as foo ";
   $listobject->performQuery();
   if (count($listobject->queryrecords) > 0) {
      $gminx = $listobject->getRecordValue(1,'xmin');
      $gminy = $listobject->getRecordValue(1,'ymin');
      $gmaxx = $listobject->getRecordValue(1,'xmax');
      $gmaxy = $listobject->getRecordValue(1,'ymax');
   } else {
      // default to Virginia state boundaries if we cannot get a valid set from the above query
      $gminx = -9314704.59406426;
      $gminy = 4375283.34703558;
      $gmaxx = -8375930.49167516;
      $gmaxy = 4788645.74402087;
   }
   if ($debug) {
      $mapHTML .= "$listobject->querystring ; <br> $gminx $gminy $gmaxx $gmaxy <br>";
   }
 
   $ol_javascript .= "var minx = $gminx;";
   $ol_javascript .= "var miny = $gminy;";
   $ol_javascript .= "var maxx = $gmaxx;";
   $ol_javascript .= "var maxy = $gmaxy;";
   $ol_javascript .= "var projectid = $projectid;";
   $ol_javascript .= "var mapfile='$basedir/$mapfile';";
   $ol_javascript .= "var mapservip='$mapservip';";
   $ol_javascript .= file_get_contents("./medit_ol.php");
   $ol_javascript .= "// -->\n</script>";
   // need this hidden variable to show nexrad time zteps, pretty cool, but not yet useful
   //$ol_javascript .= '<input type=\'text\' id=\'time\'  value="2005-08-29T13:00:00Z" onChange=\'ia_wms.mergeNewParams({"time":this.value});\' >';
###########################################
###    END map creation javascript      ###
###########################################
$mapHTML .= $ol_javascript;
$mapHTML .= "            </td>";
$mapHTML .= "         </tr>";
$mapHTML .= "         <tr>";
$mapHTML .= "            <td align=center>";
$mapHTML .= showGenericButton('clearfeature', 'Clear Selected Shapes', 'clearSelectedScratchShapes()', 1);
$mapHTML .= showGenericButton('clearallfeature', 'Clear All Temp Shapes', 'clearAllScratchShapes()', 1);
$mapHTML .= "            </td>";
$mapHTML .= "         </tr>";
$mapHTML .= "      </table>";
$mapHTML .= "   </form>";


?>