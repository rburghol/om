#!/bin/sh
# $1 - scenario name
# $2 - landuse
# $3 - land segment
# $4 - DSN (111 - SURO, 211 - IFWO, 411 - AGWO)
rm /tmp/error.fil
rm /tmp/error-output.txt
php cbpdump_wdm-land.php "$1" "$2" "$3" "$4" 
rm /tmp/error.fil
rm /tmp/error-output.txt

