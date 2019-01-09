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
   $aset_analysis = array(

      # water supply planning components

      "cova_model_info"=>array(
         # an aggregate view screen, right now we define only a search interface
          "table info"=>array("table_name"=>'cova_model_info', "tabledef"=>"", "pk"=>"", "sortcol"=>"facility", "outputformat"=>"column", 'output type'=>'tabbed', 'form_title'=>'',
             'tabs'=>array(
                'general_info'=> array('tab label'=>'General Info'),
                'contact_info'=> array('tab label'=>'Contact Info'),
                'contact_info2'=> array('tab label'=>'Secondary Contact Info'),
                'geo_info'=> array('tab label'=>'Geographic Info'),
                'other_info'=> array('tab label'=>'Other')
             ),
             'width'=>'600px', 'height'=>'400px', 'divname'=>'vwuds_facilities', 'geom_col'=>'the_geom', 'extent_where'=>'( (lat_flt <> -99) AND (lon_flt <> -99))', 'srid'=>4326, 'maplabelcols'=>'facility',
          ),
          "search info"=>array("tabledef"=>"vwuds_facility_mp", "pk"=>"record_id", 'pk_search_var'=>'srch_record_id', "sortcol"=>"record_id", "outputformat"=>"column",
             'columns'=>array(
             )
          ),
          "column info"=>array(
             "geoscope"=>array("type"=>23, "params"=>"local|Local,cumulative|Entire Drainage Area:: | ", "label"=>"Withdrawal Status","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'admin_info', 'mandatory'=>1, 'default'=>'1'),
             "flow_analysis_pts"=>array("type"=>26, "params"=>"reach_out|Reach Outflow,reach_in|Total Reach Inflow,local_in|Local Inflow,upstream_in|Upstream Inflow: | ", "label"=>"Flow Point","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'admin_info', 'mandatory'=>1, 'default'=>'1'),
             "wd_datasources"=>array("type"=>26,"params"=>"vwuds|VWUDS,wsp|Water Supply Plan: | ","label"=>"Withdrawal Data Sets","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'admin_info', 'mandatory'=>1, 'default'=>'1'),
             //"flow_analysis_metrics"=>array("type"=>26, "params"=>"min|Min,max|Max,avg|Mean,median|Median,stddev|Std. Dev.: | ", "label"=>"Flow Stats","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'admin_info', 'mandatory'=>1, 'default'=>'1')
             "flow_analysis_metrics"=>array("type"=>26, "params"=>"min|Min,max|Max,avg|Mean,median|Median,pct01|1st %ile,pct05|5th %ile,pct10|10th %ile,pct25|25th %ile,gini|Gini,stddev|Std. Dev.: | ", "label"=>"Flow Stats","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'admin_info', 'mandatory'=>1, 'default'=>'1'),
             "summary_resolution"=>array("type"=>3,"params"=>"monthly|Monthly,daily|Daily:ecvid:ecvname::0","label"=>"Conditional Flow-By Var ","visible"=>1, "readonly"=>0, "width"=>6),
           )
        )
    );

/* end adminsetup array */

?>
