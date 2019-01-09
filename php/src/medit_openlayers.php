<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <style type="text/css">
        #map {
            width: 600px;
            height: 400px;
            border: 1px solid black;
        }
    </style>
    <script src="../lib/OpenLayers.js"></script>
    <script type="text/javascript">
        <!--
        var lon = -79.5;
        var lat = 37.3;
        var zoom = 7;
        var map, layer;
<?php

   $minx = $amap->extent->minx;
   $miny = $amap->extent->miny;
   $maxx = $amap->extent->maxx;
   $maxy = $amap->extent->maxy;
   
   print("var minx = $minx;");
   print("var miny = $miny;");
   print("var maxx = $maxx;");
   print("var maxy = $maxy;");
   print("var projectid = $projectid;");
   print("var mapfile='$basedir/$mapfile';");

?>
        /*
        var url = "http://labs.metacarta.com/wms/vmap0";
        var aclayers = 'basic';
        */
        //var url = "http://172.16.210.66/cgi-bin/ucitool.exe";
        var url = "http://172.16.210.66/cgi-bin/mapserv.exe?map=" + mapfile;
        var aclayers = 'poli_bounds';
        var drawControls, vlayer;

        function init(){
            map = new OpenLayers.Map( $('map') );
           // layer = new OpenLayers.Layer.WMS( "OpenLayers WMS",
           //         "http://labs.metacarta.com/wms/vmap0", {layers: 'basic'} );
           // map.addLayer(layer);
            
            layer = new OpenLayers.Layer.MapServer( "Base Map",
                    url, {layers: aclayers},
                    {gutter: 15});
            map.addLayer(layer);
            
            var projfilterstring = '<Filter>' 
               + '<PropertyIsEqualTo>'    
               + '<PropertyName>projectid</PropertyName>'
               + ' <Literal>'
               + projectid
               + '</Literal>'
               + '</PropertyIsEqualTo>'
               + '</Filter>';
            
            /*
            layer2 = new OpenLayers.Layer.WFS( "proj_seggroups",
                    url, {typename: 'proj_seggroups'},
               {
                      typename: 'proj_seggroups',
                      featureNS: 'http://www.openplans.org/topp',
                      extractAttributes: true
               } );
            map.addLayer(layer2);
            */
            
            /*
            
            layer1 = new OpenLayers.Layer.WFS( "poli_bounds",
                    url, {typename: 'poli_bounds', filter:projfilterstring},
               {
                      typename: 'poli_bounds',
                      featureNS: 'http://www.openplans.org/topp',
                      extractAttributes: true
               } );
            map.addLayer(layer1);
            
            */
            /*
            layer = new OpenLayers.Layer.MapServer( "Selected Segments",
                    url, {layers: aclayers},
                    {gutter: 15});
            map.addLayer(layer);
            */

            var size = new OpenLayers.Size(10,17);
            var offset = new OpenLayers.Pixel(-(size.w/2), -size.h);
            
            var mapbounds = new OpenLayers.Bounds(minx, miny, maxx, maxy);
            map.zoomToExtent(mapbounds);
            map.addControl( new OpenLayers.Control.LayerSwitcher() );
            
            OpenLayers.Util.getElement('controlpanel').innerHTML = 'Test Text';
        }

        // -->

    </script>
  </head>
  <body onload="init()">
    <div id="map"></div>
  </body>
</html>