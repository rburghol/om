elid = 229819  
startdate <- "2002-10-01"
enddate <- "2002-11-15"

runid = 11
finfo = fn_get_runfile_info(elid, runid, 37, site= omsite)
rodat <- fn_get_runfile(elid, runid, site= omsite,  cached = FALSE)
mode(rodat) <- 'numeric'
#limit to period
rodaily <- window(rodat, start = startdate, end = enddate)

runid = 1131
finfo = fn_get_runfile_info(elid, runid, 37, site= omsite)
rodat <- fn_get_runfile(elid, runid, site= omsite,  cached = FALSE)
mode(rodat) <- 'numeric'
#limit to period
rohourly <- window(rodat, start = startdate, end = enddate)

quantile(rodaily, na.rm = TRUE)
quantile(rohourly, na.rm = TRUE)
mean(rodaily$Runit)
mean(rohourly$Runit)
dd <- as.data.frame(rodaily[1,c("thisdate", "Runit")])
hrd <- as.data.frame(rohourly[1:24,c("thisdate", "Runit")])
mean(dd$Runit)
mean(hrd$Runit)
