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

dat <- fn_get_runfile(elid, runid, site= omsite,  cached = FALSE)
syear = as.integer(min(dat$year))
eyear = as.integer(max(dat$year))
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

wd_mgd <- mean(as.numeric(dat$wd_mgd) )
if (is.na(wd_mgd)) {
  wd_mgd = 0.0
}
wd_cumulative_mgd <- mean(as.numeric(dat$wd_cumulative_mgd) )
if (is.na(wd_cumulative_mgd)) {
  wd_cumulative_mgd = 0.0
}
ps_mgd <- mean(as.numeric(dat$ps_mgd) )
if (is.na(ps_mgd)) {
  ps_mgd = 0.0
}
ps_cumulative_mgd <- mean(as.numeric(dat$ps_cumulative_mgd) )
if (is.na(ps_cumulative_mgd)) {
  ps_cumulative_mgd = 0.0
}
ps_nextdown_mgd <- mean(as.numeric(dat$ps_nextdown_mgd) )
if (is.na(ps_nextdown_mgd)) {
  ps_nextdown_mgd = 0.0
}
Qout <- mean(as.numeric(dat$Qout) )
if (is.na(Qout)) {
  Qout = 0.0
}
net_consumption_mgd <- wd_cumulative_mgd - ps_cumulative_mgd
if (is.na(net_consumption_mgd)) {
  net_consumption_mgd = 0.0
}
dat$Qbaseline <- dat$Qout + 
  (dat$wd_cumulative_mgd - dat$ps_cumulative_mgd ) * 1.547
Qbaseline <- mean(as.numeric(dat$Qbaseline) )
if (is.na(Qbaseline)) {
  Qbaseline = Qout + 
    (wd_cumulative_mgd - ps_cumulative_mgd ) * 1.547
}

dat$consumptive_use_frac <- 1.0 - (dat$Qout / dat$Qbaseline)
consumptive_use_frac <-  mean(as.numeric(dat$consumptive_use_frac) )
if (is.na(consumptive_use_frac)) {
  consumptive_use_frac <- 1.0 - (Qout / Qbaseline)
}

# post em up
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'net_consumption_mgd', net_consumption_mgd, site, token)
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'wd_mgd', wd_mgd, site, token)
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'wd_cumulative_mgd', wd_cumulative_mgd, site, token)
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'ps_mgd', ps_mgd, site, token)
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'ps_cumulative_mgd', ps_cumulative_mgd, site, token)
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'Qout', Qout, site, token)
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'Qbaseline', Qbaseline, site, token)
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'ps_nextdown_mgd', ps_nextdown_mgd, site, token)
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'consumptive_use_frac', consumptive_use_frac, site, token)

# Metrics that need Zoo (IHA)
flows <- zoo(as.numeric(as.character( dat$Qout )), order.by = index(dat));
loflows <- group2(flows);
l90 <- loflows["90 Day Min"];
ndx = which.min(as.numeric(l90[,"90 Day Min"]));
l90_Qout = round(loflows[ndx,]$"90 Day Min",6);
l90_year = loflows[ndx,]$"year";

if (is.na(l90)) {
  l90_Runit = 0.0
  l90_year = 0
}

vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'l90_Qout', l90_Qout, site, token)
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'l90_year', l90_year, site, token)

l30 <- loflows["30 Day Min"];
ndx = which.min(as.numeric(l30[,"30 Day Min"]));
l30_Qout = round(loflows[ndx,]$"30 Day Min",6);
l30_year = loflows[ndx,]$"year";

if (is.na(l30)) {
  l30_Runit = 0.0
  l30_year = 0
}

vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'l30_Qout', l30_Qout, site, token)
vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'l30_year', l30_year, site, token)

# 7q10 -- also requires PearsonDS packages
x <- as.vector(as.matrix(loflows["7 Day Min"]))
for (k in 1:length(x)) {
  if (x[k] <= 0) {
    x[k] <- 0.00000001
  }
}
x <- log(x)
pars <- PearsonDS:::pearsonIIIfitML(x)
x7q10 <- round(exp(qpearsonIII(0.1, params = pars$par)),6) #1 note

vahydro_post_metric_to_scenprop(scenprop$pid, '7q10', NULL, '7q10', x7q10, site, token)

