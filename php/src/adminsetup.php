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
   $adminsetuparray = array(

      # water supply planning components

      "facility_view"=>array(
         # an aggregate view screen, right now we define only a search interface
          "table info"=>array("table_name"=>'facilities', "tabledef"=>"vwuds_facility_mp", "pk"=>"record_id", "sortcol"=>"facility", "outputformat"=>"column", 'output type'=>'tabbed', 'form_title'=>'Water Using Facilities',
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
                'userid'=> array('searchtype'=>1, 'search_var'=>'srch_userid', 'label'=>'User ID'),
                "ownname"=>array("searchtype"=>1, 'label'=>'Owner Name'),
                "facility"=>array("searchtype"=>1, 'label'=>'Facility Name'),
                "system"=>array("searchtype"=>1, 'label'=>'System Name'),
                "MPID"=>array("searchtype"=>1, 'label'=>'MPID'),
                "fipscof"=>array("searchtype"=>3, "params"=>"us_localities:stcofips:name:name:0:", 'label'=>'Locality'),
                "hucfacstr"=>array("searchtype"=>3, "params"=>"huc_va:huc:huc:huc:0:", 'label'=>'HUC8'),
                "region"=>array("searchtype"=>3, "params"=>"vwuds_deq_regions:reg_id:name:name:0:", 'label'=>'Region'),
                "category"=>array("searchtype"=>3, "params"=>"waterusetype:typeabbrev:typename:typename:0:", 'label'=>'Use Category'),
                "email_contact"=>array("searchtype"=>3,"params"=>"0|False,1|True:lid:lname::0", 'label'=>'Contact By Email Only?'),
                "email"=>array("searchtype"=>1),
                "bad_email"=>array("searchtype"=>3,"params"=>"0|False,1|True:beid:bename::0", 'label'=>'Email Address Marked as Bad?'),
                "reports_quarterly"=>array("searchtype"=>3,"params"=>"0|False,1|True:rqeid:rqename::0", 'label'=>'Reports Quarterly?'),
                "email"=>array("searchtype"=>1),
                "ownerid"=>array('searchtype'=>3,"params"=>"users:userid:lastname,firstname:lastname,firstname:0:userid in (select userid from mapusergroups where groupid in (3,4))", 'label'=>'Planner')
             )
          ),
          "column info"=>array(
            )
        ),

      "facilities"=>array(
          "table info"=>array("table_name"=>'facilities', "tabledef"=>"facilities", "pk"=>"gid", "sortcol"=>"facility", "outputformat"=>"column", 'output type'=>'tabbed', 'form_title'=>'Water Using Facilities',
             'tabs'=>array(
                'general_info'=> array('tab label'=>'General Info'),
                'contact_info'=> array('tab label'=>'Contact Info'),
                'contact_info2'=> array('tab label'=>'Secondary Contact Info'),
                'geo_info'=> array('tab label'=>'Geographic Info'),
                'other_info'=> array('tab label'=>'Other')
             ),
             'width'=>'600px', 'height'=>'400px', 'divname'=>'vwuds_facilities', 'geom_col'=>'the_geom', 'extent_where'=>'( (lat_flt <> -99) AND (lon_flt <> -99))', 'srid'=>4326, 'maplabelcols'=>'facility',
          ),
          "search info"=>array("tabledef"=>"facilities", "pk"=>"gid", 'pk_search_var'=>'srch_gid', "sortcol"=>"gid", "outputformat"=>"column",
             'columns'=>array(
                'userid'=> array('searchtype'=>1, 'search_var'=>'srch_userid', 'label'=>'User ID'),
                "ownname"=>array("searchtype"=>1),
                "facility"=>array("searchtype"=>1),
                "system"=>array("searchtype"=>1),
                "fipscof"=>array("searchtype"=>3, "params"=>"us_localities:stcofips:name:name:0:"),
                "hucfacstr"=>array("searchtype"=>3, "params"=>"huc_va:huc:huc:huc:0:"),
                "subyra"=>array("searchtype"=>1),
                "region"=>array("searchtype"=>3, "params"=>"vwuds_deq_regions:reg_id:name:name:0:"),
                "category"=>array("searchtype"=>3, "params"=>"waterusetype:typeabbrev:typename:typename:0:"),
                "email_contact"=>array("searchtype"=>3,"params"=>"0|False,1|True:lid:lname::0", 'label'=>'Contact By Email Only?'),
                "email"=>array("searchtype"=>1),
                "bad_email"=>array("searchtype"=>3,"params"=>"0|False,1|True:beid:bename::0", 'label'=>'Email Address Marked as Bad?'),
                "reports_quarterly"=>array("searchtype"=>3,"params"=>"0|False,1|True:rqeid:rqename::0", 'label'=>'Reports Quarterly?'),
                "email"=>array("searchtype"=>1),
                "ownerid"=>array('searchtype'=>3,"params"=>"users:userid:lastname,firstname:lastname,firstname:0:userid in (select userid from mapusergroups where groupid in (3,4))", 'label'=>'Planner')
             )
          ),
          "column info"=>array(
              "gid"=>array("type"=>1,"params"=>"","label"=>"Record ID: ","visible"=>1, "readonly"=>1, "width"=>32, "tab"=>'general_info'),
              "userid"=>array("type"=>1,"params"=>"","label"=>"User ID: ","visible"=>1, "readonly"=>1, "width"=>16, "tab"=>'general_info', 'mandatory'=>0, "maxlength"=>8),
              "ownname"=>array("type"=>1,"params"=>"","label"=>"Owner Name","visible"=>1, "readonly"=>0, "width"=>64, "tab"=>'general_info', 'mandatory'=>1, "maxlength"=>254),
              "facility"=>array("type"=>1,"params"=>"","label"=>"Facility Name","visible"=>1, "readonly"=>0, "width"=>64, "tab"=>'general_info', 'mandatory'=>1, "maxlength"=>254),
              "system"=>array("type"=>1,"params"=>"","label"=>"System Name","visible"=>1, "readonly"=>0, "width"=>64, "tab"=>'general_info', 'mandatory'=>1, "maxlength"=>254),
              "location"=>array("type"=>1,"params"=>"","label"=>"Location","visible"=>1, "readonly"=>0, "width"=>64, "tab"=>'general_info', "maxlength"=>254),
              "category"=>array("type"=>3,"params"=>"waterusetype:typeabbrev:typename:typename:0:","label"=>"Use Type: ","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info', 'mandatory'=>1, "maxlength"=>3),
              "wateruse"=>array("type"=>1,"params"=>"","label"=>"Average Water Use","visible"=>1, "readonly"=>0, "width"=>64, "tab"=>'general_info'),
              "email_contact"=>array("type"=>3,"params"=>"0|False,1|True:lid:lname::0","label"=>"Electronic Reporting Forms Only?","visible"=>1, "readonly"=>0, "width"=>12, "tab"=>'general_info'),
              "bad_email"=>array("type"=>3,"params"=>"0|False,1|True:lid:lname::0","label"=>"Email Address Marked as Bad?","visible"=>1, "readonly"=>0, "width"=>12, "tab"=>'general_info'),
              "reports_quarterly"=>array("type"=>3,"params"=>"0|False,1|True:rqid:rqname::0","label"=>"Reports Quarterly?","visible"=>1, "readonly"=>0, "width"=>12, "tab"=>'general_info'),
              "addyra"=>array("type"=>1,"params"=>"","label"=>"Year Added","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info'),
              "subyra"=>array("type"=>1,"params"=>"","label"=>"Year Removed","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info'),
              "other"=>array("type"=>1,"params"=>"5","label"=>"Comments","visible"=>1, "readonly"=>0, "width"=>64, "tab"=>'general_info', "maxlength"=>800),

              "name"=>array("type"=>1,"params"=>"","label"=>"Contact Name","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'contact_info', "maxlength"=>254),
              "title"=>array("type"=>1,"params"=>"","label"=>"Contact Title","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'contact_info', "maxlength"=>254),
              "email"=>array("type"=>1,"params"=>"","label"=>"Contact Email","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'contact_info', "maxlength"=>128),
              "mailadd1"=>array("type"=>1,"params"=>"","label"=>"Address Line 1","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'contact_info', "maxlength"=>254),
              "mailadd2"=>array("type"=>1,"params"=>"","label"=>"Address Line 2","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'contact_info', "maxlength"=>254),
              "mailcity"=>array("type"=>1,"params"=>"","label"=>"City","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'contact_info', "maxlength"=>254),
              "mailst"=>array("type"=>1,"params"=>"","label"=>"State","visible"=>1, "readonly"=>0, "width"=>20, "tab"=>'contact_info', "maxlength"=>254),
              "mailzip"=>array("type"=>1,"params"=>"","label"=>"Zip Code","visible"=>1, "readonly"=>0, "width"=>10, "tab"=>'contact_info', "maxlength"=>10),
              "phone"=>array("type"=>1,"params"=>"","label"=>"Phone","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'contact_info', "maxlength"=>254),
              "website"=>array("type"=>1,"params"=>"","label"=>"Web Site","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'contact_info', "maxlength"=>254),

              "name2"=>array("type"=>1,"params"=>"","label"=>"2ndary Contact Name","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'contact_info2', "maxlength"=>254),
              "title2"=>array("type"=>1,"params"=>"","label"=>"2ndary Contact Title","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'contact_info2', "maxlength"=>254),
              "email2"=>array("type"=>1,"params"=>"","label"=>"2ndary Contact Email","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'contact_info2', "maxlength"=>254),
              "mail2add1"=>array("type"=>1,"params"=>"","label"=>"2ndary Address Line 1","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'contact_info2', "maxlength"=>254),
              "mail2add2"=>array("type"=>1,"params"=>"","label"=>"2ndary Address Line 2","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'contact_info2', "maxlength"=>254),
              "mail2city"=>array("type"=>1,"params"=>"","label"=>"2ndary City","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'contact_info2', "maxlength"=>254),
              "mail2state"=>array("type"=>1,"params"=>"","label"=>"2ndary State","visible"=>1, "readonly"=>0, "width"=>20, "tab"=>'contact_info2', "maxlength"=>254),
              "mail2zip"=>array("type"=>1,"params"=>"","label"=>"2ndary Zip Code","visible"=>1, "readonly"=>0, "width"=>10, "tab"=>'contact_info2', "maxlength"=>10),
              "phone2"=>array("type"=>1,"params"=>"","label"=>"2ndary Phone","visible"=>1, "readonly"=>0, "width"=>18, "tab"=>'contact_info2', "maxlength"=>254),

              "hucfacstr"=>array("type"=>1, "params"=>"", "label"=>"HUC","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'geo_info', "maxlength"=>12),
              "lat_flt"=>array("type"=>1, "params"=>"", "label"=>"Y coordinate (lat)","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'geo_info', 'mandatory'=>1),
              "lon_flt"=>array("type"=>1, "params"=>"", "label"=>"X coordinate (lon)","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'geo_info', 'mandatory'=>1),
              "fipscof"=>array("type"=>3, "params"=>"us_localities:stcofips:name:name:0:", "label"=>"Locality","visible"=>1, "width"=>24, "tab"=>'geo_info', 'mandatory'=>1, "maxlength"=>5),
              "region"=>array("type"=>3, "params"=>"vwuds_deq_regions:reg_id:name:name:0:", "label"=>"Region","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'geo_info', 'mandatory'=>1),
              "the_geom"=>array("type"=>24, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24, "tab"=>'geo_info'),

              "sic1"=>array("type"=>1, "params"=>"", "label"=>"sic1","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info'),
              "sic2"=>array("type"=>1, "params"=>"", "label"=>"sic2","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>254),
              "sic3"=>array("type"=>1, "params"=>"", "label"=>"sic3","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>254),
              "sicind"=>array("type"=>1, "params"=>"", "label"=>"sicind","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>254),
              "vwppermit"=>array("type"=>3, "params"=>"0|False,1|True:vpid:vpname::0", "label"=>"VWP Permit?","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>1),
              "permitf"=>array("type"=>1, "params"=>"", "label"=>"permitf","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>9),
              "wsplan"=>array("type"=>1, "params"=>"", "label"=>"wsplan","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>254),
              "demctr"=>array("type"=>1, "params"=>"", "label"=>"demctr","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>4),
              "private"=>array("type"=>1, "params"=>"", "label"=>"private","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>254),
              "exempt"=>array("type"=>1, "params"=>"", "label"=>"exempt","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>254),
              "ustabe"=>array("type"=>1, "params"=>"", "label"=>"ustabe","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>254),
              "gwma"=>array("type"=>1, "params"=>"", "label"=>"gwma","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>254),
              "farmnum"=>array("type"=>1, "params"=>"", "label"=>"farmnum","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>254),
              "useridstr"=>array("type"=>1, "params"=>"", "label"=>"useridstr","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>4),
              "ownerid"=>array("type"=>3,"params"=>"users:userid:lastname,firstname:lastname,firstname:0:","label"=>"Region Planner","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info')
            )
        ),

      "vwuds_deq_regions"=>array(
          "table info"=>array("table_name"=>'vwuds_deq_regions', "tabledef"=>"vwuds_deq_regions", "pk"=>"recid", "sortcol"=>"name", "outputformat"=>"column", 'output type'=>'tabbed', 'form_title'=>'VA DEQ Regions',
             'tabs'=>array(
                'general_info'=> array('tab label'=>'General Info')
             ),
             'width'=>'600px', 'height'=>'400px', 'divname'=>'vwuds_deq_regions', 'maplabelcols'=>'name',
          ),
          "search info"=>array("tabledef"=>"vwuds_deq_regions", "pk"=>"recid", 'pk_search_var'=>'srch_recid', "sortcol"=>"recid", "outputformat"=>"column",
             'columns'=>array(
                "name"=>array('searchtype'=>3, 'search_var'=>'srch_name',"params"=>"vwuds_deq_regions:name:name:name:0:"),
             )
          ),
          "column info"=>array(
              "recid"=>array("type"=>1,"params"=>"","label"=>"Record ID","visible"=>1, "readonly"=>1, "width"=>32, "tab"=>'general_info'),
              "reg_id"=>array("type"=>1,"params"=>"","label"=>"Region ID","visible"=>1, "readonly"=>1, "width"=>32, "tab"=>'general_info'),
              "name"=>array("type"=>1,"params"=>"","label"=>"Region Name","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info'),
              "add1"=>array("type"=>1,"params"=>"","label"=>"Address Line 1","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info'),
              "add2"=>array("type"=>1,"params"=>"","label"=>"Address Line 2","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info'),
              "city"=>array("type"=>1,"params"=>"","label"=>"City","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info'),
              "state"=>array("type"=>1,"params"=>"","label"=>"State","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info'),
              "zip"=>array("type"=>1,"params"=>"","label"=>"ZIP Code","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info'),
              "phone"=>array("type"=>1,"params"=>"","label"=>"Phone Number","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info'),
              "ownerid"=>array("type"=>3,"params"=>"users:userid:lastname,firstname:lastname,firstname:0:","label"=>"Region Planner","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info')
            )
        ),

      "annual_data"=>array(
          "table info"=>array("table_name"=>'annual_data', "tabledef"=>"vwuds_annual_mp_data", "pk"=>"recid", "sortcol"=>"USERID", "outputformat"=>"column", 'output type'=>'tabbed', 'form_title'=>'Annual Water Use Data Table',
             'tabs'=>array(
                'general_info'=> array('tab label'=>'General Info'),
                'annual_info'=> array('tab label'=>'Annual Data'),
                'monthly_info'=> array('tab label'=>'Monthly Data'),
                'permit_info'=> array('tab label'=>'Permit Info'),
                'admin_info'=> array('tab label'=>'Technical Info'),
                'geo_info'=> array('tab label'=>'Geographic Info')
             ),
             'width'=>'600px', 'height'=>'400px', 'divname'=>'annual_data', 'geom_col'=>'the_geom', 'extent_where'=>'lon_flt <> 0.0', 'srid'=>4326, 'maplabelcols'=>'MPID',
          ),
          "search info"=>array("tabledef"=>"vwuds_annual_mp_data", "pk"=>"recid", 'pk_search_var'=>'srch_recid', "sortcol"=>"recid", "outputformat"=>"column",
             'columns'=>array(
                "ownerid"=>array('searchtype'=>3,"params"=>"users:userid:lastname,firstname:lastname,firstname:0:userid in (select userid from mapusergroups where groupid in (3,4))", 'label'=>'Planner'),
                "REGION"=>array("searchtype"=>3, "params"=>"vwuds_deq_regions:reg_id:name:name:0:"),
                'USERID'=> array('searchtype'=>1, 'search_var'=>'srch_userid', 'label'=>'User ID'),
                'MPID'=> array('searchtype'=>1, 'search_var'=>'srch_,pid', 'label'=>'MP ID'),
                'received'=> array('searchtype'=>3, "params"=>"1|True,0|False:rcv_id:rcvname:rcvname:0:", 'label'=>'Submittal Received'),
                'processed'=> array('searchtype'=>3, "params"=>"1|True,0|False:imp_id:impname:impname:0:", 'label'=>'Submittal Processed'),
                'SUBYR_MP'=> array('searchtype'=>1, 'label'=>"Year Removed"),
                'YEAR'=> array('searchtype'=>4, 'search_var'=>'srch_year'),
                'ANNUAL'=> array('searchtype'=>4, 'search_var'=>'srch_annualtotal'),
                'monthlymax'=> array('searchtype'=>4, 'search_var'=>'srch_monthlymax', 'search_cols'=>'JANUARY,FEBRUARY,MARCH,APRIL,MAY,JUNE,JULY,AUGUST,SEPTEMBER,OCTOBER,NOVEMBER,DECEMBER', 'label'=>'Single Month Total'),
                "CAT_MP"=>array('searchtype'=>3, 'search_var'=>'srch_cat',"params"=>"waterusetype:typeabbrev:typename:typename:0:"),
                'METHOD'=> array('searchtype'=>3, "params"=>"M|Metered,E|Estimated:meth_id:methname:methname:0:", 'search_var'=>'srch_method', 'label'=>'Measurement Method'),
                'GWPERMIT'=> array('searchtype'=>1, 'search_var'=>'srch_gwpermit', 'label'=>'GW Permit #'),
                'VWP_PERMIT'=> array('searchtype'=>1, 'search_var'=>'srch_vwppermit', 'label'=>'VWP Permit #'),
                "stcofips"=>array('searchtype'=>3,"params"=>"us_localities:stcofips:name:name:0:", 'label'=>'Locality'),
                "ACTION"=>array("searchtype"=>3,"params"=>"vwuds_action:abbrev:action_text:action_text:0:"),
                "TYPE"=>array("searchtype"=>3,"params"=>"watersourcetype:wsabbrev:wsname:wsname:0:", 'label'=>'Source Type'),
                "SUBTYPE"=>array('searchtype'=>3,"params"=>"vwuds_source_subtype:abbrev:typename:typename:0:", 'label'=>'Source Sub-Type'),
                "HUC_MP"=>array("searchtype"=>3, "params"=>"huc_va:huc:huc:huc:0:", 'label'=>'HUC-8'),
                "R_BASIN"=>array("searchtype"=>3,"params"=>"vwuds_basins:abbrev:basin_name:basin_name:0:")
             )
          ),
          "column info"=>array(
              "recid"=>array("type"=>1,"params"=>"","label"=>"Record ID","visible"=>1, "readonly"=>1, "width"=>8, "tab"=>'general_info'),
              "MPID"=>array("type"=>1,"params"=>"","label"=>"Measuring Point ID","visible"=>1, "readonly"=>1, "width"=>32, "tab"=>'general_info'),
              "USERID"=>array("type"=>3,"params"=>"facilities:userid:userid,ownname,facility:ownname,facility:0:","label"=>"User ID, Owner, Facility","visible"=>1, "readonly"=>1, "width"=>32, "tab"=>'general_info'),
              "ownname"=>array("type"=>1,"params"=>"","label"=>"Owner Name","visible"=>1, "readonly"=>1, "width"=>64, "tab"=>'general_info'),
              "facility"=>array("type"=>1,"params"=>"","label"=>"Facility","visible"=>1, "readonly"=>1, "width"=>64, "tab"=>'general_info'),
              "system"=>array("type"=>1,"params"=>"","label"=>"System: ","visible"=>1, "readonly"=>1, "width"=>16, "tab"=>'general_info', 'mandatory'=>0, 'unique'=>0),
              "ACTION"=>array("type"=>3,"params"=>"vwuds_action:abbrev:action_text:action_text:0:","label"=>"Action Type","visible"=>1, "readonly"=>1, "width"=>32, "tab"=>'general_info'),
              "CAT_MP"=>array("type"=>3,"params"=>"waterusetype:typeabbrev:typename:typename:0:","label"=>"Use Type","visible"=>1, "readonly"=>1, "width"=>32, "tab"=>'general_info'),
              "OTHERC"=>array("type"=>1, "params"=>"", "label"=>"Comments","visible"=>1, "readonly"=>'0', "width"=>64, "maxlength"=>255, "tab"=>'general_info'),
              "SOURCE"=>array("type"=>1,"params"=>"","label"=>"Water Source: ","visible"=>1, "readonly"=>1, "width"=>32, "tab"=>'general_info'),
              "TYPE"=>array("type"=>3,"params"=>"watersourcetype:wsabbrev:wsname:wsname:0:","label"=>"Source Type: ","visible"=>1, "readonly"=>1, "width"=>24, "tab"=>'general_info', 'mandatory'=>1, 'unique'=>0),
              "SUBTYPE"=>array("type"=>3,"params"=>"vwuds_source_subtype:abbrev:typename:typename:0:","label"=>"Sub-Type: ","visible"=>1, "readonly"=>1, "width"=>16, "tab"=>'general_info', 'mandatory'=>0, 'unique'=>0),
              "SIC_MP"=>array("type"=>3,"params"=>"vwuds_sic_codes:sic:sicmerge:sicmerge:0:","label"=>"SIC: ","visible"=>1, "readonly"=>1, "width"=>32, "tab"=>'general_info'),
              "abandoned"=>array("type"=>3, "params"=>"0|False,1|True:abid:abname::0:", "label"=>"Withdrawal has been abandoned?","visible"=>1, "readonly"=>'1', "width"=>64, "tab"=>'general_info'),
              "YEAR"=>array("type"=>1, "params"=>"", "label"=>"Year","visible"=>1, "readonly"=>'0', "width"=>8, "tab"=>'general_info'),
              'received'=> array('type'=>3, "params"=>"1|True,2|False:rcv_id:rcvname:rcvname:0:", "readonly"=>'0', 'label'=>'Submittal Received', "tab"=>'general_info', 'mandatory'=>0, 'unique'=>0,"visible"=>1),
              'processed'=> array('type'=>3, "params"=>"1|True,2|False:imp_id:impname:impname:0:", "readonly"=>'0', 'label'=>'Submittal Processed', "tab"=>'general_info', 'mandatory'=>0, 'unique'=>0,"visible"=>1),
              "ownerid"=>array("type"=>3,"params"=>"users:userid:lastname,firstname:lastname,firstname:0:","label"=>"Region Planner","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info'),
              "last_modified"=>array("type"=>1,"params"=>"","label"=>"Last Modified: ","visible"=>1, "readonly"=>1, "width"=>32, "tab"=>'general_info'),

              "ANNUAL"=>array("type"=>9, "params"=>"4", "label"=>"Annual (MG)","visible"=>1, "readonly"=>'1', "width"=>8, "tab"=>'annual_info'),
              "ANNUAL/365"=>array("type"=>9, "params"=>"4", "label"=>"Avg. Rate (MGD)","visible"=>1, "readonly"=>'1', "width"=>6, "tab"=>'annual_info'),
              "MAXDAY"=>array("type"=>9, "params"=>"4", "label"=>"Max Daily Total (MG)","visible"=>1, "readonly"=>'0', "width"=>6, "tab"=>'annual_info'),
              "MAXMONTH"=>array("type"=>3, "params"=>"1|January,2|February,3|March,4|April,5|May,6|June,7|July,8|August,9|September,10|October,11|November,12|December:mmid:mmname::0:", "label"=>"Month of Max (mm)","visible"=>1, "readonly"=>'0', "width"=>16, "tab"=>'annual_info'),
              "ACRES"=>array("type"=>9, "params"=>"4", "label"=>"Area (acres)","visible"=>1, "readonly"=>'0', "width"=>6, "tab"=>'annual_info'),
              "CROP1"=>array("type"=>1, "params"=>"", "label"=>"Crop 1","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'annual_info', "maxlength"=>50),
              "CROP2"=>array("type"=>1, "params"=>"", "label"=>"Crop 2","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'annual_info', "maxlength"=>50),

              "JANUARY"=>array("type"=>9, "params"=>"2", "label"=>"Jan (MG)","visible"=>1, "readonly"=>'0', "width"=>6, "tab"=>'monthly_info'),
              "FEBRUARY"=>array("type"=>9, "params"=>"4", "label"=>"Feb (MG)","visible"=>1, "readonly"=>'0', "width"=>6, "tab"=>'monthly_info'),
              "MARCH"=>array("type"=>9, "params"=>"4", "label"=>"Mar (MG)","visible"=>1, "readonly"=>'0', "width"=>6, "tab"=>'monthly_info'),
              "APRIL"=>array("type"=>9, "params"=>"4", "label"=>"Apr (MG)","visible"=>1, "readonly"=>'0', "width"=>6, "tab"=>'monthly_info'),
              "MAY"=>array("type"=>9, "params"=>"4", "label"=>"May (MG)","visible"=>1, "readonly"=>'0', "width"=>6, "tab"=>'monthly_info'),
              "JUNE"=>array("type"=>9, "params"=>"4", "label"=>"Jun (MG)","visible"=>1, "readonly"=>'0', "width"=>6, "tab"=>'monthly_info'),
              "JULY"=>array("type"=>9, "params"=>"4", "label"=>"Jul (MG)","visible"=>1, "readonly"=>'0', "width"=>6, "tab"=>'monthly_info'),
              "AUGUST"=>array("type"=>9, "params"=>"4", "label"=>"Aug (MG)","visible"=>1, "readonly"=>'0', "width"=>6, "tab"=>'monthly_info'),
              "SEPTEMBER"=>array("type"=>9, "params"=>"4", "label"=>"Sep (MG)","visible"=>1, "readonly"=>'0', "width"=>6, "tab"=>'monthly_info'),
              "OCTOBER"=>array("type"=>9, "params"=>"4", "label"=>"Oct (MG)","visible"=>1, "readonly"=>'0', "width"=>6, "tab"=>'monthly_info'),
              "NOVEMBER"=>array("type"=>9, "params"=>"4", "label"=>"Nov (MG)","visible"=>1, "readonly"=>'0', "width"=>6, "tab"=>'monthly_info'),
              "DECEMBER"=>array("type"=>9, "params"=>"4", "label"=>"Dec (MG)","visible"=>1, "readonly"=>'0', "width"=>6, "tab"=>'monthly_info'),

              "GWPERMIT"=>array("type"=>1, "params"=>"", "label"=>"GW Permit #","visible"=>1, "readonly"=>'1', "width"=>24, "tab"=>'permit_info'),
              "VPDES"=>array("type"=>1, "params"=>"", "label"=>"VPDES Permit #","visible"=>1, "readonly"=>'1', "width"=>24, "tab"=>'permit_info'),
              "VDH_NUM"=>array("type"=>1, "params"=>"", "label"=>"VDH Permit #","visible"=>1, "readonly"=>'1', "width"=>24, "tab"=>'permit_info'),
              "VWP_PERMIT"=>array("type"=>1, "params"=>"", "label"=>"VWP Permit #","visible"=>1, "readonly"=>'1', "width"=>24, "tab"=>'permit_info'),
              "WELLNO"=>array("type"=>1, "params"=>"", "label"=>"Well #: ","visible"=>1, "readonly"=>'1', "width"=>24, "tab"=>'permit_info'),
              "DEQ_WELL"=>array("type"=>1, "params"=>"", "label"=>"DEQ Well #","visible"=>1, "readonly"=>'1', "width"=>24, "tab"=>'permit_info'),

              #"METHOD"=>array("type"=>3, "params"=>"M|Metered,E|Estimated:meth_id:methname:methname:0:", "label"=>"Method","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'admin_info'),
              "METHOD"=>array("type"=>23, "params"=>"M|Metered,E|Estimated", "label"=>"Method","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'admin_info'),
              "method_desc"=>array("type"=>1, "params"=>"", "label"=>"If Method Estimated, describe","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'admin_info', "maxlength"=>254),
              "metertype"=>array("type"=>23, "params"=>"S|Source,C|Customer", "label"=>"Metering type","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'admin_info'),
              "ENTITY"=>array("type"=>1, "params"=>"", "label"=>"Entity","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'admin_info', "maxlength"=>5),
              "ACCURACY"=>array("type"=>1, "params"=>"", "label"=>"Accuracy","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'admin_info', "maxlength"=>1),
              "RESTRICT"=>array("type"=>1, "params"=>"4", "label"=>"Restrict","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'admin_info', "maxlength"=>1),
              "REVISED_ON"=>array("type"=>1, "params"=>"", "label"=>"Revised On","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'admin_info'),
              "REVISED_BY"=>array("type"=>1, "params"=>"", "label"=>"Revised By","visible"=>1, "readonly"=>'0', "width"=>3, "maxlength"=>3, "tab"=>'admin_info'),
              "SALINITY"=>array("type"=>1, "params"=>"4", "label"=>"Salinity","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'admin_info', "maxlength"=>1),

              "REGION"=>array("type"=>3, "params"=>"vwuds_deq_regions:reg_id:name:name:0:", "label"=>"DEQ Region","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'geo_info'),
              "stcofips"=>array("type"=>3, "params"=>"us_localities:stcofips:name:name:0:", "label"=>"Locality","visible"=>1, "readonly"=>'1', "width"=>24, "tab"=>'geo_info', 'mandatory'=>0),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'1', "width"=>24, "tab"=>'geo_info'),
              "R_BASIN"=>array("type"=>3,"params"=>"vwuds_basins:abbrev:basin_name:basin_name:0:","label"=>"River Basin: ","visible"=>1, "readonly"=>1, "width"=>32, "tab"=>'geo_info'),
              "lon_flt"=>array("type"=>1, "params"=>"", "label"=>"X coordinate (lon)","visible"=>1, "readonly"=>'1', "width"=>24, "tab"=>'geo_info', 'mandatory'=>0),
              "lat_flt"=>array("type"=>1, "params"=>"", "label"=>"Y coordinate (lat)","visible"=>1, "readonly"=>'1', "width"=>24, "tab"=>'geo_info', 'mandatory'=>0),
            )
        ),

      "vwuds_measuring_point"=>array(
          "table info"=>array("table_name"=>'vwuds_measuring_point', "tabledef"=>"vwuds_mp_detail", "pk"=>"record_id", "pk_seq"=>'vwuds_measuring_point_record_id_seq', "sortcol"=>"record_id", "outputformat"=>"column", 'output type'=>'tabbed', 'form_title'=>'Measuring Point Table', 'maplabelcols'=>'MPID',
             'tabs'=>array(
                'general_info'=> array('tab label'=>'General Info'),
                'permit_info'=> array('tab label'=>'Permit Info'),
                'limit_info'=> array('tab label'=>'Capacities/Limits'),
                'geo_info'=> array('tab label'=>'Geographic Info'),
                'admin_info'=> array('tab label'=>'Administrative Info')
             ),
             'width'=>'600px', 'height'=>'400px', 'divname'=>'mp', 'geom_col'=>'the_geom', 'srid'=>4326, 'extent_where'=>'lon_flt <> 0.0'
          ),
          "search info"=>array("tabledef"=>"vwuds_mp_detail", "pk"=>"record_id", 'pk_search_var'=>'srch_elementid', "sortcol"=>"record_id", "outputformat"=>"column",
             'columns'=>array(
                "ownerid"=>array('searchtype'=>3,"params"=>"users:userid:lastname,firstname:lastname,firstname:0:userid in (select userid from mapusergroups where groupid in (3,4))", 'label'=>'Planner'),
                'facuserid'=> array('searchtype'=>1, 'search_var'=>'srch_facuserid', 'label'=>'Facility User ID'),
                'USERID'=> array('searchtype'=>4, 'search_var'=>'srch_userid', 'label'=>'User ID'),
                "REGION"=>array("searchtype"=>3, "params"=>"vwuds_deq_regions:reg_id:name:name:0:"),
                'MPID'=> array('searchtype'=>1, 'search_var'=>'srch_mpid'),
                'GWPERMIT'=> array('searchtype'=>1, 'search_var'=>'srch_gwpermit'),
                'VWP_PERMIT'=> array('searchtype'=>1, 'search_var'=>'srch_vwppermit'),
                "VDH_NUM"=>array('searchtype'=>1),
                "ADDYR_MP"=>array('searchtype'=>1),
                "SUBYR_MP"=>array('searchtype'=>1),
                "stcofips"=>array('searchtype'=>3,"params"=>"us_localities:stcofips:name:name:0:"),
                "ACTION"=>array("searchtype"=>3,"params"=>"vwuds_action:abbrev:action_text:action_text:0:"),
                "TYPE"=>array("searchtype"=>3,"params"=>"watersourcetype:wsabbrev:wsname:wsname:0:"),
                "SUBTYPE"=>array('searchtype'=>3,"params"=>"vwuds_source_subtype:abbrev:typename:typename:0:"),
                "CAT_MP"=>array("searchtype"=>3,"params"=>"waterusetype:typeabbrev:typename:typename:0:"),
                "R_BASIN"=>array("searchtype"=>3,"params"=>"vwuds_basins:abbrev:basin_name:basin_name:0:")
             )
          ),
          "column info"=>array(
              "record_id"=>array("type"=>1,"params"=>"","label"=>"Record ID: ","visible"=>1, "readonly"=>1, "width"=>32, "tab"=>'general_info', 'mandatory'=>0, 'unique'=>0),
              "MPID"=>array("type"=>1,"params"=>"","label"=>"Measuring Point ID: ","visible"=>1, "readonly"=>1, "width"=>32, "tab"=>'general_info', 'mandatory'=>0, 'unique'=>1),
              "USERID"=>array("type"=>3,"params"=>"facilities:userid:userid,ownname,system,facility:ownname,facility:0:","label"=>"User ID, Owner, Facility: ","visible"=>1, "readonly"=>0, "width"=>16, "tab"=>'general_info', 'mandatory'=>1, 'unique'=>0),
              "ownname"=>array("type"=>1,"params"=>"","label"=>"Owner: ","visible"=>1, "readonly"=>1, "width"=>16, "tab"=>'general_info', 'mandatory'=>0, 'unique'=>0),
              "system"=>array("type"=>1,"params"=>"","label"=>"System: ","visible"=>1, "readonly"=>1, "width"=>16, "tab"=>'general_info', 'mandatory'=>0, 'unique'=>0),
              "facuserid"=>array("type"=>1,"params"=>"","label"=>"Facility USERID: ","visible"=>1, "readonly"=>1, "width"=>16, "tab"=>'general_info', 'mandatory'=>0, 'unique'=>0),
              "ACTION"=>array("type"=>3,"params"=>"vwuds_action:abbrev:action_text:action_text:0:","label"=>"Action Type: ","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info', 'mandatory'=>1, 'unique'=>0),
              "TYPE"=>array("type"=>3,"params"=>"watersourcetype:wsabbrev:wsname:wsname:0:","label"=>"Source Type: ","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info', 'mandatory'=>1, 'unique'=>0),
              "SUBTYPE"=>array("type"=>3,"params"=>"vwuds_source_subtype:abbrev:typename:typename:0:","label"=>"Sub-Type: ","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info', 'mandatory'=>0, 'unique'=>0),
              "CAT_MP"=>array("type"=>3,"params"=>"waterusetype:typeabbrev:typename:typename:0:","label"=>"Use Type: ","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info', 'mandatory'=>0, 'unique'=>0),
              "SIC_MP"=>array("type"=>3,"params"=>"vwuds_sic_codes:sic:sicmerge:sicmerge:0:","label"=>"SIC: ","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info'),
              "SOURCE"=>array("type"=>1,"params"=>"","label"=>"Water Source: ","visible"=>1, "readonly"=>0, "width"=>64, "tab"=>'general_info', "maxlength"=>128),
              "abandoned"=>array("type"=>3, "params"=>"0|False,1|True:abid:abname::0:", "label"=>"Withdrawal has been abandoned?","visible"=>1, "readonly"=>'0', "width"=>12, "tab"=>'general_info'),
              

              "GWPERMIT"=>array("type"=>1, "params"=>"", "label"=>"GW Permit #","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'permit_info'),
              "VPDES"=>array("type"=>1, "params"=>"", "label"=>"VPDES Permit #","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'permit_info'),
              "VDH_NUM"=>array("type"=>1, "params"=>"", "label"=>"VDH Permit #","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'permit_info'),
              "VWP_PERMIT"=>array("type"=>1, "params"=>"", "label"=>"VWP Permit #","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'permit_info'),
              "WELLNO"=>array("type"=>1, "params"=>"", "label"=>"Well #: ","visible"=>1, "readonly"=>'0', "width"=>8, 'maxlength'=>8, "tab"=>'permit_info'),
              "DEQ_WELL"=>array("type"=>1, "params"=>"", "label"=>"DEQ Well #","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'permit_info'),
              "max_annual_mg"=>array("type"=>9, "params"=>"", "label"=>"Maximum Annual Withdrawal (MG)","visible"=>1, "readonly"=>'0', "width"=>24, "default"=>'0.0', "tab"=>'permit_info'),

              "CAPACITY"=>array("type"=>1, "params"=>"", "label"=>"Capacity","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'limit_info'),
              "LIMITER"=>array("type"=>1, "params"=>"", "label"=>"Limiter","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'limit_info'),
              "GWLIMIT_DAY"=>array("type"=>1, "params"=>"", "label"=>"GW Daily Limit","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'limit_info'),
              "GWLIMIT_MO"=>array("type"=>1, "params"=>"", "label"=>"GW Monthly Limit","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'limit_info'),
              "GWLIMIT_YR"=>array("type"=>1, "params"=>"", "label"=>"GW Annual Limit","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'limit_info'),
              "CAPACITY"=>array("type"=>1, "params"=>"", "label"=>"Capacity","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'limit_info'),
              "permit_exemption"=>array("type"=>3, "params"=>"1|N/A,1|Valid Exemption - Grandfathered,2|Valid Exemption - Other,3|Add-info - Need Date,4|Add-Info - Other:peid:pename::0:", "label"=>"VWP Permit Exclusion Status","visible"=>1, "readonly"=>'0', "width"=>12, "tab"=>'permit_info'),
              "vwp_exclusion_rcvd"=>array("type"=>3, "params"=>"0|Not Received,1|Received,2|N/A:vebid:vebname::0:", "label"=>"VWP Permit exclusion forms sent/received?","visible"=>1, "readonly"=>'0', "width"=>12, "tab"=>'permit_info'),
              "vwp_excl_admin_def"=>array("type"=>3, "params"=>"0|False,1|True:vadid:vadbname::0:", "label"=>"VWP Exclusion forms Adminstratively Deficient?","visible"=>1, "readonly"=>'0', "width"=>12, "tab"=>'permit_info'),
              "vwp_excl_admin_comp"=>array("type"=>3, "params"=>"0|False,1|True:vacbid:vacbname::0:", "label"=>"VWP Exclusion forms Adminstratively Complete?","visible"=>1, "readonly"=>'0', "width"=>12, "tab"=>'permit_info'),

              "stcofips"=>array("type"=>3, "params"=>"us_localities:stcofips:name:name:0:", "label"=>"Locality","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'geo_info', 'mandatory'=>1),
              "REGION"=>array("type"=>3, "params"=>"vwuds_deq_regions:reg_id:name:name:0:", "label"=>"DEQ Region","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'geo_info', 'mandatory'=>1),
              "R_BASIN"=>array("type"=>3,"params"=>"vwuds_basins:abbrev:basin_name:basin_name:0:","label"=>"River Basin: ","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'geo_info', 'mandatory'=>1),
              "QUAD"=>array("type"=>1, "params"=>"", "label"=>"USGS Quad","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'geo_info'),
              "HUC_MP"=>array("type"=>1, "params"=>"", "label"=>"HUC","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'geo_info'),
              "STCODE"=>array("type"=>1, "params"=>"", "label"=>"State","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'geo_info'),
              "lon_flt"=>array("type"=>25, "params"=>"", "label"=>"X coordinate (lon)","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'geo_info', 'mandatory'=>1),
              "lat_flt"=>array("type"=>25, "params"=>"", "label"=>"Y coordinate (lat)","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'geo_info', 'mandatory'=>1),
              "point_estimated"=>array("type"=>3, "params"=>"0|False,1|True:peid:pename::0:", "label"=>"Is Lat/Lon Estimated?","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'geo_info'),
              "AQUIFER"=>array("type"=>1, "params"=>"", "label"=>"Aquifer","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'geo_info'),
              "ACRES"=>array("type"=>9, "params"=>"4", "label"=>"Area (acres)","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'geo_info', "default"=>'0.0'),
              "WBID"=>array("type"=>1, "params"=>"", "label"=>"NHD Water Body ID","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'geo_info'),

              "ADDYR_MP"=>array("type"=>1, "params"=>"", "label"=>"Year Added","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'admin_info'),
              "SUBYR_MP"=>array("type"=>1, "params"=>"", "label"=>"Year Removed","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'admin_info'),
              "OTHER_MP"=>array("type"=>1, "params"=>"", "label"=>"Comments","visible"=>1, "readonly"=>'0', "width"=>64, "tab"=>'admin_info'),
              "REVISED_ON"=>array("type"=>1, "params"=>"", "label"=>"Revised On","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'admin_info'),
              "REVISED_BY"=>array("type"=>1, "params"=>"", "label"=>"Revised By","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'admin_info'),
              "the_geom"=>array("type"=>24, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24, "tab"=>'general_info'),
                "ownerid"=>array('type'=>3,"params"=>"users:userid:lastname,firstname:lastname,firstname:0:userid in (select userid from mapusergroups where groupid in (3,4))","label"=>"Responsible Planner","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'admin_info', 'mandatory'=>1)
            )
        ),

      "plannercontact"=>array(
          "table info"=>array("table_name"=>'users', "tabledef"=>"users", "pk"=>"recid", "sortcol"=>"name", "outputformat"=>"column", 'output type'=>'tabbed', 'form_title'=>'VA DEQ Planners',
             'tabs'=>array(
                'general_info'=> array('tab label'=>'General Info')
             ),
             'width'=>'600px', 'height'=>'400px', 'divname'=>'vwuds_deq_regions', 'maplabelcols'=>'name',
          ),
          "search info"=>array("tabledef"=>"users", "pk"=>"userid", 'pk_search_var'=>'userid', "sortcol"=>"userid", "outputformat"=>"column",
             'columns'=>array(
             )
          ),
          "column info"=>array(
              "userid"=>array("type"=>1,"params"=>"","label"=>"User ID","visible"=>1, "readonly"=>1, "width"=>32, "tab"=>'general_info'),
              "sender"=>array("type"=>1,"params"=>"","label"=>"Planner Name","visible"=>1, "readonly"=>1, "width"=>32, "tab"=>'general_info'),
              "replyto"=>array("type"=>1,"params"=>"","label"=>"Email","visible"=>1, "readonly"=>1, "width"=>32, "tab"=>'general_info'),
              "contactphone"=>array("type"=>1,"params"=>"","label"=>"Phone Number","visible"=>1, "readonly"=>1, "width"=>32, "tab"=>'general_info'),
              "address1"=>array("type"=>1,"params"=>"","label"=>"Address Line 1","visible"=>1, "readonly"=>1, "width"=>32, "tab"=>'general_info'),
              "address2"=>array("type"=>1,"params"=>"","label"=>"Address Line 2","visible"=>1, "readonly"=>1, "width"=>32, "tab"=>'general_info'),
              "city"=>array("type"=>1,"params"=>"","label"=>"City","visible"=>1, "readonly"=>1, "width"=>32, "tab"=>'general_info'),
              "state"=>array("type"=>1,"params"=>"","label"=>"State","visible"=>1, "readonly"=>1, "width"=>32, "tab"=>'general_info'),
              "zip"=>array("type"=>1,"params"=>"","label"=>"Zip Code","visible"=>1, "readonly"=>1, "width"=>32, "tab"=>'general_info')
            )
        ),

      "users"=>array(
          "table info"=>array("table_name"=>'users', "tabledef"=>"users", "pk"=>"userid", "sortcol"=>"name", "outputformat"=>"column", 'output type'=>'tabbed', 'form_title'=>'VA DEQ Planners',
             'tabs'=>array(
                'general_info'=> array('tab label'=>'General Info')
             ),
             'width'=>'600px', 'height'=>'400px', 'divname'=>'vwuds_deq_regions', 'maplabelcols'=>'name',
          ),
          "search info"=>array("tabledef"=>"users", "pk"=>"userid", 'pk_search_var'=>'userid', "sortcol"=>"userid", "outputformat"=>"column",
             'columns'=>array(
                'firstname'=> array('searchtype'=>1, 'search_var'=>'srch_fname'),
                'lastname'=> array('searchtype'=>1, 'search_var'=>'srch_lname'),
             )
          ),
          "column info"=>array(
              "userid"=>array("type"=>1,"params"=>"","label"=>"User ID","visible"=>1, "readonly"=>1, "width"=>32, "tab"=>'general_info'),
              "firstname"=>array("type"=>1,"params"=>"","label"=>"First Name","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info'),
              "lastname"=>array("type"=>1,"params"=>"","label"=>"Last Name","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info'),
              "address1"=>array("type"=>1,"params"=>"","label"=>"Address Line 1","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info'),
              "address2"=>array("type"=>1,"params"=>"","label"=>"Address Line 2","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info'),
              "city"=>array("type"=>1,"params"=>"","label"=>"City","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info'),
              "state"=>array("type"=>1,"params"=>"","label"=>"State","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info'),
              "zip"=>array("type"=>1,"params"=>"","label"=>"Zip Code","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info'),
              "email"=>array("type"=>1,"params"=>"","label"=>"Email","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info'),
              "phone"=>array("type"=>1,"params"=>"","label"=>"Phone Number","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info')
            )
        ),
        
      # model data tables


      "scen_model_element"=>array(
          "table info"=>array("table_name"=>'scen_model_element', "tabledef"=>"scen_model_element", "pk"=>"elementid", "sortcol"=>"elemname", "outputformat"=>"column", 'output type'=>'tabbed', 'form_title'=>'Model Elements',
             'tabs'=>array(
                'general_info'=> array('tab label'=>'General Info')
             ),
             'width'=>'600px', 'height'=>'400px', 'divname'=>'vwuds_deq_regions', 'maplabelcols'=>'name',
          ),
          "search info"=>array("tabledef"=>"scen_model_element", "pk"=>"elementid", 'pk_search_var'=>'elementid', "sortcol"=>"elemname", "outputformat"=>"column",
             'columns'=>array(
                'scenarioid'=> array('searchtype'=>3, 'search_var'=>'exsrch_scenarioid'),
                'elementid'=> array('searchtype'=>1, 'search_var'=>'exsrch_elementid'),
                'elemname'=> array('searchtype'=>1, 'search_var'=>'srch_elemname'),
                'objectclass'=> array('searchtype'=>1, 'search_var'=>'objectclass'),
                'custom1'=> array('searchtype'=>1, 'search_var'=>'srch_custom1'),
                'custom2'=> array('searchtype'=>1, 'search_var'=>'srch_custom2'),
             )
          ),
          "column info"=>array(
              "scenarioid"=>array("type"=>1,"params"=>"","label"=>"Scenario","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info'),
              "elementid"=>array("type"=>1,"params"=>"","label"=>"Element ID","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info'),
              "elemname"=>array("type"=>1,"params"=>"","label"=>"Element Name","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info'),
              "objectclass"=>array("type"=>1,"params"=>"","label"=>"Object Class","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info'),
              "custom1"=>array("type"=>1,"params"=>"","label"=>"Custom Attribute #1","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info'),
              "custom2"=>array("type"=>1,"params"=>"","label"=>"Custom Attribute #2","visible"=>1, "readonly"=>0, "width"=>32, "tab"=>'general_info')
            )
        ),

      # modeling components

      "USGSGageObject"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Object Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "staid"=>array("type"=>1,"params"=>"","label"=>"Gage ID","visible"=>1, "readonly"=>0),
              #"laststaid"=>array("type"=>1,"params"=>"","label"=>"Last USGS Gage ID","visible"=>0, "readonly"=>0, "width"=>12),
              #"staid"=>array("type"=>3,"params"=>"proj_points:pointname:pointname:pointname:0:projectid=$projectid ","label"=>"USGS Gage ID","visible"=>1, "readonly"=>0, "width"=>12),
              "sitetype"=>array("type"=>3,"params"=>"1|Stream,2|Groundwater,3|Reservoir:fmid:fmname::0:","label"=>"Site Type","visible"=>1, "readonly"=>0, "width"=>12),
              "rectype"=>array("type"=>3,"params"=>"0|Realtime,1|Daily:fmid:fmname::0:","label"=>"Record Type","visible"=>1, "readonly"=>0, "width"=>12),
              "intmethod"=>array("type"=>3,"params"=>"1|Previous Value,2|Next Value,3|Mean,4|Mininum,5|Maximum,6|Sum:imid:imname::0:","label"=>"Gage Type","visible"=>1, "readonly"=>0, "width"=>12),
              "startdate"=>array("type"=>1, "params"=>"","label"=>"Start Date","visible"=>1, "readonly"=>'0', "width"=>24),
              "enddate"=>array("type"=>1, "params"=>"", "label"=>"End Date","visible"=>1, "readonly"=>'0', "width"=>24),
              "area"=>array("type"=>1, "params"=>"","label"=>"Drainage Area (sq. mi.)","visible"=>1, "readonly"=>'0', "width"=>24),
              "flow_begin"=>array("type"=>1, "params"=>"","label"=>"Flow Record Start Date","visible"=>1, "readonly"=>'1', "width"=>24),
              "flow_end"=>array("type"=>1, "params"=>"", "label"=>"Flow Record End Date","visible"=>1, "readonly"=>'1', "width"=>24),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "geomx"=>array("type"=>1, "params"=>"", "label"=>"X coordinate (lon)","visible"=>1, "readonly"=>'0', "width"=>24),
              "geomy"=>array("type"=>1, "params"=>"", "label"=>"Y coordinate (lat)","visible"=>1, "readonly"=>'0', "width"=>24),
              "max_memory_values"=>array("type"=>1, "params"=>"", "label"=>"Max. Values to Store in Memory (-1 is unlimited)","visible"=>1, "readonly"=>'0', "width"=>24),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "USGSGageSubComp"=>array(
         "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
         "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Element Name","visible"=>1, "readonly"=>0, "width"=>32),
              "staid"=>array("type"=>1,"params"=>"","label"=>"Gage ID","visible"=>1, "readonly"=>0),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6)
          )
       ),
      "USGSSyntheticRecord"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Element Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "equationtype"=>array("type"=>3,"params"=>"0|y = bx^m,1|log[y] = m(log[x]) + b:clid:clname::0:","label"=>"Equation Form ","visible"=>1, "readonly"=>0, "width"=>6),
              "m"=>array("type"=>1, "params"=>"", "label"=>"Equation m","visible"=>1, "readonly"=>'0', "width"=>24),
              "b"=>array("type"=>1, "params"=>"", "label"=>"Equation b","visible"=>1, "readonly"=>'0', "width"=>24),
              "mup"=>array("type"=>1, "params"=>"", "label"=>"[Upper m]","visible"=>1, "readonly"=>'0', "width"=>24),
              "bup"=>array("type"=>1, "params"=>"", "label"=>"[Upper b]","visible"=>1, "readonly"=>'0', "width"=>24),
              "mlow"=>array("type"=>1, "params"=>"", "label"=>"[Lower m]","visible"=>1, "readonly"=>'0', "width"=>24),
              "blow"=>array("type"=>1, "params"=>"", "label"=>"[Lower b]","visible"=>1, "readonly"=>'0', "width"=>24),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>24),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),

      "droughtMonitor"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Object Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
             # "staid"=>array("type"=>1,"params"=>"","label"=>"Gage ID","visible"=>1, "readonly"=>0),
              #"laststaid"=>array("type"=>1,"params"=>"","label"=>"Last USGS Gage ID","visible"=>0, "readonly"=>0, "width"=>12),
              "flowgage"=>array("type"=>3,"params"=>"proj_points:pointname:pointname:pointname:0:","label"=>"USGS Flow Gage ID","visible"=>1, "readonly"=>0, "width"=>12),
              "gw_gage"=>array("type"=>3,"params"=>"proj_points:pointname:pointname:pointname:0:","label"=>"USGS Groundwater Gage ID","visible"=>1, "readonly"=>0, "width"=>12),
              "palmer_region"=>array("type"=>3,"params"=>"(select climdivs_,name from noaa_climate_divisions where st = 'VA' group by climdivs_,name) as foo:climdivs_:name:name:0:","label"=>"NOAA Climate Divisions","visible"=>1, "readonly"=>0, "width"=>12,"label"=>"NOAA Climate Divisions","visible"=>1, "readonly"=>0, "width"=>12),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "geomx"=>array("type"=>1, "params"=>"", "label"=>"X coordinate (lon)","visible"=>1, "readonly"=>'0', "width"=>24),
              "geomy"=>array("type"=>1, "params"=>"", "label"=>"Y coordinate (lat)","visible"=>1, "readonly"=>'0', "width"=>24),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "NOAADataObject"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Object Name","visible"=>1, "readonly"=>0, "width"=>64),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "datatype"=>array("type"=>3,"params"=>"0|Area Forecast Matrix,1|Stream Flow Forecast:did:dname::0:","label"=>"Data Product Type ","visible"=>1, "readonly"=>0, "width"=>6),
              "stationid"=>array("type"=>1,"params"=>"","label"=>"NOAA Station ID","visible"=>1, "readonly"=>0, "width"=>12),
              "dataURL"=>array("type"=>1,"params"=>"","label"=>"URL For Resource","visible"=>1, "readonly"=>0, "width"=>64),
              "timezone"=>array("type"=>3,"params"=>"AST|Atlantic Standard,EST|Eastern Standard,CST|Central Standard,MST|Mountain Standard,PST|Pacific Standard,UTC|UTC:tzid:tzname::0:","label"=>"Required Time Zone ","visible"=>1, "readonly"=>0, "width"=>6),
              "logfile"=>array("type"=>1,"params"=>"","label"=>"Cache File Name","visible"=>1, "readonly"=>0, "width"=>64),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "geomx"=>array("type"=>1, "params"=>"", "label"=>"X coordinate (lon)","visible"=>1, "readonly"=>'0', "width"=>24),
              "geomy"=>array("type"=>1, "params"=>"", "label"=>"Y coordinate (lat)","visible"=>1, "readonly"=>'0', "width"=>24),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "flowTransformer"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Object Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "staid"=>array("type"=>14,"params"=>"staid::proj_points:::pointname:pointname:0:5:","label"=>"USGS Gage ID","visible"=>1, "readonly"=>0, "width"=>12),
              "inputname"=>array("type"=>1,"params"=>"","label"=>"Name of Input to transform (e.g. Qin)","visible"=>1, "readonly"=>0, "width"=>12),
              "method"=>array("type"=>1, "params"=>"","label"=>"Transformation Method (0 - area-weighted, 1 - mean value)","visible"=>1, "readonly"=>'0', "width"=>6),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "geomx"=>array("type"=>1, "params"=>"", "label"=>"X coordinate (lon)","visible"=>1, "readonly"=>'0', "width"=>24),
              "geomy"=>array("type"=>1, "params"=>"", "label"=>"Y coordinate (lat)","visible"=>1, "readonly"=>'0', "width"=>24),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "channelObject"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Stream Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "base"=>array("type"=>1,"params"=>"","label"=>"Channel Base Width(ft)","visible"=>1, "readonly"=>0, "width"=>12),
              "length"=>array("type"=>1, "params"=>"","label"=>"Channel Mainstem Length","visible"=>1, "readonly"=>'0', "width"=>6),
              "slope"=>array("type"=>1, "params"=>"","label"=>"Channel Slope (ft/ft)","visible"=>1, "readonly"=>'0', "width"=>6),
              "Z"=>array("type"=>1, "params"=>"", "label"=>"Side-Slope Ratio","visible"=>1, "readonly"=>'0', "width"=>24),
              "n"=>array("type"=>1, "params"=>"", "label"=>"Mannings N - Channel Roughness","visible"=>1, "readonly"=>'0', "width"=>24),
              "substrateclass"=>array("type"=>1, "params"=>"", "label"=>"Substrate Class (A-D)","visible"=>1, "readonly"=>'0', "width"=>24),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "cascadedebug"=>array("type"=>3,"params"=>"0|False,1|True:fmid:fmname::0:","label"=>"Force debug mode to child objects? ","visible"=>1, "readonly"=>0, "width"=>6),
              "geomx"=>array("type"=>1, "params"=>"", "label"=>"X coordinate (lon)","visible"=>1, "readonly"=>'0', "width"=>24),
              "geomy"=>array("type"=>1, "params"=>"", "label"=>"Y coordinate (lat)","visible"=>1, "readonly"=>'0', "width"=>24),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "reverseFlowObject"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Stream Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "base"=>array("type"=>1,"params"=>"","label"=>"Channel Base Width(ft)","visible"=>1, "readonly"=>0, "width"=>12),
              "length"=>array("type"=>1, "params"=>"","label"=>"Channel Mainstem Length","visible"=>1, "readonly"=>'0', "width"=>6),
              "slope"=>array("type"=>1, "params"=>"","label"=>"Channel Slope (ft/ft)","visible"=>1, "readonly"=>'0', "width"=>6),
              "Z"=>array("type"=>1, "params"=>"", "label"=>"Side-Slope Ratio","visible"=>1, "readonly"=>'0', "width"=>24),
              "n"=>array("type"=>1, "params"=>"", "label"=>"Mannings N - Channel Roughness","visible"=>1, "readonly"=>'0', "width"=>24),
              "substrateclass"=>array("type"=>1, "params"=>"", "label"=>"Substrate Class (A-D)","visible"=>1, "readonly"=>'0', "width"=>24),
              "Qvar"=>array("type"=>3,"params"=>"0|False,1|True:fvid:fvname::0:","label"=>"Flow Variable ","visible"=>1, "readonly"=>0, "width"=>6),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>24),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "USGSChannelGeomObject"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Stream Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "starttime"=>array("type"=>1,"params"=>"","label"=>"Simulation Start Time (YYYY/MM/DD hh:mm:ss)","visible"=>1, "readonly"=>0, "width"=>32),
              "endtime"=>array("type"=>1,"params"=>"","label"=>"Simulation End Time (YYYY/MM/DD hh:mm:ss)","visible"=>1, "readonly"=>0, "width"=>32),
              "dt"=>array("type"=>1,"params"=>"","label"=>"Simulation Time-Step (seconds)","visible"=>1, "readonly"=>0, "width"=>6),
              "outputinterval"=>array("type"=>1,"params"=>"","label"=>"Output interval (in time steps)","visible"=>1, "readonly"=>0, "width"=>6),
              "drainage_area"=>array("type"=>1,"params"=>"","label"=>"Watershed Drainage Area (sq. mi.)","visible"=>1, "readonly"=>0, "width"=>12),
              "area"=>array("type"=>1,"params"=>"","label"=>"Local Drainage Area (sq. mi.)","visible"=>1, "readonly"=>0, "width"=>12),
              "length"=>array("type"=>1, "params"=>"","label"=>"Channel Mainstem Length","visible"=>1, "readonly"=>'0', "width"=>6),
              "province"=>array("type"=>3,"params"=>"1|Appalachian Plateau,2|Valley and Ridge,3|Piedmont,4|Coastal Plain:prid:prname::0","label"=>"Physiographic Province: ","visible"=>1, "readonly"=>0, "width"=>6),
              "reset_channelprops"=>array("type"=>3,"params"=>"0|False,1|True:rcpid:rcpname::0:","label"=>"Recalculate Channel Properties? ","visible"=>1, "readonly"=>0, "width"=>6),
              "base"=>array("type"=>1,"params"=>"","label"=>"Channel Base Width ft(auto-calc)","visible"=>1, "readonly"=>0, "width"=>12),
              "slope"=>array("type"=>1, "params"=>"","label"=>"Channel Slope (ft/ft)","visible"=>1, "readonly"=>'0', "width"=>6),
              "Z"=>array("type"=>1, "params"=>"", "label"=>"Side-Slope Ratio (auto-calc)","visible"=>1, "readonly"=>0, "width"=>24),
              "n"=>array("type"=>1, "params"=>"", "label"=>"Mannings N - Channel Roughness","visible"=>1, "readonly"=>'0', "width"=>24),
              "substrateclass"=>array("type"=>1, "params"=>"", "label"=>"Substrate Class (A-D)","visible"=>1, "readonly"=>'0', "width"=>24),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "cascadedebug"=>array("type"=>3,"params"=>"0|False,1|True:fmid:fmname::0:","label"=>"Force debug mode to child objects? ","visible"=>1, "readonly"=>0, "width"=>6),
              "geomx"=>array("type"=>1, "params"=>"", "label"=>"X coordinate (lon)","visible"=>1, "readonly"=>'0', "width"=>24),
              "geomy"=>array("type"=>1, "params"=>"", "label"=>"Y coordinate (lat)","visible"=>1, "readonly"=>'0', "width"=>24),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "USGSChannelGeomObject_sub"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Object Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "drainage_area"=>array("type"=>1,"params"=>"","label"=>"Watershed Drainage Area (sq. mi.)","visible"=>1, "readonly"=>0, "width"=>12),
              "area"=>array("type"=>1,"params"=>"","label"=>"Local Drainage Area (sq. mi.)","visible"=>1, "readonly"=>0, "width"=>12),
              "length"=>array("type"=>1, "params"=>"","label"=>"Channel Mainstem Length","visible"=>1, "readonly"=>'0', "width"=>6),
              "province"=>array("type"=>3,"params"=>"1|Appalachian Plateau,2|Valley and Ridge,3|Piedmont,4|Coastal Plain:prid:prname::0","label"=>"Physiographic Province: ","visible"=>1, "readonly"=>0, "width"=>6),
              "reset_channelprops"=>array("type"=>3,"params"=>"0|False,1|True:rcpid:rcpname::0:","label"=>"Recalculate Channel Properties? ","visible"=>1, "readonly"=>0, "width"=>6),
              "base"=>array("type"=>1,"params"=>"","label"=>"Channel Base Width(ft)","visible"=>1, "readonly"=>0, "width"=>12),
              "slope"=>array("type"=>1, "params"=>"","label"=>"Channel Slope (ft/ft)","visible"=>1, "readonly"=>'0', "width"=>6),
              "Z"=>array("type"=>1, "params"=>"", "label"=>"Side-Slope Ratio","visible"=>1, "readonly"=>0, "width"=>24),
              "n"=>array("type"=>1, "params"=>"", "label"=>"Mannings N - Channel Roughness","visible"=>1, "readonly"=>'0', "width"=>24),
              "substrateclass"=>array("type"=>1, "params"=>"", "label"=>"Substrate Class (A-D)","visible"=>1, "readonly"=>'0', "width"=>24),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "q_var"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:qpid:qpname::0","label"=>"Upstream Inflow: ","visible"=>1, "readonly"=>0, "width"=>6),
              "r_var"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:rpid:rpname::0","label"=>"Runoff Inflow: ","visible"=>1, "readonly"=>0, "width"=>6),
              "w_var"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:wpid:wpname::0","label"=>"Demand (cfs): ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "timeSeriesInput"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "cache_ts"=>array("type"=>3,"params"=>"0|False,1|True:lid:lname::0:","label"=>"Retrieve/Store Time Series Values in File", "visible"=>1, "readonly"=>0, "width"=>6),
              "filepath"=>array("type"=>22,"params"=>"tsfile","label"=>"Time Series File", "visible"=>1, "readonly"=>0, "width"=>80),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "cascadedebug"=>array("type"=>3,"params"=>"0|False,1|True:fmid:fmname::0:","label"=>"Force debug mode to child objects? ","visible"=>1, "readonly"=>0, "width"=>6),
              "max_memory_values"=>array("type"=>1, "params"=>"", "label"=>"Max. Values to Store in Memory (-1 is unlimited)","visible"=>1, "readonly"=>'0', "width"=>24),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "timeSeriesFile"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "delimiter"=>array("type"=>3,"params"=>"0|Comma,1|Tab,2|pipe,3|Space:dlid:dlname::0:","label"=>"File Delimiter ","visible"=>1, "readonly"=>0, "width"=>6),
              "intmethod"=>array("type"=>3,"params"=>"1|Previous Value,2|Next Value,3|Mean,4|Mininum,5|Maximum,6|Sum:imid:imname::0:","label"=>"Gage Type","visible"=>1, "readonly"=>0, "width"=>12),
              "location_type"=>array("type"=>3,"params"=>"0|Local File,1|Remote URL:ltid:ltname::0:","label"=>"File Location Type","visible"=>1, "readonly"=>0, "width"=>12),
              "remote_url"=>array("type"=>1,"params"=>"","label"=>"Remote URL", "visible"=>1, "readonly"=>0, "width"=>80),
              "filepath"=>array("type"=>22,"params"=>"tsfile","label"=>"Local File", "visible"=>1, "readonly"=>0, "width"=>80),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "cascadedebug"=>array("type"=>3,"params"=>"0|False,1|True:fmid:fmname::0:","label"=>"Force debug mode to child objects? ","visible"=>1, "readonly"=>0, "width"=>6),
              "max_memory_values"=>array("type"=>1, "params"=>"", "label"=>"Max. Values to Store in Memory (-1 is unlimited)","visible"=>1, "readonly"=>'0', "width"=>24),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "storageObject"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "full_depth"=>array("type"=>1,"params"=>"","label"=>"Water Surface Elevation at Full Storage (ft)","visible"=>1, "readonly"=>0, "width"=>12),
              "maxcapacity"=>array("type"=>1,"params"=>"","label"=>"Volume at Full Storage (acre-ft)","visible"=>1, "readonly"=>0, "width"=>12),
              "initstorage"=>array("type"=>1,"params"=>"","label"=>"Initial Storage (acre-ft)","visible"=>1, "readonly"=>0, "width"=>12),
              "unusable_storage"=>array("type"=>1,"params"=>"","label"=>"Volume Where water is unusable (acre-ft)","visible"=>1, "readonly"=>0, "width"=>12),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "logfile"=>array("type"=>1,"params"=>"","label"=>"Export Data to File Name: ","visible"=>1, "readonly"=>0, "width"=>6),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "cascadedebug"=>array("type"=>3,"params"=>"0|False,1|True:fmid:fmname::0:","label"=>"Force debug mode to child objects? ","visible"=>1, "readonly"=>0, "width"=>6),
              "geomx"=>array("type"=>1, "params"=>"", "label"=>"X coordinate (lon)","visible"=>1, "readonly"=>'0', "width"=>24),
              "geomy"=>array("type"=>1, "params"=>"", "label"=>"Y coordinate (lat)","visible"=>1, "readonly"=>'0', "width"=>24),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "hydroImpoundment"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "full_depth"=>array("type"=>1,"params"=>"","label"=>"Water Surface Elevation at Full Storage (ft)","visible"=>1, "readonly"=>0, "width"=>12),
              "maxcapacity"=>array("type"=>1,"params"=>"","label"=>"Volume at Full Storage (acre-ft)","visible"=>1, "readonly"=>0, "width"=>12),
              "full_surface_area"=>array("type"=>1,"params"=>"","label"=>"Surface Area at Full Storage (acres)","visible"=>1, "readonly"=>0, "width"=>12),
              "initstorage"=>array("type"=>1,"params"=>"","label"=>"Initial Storage (acre-ft)","visible"=>1, "readonly"=>0, "width"=>12),
              "unusable_storage"=>array("type"=>1,"params"=>"","label"=>"Volume Where water is unusable (acre-ft)","visible"=>1, "readonly"=>0, "width"=>12),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "logfile"=>array("type"=>1,"params"=>"","label"=>"Export Data to File Name: ","visible"=>1, "readonly"=>0, "width"=>6),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "cascadedebug"=>array("type"=>3,"params"=>"0|False,1|True:fmid:fmname::0:","label"=>"Force debug mode to child objects? ","visible"=>1, "readonly"=>0, "width"=>6),
              "geomx"=>array("type"=>1, "params"=>"", "label"=>"X coordinate (lon)","visible"=>1, "readonly"=>'0', "width"=>24),
              "geomy"=>array("type"=>1, "params"=>"", "label"=>"Y coordinate (lat)","visible"=>1, "readonly"=>'0', "width"=>24),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "hydroImpSmall"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "storage_stage_area"=>array("type"=>1,"params"=>"","label"=>"Storage Table","visible"=>1, "readonly"=>0, "width"=>12),
              "initstorage"=>array("type"=>1,"params"=>"","label"=>"Initial Storage (acre-ft)","visible"=>1, "readonly"=>0, "width"=>12),
              "maxcapacity"=>array("type"=>1,"params"=>"","label"=>"Max Storage (acre-ft)","visible"=>1, "readonly"=>0, "width"=>12),
              "unusable_storage"=>array("type"=>1,"params"=>"","label"=>"Volume Where water is unusable (acre-ft)","visible"=>1, "readonly"=>0, "width"=>12),
              "Qin"=>array("type"=>3,"params"=>"0|False,1|True:said:saname::0:","label"=>"Inflow (cfs)","visible"=>1, "readonly"=>0, "width"=>12),
              "demand"=>array("type"=>3,"params"=>"0|False,1|True:said:saname::0:","label"=>"Demand (MGD)","visible"=>1, "readonly"=>0, "width"=>12),
              "refill"=>array("type"=>3,"params"=>"0|False,1|True:said:saname::0:","label"=>"Inflow (MGD)","visible"=>1, "readonly"=>0, "width"=>12),
              "et_in"=>array("type"=>3,"params"=>"0|False,1|True:said:saname::0:","label"=>"Evap (in/day)","visible"=>1, "readonly"=>0, "width"=>12),
              "precip_in"=>array("type"=>3,"params"=>"0|False,1|True:said:saname::0:","label"=>"Precip (in/day)","visible"=>1, "readonly"=>0, "width"=>12),
              "release"=>array("type"=>3,"params"=>"0|False,1|True:said:saname::0:","label"=>"Release (MGD)","visible"=>1, "readonly"=>0, "width"=>12),
              "riser_diameter"=>array("type"=>1,"params"=>"","label"=>"Riser Diameter","visible"=>1, "readonly"=>0, "width"=>12),
              "riser_pipe_flow_head"=>array("type"=>1,"params"=>"","label"=>"Riser Pipe Flow Head","visible"=>1, "readonly"=>0, "width"=>12),
              "riser_opening_storage"=>array("type"=>1,"params"=>"","label"=>"Riser Pipe opening Storage","visible"=>1, "readonly"=>0, "width"=>12),
              "riser_length"=>array("type"=>1,"params"=>"","label"=>"Riser Length","visible"=>1, "readonly"=>0, "width"=>12),
              "riser_enabled"=>array("type"=>3,"params"=>"0|False,1|True:said:saname::0:","label"=>"Use Riser","visible"=>1, "readonly"=>0, "width"=>12),
              "delimiter"=>array("type"=>3,"params"=>"0|Comma,1|Tab,2|pipe,3|Space:dlid:dlname::0:","label"=>"Text Delimiter ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>24),
              "log_solution_problems"=>array("type"=>3,"params"=>"0|False,1|True:lspid:lspname::0","label"=>"Log Failed Solutions? ","visible"=>1, "readonly"=>0, "width"=>6),
           )
        ),
      "modelContainer"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Object Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "starttime"=>array("type"=>1,"params"=>"","label"=>"Simulation Start Time (YYYY/MM/DD hh:mm:ss)","visible"=>1, "readonly"=>0, "width"=>32),
              "endtime"=>array("type"=>1,"params"=>"","label"=>"Simulation End Time (YYYY/MM/DD hh:mm:ss)","visible"=>1, "readonly"=>0, "width"=>32),
              "dt"=>array("type"=>1,"params"=>"","label"=>"Simulation Time-Step (seconds)","visible"=>1, "readonly"=>0, "width"=>6),
              "custom1"=>array("type"=>1,"params"=>"","label"=>"Custom #1","visible"=>1, "readonly"=>0, "width"=>32),
              "custom2"=>array("type"=>1,"params"=>"","label"=>"Custom #2","visible"=>1, "readonly"=>0, "width"=>32),
              "outputinterval"=>array("type"=>1,"params"=>"","label"=>"Output interval (in time steps)","visible"=>1, "readonly"=>0, "width"=>6),
              "standalone"=>array("type"=>3,"params"=>"0|False,1|True:said:saname::0:","label"=>"Can be run as standalone? ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "run_mode"=>array("type"=>1,"params"=>"","label"=>"Run Mode (for custom use by model components) ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "bufferlog"=>array("type"=>3,"params"=>"0|False,1|True:blid:blname::0","label"=>"Buffer Log Queries? ","visible"=>1, "readonly"=>0, "width"=>6),
              "cascadedebug"=>array("type"=>3,"params"=>"0|False,1|True:fmid:fmname::0:","label"=>"Force debug mode to child objects? ","visible"=>1, "readonly"=>0, "width"=>6),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "waterSupplyModelNode"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Object Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "starttime"=>array("type"=>1,"params"=>"","label"=>"Simulation Start Time (YYYY/MM/DD hh:mm:ss)","visible"=>1, "readonly"=>0, "width"=>32),
              "endtime"=>array("type"=>1,"params"=>"","label"=>"Simulation End Time (YYYY/MM/DD hh:mm:ss)","visible"=>1, "readonly"=>0, "width"=>32),
              "dt"=>array("type"=>1,"params"=>"","label"=>"Simulation Time-Step (seconds)","visible"=>1, "readonly"=>0, "width"=>6),
              "outputinterval"=>array("type"=>1,"params"=>"","label"=>"Output interval (in time steps)","visible"=>1, "readonly"=>0, "width"=>6),
              "standalone"=>array("type"=>3,"params"=>"0|False,1|True:said:saname::0:","label"=>"Can be run as standalone? ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "run_mode"=>array("type"=>1,"params"=>"","label"=>"Run Mode (for custom use by model components) ","visible"=>1, "readonly"=>0, "width"=>6),
              "flow_mode"=>array("type"=>3,"params"=>"0|Best Available,1|USGS Baseline,2|USGS Synthetic,3|VAHydro HSPF,4|USGS Historical:fmid:fmname::0","label"=>"Flow Mode (0 - best available, 1 - USGS baseline, 2 - USGS Synth, 3 - VA HSPF, 4+ - Custom) ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "bufferlog"=>array("type"=>3,"params"=>"0|False,1|True:blid:blname::0","label"=>"Buffer Log Queries? ","visible"=>1, "readonly"=>0, "width"=>6),
              "cascadedebug"=>array("type"=>3,"params"=>"0|False,1|True:fmid:fmname::0:","label"=>"Force debug mode to child objects? ","visible"=>1, "readonly"=>0, "width"=>6),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "waterSupplyElement"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Object Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "starttime"=>array("type"=>1,"params"=>"","label"=>"Simulation Start Time (YYYY/MM/DD hh:mm:ss)","visible"=>1, "readonly"=>0, "width"=>32),
              "endtime"=>array("type"=>1,"params"=>"","label"=>"Simulation End Time (YYYY/MM/DD hh:mm:ss)","visible"=>1, "readonly"=>0, "width"=>32),
              "dt"=>array("type"=>1,"params"=>"","label"=>"Simulation Time-Step (seconds)","visible"=>1, "readonly"=>0, "width"=>6),
              "outputinterval"=>array("type"=>1,"params"=>"","label"=>"Output interval (in time steps)","visible"=>1, "readonly"=>0, "width"=>6),
              "standalone"=>array("type"=>3,"params"=>"0|False,1|True:said:saname::0:","label"=>"Can be run as standalone? ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "run_mode"=>array("type"=>1,"params"=>"","label"=>"Run Mode (for custom use by model components) ","visible"=>1, "readonly"=>0, "width"=>6),
              "flow_mode"=>array("type"=>3,"params"=>"0|Best Available,1|USGS Baseline,2|USGS Synthetic,3|VAHydro HSPF,4|USGS Historical:fmid:fmname::0","label"=>"Flow Mode (0 - best available, 1 - USGS baseline, 2 - USGS Synth, 3 - VA HSPF, 4+ - Custom) ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "bufferlog"=>array("type"=>3,"params"=>"0|False,1|True:blid:blname::0","label"=>"Buffer Log Queries? ","visible"=>1, "readonly"=>0, "width"=>6),
              "cascadedebug"=>array("type"=>3,"params"=>"0|False,1|True:fmid:fmname::0:","label"=>"Force debug mode to child objects? ","visible"=>1, "readonly"=>0, "width"=>6),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "hydroContainer"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Object Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "starttime"=>array("type"=>1,"params"=>"","label"=>"Simulation Start Time (YYYY/MM/DD hh:mm:ss)","visible"=>1, "readonly"=>0, "width"=>32),
              "endtime"=>array("type"=>1,"params"=>"","label"=>"Simulation End Time (YYYY/MM/DD hh:mm:ss)","visible"=>1, "readonly"=>0, "width"=>32),
              "dt"=>array("type"=>1,"params"=>"","label"=>"Simulation Time-Step (seconds)","visible"=>1, "readonly"=>0, "width"=>6),
              "outputinterval"=>array("type"=>1,"params"=>"","label"=>"Output interval (in time steps)","visible"=>1, "readonly"=>0, "width"=>6),
              "run_mode"=>array("type"=>1,"params"=>"","label"=>"Run Mode (for custom use by model components) ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "bufferlog"=>array("type"=>3,"params"=>"0|False,1|True:blid:blname::0","label"=>"Buffer Log Queries? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "cascadedebug"=>array("type"=>3,"params"=>"0|False,1|True:fmid:fmname::0:","label"=>"Force debug mode to child objects? ","visible"=>1, "readonly"=>0, "width"=>6),
              "geomx"=>array("type"=>1, "params"=>"", "label"=>"X coordinate (lon)","visible"=>1, "readonly"=>'0', "width"=>24),
              "geomy"=>array("type"=>1, "params"=>"", "label"=>"Y coordinate (lat)","visible"=>1, "readonly"=>'0', "width"=>24),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "CBPModelContainer"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Object Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "starttime"=>array("type"=>1,"params"=>"","label"=>"Simulation Start Time (YYYY/MM/DD hh:mm:ss)","visible"=>1, "readonly"=>0, "width"=>32),
              "endtime"=>array("type"=>1,"params"=>"","label"=>"Simulation End Time (YYYY/MM/DD hh:mm:ss)","visible"=>1, "readonly"=>0, "width"=>32),
              "dt"=>array("type"=>1,"params"=>"","label"=>"Simulation Time-Step (seconds)","visible"=>1, "readonly"=>0, "width"=>6),
              "filepath"=>array("type"=>22,"params"=>"uci","label"=>"UCI File", "visible"=>1, "readonly"=>0, "width"=>80),
              "autoloadriver"=>array("type"=>3,"params"=>"0|False,1|True:alid:alname::0:","label"=>"Auto-load river components? ","visible"=>1, "readonly"=>0, "width"=>6),
              "autoloadland"=>array("type"=>3,"params"=>"0|False,1|True:allid:allname::0:","label"=>"Auto-load land components? ","visible"=>1, "readonly"=>0, "width"=>6),
              "run_mode"=>array("type"=>1,"params"=>"","label"=>"Run Mode (for custom use by model components) ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "cascadedebug"=>array("type"=>3,"params"=>"0|False,1|True:fmid:fmname::0:","label"=>"Force debug mode to child objects? ","visible"=>1, "readonly"=>0, "width"=>6),
              "geomx"=>array("type"=>1, "params"=>"", "label"=>"X coordinate (lon)","visible"=>1, "readonly"=>'0', "width"=>24),
              "geomy"=>array("type"=>1, "params"=>"", "label"=>"Y coordinate (lat)","visible"=>1, "readonly"=>'0', "width"=>24),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "HSPFContainer"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Object Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "filepath"=>array("type"=>22,"params"=>"uci","label"=>"UCI File", "visible"=>1, "readonly"=>0, "width"=>80),
              "hspf_timestep"=>array("type"=>1, "params"=>"", "label"=>"HSPF Time-step (seconds)","visible"=>1, "readonly"=>'0', "width"=>24),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "max_memory_values"=>array("type"=>1, "params"=>"", "label"=>"Max. Values for DSNs to Store in Memory (-1 is unlimited)","visible"=>1, "readonly"=>'0', "width"=>24),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "cascadedebug"=>array("type"=>3,"params"=>"0|False,1|True:fmid:fmname::0:","label"=>"Force debug mode to child objects? ","visible"=>1, "readonly"=>0, "width"=>6),
              "geomx"=>array("type"=>1, "params"=>"", "label"=>"X coordinate (lon)","visible"=>1, "readonly"=>'0', "width"=>24),
              "geomy"=>array("type"=>1, "params"=>"", "label"=>"Y coordinate (lat)","visible"=>1, "readonly"=>'0', "width"=>24),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "HSPFPlotgen"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Variable Name","visible"=>1, "readonly"=>0, "width"=>32),
              "plotgenoutput"=>array("type"=>3,"params"=>"1|1:fid:fname::0:","label"=>"Plotgen Output","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64)
           )
        ),
      "WDMDSNaccessor"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Variable Name","visible"=>1, "readonly"=>0, "width"=>32),
              "wdmoutput"=>array("type"=>3,"params"=>"1|1:fid:fname::0:","label"=>"WDM Output","visible"=>1, "readonly"=>0, "width"=>32),
              "max_memory_values"=>array("type"=>1, "params"=>"", "label"=>"Max. Values to Store in Memory (-1 is unlimited)","visible"=>1, "readonly"=>'0', "width"=>24),
              "intmethod"=>array("type"=>3,"params"=>"0|linear,1|previous value,2|next value,3|period mean,4|period min,5|period max ,6|period sum:imid:imname::0:","label"=>"Data Interpolation","visible"=>1, "readonly"=>0, "width"=>32),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64)
           )
        ),
      "Equation"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Variable Name","visible"=>1, "readonly"=>0, "width"=>8),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "equation"=>array("type"=>1,"params"=>"","label"=>"=","visible"=>1, "readonly"=>0, "width"=>64),
              "defaultval"=>array("type"=>1,"params"=>"","label"=>"Initial Value","visible"=>1, "readonly"=>0, "width"=>24),
              "nanvalue"=>array("type"=>1,"params"=>"","label"=>"Null/Nan Value","visible"=>1, "readonly"=>0, "width"=>24),
              "strictnull"=>array("type"=>3,"params"=>"0|False,1|true:typeid:typename::0","label"=>"Strict Null Evaluation","visible"=>1, "readonly"=>0, "width"=>24),
              "nonnegative"=>array("type"=>3,"params"=>"0|False,1|true:nnid:nnename::0","label"=>"Non-Negative?","visible"=>1, "readonly"=>0, "width"=>24),
              "minvalue"=>array("type"=>1,"params"=>"","label"=>"Minimum Value (if non-negative)","visible"=>1, "readonly"=>0, "width"=>24),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>24),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "dataMatrix"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Variable Name","visible"=>1, "readonly"=>0, "width"=>24),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "matrix"=>array("type"=>1,"params"=>"","label"=>"Matrix ","visible"=>1, "readonly"=>0, "width"=>64),
              "defaultval"=>array("type"=>1,"params"=>"","label"=>"Default Value ","visible"=>1, "readonly"=>0, "width"=>6),
              "delimiter"=>array("type"=>3,"params"=>"0|Comma,1|Tab,2|pipe,3|Space:dlid:dlname::0:","label"=>"Text Delimiter ","visible"=>1, "readonly"=>0, "width"=>6),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>24),
              "valuetype"=>array("type"=>3,"params"=>"0|Normal,1|1-D Lookup,2|2-D Lookup,3|CSV(pure assoc array):vtid:vtname::0","label"=>"Value Type ","visible"=>1, "readonly"=>0, "width"=>6),
              "keycol1"=>array("type"=>3,"params"=>"0|False,1|True:k1id:k1name::0","label"=>"Lookup Row Key ","visible"=>1, "readonly"=>0, "width"=>6),
              "lutype1"=>array("type"=>3,"params"=>"0|Exact Match,1|Interpolated,2|Stair Step,3|Key Interpolate:lu1id:lu1name::0","label"=>"Lookup Type","visible"=>1, "readonly"=>0, "width"=>24),
              "keycol2"=>array("type"=>3,"params"=>"0|False,1|True:k2id:k2name::0","label"=>"Lookup Column Key ","visible"=>1, "readonly"=>0, "width"=>6),
              "lutype2"=>array("type"=>3,"params"=>"0|Exact Match,1|Interpolated,2|Stair Step,3|Key Interpolate:lu2id:lu2name::0","label"=>"Lookup Type","visible"=>1, "readonly"=>0, "width"=>24),
              "autosetvars"=>array("type"=>3,"params"=>"0|False,1|True:asvid:asvname::0","label"=>"Auto-Set Parent Vars?","visible"=>1, "readonly"=>0, "width"=>24),
              "loggable"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Loggable? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "matrixAccessor"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Variable Name","visible"=>1, "readonly"=>0, "width"=>24),
              "defaultval"=>array("type"=>1,"params"=>"","label"=>"Default Value ","visible"=>1, "readonly"=>0, "width"=>6),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "targetmatrix"=>array("type"=>3,"params"=>"0|False,1|True:k1id:k1name::0","label"=>"Target Matrix ","visible"=>1, "readonly"=>0, "width"=>6),
              "keycol1"=>array("type"=>1,"params"=>"","label"=>"Row Expression ","visible"=>1, "readonly"=>0, "width"=>16),
              "coltype1"=>array("type"=>3,"params"=>"0|Text/Constant,1|Equation:ct1id:ct1name::0","visible"=>1, "readonly"=>0, "width"=>16),
              "keycol2"=>array("type"=>1,"params"=>"","label"=>"Col Expression ","visible"=>1, "readonly"=>0, "width"=>16),
              "coltype2"=>array("type"=>3,"params"=>"0|Text/Constant,1|Equation:ct2id:ct2name::0","visible"=>1, "readonly"=>0, "width"=>16),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>24),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "Statistic"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "name"=>array("type"=>1,"params"=>"","label"=>"Variable Name","visible"=>1, "readonly"=>0, "width"=>8),
              "statname"=>array("type"=>3,"params"=>"min|Min,max|Max,mean|Mean,median|Median,stddev|Std. Dev.,pow|Power(x ^ y),log|ln,log10|log base 10,stack|Stack:typeid:typename::0","label"=>"Statistical Operator","visible"=>1, "readonly"=>0, "width"=>24),
              "operands"=>array("type"=>1,"params"=>"","label"=>"CSV List of operands","visible"=>1, "readonly"=>0, "width"=>24),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>24),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "HabitatSuitabilityObject"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"HSI Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>24),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "geomx"=>array("type"=>1, "params"=>"", "label"=>"X coordinate (lon)","visible"=>1, "readonly"=>'0', "width"=>24),
              "geomy"=>array("type"=>1, "params"=>"", "label"=>"Y coordinate (lat)","visible"=>1, "readonly"=>'0', "width"=>24),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "HabitatSuitabilityObject_NWRC"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"HSI Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>24),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "units"=>array("type"=>3,"params"=>"1|Metric(SI),2|English(EE):unid:unname::0","label"=>"Units","visible"=>1, "readonly"=>0, "width"=>6),
              "V"=>array("type"=>1,"params"=>"","label"=>"Flow Velocity (default)","visible"=>1, "readonly"=>0, "width"=>6),
              "depth"=>array("type"=>1,"params"=>"","label"=>"Flow Depth (default)","visible"=>1, "readonly"=>0, "width"=>6),
              "pH"=>array("type"=>1,"params"=>"","label"=>"pH (default)","visible"=>1, "readonly"=>0, "width"=>6),
              "T"=>array("type"=>1,"params"=>"","label"=>"Temp (default)","visible"=>1, "readonly"=>0, "width"=>6),
              "dT_bot"=>array("type"=>1,"params"=>"","label"=>"Ratio of bottom temp to mean temp","visible"=>1, "readonly"=>0, "width"=>6),
              "dT_sur"=>array("type"=>1,"params"=>"","label"=>"Ratio of surface temp to mean temp","visible"=>1, "readonly"=>0, "width"=>6),
              "DO"=>array("type"=>1,"params"=>"","label"=>"Dis. Oxygen, ppm (default)","visible"=>1, "readonly"=>0, "width"=>6),
              "salinity"=>array("type"=>1,"params"=>"","label"=>"Salinity, ppm (default)","visible"=>1, "readonly"=>0, "width"=>6),
              "tds"=>array("type"=>1,"params"=>"","label"=>"Total Disolved Solids, ppm (default)","visible"=>1, "readonly"=>0, "width"=>6),
              "tur"=>array("type"=>1,"params"=>"","label"=>"Turbidity (JTU)","visible"=>1, "readonly"=>0, "width"=>6),
              "substrateclass"=>array("type"=>3,"params"=>"A|Silt,Sand (<0.2 cm) and/or rooted vegetation,B|Pebble (0.2-1.5 cm),C|Gravel, broken rock (1.6-2.0 cm),D|Cobble,E|Boulder and Bedrock:ssmid:ssmname::0","label"=>"Subtrate Class","visible"=>1, "readonly"=>0, "width"=>6),
              "sub_silt_pct"=>array("type"=>1,"params"=>"","label"=>"Substrate silt percent","visible"=>1, "readonly"=>0, "width"=>6),
              "sub_sand_pct"=>array("type"=>1,"params"=>"","label"=>"Substrate sand percent","visible"=>1, "readonly"=>0, "width"=>6),
              "sub_pebble_pct"=>array("type"=>1,"params"=>"","label"=>"substrate pebble percent","visible"=>1, "readonly"=>0, "width"=>6),
              "sub_cobble_pct"=>array("type"=>1,"params"=>"","label"=>"substrate cobble percent","visible"=>1, "readonly"=>0, "width"=>6),
              "sub_rock_pct"=>array("type"=>1,"params"=>"","label"=>"substrate large rock percent","visible"=>1, "readonly"=>0, "width"=>6),
              "slope"=>array("type"=>1,"params"=>"","label"=>"slope percent","visible"=>1, "readonly"=>0, "width"=>6),
              "width"=>array("type"=>1,"params"=>"","label"=>"Channel base width ft / m","visible"=>1, "readonly"=>0, "width"=>6),
              "length"=>array("type"=>1,"params"=>"","label"=>"channel section length ft / m","visible"=>1, "readonly"=>0, "width"=>6),
              "pool_pct"=>array("type"=>1,"params"=>"","label"=>"Percent Pools","visible"=>1, "readonly"=>0, "width"=>6),
              "pool_depth"=>array("type"=>1,"params"=>"","label"=>"depth of pools (ft/m) - relative to the channel mean","visible"=>1, "readonly"=>0, "width"=>6),
              "pool_substrate"=>array("type"=>3,"params"=>"A|Silt,Sand (<0.2 cm) and/or rooted vegetation,B|Pebble (0.2-1.5 cm),C|Gravel, broken rock (1.6-2.0 cm),D|Cobble,E|Boulder and Bedrock:psmid:psmname::0","label"=>"substrate in pools","visible"=>1, "readonly"=>0, "width"=>6),
              "pool_class"=>array("type"=>3,"params"=>"A|A,B|B:pcid:pcname::0","label"=>"<ul><li>A - large,deep 'deadwater' pools (stream mouths),<li>B - Moderate below falls or riffle-run areas; 5-30% of bottom obscured by turbulence<li>C - Small or shallow or both, no surface turbulence and little structure</ul>","visible"=>1, "readonly"=>0, "width"=>6),
              "riffle_pct"=>array("type"=>1,"params"=>"","label"=>"Percent Riffles","visible"=>1, "readonly"=>0, "width"=>6),
              "riffle_depth"=>array("type"=>1,"params"=>"","label"=>"depth of riffles - relative to the channel mean","visible"=>1, "readonly"=>0, "width"=>6),
              "riffle_substrate"=>array("type"=>3,"params"=>"A|Silt,Sand (<0.2 cm) and/or rooted vegetation,B|Pebble (0.2-1.5 cm),C|Gravel, broken rock (1.6-2.0 cm),D|Cobble,E|Boulder and Bedrock:rsmid:rsmname::0","label"=>"substrate in riffles","visible"=>1, "readonly"=>0, "width"=>6),
              "run_pct"=>array("type"=>1,"params"=>"","label"=>"Percent Runs","visible"=>1, "readonly"=>0, "width"=>6),
              "run_depth"=>array("type"=>1,"params"=>"","label"=>"depth of runs - relative to the channel mean","visible"=>1, "readonly"=>0, "width"=>6),
              "run_substrate"=>array("type"=>3,"params"=>"A|Silt,Sand (<0.2 cm) and/or rooted vegetation,B|Pebble (0.2-1.5 cm),C|Gravel, broken rock (1.6-2.0 cm),D|Cobble,E|Boulder and Bedrock:rusmid:rusmname::0","label"=>"substrate in runs","visible"=>1, "readonly"=>0, "width"=>6),
              "margin_pct"=>array("type"=>1,"params"=>"","label"=>"Percent Shallow Margin","visible"=>1, "readonly"=>0, "width"=>6),
              "margin_depth"=>array("type"=>1,"params"=>"","label"=>"depth of margins - relative to the channel mean","visible"=>1, "readonly"=>0, "width"=>6),
              "margin_substrate"=>array("type"=>3,"params"=>"A|Silt,Sand (<0.2 cm) and/or rooted vegetation,B|Pebble (0.2-1.5 cm),C|Gravel, broken rock (1.6-2.0 cm),D|Cobble,E|Boulder and Bedrock:msmid:msmname::0","label"=>"substrate in margins","visible"=>1, "readonly"=>0, "width"=>6),
              "cover_pct_lg"=>array("type"=>1,"params"=>"","label"=>"percent cover from large objects, such as boulders, stumps, crevices","visible"=>1, "readonly"=>0, "width"=>6),
              "cover_pct_sv"=>array("type"=>1,"params"=>"","label"=>"percent cover from vegetation","visible"=>1, "readonly"=>0, "width"=>6),
              "wetland_pct"=>array("type"=>1,"params"=>"","label"=>"Percent Wetland","visible"=>1, "readonly"=>0, "width"=>6),
              "shade_pct"=>array("type"=>1,"params"=>"","label"=>"percent of stream area shaded","visible"=>1, "readonly"=>0, "width"=>6),
              "zoopl_count"=>array("type"=>1,"params"=>"","label"=>"mean zooplankton count per gal / liter of water","visible"=>1, "readonly"=>0, "width"=>6),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "surfaceObject"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Element Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "base"=>array("type"=>1, "params"=>"", "label"=>"X coordinate (lon)","visible"=>1, "readonly"=>'0', "width"=>24),
              "length"=>array("type"=>1, "params"=>"", "label"=>"Y coordinate (lat)","visible"=>1, "readonly"=>'0', "width"=>24),
              "irate"=>array("type"=>1, "params"=>"", "label"=>"Infiltration Rate (in/hr)","visible"=>1, "readonly"=>'0', "width"=>24),
              "pct_clay"=>array("type"=>1, "params"=>"", "label"=>"% Clay","visible"=>1, "readonly"=>'0', "width"=>24),
              "pct_sand"=>array("type"=>1, "params"=>"", "label"=>"% Sand","visible"=>1, "readonly"=>'0', "width"=>24),
              "pct_om"=>array("type"=>1, "params"=>"", "label"=>"% Organic Matter","visible"=>1, "readonly"=>'0', "width"=>24),
              "ksat"=>array("type"=>1, "params"=>"", "label"=>"K at saturation (KSat)","visible"=>1, "readonly"=>'0', "width"=>24),
              "wiltp"=>array("type"=>1, "params"=>"", "label"=>"Wilting Point (in/in)","visible"=>1, "readonly"=>'0', "width"=>24),
              "thetasat"=>array("type"=>1, "params"=>"", "label"=>"Theta at Saturation (ThetaSat)","visible"=>1, "readonly"=>'0', "width"=>24),
              "Sav"=>array("type"=>1, "params"=>"", "label"=>"Wetting Front Suction","visible"=>1, "readonly"=>'0', "width"=>24),
              "fc"=>array("type"=>1, "params"=>"", "label"=>"Field Capacity (in/in)","visible"=>1, "readonly"=>'0', "width"=>24),
              "F"=>array("type"=>1, "params"=>"", "label"=>"Initial water infiltrated into soil (in)","visible"=>1, "readonly"=>'0', "width"=>24),
              "sdepth"=>array("type"=>1, "params"=>"", "label"=>"Soil Layer Depth","visible"=>1, "readonly"=>'0', "width"=>24),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>24),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "PopulationGenerationObject"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"HSI Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "population"=>array("type"=>1,"params"=>"","label"=>"Initial Population","visible"=>1, "readonly"=>0, "width"=>32),
              "birth_rate"=>array("type"=>1,"params"=>"","label"=>"Birth Rate (zeroth order)","visible"=>1, "readonly"=>0, "width"=>8),
              "death_rate"=>array("type"=>1,"params"=>"","label"=>"Mortality Rate (zeroth order)","visible"=>1, "readonly"=>0, "width"=>8),
              "age_resolution"=>array("type"=>3,"params"=>"year|Years,month|Months,day|Days,hour|Hours,minute|Minutes,second|Seconds:arid:arname::0","label"=>"Units of Age","visible"=>1, "readonly"=>0, "width"=>6),
              "max_age"=>array("type"=>1,"params"=>"","label"=>"Maximum Age (-1 if no limit)","visible"=>1, "readonly"=>0, "width"=>8),
              "min_birth_age"=>array("type"=>1,"params"=>"","label"=>"Minimum Age For Reproduction (-1 if no limit)","visible"=>1, "readonly"=>0, "width"=>8),
              "max_birth_age"=>array("type"=>1,"params"=>"","label"=>"Maximum Age For Reproduction (-1 if no limit)","visible"=>1, "readonly"=>0, "width"=>8),
              "birth_frequency"=>array("type"=>1,"params"=>"","label"=>"Age Intervals Between Reproductive Cycle","visible"=>1, "readonly"=>0, "width"=>8),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>24),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "geomx"=>array("type"=>1, "params"=>"", "label"=>"X coordinate (lon)","visible"=>1, "readonly"=>'0', "width"=>24),
              "geomy"=>array("type"=>1, "params"=>"", "label"=>"Y coordinate (lat)","visible"=>1, "readonly"=>'0', "width"=>24),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "reportObject"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Report Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "logfile"=>array("type"=>1,"params"=>"","label"=>"File Name","visible"=>1, "readonly"=>0, "width"=>32),
              #"goutdir"=>array("type"=>1,"params"=>"","label"=>"Graphics Output Directory (full path for writing)","visible"=>1, "readonly"=>0, "width"=>64),
              #"gouturl"=>array("type"=>1,"params"=>"","label"=>"Graphics Output URL (for link)","visible"=>1, "readonly"=>0, "width"=>64),
              "numform"=>array("type"=>1, "params"=>"", "label"=>"Numeric Output Format (sprintf notation)","visible"=>1, "readonly"=>'0', "width"=>24),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "geomx"=>array("type"=>1, "params"=>"", "label"=>"X coordinate (lon)","visible"=>1, "readonly"=>'0', "width"=>24),
              "geomy"=>array("type"=>1, "params"=>"", "label"=>"Y coordinate (lat)","visible"=>1, "readonly"=>'0', "width"=>24),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24)
           )
        ),
      "graphObject"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Graph Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              #"goutdir"=>array("type"=>1,"params"=>"","label"=>"Graphics Output Directory (full path for writing)","visible"=>1, "readonly"=>0, "width"=>64),
              #"gouturl"=>array("type"=>1,"params"=>"","label"=>"Graphics Output URL (for link)","visible"=>1, "readonly"=>0, "width"=>64),
              "gwidth"=>array("type"=>1,"params"=>"","label"=>"Graphic Width (pixels)","visible"=>1, "readonly"=>0, "width"=>6),
              "gheight"=>array("type"=>1,"params"=>"","label"=>"Graphic Height (pixels)","visible"=>1, "readonly"=>0, "width"=>6),
              "title"=>array("type"=>1,"params"=>"","label"=>"Graph Title","visible"=>1, "readonly"=>0, "width"=>32),
              "xlabel"=>array("type"=>1,"params"=>"","label"=>"X-axis Label","visible"=>1, "readonly"=>0, "width"=>32),
              "x_interval"=>array("type"=>1,"params"=>"","label"=>"Ticks Between Labels on X-axis","visible"=>1, "readonly"=>0, "width"=>32),
              "ylabel"=>array("type"=>1,"params"=>"","label"=>"Y-axis Label","visible"=>1, "readonly"=>0, "width"=>32),
              "y2label"=>array("type"=>1,"params"=>"","label"=>"Y2-axis Label (if multiple axis)","visible"=>1, "readonly"=>0, "width"=>32),
              "normalize"=>array("type"=>3,"params"=>"0|False,1|True:nid:nname::0","label"=>"Normalize on Max Value? ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:cid:cname::0","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "logfile"=>array("type"=>1,"params"=>"","label"=>"Export Data to File Name: ","visible"=>1, "readonly"=>0, "width"=>6),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "restrictdates"=>array("type"=>3,"params"=>"0|False,1|True:rdid:rdname::0","label"=>"Restrict Dates ","visible"=>1, "readonly"=>0, "width"=>6),
              "startdate"=>array("type"=>1,"params"=>"","label"=>"Start Date","visible"=>1, "readonly"=>0, "width"=>32),
              "enddate"=>array("type"=>1,"params"=>"","label"=>"End Date","visible"=>1, "readonly"=>0, "width"=>32),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "cascadedebug"=>array("type"=>3,"params"=>"0|False,1|True:smid:smname::0","label"=>"Propagate debug mode to child objects? ","visible"=>1, "readonly"=>0, "width"=>6),
              "scale"=>array("type"=>1,"params"=>"","label"=>"Graph Scale (intlin, linlin, loglin, etc.)","visible"=>1, "readonly"=>0, "width"=>32),
              "labelangle"=>array("type"=>1,"params"=>"","label"=>"X Label Angle (0-360)","visible"=>1, "readonly"=>0, "width"=>32),
              "graphtype"=>array("type"=>1,"params"=>"","label"=>"Graph Type (line, bar)","visible"=>1, "readonly"=>0, "width"=>32),
              "forceyscale"=>array("type"=>3,"params"=>"0|False,1|True:fysid:fysname::0","label"=>"Use Preset Y-scale ","visible"=>1, "readonly"=>0, "width"=>6),
              "ymin"=>array("type"=>1,"params"=>"","label"=>"Y-Minimum","visible"=>1, "readonly"=>0, "width"=>32),
              "ymax"=>array("type"=>1,"params"=>"","label"=>"Y-Maximum","visible"=>1, "readonly"=>0, "width"=>32),
              "geomx"=>array("type"=>1, "params"=>"", "label"=>"X coordinate (lon)","visible"=>1, "readonly"=>'0', "width"=>24),
              "geomy"=>array("type"=>1, "params"=>"", "label"=>"Y coordinate (lat)","visible"=>1, "readonly"=>'0', "width"=>24),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24)
           )
        ),
      "flowDurationGraph"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Graph Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              #"goutdir"=>array("type"=>1,"params"=>"","label"=>"Graphics Output Directory (full path for writing)","visible"=>1, "readonly"=>0, "width"=>64),
              #"gouturl"=>array("type"=>1,"params"=>"","label"=>"Graphics Output URL (for link)","visible"=>1, "readonly"=>0, "width"=>64),
              "normalize"=>array("type"=>3,"params"=>"0|False,1|True:nid:nname::0","label"=>"Normalize on Max Value? ","visible"=>1, "readonly"=>0, "width"=>6),
              "flowstat"=>array("type"=>3,"params"=>"0|Exceedance %,1|Recurrence Interval:fsid:fsname::0","label"=>"Sort Values By: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gwidth"=>array("type"=>1,"params"=>"","label"=>"Graphic Width (pixels)","visible"=>1, "readonly"=>0, "width"=>6),
              "gheight"=>array("type"=>1,"params"=>"","label"=>"Graphic Height (pixels)","visible"=>1, "readonly"=>0, "width"=>6),
              "title"=>array("type"=>1,"params"=>"","label"=>"Graph Title","visible"=>1, "readonly"=>0, "width"=>32),
              "xlabel"=>array("type"=>1,"params"=>"","label"=>"X-axis Label","visible"=>1, "readonly"=>0, "width"=>32),
              "ylabel"=>array("type"=>1,"params"=>"","label"=>"Y-axis Label","visible"=>1, "readonly"=>0, "width"=>32),
              "y2label"=>array("type"=>1,"params"=>"","label"=>"Y2-axis Label (if multiple axis)","visible"=>1, "readonly"=>0, "width"=>32),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "scale"=>array("type"=>1,"params"=>"","label"=>"Graph Scale (intlin, linlin, loglin, etc.)","visible"=>1, "readonly"=>0, "width"=>32),
              "labelangle"=>array("type"=>1,"params"=>"","label"=>"X Label Angle (0-360)","visible"=>1, "readonly"=>0, "width"=>32),
              "graphtype"=>array("type"=>1,"params"=>"","label"=>"Graph Type (line, bar)","visible"=>1, "readonly"=>0, "width"=>32),
              "geomx"=>array("type"=>1, "params"=>"", "label"=>"X coordinate (lon)","visible"=>1, "readonly"=>'0', "width"=>24),
              "geomy"=>array("type"=>1, "params"=>"", "label"=>"Y coordinate (lat)","visible"=>1, "readonly"=>'0', "width"=>24),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24)
           )
        ),
      "giniGraph"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Graph Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "gwidth"=>array("type"=>1,"params"=>"","label"=>"Graphic Width (pixels)","visible"=>1, "readonly"=>0, "width"=>6),
              "gheight"=>array("type"=>1,"params"=>"","label"=>"Graphic Height (pixels)","visible"=>1, "readonly"=>0, "width"=>6),
              "title"=>array("type"=>1,"params"=>"","label"=>"Graph Title","visible"=>1, "readonly"=>0, "width"=>32),
              "xlabel"=>array("type"=>1,"params"=>"","label"=>"X-axis Label","visible"=>1, "readonly"=>0, "width"=>32),
              "ylabel"=>array("type"=>1,"params"=>"","label"=>"Y-axis Label","visible"=>1, "readonly"=>0, "width"=>32),
              "y2label"=>array("type"=>1,"params"=>"","label"=>"Y2-axis Label (if multiple axis)","visible"=>1, "readonly"=>0, "width"=>32),
              "scale"=>array("type"=>1,"params"=>"","label"=>"Graph Scale (intlin, linlin, loglin, etc.)","visible"=>1, "readonly"=>0, "width"=>32),
              "labelangle"=>array("type"=>1,"params"=>"","label"=>"X Label Angle (0-360)","visible"=>1, "readonly"=>0, "width"=>32),
              "graphtype"=>array("type"=>1,"params"=>"","label"=>"Graph Type (line, bar)","visible"=>1, "readonly"=>0, "width"=>32),
              "splitmedian"=>array("type"=>3,"params"=>"0|False,1|True:smid:smname::0","label"=>"Evaluate Above and Below Median Seperately? ","visible"=>1, "readonly"=>0, "width"=>6),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "geomx"=>array("type"=>1, "params"=>"", "label"=>"X coordinate (lon)","visible"=>1, "readonly"=>'0', "width"=>24),
              "geomy"=>array("type"=>1, "params"=>"", "label"=>"Y coordinate (lat)","visible"=>1, "readonly"=>'0', "width"=>24),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24)
           )
        ),
      "graphComponent"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Name (must be unique)","visible"=>1, "readonly"=>0, "width"=>32),
              "graphtype"=>array("type"=>3,"params"=>"line|Line,bar|Bar:gtid:gtname::0","label"=>"Graph Type","visible"=>1, "readonly"=>0, "width"=>6),
              "xcol"=>array("type"=>3,"params"=>"name|name:xid:xname::0","label"=>"X Column","visible"=>1, "readonly"=>0, "width"=>32),
              "ycol"=>array("type"=>3,"params"=>"name|name:xid:xname::0","label"=>"Y Column","visible"=>1, "readonly"=>0, "width"=>32),
              "yaxis"=>array("type"=>3,"params"=>"1|1,2|2:smid:smname::0","label"=>"Y-axis (1 or 2) ","visible"=>1, "readonly"=>0, "width"=>6),
              "ylegend"=>array("type"=>1,"params"=>"","label"=>"Y axis Legend","visible"=>1, "readonly"=>0, "width"=>64),
              "color"=>array("type"=>3,"params"=>"black|Black,red|Red,green|Green,blue|Blue,orange|Orange,brown|Brown,thistle|Thistle,tan|Tan,springgreen|Spring Green:typeid:typename::0","label"=>"Color ","visible"=>1, "readonly"=>0, "width"=>64),
              "weight"=>array("type"=>3,"params"=>"1|1px,2|2px,3|3px,4|4px,5|5px,6|6px,7|7px,8|8px,9|9px,10|10px:wgtid:wgtname::0","label"=>"Weight ","visible"=>1, "readonly"=>0, "width"=>64),
              "sorted"=>array("type"=>3,"params"=>"0|False,1|true:sid:sname::0","label"=>"Sort Records Based on a column?","visible"=>1, "readonly"=>0, "width"=>24),
              "sortcol"=>array("type"=>1, "params"=>"", "label"=>"Column to Sort By (if sorted)","visible"=>1, "readonly"=>'0', "width"=>16),
              "disabled"=>array("type"=>3,"params"=>"0|False,1|True:smid:smname::0","label"=>"Disabled? ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "lookupObject"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Variable Name","visible"=>1, "readonly"=>0, "width"=>64),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "input"=>array("type"=>3,"params"=>"1|1:fid:fname::0","label"=>"Lookup Key","visible"=>1, "readonly"=>0, "width"=>32),
              #"input"=>array("type"=>1,"params"=>"","label"=>"Lookup Key Name","visible"=>1, "readonly"=>0, "width"=>24),
              "lutype"=>array("type"=>3,"params"=>"0|Exact Match,1|Interpolated,2|Stair Step:sid:sname::0","label"=>"Lookup Type","visible"=>1, "readonly"=>0, "width"=>24),
              "valtype"=>array("type"=>3,"params"=>"0|Numeric,1|Alphanumeric,2|Variable Reference:vtid:vtname::0","label"=>"Value Type","visible"=>1, "readonly"=>0, "width"=>24),
              'defval'=>array("type"=>1,"params"=>"","label"=>"Default Value (if no match)","visible"=>1, "readonly"=>0, "width"=>24),
              'nullvalue'=>array("type"=>1,"params"=>"","label"=>"NULL Value (if disabled due to date constraint)","visible"=>1, "readonly"=>0, "width"=>24),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>24),
              'startyear'=>array("type"=>1,"params"=>"","label"=>"Start Year","visible"=>1, "readonly"=>0, "width"=>24),
              'endyear'=>array("type"=>1,"params"=>"","label"=>"End Year","visible"=>1, "readonly"=>0, "width"=>24),
              'startmonth'=>array("type"=>1,"params"=>"","label"=>"Start Month","visible"=>1, "readonly"=>0, "width"=>24),
              'endmonth'=>array("type"=>1,"params"=>"","label"=>"End Month","visible"=>1, "readonly"=>0, "width"=>24),
              'startday'=>array("type"=>1,"params"=>"","label"=>"Start Day","visible"=>1, "readonly"=>0, "width"=>24),
              'endday'=>array("type"=>1,"params"=>"","label"=>"End Day","visible"=>1, "readonly"=>0, "width"=>24),
              'startweekday'=>array("type"=>1,"params"=>"","label"=>"Start Weekday ([1]Mon-[7]Sun)","visible"=>1, "readonly"=>0, "width"=>24),
              'endweekday'=>array("type"=>1,"params"=>"","label"=>"End Weekday ([1]Mon-[7]Sun)","visible"=>1, "readonly"=>0, "width"=>24),
              'starthour'=>array("type"=>1,"params"=>"","label"=>"Start Hour","visible"=>1, "readonly"=>0, "width"=>24),
              'endhour'=>array("type"=>1,"params"=>"","label"=>"End Hour","visible"=>1, "readonly"=>0, "width"=>24),
              'lucsv'=>array("type"=>1,"params"=>"","label"=>"Lookup Values (format = key1:value1, key2:value2, ...)","visible"=>1, "readonly"=>0, "width"=>64),
              "filepath"=>array("type"=>22,"params"=>"csv:txt","label"=>"Lookup Values from CSV File (over-rides values field)", "visible"=>1, "readonly"=>0, "width"=>80),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              #"geomx"=>array("type"=>1, "params"=>"", "label"=>"X coordinate (lon)","visible"=>1, "readonly"=>'0', "width"=>24),
              #"geomy"=>array("type"=>1, "params"=>"", "label"=>"Y coordinate (lat)","visible"=>1, "readonly"=>'0', "width"=>24)
           )
        ),
      "stockComponent"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Variable Name","visible"=>1, "readonly"=>0, "width"=>64),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "inflows"=>array("type"=>4,"params"=>"1|1:ifid:ifname::0","label"=>"Inflows","visible"=>1, "readonly"=>0, "width"=>32),
              "outflows"=>array("type"=>4,"params"=>"1|1:ofid:ofname::0","label"=>"Outflows","visible"=>1, "readonly"=>0, "width"=>32)
           )
        ),
      "blankShell"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Element Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>24),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "genericLandSurface"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Element Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "genericDwelling"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Element Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "occupants"=>array("type"=>1,"params"=>"","label"=>"# of Full-time Inhabitants","visible"=>1, "readonly"=>0, "width"=>6),
              "occupants"=>array("type"=>1,"params"=>"","label"=>"# of Full-time Inhabitants","visible"=>1, "readonly"=>0, "width"=>6),
              "matrix"=>array("type"=>27,"params"=>"6:4:2:8:1:1,0,1,0,1,0","label"=>"Use Types","visible"=>1, "readonly"=>0, "width"=>6),
              //"log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              //"cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              //"debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              //"groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              //"operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              //"gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              //"pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              //"the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              //"cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "hydroTank"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "maxcapacity"=>array("type"=>1,"params"=>"","label"=>"Volume at Full Storage (Gallons)","visible"=>1, "readonly"=>0, "width"=>12),
              "initstorage"=>array("type"=>1,"params"=>"","label"=>"Initial Storage (Gallons)","visible"=>1, "readonly"=>0, "width"=>12),
              "Qin"=>array("type"=>3,"params"=>"0|False,1|True:qid:qname::0:","label"=>"Inflow (gpd)","visible"=>1, "readonly"=>0, "width"=>12),
              "precip"=>array("type"=>3,"params"=>"0|False,1|True:pid:pname::0:","label"=>"Precip (in/day)","visible"=>1, "readonly"=>0, "width"=>12),
              "pan_evap"=>array("type"=>3,"params"=>"0|False,1|True:eid:ename::0:","label"=>"Pan Evap (in/day)","visible"=>1, "readonly"=>0, "width"=>12),
              "demand"=>array("type"=>3,"params"=>"0|False,1|True:did:dname::0:","label"=>"Demand (GPD)","visible"=>1, "readonly"=>0, "width"=>12),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "wsp_waterUser"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Element Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "waterusetype"=>array("type"=>3, "params"=>"AGR|Agriculture,COM|Commercial,PF|Fossil Power,PH|Hydro Power,IRR|Irrigation,MAN|Manufacturing,MIN|Mining,PN|Nuclear Power,OTH|Other,PWS|Public Water Supply:typid:typname::0", "label"=>"Use Type","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>1),
              "wdtype"=>array("type"=>3, "params"=>"GW|Groundwater,SW|Surface Water,TW|Transferred Water:wtypid:wtypname::0", "label"=>"Source Type","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>1),
              "max_wd_annual"=>array("type"=>1,"params"=>"","label"=>"Max Annual Withdrawal allowed (MGY)","visible"=>1, "readonly"=>0, "width"=>32),
              "withdrawal_enabled"=>array("type"=>3,"params"=>"0|False,1|True:edid:edname::0:","label"=>"Enable Withdrawal? ","visible"=>1, "readonly"=>0, "width"=>6),
              "discharge_enabled"=>array("type"=>3,"params"=>"0|False,1|True:edid:edname::0:","label"=>"Enable Discharge? ","visible"=>1, "readonly"=>0, "width"=>6),
              "id1"=>array("type"=>1,"params"=>"","label"=>"User ID","visible"=>1, "readonly"=>0, "width"=>32),
              "id2"=>array("type"=>1,"params"=>"","label"=>"MPID","visible"=>1, "readonly"=>0, "width"=>32),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>24),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "wsp_vpdesvwuds"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Element Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "id1"=>array("type"=>1,"params"=>"","label"=>"User ID","visible"=>1, "readonly"=>0, "width"=>32),
              "id2"=>array("type"=>1,"params"=>"","label"=>"MPID","visible"=>1, "readonly"=>0, "width"=>32),
              "waterusetype"=>array("type"=>3, "params"=>"AGR|Agriculture,COM|Commercial,PF|Fossil Power,PH|Hydro Power,IRR|Irrigation,MAN|Manufacturing,MIN|Mining,PN|Nuclear Power,OTH|Other,PWS|Public Water Supply:typid:typname::0", "label"=>"Use Type","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>1),
              "action"=>array("type"=>3, "params"=>"WL|Withdrawal,SR|System Release,RL|Release:typid:typname::0", "label"=>"Use Type","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>1),
              "wdtype"=>array("type"=>3, "params"=>"GW|Groundwater,SW|Surface Water,TW|Transferred Water:wtypid:wtypname::0", "label"=>"Source Type","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>1),
              "max_wd_annual"=>array("type"=>1,"params"=>"","label"=>"Max Annual Withdrawal allowed (MGY)","visible"=>1, "readonly"=>0, "width"=>32),
              "max_wd_daily"=>array("type"=>1,"params"=>"","label"=>"Max Daily Withdrawal allowed (MGD)","visible"=>1, "readonly"=>0, "width"=>32),
              "withdrawal_enabled"=>array("type"=>3,"params"=>"0|False,1|True:edid:edname::0:","label"=>"Enable Withdrawal? ","visible"=>1, "readonly"=>0, "width"=>6),
              "discharge_enabled"=>array("type"=>3,"params"=>"0|False,1|True:edid:edname::0:","label"=>"Enable Discharge? ","visible"=>1, "readonly"=>0, "width"=>6),
              "vpdes_permitno"=>array("type"=>1,"params"=>"","label"=>"VPDES Permit #","visible"=>1, "readonly"=>0, "width"=>32),
              "patchnull"=>array("type"=>3,"params"=>"none|None,vpdes|VPDES Only,vwuds|VWUDS Only,all|All:lid:lname::0:","label"=>"Estimate Null Records? ","visible"=>1, "readonly"=>0, "width"=>6),
              "current_years"=>array("type"=>1,"params"=>"","label"=>"Years for 'current' mean values (csv)","visible"=>1, "readonly"=>0, "width"=>32),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>24),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "cova_watershedContainerLink"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column", "showPlusMinus"=>1, "formName"=>'phone'),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Field Name","visible"=>1, "readonly"=>0, "width"=>24),
              "charlength"=>array("type"=>1,"params"=>"","label"=>"Max Length in Characters ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "set_parent_geom"=>array("type"=>3,"params"=>"0|False,1|True:spgid:spgname::0","label"=>"Over-ride Object Geometry? ","visible"=>1, "readonly"=>0, "width"=>6),
              "parent_geom_type"=>array("type"=>3,"params"=>"0|Point,1|Polygon:spgid:spgname::0","label"=>"Type of Geom to Set on Parent ","visible"=>1, "readonly"=>0, "width"=>6),
              "value"=>array("type"=>1, "params"=>"1", "label"=>"Value","visible"=>1, "readonly"=>'0', "default"=>0, "width"=>24)
           )
        ),
      "USGSArima"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Output Variable Name","visible"=>1, "readonly"=>0, "width"=>12),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>4),
              "q_var"=>array("type"=>3,"params"=>"0|False,1|True:ecvid:ecvname::0","label"=>"Conditional Flow-By Var ","visible"=>1, "readonly"=>0, "width"=>6),
              "num_vars"=>array("type"=>1,"params"=>"1","label"=>"Number of Days in Past (t - 1-n) ","visible"=>1, "readonly"=>0, "width"=>6),
              "var_prefix"=>array("type"=>1,"params"=>"1","label"=>"Flow Variable Prefix (Q1 ... Qn) ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "arima_eqn"=>array("type"=>1,"params"=>"","label"=>"Equation","visible"=>1, "readonly"=>0, "width"=>48),
              "init_vals"=>array("type"=>1,"params"=>"","label"=>"Initial Value","visible"=>1, "readonly"=>0, "width"=>16)
           )
        ),
      "USGSRecharge"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Output Variable Name","visible"=>1, "readonly"=>0, "width"=>12),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>4),
              
              "q_var"=>array("type"=>3,"params"=>"0|False,1|True:ecvid:ecvname::0","label"=>"Flow Variable ","visible"=>1, "readonly"=>0, "width"=>6),
              
              "r_start_day"=>array("type"=>3,"params"=>"jdate366:jday:thisdate:jday:0::1","label"=>"NOAA Climate Divisions","visible"=>1, "readonly"=>0, "width"=>12,"label"=>"Recharge Start Day","visible"=>1, "readonly"=>0, "width"=>12),
              "r_end_day"=>array("type"=>3,"params"=>"jdate366:jday:thisdate:jday:0::1","label"=>"NOAA Climate Divisions","visible"=>1, "readonly"=>0, "width"=>12,"label"=>"Recharge End Day","visible"=>1, "readonly"=>0, "width"=>12),
              
              "b0"=>array("type"=>1,"params"=>"1","label"=>"Coefficient b0 () ","visible"=>1, "readonly"=>0, "width"=>6),
              "b1"=>array("type"=>1,"params"=>"1","label"=>"Coefficient b1 ","visible"=>1, "readonly"=>0, "width"=>6),
              
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
           )
        ),
      "VTFungusRiskModel"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Output Variable Name","visible"=>1, "readonly"=>0, "width"=>12),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>4),
              
              "d_var"=>array("type"=>3,"params"=>"0|False,1|True:ecvid:ecvname::0","label"=>"Dryness Input ","visible"=>1, "readonly"=>0, "width"=>6),
              "t_var"=>array("type"=>3,"params"=>"0|False,1|True:ecvid:ecvname::0","label"=>"Temperature Input ","visible"=>1, "readonly"=>0, "width"=>6),
              "w_var"=>array("type"=>3,"params"=>"0|False,1|True:ecvid:ecvname::0","label"=>"Wetness Input ","visible"=>1, "readonly"=>0, "width"=>6),
              "dry_reset"=>array("type"=>1,"params"=>"1","label"=>"Dryness Reset Duration (min)","visible"=>1, "readonly"=>0, "width"=>6),
              
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
           )
        ),
      "wsp_flowby"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Variable Name","visible"=>1, "readonly"=>0, "width"=>12),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>4),
              "flowby_eqn"=>array("type"=>1,"params"=>"","label"=>"Flow By","visible"=>1, "readonly"=>0, "width"=>16),
              "cfb_var"=>array("type"=>3,"params"=>"0|False,1|True:ecvid:ecvname::0","label"=>"Conditional Flow-By Var ","visible"=>1, "readonly"=>0, "width"=>6),
              "enable_cfb"=>array("type"=>26,"params"=>"1","label"=>"Enable Conditional Flow-By ","visible"=>1, "readonly"=>0, "width"=>6),
              "cfb_condition"=>array("type"=>3,"params"=>"lt|<,gt|>:ecfbid:ecfbname::0","label"=>"Flow-By is ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "wsp_PopBasedProjection_VAWC"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Variable Name","visible"=>1, "readonly"=>0, "width"=>12),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>4),
              "fips"=>array("type"=>1,"params"=>"","label"=>"FIPS Codes (comma separated)","visible"=>1, "readonly"=>0, "width"=>16),
              "yearvar"=>array("type"=>3,"params"=>"0|False,1|True:ecvid:ecvname::0","label"=>"Year Variable ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "dynamicWaterUsers"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Variable Name","visible"=>1, "readonly"=>0, "width"=>12),
              "description"=>array("type"=>1,"params"=>"","label"=>"Variable Name","visible"=>1, "readonly"=>0, "width"=>48),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>4),
              "scenarioid"=>array("type"=>1,"params"=>"","label"=>"Scenario ID","visible"=>1, "readonly"=>0, "width"=>4),
              "custom1"=>array("type"=>1,"params"=>"","label"=>"Object Category","visible"=>1, "readonly"=>0, "width"=>16),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "refresh"=>array("type"=>3,"params"=>"0|Never,1|On Run,2|Always:smid:smname::0","label"=>"Refresh Level ","visible"=>1, "readonly"=>0, "width"=>6),
              
              "threshold_var"=>array("type"=>3, "params"=>"na|None,max_wd_annual|Max Annual,current_mgy|Est. Current Annual,max_wd_daily|Max Daily,max_wd_monthly|Max Monthly:toid:toname::0", "label"=>"Threshold Variable","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>16),
              "threshold"=>array("type"=>1, "params"=>"", "label"=>"Threshold (MGY)","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>16),
              "threshold_operator"=>array("type"=>3, "params"=>"na|None,lt|<,gt|>,le|<=,ge|>=:toid:toname::0", "label"=>"Threshold Type","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>1),
              /*
              // single select version
              "action"=>array("type"=>3, "params"=>"|Any,WL|Withdrawal,SR|System Release,RL|Release:typid:typname::0", "label"=>"Use Type","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>1),
              "waterusetype"=>array("type"=>3, "params"=>"|Any,AGR|Agriculture,COM|Commercial,PF|Fossil Power,PH|Hydro Power,IRR|Irrigation,MAN|Manufacturing,MIN|Mining,PN|Nuclear Power,OTH|Other,PWS|Public Water Supply:typid:typname::0", "label"=>"Use Type","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>1),
              "wdtype"=>array("type"=>3, "params"=>"|Any,GW|Groundwater,SW|Surface Water,TW|Transferred Water:wtypid:wtypname::0", "label"=>"Source Type","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>1)
               */
             //multiselect version
              "action"=>array("type"=>14, "params"=>"action::|Any,WL|Withdrawal,SR|System Release,RL|Release:::actid:actionname:0:3", "label"=>"Use Type","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>1),
              "waterusetype"=>array("type"=>14, "params"=>"waterusetype::|Any,AGR|Agriculture,COM|Commercial,PF|Fossil Power,PH|Hydro Power,IRR|Irrigation,MAN|Manufacturing,MIN|Mining,PN|Nuclear Power,OTH|Other,PWS|Public Water Supply:::typid:typname:0:3", "label"=>"Use Type","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>1),
              "wdtype"=>array("type"=>14, "params"=>"wdtype::|Any,GW|Groundwater,SW|Surface Water,TW|Transferred Water:::typename:typename:0:3", "label"=>"Source Type","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>1)
            
              
           )
        ),
      "vwudsUserGroup"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Variable Name","visible"=>1, "readonly"=>0, "width"=>12),
              "description"=>array("type"=>1,"params"=>"","label"=>"Variable Name","visible"=>1, "readonly"=>0, "width"=>24),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>4),
              "scenarioid"=>array("type"=>1,"params"=>"","label"=>"Scenario ID","visible"=>1, "readonly"=>0, "width"=>16),
              "userids"=>array("type"=>1,"params"=>"","label"=>"VWUDS UserID","visible"=>1, "readonly"=>0, "width"=>64),
              "mpids"=>array("type"=>1,"params"=>"","label"=>"VWUDS UserID","visible"=>1, "readonly"=>0, "width"=>16),
              
              /*
              "action"=>array("type"=>3, "params"=>"|Any,WL|Withdrawal,SR|System Release,RL|Release:typid:typname::0", "label"=>"Use Type","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>1),
              "waterusetype"=>array("type"=>3, "params"=>"|Any,AGR|Agriculture,COM|Commercial,PF|Fossil Power,PH|Hydro Power,IRR|Irrigation,MAN|Manufacturing,MIN|Mining,PN|Nuclear Power,OTH|Other,PWS|Public Water Supply:typid:typname::0", "label"=>"Use Type","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>1),
              "wdtype"=>array("type"=>3, "params"=>"|Any,GW|Groundwater,SW|Surface Water,TW|Transferred Water:wtypid:wtypname::0", "label"=>"Source Type","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>1),
              */
             //multiselect version
              "action"=>array("type"=>14, "params"=>"action::|Any,WL|Withdrawal,SR|System Release,RL|Release:::actid:actionname:0:3", "label"=>"Use Type","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>1),
              "waterusetype"=>array("type"=>14, "params"=>"waterusetype::|Any,AGR|Agriculture,COM|Commercial,PF|Fossil Power,PH|Hydro Power,IRR|Irrigation,MAN|Manufacturing,MIN|Mining,PN|Nuclear Power,OTH|Other,PWS|Public Water Supply:::typid:typname:0:3", "label"=>"Use Type","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>1),
              "wdtype"=>array("type"=>14, "params"=>"wdtype::|Any,GW|Groundwater,SW|Surface Water,TW|Transferred Water:::typename:typename:0:3", "label"=>"Source Type","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>1),
              
              
              "threshold"=>array("type"=>1, "params"=>"", "label"=>"Withdrawal Threshold (MGY)","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>16),
              "threshold_operator"=>array("type"=>3, "params"=>"na|None,lt|<,gt|>,le|<=,ge|>=:toid:toname::0", "label"=>"Threshold Type","visible"=>1, "readonly"=>'0', "width"=>24, "tab"=>'other_info', "maxlength"=>1),
              
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "refresh"=>array("type"=>3,"params"=>"0|Never,1|On Run,2|Always:smid:smname::0","label"=>"Refresh Level ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "wsp_1tierflowby"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Variable Name","visible"=>1, "readonly"=>0, "width"=>12),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>4),
              "matrix"=>array("type"=>1,"params"=>"","label"=>"Matrix ","visible"=>1, "readonly"=>0, "width"=>64),
              "enable_cfb"=>array("type"=>26,"params"=>"1","label"=>"Enable Conditional Flow-By ","visible"=>1, "readonly"=>0, "width"=>6),
              "cfb_condition"=>array("type"=>3,"params"=>"lt|<,gt|>:ecfbid:ecfbname::0","label"=>"Flow-By is ","visible"=>1, "readonly"=>0, "width"=>6),
              "delimiter"=>array("type"=>3,"params"=>"0|Comma,1|Tab,2|pipe,3|Space:dlid:dlname::0:","label"=>"Delimiter ","visible"=>1, "readonly"=>0, "width"=>6),
              "cfb_var"=>array("type"=>3,"params"=>"0|False,1|True:ecvid:ecvname::0","label"=>"Conditional Flow-By Var ","visible"=>1, "readonly"=>0, "width"=>6),
              "tier_var"=>array("type"=>3,"params"=>"0|False,1|True:tvid:tvname::0","label"=>"Tier Flow-By Var ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "wsp_conservation"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Variable Name","visible"=>1, "readonly"=>0, "width"=>12),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>4),
              "cc_watch"=>array("type"=>1,"params"=>"","label"=>"Drought Watch Reduction","visible"=>1, "readonly"=>0, "width"=>4),
              "cc_warning"=>array("type"=>1,"params"=>"","label"=>"Drought Warning Reduction","visible"=>1, "readonly"=>0, "width"=>4),
              "cc_emergency"=>array("type"=>1,"params"=>"","label"=>"Drought Emergency Reduction","visible"=>1, "readonly"=>0, "width"=>4),
              "status_var"=>array("type"=>3,"params"=>"0|False,1|True:svid:svname::0","label"=>"Status Variable ","visible"=>1, "readonly"=>0, "width"=>6),
              "custom_cons_var"=>array("type"=>3,"params"=>"0|False,1|True:ccvid:ccvname::0","label"=>"Custom Conservation Variable ","visible"=>1, "readonly"=>0, "width"=>6),
              "enable_conservation"=>array("type"=>26,"params"=>"1","label"=>"Enable Conservation ","visible"=>1, "readonly"=>0, "width"=>6),
              "custom_conservation"=>array("type"=>26,"params"=>"1","label"=>"Use Custom Conservation ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "wsp_demand"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Variable Name","visible"=>1, "readonly"=>0, "width"=>12),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>4),
              "demand_eqn"=>array("type"=>1,"params"=>"","label"=>"Demand","visible"=>1, "readonly"=>0, "width"=>24),
              "cc_watch"=>array("type"=>1,"params"=>"","label"=>"Drought Watch Reduction","visible"=>1, "readonly"=>0, "width"=>4),
              "cc_warning"=>array("type"=>1,"params"=>"","label"=>"Drought Warning Reduction","visible"=>1, "readonly"=>0, "width"=>4),
              "cc_emergency"=>array("type"=>1,"params"=>"","label"=>"Drought Emergency Reduction","visible"=>1, "readonly"=>0, "width"=>4),
              "status_var"=>array("type"=>3,"params"=>"0|False,1|True:svid:svname::0","label"=>"Status Variable ","visible"=>1, "readonly"=>0, "width"=>6),
              "custom_cons_var"=>array("type"=>3,"params"=>"0|False,1|True:ccvid:ccvname::0","label"=>"Custom Conservation Variable ","visible"=>1, "readonly"=>0, "width"=>6),
              "enable_conservation"=>array("type"=>3,"params"=>"disabled|Disabled,internal|Enabled,custom|Custom:eccvid:eccvname::0","label"=>"Enable Conservation ","visible"=>1, "readonly"=>0, "width"=>6),
              "custom_conservation"=>array("type"=>26,"params"=>"1","label"=>"Use Custom Conservation ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "wsp_LUBasedProjection"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Element Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy (if order cannot be determined)","visible"=>1, "readonly"=>0, "width"=>24),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24)
           )
        ),
      "dataConnectionObject"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "conntype"=>array("type"=>3,"params"=>"1|PostgreSQL,2|ODBC,3|Oracle,4|WFS,5|Shared:ctid:ctname::0","label"=>"Connection Type","visible"=>1, "readonly"=>0, "width"=>6),
              "host"=>array("type"=>1,"params"=>"","label"=>"Connection Host","visible"=>1, "readonly"=>0, "width"=>6),
              "username"=>array("type"=>1,"params"=>"","label"=>"User Name","visible"=>1, "readonly"=>0, "width"=>32),
              "password"=>array("type"=>21,"params"=>"","label"=>"Password","visible"=>1, "readonly"=>0, "width"=>32),
              "dbname"=>array("type"=>1,"params"=>"","label"=>"Database Name","visible"=>1, "readonly"=>0, "width"=>32),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "single_datecol"=>array("type"=>3,"params"=>"0|False,1|True:sdcid:sdcname::0","label"=>"Use a Single Column For Date? ","visible"=>1, "readonly"=>0, "width"=>6),
              "datecolumn"=>array("type"=>3,"params"=>"0|False,1|True:did:dname::0","label"=>"Column With Date/Time","visible"=>1, "readonly"=>0, "width"=>32),
              "yearcolumn"=>array("type"=>3,"params"=>"0|False,1|True:dyid:dyname::0","label"=>"Column With Year","visible"=>1, "readonly"=>0, "width"=>32),
              "monthcolumn"=>array("type"=>3,"params"=>"0|False,1|True:dmid:dmname::0","label"=>"Column With Month","visible"=>1, "readonly"=>0, "width"=>32),
              "daycolumn"=>array("type"=>3,"params"=>"0|False,1|True:ddid:ddname::0","label"=>"Column With Day","visible"=>1, "readonly"=>0, "width"=>32),
              "restrict_spatial"=>array("type"=>3,"params"=>"0|False,1|True:rsid:rsname::0","label"=>"Restrict to spatial bounds? ","visible"=>1, "readonly"=>0, "width"=>6),
              "lon_col"=>array("type"=>3,"params"=>"0|False,1|True:loid:loname::0","label"=>"Column With Longitude","visible"=>1, "readonly"=>0, "width"=>32),
              "lat_col"=>array("type"=>3,"params"=>"0|False,1|True:laid:laname::0","label"=>"Column With Latitude","visible"=>1, "readonly"=>0, "width"=>32),
              "intmethod"=>array("type"=>3,"params"=>"1|Previous Value,2|Next Value,3|Mean,4|Mininum,5|Maximum,6|Sum:imid:imname::0:","label"=>"Data Interpolation Method","visible"=>1, "readonly"=>0, "width"=>12),
              "sql_query"=>array("type"=>1,"params"=>"8","label"=>"SQL Query ","visible"=>1, "readonly"=>0, "width"=>64),
              "cache_ts"=>array("type"=>3,"params"=>"0|False,1|True:cid:cname::0","label"=>"Cache Time Series data in external file? ","visible"=>1, "readonly"=>0, "width"=>6),
              "force_refresh"=>array("type"=>3,"params"=>"0|False,1|True:frid:frname::0","label"=>"Force Refresh of cached data? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "geomx"=>array("type"=>1, "params"=>"", "label"=>"X coordinate (lon)","visible"=>0, "readonly"=>'0', "width"=>24),
              "geomy"=>array("type"=>1, "params"=>"", "label"=>"Y coordinate (lat)","visible"=>0, "readonly"=>'0', "width"=>24),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "dataConnectionSubObject"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "conntype"=>array("type"=>3,"params"=>"1|PostgreSQL,2|ODBC,3|Oracle,4|WFS,5|Shared:ctid:ctname::0","label"=>"Connection Type","visible"=>1, "readonly"=>0, "width"=>6),
              "host"=>array("type"=>1,"params"=>"","label"=>"Connection Host","visible"=>1, "readonly"=>0, "width"=>6),
              "username"=>array("type"=>1,"params"=>"","label"=>"User Name","visible"=>1, "readonly"=>0, "width"=>32),
              "password"=>array("type"=>21,"params"=>"","label"=>"Password","visible"=>1, "readonly"=>0, "width"=>32),
              "dbname"=>array("type"=>1,"params"=>"","label"=>"Database Name","visible"=>1, "readonly"=>0, "width"=>32),
              "single_datecol"=>array("type"=>3,"params"=>"0|False,1|True:sdcid:sdcname::0","label"=>"Use a Single Column For Date? ","visible"=>1, "readonly"=>0, "width"=>6),
              "datecolumn"=>array("type"=>3,"params"=>"0|False,1|True:did:dname::0","label"=>"Column With Date/Time","visible"=>1, "readonly"=>0, "width"=>32),
              "yearcolumn"=>array("type"=>3,"params"=>"0|False,1|True:dyid:dyname::0","label"=>"Column With Year","visible"=>1, "readonly"=>0, "width"=>32),
              "monthcolumn"=>array("type"=>3,"params"=>"0|False,1|True:dmid:dmname::0","label"=>"Column With Month","visible"=>1, "readonly"=>0, "width"=>32),
              "daycolumn"=>array("type"=>3,"params"=>"0|False,1|True:ddid:ddname::0","label"=>"Column With Day","visible"=>1, "readonly"=>0, "width"=>32),
              "restrict_spatial"=>array("type"=>3,"params"=>"0|False,1|True:rsid:rsname::0","label"=>"Restrict to spatial bounds? ","visible"=>1, "readonly"=>0, "width"=>6),
              "lon_col"=>array("type"=>3,"params"=>"0|False,1|True:loid:loname::0","label"=>"Column With Longitude","visible"=>1, "readonly"=>0, "width"=>32),
              "lat_col"=>array("type"=>3,"params"=>"0|False,1|True:laid:laname::0","label"=>"Column With Latitude","visible"=>1, "readonly"=>0, "width"=>32),
              "intmethod"=>array("type"=>3,"params"=>"1|Previous Value,2|Next Value,3|Mean,4|Mininum,5|Maximum,6|Sum:imid:imname::0:","label"=>"Data Interpolation Method","visible"=>1, "readonly"=>0, "width"=>12),
              "sql_query"=>array("type"=>1,"params"=>"8","label"=>"SQL Query ","visible"=>1, "readonly"=>0, "width"=>64),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
           )
        ),
      "dataConnectionTransform"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column", "showlabels"=>0),
          "column info"=>array(
              "name"=>array("type"=>1, "params"=>"1", "label"=>"Variable Name","visible"=>1, "readonly"=>'0', "default"=>'', "width"=>16),
              "description"=>array("type"=>1, "params"=>"1", "label"=>"Description","visible"=>1, "readonly"=>'0', "default"=>'', "width"=>64),
              "col_name"=>array("type"=>3,"params"=>"name|name:qcid:qcname::0","label"=>"Column to Query","visible"=>1, "readonly"=>0, "width"=>12),
              "func"=>array("type"=>3,"params"=>"none|None,min|min(),max|max(),mean|mean(),sum|sum(),gini|gini(),count|count():fid:fname::0","label"=>"Function","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "withdrawalRuleObject"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column", "showlabels"=>0),
          "column info"=>array(
              "name"=>array("type"=>1, "params"=>"1", "label"=>"Name","visible"=>1, "readonly"=>'0', "default"=>'', "width"=>16),
              "description"=>array("type"=>1, "params"=>"1", "label"=>"Description","visible"=>1, "readonly"=>'0', "default"=>'', "width"=>64),
              "max_annual_mg"=>array("type"=>9,"params"=>"","label"=>"Maximum Annual Withdrawal (MG)","visible"=>1, "readonly"=>0, "width"=>12),
              "max_daily_mgd"=>array("type"=>9,"params"=>"","label"=>"Maximum Daily Withdrawal Rate (MGD)","visible"=>1, "readonly"=>0, "width"=>12),
              "max_instant_mgd"=>array("type"=>9,"params"=>"","label"=>"Maximum Instantaneous Withdrawal (MGD)","visible"=>1, "readonly"=>0, "width"=>12),
              "flowby_type"=>array("type"=>3,"params"=>"1|Simple Rate Flowby,2|Simple Percentage Flowby,3|Simple Tiered Rate,4|Simple Tiered Percent,5|Monthly Tiered Rate,6|Monthly Tiered Percent:fid:fname::0","label"=>"Flow-by Type","visible"=>1, "readonly"=>0, "width"=>6),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "noaaGriddedPrecip"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "conntype"=>array("type"=>3,"params"=>"1|PostgreSQL,2|ODBC,3|Oracle,4|WFS,5|Shared:ctid:ctname::0","label"=>"Connection Type","visible"=>1, "readonly"=>0, "width"=>6),
              "host"=>array("type"=>1,"params"=>"","label"=>"Connection Host","visible"=>1, "readonly"=>0, "width"=>6),
              "username"=>array("type"=>1,"params"=>"","label"=>"User Name","visible"=>1, "readonly"=>0, "width"=>32),
              "password"=>array("type"=>21,"params"=>"","label"=>"Password","visible"=>1, "readonly"=>0, "width"=>32),
              "dbname"=>array("type"=>1,"params"=>"","label"=>"Database Name","visible"=>1, "readonly"=>0, "width"=>32),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "single_datecol"=>array("type"=>3,"params"=>"0|False,1|True:sdcid:sdcname::0","label"=>"Use a Single Column For Date? ","visible"=>1, "readonly"=>0, "width"=>6),
              "datecolumn"=>array("type"=>3,"params"=>"0|False,1|True:did:dname::0","label"=>"Column With Date/Time","visible"=>1, "readonly"=>0, "width"=>32),
              "yearcolumn"=>array("type"=>3,"params"=>"0|False,1|True:dyid:dyname::0","label"=>"Column With Year","visible"=>1, "readonly"=>0, "width"=>32),
              "monthcolumn"=>array("type"=>3,"params"=>"0|False,1|True:dmid:dmname::0","label"=>"Column With Month","visible"=>1, "readonly"=>0, "width"=>32),
              "daycolumn"=>array("type"=>3,"params"=>"0|False,1|True:ddid:ddname::0","label"=>"Column With Day","visible"=>1, "readonly"=>0, "width"=>32),
              "restrict_spatial"=>array("type"=>3,"params"=>"0|False,1|True:rsid:rsname::0","label"=>"Restrict to spatial bounds? ","visible"=>1, "readonly"=>0, "width"=>6),
              "lon_col"=>array("type"=>3,"params"=>"0|False,1|True:loid:loname::0","label"=>"Column With Longitude","visible"=>1, "readonly"=>0, "width"=>32),
              "lat_col"=>array("type"=>3,"params"=>"0|False,1|True:laid:laname::0","label"=>"Column With Latitude","visible"=>1, "readonly"=>0, "width"=>32),
              "intmethod"=>array("type"=>3,"params"=>"1|Previous Value,2|Next Value,3|Mean,4|Mininum,5|Maximum,6|Sum:imid:imname::0:","label"=>"Data Interpolation Method","visible"=>1, "readonly"=>0, "width"=>12),
              "sql_query"=>array("type"=>1,"params"=>"8","label"=>"SQL Query ","visible"=>1, "readonly"=>0, "width"=>64),
              "cache_ts"=>array("type"=>3,"params"=>"0|False,1|True:cid:cname::0","label"=>"Cache Time Series data in external file? ","visible"=>1, "readonly"=>0, "width"=>6),
              "force_refresh"=>array("type"=>3,"params"=>"0|False,1|True:frid:frname::0","label"=>"Force Refresh of cached data? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "geomx"=>array("type"=>1, "params"=>"", "label"=>"X coordinate (lon)","visible"=>0, "readonly"=>'0', "width"=>24),
              "geomy"=>array("type"=>1, "params"=>"", "label"=>"Y coordinate (lat)","visible"=>0, "readonly"=>'0', "width"=>24),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "CBPDataConnection"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "scid"=>array("type"=>3,"params"=>"1|Version 5.18 - p5186,2|Version 5.2 - p52An,3|Version 5.2 - ICPRB,4|Version 5.3:scid:scname::0","label"=>"Model Scenario: ","visible"=>1, "readonly"=>0, "width"=>6),
              "id1"=>array("type"=>3,"params"=>"river|river,land|land,met|met:id1id:id1name::0","label"=>"Data Type","visible"=>1, "readonly"=>0, "width"=>32),
              "id2"=>array("type"=>1,"params"=>"","label"=>"River Segment (catcode2)","visible"=>1, "readonly"=>0, "width"=>32),
              "conntype"=>array("type"=>3,"params"=>"1|PostgreSQL,2|ODBC,3|Oracle,4|WFS,5|Shared:ctid:ctname::0","label"=>"Connection Type","visible"=>1, "readonly"=>0, "width"=>6),
              "host"=>array("type"=>1,"params"=>"","label"=>"Connection Host","visible"=>1, "readonly"=>0, "width"=>6),
              "username"=>array("type"=>1,"params"=>"","label"=>"User Name","visible"=>1, "readonly"=>0, "width"=>32),
              "password"=>array("type"=>21,"params"=>"","label"=>"Password","visible"=>1, "readonly"=>0, "width"=>32),
              "dbname"=>array("type"=>1,"params"=>"","label"=>"Database Name","visible"=>1, "readonly"=>0, "width"=>32),
              "cache_log"=>array("type"=>3,"params"=>"0|False,1|True:clid:clname::0:","label"=>"Store Run Data in Text File? ","visible"=>1, "readonly"=>0, "width"=>6),
              "max_memory_values"=>array("type"=>1, "params"=>"", "label"=>"Max. Values to Store in Memory (-1 is unlimited)","visible"=>1, "readonly"=>'0', "width"=>24),
              "single_datecol"=>array("type"=>3,"params"=>"0|False,1|True:sdcid:sdcname::0","label"=>"Use a Single Column For Date? ","visible"=>1, "readonly"=>0, "width"=>6),
              "datecolumn"=>array("type"=>3,"params"=>"0|False,1|True:did:dname::0","label"=>"Column With Date/Time","visible"=>1, "readonly"=>0, "width"=>32),
              "yearcolumn"=>array("type"=>3,"params"=>"0|False,1|True:dyid:dyname::0","label"=>"Column With Year","visible"=>1, "readonly"=>0, "width"=>32),
              "monthcolumn"=>array("type"=>3,"params"=>"0|False,1|True:dmid:dmname::0","label"=>"Column With Month","visible"=>1, "readonly"=>0, "width"=>32),
              "daycolumn"=>array("type"=>3,"params"=>"0|False,1|True:ddid:ddname::0","label"=>"Column With Day","visible"=>1, "readonly"=>0, "width"=>32),
              "restrict_spatial"=>array("type"=>3,"params"=>"0|False,1|True:rsid:rsname::0","label"=>"Restrict to spatial bounds? ","visible"=>1, "readonly"=>0, "width"=>6),
              "lon_col"=>array("type"=>3,"params"=>"0|False,1|True:loid:loname::0","label"=>"Column With Longitude","visible"=>1, "readonly"=>0, "width"=>32),
              "lat_col"=>array("type"=>3,"params"=>"0|False,1|True:laid:laname::0","label"=>"Column With Latitude","visible"=>1, "readonly"=>0, "width"=>32),
              "sql_query"=>array("type"=>1,"params"=>"8","label"=>"SQL Query ","visible"=>1, "readonly"=>0, "width"=>64),
              "cache_ts"=>array("type"=>3,"params"=>"0|False,1|True:cid:cname::0","label"=>"Cache Time Series data in external file? ","visible"=>1, "readonly"=>0, "width"=>6),
              "force_refresh"=>array("type"=>3,"params"=>"0|False,1|True:frid:frname::0","label"=>"Force Refresh of cached data? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "geomx"=>array("type"=>1, "params"=>"", "label"=>"X coordinate (lon)","visible"=>0, "readonly"=>'0', "width"=>24),
              "geomy"=>array("type"=>1, "params"=>"", "label"=>"Y coordinate (lat)","visible"=>0, "readonly"=>'0', "width"=>24),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "CBPLandDataConnection"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "scid"=>array("type"=>3,"params"=>"1|Version 5.18 - p5186,2|Version 5.2 - p52An,3|Version 5.2 - ICPRB,4|Version 5.3:scid:scname::0","label"=>"Model Scenario: ","visible"=>1, "readonly"=>0, "width"=>6),
              "id2"=>array("type"=>1,"params"=>"","label"=>"Land Segment (fipsab)","visible"=>1, "readonly"=>0, "width"=>32),
              "riverseg"=>array("type"=>1,"params"=>"","label"=>"River Segment (optional)","visible"=>1, "readonly"=>0, "width"=>32),
              "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "romode"=>array("type"=>3,"params"=>"merged|Merged,component|Component (suro-agwo-ifwo):roid:roname::0:","label"=>"Runoff Data Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "hspf_timestep"=>array("type"=>1, "params"=>"", "label"=>"HSPF Time-step (seconds)","visible"=>1, "readonly"=>'0', "width"=>24),
              "max_memory_values"=>array("type"=>1, "params"=>"", "label"=>"Max. Values to Store in Memory (-1 is unlimited)","visible"=>1, "readonly"=>'0', "width"=>24),
              "landuse_var"=>array("type"=>1, "params"=>"", "label"=>"Land Use Variable (sub-comp name)","visible"=>1, "readonly"=>'0', "width"=>24),
              "feed_address"=>array("type"=>1,"params"=>"","label"=>"Data Feed URL","visible"=>1, "readonly"=>0, "width"=>64),
              "data_inventory_address"=>array("type"=>1,"params"=>"","label"=>"Data Inventory URL","visible"=>1, "readonly"=>0, "width"=>64),
              "log2db"=>array("type"=>3,"params"=>"0|Memory,1|Database,2|File:lid:lname::0:","label"=>"Runtime Logging Option ","visible"=>1, "readonly"=>0, "width"=>6),
              "cache_ts"=>array("type"=>3,"params"=>"0|False,1|True:cid:cname::0","label"=>"Cache Time Series data in external file? ","visible"=>1, "readonly"=>0, "width"=>6),
              "force_refresh"=>array("type"=>3,"params"=>"0|False,1|True:frid:frname::0","label"=>"Force Refresh of cached data? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "CBPLandDataConnection_sub"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "scid"=>array("type"=>3,"params"=>"1|Version 5.18 - p5186,2|Version 5.2 - p52An,3|Version 5.2 - ICPRB,4|Version 5.3:scid:scname::0","label"=>"Model Scenario: ","visible"=>1, "readonly"=>0, "width"=>6),
              "id2"=>array("type"=>1,"params"=>"","label"=>"Land Segment (fipsab)","visible"=>1, "readonly"=>0, "width"=>8),
              "hspf_timestep"=>array("type"=>1, "params"=>"", "label"=>"HSPF Time-step (seconds)","visible"=>1, "readonly"=>'0', "width"=>8),
              "max_memory_values"=>array("type"=>1, "params"=>"", "label"=>"Max. Values to Store in Memory (-1 is unlimited)","visible"=>1, "readonly"=>'0', "width"=>24),
              "feed_address"=>array("type"=>1,"params"=>"","label"=>"Data Feed URL","visible"=>1, "readonly"=>0, "width"=>64),
              "data_inventory_address"=>array("type"=>1,"params"=>"","label"=>"Data Inventory URL","visible"=>1, "readonly"=>0, "width"=>64),
              "lat_dd"=>array("type"=>3,"params"=>"0|False,1|True:smid:smname::0","label"=>"Lattitude ","visible"=>1, "readonly"=>0, "width"=>6),
              "lon_dd"=>array("type"=>3,"params"=>"0|False,1|True:smid:smname::0","label"=>"Longitude","visible"=>1, "readonly"=>0, "width"=>6),
              "nearest_landseg"=>array("type"=>1,"params"=>"","label"=>"Longitude","visible"=>1, "readonly"=>1, "width"=>8),
              "reload_nlcd"=>array("type"=>3,"params"=>"0|False,1|True:rnid:rnname::0","label"=>"Reload NHD+ Landuse? ","visible"=>1,"readonly"=>0, "width"=>6),
              "channel_length"=>array("type"=>1, "params"=>"","label"=>"Channel Length (ft)","visible"=>1, "readonly"=>'1', "width"=>6),
              "channel_slope"=>array("type"=>1, "params"=>"","label"=>"Channel Length (ft)","visible"=>1, "readonly"=>'1', "width"=>6),
              "drainage_area"=>array("type"=>1, "params"=>"","label"=>"Channel Drainage (sqmi)","visible"=>1, "readonly"=>'1', "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "wsp_VWUDSData"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>64),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "id1"=>array("type"=>1,"params"=>"","label"=>"VWUDS User ID","visible"=>1, "readonly"=>0, "width"=>12),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "feed_address"=>array("type"=>1,"params"=>"","label"=>"Data Feed URL","visible"=>0, "readonly"=>0, "width"=>64),
              "data_inventory_address"=>array("type"=>1,"params"=>"","label"=>"Data Inventory URL","visible"=>0, "readonly"=>0, "width"=>64),
              "extra_variables"=>array("type"=>1,"params"=>"8","label"=>"Additional URL Variables ","visible"=>0, "readonly"=>0, "width"=>64),
              "single_datecol"=>array("type"=>3,"params"=>"0|False,1|True:sdcid:sdcname::0","label"=>"Use a Single Column For Date? ","visible"=>0, "readonly"=>0, "width"=>6),
              "cache_ts"=>array("type"=>3,"params"=>"0|False,1|True:cid:cname::0","label"=>"Cache Time Series data in external file? ","visible"=>0, "readonly"=>0, "width"=>6),
              "force_refresh"=>array("type"=>3,"params"=>"0|False,1|True:frid:frname::0","label"=>"Force Refresh of cached data? ","visible"=>0, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "the_geom"=>array("type"=>1, "params"=>"", "label"=>"Geometry Info","visible"=>0, "readonly"=>'0', "width"=>24),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "XMLDataConnection"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "feed_address"=>array("type"=>1,"params"=>"","label"=>"Data Feed URL","visible"=>1, "readonly"=>0, "width"=>6),
              "data_inventory_address"=>array("type"=>1,"params"=>"","label"=>"Data Inventory URL","visible"=>1, "readonly"=>0, "width"=>32),
              "extra_variables"=>array("type"=>1,"params"=>"8","label"=>"Additional URL Variables ","visible"=>1, "readonly"=>0, "width"=>64),
              "single_datecol"=>array("type"=>3,"params"=>"0|False,1|True:sdcid:sdcname::0","label"=>"Use a Single Column For Date? ","visible"=>1, "readonly"=>0, "width"=>6),
              "datecolumn"=>array("type"=>3,"params"=>"0|False,1|True:did:dname::0","label"=>"Column With Date/Time","visible"=>1, "readonly"=>0, "width"=>32),
              "yearcolumn"=>array("type"=>3,"params"=>"0|False,1|True:dyid:dyname::0","label"=>"Column With Year","visible"=>1, "readonly"=>0, "width"=>32),
              "monthcolumn"=>array("type"=>3,"params"=>"0|False,1|True:dmid:dmname::0","label"=>"Column With Month","visible"=>1, "readonly"=>0, "width"=>32),
              "daycolumn"=>array("type"=>3,"params"=>"0|False,1|True:ddid:ddname::0","label"=>"Column With Day","visible"=>1, "readonly"=>0, "width"=>32),
              "restrict_spatial"=>array("type"=>3,"params"=>"0|False,1|True:rsid:rsname::0","label"=>"Restrict to spatial bounds? ","visible"=>1, "readonly"=>0, "width"=>6),
              "lon_col"=>array("type"=>3,"params"=>"0|False,1|True:loid:loname::0","label"=>"Column With Longitude","visible"=>1, "readonly"=>0, "width"=>32),
              "lat_col"=>array("type"=>3,"params"=>"0|False,1|True:laid:laname::0","label"=>"Column With Latitude","visible"=>1, "readonly"=>0, "width"=>32),
              "cache_ts"=>array("type"=>3,"params"=>"0|False,1|True:cid:cname::0","label"=>"Cache Time Series data in external file? ","visible"=>1, "readonly"=>0, "width"=>6),
              "force_refresh"=>array("type"=>3,"params"=>"0|False,1|True:frid:frname::0","label"=>"Force Refresh of cached data? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "RSSDataConnection"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column"),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "feed_address"=>array("type"=>1,"params"=>"","label"=>"Data Feed URL","visible"=>1, "readonly"=>0, "width"=>6),
              "data_inventory_address"=>array("type"=>1,"params"=>"","label"=>"Data Inventory URL","visible"=>1, "readonly"=>0, "width"=>32),
              "extra_variables"=>array("type"=>1,"params"=>"8","label"=>"Additional URL Variables ","visible"=>1, "readonly"=>0, "width"=>64),
              "single_datecol"=>array("type"=>3,"params"=>"0|False,1|True:sdcid:sdcname::0","label"=>"Use a Single Column For Date? ","visible"=>1, "readonly"=>0, "width"=>6),
              "datecolumn"=>array("type"=>3,"params"=>"0|False,1|True:did:dname::0","label"=>"Column With Date/Time","visible"=>1, "readonly"=>0, "width"=>32),
              "yearcolumn"=>array("type"=>3,"params"=>"0|False,1|True:dyid:dyname::0","label"=>"Column With Year","visible"=>1, "readonly"=>0, "width"=>32),
              "monthcolumn"=>array("type"=>3,"params"=>"0|False,1|True:dmid:dmname::0","label"=>"Column With Month","visible"=>1, "readonly"=>0, "width"=>32),
              "daycolumn"=>array("type"=>3,"params"=>"0|False,1|True:ddid:ddname::0","label"=>"Column With Day","visible"=>1, "readonly"=>0, "width"=>32),
              "restrict_spatial"=>array("type"=>3,"params"=>"0|False,1|True:rsid:rsname::0","label"=>"Restrict to spatial bounds? ","visible"=>1, "readonly"=>0, "width"=>6),
              "lon_col"=>array("type"=>3,"params"=>"0|False,1|True:loid:loname::0","label"=>"Column With Longitude","visible"=>1, "readonly"=>0, "width"=>32),
              "lat_col"=>array("type"=>3,"params"=>"0|False,1|True:laid:laname::0","label"=>"Column With Latitude","visible"=>1, "readonly"=>0, "width"=>32),
              "cache_ts"=>array("type"=>3,"params"=>"0|False,1|True:cid:cname::0","label"=>"Cache Time Series data in external file? ","visible"=>1, "readonly"=>0, "width"=>6),
              "force_refresh"=>array("type"=>3,"params"=>"0|False,1|True:frid:frname::0","label"=>"Force Refresh of cached data? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "debugmode"=>array("type"=>3,"params"=>"0|Screen Output (Normal),1|System Log,2|STDOUT,3|To File:dbid:dbname::0","label"=>"Debug Mode ","visible"=>1, "readonly"=>0, "width"=>6),
              "groupid"=>array("type"=>3,"params"=>"groups:groupid:groupname:groupname:0:","label"=>"Group ID","visible"=>1, "readonly"=>0, "width"=>12),
              "operms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:opid:opname::0","label"=>"Owner Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "gperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:gpid:gpname::0","label"=>"Group Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "pperms"=>array("type"=>3,"params"=>"7|All (RW-Del),6|Read/Write,4|Read-Only,0|None:ppid:ppname::0","label"=>"Public Permissions: ","visible"=>1, "readonly"=>0, "width"=>6),
              "cacheable"=>array("type"=>3,"params"=>"0|Do Not Cache,1|Cacheable,2|Pass-Through (children can cache),3|0th Level (allow perm child cache):cmid:cmname::0","label"=>"Run Cache Mode: ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "phone"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"row", "showPlusMinus"=>1, "formName"=>'phone'),
          "column info"=>array(
              "phonenum"=>array("type"=>1,"params"=>"","label"=>"Phone Number","visible"=>1, "readonly"=>0, "width"=>24),
              "phonetype"=>array("type"=>3,"params"=>"1|Home,2|Work,3|Mobile:ptid:ptname::0","label"=>"Phone Type ","visible"=>1, "readonly"=>0, "width"=>6),
              "primaryphone"=>array("type"=>23, "params"=>"1", "label"=>"Primary?","visible"=>1, "readonly"=>'0', "default"=>0, "width"=>24)
           )
        ),
      "textField"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column", "showPlusMinus"=>1, "formName"=>'phone'),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Field Name","visible"=>1, "readonly"=>0, "width"=>24),
              "charlength"=>array("type"=>1,"params"=>"","label"=>"Max Length in Characters ","visible"=>1, "readonly"=>0, "width"=>6),
              "value"=>array("type"=>1, "params"=>"1", "label"=>"Value","visible"=>1, "readonly"=>'0', "default"=>0, "width"=>24)
           )
        ),
      "runVariableStorageObject"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"row", "showPlusMinus"=>1,"showlabels"=>1 ),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "dataname"=>array("type"=>3,"params"=>"name|name:xid:xname::0", "quote_values"=>0,"label"=>"Variable to Summarize:","visible"=>1, "readonly"=>0, "width"=>8),
              "reporting_frequency"=>array("type"=>3,"params"=>"single|Single,ts|Every Time-step,daily|Daily,monthly|Monthly:smid:smname::0","label"=>"Reporting Frequency","visible"=>1, "readonly"=>0, "width"=>6),
              "temporal_res"=>array("type"=>1,"params"=>"","label"=>"Temporal Resolution","visible"=>1, "readonly"=>0, "width"=>64),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy","visible"=>1, "readonly"=>0, "width"=>24),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "broadCastObject"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"row", "showPlusMinus"=>1,"showlabels"=>1 ),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6),
              "broadcast_class"=>array("type"=>1,"params"=>"","label"=>"Broadcast Class Name","visible"=>1, "readonly"=>0, "width"=>64),
              "broadcast_hub"=>array("type"=>3,"params"=>"child|Child Hub,parent|Parent Hub:smid:smname::0","label"=>"Broadcast Hub","visible"=>1, "readonly"=>0, "width"=>6),
              "broadcast_mode"=>array("type"=>3,"params"=>"cast|Broadcast,read|Listen:smid:smname::0","label"=>"Broadcast Mode","visible"=>1, "readonly"=>0, "width"=>6),
              "exec_hierarch"=>array("type"=>1,"params"=>"","label"=>"Execution Hierarchy","visible"=>1, "readonly"=>0, "width"=>24),
              "broadcast_varname"=>array("type"=>3,"params"=>"name|name:xid:xname::0", "quote_values"=>0,"label"=>"Broadcast Name:","visible"=>1, "readonly"=>0, "width"=>8),
              "local_varname"=>array("type"=>3,"params"=>"name|name:xid:xname::0", "quote_values"=>0,"label"=>"Local Name:","visible"=>1, "readonly"=>0, "width"=>8)
           )
        ),
      "queryWizardComponent"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"column", "child_formats"=>array('queryWizard_selectcolumns', 'queryWizard_wherecolumns', 'queryWizard_ordercolumns') ),
          "column info"=>array(
              "name"=>array("type"=>1,"params"=>"","label"=>"Name","visible"=>1, "readonly"=>0, "width"=>32),
              "description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>64),
              "debug"=>array("type"=>3,"params"=>"0|False,1|True,2|Verbose:smid:smname::0","label"=>"Run in debug mode? ","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
       # BEGIN - meta-row-columns used by queryWizardComponent
       # select columns
      "queryWizard_selectcolumns"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"row", "showPlusMinus"=>1, "formName"=>'phone', "adname"=>'queryWizard_selectcolumns', "parentname"=>'selectedcolumns', "childname"=>'selectid',"showlabels"=>0),
          "column info"=>array(
              "qcols"=>array("type"=>3,"params"=>"name|name:qcid:qcname::0","label"=>"Column","visible"=>1, "readonly"=>0, "width"=>12),
              //"qcols_func"=>array("type"=>3,"params"=>"none|None,min|min(),max|max(),median|median(),mean|mean(),sum|sum(),gini|gini(),count|count():fid:fname::0","label"=>"f(x)","visible"=>1, "readonly"=>0, "width"=>6),
              "qcols_func"=>array("type"=>3,"params"=>"none|None,min|min(),max|max(),mean|mean(),median|median(),sum|sum(),gini|gini(),stddev|stddev(),count|count(),quantile|quantile(n&#44;dec):fid:fname::0","label"=>"f(x)","visible"=>1, "readonly"=>0, "width"=>6),
              "qcols_alias"=>array("type"=>1, "params"=>"1", "label"=>"AS ","visible"=>1, "readonly"=>'0', "default"=>'', "width"=>12),
              "qcols_txt"=>array("type"=>1, "params"=>"1", "label"=>"[Args]","visible"=>1, "readonly"=>'0', "default"=>'', "width"=>4)
           )
        ),
      "queryWizard_selectcolumns_r-enabled"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"row", "showPlusMinus"=>1, "formName"=>'phone', "adname"=>'queryWizard_selectcolumns', "parentname"=>'selectedcolumns', "childname"=>'selectid',"showlabels"=>0),
          "column info"=>array(
              "qcols"=>array("type"=>3,"params"=>"name|name:qcid:qcname::0","label"=>"Column","visible"=>1, "readonly"=>0, "width"=>12),
              "qcols_func"=>array("type"=>3,"params"=>"none|None,min|min(),max|max(),median|median(),mean|mean(),sum|sum(),gini|gini(),count|count():fid:fname::0","label"=>"f(x)","visible"=>1, "readonly"=>0, "width"=>6),
              "qcols_alias"=>array("type"=>1, "params"=>"1", "label"=>"AS ","visible"=>1, "readonly"=>'0', "default"=>'', "width"=>12),
              "qcols_txt"=>array("type"=>1, "params"=>"1", "label"=>"[Args]","visible"=>1, "readonly"=>'0', "default"=>'', "width"=>8)
           )
        ),
        # where conditions
      "queryWizard_wherecolumns"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"row", "showPlusMinus"=>1, "formName"=>'phone', "adname"=>'queryWizard_wherecolumns', "parentname"=>'wherecolumns', "childname"=>'whereid',"showlabels"=>1),
          "column info"=>array(
              "wcols"=>array("type"=>3,"params"=>"name|name:wcid:wcname::0","label"=>"Column","visible"=>1, "readonly"=>0, "width"=>12),
              "wcols_op"=>array("type"=>3,"params"=>"=|=,>|>,<|<,>=|>=,<=|<=,<>|<>,in|in (),contains|contains(),notnull|not null,isnull|is null:fid:fname::0","label"=>"Operator","visible"=>1, "readonly"=>0, "width"=>6),
              "wcols_value"=>array("type"=>1, "params"=>"1", "label"=>"Value","visible"=>1, "readonly"=>'0', "default"=>'', "width"=>12)
           )
        ),
        # where conditions
      "queryWizard_ordercolumns"=>array(
          "table info"=>array("pk"=>"elementid", "sortcol"=>"name", "outputformat"=>"row", "showPlusMinus"=>1, "formName"=>'phone', "adname"=>'queryWizard_ordercolumns', "parentname"=>'ordercolumns', "childname"=>'orderid',"showlabels"=>1),
          "column info"=>array(
              "ocols"=>array("type"=>3,"params"=>"name|name:ocid:ocname::0","label"=>"Column","visible"=>1, "readonly"=>0, "width"=>12)
           )
        ),
       # END - meta-row-columns used by queryWizardComponent
      "droughtflowgwlake"=>array(
          "table info"=>array("pk"=>"groupname", "sortcol"=>"groupname", "outputformat"=>"row"),
          "column info"=>array(
              "groupname"=>array("type"=>1,"params"=>"","label"=>"Region Name","visible"=>1, "readonly"=>0, "width"=>64),
              "startdate"=>array("type"=>1,"params"=>"","label"=>"Start Date","visible"=>1, "readonly"=>0, "width"=>12),
              "enddate"=>array("type"=>1,"params"=>"","label"=>"End Date","visible"=>1, "readonly"=>0, "width"=>12),
              "thispct"=>array("type"=>7, "params"=>"2","label"=>"Return Frequency","visible"=>1, "readonly"=>'0', "width"=>6),
              "thisval"=>array("type"=>9, "params"=>"2", "label"=>"Mean Value","visible"=>1, "readonly"=>'0', "width"=>24),
              "minpct"=>array("type"=>7, "params"=>"2","label"=>"Min Return Freq.","visible"=>1, "readonly"=>'0', "width"=>6),
              "minval"=>array("type"=>9, "params"=>"2", "label"=>"Min Value","visible"=>1, "readonly"=>'0', "width"=>24),
              "maxpct"=>array("type"=>7, "params"=>"2","label"=>"Max Return Freq.","visible"=>1, "readonly"=>'0', "width"=>6),
              "maxval"=>array("type"=>9, "params"=>"2", "label"=>"Max Value","visible"=>1, "readonly"=>'0', "width"=>24),
              "val_pct"=>array("type"=>1, "params"=>"2", "label"=>"Mean Value/Pct","visible"=>1, "readonly"=>'0', "width"=>24)
           )
        ),
      "droughtprecip"=>array(
          "table info"=>array("pk"=>"groupname", "sortcol"=>"groupname", "outputformat"=>"row"),
          "column info"=>array(
              "groupname"=>array("type"=>1,"params"=>"","label"=>"Region Name","visible"=>1, "readonly"=>0, "width"=>64),
              "startdate"=>array("type"=>1,"params"=>"","label"=>"Start Date","visible"=>1, "readonly"=>0, "width"=>12),
              "enddate"=>array("type"=>1,"params"=>"","label"=>"End Date","visible"=>1, "readonly"=>0, "width"=>12),
              "pct"=>array("type"=>7, "params"=>"2","label"=>"% of Normal","visible"=>1, "readonly"=>'0', "width"=>6),
              "obs"=>array("type"=>9, "params"=>"2", "label"=>"Total Precip (in)","visible"=>1, "readonly"=>'0', "width"=>24),
              "nml"=>array("type"=>9, "params"=>"2", "label"=>"Normal Precip (in)","visible"=>1, "readonly"=>'0', "width"=>24),
              "dep"=>array("type"=>9, "params"=>"2", "label"=>"Departure from Normal (in)","visible"=>1, "readonly"=>'0', "width"=>24)
           )
        ),
        # Note:
        #   1) Water supply planning form elements must be defined in the 'watersupply_plan' record, and then again in an
        #      individual record following. The initial record contains only the name and form title, the second record defines
        #      column info.  See 'watersupply_plan' record for an example.
        #   2) Record entry forms that appear in the SAME tab must be defined with unique column names, since they will
        #      over-write one another during form subsmission if name duplicates exist
        #   2) Record entry forms that appear in the SAME tab must be defined with unique column names, since they will
        #      over-write one another during form subsmission if name duplicates exist
      "watersupply_plan"=>array(
          "table info"=>array("pk"=>"objectname", "sortcol"=>"objectname", "outputformat"=>"column"),
          "column info"=>array(
             # general plan info
              "element_seggroup"=>array("type"=>3,"params"=>"proj_seggroups:gid:groupname:groupname:0:","label"=>"Geographic Grouping","visible"=>1, "readonly"=>0, "width"=>12),
              "planname"=>array("type"=>1,"params"=>"","label"=>"Plan Name","visible"=>1, "readonly"=>0, "width"=>64),
              "entityname"=>array("type"=>1,"params"=>"","label"=>"Planning Entity Name","visible"=>1, "readonly"=>0, "width"=>64),
              "plandate"=>array("type"=>1,"params"=>"","label"=>"Date of Plan Activation","visible"=>1, "readonly"=>0, "width"=>12),
              "pocname"=>array("type"=>1, "params"=>"","label"=>"POC Name","visible"=>1, "readonly"=>'0', "width"=>6),
              "pocphone"=>array("type"=>1, "params"=>"", "label"=>"POC Phone Number","visible"=>1, "readonly"=>'0', "width"=>24),
              "pocemail"=>array("type"=>1, "params"=>"", "label"=>"POC Email","visible"=>1, "readonly"=>'0', "width"=>32),
              # groundwater sources - this is a multiple, so it is only referenced as the name of the data array, which
              # is described below in column detail
              "existing_gw_sources"=>array("type"=>1,"params"=>"","label"=>"Existing Ground Water Sources","visible"=>1, "readonly"=>0, "width"=>64),
              # Surface Water sources - this is a multiple, so it is only referenced as the name of the data array, which
              # is described below in column detail
              "existing_sw_sources"=>array("type"=>1,"params"=>"","label"=>"Existing Surface Water Sources","visible"=>1, "readonly"=>0, "width"=>64),
              # Water Treatment Plants - this is a multiple, so it is only referenced as the name of the data array, which
              # is described below in column detail
              "water_treatment"=>array("type"=>1,"params"=>"","label"=>"Treatment Plants","visible"=>1, "readonly"=>0, "width"=>64),
              # Existing Water Demands - this is a multiple, so it is only referenced as the name of the data array, which
              # is described below in column detail
              "existing_uses"=>array("type"=>1,"params"=>"","label"=>"Existing Water Uses (Community Supplied)","visible"=>1, "readonly"=>0, "width"=>64),
              "existing_uses_self_gt300k"=>array("type"=>1,"params"=>"","label"=>"Existing Water Uses (self-supplied > 300K)","visible"=>1, "readonly"=>0, "width"=>64),
              "existing_uses_self_lt300k"=>array("type"=>1,"params"=>"","label"=>"Existing Water Uses (self-supplied < 300K)","visible"=>1, "readonly"=>0, "width"=>64),
              "resources_et_species"=>array("type"=>1,"params"=>"","label"=>"Resources: Endangered/Threatened Species","comment"=>"<ul><li> Rank: G1/S1 - Critically Imperiled, G2/S2 - Imperiled, G3/S3 - Vulnerable<li> Status: LE - Listed Endangered, LT - Listed Threatened<li> Q - Inidicates that a taxonomic question concerning that species exists</ul>","visible"=>1, "readonly"=>0, "width"=>64),
              "resources_habitats"=>array("type"=>1,"params"=>"","label"=>"Resources: Habitats of Concern","visible"=>1, "readonly"=>0, "width"=>64),
              "resources_recreation"=>array("type"=>1,"params"=>"","label"=>"Resources: Recreational","visible"=>1, "readonly"=>0, "width"=>64),
              "resources_anadromous"=>array("type"=>1,"params"=>"","label"=>"Resources: Anadromous, Trout, Other Significant Fisheries","comment"=>"<ul><li> Status: FE/SE - Endangered, FT/ST - Threatened, FC - Candidate; FS/SS - Species of Concern (not a legal status; list maintained by USFWS Virginia Field Office)<li> Game Fish: As identified by the Virginia Department of Game and Inland Fisheries</ul>","visible"=>1, "readonly"=>0, "width"=>64),
              "resources_scenic"=>array("type"=>1,"params"=>"","label"=>"Resources: Scenic Rivers","visible"=>1, "readonly"=>0, "width"=>64),
              "resources_historic"=>array("type"=>1,"params"=>"","label"=>"Resources: Historic","comment"=>"<ul><li> VLR - Virginia Landmarks Register (Date Entered)<li> NRHP - National Register of Historic Places (Date Entered)</ul>","visible"=>1, "readonly"=>0, "width"=>64),
              "resources_geologic"=>array("type"=>1,"params"=>"","label"=>"Resources: Unusual Geologic Formations Or Special Soil Types","visible"=>1, "readonly"=>0, "width"=>64),
              "resources_wetlands"=>array("type"=>1,"params"=>"","label"=>"Resources: Wetlands","visible"=>1, "readonly"=>0, "width"=>64),
              "resources_buffers"=>array("type"=>1,"params"=>"","label"=>"Resources: Riparian Buffers/","visible"=>1, "readonly"=>0, "width"=>64),
              "resources_easements"=>array("type"=>1,"params"=>"","label"=>"Resources: Conservation Easements","visible"=>1, "readonly"=>0, "width"=>64),
              "resources_landuse"=>array("type"=>1,"params"=>"","label"=>"Resources: Land Use","visible"=>1, "readonly"=>0, "width"=>64),
              "resources_tmdl"=>array("type"=>1,"params"=>"","label"=>"Resources: TMDLs","visible"=>1, "readonly"=>0, "width"=>64),
              "resources_pointsource"=>array("type"=>1,"params"=>"","label"=>"Resources: Point Source Discharges","visible"=>1, "readonly"=>0, "width"=>64)
           )
        ),
      "plan_overview"=>array(
          "table info"=>array("pk"=>"objectname", "sortcol"=>"objectname", "outputformat"=>"column", "valign"=>"bottom"),
          "column info"=>array(
              "element_seggroup"=>array("type"=>3,"params"=>"proj_seggroups:gid:groupname:groupname:0:","label"=>"Geographic Grouping","visible"=>1, "readonly"=>0, "width"=>12),
              "elementid"=>array("type"=>1,"params"=>"","label"=>"Element ID","visible"=>0, "readonly"=>1, "width"=>12),
              "planname"=>array("type"=>1,"params"=>"","label"=>"Plan Name","visible"=>1, "readonly"=>0, "width"=>64),
              "entityname"=>array("type"=>1,"params"=>"","label"=>"Planning Entity Name","visible"=>1, "readonly"=>0, "width"=>64),
              "plandate"=>array("type"=>1,"params"=>"","label"=>"Date of Plan Activation","visible"=>1, "readonly"=>0, "width"=>12),
              "pocname"=>array("type"=>1, "params"=>"","label"=>"POC Name","visible"=>1, "readonly"=>'0', "width"=>64),
              "pocphone"=>array("type"=>1, "params"=>"", "label"=>"POC Phone Number","visible"=>1, "readonly"=>'0', "width"=>24),
              "pocemail"=>array("type"=>1, "params"=>"", "label"=>"POC Email","visible"=>1, "readonly"=>'0', "width"=>32)
           )
        ),
      "existing_gw_sources"=>array(
          "table info"=>array("pk"=>"numkey", "sortcol"=>"objectname", "outputformat"=>"mapmatrix"),
          "column info"=>array(
              #"gw_vwuds"=>array("type"=>3,"params"=>"water_use_dd:\"MPID\":\"OWNNAME\",\"FACILITY\",\"MPID\":\"OWNNAME\",\"FACILITY\":0: \"TYPE\" = 'GW' and the_geom && (select the_geom from proj_seggroups where gid = " . $plandata['element_seggroup'] . ")","label"=>"VWUDS Source","visible"=>1, "readonly"=>0, "width"=>24),
              "gw_sourcename"=>array("type"=>1,"params"=>"","label"=>"Well Name","visible"=>1, "readonly"=>0, "width"=>24),
              "gw_vdhwellid"=>array("type"=>1,"params"=>"","label"=>"VDH Well ID","visible"=>1, "readonly"=>0, "width"=>12),
              "gw_welldepth"=>array("type"=>1,"params"=>"","label"=>"Well Depth (ft)","visible"=>1, "readonly"=>0, "width"=>6),
              "gw_casingdepth"=>array("type"=>1,"params"=>"","label"=>"Casing Depth (ft)","visible"=>1, "readonly"=>0, "width"=>6),
              "gw_screendepth"=>array("type"=>1, "params"=>"","label"=>"Screen Depth (ft)","visible"=>1, "readonly"=>'0', "width"=>6),
              "gw_welldiameter"=>array("type"=>1, "params"=>"", "label"=>"Well Diameter (ft)","visible"=>1, "readonly"=>'0', "width"=>6),
              "gw_avgdailywd"=>array("type"=>1, "params"=>"", "label"=>"Avg. Daily Withdrawal (MGD)","visible"=>1, "readonly"=>'0', "width"=>8),
              "gw_designmaxdailywd"=>array("type"=>1, "params"=>"", "label"=>"Design Max Daily (MGD)","visible"=>1, "readonly"=>'0', "width"=>8),
              "gw_permitmaxdailywd"=>array("type"=>1, "params"=>"", "label"=>"Permit Max Daily (MGD)","visible"=>1, "readonly"=>'0', "width"=>8)
           )
        ),
      "existing_sw_sources"=>array(
          "table info"=>array("pk"=>"numkey", "sortcol"=>"objectname", "outputformat"=>"mapmatrix"),
          "column info"=>array(
              #"sw_vwuds"=>array("type"=>3,"params"=>"water_use_dd:\"MPID\":\"OWNNAME\",\"FACILITY\",\"MPID\":\"OWNNAME\",\"FACILITY\":0: \"TYPE\" = 'SW' and the_geom && (select the_geom from proj_seggroups where gid = " . $plandata['element_seggroup'] . ")","label"=>"VWUDS Source","visible"=>1, "readonly"=>0, "width"=>24),
              #"sw_reservoirname"=>array("type"=>3,"params"=>"proj_subsheds:subshedid:name:name:0:projectid=$projectid and subshedid in ($seglist)","label"=>"Source Name","visible"=>1, "readonly"=>0, "width"=>24),
              "sw_reservoirname"=>array("type"=>1,"params"=>"","label"=>"Reservoir/River Name","visible"=>1, "readonly"=>0, "width"=>24),
              "sw_basin"=>array("type"=>1,"params"=>"","label"=>"Reservoir/River Basin","visible"=>1, "readonly"=>0, "width"=>12),
              "sw_subbasin"=>array("type"=>1,"params"=>"","label"=>"Reservoir/River Sub-Basin","visible"=>1, "readonly"=>0, "width"=>12),
              "sw_drainage_area"=>array("type"=>1,"params"=>"","label"=>"Drainage Area (sq. mi.)","visible"=>1, "readonly"=>0, "width"=>6),
              "sw_onstream_storage"=>array("type"=>1, "params"=>"","label"=>"Available On-Stream Storage (MG)","visible"=>1, "readonly"=>'0', "width"=>6),
              "sw_designavgdailywd"=>array("type"=>1, "params"=>"", "label"=>"Design Avg. Daily (MGD)","visible"=>1, "readonly"=>'0', "width"=>8),
              "sw_designmaxdailywd"=>array("type"=>1, "params"=>"", "label"=>"Design Max Daily (MGD)","visible"=>1, "readonly"=>'0', "width"=>8),
              "sw_limitations"=>array("type"=>1, "params"=>"", "label"=>"Withdrawal Limitations","visible"=>1, "readonly"=>'0', "width"=>6),
              "sw_safeyield"=>array("type"=>1, "params"=>"", "label"=>"Safe Yield (MGD)","visible"=>1, "readonly"=>'0', "width"=>8)
           )
        ),
      "water_treatment"=>array(
          "table info"=>array("pk"=>"numkey", "sortcol"=>"objectname", "outputformat"=>"mapmatrix"),
          "column info"=>array(
              "plantname"=>array("type"=>1,"params"=>"","label"=>"Plant Name","visible"=>1, "readonly"=>0, "width"=>24),
              "plant_capacity"=>array("type"=>1,"params"=>"","label"=>"Treatment Plant Capacity (MGD)","visible"=>1, "readonly"=>0, "width"=>12),
              "plant_permitted"=>array("type"=>1,"params"=>"","label"=>"Permitted Capacity of System (MGD)","visible"=>1, "readonly"=>0, "width"=>12)
           )
        ),
      "existing_uses"=>array(
          "table info"=>array("pk"=>"numkey", "sortcol"=>"objectname", "outputformat"=>"mapmatrix"),
          "column info"=>array(
              "use_type"=>array("type"=>3,"params"=>"Residential|Residential,Industrial|Industrial,Commercial|Commercial,Agricultural|Agricultural (non-Irrigation),Irrigation|Agricultural (Irrigation),Maufacturing|Manufacturing:typeid:typename::0","label"=>"Usage Type","visible"=>1, "readonly"=>0, "width"=>24),
              #"use_source"=>array("type"=>3,"params"=> implode_with_keys(extract_keyvalue($plandata['existing_gw_sources'], 'gw_sourcename', 'gw_sourcename'), '|', ',', $is_query = false) . ":typeid:typename::0","label"=>"Usage Source","visible"=>1, "readonly"=>0, "width"=>24),
              "use_units"=>array("type"=>1,"params"=>"","label"=>"Units (Population, etc.)","visible"=>1, "readonly"=>0, "width"=>12),
              "use_connections"=>array("type"=>1,"params"=>"","label"=>"# of Connections","visible"=>1, "readonly"=>0, "width"=>6),
              "use_per_unit"=>array("type"=>1,"params"=>"","label"=>"Avg. Consumption Per Unit (GD)","visible"=>1, "readonly"=>0, "width"=>6),
              "avg_monthly"=>array("type"=>1,"params"=>"","label"=>"Avg. Monthly Consumption (MGD)","visible"=>1, "readonly"=>0, "width"=>12),
              "avg_annual"=>array("type"=>1,"params"=>"","label"=>"Avg. Annual Consumption (MGD)","visible"=>1, "readonly"=>0, "width"=>12)
           )
        ),
      "existing_uses_self_gt300k"=>array(
          "table info"=>array("pk"=>"numkey", "sortcol"=>"objectname", "outputformat"=>"mapmatrix"),
          "column info"=>array(
              "ssg_use_type"=>array("type"=>3,"params"=>"1|Residential,2|Industrial,3|Commercial,4|Agricultural (non-Irrigation),5|Agricultural (Irrigation),6|Manufacturing:typeid:typename::0","label"=>"Usage Type","visible"=>1, "readonly"=>0, "width"=>24),
              "ssg_use_units"=>array("type"=>1,"params"=>"","label"=>"Units (Population, etc.)","visible"=>1, "readonly"=>0, "width"=>12),
              "ssg_use_connections"=>array("type"=>1,"params"=>"","label"=>"# of Connections","visible"=>1, "readonly"=>0, "width"=>6),
              "ssg_use_per_unit"=>array("type"=>1,"params"=>"","label"=>"Avg. Consumption Per Unit (GD)","visible"=>1, "readonly"=>0, "width"=>6),
              "ssg_avg_monthly"=>array("type"=>1,"params"=>"","label"=>"Avg. Monthly Consumption (MGD)","visible"=>1, "readonly"=>0, "width"=>12),
              "ssg_avg_annual"=>array("type"=>1,"params"=>"","label"=>"Avg. Annual Consumption (MGD)","visible"=>1, "readonly"=>0, "width"=>12)
           )
        ),
      "existing_uses_self_lt300k"=>array(
          "table info"=>array("pk"=>"numkey", "sortcol"=>"objectname", "outputformat"=>"mapmatrix"),
          "column info"=>array(
              "ssl_use_type"=>array("type"=>3,"params"=>"1|Residential,2|Industrial,3|Commercial,4|Agricultural (non-Irrigation),5|Agricultural (Irrigation),6|Manufacturing:typeid:typename::0","label"=>"Usage Type","visible"=>1, "readonly"=>0, "width"=>24),
              "ssl_use_units"=>array("type"=>1,"params"=>"","label"=>"Units (Population, etc.)","visible"=>1, "readonly"=>0, "width"=>12),
              "ssl_use_connections"=>array("type"=>1,"params"=>"","label"=>"# of Connections","visible"=>1, "readonly"=>0, "width"=>6),
              "ssl_use_per_unit"=>array("type"=>1,"params"=>"","label"=>"Avg. Consumption Per Unit (MGD)","visible"=>1, "readonly"=>0, "width"=>6),
              "ssl_avg_monthly"=>array("type"=>1,"params"=>"","label"=>"Avg. Monthly Consumption (MGD)","visible"=>1, "readonly"=>0, "width"=>12),
              "ssl_avg_annual"=>array("type"=>1,"params"=>"","label"=>"Avg. Annual Consumption (MGD)","visible"=>1, "readonly"=>0, "width"=>12)
           )
        ),
      "resources_et_species"=>array(
          "table info"=>array("pk"=>"numkey", "sortcol"=>"objectname", "outputformat"=>"mapmatrix"),
          "column info"=>array(
              "re_ets_class"=>array("type"=>1,"params"=>"","label"=>"Class","visible"=>1, "readonly"=>0, "width"=>16),
              "re_ets_sciname"=>array("type"=>1,"params"=>"","label"=>"Scientific Name","visible"=>1, "readonly"=>0, "width"=>16),
              "re_ets_common"=>array("type"=>1,"params"=>"","label"=>"Common Name","visible"=>1, "readonly"=>0, "width"=>16),
              "re_ets_globalrank"=>array("type"=>3,"params"=>"G1|G1,G2|G2,G3|G3,GNR|GNR:grid:grname::0","label"=>"Global Rank","visible"=>1, "readonly"=>0, "width"=>6),
              "re_ets_staterank"=>array("type"=>3,"params"=>"S1|S1,S2|S2,S3|S3,SNR|SNR:srid:srname::0","label"=>"State Rank","visible"=>1, "readonly"=>0, "width"=>6),
              "re_ets_fed_status"=>array("type"=>3,"params"=>"LE|LE,LT|LT,FQ|GQ:fsrid:fsname::0","label"=>"Federal Status","visible"=>1, "readonly"=>0, "width"=>12),
              "re_ets_state_status"=>array("type"=>3,"params"=>"LE|LE,LT|LT,FQ|GQ:ssrid:ssname::0","label"=>"State Status","visible"=>1, "readonly"=>0, "width"=>12),
              "re_ets_lastyear"=>array("type"=>1,"params"=>"","label"=>"Last year Observed","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "resources_habitats"=>array(
          "table info"=>array("pk"=>"numkey", "sortcol"=>"objectname", "outputformat"=>"mapmatrix"),
          "column info"=>array(
              "re_hab_common"=>array("type"=>1,"params"=>"","label"=>"Common Name","visible"=>1, "readonly"=>0, "width"=>16),
              "re_hab_globalrank"=>array("type"=>1,"params"=>"","label"=>"Global Rank","visible"=>1, "readonly"=>0, "width"=>6),
              "re_hab_fedrank"=>array("type"=>3,"params"=>"F1|F1,F2|F2,F3|F3,FNR|FNR:hfrid:hfrname::0","label"=>"Federal Rank","visible"=>1, "readonly"=>0, "width"=>12),
              "re_hab_staterank"=>array("type"=>3,"params"=>"S1|S1,S2|S2,S3|S3,SNR|SNR:hsrid:hsrname::0","label"=>"State Rank","visible"=>1, "readonly"=>0, "width"=>6),
              "re_hab_lastyear"=>array("type"=>1,"params"=>"","label"=>"Last year Observed","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "resources_anadromous"=>array(
          "table info"=>array("pk"=>"numkey", "sortcol"=>"objectname", "outputformat"=>"mapmatrix"),
          "column info"=>array(
              "ran_name_common"=>array("type"=>1,"params"=>"","label"=>"Common Name","visible"=>1, "readonly"=>0, "width"=>16),
              "ran_name_scientific"=>array("type"=>1,"params"=>"","label"=>"Scientific Name","visible"=>1, "readonly"=>0, "width"=>24),
              "ran_ets_fed_status"=>array("type"=>3,"params"=>"LE|LE,LT|LT,FQ|GQ:franid:franname::0","label"=>"Federal Status","visible"=>1, "readonly"=>0, "width"=>12),
              "ran_ets_state_status"=>array("type"=>3,"params"=>"LE|LE,LT|LT,FQ|GQ:sranid:sranname::0","label"=>"State Status","visible"=>1, "readonly"=>0, "width"=>12),
              "ran_game"=>array("type"=>3,"params"=>"0|No,1|Yes:rangid:rangname::0","label"=>"Game Fish","visible"=>1, "readonly"=>0, "width"=>12)
           )
        ),
      "resources_recreation"=>array(
          "table info"=>array("pk"=>"numkey", "sortcol"=>"objectname", "outputformat"=>"mapmatrix"),
          "column info"=>array(
              "rr_name"=>array("type"=>1,"params"=>"","label"=>"Resource Name","visible"=>1, "readonly"=>0, "width"=>16),
              "rr_locationn"=>array("type"=>1,"params"=>"","label"=>"Location","visible"=>1, "readonly"=>0, "width"=>16),
              "rr_fedstatus"=>array("type"=>3,"params"=>"N|None,F|Federal:rrfsid:rrfsname::0","label"=>"Federal Status","visible"=>1, "readonly"=>0, "width"=>12),
              "rr_statestatus"=>array("type"=>3,"params"=>"N|None,S|State:rrssid:rrssname::0","label"=>"State Status","visible"=>1, "readonly"=>0, "width"=>6),
              "rr_components"=>array("type"=>14,"params"=>"rrcid::ST|Stocked Trout,CK|Canoe/Kayak:::rrcname:rrcname:0:3","label"=>"Recreation Component","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "resources_scenic"=>array(
          "table info"=>array("pk"=>"numkey", "sortcol"=>"objectname", "outputformat"=>"mapmatrix"),
          "column info"=>array(
              "rr_scen_common"=>array("type"=>1,"params"=>"","label"=>"Resource Name","visible"=>1, "readonly"=>0, "width"=>16),
              "rr_scen_location"=>array("type"=>1,"params"=>"","label"=>"Location","visible"=>1, "readonly"=>0, "width"=>12),
              "rr_scen_status"=>array("type"=>3,"params"=>"N|None,Q|Qualified,P|Potential:rrscsid:rrssname::0","label"=>"State Status","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "resources_historic"=>array(
          "table info"=>array("pk"=>"numkey", "sortcol"=>"objectname", "outputformat"=>"mapmatrix"),
          "column info"=>array(
              "rhist_name"=>array("type"=>1,"params"=>"","label"=>"Resource Name","visible"=>1, "readonly"=>0, "width"=>16),
              "rhist_location"=>array("type"=>1,"params"=>"","label"=>"Location","visible"=>1, "readonly"=>0, "width"=>12),
              "rhist_vlr_date"=>array("type"=>1,"params"=>"","label"=>"VLR Date","visible"=>1, "readonly"=>0, "width"=>6),
              "rhist_nrhp_date"=>array("type"=>1,"params"=>"","label"=>"NRHP Date","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "resources_geologic"=>array(
          "table info"=>array("pk"=>"numkey", "sortcol"=>"objectname", "outputformat"=>"mapmatrix"),
          "column info"=>array(
              "rg_type"=>array("type"=>3,"params"=>"S|Soil Series,G|Geologic Formation:rgid:rgname::0","label"=>"Type","visible"=>1, "readonly"=>0, "width"=>6),
              "rg_name"=>array("type"=>1,"params"=>"","label"=>"Resource Name","visible"=>1, "readonly"=>0, "width"=>16)
           )
        ),
      "resources_wetlands"=>array(
          "table info"=>array("pk"=>"numkey", "sortcol"=>"objectname", "outputformat"=>"mapmatrix"),
          "column info"=>array(
              "rw_location"=>array("type"=>1,"params"=>"","label"=>"Location","visible"=>1, "readonly"=>0, "width"=>24),
              "rw_area"=>array("type"=>1,"params"=>"","label"=>"Area (acres)","visible"=>1, "readonly"=>0, "width"=>16)
           )
        ),
      "resources_easements"=>array(
          "table info"=>array("pk"=>"numkey", "sortcol"=>"objectname", "outputformat"=>"mapmatrix"),
          "column info"=>array(
              "re_location"=>array("type"=>1,"params"=>"","label"=>"Resource Location","visible"=>1, "readonly"=>0, "width"=>16),
              "re_number"=>array("type"=>1,"params"=>"","label"=>"Easement Projects","visible"=>1, "readonly"=>0, "width"=>16),
              "re_area"=>array("type"=>1,"params"=>"","label"=>"Area (acres)","visible"=>1, "readonly"=>0, "width"=>16)
           )
        ),
      "resources_buffers"=>array(
          "table info"=>array("pk"=>"numkey", "sortcol"=>"objectname", "outputformat"=>"mapmatrix"),
          "column info"=>array(
              "rbuf_type"=>array("type"=>3,"params"=>"B|BMP,C|CREP:rbeid:rbegname::0","label"=>"Type","visible"=>1, "readonly"=>0, "width"=>6),
              "rbuf_location"=>array("type"=>1,"params"=>"","label"=>"Resource Location","visible"=>1, "readonly"=>0, "width"=>16),
              "rbuf_area"=>array("type"=>1,"params"=>"","label"=>"Affected Area (acres)","visible"=>1, "readonly"=>0, "width"=>16)
           )
        ),
      "resources_landuse"=>array(
          "table info"=>array("pk"=>"numkey", "sortcol"=>"objectname", "outputformat"=>"mapmatrix"),
          "column info"=>array(
              "rlu_type"=>array("type"=>3,"params"=>"F|Forest,C|Crop,P|Pasture,O|Orchards,R|Residential,U|Urban:rbeid:rbegname::0","label"=>"Type","visible"=>1, "readonly"=>0, "width"=>6),
              "rlu_location"=>array("type"=>1,"params"=>"","label"=>"Location","visible"=>1, "readonly"=>0, "width"=>16),
              "rlu_area"=>array("type"=>1,"params"=>"","label"=>"Area (acres)","visible"=>1, "readonly"=>0, "width"=>16)
           )
        ),
      "resources_tmdl"=>array(
          "table info"=>array("pk"=>"numkey", "sortcol"=>"objectname", "outputformat"=>"mapmatrix"),
          "column info"=>array(
              "rtmdl_name"=>array("type"=>1,"params"=>"","label"=>"Impairment Name","visible"=>1, "readonly"=>0, "width"=>16),
              "rtmdl_location"=>array("type"=>1,"params"=>"","label"=>"Location","visible"=>1, "readonly"=>0, "width"=>16),
              "rtmdl_type"=>array("type"=>3,"params"=>"WT|Water temperature,PCB|PCB in Fish Tissue,FC|Fecal Coliform,BMB|Benthic-Macroinvertebrate Bioassessments,pH|pH level,EC|Escherichia Coli,PCB|PCB in Fish Tissue,Cu|Copper,Zn|Zinc,DDT|DDT,DDE|DDE,HE|Heptachlor Epoxide:rbeid:rbegname::0","label"=>"Impariment Type","visible"=>1, "readonly"=>0, "width"=>6)
           )
        ),
      "resources_pointsource"=>array(
          "table info"=>array("pk"=>"numkey", "sortcol"=>"objectname", "outputformat"=>"mapmatrix"),
          "column info"=>array(
              "rps_name"=>array("type"=>1,"params"=>"","label"=>"Facility Name","visible"=>1, "readonly"=>0, "width"=>16),
              "rps_location"=>array("type"=>1,"params"=>"","label"=>"Address","visible"=>1, "readonly"=>0, "width"=>16),
              "rps_issued"=>array("type"=>1,"params"=>"","label"=>"Date Issued","visible"=>1, "readonly"=>0, "width"=>16),
              "rps_expired"=>array("type"=>1,"params"=>"","label"=>"Date Expired","visible"=>1, "readonly"=>0, "width"=>16),
              "rps_description"=>array("type"=>1,"params"=>"","label"=>"Description","visible"=>1, "readonly"=>0, "width"=>16)
           )
        )
    );

   $planpages = array(
      'plan_overview'=>array('tab_text'=>'Overview','title'=>'Plan Overview', 'formatname'=>'plan_overview', 'multi'=>0, 'components'=>array('plan_overview')),
      'existing_sources'=>array('tab_text'=>'Sources','title'=>'Existing Water Sources', 'formatname'=>'existing_sources', 'multi'=>1, 'components'=>array('existing_gw_sources','existing_sw_sources','water_treatment')),
      'existing_uses'=>array('tab_text'=>'Uses','title'=>'Existing Water Uses', 'formatname'=>'existing_uses', 'multi'=>1, 'components'=>array('existing_uses','existing_uses_self_gt300k','existing_uses_self_lt300k')),
      'existing_resources'=>array('tab_text'=>'Resources','title'=>'Existing Resources', 'formatname'=>'existing_resources', 'multi'=>1, 'components'=>array('resources_et_species','resources_anadromous','resources_habitats','resources_recreation','resources_scenic','resources_historic','resources_geologic','resources_wetlands','resources_buffers','resources_easements','resources_landuse','resources_tmdl','resources_pointsource')),
      'projected_demand'=>array('tab_text'=>'Projected Demand','title'=>'Projected Demand', 'formatname'=>'projected_demand', 'multi'=>1, 'components'=>array('projected_demand')),
      'demand_management'=>array('tab_text'=>'Demand Management','title'=>'Demand Management', 'formatname'=>'demand_management', 'multi'=>0, 'components'=>array('demand_management')),
      'alternatives'=>array('tab_text'=>'Alternatives','title'=>'Water Supply Alternatives', 'formatname'=>'alternatives', 'multi'=>0, 'components'=>array('alternatives'))
   );

    $tablecolumns = array(
       "subluparams"=>array(
          'projectid','sublu','luname','luarea','forest','lzsn','infilt','lsur', 'slsur','kvary','agwrc','petmax','petmin','infexp','infild','deepfr','basetp', 'agwetp','cepsc','uzsn','nsur','intfw','irc','lzetp','ceps','surs','uzs','ifws', 'lzs','agws','gwvs','subshedid','paramtype','ripbuffer','sqo','sqolim','retsc', 'stcofips','lucode','parentid', 'catcode', 'maxn', 'maxp', 'pct_nm', 'nm_planbase',  'thisyear'
       ),
       "landuses"=>array(
          'luid', 'landuseid', 'projectid', 'landusetype', 'landuse', 'pct_impervious', 'lzetp', 'cepsc', 'wsqop', 'ioqc', 'aoqc', 'sqolim', 'hspflu', 'parentid'
       ),
       "monthlydistro"=>array(
          'distroid', 'spreadid', 'sourceid', 'sourcetype', 'projectid', 'landuseid', 'distroname', 'jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec', 'parentid'
       ),
       "sourcepollutants"=>array(
          'typeid', 'sourcetypeid', 'pollutantname', 'pollutantconc', 'storagedieoff', 'volatilization', 'concunits', 'conv', 'convunits', 'projectid', 'pollutanttype', 'starttime', 'duration', 'directfraction', 'comments', 'parentid'
       ),
       "sources"=>array(
          'sourceid', 'typeid', 'distrotype', 'projectid', 'sourcename', 'avgweight', 'parentid'
       ),
       "subshed"=>array(
          'subid', 'projectid', 'subshedid', 'sourceid', 'popyear', 'sourcepop', 'parentid'
       ),
       "sourceloadtype"=>array(
          'typeid', 'sourcename', 'auweight', 'pollutantprod', 'produnits', 'pollutantconc', 'storagedieoff', 'concunits', 'conv', 'convunits', 'projectid', 'sourceclass', 'starttime', 'duration', 'directfraction', 'avgweight', 'parentid'
       ),
       "groupings"=>array(
          'grp_id', 'projectid', 'groupname', 'subwatersheds', 'grouptype', 'parentid'
       ),
       "hspf_globals"=>array(
          'globalid', 'projectid', 'ucifile', 'wdm1', 'wdm2', 'wdm3', 'wdm4', 'startdate', 'enddate', 'precip_wdm_id', 'evap_wdm_id', 'uzsn_mo', 'cepsc_mo', 'lzetp_mo', 'reach_wdm_id1', 'reachid1', 'reach_wdm_id2', 'reachid2', 'copyreaches', 'copysubsheds', 'useftablefile', 'ftablefile', 'if', 'ro', 'nsur_mo', 'usethiessen', 'usegqual', 'depwater', 'fcreach', 'trackruns', 'consqual', 'timestep', 'usehydromonfile', 'hydromonfile', 'calcioqcsqolim', 'monioqc', 'allowagwetp', 'impwashoff', 'zerodate', 'parentid'
       ),
       "map_generic_distro"=>array(
          'mapid', 'projectid', 'spreadid', 'landuseid', 'parentid', 'apprate', 'limpollutant'
       )
    );

    # this is a table containing the colums that should be updated in a parent-child relationship
    # generally speaking, these are data only columns.
    $child_columns = array(
       "subluparams"=>array(
          'luname','luarea','forest','lzsn','infilt','lsur', 'slsur','kvary','agwrc','petmax','petmin','infexp','infild','deepfr','basetp', 'agwetp','cepsc','uzsn','nsur','intfw','irc','lzetp','ceps','surs','uzs','ifws', 'lzs','agws','gwvs','subshedid','paramtype','ripbuffer','sqo','sqolim','retsc', 'stcofips','lucode','parentid', 'catcode', 'maxn', 'maxp', 'pct_nm', 'nm_planbase'
       ),
       "landuses"=>array(
          'luid', 'landuseid', 'projectid', 'landusetype', 'landuse', 'pct_impervious', 'lzetp', 'cepsc', 'wsqop', 'ioqc', 'aoqc', 'sqolim', 'hspflu', 'parentid'
       ),
       "monthlydistro"=>array(
          'distroid', 'spreadid', 'sourceid', 'sourcetype', 'projectid', 'landuseid', 'distroname', 'jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec', 'parentid'
       ),
       "sourcepollutants"=>array(
          'pollutantname', 'pollutantconc', 'storagedieoff', 'volatilization', 'concunits', 'conv', 'convunits', 'projectid', 'pollutanttype', 'starttime', 'duration', 'directfraction', 'comments'
       ),
       "sources"=>array(
          'sourceid', 'typeid', 'distrotype', 'projectid', 'sourcename', 'avgweight', 'parentid'
       ),
       "subshed"=>array(
          'sourcepop'
       ),
       "sourceloadtype"=>array(
          'typeid', 'sourcename', 'auweight', 'pollutantprod', 'produnits', 'pollutantconc', 'storagedieoff', 'concunits', 'conv', 'convunits', 'projectid', 'sourceclass', 'starttime', 'duration', 'directfraction', 'avgweight', 'parentid'
       ),
       "groupings"=>array(
          'grp_id', 'projectid', 'groupname', 'subwatersheds', 'grouptype', 'parentid'
       ),
       "hspf_globals"=>array(
          'globalid', 'projectid', 'ucifile', 'wdm1', 'wdm2', 'wdm3', 'wdm4', 'startdate', 'enddate', 'precip_wdm_id', 'evap_wdm_id', 'uzsn_mo', 'cepsc_mo', 'lzetp_mo', 'reach_wdm_id1', 'reachid1', 'reach_wdm_id2', 'reachid2', 'copyreaches', 'copysubsheds', 'useftablefile', 'ftablefile', 'if', 'ro', 'nsur_mo', 'usethiessen', 'usegqual', 'depwater', 'fcreach', 'trackruns', 'consqual', 'timestep', 'usehydromonfile', 'hydromonfile', 'calcioqcsqolim', 'monioqc', 'allowagwetp', 'impwashoff', 'zerodate', 'parentid'
       )
    );

    # file formats
    $file_formats = array(
       "p4eos"=>array(
          # line of first data entry (from 1 as 1st line)
          'dataline_number'=>7,
          'columns'=>'FL,SEG,LANDUSE,NH3,NO3,ORGN,TN,PO4,ORGP,TP,SED,ACRES',
          'row_format'=>'%5s%5s%15[^\n]%12f%12f%12f%12f%12f%12f%12f%12f%12f'
       ),
       "p4delivered"=>array(
          # line of first data entry (from 1 as 1st line)
          'dataline_number'=>5,
          'columns'=>'',
          'row_format'=>''
       )
    );

/* end adminsetup array */

?>
