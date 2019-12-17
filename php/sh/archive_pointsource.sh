#!/bin/bash

# Rename, move, replace Point Source Folders with a timeseries cache of old model runs
templateid=347395
archive=347396
newscen=30
basedir="/media/NAS/omdata"

# Inputs:
#   - riverseg ID
#   - 1. Withdrawals container elementid
#   - Old point source container elementid 
#   - template ID for new
# test: RU4_5640_6030 257477 257479 
riverseg=$1
parentid=$2
oldelement=$3
srcfile=$4
if [ $# -lt 4 ]; then
  echo 1>&2 "Usage: archive_runoff.sh riverseg parentid oldelement srcfile [template$template]"
  exit 2
fi 

# create a clone of the new element desired
cd /var/www/html/om 

php setprop.php $oldelement name="PS Elements:$riverseg"
php fn_addObjectLink.php $oldelement $archive

# Finally, create a replacement and set the appropriate file name 
newid=`php fn_copy_element.php 37 $templateid $parentid`
# set the "remote_url" property, even though this is not remote, 
# it allows us to set a path and avoid filenice browser which should be deprecated anyhow.
newfilename="$basedir/p6/vahydro/point_source/ps.${riverseg}.2010.vahydro.log"

sudo cp $srcfile $newfilename 
php setprop.php $newid name="PS TimeSeries:$riverseg"
php setprop.php $newid remote_url=$newfilename