# ALF -- also requires IHA package and lubridate
alf_data <- data.frame(matrix(data = NA, nrow = length(dat$thisdate), ncol = 5))
colnames(alf_data) <- c('Qout', 'thisdate', 'year', 'month', 'day')
alf_data$Qout <- dat$Qout
alf_data$thisdate <- index(dat)
alf_data$year <- year(ymd(alf_data$thisdate))
alf_data$month <- month(ymd(alf_data$thisdate))
alf_data$day <- day(ymd(alf_data$thisdate))
monthly_mins <- zoo(alf_data$Qout, order.by = alf_data$thisdate)
modat <- group1(monthly_mins,'water','min')
g1vec <- as.vector(as.matrix(modat[,7]))
alf <- round(quantile(g1vec, 0.5, na.rm = TRUE),6)

vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'ml8', alf, site, token)

# Sept. 10%
sept_flows <- subset(alf_data, month == '9')
sept_10 <- as.numeric(round(quantile(sept_flows$Qout, 0.10),6))

vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'mne9_10', sept_10, site, token)

# Metrics trimmed to climate change scenario timescale (Jan. 1 1990 -- Dec. 31 2000)
if (syear <= 1990 && eyear >= 2000) {
  sdate_trim <- as.Date(paste0(1990,"-10-01"))
  edate_trim <- as.Date(paste0(2000,"-09-30"))
  
  dat_trim <- window(dat, start = sdate_trim, end = edate_trim);
  mode(dat_trim) <- 'numeric'
  
  flows_trim <- zoo(as.numeric(as.character( dat_trim$Qout )), order.by = index(dat_trim));
  loflows_trim <- group2(flows_trim);
  l90_trim <- loflows_trim["90 Day Min"];
  ndx_trim = which.min(as.numeric(l90_trim[,"90 Day Min"]));
  l90_Qout_trim = round(loflows_trim[ndx_trim,]$"90 Day Min",6);
  l90_year_trim = loflows_trim[ndx_trim,]$"year";
  
  if (is.na(l90_trim)) {
    l90_Qout_trim = 0.0
    l90_year_trim = 0
  }
  
  vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'l90_cc_Qout', l90_Qout_trim, site, token)
  vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'l90_cc_year', l90_year_trim, site, token)
  
  l30_trim <- loflows_trim["30 Day Min"];
  ndx_trim = which.min(as.numeric(l30_trim[,"30 Day Min"]));
  l30_Qout_trim = round(loflows_trim[ndx_trim,]$"30 Day Min",6);
  l30_year_trim = loflows_trim[ndx_trim,]$"year";
  
  if (is.na(l30_trim)) {
    l30_Qout_trim = 0.0
    l30_year_trim = 0
  }
  
  vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'l30_cc_Qout', l30_Qout_trim, site, token)
  vahydro_post_metric_to_scenprop(scenprop$pid, 'om_class_Constant', NULL, 'l30_cc_year', l30_year_trim, site, token)
}

