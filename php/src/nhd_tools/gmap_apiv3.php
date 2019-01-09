<html> 
<head> 
<meta name="viewport" content="initial-scale=1.0, user-scalable=no"/> 
<meta http-equiv="content-type" content="text/html; charset=UTF-8"/> 
<title>Google Maps with Web Map Service - Google Maps V3 - MapServer WMS and Google Maps</title> 
 
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script> 

  
<script type="text/javascript"> 
    //setting up the map
    var map;
 
       
    var wmsMapType;
  
  
    function initialize() {
        //Set the center of the map
        
        var usa = new google.maps.LatLng(38.87, -94.63);
 
        var map_setup = {
            zoom: 5,
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            MapTypeControl: true,
            zoomControlOptions: {style: google.maps.ZoomControlStyle.SMALL},
            navigationControl: true,
            navigationControlOptions: { style: google.maps.NavigationControlStyle.ZOOM_PAN },
            
            scrollwheel: false,
            panControl: false,
            maxZoom: 17,
            minZoom: 1,
            scaleControl: true,
            center: usa
            
        }
 
        map = new google.maps.Map(document.getElementById("map"), map_setup);
        
        
      
       
        //Creating the WMS layer options.  This code creates the Google imagemaptype options for each wms layer.  In the options the function that calls the individual 
        //wms layer is set 
      
 
        var wmsOptions = {
            alt: "MapServer Layer",
            getTileUrl: WMSGetTileUrl,
            isPng: false,
            maxZoom: 17,
            minZoom: 1,
            name: "MapServer Layer",
            tileSize: new google.maps.Size(256, 256)
        };
 
 
        //Creating the object to create the ImageMapType that will call the WMS Layer Options. 
        
       wmsMapType = new google.maps.ImageMapType(wmsOptions);
 
      
    //Where the initial map type is set.  This can be adjusted as necessary.  The map name in ' ' indicates the default map viewed when the user 
    //visits the page


    map.overlayMapTypes.insertAt(0, wmsMapType);
    
      
}
   
  //The code that reads in the WMS tiles.  To change the WMS layer the user would update the layers line.  As this is constructed now you need to have this code for each WMS layer.
  //Check with your Web Map Server to see what are the required components of the address.  You may need to add a couple of segements.  For example, the ArcServer WMS requires
  //a CRS value which is tacked on to the end of the url.  For an example visit http://www.gisdoctor.com/v3/arcserver_wms.html 
  
  function WMSGetTileUrl(tile, zoom) {
      var projection = window.map.getProjection();
      var zpow = Math.pow(2, zoom);
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
      //The user will enter the address to the public WMS layer here.  The data must be in WGS84
      var baseURL = "http://deq2.bse.vt.edu/cgi-bin/mapserv?";
      baseURL += 'map=/var/www/html/om/nhd_tools/nhd_cbp_small.map&';
      var version = "1.1.1";
      var request = "GetMap";
      var format = "image/png"; //type of image returned 
      //The layer ID.  Can be found when using the layers properties tool in ArcMap
      var layers = "nhd_fulldrainage proj_seggroups";
      var srs = "EPSG:4326"; //projection to display. This is the projection of google map. Don't change unless you know what you are doing.
      var bbox = lon1 + "," + lat1 + "," + lon2 + "," + lat2;
 
      //Add the components of the URL together
      var width = "256";
      var height = "256";
 
      var styles = "default";
 
      var url = baseURL + "version=" + version + "&request=" + request + "&Layers=" + layers + "&Styles=" + styles + "&SRS=" + srs + "&BBOX=" + bbox + "&width=" + width + "&height=" + height + "&format=" + format + "&TRANSPARENT=TRUE" + '&' + shapecol + '=' + elid;
      alert(url);
      return url;
  }

 
 
</script> 
 
</head> 
<body onload="initialize()"> 
 
<div style ="height:25px; width:100%; background-color:black; border-bottom-style:solid; border-color:Black;padding-left:15px; padding-top:10px;"> 
    <a href="http://www.gisdoctor.com" style="text-decoration:none;"><b> 
        <span style="color:White; font-family:Arial">Return to GIS Doctor</span></b></a> 
</div> 
<div style="width:1000px; padding-left:10px; font-family:Arial"> 
<h2>MapServer WMS in Google Maps V3</h2> 
 The following map displays a Google Map and a <a href="http://mapserver.org/index.html">MapServer WMS</a> data layer.  The sample layer comes from <a href="http://mesonet.agron.iastate.edu/index.phtml/">here</a> and is a time enabled <a href="http://mesonet.agron.iastate.edu/ogc/">OGC Layer</a>.
 The code is written based on <a href="http://code.google.com/apis/maps/documentation/javascript/">Google Maps API V3</a>.  Feel free to use the code, just remember to provide a link to <a href="http://www.gisdoctor.com">GISDoctor.com</a>.
 </div> 
 <br />
<div id="map" style="position:absolute; left:10px; width:99%; height:95%;"></div> 
 
 
 <!--Do not copy and paste the next two blocks of code into your page-->
<script type="text/javascript"> 
    var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
    document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script> 
<script type="text/javascript"> 
    var pageTracker = _gat._getTracker("UA-10269571-1");
    pageTracker._initData();
    pageTracker._trackPageview();
</script> 
</body> 
</html> 