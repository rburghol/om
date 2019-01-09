
          for (var i=0; i<flayer.features.length; ++i) {
             var ff = flayer.features[i]; 
             var ftext = ff.attributes.elementid + " - " + ff.attributes.elemname;
             if (ff.attributes.elementid == elementid) {
                alert(ftext);
                break;
             }
          }
