# dirs/URLs
save_directory <- "/var/www/html/files/fe/plots"
#----------------------------------------------
site <- "http://deq2.bse.vt.edu/d.dh"    #Specify the site of interest, either d.bet OR d.dh
#----------------------------------------------
# Load Libraries
basepath='/var/www/R';
source(paste(basepath,'config.R',sep='/'))

# Read Args
argst <- commandArgs(trailingOnly=T)
pid <- as.integer(argst[1])
elid <- as.integer(argst[2])
runid <- as.integer(argst[3])

dat <- fn_get_runfile(elid, runid, site= omsite,  cached = FALSE)
syear = min(dat$year)
eyear = max(dat$year)
if (syear != eyear) {
  sdate <- as.Date(paste0(syear,"-10-01"))
  edate <- as.Date(paste0(eyear,"-09-30"))
} else {
  sdate <- as.Date(paste0(syear,"-02-01"))
  edate <- as.Date(paste0(eyear,"-12-31"))
}
dat <- window(dat, start = sdate, end = edate);

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
sceninfo <- list(
  varkey = 'om_scenario',
  propname = scen.propname,
  featureid = pid,
  entity_type = "dh_properties"
)

wd_mgd <- mean(as.numeric(dat$wd_mgd) )
if (is.na(wd_mgd)) {
  wd_mgd = 0.0
}
ps_mgd <- mean(as.numeric(dat$ps_mgd) )
if (is.na(ps_mgd)) {
  ps_mgd = 0.0
}
evap_mgd <- mean(as.numeric(dat$evap_mgd) )
if (is.na(evap_mgd)) {
  evap_mgd = 0.0
}
Qin <- mean(as.numeric(dat$Qin) )
if (is.na(Qin)) {
  Qin = 0.0
}
Qout <- mean(as.numeric(dat$Qout) )
if (is.na(Qout)) {
  Qout = 0.0
}
if (is.na(lake_elev)) {
  elev_p0 <- 0
  elev_p10 <- 0
  elev_p50 <- 0
} else {
  elev_qs = quantile(as.numeric(dat$lake_elev), c(0,0.1,0.5) )
  elev_p0 <- elev_qs["0%"]
  elev_p10 <- elev_qs["10%"]
  elev_p50 <- elev_qs["50%"]
}

# post em up
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'wd_mgd', wd_mgd, site, token)
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'ps_mgd', ps_mgd, site, token)
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'evap_mgd', evap_mgd, site, token)
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'Qout', Qout, site, token)
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'Qin', Qin, site, token)
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'elev_p0', elev_p0, site, token)
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'elev_p10', elev_p10, site, token)
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'elev_p50', elev_p50, site, token)

