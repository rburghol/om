# dirs/URLs
save_directory <- "/var/www/html/files/fe/plots"
#----------------------------------------------
site <- "http://deq2.bse.vt.edu/d.dh"    #Specify the site of interest, either d.bet OR d.dh
#----------------------------------------------
# Load Libraries
basepath='/var/www/R';
source(paste(basepath,'config.R',sep='/'))

# Camp Creek - 279191
elid = 258405
runid = 11

omsite = site 
dat <- fn_get_runfile(elid, runid, site= omsite,  cached = FALSE);
amn <- 10.0 * mean(as.numeric(dat$Qout))

dat <- window(dat, start = as.Date("1984-10-01"), end = as.Date("2014-09-30"));

#plot(as.numeric(dat$Qout), ylim=c(0,amn))

#boxplot(as.numeric(dat$Qout) ~ dat$year, ylim=c(0,amn))
#boxplot(as.numeric(dat$Qin) ~ dat$month, ylim=c(0,amn))
#boxplot(as.numeric(dat$Qout) ~ dat$month, ylim=c(0,amn))
#boxplot(as.numeric(dat$Runit) ~ dat$month, ylim=c(0,10))
