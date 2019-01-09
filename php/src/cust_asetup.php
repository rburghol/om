<?php

   # adminsetup
   # file contains definitions for database table formatting parameters
   # type - display type
   # params -
   #    #3 - select list - foreigntable:pkcol:listcols(csv):sortcol:showlabels
   #    #8 - scientific notation
   #    #9 - number format
   #    #10 - currency format
   #    #11 - 2 columns map - localvar1:localvar2:foreigntable:foreigncolumn1:foreigncolumn2:
   #                      listcolumns(csv):sortcol:showlabels(bool)
   #    #12 - formatted select - foreigntable:pkcol:listcols(csv):sortcol:showlabels
   #    #13 - 2 columns map, constant first parameter - constant:localvar2:foreigntable:
   #                      listcolumns(csv):sortcol:showlabels(bool)
   #    #15 - edit link
   #    #16 - subSelectList shows only a subset of the intems in the select list table,
            #   indicated by the seckeycol, the matching value of the seckeycol coming
            #   from the currentrecord, the value of the field named "myseckeycol" -
            #   params = foreigntable:pkcol:seckeycol:myseckeycol:listcols(csv):sortcol:showlabels
   #    #17 - mapped parameter value foreigntable:localkeycol:foreignkeycol:paramcol(csv)
   #    #18 - keyed map, has two local values to indicate the local entry in the mapping table
            # with one foreign key value, may be a multi-list
            # params ($maptable:$local1:$local2:$map1:$map2:$foreignmapcol:$foreigntable:$foreignkeycol:$paramcol:$ismulti:$numrows)
   #    #24 - geometry column
   # 'search info' - columns to be included in search form, method for searching
      # 1 - exact match, entry field, multi-select or select list, based on 'column info' entry
      # 2 - exact match, select list, based on 'column info' entry
      # 3 - exact match, multi-select, based on 'column info' entry
      # 4 - upper, lower bounds
      # 5 - fuzzy match - uses ilike or like (case-sensitive is parameter)
      # 6 - geometric bounding box overlap && - not yet implemented
   if (!isset($adminsetuparray)) {
      $adminsetuparray = array();
   }
   
   $adminsetuparray["test"] = array(
       "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column", "child_formats"=>array() ),
       "column info"=>array(
          "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
          "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
          "test_matrix"=>array("type"=>27,"params"=>"3:0:2:8","label"=>"Data Matrix","visible"=>1, "readonly"=>0, "width"=>6),
          "debug"=>array("type"=>3,"params"=>"0|False,1|True:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1,
          "readonly"=>0, "width"=>6)
       )
   );
   
   $adminsetuparray["vahydro_localtrib"] = array(
       "table info"=>array('pk'=>'elementid', "templateid"=>326359, "object_custom1"=>'vahydro_lite_container', 'runform'=>'generic', "templatefile"=>'./forms/vahydro_node.html', "sortcol"=>"name", "outputformat"=>"column", "child_formats"=>array() ),
       "column info"=>array(
          "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
          "the_geom"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>1, "width"=>64)
       )
   );
   
   $adminsetuparray["cova_ws_container"] = array(
       "table info"=>array('pk'=>'elementid', "templateid"=>326359, "object_custom1"=>'cova_ws_container', 'runform'=>'child_select', "templatefile"=>'./forms/vahydro_node.html', "sortcol"=>"name", "outputformat"=>"column", "child_formats"=>array() ),
       "column info"=>array(
          "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
          "the_geom"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>1, "width"=>64)
       )
   );
   
   $adminsetuparray["usgs_node"] = array(
       "table info"=>array('pk'=>'elementid', "templateid"=>326359, "object_custom1"=>'usgs_node', 'runform'=>'child_select', "templatefile"=>'./forms/vahydro_node.html', "sortcol"=>"name", "outputformat"=>"column", "child_formats"=>array() ),
       "column info"=>array(
          "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
          "the_geom"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>1, "width"=>64)
       )
   );

   $adminsetuparray["wsp_section80"] = array(
       "table info"=>array('pk'=>'elementid', "templateid"=>326359, "object_custom1"=>'cova_wsp_region', 'runform'=>'generic', "templatefile"=>'./forms/wsp_80.1-3.html', "sortcol"=>"name", "outputformat"=>"column", "child_formats"=>array() ),
       "column info"=>array(
           "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
           "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
           
           "cws_info"=>array("type"=>2,"params"=>"","label"=>"CWS Info","visible"=>1, "readonly"=>1, "width"=>6),
           "cws_info_plan"=>array("type"=>27,"params"=>"10:0:2:16","label"=>"","visible"=>1, "readonly"=>0, "width"=>6),
           
           "locality_info_plan"=>array("type"=>27,"params"=>"9:0:2:16","label"=>"","visible"=>1, "readonly"=>0, "width"=>6),
           
           "ssu_gt300k_nonag_info"=>array("type"=>2,"params"=>"","label"=>"SSU > 300K Info","visible"=>1, "readonly"=>1, "width"=>6),
           "ssu_gt300k_nonag_info_plan"=>array("type"=>27,"params"=>"9:0:2:16","label"=>"","visible"=>1, "readonly"=>0, "width"=>6),
           
           "ssu_lt300k_nonag_info"=>array("type"=>2,"params"=>"","label"=>"SSU > 300K Info","visible"=>1, "readonly"=>1, "width"=>6),
           "ssu_lt300k_nonag_info_plan"=>array("type"=>27,"params"=>"9:0:2:16","label"=>"","visible"=>1, "readonly"=>0, "width"=>6),
           
           "ssu_gt300k_ag_info"=>array("type"=>2,"params"=>"","label"=>"CWS Info","visible"=>1, "readonly"=>1, "width"=>6),
           "ssu_gt300k_ag_info_plan"=>array("type"=>27,"params"=>"6:0:2:16","label"=>"","visible"=>1, "readonly"=>0, "width"=>6),
           
           "debug"=>array("type"=>3,"params"=>"0|False,1|True:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6)
        )
     );

   $adminsetuparray["cova_reservoir"] = array(
       "table info"=>array("templateid"=>321861, "object_custom1"=>'cova_impoundment', 'runform'=>'cova_child', "templatefile"=>"./forms/cova_reservoir_info.html", "pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column", "child_formats"=>array() ),
       "column info"=>array(
           "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
           "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
           
           "flow_mode"=>array("type"=>3,"params"=>"0|Best Available,1|USGS Baseline,2|USGS Synthetic,3|VAHydro HSPF,4|USGS Historical:fmid:fmname::0","label"=>"Flow Mode (0 - best available, 1 - USGS baseline, 2 - USGS Synth, 3 - VA HSPF, 4+ - Custom) ","visible"=>1, "readonly"=>0, "width"=>6),
           "surrogate_gage"=>array("type"=>1,"params"=>"","label"=>"USGS Gage ID","visible"=>1, "readonly"=>0, "width"=>8),
           
           // flowby stuff
           // the simple flowby here
          "flowby_type"=>array("type"=>3,"params"=>"0|Simple,1|Tiered:fbtid:fbtname::0","label"=>"Flow-By Type ","visible"=>1,
          "readonly"=>0, "width"=>6),
           "simple_flowby_desc"=>array("type"=>1,"params"=>"","label"=>"Description of Flow-By","visible"=>1, "readonly"=>0, "width"=>48),
           "simple_flowby_desc"=>array("type"=>1,"params"=>"","label"=>"Description of Flow-By","visible"=>1, "readonly"=>0, "width"=>48),
           "simple_flowby_eqn"=>array("type"=>1,"params"=>"","label"=>"Flow-By","visible"=>1, "readonly"=>0, "width"=>48),
           "simple_flowby_cfb_var"=>array("type"=>3,"params"=>"Qin|Flow Into Impoundment (cfs),aux1|Auxiliary 1,aux2|Auxiliary 2,stat1|Statistic 1,stat2|Statistic 2:ecvid:ecvname::0","label"=>"Conditional Flow-By Var ","visible"=>1, "readonly"=>0, "width"=>6),
           "simple_flowby_enable_cfb"=>array("type"=>26,"params"=>"1:NULL:0","label"=>"Enable Conditional Flow-By ","visible"=>1, "readonly"=>0, "width"=>6),
           "simple_flowby_cfb_condition"=>array("type"=>3,"params"=>"lt|<,gt|>:ecfbid:ecfbname::0","label"=>"Flow-By is ","visible"=>1, "readonly"=>0, "width"=>6),
           // a 1-tiered flowby goes here
           "tiered_flowby_matrix"=>array("type"=>27,"params"=>"2:0:2:8","label"=>"Flow-By","visible"=>1, "readonly"=>0, "width"=>6),
           "tiered_flowby_tier_var"=>array("type"=>3,"params"=>"Qin|Flow Into Impoundment (cfs),simple_flowby|Simple Release (above),use_remain_mg|Usable Storate Remaining (MG),lake_elev|Lake Surface Elevation,month|Month,aux1|Auxiliary 1,aux2|Auxiliary 2,stat1|Statistic 1,stat2|Statistic 2:tftvid:tftvname::0","label"=>"Tier Variable Var ","visible"=>1, "readonly"=>0, "width"=>6),
           "tiered_flowby_enable_cfb"=>array("type"=>26,"params"=>"1:NULL:0","label"=>"Enable Conditional Flow-By ","visible"=>1, "readonly"=>0, "width"=>6),
           "tiered_flowby_cfb_var"=>array("type"=>3,"params"=>"Qin|Flow Into Impoundment (cfs),simple_flowby|Simple Release (above),aux1|Auxiliary 1,aux2|Auxiliary 2,stat1|Statistic 1,stat2|Statistic 2:tfcvid:tfcvname::0","label"=>"Conditional Flow-By Var ","visible"=>1, "readonly"=>0, "width"=>6),
           "tiered_flowby_cfb_condition"=>array("type"=>3,"params"=>"lt|<,gt|>:ecfbid:ecfbname::0","label"=>"Flow-By is ","visible"=>1, "readonly"=>0, "width"=>6),
           // auxilliary variables go here
           "aux1_eqn"=>array("type"=>1,"params"=>"","label"=>"Aux1 Eqn.","visible"=>1, "readonly"=>0, "width"=>20),
           "aux1_desc"=>array("type"=>1,"params"=>"","label"=>"Aux1 Descr.","visible"=>1, "readonly"=>0, "width"=>20),
           "aux2_eqn"=>array("type"=>1,"params"=>"","label"=>"Aux2 Eqn.","visible"=>1, "readonly"=>0, "width"=>20),
           "aux2_desc"=>array("type"=>1,"params"=>"","label"=>"Aux2 Descr.","visible"=>1, "readonly"=>0, "width"=>20),
           // auxilliary stat
           "stat1_statname"=>array("type"=>3,"params"=>"min|Min,max|Max,mean|Mean,median|Median,stddev|Std. Dev.,pow|Power(x ^ y),log|ln,log10|log base 10,stack|Stack:typeid:typename::0","label"=>"Statistical Operator","visible"=>1, "readonly"=>0, "width"=>24),
           "stat1_operands"=>array("type"=>1,"params"=>"","label"=>"CSV List of operands","visible"=>1, "readonly"=>0, "width"=>12),
           "stat1_desc"=>array("type"=>1,"params"=>"","label"=>"Desc","visible"=>1, "readonly"=>0, "width"=>24),
           "stat2_statname"=>array("type"=>3,"params"=>"min|Min,max|Max,mean|Mean,median|Median,stddev|Std. Dev.,pow|Power(x ^ y),log|ln,log10|log base 10,stack|Stack:typeid:typename::0","label"=>"Statistical Operator","visible"=>1, "readonly"=>0, "width"=>24),
           "stat2_operands"=>array("type"=>1,"params"=>"","label"=>"CSV List of operands","visible"=>1, "readonly"=>0, "width"=>12),
           "stat2_desc"=>array("type"=>1,"params"=>"","label"=>"Desc","visible"=>1, "readonly"=>0, "width"=>24),
           
           
           // reservoir info
           "storage_stage_area"=>array("type"=>27,"params"=>"3:0:2:8:1:0","label"=>"Impoundment Geometry","visible"=>1, "readonly"=>0, "width"=>6),
           "maxcapacity"=>array("type"=>1,"params"=>"","label"=>"Max Impoundment Storage (ac-ft)","visible"=>1, "readonly"=>0, "width"=>8),
           "unusable_storage"=>array("type"=>1,"params"=>"","label"=>"Dead Storage (ac-ft)","visible"=>1, "readonly"=>0, "width"=>8),
           "initstorage"=>array("type"=>1,"params"=>"","label"=>"Initial Impoundment Storage (ac-ft)","visible"=>1, "readonly"=>0, "width"=>8),
           
           "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
           "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
           "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
           "watershed_map"=>array("type"=>-1,"params"=>"","label"=>"Watershed","visible"=>1, "readonly"=>1, "width"=>6),
           "locid"=>array("type"=>-1,"params"=>"","label"=>"Loc ID? ","visible"=>1, "readonly"=>1, "width"=>6),
           "debug"=>array("type"=>3,"params"=>"0|False,1|True:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6)
        )
     );


   $adminsetuparray["project_info"] = array(
       "table info"=>array("templateid"=>321861, "object_custom1"=>'cova_vwp_projinfo', 'runform'=>'vwp', "templatefile"=>"./forms/project_info.html", "pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column", "child_formats"=>array() ),
       "column info"=>array(
           "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
           "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
           "project_type"=>array("type"=>3,"params"=>"1|Direct Withdrawal,2|Off-Stream Storage,3|On-Stream Storage:vpdesid:vpdesname::0","label"=>"Currently in VPDES? ","visible"=>1, "readonly"=>0, "width"=>6),
           "wd_lat"=>array("type"=>1,"params"=>"","label"=>"WD Lat","visible"=>1, "readonly"=>0, "width"=>8),
           "wd_lon"=>array("type"=>1,"params"=>"","label"=>"WD Lon","visible"=>1, "readonly"=>0, "width"=>8),
           "imp_lat"=>array("type"=>1,"params"=>"","label"=>"IM Lat","visible"=>1, "readonly"=>0, "width"=>8),
           "imp_lon"=>array("type"=>1,"params"=>"","label"=>"IM Lon","visible"=>1, "readonly"=>0, "width"=>8),
           "source_name"=>array("type"=>1,"params"=>"","label"=>"Source:","visible"=>1, "readonly"=>0, "width"=>32),
           "vwp_current"=>array("type"=>3,"params"=>"0|False,1|True:vwpid:vwpname::0","label"=>"Currently in VWP? ","visible"=>1, "readonly"=>0, "width"=>6),
           "vwp_id"=>array("type"=>1,"params"=>"","label"=>"VWP","visible"=>1, "readonly"=>0, "width"=>16),
           "vpdes_current"=>array("type"=>3,"params"=>"0|False,1|True:vpdesid:vpdesname::0","label"=>"Currently in VPDES? ","visible"=>1, "readonly"=>0, "width"=>6),
           "vpdes_id"=>array("type"=>1,"params"=>"","label"=>"VDES","visible"=>1, "readonly"=>0, "width"=>16),
           "vpdes_outfalls"=>array("type"=>27,"params"=>"1:0:2:8","label"=>"Outfall #s","visible"=>1, "readonly"=>0, "width"=>6),
           "vwuds_current"=>array("type"=>3,"params"=>"0|False,1|True:vwudsid:vwudsname::0","label"=>"Currently in VWUDS? ","visible"=>1, "readonly"=>0, "width"=>6),
           "vwuds_userid"=>array("type"=>1,"params"=>"","label"=>"VWUDS","visible"=>1, "readonly"=>0, "width"=>16),
           
           "intake_drainage_area"=>array("type"=>1,"params"=>"","label"=>"Drainage Area (sqmi)","visible"=>1, "readonly"=>0, "width"=>8),
           "Qintake"=>array("type"=>1,"params"=>"","label"=>"Intake Equation","visible"=>1, "readonly"=>0, "width"=>48),
           "drainage_area"=>array("type"=>1,"params"=>"","label"=>"Drainage Area (sqmi)","visible"=>1, "readonly"=>0, "width"=>8),
           "flow_mode"=>array("type"=>3,"params"=>"0|Best Available,1|USGS Baseline,2|USGS Synthetic,3|VAHydro HSPF,4|USGS Historical:fmid:fmname::0","label"=>"Flow Mode (0 - best available, 1 - USGS baseline, 2 - USGS Synth, 3 - VA HSPF, 4+ - Custom) ","visible"=>1, "readonly"=>0, "width"=>6),
           "surrogate_gage"=>array("type"=>1,"params"=>"","label"=>"USGS Gage ID","visible"=>1, "readonly"=>0, "width"=>8),
           
           // withdrawal / refill characteristics
           "annual_mg"=>array("type"=>1,"params"=>"","label"=>"Max Annual (MG)","visible"=>1, "readonly"=>0, "width"=>8),
           "max_mgd"=>array("type"=>1,"params"=>"","label"=>"Max Day (MG)","visible"=>1, "readonly"=>0, "width"=>8),
           "maxmonth_mg"=>array("type"=>1,"params"=>"","label"=>"Max Month (MG)","visible"=>1, "readonly"=>0, "width"=>8),
           "has_storage"=>array("type"=>3,"params"=>"0|False,1|True:hsmid:hsmname::0","label"=>"Has Storage? ","visible"=>1, "readonly"=>0, "width"=>6),
           "monthly_pct"=>array("type"=>27,"params"=>"2:13:2:8","label"=>"Monthly %","visible"=>1, "readonly"=>0, "width"=>6),
		   // daily demand and conservation
           "max_mgd"=>array("type"=>1,"params"=>"","label"=>"Max Day (MG)","visible"=>1, "readonly"=>0, "width"=>8),
		   "safeyield_mgd_demand_eqn"=>array("type"=>1,"params"=>"","label"=>"Demand","visible"=>1, "readonly"=>0, "width"=>32),
		   "safeyield_mgd_cc_watch"=>array("type"=>1,"params"=>"","label"=>"Drought Watch Reduction","visible"=>1, "readonly"=>0, "width"=>4),
		   "safeyield_mgd_cc_warning"=>array("type"=>1,"params"=>"","label"=>"Drought Warning Reduction","visible"=>1, "readonly"=>0, "width"=>4),
		   "safeyield_mgd_cc_emergency"=>array("type"=>1,"params"=>"","label"=>"Drought Emergency Reduction","visible"=>1, "readonly"=>0, "width"=>4),
		   "safeyield_mgd_enable_conservation"=>array("type"=>3,"params"=>"disabled|Disabled,internal|Enabled,custom|Custom:eccvid:eccvname::0","label"=>"Enable Conservation ","visible"=>1, "readonly"=>0, "width"=>6),
           
           // flowby stuff
           // the simple flowby here
          "flowby_type"=>array("type"=>3,"params"=>"0|Simple,1|Tiered:fbtid:fbtname::0","label"=>"Flow-By Type ","visible"=>1,
          "readonly"=>0, "width"=>6),
           "simple_flowby_desc"=>array("type"=>1,"params"=>"","label"=>"Description of Flow-By","visible"=>1, "readonly"=>0, "width"=>48),
           "simple_flowby_eqn"=>array("type"=>1,"params"=>"","label"=>"Flow-By","visible"=>1, "readonly"=>0, "width"=>48),
           "simple_flowby_cfb_var"=>array("type"=>3,"params"=>"Qintake|Flow Past Intake (cfs),impoundment_Qin|Imp. Inflow (cfs),aux1|Auxiliary 1,aux2|Auxiliary 2,stat1|Statistic 1,stat2|Statistic 2:ecvid:ecvname::0","label"=>"Conditional Flow-By Var ","visible"=>1, "readonly"=>0, "width"=>6),
           "simple_flowby_enable_cfb"=>array("type"=>26,"params"=>"1:NULL:0","label"=>"Enable Conditional Flow-By ","visible"=>1, "readonly"=>0, "width"=>6),
           "simple_flowby_cfb_condition"=>array("type"=>3,"params"=>"lt|<,gt|>:ecfbid:ecfbname::0","label"=>"Flow-By is ","visible"=>1, "readonly"=>0, "width"=>6),
           // a 1-tiered flowby goes here
           "tiered_flowby_matrix"=>array("type"=>27,"params"=>"2:0:2:8","label"=>"Flow-By","visible"=>1, "readonly"=>0, "width"=>6),
           "tiered_flowby_tier_var"=>array("type"=>3,"params"=>"Qintake|Flow Past Intake (cfs),simple_flowby|Simple Flow-By (above),impoundment_use_remain_mg|Usable Storate Remaining (MG),impoundment_lake_elev|Lake Surface Elevation,month|Month,aux1|Auxiliary 1,aux2|Auxiliary 2,stat1|Statistic 1,stat2|Statistic 2:tftvid:tftvname::0","label"=>"Tier Variable Var ","visible"=>1, "readonly"=>0, "width"=>6),
           "tiered_flowby_enable_cfb"=>array("type"=>26,"params"=>"1:NULL:0","label"=>"Enable Conditional Flow-By ","visible"=>1, "readonly"=>0, "width"=>6),
           "tiered_flowby_cfb_var"=>array("type"=>3,"params"=>"Qintake|Flow Past Intake (cfs),impoundment_Qin|Imp. Inflow (cfs),simple_flowby|Simple Flow-By (above),aux1|Auxiliary 1,aux2|Auxiliary 2,stat1|Statistic 1,stat2|Statistic 2:tfcvid:tfcvname::0","label"=>"Conditional Flow-By Var ","visible"=>1, "readonly"=>0, "width"=>6),
           "tiered_flowby_cfb_condition"=>array("type"=>3,"params"=>"lt|<,gt|>:ecfbid:ecfbname::0","label"=>"Flow-By is ","visible"=>1, "readonly"=>0, "width"=>6),
           
           // auxilliary variables go here
           "aux1_eqn"=>array("type"=>1,"params"=>"","label"=>"Aux1 Eqn.","visible"=>1, "readonly"=>0, "width"=>20),
           "aux1_desc"=>array("type"=>1,"params"=>"","label"=>"Aux1 Descr.","visible"=>1, "readonly"=>0, "width"=>12),
           "aux2_eqn"=>array("type"=>1,"params"=>"","label"=>"Aux2 Eqn.","visible"=>1, "readonly"=>0, "width"=>20),
           "aux2_desc"=>array("type"=>1,"params"=>"","label"=>"Aux2 Descr.","visible"=>1, "readonly"=>0, "width"=>12),
           // auxilliary stat
           "stat1_statname"=>array("type"=>3,"params"=>"min|Min,max|Max,mean|Mean,median|Median,stddev|Std. Dev.,pow|Power(x ^ y),log|ln,log10|log base 10,stack|Stack:typeid:typename::0","label"=>"Statistical Operator","visible"=>1, "readonly"=>0, "width"=>24),
           "stat1_operands"=>array("type"=>1,"params"=>"","label"=>"CSV List of operands","visible"=>1, "readonly"=>0, "width"=>12),
           "stat1_desc"=>array("type"=>1,"params"=>"","label"=>"Desc","visible"=>1, "readonly"=>0, "width"=>12),
           "stat2_statname"=>array("type"=>3,"params"=>"min|Min,max|Max,mean|Mean,median|Median,stddev|Std. Dev.,pow|Power(x ^ y),log|ln,log10|log base 10,stack|Stack:typeid:typename::0","label"=>"Statistical Operator","visible"=>1, "readonly"=>0, "width"=>24),
           "stat2_operands"=>array("type"=>1,"params"=>"","label"=>"CSV List of operands","visible"=>1, "readonly"=>0, "width"=>12),
           "stat2_desc"=>array("type"=>1,"params"=>"","label"=>"Desc","visible"=>1, "readonly"=>0, "width"=>12),
           
           
           // reservoir info
           "imp_inflow"=>array("type"=>1,"params"=>"","label"=>"Impoundment Inflow Equation","visible"=>1, "readonly"=>0, "width"=>48),
           "storage_stage_area"=>array("type"=>27,"params"=>"3:0:2:8:1:0","label"=>"Impoundment Geometry","visible"=>1, "readonly"=>0, "width"=>6),
           "maxcapacity"=>array("type"=>1,"params"=>"","label"=>"Max Impoundment Storage (ac-ft)","visible"=>1, "readonly"=>0, "width"=>8),
           "unusable_storage"=>array("type"=>1,"params"=>"","label"=>"Dead Storage (ac-ft)","visible"=>1, "readonly"=>0, "width"=>8),
           "initstorage"=>array("type"=>1,"params"=>"","label"=>"Initial Impoundment Storage (ac-ft)","visible"=>1, "readonly"=>0, "width"=>8),
           "imp_et_in"=>array("type"=>3,"params"=>"et_in|et_in,aux1|Auxiliary 1,aux2|Auxiliary 2,stat1|Statistic 1,stat2|Statistic 2:ecvid:ecvname::0","label"=>"Evap Var ","visible"=>1, "readonly"=>0, "width"=>6),
           "imp_precip_in"=>array("type"=>3,"params"=>"precip_in|precip_in,aux1|Auxiliary 1,aux2|Auxiliary 2,stat1|Statistic 1,stat2|Statistic 2:ecvid:ecvname::0","label"=>"Precip Var ","visible"=>1, "readonly"=>0, "width"=>6),
           // Release rules
           // the simple release here
          "release_type"=>array("type"=>3,"params"=>"0|Simple,1|Tiered:fbtid:fbtname::0","label"=>"Release Type ","visible"=>1,
          "readonly"=>0, "width"=>6),
           "simple_release_desc"=>array("type"=>1,"params"=>"","label"=>"Flow-By","visible"=>1, "readonly"=>0, "width"=>48),
           "simple_release_eqn"=>array("type"=>1,"params"=>"","label"=>"Flow-By","visible"=>1, "readonly"=>0, "width"=>48),
           "simple_release_cfb_var"=>array("type"=>3,"params"=>"impoundment_Qin|Imp. Inflow (cfs),tiered_release|Tiered Release (below),simple_flowby|Simple Flow-By (left),tiered_flowby|Tiered Flow-By (left),aux1|Auxiliary 1,aux2|Auxiliary 2,stat1|Statistic 1,stat2|Statistic 2:ecvid:ecvname::0","label"=>"Conditional Flow-By Var ","visible"=>1, "readonly"=>0, "width"=>6),
           "simple_release_enable_cfb"=>array("type"=>26,"params"=>"1:NULL:0","label"=>"Enable Conditional Flow-By ","visible"=>1, "readonly"=>0, "width"=>6),
           "simple_release_cfb_condition"=>array("type"=>3,"params"=>"lt|<,gt|>:ecfbid:ecfbname::0","label"=>"Flow-By is ","visible"=>1, "readonly"=>0, "width"=>6),
           // a 1-tiered release goes here
           "tiered_release_matrix"=>array("type"=>27,"params"=>"2:0:2:8","label"=>"Flow-By","visible"=>1, "readonly"=>0, "width"=>6),
           "tiered_release_tier_var"=>array("type"=>3,"params"=>"impoundment_Qin|Imp. Inflow (cfs),impoundment_use_remain_mg|Usable Storate Remaining (MG),impoundment_lake_elev|Lake Surface Elevation,month|Month,aux1|Auxiliary 1,aux2|Auxiliary 2,stat1|Statistic 1,stat2|Statistic 2:tftvid:tftvname::0","label"=>"Tier Variable Var ","visible"=>1, "readonly"=>0, "width"=>6),
           "tiered_release_enable_cfb"=>array("type"=>26,"params"=>"1:NULL:0","label"=>"Enable Conditional Flow-By ","visible"=>1, "readonly"=>0, "width"=>6),
           "tiered_release_cfb_var"=>array("type"=>3,"params"=>"impoundment_Qin|Imp. Inflow (cfs),simple_flowby|Simple Flow-By (above),aux1|Auxiliary 1,aux2|Auxiliary 2,stat1|Statistic 1,stat2|Statistic 2:tfcvid:tfcvname::0","label"=>"Conditional Release Var ","visible"=>1, "readonly"=>0, "width"=>6),
           "tiered_release_cfb_condition"=>array("type"=>3,"params"=>"lt|<,gt|>:ecfbid:ecfbname::0","label"=>"Flow-By is ","visible"=>1, "readonly"=>0, "width"=>6),
           // auxilliary matrix settings here 
           "aux_matrix_matrix"=>array("type"=>27,"params"=>"2:0:2:8","label"=>"Flow-By","visible"=>1, "readonly"=>0, "width"=>6),
           "aux_matrix_key1"=>array("type"=>3,"params"=>"impoundment_Qin|Imp. Inflow (cfs),impoundment_use_remain_mg|Usable Storate Remaining (MG),impoundment_lake_elev|Lake Surface Elevation,month|Month,aux1|Auxiliary 1,aux2|Auxiliary 2,stat1|Statistic 1,stat2|Statistic 2:tftvid:tftvname::0","label"=>"Tier Variable Var ","visible"=>1, "readonly"=>0, "width"=>6),
           "aux_matrix_key2"=>array("type"=>3,"params"=>"impoundment_Qin|Imp. Inflow (cfs),impoundment_use_remain_mg|Usable Storate Remaining (MG),impoundment_lake_elev|Lake Surface Elevation,month|Month,aux1|Auxiliary 1,aux2|Auxiliary 2,stat1|Statistic 1,stat2|Statistic 2:tftvid:tftvname::0","label"=>"Tier Variable Var ","visible"=>1, "readonly"=>0, "width"=>6),
           "aux_matrix_lu1"=>array("type"=>3,"params"=>"0|Exact Match,1|Interpolated,2|Stair Step,3|Key Interpolate:lu1id:lu1name::0","label"=>"Lookup Type","visible"=>1, "readonly"=>0, "width"=>24),
           "aux_matrix_lu2"=>array("type"=>3,"params"=>"0|Exact Match,1|Interpolated,2|Stair Step,3|Key Interpolate:lu1id:lu1name::0","label"=>"Lookup Type","visible"=>1, "readonly"=>0, "width"=>24),
           
           "vwuds_mpids"=>array("type"=>27,"params"=>"1:0:2:8","label"=>"MPIDs","visible"=>1, "readonly"=>0, "width"=>6),
           "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
           "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
           "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
           "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
           "watershed_map"=>array("type"=>-1,"params"=>"","label"=>"Watershed","visible"=>1, "readonly"=>1, "width"=>6),
           "locid"=>array("type"=>-1,"params"=>"","label"=>"Loc ID? ","visible"=>1, "readonly"=>1, "width"=>6),
           "debug"=>array("type"=>3,"params"=>"0|False,1|True:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6)
        )
     );

   $adminsetuparray["waterproject_info"] = array(
       "table info"=>array("pk"=>"elementid","templateid"=>176170, "object_custom1"=>'greenDesignProject', 'runform'=>'generic', "templatefile"=>"./forms/waterproject_info.html", "sortcol"=>"name", "outputformat"=>"column", "child_formats"=>array() ),
       "column info"=>array(
           "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
           "project"=>array("type"=>1,"params"=>"","label"=>"Project Name","visible"=>1, "readonly"=>0, "width"=>64),
           "street"=>array("type"=>1,"params"=>"","label"=>"Street ","visible"=>1, "readonly"=>0, "width"=>6),
           "city"=>array("type"=>1,"params"=>"","label"=>"City","visible"=>1, "readonly"=>0, "width"=>8),
           "area"=>array("type"=>1,"params"=>"","label"=>"Lot Size (acres)","visible"=>1, "readonly"=>0, "width"=>8),
           "CN"=>array("type"=>1,"params"=>"","label"=>"Curve Number","visible"=>1, "readonly"=>0, "width"=>8),
           "roof"=>array("type"=>1,"params"=>"","label"=>"Roof Area (sq ft)","visible"=>1, "readonly"=>0, "width"=>8),
           "baths"=>array("type"=>1,"params"=>"","label"=>"# Bathrooms","visible"=>1, "readonly"=>0, "width"=>8),
           "occupants"=>array("type"=>1,"params"=>"","label"=>"# Full-Time Occupants","visible"=>1, "readonly"=>0, "occupants"=>8),
           "laundry"=>array("type"=>1,"params"=>"","label"=>"# Loads opf Laundry Per Week","visible"=>1, "readonly"=>0, "occupants"=>8),
           "shwr_time"=>array("type"=>1,"params"=>"","label"=>"Avg. Shower Duration (hours)","visible"=>1, "readonly"=>0, "occupants"=>8),
           "shwr_wk"=>array("type"=>1,"params"=>"","label"=>"# Showers per person/week","visible"=>1, "readonly"=>0, "occupants"=>8),
           "area_turf"=>array("type"=>1,"params"=>"","label"=>"Area of irrigated turf (sq feet)","visible"=>1, "readonly"=>0, "occupants"=>8),
           "area_food"=>array("type"=>1,"params"=>"","label"=>"Area of irrigated fruits/veg (sq feet)","visible"=>1, "readonly"=>0, "occupants"=>8),
           "lon"=>array("type"=>1,"params"=>"","label"=>"Lon","visible"=>1, "readonly"=>0, "width"=>8),
           "lat"=>array("type"=>1,"params"=>"","label"=>"Lat","visible"=>1, "readonly"=>0, "width"=>8),
           "storage_gallons"=>array("type"=>1,"params"=>"","label"=>"Lat","visible"=>1, "readonly"=>0, "width"=>8)
         )
     );

     
   $adminsetuparray["fe_project_info"] = array(
       "table info"=>array("templateid"=>327568, "object_custom1"=>'cova_fe_project', 'runform'=>'wsp', "templatefile"=>"./forms/fe_project.html", "pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column", "child_formats"=>array() ),
       "column info"=>array(
           "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
           "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
           "project_type"=>array("type"=>3,"params"=>"1|Tributary Riverine,2|Tributary Impoundment,3|Tributary Pump-Store - TBD,4|Mainstem Riverine,5|Mainstem Impoundment - TBD,6|Pass-Through Demand:vpdesid:vpdesname::0","label"=>"Currently in VPDES? ","visible"=>1, "readonly"=>0, "width"=>6),
           "wd_lat"=>array("type"=>1,"params"=>"","label"=>"WD Lat","visible"=>1, "readonly"=>0, "width"=>8, 'onchange'=>'xajax_showLocationSelector(xajax.getFormValues("form1"));'),
           "wd_lon"=>array("type"=>1,"params"=>"","label"=>"WD Lon","visible"=>1, "readonly"=>0, "width"=>8, 'onchange'=>'xajax_showLocationSelector(xajax.getFormValues("form1"));'),
           "imp_lat"=>array("type"=>1,"params"=>"","label"=>"IM Lat","visible"=>1, "readonly"=>0, "width"=>8),
           "imp_lon"=>array("type"=>1,"params"=>"","label"=>"IM Lon","visible"=>1, "readonly"=>0, "width"=>8),
           "source_name"=>array("type"=>1,"params"=>"","label"=>"Source:","visible"=>1, "readonly"=>0, "width"=>32),
           "vwp_current"=>array("type"=>3,"params"=>"0|False,1|True:vwpid:vwpname::0","label"=>"Currently in VWP? ","visible"=>1, "readonly"=>0, "width"=>6),
           "vwp_id"=>array("type"=>1,"params"=>"","label"=>"VWP","visible"=>1, "readonly"=>0, "width"=>16),
           "vpdes_current"=>array("type"=>3,"params"=>"0|False,1|True:vpdesid:vpdesname::0","label"=>"Currently in VPDES? ","visible"=>1, "readonly"=>0, "width"=>6),
           "vpdes_id"=>array("type"=>1,"params"=>"","label"=>"VDES","visible"=>1, "readonly"=>0, "width"=>16),
           "vpdes_outfalls"=>array("type"=>27,"params"=>"1:0:2:8","label"=>"Outfall #s","visible"=>1, "readonly"=>0, "width"=>6),
           "vwuds_current"=>array("type"=>3,"params"=>"0|False,1|True:vwudsid:vwudsname::0","label"=>"Currently in VWUDS? ","visible"=>1, "readonly"=>0, "width"=>6),
           "vwuds_userid"=>array("type"=>1,"params"=>"","label"=>"VWUDS","visible"=>1, "readonly"=>0, "width"=>16),
           
           "intake_drainage_area"=>array("type"=>1,"params"=>"","label"=>"Drainage Area (sqmi)","visible"=>1, "readonly"=>0, "width"=>8),
           "Qintake"=>array("type"=>1,"params"=>"","label"=>"Intake Equation","visible"=>1, "readonly"=>0, "width"=>48),
           "drainage_area"=>array("type"=>1,"params"=>"","label"=>"Drainage Area (sqmi)","visible"=>1, "readonly"=>0, "width"=>8),
           "flow_mode"=>array("type"=>3,"params"=>"0|Best Available,1|USGS Baseline,2|USGS Synthetic,3|VAHydro HSPF,4|USGS Historical:fmid:fmname::0","label"=>"Flow Mode (0 - best available, 1 - USGS baseline, 2 - USGS Synth, 3 - VA HSPF, 4+ - Custom) ","visible"=>1, "readonly"=>0, "width"=>6),
           "surrogate_gage"=>array("type"=>1,"params"=>"","label"=>"USGS Gage ID","visible"=>1, "readonly"=>0, "width"=>8),
           
           // landuse
           "cbp_runoff"=>array("type"=>27,"params"=>"3:0:2:8","label"=>"Monthly %","visible"=>1, "readonly"=>0, "width"=>6),
           "cbp_nearest_landseg"=>array("type"=>1,"params"=>"","label"=>"Nearest Landseg","visible"=>1, "readonly"=>1, "width"=>6),
           "cbp_landseg"=>array("type"=>1,"params"=>"","label"=>"Nearest Landseg","visible"=>1, "readonly"=>0, "width"=>6),
           "reload_nlcd"=>array("type"=>3,"params"=>"0|False,1|True:rnid:rnname::0","label"=>"Reload NHD+ Landuse? ","visible"=>1,"readonly"=>0, "width"=>6),
           "nhd_channel_length"=>array("type"=>1, "params"=>"","label"=>"Channel Length (ft)","visible"=>1, "readonly"=>'1', "width"=>6),
           "nhd_channel_slope"=>array("type"=>1, "params"=>"","label"=>"Channel Length (ft)","visible"=>1, "readonly"=>'1', "width"=>6),
           "nhd_channel_drainage"=>array("type"=>1, "params"=>"","label"=>"Channel Drainage (sqmi)","visible"=>1, "readonly"=>'1', "width"=>6),
           
           // channel
           "channel_reset_channelprops"=>array("type"=>3,"params"=>"0|False,1|True:rcpid:rcpname::0:","label"=>"Recalculate Channel Properties? ","visible"=>1, "readonly"=>0, "width"=>6),
           "channel_area"=>array("type"=>1,"params"=>"","label"=>"Max Annual (MG)","visible"=>1, "readonly"=>0, "width"=>8),
           "channel_drainage_area"=>array("type"=>1,"params"=>"","label"=>"Max Annual (MG)","visible"=>1, "readonly"=>0, "width"=>8),
           "channel_r_var"=>array("type"=>3,"params"=>"Runit|from Parent Model,Qlocal|from Custom Land-use Runoff:fmid:fmname::0","label"=>"Channel Runoff Input","visible"=>1, "readonly"=>0, "width"=>6),
           "channel_province"=>array("type"=>3,"params"=>"1|Appalachian Plateau,2|Valley and Ridge,3|Piedmont,4|Coastal Plain:prid:prname::0","label"=>"Physiographic Province: ","visible"=>1, "readonly"=>0, "width"=>6),
           "channel_base"=>array("type"=>1,"params"=>"","label"=>"Channel Base Width(ft)","visible"=>1, "readonly"=>0, "width"=>12),
           "channel_slope"=>array("type"=>1, "params"=>"","label"=>"Channel Slope (ft/ft)","visible"=>1, "readonly"=>'0', "width"=>6),
           "channel_length"=>array("type"=>1, "params"=>"","label"=>"Channel Length (ft)","visible"=>1, "readonly"=>'0', "width"=>6),
           "channel_Z"=>array("type"=>1, "params"=>"", "label"=>"Side-Slope Ratio","visible"=>1, "readonly"=>0, "width"=>24),
           "channel_n"=>array("type"=>1, "params"=>"", "label"=>"Mannings N - Channel Roughness","visible"=>1, "readonly"=>'0', "width"=>24),
           "channel_substrateclass"=>array("type"=>3,"params"=>"A|A,B|B,C|C,D|D:fmid:fmname::0", "label"=>"Substrate Class","visible"=>1, "readonly"=>'0', "width"=>24),
           
           // point source info
           "ps_src"=>array("type"=>3,"params"=>"1|Custom,2|VA Hydro,3|VPDES Current,4|VPDES Historic,5|WSP Current,6|WSP 2040,7|WSP Max:cpsmid:cpsmname::0","label"=>"Discharge Data Source? ","visible"=>1, "readonly"=>0, "width"=>6),
           "ps_calc_mgd_eqn"=>array("type"=>1,"params"=>"","label"=>"Custom Point Source","visible"=>1, "readonly"=>0, "width"=>20),
           
           // withdrawal / refill characteristics
           "wd_src"=>array("type"=>3,"params"=>"1|Custom,2|VAHydro Scenario,3|VWUDS Current,4|WSP Current,5|WSP 2040,6|WSP Max:cwdsmid:cwdsmname::0","label"=>"Withdrawal Source? ","visible"=>1, "readonly"=>0, "width"=>6),
           "annual_mg"=>array("type"=>1,"params"=>"","label"=>"Max Annual (MG)","visible"=>1, "readonly"=>0, "width"=>8),
           "max_mgd"=>array("type"=>1,"params"=>"","label"=>"Max Day (MG)","visible"=>1, "readonly"=>0, "width"=>8),
           "maxmonth_mg"=>array("type"=>1,"params"=>"","label"=>"Max Month (MG)","visible"=>1, "readonly"=>0, "width"=>8),
           "has_storage"=>array("type"=>3,"params"=>"0|False,1|True:hsmid:hsmname::0","label"=>"Has Storage? ","visible"=>1, "readonly"=>0, "width"=>6),
           "monthly_pct"=>array("type"=>27,"params"=>"2:13:2:8","label"=>"Monthly %","visible"=>1, "readonly"=>0, "width"=>6),
		   // daily demand and conservation
           "max_mgd"=>array("type"=>1,"params"=>"","label"=>"Max Day (MG)","visible"=>1, "readonly"=>0, "width"=>8),
		   "safeyield_mgd_demand_eqn"=>array("type"=>1,"params"=>"","label"=>"Demand","visible"=>1, "readonly"=>0, "width"=>32),
		   "safeyield_mgd_cc_watch"=>array("type"=>1,"params"=>"","label"=>"Drought Watch Reduction","visible"=>1, "readonly"=>0, "width"=>4),
		   "safeyield_mgd_cc_warning"=>array("type"=>1,"params"=>"","label"=>"Drought Warning Reduction","visible"=>1, "readonly"=>0, "width"=>4),
		   "safeyield_mgd_cc_emergency"=>array("type"=>1,"params"=>"","label"=>"Drought Emergency Reduction","visible"=>1, "readonly"=>0, "width"=>4),
		   "safeyield_mgd_enable_conservation"=>array("type"=>3,"params"=>"disabled|Disabled,internal|Enabled,custom|Custom:eccvid:eccvname::0","label"=>"Enable Conservation ","visible"=>1, "readonly"=>0, "width"=>6),
           
           // flowby stuff
           // the simple flowby here
          "flowby_type"=>array("type"=>3,"params"=>"0|Simple,1|Tiered:fbtid:fbtname::0","label"=>"Flow-By Type ","visible"=>1,
          "readonly"=>0, "width"=>6),
           "simple_flowby_desc"=>array("type"=>1,"params"=>"","label"=>"Description of Flow-By","visible"=>1, "readonly"=>0, "width"=>48),
           "simple_flowby_eqn"=>array("type"=>1,"params"=>"","label"=>"Flow-By","visible"=>1, "readonly"=>0, "width"=>48),
           "simple_flowby_cfb_var"=>array("type"=>3,"params"=>"Qintake|Flow Past Intake (cfs),impoundment_Qin|Imp. Inflow (cfs),aux1|Auxiliary 1,aux2|Auxiliary 2,stat1|Statistic 1,stat2|Statistic 2:ecvid:ecvname::0","label"=>"Conditional Flow-By Var ","visible"=>1, "readonly"=>0, "width"=>6),
           "simple_flowby_enable_cfb"=>array("type"=>26,"params"=>"1:NULL:0","label"=>"Enable Conditional Flow-By ","visible"=>1, "readonly"=>0, "width"=>6),
           "simple_flowby_cfb_condition"=>array("type"=>3,"params"=>"lt|<,gt|>:ecfbid:ecfbname::0","label"=>"Flow-By is ","visible"=>1, "readonly"=>0, "width"=>6),
           // a 1-tiered flowby goes here
           "tiered_flowby_matrix"=>array("type"=>27,"params"=>"2:0:2:8","label"=>"Flow-By","visible"=>1, "readonly"=>0, "width"=>6),
           "tiered_flowby_tier_var"=>array("type"=>3,"params"=>"Qintake|Flow Past Intake (cfs),simple_flowby|Simple Flow-By (above),impoundment_use_remain_mg|Usable Storate Remaining (MG),impoundment_lake_elev|Lake Surface Elevation,month|Month,aux1|Auxiliary 1,aux2|Auxiliary 2,stat1|Statistic 1,stat2|Statistic 2:tftvid:tftvname::0","label"=>"Tier Variable Var ","visible"=>1, "readonly"=>0, "width"=>6),
           "tiered_flowby_enable_cfb"=>array("type"=>26,"params"=>"1:NULL:0","label"=>"Enable Conditional Flow-By ","visible"=>1, "readonly"=>0, "width"=>6),
           "tiered_flowby_cfb_var"=>array("type"=>3,"params"=>"Qintake|Flow Past Intake (cfs),impoundment_Qin|Imp. Inflow (cfs),simple_flowby|Simple Flow-By (above),aux1|Auxiliary 1,aux2|Auxiliary 2,stat1|Statistic 1,stat2|Statistic 2:tfcvid:tfcvname::0","label"=>"Conditional Flow-By Var ","visible"=>1, "readonly"=>0, "width"=>6),
           "tiered_flowby_cfb_condition"=>array("type"=>3,"params"=>"lt|<,gt|>:ecfbid:ecfbname::0","label"=>"Flow-By is ","visible"=>1, "readonly"=>0, "width"=>6),
           
           // auxilliary variables go here
           "aux1_eqn"=>array("type"=>1,"params"=>"","label"=>"Aux1 Eqn.","visible"=>1, "readonly"=>0, "width"=>20),
           "aux1_desc"=>array("type"=>1,"params"=>"","label"=>"Aux1 Descr.","visible"=>1, "readonly"=>0, "width"=>12),
           "aux2_eqn"=>array("type"=>1,"params"=>"","label"=>"Aux2 Eqn.","visible"=>1, "readonly"=>0, "width"=>20),
           "aux2_desc"=>array("type"=>1,"params"=>"","label"=>"Aux2 Descr.","visible"=>1, "readonly"=>0, "width"=>12),
           // auxilliary stat
           "stat1_statname"=>array("type"=>3,"params"=>"min|Min,max|Max,mean|Mean,median|Median,stddev|Std. Dev.,pow|Power(x ^ y),log|ln,log10|log base 10,stack|Stack:typeid:typename::0","label"=>"Statistical Operator","visible"=>1, "readonly"=>0, "width"=>24),
           "stat1_operands"=>array("type"=>1,"params"=>"","label"=>"CSV List of operands","visible"=>1, "readonly"=>0, "width"=>12),
           "stat1_desc"=>array("type"=>1,"params"=>"","label"=>"Desc","visible"=>1, "readonly"=>0, "width"=>12),
           "stat2_statname"=>array("type"=>3,"params"=>"min|Min,max|Max,mean|Mean,median|Median,stddev|Std. Dev.,pow|Power(x ^ y),log|ln,log10|log base 10,stack|Stack:typeid:typename::0","label"=>"Statistical Operator","visible"=>1, "readonly"=>0, "width"=>24),
           "stat2_operands"=>array("type"=>1,"params"=>"","label"=>"CSV List of operands","visible"=>1, "readonly"=>0, "width"=>12),
           "stat2_desc"=>array("type"=>1,"params"=>"","label"=>"Desc","visible"=>1, "readonly"=>0, "width"=>12),
           // auxilliary matrix settings here 
           "aux_matrix_matrix"=>array("type"=>27,"params"=>"2:0:2:8","label"=>"Flow-By","visible"=>1, "readonly"=>0, "width"=>6),
           "aux_matrix_key1"=>array("type"=>3,"params"=>"month|Month,tiered_release|Tiered Release,simple_release|Simple Release,tiered_flowby|Tiered Flowby,simple_flowby|Simple Flowby,impoundment_Qin|Imp. Inflow (cfs),impoundment_use_remain_mg|Usable Storate Remaining (MG),impoundment_lake_elev|Lake Surface Elevation,aux1|Auxiliary 1,aux2|Auxiliary 2,stat1|Statistic 1,stat2|Statistic 2:tftvid:tftvname::0","label"=>"Tier Variable Var ","visible"=>1, "readonly"=>0, "width"=>6),
           "aux_matrix_key2"=>array("type"=>3,"params"=>",month|Month,tiered_release|Tiered Release,simple_release|Simple Release,tiered_flowby|Tiered Flowby,simple_flowby|Simple Flowby,impoundment_Qin|Imp. Inflow (cfs),impoundment_use_remain_mg|Usable Storate Remaining (MG),impoundment_lake_elev|Lake Surface Elevationaux1|Auxiliary 1,aux2|Auxiliary 2,stat1|Statistic 1,stat2|Statistic 2:tftvid:tftvname::0","label"=>"Tier Variable Var ","visible"=>1, "readonly"=>0, "width"=>6),
           "aux_matrix_lu1"=>array("type"=>3,"params"=>"0|Exact Match,1|Interpolated,2|Stair Step,3|Key Interpolate:lu1id:lu1name::0","label"=>"Lookup Type","visible"=>1, "readonly"=>0, "width"=>24),
           "aux_matrix_lu2"=>array("type"=>3,"params"=>"0|Exact Match,1|Interpolated,2|Stair Step,3|Key Interpolate:lu1id:lu1name::0","label"=>"Lookup Type","visible"=>1, "readonly"=>0, "width"=>24),
           
           
           // reservoir info
           "imp_inflow"=>array("type"=>1,"params"=>"","label"=>"Impoundment Inflow Equation","visible"=>1, "readonly"=>0, "width"=>48),
           "storage_stage_area"=>array("type"=>27,"params"=>"3:0:2:8:1:0","label"=>"Impoundment Geometry","visible"=>1, "readonly"=>0, "width"=>6),
           "maxcapacity"=>array("type"=>1,"params"=>"","label"=>"Max Impoundment Storage (ac-ft)","visible"=>1, "readonly"=>0, "width"=>8),
           "unusable_storage"=>array("type"=>1,"params"=>"","label"=>"Dead Storage (ac-ft)","visible"=>1, "readonly"=>0, "width"=>8),
           "initstorage"=>array("type"=>1,"params"=>"","label"=>"Initial Impoundment Storage (ac-ft)","visible"=>1, "readonly"=>0, "width"=>8),
           "refill_max_mgd"=>array("type"=>1,"params"=>"","label"=>"Maximum Refill Pumping Rate (MGD)","visible"=>1, "readonly"=>0, "width"=>8),
           // Release rules
           // the simple release here
          "release_type"=>array("type"=>3,"params"=>"0|Simple,1|Tiered:fbtid:fbtname::0","label"=>"Release Type ","visible"=>1,
          "readonly"=>0, "width"=>6),
           "simple_release_desc"=>array("type"=>1,"params"=>"","label"=>"Flow-By","visible"=>1, "readonly"=>0, "width"=>48),
           "simple_release_eqn"=>array("type"=>1,"params"=>"","label"=>"Flow-By","visible"=>1, "readonly"=>0, "width"=>48),
           "simple_release_cfb_var"=>array("type"=>3,"params"=>"impoundment_Qin|Imp. Inflow (cfs),tiered_release|Tiered Release (below),simple_flowby|Simple Flow-By (left),tiered_flowby|Tiered Flow-By (left),aux1|Auxiliary 1,aux2|Auxiliary 2,stat1|Statistic 1,stat2|Statistic 2:ecvid:ecvname::0","label"=>"Conditional Flow-By Var ","visible"=>1, "readonly"=>0, "width"=>6),
           "simple_release_enable_cfb"=>array("type"=>26,"params"=>"1:NULL:0","label"=>"Enable Conditional Flow-By ","visible"=>1, "readonly"=>0, "width"=>6),
           "simple_release_cfb_condition"=>array("type"=>3,"params"=>"lt|<,gt|>:ecfbid:ecfbname::0","label"=>"Flow-By is ","visible"=>1, "readonly"=>0, "width"=>6),
           // a 1-tiered release goes here
           "tiered_release_matrix"=>array("type"=>27,"params"=>"2:0:2:8","label"=>"Flow-By","visible"=>1, "readonly"=>0, "width"=>6),
           "tiered_release_tier_var"=>array("type"=>3,"params"=>"impoundment_Qin|Imp. Inflow (cfs),impoundment_use_remain_mg|Usable Storate Remaining (MG),impoundment_lake_elev|Lake Surface Elevation,month|Month,aux1|Auxiliary 1,aux2|Auxiliary 2,stat1|Statistic 1,stat2|Statistic 2:tftvid:tftvname::0","label"=>"Tier Variable Var ","visible"=>1, "readonly"=>0, "width"=>6),
           "tiered_release_enable_cfb"=>array("type"=>26,"params"=>"1:NULL:0","label"=>"Enable Conditional Flow-By ","visible"=>1, "readonly"=>0, "width"=>6),
           "tiered_release_cfb_var"=>array("type"=>3,"params"=>"impoundment_Qin|Imp. Inflow (cfs),simple_flowby|Simple Flow-By (above),aux1|Auxiliary 1,aux2|Auxiliary 2,stat1|Statistic 1,stat2|Statistic 2:tfcvid:tfcvname::0","label"=>"Conditional Release Var ","visible"=>1, "readonly"=>0, "width"=>6),
           "tiered_release_cfb_condition"=>array("type"=>3,"params"=>"lt|<,gt|>:ecfbid:ecfbname::0","label"=>"Flow-By is ","visible"=>1, "readonly"=>0, "width"=>6),
           
           "vwuds_mpids"=>array("type"=>27,"params"=>"1:0:2:8","label"=>"MPIDs","visible"=>1, "readonly"=>0, "width"=>6),
           "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
           "scenarioid"=>array("type"=>3,"params"=>"scenario:scenarioid:scenario:scenario:0:","label"=>"Scenario ID","visible"=>1, "readonly"=>0, "width"=>12),
           "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
           "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
           "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
           "watershed_map"=>array("type"=>-1,"params"=>"","label"=>"Watershed","visible"=>1, "readonly"=>1, "width"=>6),
           //"locid"=>array("type"=>-1,"params"=>"","label"=>"Loc ID? ","visible"=>1, "readonly"=>1, "width"=>6),
           "debug"=>array("type"=>3,"params"=>"0|False,1|True:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6)
        )
     );
     
/* end adminsetup array */

?>
