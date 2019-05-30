#!/bin/sh

for i in A37001 A37033 A51083 A51037 B51019 A51690 B51161 A37171 A51770 A37081 A51117 A37185; do
   for j in nlo; do
      echo "./copy_land_dsn.sh p53sova $j $i 411"
      ./copy_land_dsn.sh p53sova $j $i 411
   done
done
