!/bin/sh

#filename='/var/www/html/files/vahydro/facs-copy-template.txt'
#filename='/var/www/html/files/vahydro/facs-copy-template-short.txt'
filename='/var/www/html/files/vahydro/facs-copy-template-remainder.txt'
for i in `cat $filename`; do
  drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties 4988636 dh_properties $i ps_enabled;
  drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties 4988636 dh_properties $i fac_demand_mgy;   
  drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties 4988636 dh_properties $i wd_mgd;   
  drush scr modules/om/src/om_copy_subcomp.php cmd dh_properties 4988636 dh_properties $i discharge_mgd; 
done
