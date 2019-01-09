<?php
if (isset($_GET['format'])) {
   $format = $_GET['format'];
} else {
   $format = 'html';
}
$runid = 0;
$scenarioid = 37;
$noajax = 1;
include('./config.php');
error_reporting(E_ERROR);

if (isset($_GET['latdd'])) {
   $latdd = $_GET['latdd'];
}
if (isset($_GET['londd'])) {
   $londd = $_GET['londd'];
} else {
   print("Usage: get_pointWDs.php?latdd=XXX&londd=YYY<br>Quitting.");
   //print("</body>");
   die;
}
switch ($format) {
   case 'html':
   $outstring = "<html>";
   $outstring .= "<head>";
   $outstring .= "<script language='JavaScript' src='/scripts/scripts.js'>;";
   $outstring .= "</script>";
   $outstring .= "<link href='styles/clmenu.css' type='text/css' rel='stylesheet'>";
   $outstring .= "<link href='/styles/xajaxGrid.css' type='text/css' rel='stylesheet'>";
   $outstring .= "</head><body>";
   $outstring .= "<i>Enter your location to retrieve point source discharges</i> <br>";
   $outstring .= "<form action='$scriptname' method=get>";
   $outstring .= "<b>Latitude:</b> ";
   $outstring .= showWidthTextField('latdd', $latdd, 12, '', 1);
   $outstring .= "&nbsp;&nbsp;<b>Longitude:</b> ";
   $outstring .= showWidthTextField('londd', $londd, 12, '', 1);
   $outstring .= showSubmitButton('submit','submit','',1);
   $outstring .= "</form>";
   break;
   
   case 'xml':
   //create xml object
   require_once("$libdir/feedcreator/feedcreator.class.php");
   // make sure the cache is cleared
   // shouldn't do this, as I think this is actually used by magpie, NOT feedcreator
   //shell_exec("rm ../rsscache/* -f");
   $rss = new UniversalFeedCreator();
   //$rss->useCached();
   $rss->title = "Withdrawal Information";
   $rss->syndicationURL = 'http://' . $_SERVER['SERVER_NAME'] . "/" . $PHP_SELF;
   //$rss->xslStyleSheet = 'http://' . $_SERVER['SERVER_NAME'] . "/styles/rss.css";
   break;
}


$geoscope = 'cumulative';

$riverseg = getCOVACBPPointContainer($listobject, $latdd, $londd);
$elementid = getCOVACBPContainer($listobject, $scenarioid, $riverseg) ;

$pcnt = array('custom1'=>array('cova_ws_container'));
$mpc = 2;
$ncnt = array('cova_ws_container', 'cova_ws_subnodal');
$ccnt = 'cova_ws_container';
$uscont_id = getCOVAUpstream($listobject, $elementid, $debug);
$trib_contid = getCOVATribs($listobject, $elementid);

$wkt_segs = getCOVASegments($elementid, $ncnt, $geoscope);
//print("getCOVASegments($elementid, $ncnt); = " . print_r($wkt_segs,1) . " <br>");
$wkt = getMergedCOVAShape($scenarioid, $listobject, $wkt_segs);


$anwd_recs = getTotalAnnualSurfaceCATWithdrawalByWKT($vwuds_listobject, ( intval(date('Y')) - 10), date('Y'), $wkt, $debug, array('PH'));
$base_query = $anwd_recs['query'];
$vwuds_listobject->querystring = "create temp table tmp_vwuds_cross$elementid as select * from ($base_query) as foo where totalannual is not null";
$vwuds_listobject->performQuery();
$by_yearcat = doGenericCrossTab ($vwuds_listobject, "tmp_vwuds_cross$elementid", 'thisyear', 'category', 'totalannual', 1, 1);
//$outstring .= "$by_yearcat <br>";
$vwuds_listobject->querystring = $by_yearcat;
$vwuds_listobject->performQuery();
$vwuds_listobject->show = 0;
$vwuds_listobject->showList();
$anwd_recs = $vwuds_listobject->queryrecords;
switch ($format) {
   case 'html':
      $outstring .= "<table><tr>";
      $outstring .= "<td>Recent reported Surface Withdrawals: <br>Method 1:" . $vwuds_listobject->outstring . "</td>";
   break;
   
   case 'xml':
      $rss->link = 'http://' . $_SERVER['SERVER_NAME'] . "/" . $PHP_SELF;
      $item = new FeedItem();
      $item->title = "Withrawl records for lat = $latdd, lon = $londd";
      $item->link = 'http://' . $_SERVER['SERVER_NAME'] . "/" . $PHP_SELF;
      $item->description = "Annual Withdrawal Records";
      //foreach ($anwd_recs['annual_records'] as $thisrec) {
      //   $p[$thisrec['thisyear']] = $thisrec['total_mgd'];
      //}
      //$item->additionalElements = array('Annual Reported' => $vwuds_listobject->outstring);
   break;
}
$colors = array('red','green', 'blue', 'brown', 'purple', 'orange', 'black', 'pink');
$i = 0;
foreach (array_keys($anwd_recs[0]) as $cols) {
   if ($cols <> 'thisyear') {
      $graph = array();
      $graph['graphrecs'] = $anwd_recs;
      $graph['xcol'] = 'thisyear';
      $graph['ycol'] = $cols;
      $graph['yaxis'] = 1;
      $graph['plottype'] = 'bar';
      $graph['color'] = $colors[$i];
      $graph['ylegend'] = $cols;
      $bgs[] = $graph;
      $i++;
   }
}
//$outstring .= "Graph Recs: " . print_r($bgs, 1) . "<br>";
$multibar = array(
   'title'=> "Annual Withdrawals by Category",
   'xlabel'=>'Year',
   'ylabel'=>'Mean Withdrawal (MGD)',
   'num_xlabels'=>15,
   'gwidth'=>600,
   'gheight'=>400,
   'overlapping'=>0,
   'labelangle'=>90,
   'randomname'=>0,
   'legendlayout'=>LEGEND_HOR,
   'legendpos'=>array(0.20,0.95,'left','bottom'),
   'base
name'=>"wd_$elementid",
   'bargraphs'=>$bgs
);

$graphurl = showGenericMultiPlot($goutdir, $goutpath, $multibar, $debug);



switch ($format) {
   case 'html':
      $outstring .= "<td>Graph: <br><img src='$graphurl'></td>";
      $outstring .= "</tr></table>";
      $outstring .= "</body></html>";
   break;
   
   case 'xml':
      $image = new FeedImage(); 
      $image->title = "Water Use Graph"; 
      $image->url = 'http://' . $_SERVER['SERVER_NAME'] . "$graphurl"; 
      $image->link = 'http://' . trim($_SERVER['SERVER_NAME']); 
      $image->description = "Image of water use."; 

      //optional
      //$image->descriptionTruncSize = 500;
      //$image->descriptionHtmlSyndicated = true;

      $rss->image = $image; 
      $rss->addItem($item);
      $outstring = $rss->createFeed("2.0");
   break;
}


print($outstring);
?>