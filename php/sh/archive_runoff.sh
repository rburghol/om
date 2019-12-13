#!/bin/sh

# Rename, move, replace Point Source Folders with a timeseries cache of old model runs

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
templateid=347405
archive=347403
newscen=30
basedir="/media/NAS/omdata"

# create a clone of the new element desired
cd /var/www/html/om 

php setprop.php $oldelement name="PS Elements:$riverseg"
php fn_addObjectLink.php $oldelement $archive

# Finally, create a replacement and set the appropriate file name 
newid=`php fn_copy_element.php 37 $templateid $parentid`
# set the "remote_url" property, even though this is not remote, 
# it allows us to set a path and avoid filenice browser which should be deprecated anyhow.
newfilename="$basedir/p6/vahydro/runoff/ps.${riverseg}.2010.2.log"

sudo cp $srcfile $newfilename 
php setprop.php $newid name="PS TimeSeries:$riverseg"
php setprop.php $newid remote_url=$newfilename
