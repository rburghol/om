#----------------------------------------------
site <- "http://deq2.bse.vt.edu/d.dh"    #Specify the site of interest, either d.bet OR d.dh
#----------------------------------------------
# Load Libraries
basepath='/var/www/R';
source(paste(basepath,'config.R',sep='/'))
library(stringr)

elid = 245877
runid = 13
dat <- om_get_rundata(elid, runid)
dat$Qbaseline <- dat$Qout + 
  (dat$wd_cumulative_mgd - dat$ps_cumulative_mgd ) * 1.547
pdstart = as.Date( '1988-06-07')
pdend = as.Date('1988-09-04' )
datpd <- window(
  dat, 
  start = pdstart, 
  end = pdend
);
ymx <- max(datpd$Qbaseline, datpd$Qout)
plot(
  datpd$Qbaseline, ylim = c(0,ymx),
  ylab="Flow/WD/PS (cfs)",
  xlab=paste("Lowest 90 Day Flow Period",pdstart,"to",pdend)
)
lines(datpd$Qout,col='blue')
par(new = TRUE)
ymx <- max(datpd$wd_cumulative_mgd * 1.547, datpd$ps_cumulative_mgd * 1.547)
plot(
  datpd$wd_cumulative_mgd * 1.547,col='red', 
  axes=FALSE, xlab="", ylab="", ylim=c(0,ymx)
)
lines(datpd$ps_cumulative_mgd * 1.547,col='green')
axis(side = 4)
mtext(side = 4, line = 3, 'Flow/Demand (cfs)')


