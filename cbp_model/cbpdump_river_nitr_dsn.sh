#!/bin/sh
# $1 - scenario name
# $2 - landuse
# $3 - land segment

# import all stream nitrpogen outflows for a single CBP land segment
for i in 142 144 244 444 145 245 445 141 146 246 446 143 147 247 447; do
   echo "Importing DSN $i $1 $2 $3"
   ./copy_land_dsn.sh $1 $2 $3 $i
done 
