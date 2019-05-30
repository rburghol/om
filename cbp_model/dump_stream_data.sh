#!/bin/sh

for i in `ls /opt/model/p53/tmp/wdm/river/p53sova/eos/J* | grep -v ps | cut -d'_' -f 2`; do
   php cbpdump_river_wdm.php $i 1000
done

