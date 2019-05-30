#!/bin/sh
# $1 - scenario name
# $2 - land segment
rm /tmp/error.fil
rm /tmp/error-output.txt
php cbpdump_wdm-land.php "$1" "" "$2" 111 
php cbpdump_wdm-land.php "$1" "" "$2" 211 
php cbpdump_wdm-land.php "$1" "" "$2" 411 
rm /tmp/error.fil
rm /tmp/error-output.txt

