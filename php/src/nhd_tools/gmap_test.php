<!DOCTYPE html
  PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8"/>
<title>Google/MapServer Tile Example</title>
<script src="http://maps.google.com/maps?file=api&v=2&key=ABQIAAAAA7WL0moYTPBf2u-kPTUd8hSpxCV4KUwMR8tyB6kYVprcA33vJxTUyO8Pd0KshKNw8YTmJ-y9GxOGcA"
        type="text/javascript"></script>
<script type="text/javascript">

function load() {
  if (GBrowserIsCompatible()) {
    var urlTemplate = 'http://deq2.bse.vt.edu/cgi-bin/mapserv?';
        urlTemplate += 'map=/var/www/html/om/nhd_tools/nhd_cbp_small.map&';
        urlTemplate += 'layers=nhd_fulldrainage proj_seggroups&';
        urlTemplate += 'mode=tile&';
        urlTemplate += 'tilemode=gmap&';
        urlTemplate += 'tile={X}+{Y}+{Z}';
     <?php
        if (isset($_GET['elementid'])) {
           $shapecol = 'elementid';
        } else {
           $shapecol = 'comid';
        }
        $elid = $_GET[$shapecol];
        echo "var shapecol='" . $shapecol . "';\n";
        echo "var elid=" . $elid . ";\n";
        echo "var lon1=" . $_GET['lon1'] . ";\n";
        echo "var lat1=" . $_GET['lat1'] . ";\n";
        echo "var lon2=" . $_GET['lon2'] . ";\n";
        echo "var lat2=" . $_GET['lat2'] . ";\n";
     ?>
        urlTemplate += '&' + shapecol + '=' + elid;
        urlTemplate += '&format=image/png&transparent=true';
        //urlTemplate += '&mapext=' + lon1 + ' ' + lat1 + ' ' + lon2 + ' ' + lat2;
        //alert(urlTemplate);
    var myLayer = new GTileLayer(null,0,18,{
                                 tileUrlTemplate:urlTemplate,
                                 isPng:true,
                                 opacity:1.0 });
    var map = new GMap2(document.getElementById("map"));
    map.addControl(new GLargeMapControl());
    map.addControl(new GMapTypeControl());
    //alert(urlTemplate);
    //map.setCenter(new GLatLng(38.34, -77.57), 10);
    var southWest = new google.maps.LatLng(lat1,lon1);
    var northEast = new google.maps.LatLng(lat2,lon2);
    var bounds = new google.maps.LatLngBounds(southWest,northEast);
    map.setCenter(bounds.getCenter(), map.getBoundsZoomLevel(bounds));
    map.addOverlay(new GTileLayerOverlay(myLayer));
  }
}

</script>
</head>
<body onload="load()" onunload="GUnload()">
<?php
$mw = 240;
$mh = 240;
if (isset($_GET['mapwidth'])){
   $mw = $_GET['mapwidth'];
}
if (isset($_GET['mapheight'])){
   $mh = $_GET['mapheight'];
}

   echo "<div id=\"map\" style=\"width: $mw" . "px; height: $mh" . "px\"></div>;\n";
?>
  
</body>
</html>