# does this have an impoundment sub-comp and is imp_off = 0?
cols <- names(dat)
if("imp_off" %in% cols) {
  imp_off <- as.integer(median(dat$imp_off))
  if (!is.null(imp_off)) {
    if (imp_off == 0) {
      if("impoundment" %in% cols) {
        dat$storage_pct <- dat$impoundment_use_remain_mg * 3.07 / dat$impoundment_max_usable
        # this has an impoundment.  Plot it up.
        # Now zoom in on critical drought period
        pdstart = as.Date(paste0(l90_year,"-06-01") )
        pdend = as.Date(paste0(l90_year, "-11-15") )
        datpd <- window(
          dat, 
          start = pdstart, 
          end = pdend
        );
        fname <- paste(
          save_directory,
          paste0(
            'l90_imp_storage.',
            elid, '.', runid, '.png'
          ),
          sep = '/'
        )
        furl <- paste(
          save_url,
          paste0(
            'l90_imp_storage.',
            elid, '.', runid, '.png'
          ),
          sep = '/'
        )
        png(fname)
        plot(datpd$impoundment_Qin, ylim=c(-0.1,15))
        lines(datpd$Qout,col='blue')
        ymn <- 1
        ymx <- 100
        par(mar = c(5,5,2,5))
        plot(
          datpd$storage_pct * 100.0, 
          ylim=c(ymn,ymx), 
          ylab="Reservoir Storage (%)",
          xlab=paste("Lowest 90 Day Flow Period",pdstart,"to",pdend)
        )
        par(new = TRUE)
        plot(datpd$impoundment_Qin,col='blue', axes=FALSE, xlab="", ylab="")
        lines(datpd$impoundment_Qout,col='green')
        lines(datpd$wd_mgd * 1.547,col='red')
        axis(side = 4)
        mtext(side = 4, line = 3, 'Flow/Demand (cfs)')
        dev.off()
        print(paste("Saved file: ", fname, "with URL", furl))
        vahydro_post_metric_to_scenprop(scenprop$pid, 'dh_image_file', furl, 'fig.l90_imp_storage', 0.0, site, token)
        
        # l90 2 year
        # this has an impoundment.  Plot it up.
        # Now zoom in on critical drought period
        pdstart = as.Date(paste0( (as.integer(l90_year) - 1),"-01-01") )
        pdend = as.Date(paste0(l90_year, "-12-31") )
        datpd <- window(
          dat, 
          start = pdstart, 
          end = pdend
        );
        fname <- paste(
          save_directory,
          paste0(
            'l90_imp_storage.2yr.',
            elid, '.', runid, '.png'
          ),
          sep = '/'
        )
        furl <- paste(
          save_url,
          paste0(
            'l90_imp_storage.2yr.',
            elid, '.', runid, '.png'
          ),
          sep = '/'
        )
        png(fname)
        plot(datpd$impoundment_Qin, ylim=c(-0.1,15))
        lines(datpd$Qout,col='blue')
        ymn <- 1
        ymx <- 100
        par(mar = c(5,5,2,5))
        plot(
          datpd$storage_pct * 100.0, 
          ylim=c(ymn,ymx), 
          ylab="Reservoir Storage (%)",
          xlab=paste("Lowest 90 Day Flow Period",pdstart,"to",pdend)
        )
        par(new = TRUE)
        plot(datpd$impoundment_Qin,col='blue', axes=FALSE, xlab="", ylab="")
        lines(datpd$impoundment_Qout,col='green')
        lines(datpd$wd_mgd * 1.547,col='red')
        axis(side = 4)
        mtext(side = 4, line = 3, 'Flow/Demand (cfs)')
        dev.off()
        print(paste("Saved file: ", fname, "with URL", furl))
        vahydro_post_metric_to_scenprop(scenprop$pid, 'dh_image_file', furl, 'fig.l90_imp_storage.2yr', 0.0, site, token)
        
        # All Periods
        # this has an impoundment.  Plot it up.
        # Now zoom in on critical drought period
        datpd <- dat
        fname <- paste(
          save_directory,
          paste0(
            'l90_imp_storage.all.',
            elid, '.', runid, '.png'
          ),
          sep = '/'
        )
        furl <- paste(
          save_url,
          paste0(
            'l90_imp_storage.all.',
            elid, '.', runid, '.png'
          ),
          sep = '/'
        )
        png(fname)
        plot(datpd$impoundment_Qin, ylim=c(-0.1,15))
        lines(datpd$Qout,col='blue')
        ymn <- 1
        ymx <- 100
        par(mar = c(5,5,2,5))
        plot(
          datpd$storage_pct * 100.0, 
          ylim=c(ymn,ymx), 
          ylab="Reservoir Storage (%)",
          xlab=paste("Lowest 90 Day Flow Period",pdstart,"to",pdend)
        )
        par(new = TRUE)
        plot(datpd$impoundment_Qin,col='blue', axes=FALSE, xlab="", ylab="")
        lines(datpd$impoundment_Qout,col='green')
        lines(datpd$wd_mgd * 1.547,col='red')
        axis(side = 4)
        mtext(side = 4, line = 3, 'Flow/Demand (cfs)')
        dev.off()
        print(paste("Saved file: ", fname, "with URL", furl))
        vahydro_post_metric_to_scenprop(scenprop$pid, 'dh_image_file', furl, 'fig.imp_storage.all', 0.0, site, token)
        
      }
    } else {
      # plot Qin, Qout of mainstem, and wd_mgd, and wd_cumulative_mgd
      # TBD
      # l90 2 year
      # this has an impoundment.  Plot it up.
      # Now zoom in on critical drought period
      pdstart = as.Date(paste0(l90_year,"-06-01") )
      pdend = as.Date(paste0(l90_year, "-11-15") )
      datpd <- window(
        dat, 
        start = pdstart, 
        end = pdend
      );
      fname <- paste(
        save_directory,
        paste0(
          'l90_flows.2yr.',
          elid, '.', runid, '.png'
        ),
        sep = '/'
      )
      furl <- paste(
        save_url,
        paste0(
          'l90_flows.2yr.',
          elid, '.', runid, '.png'
        ),
        sep = '/'
      )
      png(fname)
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
      dev.off()
      print(paste("Saved file: ", fname, "with URL", furl))
      vahydro_post_metric_to_scenprop(scenprop$pid, 'dh_image_file', furl, 'fig.l90_flows.2yr', 0.0, site, token)
      
    }
  }
  
}
