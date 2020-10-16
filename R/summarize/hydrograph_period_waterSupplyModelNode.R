elid = 277538 # cootes: , mount j: 229937, strasburg: 230667
# sf shen at front royal: 229799
# shenandoah river at Potomac Confluence, Millville WV: 230533
gage_number = '03168000' # cootes: 01632000, mount j: 01633000, strasburg: 01634000
# Shen at Millville WV: 01636500
startdate <- "2001-10-01"
enddate <- "2002-11-15"

# Get and format gage data
gage_data <- gage_import_data_cfs(gage_number, startdate, enddate)
gage_data <- as.zoo(gage_data, as.POSIXct(gage_data$date,tz="EST"))
mode(gage_data) <- 'numeric'

runid = 1
finfo = fn_get_runfile_info(elid, runid, 37, site= omsite)
dat <- fn_get_runfile(elid, runid, site= omsite,  cached = FALSE)
mode(dat) <- 'numeric'
#limit to period
datpd <- window(dat, start = startdate, end = enddate)

# Plot
ymx <- max(datpd$Qout,gage_data$flow)
plot(
  datpd$Qout, ylim = c(0,ymx),
  ylab="Flow/WD/PS (cfs)",
  xlab=paste("Model vs USGS",startdate,"to",enddate),
  main="Daily Model Timestep"
)
lines(gage_data$flow, col='blue')


runid = 1131
finfo = fn_get_runfile_info(elid, runid, 37, site= omsite)
dat <- fn_get_runfile(elid, runid, site= omsite,  cached = FALSE)
mode(dat) <- 'numeric'
#limit to period
datpd <- window(dat, start = startdate, end = enddate)


# Plot
ymx <- max(datpd$Qout,gage_data$flow)
plot(
  datpd$Qout, ylim = c(0,ymx),
  ylab="Flow/WD/PS (cfs)",
  xlab=paste("Model vs USGS",startdate,"to",enddate),
  main="Hourly Model Timestep"
)
lines(gage_data$flow, col='blue')


# Hourly to Daily flow timeseries
datpd = aggregate(
  datpd,
  as.POSIXct(
    format(
      time(datpd), 
      format='%Y/%m/%d'),
    tz='UTC'
  ),
  'mean'
)

# Plot
ymx <- max(datpd$Qout,gage_data$flow)
plot(
  datpd$Qout, ylim = c(0,ymx),
  ylab="Flow/WD/PS (cfs)",
  xlab=paste("Model vs USGS",startdate,"to",enddate),
  main="Hourly Model Timestep Average to Daily",
  legend=c('Model', 'USGS')
)
lines(gage_data$flow, col='blue')