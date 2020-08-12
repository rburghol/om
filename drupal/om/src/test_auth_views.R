library(httr)
site <- 'http://deq2.bse.vt.edu/d.dh';
#Cross-site Request Forgery protection (Token needed for POST and PUT)
csrf <- GET(
  url=paste(site, '/restws/session/token/',sep=''),
  authenticate(rest_uname, rest_pw)
);
token <- content(csrf)
hydroid=77499;

# Use new omr library
# library(omr)
# token <- omr.get.token(site, token, rest_uname, rest_pw)

hydroid=146;
# without authentication
sp <- GET(
  paste(site,"dh-project-feature",hydroid,sep="/"),
  encode = "xml"
);

#print(paste("Property Query:",sp,""));
noauth <- content(sp);
noauth


# WITH authentication
sp <- GET(
  paste(site,"dh_timeseries/export",hydroid,sep="/"),
  add_headers(HTTP_X_CSRF_TOKEN = token),
  encode = "xml"
);

auth <- content(sp, type = "text/csv")
auth
