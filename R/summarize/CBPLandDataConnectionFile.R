# dirs/URLs
library(stringr)
save_directory <- "/var/www/html/files/fe/plots"
#----------------------------------------------
site <- "http://deq2.bse.vt.edu/d.dh"    #Specify the site of interest, either d.bet OR d.dh
#----------------------------------------------
# Load Libraries
basepath='/var/www/R';
source(paste(basepath,'config.R',sep='/'))
save_directory <-  "/var/www/html/data/proj3/out"

# Now do the stuff
#pid = 4823212
#elid = 233571	
#runid = 11
argst <- commandArgs(trailingOnly=T)
pid <- as.integer(argst[1])
elid <- as.integer(argst[2])
runid <- as.integer(argst[3])

omsite = "http://deq2.bse.vt.edu"
dat <- fn_get_runfile(elid, runid, site= omsite,  cached = FALSE);

dat <- window(dat, start = as.Date("1984-10-01"), end = as.Date("2014-09-30"));
dat$Runit <- as.numeric(dat$Qout) / as.numeric(dat$area_sqmi)
Runits <- zoo(as.numeric(as.character( dat$Runit )), order.by = as.POSIXct(dat$thisdate));

#boxplot(as.numeric(dat$Runit) ~ dat$year, ylim=c(0,3))
# get feature attached to this element id using REST
element <- getProperty(list(pid=pid), base_url, prop)
# Post up a run summary for this runid
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

# Metric defs

sceninfo <- list(
  varkey = 'om_scenario',
  propname = scen.propname,
  featureid = pid,
  entity_type = "dh_properties"
)

# POSTING METRICS TO SCENARIO PROPERTIES ON VA HYDRO
# QA
loflows <- group2(Runits);
l90 <- loflows["90 Day Min"];
ndx = which.min(as.numeric(l90[,"90 Day Min"]));
l90_RUnit = round(loflows[ndx,]$"90 Day Min",6);
l90_year = loflows[ndx,]$"year";

if (is.na(l90)) {
  l90_Runit = 0.0
  l90_year = 0
}
l90prop <- vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'l90_RUnit', l90_RUnit, site, token)
l90yr_prop <- vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'l90_year', l90_year, site, token)

Runit <- mean(as.numeric(dat$Runit) )
if (is.na(Runit)) {
  Runit = 0.0
}
Runitprop <- vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'Runit', Runit, site, token)

# Runoff boxplot
fname <- paste0(
  'Runit_boxplot_year',
  elid, '.', runid, '.png'
)
fpath <-  paste(
  save_directory,
  fname,
  sep='/'
)
furl <- paste(
  save_url,
  fname,
  sep='/'
)
png(fpath)
boxplot(as.numeric(dat$Runit) ~ dat$year, ylim=c(0,3))
dev.off()
print(paste("Saved file: ", fname, "with URL", furl))
vahydro_post_metric_to_scenprop(scenprop$pid, 'dh_image_file', furl, 'Runit_boxplot_year', 0.0, site, token)

# Runoff boxplot
fname <- paste0(
  'Runit_boxplot_month',
  elid, '.', runid, '.png'
)
fpath <-  paste(
  save_directory,
  fname,
  sep='/'
)
furl <- paste(
  save_url,
  fname,
  sep='/'
)
png(fpath)
boxplot(as.numeric(dat$Runit) ~ as.integer(dat$month), ylim=c(0,3))
dev.off()
print(paste("Saved file: ", fname, "with URL", furl))
vahydro_post_metric_to_scenprop(scenprop$pid, 'dh_image_file', furl, 'Runit_boxplot_month', 0.0, site, token)

