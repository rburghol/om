<?php

include("/var/www/html/devlib/XmlReader.php");

$url = "http://deq1.bse.vt.edu/wooommdev/remote/rss_cbp_land_data.php?actiontype=4&startdate=2001-01-01=&enddate=2001-01-06&id1=land&id2=C51015&timestep=43200";
$simple = file_get_contents($url);


$xml = simplexml_load_file($url);
$itemar = $xml->channel->item;
foreach ($itemar as $thisitem) {
   print_r((array)$thisitem);
}

?>
