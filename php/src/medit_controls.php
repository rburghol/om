<?php
print("<table>");
print("<tr>");
print("<td valign=top colspan=3>");
showHiddenField('lastgroup', $currentgroup);
showHiddenField('seglist', $seglist);

#print("<b>Select a Project:</b><br>");
$projclause = "projectid = $projectid ";
#$debug = 1;
# hide this in favor of a static, 1 project view
#showActiveList($listobject, 'projectid', 'project', 'projectname', 'projectid','', $projectid, 'submit()', 'projectname', $debug);
showHiddenField('projectid', $projectid);
# if this was an update form that got cancelled by a button click,
# we need to deal with the year variable, and set it right
if (is_array($thisyear)) {
   $thisyear = $lastyear;
}
#print("<input type=IMAGE SRC='$amap->images/icon_redraw.gif' WIDTH='19' HEIGHT='19' NAME='Refresh' VALUE='Refresh' BORDER=0>Refresh Map\n");
$ext = $amap->extent_to_html;
print("<INPUT TYPE=HIDDEN NAME='extent' VALUE='$ext'>");
print("</td></tr>");
print("<tr><td valign=top >");
print("<b>Select a Model Domain:</b><br>");
$scensql = " ( (select $defscenarioid as scenarioid, 'None' as scenario) ";
$scensql .= "  UNION ";
$scensql .= "  ( select scenarioid, scenario from scenario ";
$scensql .= "    where projectid = $projectid and ( (ownerid = $userid  and operms >= 4) ";
$scensql .= "       or ( groupid in ($usergroupids) and gperms >= 4 ) ";
$scensql .= "       or (pperms >= 4) ) ";
$scensql .= "    order by scenario ) ";
$scensql .= " ) as foo ";
#$debug = 1;
#print("$scensql <br>");
#showList($listobject, 'scenarioid', $scensql, 'scenario', 'scenarioid', '', $scenarioid, $debug);
#$scenjs = "document.forms['addelement'].elements.scenarioid.value=document.forms['elementbrowser'].elements.showoutside.value;";
showActiveList($listobject, 'scenarioid', $scensql, 'scenario', 'scenarioid','', $scenarioid, $scenjs, 'scenario', $debug);

print("</td>");
print("<td valign=top>&nbsp;");

print("</td>");
print("<td valign=top>&nbsp;");
#$amap->showLegend();
print("</td>");
print("</tr></table>");
?>