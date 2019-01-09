<?php

print("<script language='JavaScript' src='/scripts/scripts.js'>");
print("</script>");
# include open layers stuff
print("<script src='$liburl/OpenLayers-2.7/OpenLayers.js'></script>");
#print("<script src='$scripturl/proj4js/lib/proj4js.js'></script>");
#print("<script src='$scripturl/proj4js/lib/projCode/merc.js'></script>");
print("<script src='$scripturl/proj4js/proj4js.js'></script>");
print("<script src='$scripturl/proj4js/merc.js'></script>");

print("<style type=\"text/css\">");
print("  #map {");
print("      width: 600px;");
print("      height: 400px;");
print("      border: 1px solid black;");
print("  }");
print("</style>");

/*
print("<script type=\"text/javascript\">");
print("function setFNFunctions ($thiselem) { ");
print("var o = new com.filenice.actions;");
#print("alert('Executing Javascript');");
print("o.setFunctions(document.getElementById(\"$thiselem\"));");
print("} ");
#print("setFNFunctions(); ");
print("</script>");
*/
#$debug = 1;
if ($debug) {
   print("Showing FileNice Headers<br>");
}
if (is_object($fno)) {
   echo $fno->showFNHeaders();
}
print("<link href=\"/styles/clmenu.css\" type=\"text/css\" rel=\"stylesheet\" />");
print("<link href=\"/styles/xajaxGrid.css\" type=\"text/css\" rel=\"stylesheet\" />");
#print("</head>");
?>
