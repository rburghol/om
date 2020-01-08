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

wd_mgd
wd_cumulative_mgd
ps_mgd
ps_cumulative_mgd