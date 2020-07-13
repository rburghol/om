################################
#### *** Water Supply Element
################################
library(stringr)

# dirs/URLs
save_directory <-  "/var/www/html/data/proj3/out"

#----------------------------------------------
site <- "http://deq2.bse.vt.edu/d.dh"    #Specify the site of interest, either d.bet OR d.dh
save_url <- paste(str_remove(site, 'd.dh'), "data/proj3/out", sep='');
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
mode(dat) <- 'numeric'
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

#omsite = site <- "http://deq2.bse.vt.edu"
#dat <- fn_get_runfile(elid, runid, site= omsite,  cached = FALSE);
#amn <- 10.0 * mean(as.numeric(dat$Qreach))

#dat <- window(dat, start = as.Date("1984-10-01"), end = as.Date("2014-09-30"));
#boxplot(as.numeric(dat$Qreach) ~ dat$year, ylim=c(0,amn))

datdf <- as.data.frame(dat)
modat <- sqldf("select month, avg(wd_mgd) as wd_mgd from datdf group by month")
#barplot(wd_mgd ~ month, data=modat)

# Calculate
wd_mgd <- mean(as.numeric(dat$wd_mgd) )
if (is.na(wd_mgd)) {
  wd_mgd = 0.0
}
gw_demand_mgd <- mean(as.numeric(dat$gw_demand_mgd) )
if (is.na(gw_demand_mgd)) {
  gw_demand_mgd = 0.0
}
unmet_demand_mgd <- mean(as.numeric(dat$unmet_demand_mgd) )
if (is.na(unmet_demand_mgd)) {
  unmet_demand_mgd = 0.0
}
ps_mgd <- mean(as.numeric(dat$discharge_mgd) )
if (is.na(ps_mgd)) {
  ps_mgd = 0.0
}

# Analyze unmet demands
flows <- zoo(as.numeric(dat$unmet_demand_mgd*1.547), order.by = index(dat));
loflows <- group2(flows);

unmet90 <- loflows["90 Day Max"];
ndx = which.max(as.numeric(unmet90[,"90 Day Max"]));
unmet90 = round(loflows[ndx,]$"90 Day Max",6);
unmet30 <- loflows["30 Day Max"];
ndx1 = which.max(as.numeric(unmet30[,"30 Day Max"]));
unmet30 = round(loflows[ndx,]$"30 Day Max",6);
unmet7 <- loflows["7 Day Max"];
ndx = which.max(as.numeric(unmet7[,"7 Day Max"]));
unmet7 = round(loflows[ndx,]$"7 Day Max",6);
unmet1 <- loflows["1 Day Max"];
ndx = which.max(as.numeric(unmet1[,"1 Day Max"]));
unmet1 = round(loflows[ndx,]$"1 Day Max",6);


# post em up
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'wd_mgd', wd_mgd, site, token)
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'gw_demand_mgd', gw_demand_mgd, site, token)
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'unmet_demand_mgd', unmet_demand_mgd, site, token)
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'ps_mgd', ps_mgd, site, token)
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'unmet90_mgd', unmet90, site, token)
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'unmet30_mgd', unmet30, site, token)
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'unmet7_mgd', unmet7, site, token)
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'unmet1_mgd', unmet1, site, token)

# Define year at which highest 30 Day Max occurs (Lal's code, line 405)
u30_year2 = loflows[ndx1,]$"year";


##### Define fname before graphing
# hydroImpoundment lines 144-151

fname <- paste(
  save_directory,
  paste0(
    'fig.30daymax_unmet.',
    elid, '.', runid, '.png'
  ),
  sep = '/'
)

furl <- paste(
  save_url,
  paste0(
    'fig.30daymax_unmet.',
    elid, '.', runid, '.png'
  ),
  sep = '/'
)

png(fname)

##### Define data for graph, just within that defined year, and graph it
# Lal's code, lines 410-446 (412 commented out)

ddat2 <- window(dat, start = as.Date(paste0(u30_year2, "-06-01")), end = as.Date(paste0(u30_year2,"-09-15") ));

#dmx2 = max(ddat2$Qintake)
map2<-as.data.frame(ddat2$Qintake + (ddat2$discharge_mgd - ddat2$wd_mgd) * 1.547)
colnames(map2)<-"flow"
map2$date <- rownames(map2)
map2$base_demand_mgd<-ddat2$base_demand_mgd * 1.547
map2$unmetdemand<-ddat2$unmet_demand_mgd * 1.547

df <- data.frame(as.Date(map2$date), map2$flow, map2$base_demand_mgd,map2$unmetdemand); 

colnames(df)<-c("date","flow","base_demand_mgd","unmetdemand")

options(scipen=5, width = 1400, height = 950)
ggplot(df, aes(x=date)) + 
  geom_line(aes(y=flow, color="Flow"), size=0.5) +
  geom_line(aes(y=base_demand_mgd, colour="Base demand"), size=0.5)+
  geom_line(aes(y=unmetdemand, colour="Unmet demand"), size=0.5)+
  theme_bw()+ 
  theme(legend.position="top", 
        legend.title=element_blank(),
        legend.box = "horizontal", 
        legend.background = element_rect(fill="white",
                                         size=0.5, linetype="solid", 
                                         colour ="white"),
        legend.text=element_text(size=12),
        axis.text=element_text(size=12, color = "black"),
        axis.title=element_text(size=14, color="black"),
        axis.line = element_line(color = "black", 
                                 size = 0.5, linetype = "solid"),
        axis.ticks = element_line(color="black"),
        panel.grid.major=element_line(color = "light grey"), 
        panel.grid.minor=element_blank())+
  scale_colour_manual(values=c("purple","black","blue"))+
  guides(colour = guide_legend(override.aes = list(size=5)))+
  labs(y = "Flow (cfs)")
dev.off()

##### Naming for saving and posting to VAHydro (do we need these lines?)
# hydroImpoundment, lines 152-178

print(paste("Saved file: ", fname, "with URL", furl))

vahydro_post_metric_to_scenprop(scenprop$pid, 'dh_image_file', furl, '30daymax_unmet_vs_base', 0.0, site, token)

