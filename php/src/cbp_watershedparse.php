<?php

$noajax = 1;
$projectid = 3;
include_once('xajax_modeling.element.php');
//error_reporting(E_ALL);


# this $object_types array can contain setups for a variety of tables simultaneously, 
# so no need to overwrite if I dont want to
$object_types = array(
   'regionalba'=>array(
      'colname'=>'regionalba',
      'objects'=>array(
         'modelContainer'=>array(
            'objecttype'=>'modelContainer',
            # constant values are the same for every copy of this object
            'constants'=>array(
            ),
            # data map values are taken from the shape file record column values
            #    however, only 2 columns are ever available, and they are aliased in the query of the database
            # to be 'child_name' and 'parent_name'
            #    thus, the src_name must always be either 'child_name' or 'parent_name'
            # dest_name is the name of the model element property to set, 
            #    thus, it can be any valid property on the object class
            'datamap'=>array(
               array(
                  'src_name'=>'child_name',
                  'dest_name'=>'name'
               )
            ),
            # parent variables that child links to (format: array(parent_prop =>'', child_prop => '')
            'parent_links'=>array(
            ),
            # children of this object
            'addons'=>array(
               'dataConnectionObject'=>array(
                  'objecttype'=>'dataConnectionObject',
                  # constant values are the same for every copy of this object
                  'constants'=>array(
                     'host'=>'localhost',
                     'username'=>'postgres',
                     'password'=>'314159',
                     'dbname'=>'wsp',
                     'conntype'=>1,
                     'name'=>'VWUDS Database Connector',
                     'restrict_spatial'=>1,
                     'lat_col'=>'lat_dd',
                     'lon_col'=>'lon_dd',
                     'datecolumn'=>'thisdate',
                     'sql_query'=>"SELECT  mpid, lat_dd, lon_dd, thisdate, wd_mg, w_type, w_action, cat_mp, wd_mgd from vwuds_monthly_data WHERE w_type = 'SW' AND cat_mp <> 'PH' AND w_action = 'WL'"
                  ),
                  'datamap'=>array(
                  ),
                  # (format: array(parent_prop =>'', child_prop => '')
                  'parent_links'=>array(
                     array('parent_prop'=>'the_geom','child_prop'=>'the_geom')
                  )
               )
            )
         )
      )
   ),
   'river_basi'=>array(
      'colname'=>'river_basi',
      'objects'=>array(
         'modelContainer'=>array(
            'objecttype'=>'modelContainer',
            'constants'=>array(
            ),
            'datamap'=>array(
               array(
                  'src_name'=>'child_name',
                  'dest_name'=>'name'
               )
            ),
            'parent_links'=>array(
            ),
            'addons'=>array(
               'dataConnectionObject'=>array(
                  'objecttype'=>'dataConnectionObject',
                  'constants'=>array(
                     'host'=>'localhost',
                     'username'=>'postgres',
                     'password'=>'314159',
                     'dbname'=>'wsp',
                     'conntype'=>1,
                     'name'=>'VWUDS Database Connector'
                  ),
                  'datamap'=>array(
                  ),
                  'parent_links'=>array(
                     array('parent_prop'=>'the_geom','child_prop'=>'the_geom')
                  )
               )
            )
         )
      )
   ),
   'huc_8_digi'=>array(
      'colname'=>'huc_8_digi',
      'objects'=>array(
         'modelContainer'=>array(
            'objecttype'=>'modelContainer',
            'constants'=>array(
            ),
            'datamap'=>array(
               array(
                  'src_name'=>'child_name',
                  'dest_name'=>'name'
               )
            ),
            'parent_links'=>array(
            ),
            'addons'=>array(
               'dataConnectionObject'=>array(
                  'objecttype'=>'dataConnectionObject',
                  'constants'=>array(
                     'host'=>'localhost',
                     'username'=>'postgres',
                     'password'=>'314159',
                     'dbname'=>'wsp',
                     'conntype'=>1,
                     'name'=>'VWUDS Database Connector'
                  ),
                  'datamap'=>array(
                  ),
                  'parent_links'=>array(
                      array('parent_prop'=>'the_geom','child_prop'=>'the_geom')
                  )
               )
            )
         )
      )
   ),
   'station_nu'=>array(
      'colname'=>'station_nu',
      'objects'=>array(
         'ModelContainer'=>array(
            'objecttype'=>'modelContainer',
            'constants'=>array(
            ),
            'datamap'=>array(
               array(
                  'src_name'=>'child_name',
                  'dest_name'=>'name'
               )
            ),
            'parent_links'=>array(
               'the_geom'=>'the_geom'
            ),
            # children objects for this element
            'addons'=>array(
               'dataConnectionObject'=>array(
                  'objecttype'=>'dataConnectionObject',
                  'constants'=>array(
                     'host'=>'localhost',
                     'username'=>'postgres',
                     'password'=>'314159',
                     'dbname'=>'wsp',
                     'conntype'=>1,
                     'name'=>'VWUDS Database Connector',
                     'restrict_spatial'=>1,
                     'lat_col'=>'lat_dd',
                     'lon_col'=>'lon_dd',
                     'datecolumn'=>'thisdate',
                     'sql_query'=>"SELECT  mpid, lat_dd, lon_dd, thisdate, wd_mg, w_type, w_action, cat_mp, wd_mgd from vwuds_monthly_data WHERE w_type = 'SW' AND cat_mp <> 'PH' AND w_action = 'WL'"
                  ),
                  'datamap'=>array(
                  ),
                  'parent_links'=>array(
                     array('parent_prop'=>'the_geom','child_prop'=>'the_geom')
                  )
               ),
               'USGSGageObject'=>array(
                  'objecttype'=>'USGSGageObject',
                  'constants'=>array(
                     'sitetype'=>1
                  ),
                  'datamap'=>array(
                     0=>array(
                        'src_name'=>'child_name',
                        'dest_name'=>'staid'
                     )
                  ),
                  'parent_links'=>array(
                  )
               )
            )
         )
      )
   ),
   'watershed'=>array(
      'colname'=>'watershed',
      'objects'=>array(
         'modelContainer'=>array(
            'objecttype'=>'modelContainer',
            # constant values are the same for every copy of this object
            'constants'=>array(
            ),
            # data map values are taken from the shape file record column values
            #    however, only 2 columns are ever available, and they are aliased in the query of the database
            # to be 'child_name' and 'parent_name'
            #    thus, the src_name must always be either 'child_name' or 'parent_name'
            # dest_name is the name of the model element property to set, 
            #    thus, it can be any valid property on the object class
            'datamap'=>array(
               array(
                  'src_name'=>'child_name',
                  'dest_name'=>'name'
               )
            ),
            # parent variables that child links to (format: array(parent_prop =>'', child_prop => '')
            'parent_links'=>array(
            ),
            # children of this object
            'addons'=>array(
            )
         )
      )
   ),
   'minbas'=>array(
      'colname'=>'minbas',
      'objects'=>array(
         'modelContainer'=>array(
            'objecttype'=>'modelContainer',
            # constant values are the same for every copy of this object
            'constants'=>array(
            ),
            # data map values are taken from the shape file record column values
            #    however, only 2 columns are ever available, and they are aliased in the query of the database
            # to be 'child_name' and 'parent_name'
            #    thus, the src_name must always be either 'child_name' or 'parent_name'
            # dest_name is the name of the model element property to set, 
            #    thus, it can be any valid property on the object class
            'datamap'=>array(
               array(
                  'src_name'=>'child_name',
                  'dest_name'=>'name'
               )
            ),
            # parent variables that child links to (format: array(parent_prop =>'', child_prop => '')
            'parent_links'=>array(
            ),
            # children of this object
            'addons'=>array(
            )
         )
      )
   ),
   'rivername'=>array(
      'colname'=>'rivername',
      'objects'=>array(
         'modelContainer'=>array(
            'objecttype'=>'modelContainer',
            # constant values are the same for every copy of this object
            'constants'=>array(
            ),
            # data map values are taken from the shape file record column values
            #    however, only 2 columns are ever available, and they are aliased in the query of the database
            # to be 'child_name' and 'parent_name'
            #    thus, the src_name must always be either 'child_name' or 'parent_name'
            # dest_name is the name of the model element property to set, 
            #    thus, it can be any valid property on the object class
            'datamap'=>array(
               array(
                  'src_name'=>'child_name',
                  'dest_name'=>'name'
               )
            ),
            # parent variables that child links to (format: array(parent_prop =>'', child_prop => '')
            'parent_links'=>array(
            ),
            # children of this object
            'addons'=>array(
            )
         )
      )
   ),
   'catcode2'=>array(
      'colname'=>'catcode2',
      'objects'=>array(
         'ModelContainer'=>array(
            'objecttype'=>'modelContainer',
            'constants'=>array(
            ),
            'datamap'=>array(
               array(
                  'src_name'=>'child_name',
                  'dest_name'=>'name'
               )
            ),
            'parent_links'=>array(
               'the_geom'=>'the_geom'
            ),
            # children objects for this element
            'addons'=>array(
               'USGSChannelGeomObject'=>array(
                  'objecttype'=>'USGSChannelGeomObject',
                  'constants'=>array(
                     'sitetype'=>1
                  ),
                  'datamap'=>array(
                     0=>array(
                        'src_name'=>'contrib_area_sqmi',
                        'dest_name'=>'drainage_area',
                        'src'=>'table', # may be self, parent, or the key-name of a sibling add-on
                        'dest'=>'self' # may be self, parent, or the key-name of a sibling add-on
                     ),
                     1=>array(
                        'src_name'=>'channel_length_ft',
                        'dest_name'=>'length',
                        'src'=>'table', # may be self, parent, or the key-name of a sibling add-on
                        'dest'=>'self' # may be self, parent, or the key-name of a sibling add-on
                     ),
                     2=>array(
                        'src_name'=>'Qout',
                        'dest_name'=>'Qin',
                        'src'=>'HSPFContainer', # may be self, parent, or the key-name of a sibling add-on
                        'dest'=>'self' # may be self, parent, or the key-name of a sibling add-on
                     )
                  ),
                  'parent_links'=>array(
                  )
               ),
               'HSPFContainer'=>array(
                  'objecttype'=>'HSPFContainer',
                  'constants'=>array(
                     'sitetype'=>1
                  ),
                  'datamap'=>array(
                     0=>array(
                        'src_name'=>'uciname',
                        'dest_name'=>'filepath',
                        'src'=>'table', # may be self, parent, or the key-name of a sibling add-on
                        'dest'=>'self' # may be self, parent, or the key-name of a sibling add-on
                     ),
                     1=>array(
                        'src_name'=>'channel_length_ft',
                        'dest_name'=>'length',
                        'src'=>'table', # may be self, parent, or the key-name of a sibling add-on
                        'dest'=>'self' # may be self, parent, or the key-name of a sibling add-on
                     )
                  ),
                  'parent_links'=>array(
                  )
               )
            )
         )
      )
   )
);


