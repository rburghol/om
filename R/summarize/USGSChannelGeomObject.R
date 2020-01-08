# dirs/URLs
save_directory <- "/var/www/html/files/fe/plots"
#----------------------------------------------
site <- "http://deq2.bse.vt.edu/d.dh"    #Specify the site of interest, either d.bet OR d.dh
#----------------------------------------------
# Load Libraries
basepath='/var/www/R';
source(paste(basepath,'config.R',sep='/'))

# Camp Creek - 279191
argst <- commandArgs(trailingOnly=T)
pid <- as.integer(argst[1])
elid <- as.integer(argst[2])
runid <- as.integer(argst[3])

dat <- fn_get_runfile(elid, runid, site= omsite,  cached = FALSE);
amn <- 10.0 * mean(as.numeric(dat$Qout))

dat <- window(dat, start = as.Date("1984-10-01"), end = as.Date("2014-09-30"));

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


Rmean <- mean(as.numeric(dat$Runit) )
if (is.na(Rmean)) {
  Rmean = 0.0
}
Rmeanprop <- vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'Rmean', Rmean, site, token)

Qin_mean <- mean(as.numeric(dat$Qin) )
if (is.na(Rmean)) {
  Rmean = 0.0
}
Qin_meanprop <- vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'Qin_mean', Qin_mean, site, token)

Qout_mean <- mean(as.numeric(dat$Qout) )
if (is.na(Rmean)) {
  Rmean = 0.0
}
Qout_meanprop <- vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'Qout_mean', Qout_mean, site, token)
