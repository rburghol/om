#----------------------------------------------
site <- "http://deq2.bse.vt.edu/d.dh"    #Specify the site of interest, either d.bet OR d.dh
#----------------------------------------------
# Load Libraries
basepath='/var/www/R';
source(paste(basepath,'config.R',sep='/'))
library(stringr)
# dirs/URLs
save_directory <- "/var/www/html/data/proj3/out"
save_url <- paste(str_remove(site, 'd.dh'), "data/proj3/out", sep='');

# Read Args
argst <- commandArgs(trailingOnly=T)
pid <- as.integer(argst[1])
elid <- as.integer(argst[2])
runid <- as.integer(argst[3])

scen.propname<-paste0('runid_', runid)

# GETTING SCENARIO PROPERTY FROM VA HYDRO
sceninfo <- list(
  varkey = 'om_scenario',
  propname = scen.propname,
  featureid = pid,
  entity_type = "dh_properties"
)
scenprop <- getProperty(sceninfo, site, scenprop)
# POST PROPERTY IF IT IS NOT YET CREATED
if (identical(scenprop, FALSE)) {
  # create
  sceninfo$pid = NULL
} else {
  sceninfo$pid = scenprop$pid
}
scenprop = postProperty(inputs=sceninfo,base_url=base_url,prop)
scenprop <- getProperty(sceninfo, site, scenprop)

# Create an algorithm that finds the outlet point for the watershed 

# Load the model result data for this scenario for "consumptive_use_frac" property

# Calculate richness change here
elfgen_richness_change_8 = made_up_elfgen_function()
elfgen_richness_change_10 = made_up_elfgen_function()

vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'mne9_10', sept_10, site, token)
