        

        /*
        var url = "http://labs.metacarta.com/wms/vmap0";
        var aclayers = 'basic';
        */
        //var url = "http://172.16.210.66/cgi-bin/ucitool.exe";
        var url = "http://" + mapservip + "/cgi-bin/mapserv.exe?map=" + mapfile ;
        //var url = "http://172.16.209.70/cgi-bin/mapserv.exe?map=" + mapfile ;
        //+ "&layer=poli_bounds&layer=proj_seggroups&layer=proj_subsheds";
        var ms1 = 'proj_seggroups';
        var ms2 = 'active_seggroup';
        var map,ia_wms;
        // Make these variable names globally recognizable
        var drawControls, vlayer, selectedFeature;

        function init(){
            
            var mapbounds = new OpenLayers.Bounds(minx, miny, maxx, maxy);
            var options = {
                projection: new OpenLayers.Projection("EPSG:900913"),
                displayProjection: new OpenLayers.Projection("EPSG:4326"),
                units: "m",
                numZoomLevels: 18,
                maxResolution: 156543.0339,
                maxExtent: new OpenLayers.Bounds(-20037508, -20037508,
                                                 20037508, 20037508.34)
            };
            map = new OpenLayers.Map('map', options);

            // create Google Mercator layers
            var gphy = new OpenLayers.Layer.Google(
                "Google Physical",
                {type: G_PHYSICAL_MAP, 'sphericalMercator': true}
            );
            var gmap = new OpenLayers.Layer.Google(
                "Google Streets",
                {'sphericalMercator': true}
            );
            var gsat = new OpenLayers.Layer.Google(
                "Google Satellite",
                {type: G_SATELLITE_MAP, 'sphericalMercator': true, numZoomLevels: 22}
            );
            var ghyb = new OpenLayers.Layer.Google(
                "Google Hybrid",
                {type: G_HYBRID_MAP, 'sphericalMercator': true}
            );
            
            
            blayer = new OpenLayers.Layer.WMS( "Base Map",
                    "http://labs.metacarta.com/wms/vmap0", {layers: 'basic'} );
            
            
            // this gets the basic layer from the modeling env.  Important to set this to "transparent", otherwise, the base layer
            // will be invisible 
            // this layer shows the political boundaries (poli_bounds), the segment groupings (proj_seggroups), and points (proj_points)
            //layer = new OpenLayers.Layer.WMS( "Groups",
            //        url, {layers: ['proj_seggroups', 'proj_points'],transparent:true});
            layer = new OpenLayers.Layer.WMS( "Groups",
                    url, {
                       layers: ['huc_va','proj_seggroups', 'proj_points','vwuds_measuring_points'],
                       transparent:true 
                    }, {visibility:false});
                    
            player = new OpenLayers.Layer.WMS( "Avg. Rainfall",
                    url, {layers: ['precip_nml'],transparent:true}, {visibility:false});
                    
            nhdlayer = new OpenLayers.Layer.WMS( "NHD Streams",
                    url, {layers: ['nhd_rivers'],transparent:true}, {visibility:false});
                    
            huclayer = new OpenLayers.Layer.WMS( "HUC8 Basins",
                    url, {layers: ['huc_va'],transparent:true}, {visibility:false});
                    
            cbp5_hydro = new OpenLayers.Layer.WMS( "CBP5 Model Hydro",
                    url, {layers: ['cbp5_hydro'],transparent:true}, {visibility:false});
            
            // show nexrad data
            //ia_wms = new OpenLayers.Layer.WMS("Nexrad","http://mesonet.agron.iastate.edu/cgi-bin/wms/nexrad/n0r.cgi?",{layers:"nexrad-n0r-wmst",transparent:true,format:'image/png',time:OpenLayers.Util.getElement('time').value});
            
            // Get the scenariod ID for use later
            var scenarioid = document.forms["elementtree"].elements.scenarioid.value;
            var wfsurl = url + '&scenarioid=' + scenarioid;

            // this layer shows the political boundaries (poli_bounds), the segment groupings (proj_seggroups), and points (proj_points)
            slayer = new OpenLayers.Layer.WFS( "EDWrD Measuring Points",
                    wfsurl, {typename: 'vwuds_measuring_points', extractAttributes: true,transparent:true},
               {
                      typename: 'vwuds_measuring_points',
                      featureNS: 'http://www.openplans.org/topp',
                      extractAttributes: true
               } );
            
            flayer = new OpenLayers.Layer.WFS( "Model Elements", 
                    wfsurl, 
                    { typename: 'model_elem_poly', extractAttributes: true },
               {
                      typename: 'Model Elements',
                      featureNS: 'http://www.openplans.org/topp',
                      extractAttributes: true, 
                      visibility: false
               } );
               
            // SSURGO map unit layer - shown only for reference, not yet added to the map
            // Will probably have to add this to the Mapserver file, in order to re-project into google maps SRID
            var ssurgourl = "http://sdmdataaccess.nrcs.usda.gov/Spatial/SDMNAD83GEOGRAPHIC.wfs?"
            
            ssurgolayer = new OpenLayers.Layer.WFS( "SSURGO Soils", 
                    ssurgourl, 
                    { typename: 'MapunitPoly', extractAttributes: true },
               {
                      typename: 'SSURGO Soils',
                      featureNS: 'http://www.openplans.org/topp',
                      extractAttributes: true
               } );
            
            
            //map.addLayer(layer);
            
            // create the "scratch" layer to add shapes into
            vlayer = new OpenLayers.Layer.Vector( "Editable" );
            
            
            // Use the standard Base Layer
            //map.addLayers([ layer, blayer, vlayer, flayer ]);
            // Use the  Google Base Layers
            map.addLayers([gphy, gmap, layer, vlayer, flayer]);
            // Use the  Google Base Layers and only the editable layer
            //map.addLayers([gphy, gmap, layer, flayer]);
            // All google layers
            // show Measuring Points (slayer)
            //map.addLayers([gphy, gmap, ghyb, gsat, nhdlayer, player, layer, slayer, vlayer]);
            // Show model elements (flayer)
            //map.addLayers([gphy, gmap, ghyb, gsat, nhdlayer, player, layer, flayer, vlayer, cbp5_hydro, huclayer]);
            
            //map.addLayers([ layer, blayer, slayer, vlayer, flayer ]);
            //map.addLayers([  layer, vlayer ]);
            // show nexrad weather, very cool.
            //map.addLayers([blayer, vlayer, ia_wms]);
            
            
            // BEGINNING OF EDITING COMPONENTS - Menu
            // add editing buttons and capability
            // the edit toolbar has the point, poly and line buttons and tools already loaded
            //OpenLayers.Feature.Vector.id = '<b>Test Text</b>';
            vlayer.preFeatureInsert =  function(feature) {
                // do something with the feature
                feature.id = '<b>Test Feature text</b>';
            }; 
            editToolBar = new OpenLayers.Control.EditingToolbar(vlayer);
            
            // BEGIN - add select controls
            // create a select control that points to the vlayer
            selControl = new OpenLayers.Control.SelectFeature(vlayer,
                {onSelect: onFeatureSelect, onUnselect: onFeatureUnselect});
            // specify the button image to be used (reference to style in styles.css)
            selControl.displayClass = 'olControlSelectFeature'; 
            // add the select control to the toolbar
            //alert('adding select button');
            extraControls = [ selControl ];
            editToolBar.addControls(extraControls); 
            // Add another control to be used only in the background
            selModelControl = new OpenLayers.Control.SelectFeature(flayer);
            // specify the button image to be used (reference to style in styles.css)
            selModelControl.displayClass = 'olControlSelectFeature'; 
            // add the select control to the toolbar
            //alert('adding select button');
            // END - add select controls
            
            // now, add the tool bar to the map
            map.addControl(editToolBar);
            map.setCenter(new OpenLayers.LonLat(lon, lat), zoom);
            map.zoomToExtent(mapbounds);
            
            // END OF EDITING COMPONENTS

            var size = new OpenLayers.Size(10,17);
            var offset = new OpenLayers.Pixel(-(size.w/2), -size.h);
            
            map.addControl( new OpenLayers.Control.LayerSwitcher() );
            
            OpenLayers.Util.getElement('controlpanel').innerHTML = 'Test Text';
        }
        
        
        function onPopupClose(evt) {
            selControl.unselect(selectedFeature);
        }
        
        function onFeatureSelect(feature) {
            selectedFeature = feature;
            popup = new OpenLayers.Popup.FramedCloud("chicken", 
                                     feature.geometry.getBounds().getCenterLonLat(),
                                     null,
                                     "<div style='font-size:.8em'>" + feature.id +"</div>",
                                     null, true, onPopupClose);
            feature.popup = popup;
            map.addPopup(popup);
        }
        
        function removeAllPopups() {
            //h = map.popups.length;
            //for (i = 0; i < h; i++) {}
            //map.addPopup(popup);
        }
        
        function onFeatureUnselect(feature) {
            map.removePopup(feature.popup);
            feature.popup.destroy();
            feature.popup = null;
        }    
        
        function getScratchShapes() {
           
          // assumes that vlayer is set in globals
          var wktObj = new OpenLayers.Format.WKT;
          pParams = '';

          for(var i=0; i<vlayer.features.length; ++i) {
             var wktdata = wktObj.write(vlayer.features[i]);
             pParams += "&WKTDATA[";
             pParams += i;
             pParams += "]=";
             pParams += wktdata;
          }
          
          return pParams;

        }
        
        function getScratchGeometry(n) {
           
          // assumes that vlayer is set in globals
          // returns n'th shape in the layer, or '' if n'th does not exist
          var wktObj = new OpenLayers.Format.WKT;
          thisgeom = null;

          if (vlayer.features[n]) {
             thisgeom = vlayer.features[n].geometry;
          }
          
          return thisgeom;

        }
        
        function getSelectedScratchGeom() {
           
          // assumes that vlayer is set in globals
          // returns the first selected shape, or the0'th shape in the layer if none are selected
          var wktObj = new OpenLayers.Format.WKT;
          thisgeom = null;

          if (vlayer.selectedFeatures.length > 0) {
             thisgeom = vlayer.selectedFeatures[0].geometry;
             n = -1;
          } else {
             n = 0;
             thisgeom = getScratchGeometry(n);
          }
          
          //alert("Geometry " + n + " selected = " + thisgeom);
          
          return thisgeom;

        }
        
        function getScratchGmapGeom() {
           thisgeom = getSelectedScratchGeom();
           
           Proj4js.defs["EPSG:4326"] = "+proj=longlat +ellps=WGS84 +datum=WGS84 +no_defs";
           Proj4js.defs["EPSG:900913"] = "+proj=merc +a=6378137 +b=6378137 +lat_ts=0.0 +lon_0=0.0 +x_0=0.0 +y_0=0 +k=1.0 +units=m +nadgrids=@null +no_defs";
          
           var proj4326 = new OpenLayers.Projection("EPSG:4326");
           var projGoogle = new OpenLayers.Projection("EPSG:900913");
          
           thisgeom.transform(projGoogle, proj4326);
           return thisgeom;
        }
       
        
        function getScratchShape(n) {
           
          // assumes that vlayer is set in globals
          // returns n'th shape in the layer, or '' if n'th does not exist
          var wktObj = new OpenLayers.Format.WKT;
          pParams = '';

          if (vlayer.features[n]) {
             pParams = wktObj.write(vlayer.features[n]);
          }
          
          return pParams;

        }
        
        function getSelectedScratchShapes() {
           
          // assumes that vlayer is set in globals
          // returns the first selected shape, or the0'th shape in the layer if none are selected
          var wktObj = new OpenLayers.Format.WKT;
          pParams = '';

          if (vlayer.selectedFeatures.length > 0) {
             n = vlayer.selectedFeatures[0];
          } else {
             n = 0;
          }
          
          pParams = getScratchShape(n);
          
          return pParams;

        }
        
        function clearSelectedScratchShapes() {
           
          // assumes that vlayer is set in globals
          // removes n'th shape in the layer if it exists

          vlayer.removeFeatures(vlayer.selectedFeatures);
          //pParams = wktObj.write(vlayer.features[n]);
          var sc = flayer.features[0].style['default'].strokeColor;
          alert ("Stroke Color: " + sc);
          
        }
        
        function clearAllScratchShapes() {
           
          // assumes that vlayer is set in globals
          // removes n'th shape in the layer if it exists
          for (i = 0; i < vlayer.features.length; i++) {
             if (vlayer.features[i].popup) {
                map.removePopup(vlayer.features[i].popup);
                vlayer.features[i].popup.destroy();
                vlayer.features[i].popup = null;
             }
          }

          vlayer.removeFeatures(vlayer.features);
          //var sc = flayer.features[0].style['default'].strokeColor;
          //alert ("Stroke Color: " + sc);
          
        }
        
        function selectModelShapes(elementid) {
           
          // assumes that vlayer is set in globals
          // removes n'th shape in the layer if it exists
          selModelControl.unselectAll();
          for (var i=0; i<flayer.features.length; ++i) {
             var ff = flayer.features[i]; 
             var ftext = "Selecting " + ff.attributes.elementid + " - " + ff.attributes.elemname;
             if (ff.attributes.elementid == elementid) {
                alert(ftext);
                selModelControl.select(ff);
             } 
          }
       }
       
       function latlon2gmaps(lon, lat) {
          Proj4js.defs["EPSG:4326"] = "+proj=longlat +ellps=WGS84 +datum=WGS84 +no_defs";
          Proj4js.defs["EPSG:900913"] = "+proj=merc +a=6378137 +b=6378137 +lat_ts=0.0 +lon_0=0.0 +x_0=0.0 +y_0=0 +k=1.0 +units=m +nadgrids=@null +no_defs";
          
          var proj4326 = new OpenLayers.Projection("EPSG:4326");
          var projGoogle = new OpenLayers.Projection("EPSG:900913");
          
          point = new OpenLayers.Geometry.Point(lon, lat);
          OpenLayers.Projection.transform(point, proj4326, projGoogle);
          return point;
       }
       
       function gmapZoom(x1, y1, x2, y2) {
          e1 = latlon2gmaps(x1, y1);
          e2 = latlon2gmaps(x2, y2);
          newbounds = new OpenLayers.Bounds(e1.x, e1.y, e2.x, e2.y);
          // assumes that the variable map is global and accessible
          //alert("Zooming to " + e1.x + " " + e1.y + ", " + e2.x + " " + e2.y);
          map.zoomToExtent(newbounds);
       }
       
       function putWKTShape(WKTgeom) {
          //alert(WKTgeom);
          clearAllScratchShapes();
          for (i = 0; i < WKTgeom.length; i++) {
             setScratchShape(WKTgeom[i][0], WKTgeom[i][1]);
          }
       }
       
       function putWKTGoogleShape(WKTgeom) {
          //alert(WKTgeom);
          clearAllScratchShapes();
          for (i = 0; i < WKTgeom.length; i++) {
             setScratchGoogleShape(WKTgeom[i][0], WKTgeom[i][1]);
          }
       }
        
        function setScratchShape(WKTgeom, shapeID) {
           
          // assumes that vlayer is set in globals
          // returns the first selected shape, or the0'th shape in the layer if none are selected
          var wktObj = new OpenLayers.Format.WKT;
          var wktfeatures = wktObj.read(WKTgeom);
          wktfeatures.id = shapeID;
          //var backatya = wktObj.write(wktfeatures);
          //alert(backatya);
          // single WKT feature
          //var feature = new OpenLayers.Feature.Vector(wktfeatures);
          vlayer.addFeatures(wktfeatures);
          
          vlayer.redraw();
          // END single feature

        }
        
        function setScratchGoogleShape(WKTgeom,shapeID) {
           
          // assumes that vlayer is set in globals
          // set up projection info
          Proj4js.defs["EPSG:4326"] = "+proj=longlat +ellps=WGS84 +datum=WGS84 +no_defs";
          Proj4js.defs["EPSG:900913"] = "+proj=merc +a=6378137 +b=6378137 +lat_ts=0.0 +lon_0=0.0 +x_0=0.0 +y_0=0 +k=1.0 +units=m +nadgrids=@null +no_defs";

          var proj4326 = new OpenLayers.Projection("EPSG:4326");
          var projGoogle = new OpenLayers.Projection("EPSG:900913");
          
          
          var wktObj = new OpenLayers.Format.WKT;
          wktObj.internalProjection = projGoogle;
          wktObj.externalProjection = proj4326;
          
          var wktfeatures = wktObj.read(WKTgeom);
          wktfeatures.id = shapeID;
          alert(shapeID);
          vlayer.addFeatures(wktfeatures);
          
          //var backatya = wktObj.write(wktfeatures);
          //alert(backatya);
          
          vlayer.redraw();

        }



