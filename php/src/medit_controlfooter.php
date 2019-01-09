<?php

$controlHTML = '';
#$controlHTML .= "<span style='padding: 5px; font:Verdana, Arial, Helvetica, sans-serif; ");
#$controlHTML .= "height: 400px; width: 320px;font-size:9px; border:1px solid #000000;'>");
$controlHTML .= "   <form action='$scriptname' method=post name=giswindow>";
$controlHTML .= "      <table>";
$controlHTML .= "         <tr align=center>";
$controlHTML .= "            <td valign=top>";
# get hidden variables
include('./medit_layers.php');
$controlHTML .= $layerHTML;
#$amap->showMapHiddenFormVars();
# no java map
#printf("<INPUT TYPE=\"HIDDEN\" NAME=\"gbIsHTMLMode\" VALUE=\"$amap->gbIsHTMLMode\">");
$controlHTML .= showHiddenField('projectid',"$projectid",1);
$controlHTML .= showHiddenField('lastgroup',"$currentgroup",1);
$controlHTML .= showHiddenField('currentgroup',"$currentgroup",1);

# Uncomment the following line to use openlayers interface
#if ( ($projectid == 5) or ($projectid == 3) ) {
#   include('./medit_openlayers.php');
#} else {
   
   # format output into tabbed display object
   $taboutput = new tabbedListObject;
   $taboutput->name = 'map_window';
   $taboutput->width = '640px';
   $taboutput->height = '480px';
   #$taboutput->tab_names = array('applet','openlayers');
   $taboutput->tab_names = array('openlayers');
   $taboutput->tab_buttontext = array(
      'applet'=>'View Map',
      'openlayers'=>'Edit Map'
   );

   $open_layers = '';
   $open_layers .= "<div id=\"map\"></div>";
   #load the google map API
   $open_layers .= "<script src='http://maps.google.com/maps?file=api&amp;v=2&amp;key=$gapikey'></script>";

   $open_layers .= "<script type='text/javascript'>";
   $open_layers .= "<!-- \n";
   $open_layers .= "var lon = -79.5;\n";
   $open_layers .= "var lat = 37.3;\n";
   $open_layers .= "var zoom = 7;\n";
   $open_layers .= "var map, layer;\n";
   
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
   $gminx = $listobject->getRecordValue(1,'xmin');
   $gminy = $listobject->getRecordValue(1,'ymin');
   $gmaxx = $listobject->getRecordValue(1,'xmax');
   $gmaxy = $listobject->getRecordValue(1,'ymax');
   if ($debug) {
      $controlHTML .= "$listobject->querystring ; <br> $gminx $gminy $gmaxx $gmaxy <br>";
   }
 
   $open_layers .= "var minx = $gminx;";
   $open_layers .= "var miny = $gminy;";
   $open_layers .= "var maxx = $gmaxx;";
   $open_layers .= "var maxy = $gmaxy;";
   $open_layers .= "var projectid = $projectid;";
   $open_layers .= "var mapfile='$basedir/$mapfile';";
   $open_layers .= "var mapservip='$mapservip';";
   $open_layers .= file_get_contents("./medit_ol.php");
   $open_layers .= "// -->\n</script>";
   // need this hidden variable to show nexrad time zteps, pretty cool, but not yet useful
   //$open_layers .= '<input type=\'text\' id=\'time\'  value="2005-08-29T13:00:00Z" onChange=\'ia_wms.mergeNewParams({"time":this.value});\' >';
   #$controlHTML .= "Map Window <br>" . $open_layers);
   $taboutput->tab_HTML['openlayers'] = $open_layers;
   # now, render the tabbed browser
   # add the tabbed view the this object
   /*
   $amap->silent = 1;
   $mapwin = $amap->drawMap2();
   #$controlHTML .= $mapwin);
   $toolbar = $amap->showFormatedToolbar(2);
   #$controlHTML .= $toolbar);
   $taboutput->tab_HTML['applet'] = $mapwin . $toolbar;
   */
   $taboutput->createTabListView('openlayers');
   $innerHTML .= $taboutput->innerHTML;
   $controlHTML .= $innerHTML;
#}

$controlHTML .= "            </td>";
$controlHTML .= "         </tr>";
/*
$controlHTML .= "         <tr>";
$controlHTML .= "            <td valign=top>";
$amap->showLayerPallete2();
$controlHTML .= "            </td>";
$controlHTML .= "         </tr>";
*/
$controlHTML .= "         <tr>";
$controlHTML .= "            <td align=center>";
# no java map
#$amap->showJavaToggle();
$controlHTML .= showGenericButton('clearfeature', 'Clear Selected Shapes', 'clearSelectedScratchShapes()', 1);
$controlHTML .= showGenericButton('clearallfeature', 'Clear All Temp Shapes', 'clearAllScratchShapes()', 1);
# $controlHTML .= "<br>Current Scale: $ext");
$controlHTML .= "            </td>";
$controlHTML .= "         </tr>";
$controlHTML .= "      </table>";
$controlHTML .= "   </form>";
#$controlHTML .= "</span>";


?>
