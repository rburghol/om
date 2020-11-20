library('zoo')
om_get_rundata <- function(elid, runid, site='http://deq2.bse.vt.edu', cached=FALSE, hydrowarmup=TRUE) {
  
  # replace this with a single function that grabs
  # a hydro model for summarization and slims it down
  dat <- fn_get_runfile(elid, runid, site= omsite,  cached = FALSE)
  syear = as.integer(min(dat$year))
  eyear = as.integer(max(dat$year))
  if (syear < (eyear - 2)) {
    sdate <- paste0(syear,"-10-01")
    edate <- paste0(eyear,"-09-30")
  } else {
    sdate <- paste0(syear,"-02-01")
    edate <- paste0(eyear,"-12-31")
  }
  dat <- window(dat, start = sdate, end = edate);
  mode(dat) <- 'numeric'
  return(dat)
}