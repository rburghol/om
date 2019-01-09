devtools::install_github("jsta/nhdR");

library(nhdR)
# vpu in VA
# 02, 03N, 05, 06

# download the data for Catchments
nhd_plus_get(vpu = 5, "NHDPlusCatchment")
nhd_plus_load(vpu = 5,  component = "NHDSnapshot", dsn = "NHDPlusCatchment")
nhd_plus_info(vpu = 5,  component = "NHDSnapshot", dsn = "NHDPlusCatchment")



nhd_plus_get(vpu = 4, "NHDPlusCatchment")
nhd_plus_get(vpu = 4, "NHDSnapshot")
nhd_plus_get(vpu = 4, "NHDPlusAttributes")

# trying again, in order get, list, load, info
nhd_plus_get(vpu = 4)
nhd_plus_info(vpu = 4, component = 'all', dsn=1)

nhd_plus_get(vpu = 4, component = "NHDPlusAttributes")
nhd_plus_load(vpu = 1, component = "NHDPlusAttributes")


nhd_plus_load(vpu = 4, component = "NHDSnapshot", dsn = "NHDWaterbody")

nhd_plus_list(vpu = 4, "NHDPlusCatchment")
nhd_plus_list(vpu = 4, component = "NHDSnapshot")


nhd_plus_info(vpu = 4, "NHDSnapshot")
nhd_plus_list(vpu = 4, "NHDSnapshot")

nhd_plus_load(vpu = 4, "NHDSnapshot", "NHDPlusCatchment")
nhd_plus_info(vpu = 4, "NHDSnapshot", "NHDPlusCatchment")
nhd_plus_info(vpu = 4, "NHDSnapshot", "NHDWaterbody")
nhd_plus_info(vpu = 4, component = "NHDSnapshot", dsn = "NHDWaterbody")

# VA works kinda
nhd_get(state='VA')
nhd_info(state='VA')
nhd_list('VA')
nhd_load(state='VA', 'NHDFlowlineVAA')
nhd_info(state='VA', 'NHDFlowlineVAA')

