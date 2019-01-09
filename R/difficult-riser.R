#library(pander);
library(httr);
save_directory <- "/var/www/html/files/fe/plots"
#----------------------------------------------
site <- "http://deq1.bse.vt.edu/d.dh"    #Specify the site of interest, either d.bet OR d.dh
#----------------------------------------------
# Load Libraries
fxn_locations = '/usr/local/home/git/r-dh-ecohydro/Analysis';
source(paste(fxn_locations,"fn_vahydro-1.0.R", sep = "/"));  
source(paste(fxn_locations,"fn_iha.R", sep = "/"));  

#getActiveDocumentContext();

library(dataRetrieval);
library(hydroTSM);
library(zoo);
#runid = 232018; # 2001-2005 run
elid = 339991; 
runid = 2000; # 901 
datamode = 'file'; 
# datamode = 'file' or 'variable'.  
# file is great if downloads are not too big, then variable is needed
# get a single variable in a timeseries summarized by day, keyed by thisdate
#
#plot(elevs);
# get all data from the run file, keyed by timestamp (at whatever timestep model is run)

if (datamode == 'file') {
  dat <- fn_get_runfile(elid, runid);
} else {
  # need to assemble separately, takes time for short runs but prevents time outs in longer runs
  impoundment_Qin <- fn_get_rundata(elid, runid, "impoundment_Qin");
  dat = zoo(data.frame('impoundment_Qin' = impoundment_Qin), order.by = index(impoundment_Qin));
  impoundment_Qout <- fn_get_rundata(elid, runid, "impoundment_Qout");
  dat$impoundment_Qout <- impoundment_Qout; 
  impoundment_lake_elev <- fn_get_rundata(elid, runid, "impoundment_lake_elev");
  dat$impoundment_lake_elev <- impoundment_lake_elev; 
  Runit <- fn_get_rundata(elid, runid, "cbp_runoff_Runit");
  dat$Runit <- Runit; 
  thisdate = zoo(
    as.character(time(impoundment_lake_elev)), 
    order.by = time(impoundment_lake_elev)
  );
  local_channel_Qout <- fn_get_rundata(
    elid, runid, "local_channel_Qout"
  );
  dat$local_channel_Qout = local_channel_Qout;
  Qout <- fn_get_rundata(elid, runid, "Qout");
  dat$Qout <- Qout; 
}

ymax = ceiling(max(as.numeric(dat$impoundment_Qin)));
emax = ceiling(max(as.numeric(dat$impoundment_lake_elev)));
plot(dat$Qout,ylim=c(0,ymax))
par(pch=22, col="green") # plotting symbol and color 
points(dat$impoundment_Qin)
par(pch=22, col="black") # plotting symbol and color 
points(dat$impoundment_Qout)
par(pch=22, col="orange") # plotting symbol and color 


# plot drawdown
par(las=2)
plot(dat$impoundment_lake_elev, ylim = c(0.0, emax));
plot(dat$impoundment_Qin, ylim = c(0.0, ymax));
par(pch=22, col="green") # plotting symbol and color 
points(dat$impoundment_Qout);
# or at a gage:
par(pch=22, col="blue") # plotting symbol and color 
points(dat$Qusgs);

# Single event window 5/5-5/10
wudat <- window(dat, start = as.Date("1989-05-01"), end = as.Date("1989-05-03 23:59:59"));
#wudat <- window(dat, start = as.Date("1985-06-01"), end = as.Date("1985-06-05 23:59:59"));
#wudat <- window(dat, start = as.Date("1985-05-03"), end = as.Date("1985-05-06 23:59:59"));
#wudat <- window(dat, start = as.Date("1985-02-11 18:00:00"), end = as.Date("1985-02-12 10:59:59"));

#wudat <- window(dat, start = as.Date("1989-09-12"), end = as.Date("1989-09-13 23:59:59"));
#wudaily <-  window(Qdaily, start = as.Date("1989-05-06"), end = as.Date("1989-05-08"));
#wudat <- window(dat, start = as.POSIXct("1985-08-18 01:00:00"), end = as.POSIXct("1985-08-20 23:59:59"));
#wudat <- window(dat, start = as.POSIXct("1985-07-01 01:00:00"), end = as.POSIXct("1985-07-03 23:59:59"));

ymax = ceiling(max(as.numeric(wudat$impoundment_Qin)));
#ymax = 15000
plot(
  wudat$Qout,ylim=c(0,ymax), 
  main=paste("El=",elid, ", Run=",runid, min(wudat$thisdate), 'to', max(wudat$thisdate))
)
par(pch=22, col="green") # plotting symbol and color 
lines(wudat$impoundment_Qin);
par(pch=22, col="red") # plotting symbol and color 
lines(wudat$impoundment_riser_head);

par(pch=22, col="green") # plotting symbol and color 
points(wudaily$x);

# Riser detail
ymax = ceiling(max(as.numeric(wudat$impoundment_Qin)));
plot(wudat$impoundment_Qin,ylim=c(0,ymax))
par(pch=22, col="green") # plotting symbol and color 
points(wudat$impoundment_riser_flow)
#par(pch=22, col="black") # plotting symbol and color 
#points(wudat$impoundment_spill)
par(pch=22, col="orange") # plotting symbol and color 
lines(wudat$impoundment_Qout)
par(pch=22, col="red") # plotting symbol and color 
lines(wudat$impoundment_riser_head);

plot(wudat$impoundment_riser_head,ylim = c(-0.5,2));
par(pch=22, col="green") # plotting symbol and color 
wudat$final_riser_head <- (as.numeric(wudat$impoundment_lake_elev) - 7.4068)
points(wudat$final_riser_head)
par(pch=22, col="blue") # plotting symbol and color 
lines(wudat$impoundment_Qin)

par(pch=22, col="orange") # plotting symbol and color 
lines(wudat$impoundment_Qout)
