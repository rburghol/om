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

# try a 1 hour offset

for (j in 1:10) {
  offdd <- as.data.frame(rodaily[j,c("thisdate", "Runit")])
  offdd$thisdate <- as.Date(index(rodaily[j]))
  ddmean <- mean(offdd$Runit)
  dddate <- offdd$thisdate
  for (i in 1:4) {
    offhrd <- as.data.frame(rohourly[((j-1)*24 + 10):(i+((j-1)*24 + 24)),c("thisdate", "Runit")])
    offmean <- mean(offhrd$Runit)
    offmed <- median(offhrd$Runit)
    offhrd$thisdate <- as.Date.POSIXct(index(rohourly[i:(i+23)]))
    print(paste(i, round(offmean - ddmean, 4),"Daily", round(ddmean,5), '@', dddate, 'vs', round(offmean,5), '/', round(offmed,4), '@', min(offhrd$thisdate), 'to', max(offhrd$thisdate)))
  }
  
}
