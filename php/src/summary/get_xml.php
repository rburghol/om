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
$rss = new UniversalFeedCreator(); 
$rss->useCached(); // use cached version if age<1 hour
$rss->title = "PHP news"; 
$rss->description = "daily news from the PHP scripting world"; 

//optional
$rss->descriptionTruncSize = 500;
$rss->descriptionHtmlSyndicated = true;

$rss->link = "http://www.dailyphp.net/news"; 
$rss->syndicationURL = "http://www.dailyphp.net/".$_SERVER["PHP_SELF"]; 

$image = new FeedImage(); 
$image->title = "Water Use Graph"; 
$image->url = "http://deq2.bse.vt.edu/tmp/total_mgd.0d4a27ae440b17a0d365d1a36ec241a6.png"; 
$image->link = "http://deq2.bse.vt.edu"; 
$image->description = "Image of water use."; 

//optional
$image->descriptionTruncSize = 500;
$image->descriptionHtmlSyndicated = true;

$rss->image = $image; 


    $item = new FeedItem(); 
    $item->title = "Test Feed"; 
    $item->link = 'http://deq2.bse.vt.edu/wooommdev/summary/get_xml.php'; 
    $item->description = 'This is a test'; 
    
    //optional
    $item->descriptionTruncSize = 500;
    $item->descriptionHtmlSyndicated = true;

    $item->date = $data->newsdate; 
    $item->source = "http://www.dailyphp.net"; 
    $item->author = "John Doe"; 
     
    $rss->addItem($item); 

// valid format strings are: RSS0.91, RSS1.0, RSS2.0, PIE0.1 (deprecated),
// MBOX, OPML, ATOM, ATOM0.3, HTML, JS
      $outstring = $rss->createFeed("2.0");

print($outstring);
?>