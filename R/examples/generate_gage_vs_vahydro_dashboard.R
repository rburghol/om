# SETTING UP BASEPATH AND SOURCING FUNCTIONS
basepath <- '/var/www/R'
setwd(basepath)
source(paste(basepath,"config.local.private", sep = "/"))
source(paste0(cbp6_location,"/code/cbp6_functions.R"))
source(paste0(github_location, "/auth.private"));
source(paste(cbp6_location, "/code/fn_vahydro-1.0.R", sep = ''))

# ESSENTIAL INPUTS
riv.seg <- 'PS5_4380_4370'
dat.source1 <- 'gage' # cbp_model
dat.source2 <- 'vahydro'
site <- "http://deq2.bse.vt.edu/d.dh"
token <- rest_token(site, token, rest_uname, rest_pw)
# If a gage is used -- all data is trimmed to gage timeframe.  Otherwise, start/end date defaults
# can be found in the gage.timespan.trimmed loop.

# Inputs if using CBP Model -- otherwise, can ignore
mod.phase <- 'p6/p6_gb604' #or "p532c-sova" (phase 5)
mod.scenario1 <- 'CFBASE30Y20180615' #or 'CBASE1808L55CY55R45P50R45P50Y' (climate change) 'CFBASE30Y20180615' (base) 'CBASE1808L55CY55R45P10R45P10Y' (climate change 10%) 'CBASE1808L55CY55R45P90R45P90Y' (climate change 90%)
mod.scenario2 <- mod.scenario1
site.or.server <- 'site'

# Inputs if using VA Hydro -- otherwise, can ignore
run.id1 <- '11'
run.id2 <- run.id1

# Inputs if using USGS gage -- otherwise, can ignore
gage_number <- '01636500'
gage_timespan <- get.gage.timespan(gage_number)
gage.title <- 'USGS 01636500 SHENANDOAH RIVER AT MILLVILLE, WV'

if (dat.source1 == 'gage' || dat.source2 == 'gage') {
  gage.timespan.trimmed <- TRUE #or FALSE
  post.gage.scen.prop(riv.seg, gage.title, site, token)
  
}

if (gage.timespan.trimmed == TRUE) {
  start.date <- as.character(gage_timespan[[1]]) #1984-01-01
  end.date <- as.character(gage_timespan[[2]]) #1984-12-31
} else {
  start.date <- '1991-01-01' #1984-01-01
  end.date <- '2000-12-31' #1984-12-31
}

# Changes graph labels automatically
if (dat.source1 == 'vahydro') {
  cn1 <- paste('VAhydro_runid_', run.id1, sep = '')
} else if (dat.source1 == 'gage') {
  cn1 <- paste('USGS_', gage_number, sep = '')
} else if (dat.source1 == 'cbp_model') {
  cn1 <- paste('CBP_scen_ ', mod.scenario1, sep = '')
}

if (dat.source2 == 'vahydro') {
  cn2 <- paste('VAhydro_runid_', run.id2, sep = '')
} else if (dat.source2 == 'gage') {
  cn2 <- paste('USGS_', gage_number, sep = '')
} else if (dat.source2 == 'cbp_model') {
  cn2 <- paste('CBP_scen_ ', mod.scenario2, sep = '')
}

# POSTING METRICS
automated_metric_2_vahydro(dat.source = dat.source1, riv.seg = riv.seg, gage_number = gage_number, run.id = run.id1, gage.timespan.trimmed = gage.timespan.trimmed, mod.phase = mod.phase, mod.scenario = mod.scenario1, start.date = start.date, end.date = end.date, github_link = github_location, site = site, site.or.server = 'site', token = token)
automated_metric_2_vahydro(dat.source = dat.source2, riv.seg = riv.seg, gage_number = gage_number, run.id = run.id2, gage.timespan.trimmed = gage.timespan.trimmed, mod.phase = mod.phase, mod.scenario = mod.scenario2, start.date = start.date, end.date = end.date, github_link = github_location, site = site, site.or.server = 'site', token = token)

# CREATING DASHBOARD (outputted in /var/www/R)
rmarkdown::render(paste0(cbp6_location, '/code/Modularized_Dashboard_VAHydro.Rmd'), 
                  output_dir = basepath, output_file = paste0(riv.seg, '.pdf'), 
                  params = list(riv.seg = riv.seg, dat.source1 = dat.source1, 
                                dat.source2 = dat.source2, start.date = start.date, 
                                end.date = end.date, github_location = github_location, site = site, 
                                site.or.server = site.or.server, run.id1 = run.id1, 
                                run.id2 = run.id2, gage_number = gage_number, 
                                mod.phase1 = mod.phase, mod.scenario1 = mod.scenario1, 
                                mod.phase2 = mod.phase, mod.scenario2 = mod.scenario2, 
                                gage.timespan.trimmed = gage.timespan.trimmed, 
                                cn1 = cn1, cn2 = cn2))