/*
# USGS framework
$tablename = 'va_drainage_dd';
$containment_columns = array('regionalba', 'river_basi', 'huc_8_digi', 'station_nu');
$src_props = array('Qout');
$dest_props = array('Qin');
*/

# CBP model framework
$tablename = 'sc_cbp5';
$containment_columns = array('watershed', 'minbas', 'rivername', 'catcode2');
$src_props = array('Qout');
$dest_props = array('Qin');
$extrawhere = " catcode2 in (select catcode2 from sc_rivanna) ";
$aextrawhere = " a.catcode2 in (select catcode2 from sc_rivanna) ";
$bextrawhere = " b.catcode2 in (select catcode2 from sc_rivanna) ";
#$extrawhere = " (1 = 1) ";

$allcolumns = '"' . join ('","', $containment_columns) . '"';
$f_node_col = 'f_node';
$t_node_col = 't_node';
$scenarioid = 12;
$delete_old = 1;
$userid = 1;
$geomcol = 'the_geom';
$geomtype = 3;
# whether we should create geometry for upper levels of containment
$union_geoms = 0;
# whether we should capture geometry for the bottom level of element
$child_geoms = 1;

if ($delete_old) {
   $listobject->querystring = "  delete from scen_model_element where scenarioid = $scenarioid ";
   $listobject->performQuery();
   $listobject->querystring = "  delete from map_model_linkages where scenarioid = $scenarioid ";
   $listobject->performQuery();
   $listobject->querystring = "  select max(elementid) as maxel from scen_model_element ";
   $listobject->performQuery();
   $maxel = intval($listobject->getRecordValue(1,'maxel')) + 1;
   $listobject->querystring = " select setval('scen_model_element_elementid_seq', $maxel) ";
   print("$listobject->querystring ; <br>");
   $listobject->performQuery();
   
}

