#!/bin/sh

# Work in progress.  Should create a timeSeriesFile element and link back to some other
# full model object of a given source run.  If the timeSeriesFile element already exists
# based on custom2 entry, simply update the timeseries file since we are storing these 
# in a cached location in the model tree.  
# Alternative: link directly to the "live" model runfile on the remote element and avoid 
#   having to re-do this.

templateid=347405
archive=347403
newscen=30
basedir="/media/NAS/omdata"

if [ $# -lt 4 ]; then
  echo 1>&2 "Usage: archive_runoff.sh riverseg parentid oldelement srcfile [template$template]"
  exit 2
fi 
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

# create a clone of the new element desired
cd /var/www/html/om 

# Finally, create a timeSeriesFile element and set the appropriate file name 
newid=`php fn_copy_element.php 37 $templateid $parentid`
# set the "remote_url" property, even though this is not remote, 
# it allows us to set a path and avoid filenice browser which should be deprecated anyhow.
newfilename="$basedir/p6/vahydro/runoff/${riverseg}.vahydro.cbp532.log"

sudo cp $srcfile $newfilename 
php setprop.php $newid name="VAHydro 1.0/CBP5.3.2:$riverseg"
php setprop.php $newid remote_url=$newfilename
php setprop.php $newid custom2=$riverseg
