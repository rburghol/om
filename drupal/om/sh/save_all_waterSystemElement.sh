#!/bin/sh

i=$1

drush scr modules/om/src/om_saveprop.php cmd dh_properties $i adj_demand_mgd;
drush scr modules/om/src/om_saveprop.php cmd dh_properties $i available_mgd;
drush scr modules/om/src/om_saveprop.php cmd dh_properties $i base_demand_mgd;
drush scr modules/om/src/om_saveprop.php cmd dh_properties $i consumption;
drush scr modules/om/src/om_saveprop.php cmd dh_properties $i current_mgy;
drush scr modules/om/src/om_saveprop.php cmd dh_properties $i discharge_mgd;
drush scr modules/om/src/om_saveprop.php cmd dh_properties $i drought_pct;
drush scr modules/om/src/om_saveprop.php cmd dh_properties $i drought_response_enabled;
drush scr modules/om/src/om_saveprop.php cmd dh_properties $i fac_current_mgy;
drush scr modules/om/src/om_saveprop.php cmd dh_properties $i fac_demand_mgy;
drush scr modules/om/src/om_saveprop.php cmd dh_properties $i fac_demand_mgd;
drush scr modules/om/src/om_saveprop.php cmd dh_properties $i flowby;
drush scr modules/om/src/om_saveprop.php cmd dh_properties $i historic_monthly_pct;
drush scr modules/om/src/om_saveprop.php cmd dh_properties $i max_mgd;
drush scr modules/om/src/om_saveprop.php cmd dh_properties $i ps_enabled;
drush scr modules/om/src/om_saveprop.php cmd dh_properties $i Qintake;
drush scr modules/om/src/om_saveprop.php cmd dh_properties $i riverseg_frac;
drush scr modules/om/src/om_saveprop.php cmd dh_properties $i unaccounted_losses;
drush scr modules/om/src/om_saveprop.php cmd dh_properties $i unmet_demand_mgd
drush scr modules/om/src/om_saveprop.php cmd dh_properties $i vwp_exempt_mgd;
drush scr modules/om/src/om_saveprop.php cmd dh_properties $i wd_mgd;
drush scr modules/om/src/om_saveprop.php cmd dh_properties $i wsp2020_2020_mgy
drush scr modules/om/src/om_saveprop.php cmd dh_properties $i wsp2020_2030_mgy
drush scr modules/om/src/om_saveprop.php cmd dh_properties $i wsp2020_2040_mgy

