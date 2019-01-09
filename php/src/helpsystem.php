<html>
<?php

print("<head>");
include("./medit_header.php");
print("</head>");
print("<body bgcolor=ffffff onload=\"init()\">");

#########################################
# Print Headers
#########################################
include("./medit_menu.php");
#########################################
# END - Print Headers
#########################################
print("<table>");
print("<tr>");
print("   <td valign=top width=800>");
print("<form action='$scriptname' method=post name='activemap' id='activemap'>");
# show project navigation controls
include('medit_controls.php');
# make all layers visible
include('./medit_layers.php');

print("<br><b>Help Topics:</b><br>");
print("<table width=100% border=1><tr>");
print("<td valign=top bgcolor=#E2EFF5><b>Mail Help and Listservs</b>");
print("<ul style='list-style-type: circle'>");
print("<li><a href='https://lists.sourceforge.net/lists/listinfo/npsource-wooomm' target='_new'>Visit Mailing List for WOOOMM System</a>");
print("<li><a href='mailto:rburghol@vt.edu'>Send mail to sysadmin - rburghol@vt.edu</a>");
print("</ul>");
print("</td>");
print("<td valign=top bgcolor=#E2EFF5><b>Tutorials and Sample Model Descriptions</b>");
print("<ul style='list-style-type: circle'>");
print("<li><a href='./doc/tutorial_02.html' target='_new'>Coupling with the Chesapeake Bay Program Phase 5 Watershed Model</a>");
print("<li><a href='./doc/sample_models.html' target='_new'>Sample Model Descriptions and Output</a>");
print("<li><a href='./doc/tutorials.html' target='_new'>Introducing WOOOMM - Using the Lotka-Volterra Predator-Prey Model</a>");
print("</ul>");
print("</td>");
print("</tr></table>");
#showSubmitButton('changefunction','Change Function');
print("</form>");
print("<hr>");

print("<table width=100% border=1><tr>");
print("<td valign=top bgcolor=#E2EFF5>");
print("\n<div id='controlpanel' bgcolor='lightgrey'>");
print("</div>\n");
print("</td>");
print("</tr></table>");


print("   </td>");
print("   <td valign=top width=350>");

include('medit_controlfooter.php');

print("   </td>");
print("</tr>");

print("<tr>");
print("   <td colspan=2>");
print("\n<div id='workspace'></div>\n");

print("   </td>");
print("</tr>");
print("</table>");

?>

</body>
</html>