$listobject->queryrecords = array();

# how to store this thing?  Create all objects in the first query and stick them in the database
# then, iteratively go through, create the successive levels of containment, and stick them in there and establish links 
# as they traverse containers on the current level
# i.e.,
# 1. create all 1st level objects (catcode2 in this case)
# 2. Create all 2nd level objects / 1st level containers (rivernames in this case)

$toplevel = max(array_keys($containment_columns));
$bottomlevel = min(array_keys($containment_columns));
for ($n = $bottomlevel; $n <= $toplevel; $n++ ) {
   
   # select and create the containers on this level, then iterate through and select and create the 
   # ones below this container -- this is recursion?
   # so long as n >= 1 we can use the following formulas: (use a different formula if there are links at the n = 0 level )
   # C = container
   # baselevel = the most distinct object representing a sub-watershed
   # current level = the current level of containment
   # containing level = the level above the current containment level
   # C[t] = base level
   $baselevel = $containment_columns[$toplevel];
   # C[n] =  current level = the name of the column that we are currently focused on
   $currentlevel = $containment_columns[$n];
   # C[n-1] =  containing level 
   $containinglevel = $containment_columns[$n - 1];
   
   # pointer to this levels containers
   $cn = $n - 1;
   # place to stash containers
   # initialize this each time, since we don't need anything above the current container
   # AND we want to make sure that we don't have two containers with the same name
   $containers_created[$n] = array();
   
   $cname = $currentlevel;
   $pname = $containinglevel;
   # obtain children  at this level
   $cquery = "  select \"$cname\" as child_name, ";
   if ($pname <> '') {
      $cquery .= " \"$pname\" as parent_name ";
   } else {
      $cquery .= " '' as parent_name ";
   }
   if ($child_geoms and ($currentlevel == $baselevel)) {
      $cquery .= ", asText(\"$geomcol\") as \"$geomcol\" ";
   }
   $cquery .= " from $tablename ";
   $cquery .= "WHERE " . $extrawhere;
   $cquery .= " group by child_name, parent_name ";
   if ($child_geoms and ($currentlevel == $baselevel)) {
      $cquery .= ", \"$geomcol\" ";
   }
   $listobject->querystring = $cquery;
   print("Get Children of this parent:" . $listobject->querystring . " ; <br>");
   $listobject->performQuery();
   $precs = $listobject->queryrecords;

   foreach ($precs as $thiscont) {
      $parent_name = $thiscont['parent_name'];
      $child_name = $thiscont['child_name'];
      //print("Looking for $parent_name in " . print_r(array_keys($containers_created[$cn]),1) . " ... ");
      if (in_array($parent_name, array_keys($containers_created[$cn]))) {
         $parentcontainer = $containers_created[$cn][$parent_name]['elementid'];
         print("Found $parent_name <br>" );
      } else {
         $parentcontainer = -1;
         print("Could not find $parent_name <br>" );
      }
      foreach($object_types[$currentlevel]['objects'] as $this_type) {
         # set up the initial values for these objects
         $cvalues = array(
            'activecontainerid'=>$parentcontainer,
            'scenarioid'=>$scenarioid,
            'newcomponenttype'=>$this_type['objecttype'],
            'name'=>$child_name
         );
         $datamaps = $this_type['datamap'];
         foreach ($datamaps as $thismap) {
            if (!isset($thiscont[$thismap['src_name']])) {
               # fetch it from the table
               $mquery = "  select " . $thismap['src_name'];
               $mquery .= " from $tablename ";
               $mquery .= " where \"$cname\" = '$child_name' ";
               if ($pname <> '') {
                  $mquery .= " and \"$pname\" = '$parent_name'  ";
               } 
               if ($debug) {
                  print("$mquery ; <br>");
               }
               $listobject->querystring = $mquery;
               $listobject->performQuery();
               $mapval = $listobject->getRecordValue(1,$thismap['src_name']);
            } else {
               $mapval = $thiscont[$thismap['src_name']];
            }
            $cvalues[$thismap['dest_name']] = $mapval;
         }
         $constants = $this_type['constants'];
         foreach ($constants as $key => $val) {
            $cvalues[$key] = $val;
         }
         if (isset($thiscont[$geomcol])) {
            $cvalues['the_geom'] = $thiscont[$geomcol];
            $cvalues['geomtype'] = $geomtype;
         }
         $debug = 0;
         $feedback = insertBlankComponent($cvalues);
         $debug = 0;
         print("Creation routine input:" . print_r(array_keys($cvalues),1) . " <br>");
         print("Creation routine output:" . $feedback . " <br>");
         $listobject->querystring = "SELECT currval('scen_model_element_elementid_seq') ";
         print("Get parent ID:" . $listobject->querystring . " ; <br>");
         $listobject->performQuery();
         $listobject->show = 0;
         #$innerHTML .= "$listobject->outstring <br>";
         $newelid = $listobject->getRecordValue(1,'currval');
         $containers_created[$n][$child_name]['elementid'] = $newelid;
         
         foreach (array_keys($this_type['addons']) as $thischild_key) {
            # set up the initial values for these child objects
            $thischild = $this_type['addons'][$thischild_key];
            $cvalues = array(
               'activecontainerid'=>$newelid,
               'scenarioid'=>$scenarioid,
               'newcomponenttype'=>$thischild['objecttype']
            );
            $datamaps = $thischild['datamap'];
            foreach ($datamaps as $thismap) {
               if (!isset($thiscont[$thismap['src_name']])) {
                  # fetch it from the table
                  $mquery = "  select " . $thismap['src_name'];
                  $mquery .= " from $tablename ";
                  $mquery .= " where \"$cname\" = '$child_name' ";
                  if ($pname <> '') {
                     $mquery .= " and \"$pname\" = '$parent_name'  ";
                  } 
                  #if ($debug) {
                     print("$mquery ; <br>");
                  #}
                  $listobject->querystring = $mquery;
                  $listobject->performQuery();
                  $mapval = $listobject->getRecordValue(1,$thismap['src_name']);
               } else {
                  $mapval = $thiscont[$thismap['src_name']];
               }
               $cvalues[$thismap['dest_name']] = $mapval;
            }
            $constants = $thischild['constants'];
            foreach ($constants as $key => $val) {
               $cvalues[$key] = $val;
            }
            $debug = 0;
            $feedback = insertBlankComponent($cvalues);
            $debug = 0;
            print("Creation routine input:" . print_r(array_keys($cvalues),1) . " <br>");
            print("Creation routine output:" . $feedback . " <br>");
            $listobject->querystring = "SELECT currval('scen_model_element_elementid_seq') ";
            $listobject->performQuery();
            $listobject->show = 0;
            #$innerHTML .= "$listobject->outstring <br>";
            $childid = $listobject->getRecordValue(1,'currval');
            $parent_links = $thischild['parent_links'];
            foreach ($parent_links as $thislink) {
               $src_prop = $thislink['parent_prop'];
               $dest_prop = $thislink['child_prop'];
               createObjectLink($projectid, $scenarioid, $newelid, $childid, 2, $src_prop, $dest_prop);
            }
            
         }
      }
   }
   
   # do linkages if we have the information to do this (t_node and f_node columns ,must be degined)
   if ( ($t_node_col <> '') and ($f_node_col <> '') ) {
      # C[n-t] =  topmost level level where t is equal to the number of total containment levels
      # this selects all cross-container linkages at this level
      $lquery = "  select a.\"$containinglevel\" as from_container, b.\"$containinglevel\" as to_container, ";
      $lquery .= "    a.\"$currentlevel\" as from_object, b.\"$currentlevel\" as to_object ";
      $lquery .= " from $tablename as a, $tablename as b ";
      # the current level should NOT match
      $lquery .= " WHERE a.\"$containinglevel\" <> b.\"$containinglevel\" ";
      $lquery .= "    AND a.\"$t_node_col\" = b.\"$f_node_col\" ";
      $lquery .= "    AND $aextrawhere ";
      $lquery .= "    AND $bextrawhere ";
      # iterate through all levels above this one, they should match, 
      for ($j = ($n - 2); $j >= 0; $j--) {
         $hierlevel = $containment_columns[$j];
         $lquery .= "    AND a.\"$hierlevel\" =  b.\"$hierlevel\" ";
      }
      print($lquery . " ; <br>");
      $listobject->querystring = $lquery;
      $listobject->performQuery();
      $clrecs = $listobject->queryrecords;
      print("Creating cross-container linkages at the $pname level<br>");
      foreach ($clrecs as $thisrec) {
         $to_object = $thisrec['to_object'];
         $from_object = $thisrec['from_object'];
         $from_container = $thisrec['from_container'];
         $to_container = $thisrec['to_container'];

         $from_parentid = $containers_created[$cn][$from_container]['elementid'];
         $to_parentid = $containers_created[$cn][$to_container]['elementid'];
         $from_objectid = $containers_created[$n][$from_object]['elementid'];
         $to_objectid = $containers_created[$n][$to_object]['elementid'];
         # create link from FROMchild to FROMparent container
         # create link from FROMparent container to TOParentcontainer
         # create link from TOparent container to TOChild object
         # repeat it for as many properties as we are instructed to link
         $sp = $src_props;
         $dp = $dest_props;
         while(count($sp) > 0) {
            $src_prop = array_pop($sp);
            $dest_prop = array_pop($dp);
            print("Linking $from_container ($from_parentid) ($from_object($from_objectid)) to $to_container ($to_parentid) ($to_object($to_objectid)) <br>");
            # FROMchild to FROMparent
            $listobject->querystring = "  insert into map_model_linkages (projectid, scenarioid, src_prop, dest_prop, ";
            $listobject->querystring .= "    src_id, dest_id, linktype) ";
            $listobject->querystring .= " values ($projectid, $scenarioid, '$src_prop', '$src_prop', $from_objectid, $from_parentid, 2) ";
            #if ($debug) {
               $innerHTML .= "$listobject->querystring<br>";
            #}
            $listobject->performQuery();
            # FROMparent to TOParent
            $listobject->querystring = "  insert into map_model_linkages (projectid, scenarioid, src_prop, dest_prop, ";
            $listobject->querystring .= "    src_id, dest_id, linktype) ";
            $listobject->querystring .= " values ($projectid, $scenarioid, '$src_prop', '$dest_prop', $from_parentid, $to_parentid, 2) ";
            #if ($debug) {
               $innerHTML .= "$listobject->querystring<br>";
            #}
            $listobject->performQuery();
            # TOparent to TOChild
            $listobject->querystring = "  insert into map_model_linkages (projectid, scenarioid, src_prop, dest_prop, ";
            $listobject->querystring .= "    src_id, dest_id, linktype) ";
            $listobject->querystring .= " values ($projectid, $scenarioid, '$dest_prop', '$dest_prop', $to_parentid, $to_objectid, 2) ";
           # if ($debug) {
               $innerHTML .= "$listobject->querystring<br>";
           # }
            $listobject->performQuery();
         }
      }
   }
   
   
   # Now that we have established all of the objects, and the cross-container linkages, we can go ahead and 
   # establish all the within-container linkages
   
   # do linkages if we have the information to do this (t_node and f_node columns ,must be degined)
   if ( ($t_node_col <> '') and ($f_node_col <> '') ) {
      # C[n-t] =  topmost level level where t is equal to the number of total containment levels
      # this selects all cross-container linkages at this level
      $lquery = "  select a.\"$containinglevel\" as from_container, b.\"$containinglevel\" as to_container, ";
      $lquery .= "    a.\"$currentlevel\" as from_object, b.\"$currentlevel\" as to_object ";
      $lquery .= " from $tablename as a, $tablename as b ";
      # the current level should NOT match
      $lquery .= " WHERE a.\"$containinglevel\" = b.\"$containinglevel\" ";
      $lquery .= "    AND a.\"$t_node_col\" = b.\"$f_node_col\" ";
      $lquery .= "    AND a.\"$currentlevel\" <> b.\"$currentlevel\" ";
      $lquery .= "    AND $aextrawhere ";
      $lquery .= "    AND $bextrawhere ";
      # iterate through all levels above this one, they should match, 
      for ($j = ($n - 2); $j >= 0; $j--) {
         $hierlevel = $containment_columns[$j];
         $lquery .= "    AND a.\"$hierlevel\" =  b.\"$hierlevel\" ";
      }
      print($lquery . " ; <br>");
      $listobject->querystring = $lquery;
      $listobject->performQuery();
      $clrecs = $listobject->queryrecords;
      print("Creating within-container linkages at the $pname level<br>");
      foreach ($clrecs as $thisrec) {
         $to_object = $thisrec['to_object'];
         $from_object = $thisrec['from_object'];
         $from_container = $thisrec['from_container'];
         $to_container = $thisrec['to_container'];

         $from_parentid = $containers_created[$cn][$from_container]['elementid'];
         $to_parentid = $containers_created[$cn][$to_container]['elementid'];
         $from_objectid = $containers_created[$n][$from_object]['elementid'];
         $to_objectid = $containers_created[$n][$to_object]['elementid'];
         # create link from FROMchild to FROMparent container
         # create link from FROMparent container to TOParentcontainer
         # create link from TOparent container to TOChild object
         # repeat it for as many properties as we are instructed to link
         $sp = $src_props;
         $dp = $dest_props;
         while(count($sp) > 0) {
            $src_prop = array_pop($sp);
            $dest_prop = array_pop($dp);
            print("Linking $from_object($from_objectid) to $to_object($to_objectid) <br>");
            # FROMchild to FROMparent
            $listobject->querystring = "  insert into map_model_linkages (projectid, scenarioid, src_prop, dest_prop, ";
            $listobject->querystring .= "    src_id, dest_id, linktype) ";
            $listobject->querystring .= " values ($projectid, $scenarioid, '$src_prop', '$dest_prop', $from_objectid, $to_objectid, 2) ";
            #if ($debug) {
               print( "$listobject->querystring<br>");
            #}
            $listobject->performQuery();
         }
      }
   }
}

?>