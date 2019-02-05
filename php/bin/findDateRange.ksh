#!/bin/ksh
#################################################
## File: findDateRange.sh
## Date: May 27, 2008
## Author: Saurav Sen
## Purpose: A script to find the files within
## a given date range
#################################################
fpath=$1
strtdt=$2
enddt=$3
touch -t ${strtdt}0000 /tmp/newerstart
touch -t ${enddt}2359 /tmp/newerend
#find ./ \( -newer /tmp/newerstart -a \! -newer /tmp/newerend \) -print
find $fpath \( -newer /tmp/newerstart -a \! -newer /tmp/newerend \) -exec ls -l {} \;
