library(httr)
site <- 'http://deq1.bse.vt.edu/d.alpha';
#Cross-site Request Forgery protection (Token needed for POST and PUT)
csrf <- GET(
  url=paste(site, '/restws/session/token/',sep=''),
  authenticate(rest_uname, rest_pw)
);
token <- content(csrf)
hydroid=77499;
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
  paste(site,"dh-project-feature",hydroid,sep="/"),
  add_headers(HTTP_X_CSRF_TOKEN = token),
  encode = "xml"
);

auth <- content(sp);
auth
