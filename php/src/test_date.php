<?php

$indate = '01/31/08 21:00:00';
$s2ts = strtotime($indate);
$outdate = date('U', $s2ts);
$rdate = date('r', $s2ts);

print("$indate \n");
print("$s2ts \n");
print("$outdate \n");
print("$rdate \n");



$indate = 'today + 1 day';
$s2ts = strtotime($indate);
$outdate = date('U', $s2ts);
$rdate = date('r', $s2ts);
$thistime = new DateTime($indate);
$dtdate = $thistime->format('r');

print("$indate \n");
print("$s2ts \n");
print("$outdate \n");
print("$rdate \n");
print("$dtdate \n");



?>