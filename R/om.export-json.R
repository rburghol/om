library(httr);
library(stringr);
library(RCurl); #required for limiting connection timeout in vahydro_fe_data_icthy()

base_url = 'http://deq2.bse.vt.edu/om/get_model.php?'
params = list(
  elementid = 251527
)


element_json <- GET(
  paste(base_url,"elementid=",elementid,sep=""), 
  add_headers(HTTP_X_CSRF_TOKEN = token),
  query=params,
  encode = "json"
);

element = content(element_json)
procnames = names(element$processors)
components = element$components
components[[1]]$elementid
# This:
element$processors$Qout$object_class
# Same as this:
element$processors[[procnames[5]]]$object_class



