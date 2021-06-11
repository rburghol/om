library("hydrotools")
elid = 219565 
scenid = 37
runid = 400
omsite = 'https://deq1.bse.vt.edu'
finfo <- fn_get_runfile_info(elid, runid, scenid, omsite)
dat <- om_get_rundata(elid, runid, site=omsite, FALSE)

