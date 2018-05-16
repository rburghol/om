library(httr)
site <- 'http://deq1.bse.vt.edu/d.alpha';
#Cross-site Request Forgery protection (Token needed for POST and PUT)
csrf <- GET(
  url=paste(site, '/restws/session/token/',sep=''),
  authenticate("restws_admin", "@dmin123RESTFUL")
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
