<?php

$noajax = 1;
$projectid = 3;
$target_scenarioid = 14;
# branch outlets
# North Fork New River 9170
# New River 8051
# North Branch Potomac 3930
# Monocacy 4040
# Occoquan 5250
# Shenandoah 4370
$outlet = '4370';

include_once('xajax_modeling.element.php');
error_reporting(E_ERROR);
if (!($userid == 1)) {
   # user must be logged in, and must be administrator account
   print("Unauthorized access to this routine - user must be logged in, and must be administrator account<br>");
   die;
}

function createBranch($projectid, $listobject, $scenarioid, $tablename, $containername_col, $componentname_col, $tnode_col, $fnode_col, $outletnode=-1, $criteria=array(), $object_types, $debug) {
   
   $trib_links = $object_types['trib_links'];
   $outlet_links = $object_types['outlet_links'];
   
   $branchsegs = array();
   # if outlet node is -1, find the outlet
   if ($outletnode == -1) {
      $tnode_result = getTerminalNode($listobject, $tablename, $componentname_col, $tnode_col, $fnode_col, $criteria = array(), $debug = 0);
      $outletnode = $tnode_result['segments'][0]; # just grab a single one, if there are more than one, well, we have troubles
   }
   //print_r($tnode_result);
   
   if (count($criteria) > 0) {
      # check to see if there are values set here, if so, we use them, otherwise, if none has a non '' value, query the table
      $allnull = 1;
      foreach ($criteria as $thiskey => $thisval) {
         if ($thisval <> '') {
            $allnull = 0;
         }
      }
      if ($allnull) {
         $cs = "\"" . join(",\",\"", array_keys($criteria)) . "\"";
         
         $listobject->querystring = " select $cs from $tablename where $fnode_col = '$outletnode' ";
         //print("$listobject->querystring ; <br>");
         $listobject->performQuery();
         $criteria = $listobject->queryrecords[0];
      }
   }
   $listobject->querystring = " select \"$containername_col\" from $tablename where $fnode_col = '$outletnode' ";
   # get container name
   print("$listobject->querystring ; <br>");
   $listobject->performQuery();
   $container_name = $listobject->getRecordValue(1,$containername_col);
   # create the container
   $cont_crit = array();
   $cont_crit[$fnode_col] = $outletnode;
   print("Object Info " . print_r(array_keys($object_types[$containername_col]['object_specs']),1) . " <br>");
   $containerid = createObject($projectid, $listobject, $object_types[$containername_col], $tablename, $cont_crit, $scenarioid);
   print("Container type $containername_col - $container_name created with elementid $containerid <br>");
   
   print("Checking branch: " . print_r($criteria,1));
   
   $allnodes_result = getBranchNodes($listobject, $tablename, $componentname_col, $tnode_col, $fnode_col, $outletnode, $criteria, $debug);
   print("<br>Nodes in this branch (" . $allnodes_result['info'] . "): ");
   print_r($allnodes_result['segments']);
   $branch_nodes = $allnodes_result['segments'];
   # place to stash branch object IDs to prevent duplication, and to facilitate linkage creation
   $branch_objects = array();
   print("<br>Iterating through branches " . print_r($branch_nodes, 1));
   print("<ul>");
   foreach ($branch_nodes as $thisnode) {
      # create an element
      //$newid = createComponent($comp_props, $debug);
      # stash the ID in an array
      //$map[$thisnode] = $newid;
      # get tribs to this node
      print("<li>Checking node $thisnode for tribs: ");
      
      // for this, we replace $criteria with $blank_criteria, an empty array, because we want to get ANY trib that comes 
      // in to this node from one step upstream - 
      $blank_criteria = array();
      $node_tribs = getSegList($listobject, $tablename, $componentname_col, $tnode_col, $fnode_col, $thisnode, 1, $blank_criteria, $debug);
      print_r($node_tribs['segments']);
      // iterate through tribs, if there are any upstream tribs that are NOT in this branch
      foreach ($node_tribs['segments'] as $this_trib) {
         if (!in_array($this_trib, $branch_nodes)) {
            print("<br>$this_trib is not in branch" . print_r($branch_nodes,1) . ", will have to recurse<br>");
            $open_criteria = array();
            foreach (array_keys($criteria) as $thiscrit) {
               $open_criteria[$thiscrit] = '';
            }
            print("Open Criteria set to: " . print_r($open_criteria,1) . "<br>");
            if (!in_array($this_trib, array_keys($branch_objects))) {
               $trib_id = createBranch($projectid, $listobject, $scenarioid, $tablename, $containername_col, $componentname_col, $tnode_col, $fnode_col, $this_trib, $open_criteria, $object_types, $debug);
               $branch_objects[$this_trib] = $trib_id;
            } else {
               $trib_id = $branch_objects[$this_trib];
            }
         } else {
            array_push($branchsegs, $this_trib);
            $trib_crit = array();
            $trib_crit[$fnode_col] = $this_trib;
            if (!in_array($this_trib, array_keys($branch_objects))) {
               $trib_id = createObject($projectid, $listobject, $object_types[$componentname_col], $tablename, $trib_crit, $scenarioid);
            } else {
               $trib_id = $branch_objects[$this_trib];
            }
            # stash this trib in the folder
            $branch_objects[$this_trib] = $trib_id;
         }
         # since trib is outside of branch, but it flows into a node in this branch, we keep it as a node in this container
         # since this node is part of this branch, the parent is the branch container
         $out = createObjectLink($projectid, $scenarioid, $trib_id, $containerid, 1);
         print("Linked $this_trib ($trib_id) to Branch Container $containerid ::" . $out['innerHTML'] . "<br>");
         # create trib property linkage(s) to its downstream node if they are requested
         # only process this if the trib is NOT equal to the node, this is because the tibnode routine returns 
         # the destination node as well
         if ($this_trib <> $thisnode) {
            foreach ($trib_links as $thislink) {
               $out = createObjectLink($projectid, $scenarioid, $trib_id, $branch_objects[$thisnode], 2, $thislink['src_prop'], $thislink['dest_prop']);
               print("Creating trib link from $trib_id (" . $thislink['src_prop'] . ") to " . $branch_objects[$thisnode] . " (" .  $thislink['dest_prop'] . ")<br>");
            }
            updateObjectPropList($branch_objects[$thisnode]);
         }
      }
      
   }
   print("<li>Finished Checking: " . print_r($criteria,1) . " Found " . print_r($branchsegs,1));
   print("</ul>");
   
   # now, create any linkages from the outlet to the container, so that the container will display 
   # as if it were the outlet - in other words, these are just "pass-thru" links, and so generally will have the 
   # form that src_prop_name = dest_prop_name
   print("Linking outlet properties from $outletnode to $containerid ");
   foreach ($outlet_links as $thislink) {
      $out = createObjectLink($projectid, $scenarioid, $branch_objects[$outletnode], $containerid, 2, $thislink['src_prop'], $thislink['dest_prop']);
      print($out['innerHTML'] . "<br>");
   }
   updateObjectPropList($containerid);
   
   return $containerid;
}


function createObject($projectid, $listobject, $object_def, $tablename, $criteria, $target_scenarioid) {
   # $object_specs has
   #    - objects
   #        objects has:
   #        - constants
   #        - data_map (things from the source data table to set as properties on each object)
   #        - parent_links (linkages from add-ons to container, or from this object to its parent)
   #        - addons (component objects to be added below this object)
   # $criteria has the column matches needed to identify the unique table row source for this object
   # iterate through all component objects in this type\
   $object_specs = $object_def['object_specs'];
   # get criteria for unique record row query
   $whereclause = "(1 = 1)";
   foreach ($criteria as $thiskey => $thisval) {
      $whereclause .= " AND \"$thiskey\" = '$thisval'";
   }
   
   print("Create object called");

   # get the object type to create
   $ctype_name = $object_specs['objecttype'];
   # set up the initial values for these objects
   # all constants and datamaps go into the cvalues array
   $cvalues = array(
      'activecontainerid'=>$parentcontainer,
      'scenarioid'=>$target_scenarioid,
      'newcomponenttype'=>$object_specs['objecttype']
   );
   # get named data maps
   $datamaps = $object_specs['datamap'];
   # check to see if we want to clone an existing object set as our template, 
   # this will in effect over-ride the object type setting, since the clone will be the same
   # type as the source object
   $template_id = -1;
   if (isset($object_specs['template_id'])) {
      if ($object_specs['template_id'] > 0) {
         $template_id = $object_specs['template_id'];
      }
   }
   print("Setting up data maps ... ");
   # if we have data maps, we should assemble a query and retrieve them now
   if (count($datamaps) > 0) {
      $mquery = "  select";
      $mdel = '';
      foreach ($datamaps as $thismap) {
         $mquery .= $mdel . " \"" . $thismap['src_name'] . "\"";
         $mdel = ',';
      }
      $mquery .= " from $tablename ";
      $mquery .= " where $whereclause ";
      $listobject->querystring = $mquery;
      print("$mquery ; <br>");
      $listobject->performQuery();
      foreach ($datamaps as $thismap) {
         $cvalues[$thismap['dest_name']] = $listobject->getRecordValue(1,$thismap['src_name']);
      }

   }
   print("Setting up constants ... ");
   # now, set up any constants
   $constants = $object_specs['constants'];
   foreach ($constants as $key => $val) {
      $cvalues[$key] = $val;
   }
   if (isset($thiscont[$geomcol])) {
      //$cvalues['the_geom'] = $thiscont[$geomcol];
      //$cvalues['geomtype'] = $geomtype;
   }
   $debug = 0;
   $sub_objects = array();
   print("Data for new object " . print_r($cvalues,1) . "<br>");
   print("creating object ... ");
   if ($template_id > 0) {
      print("Trying to clone the group: $template_id <br>");
      $listobject->querystring = "  select scenarioid from scen_model_element where elementid = $template_id ";
      $listobject->performQuery();
      $src_scen = $listobject->getRecordValue(1,'scenarioid');
      $clonedata = array(
         'elements'=>array($template_id),
         'scenarioid'=>$src_scen,
         'projectid'=>$projectid,
         'dest_scenarioid'=>$target_scenarioid
      );
      $copyresult = copyModelGroupResult($clonedata);
      $feedback = $copyresult['innerHTML'];
      print("Creation routine output:" . $feedback . " <br>");
      $sub_objects = $copyresult['element_map'];
      print("Group has been cloned with the following map of id values:" . print_r($sub_objects,1) . "<br>");
      $newelid = $sub_objects[$template_id]['new_id'];
      print("Attempting to update container $newelid with variables:" . print_r(array_keys($cvalues),1) . "<br>");
      error_log("Attempting to update container $newelid with variables:" . print_r(array_keys($cvalues),1) . "<br>");
      $obres = updateObjectProps($projectid, $newelid, $cvalues);
      $feedback = $obres['innerHTML'] . "<hr>";
      #$feedback .= $obres['debugHTML'] . "<hr>";
      print("Update routine output:" . $feedback . " <br>");
   } else {
      $feedback = insertBlankComponent($cvalues);
      print("Creation routine output:" . $feedback . " <br>");
      $listobject->querystring = "SELECT currval('scen_model_element_elementid_seq') ";
      print("Get parent ID:" . $listobject->querystring . " ; <br>");
      $listobject->performQuery();
      $listobject->show = 0;
      #$innerHTML .= "$listobject->outstring <br>";
      $newelid = $listobject->getRecordValue(1,'currval');
   }
   //$debug = 0;
   print("Finished processing " . $object_specs['objecttype'] . " ($newelid)");
   # add any addons, by calling this routine recursively
   foreach (array_keys($object_specs['addons']) as $thischild_key) {
      # set up the initial values for these child objects
      $thischild = $object_specs['addons'][$thischild_key];
      $template_id = -1;
      if (isset($thischild['template_id'])) {
         if ($thischild['template_id'] > 0) {
            $template_id = $thischild['template_id'];
         }
         # now, check to see if the parent of this object was actually cloned succesfully, and if the 
         # corresponding child object exists - if not, we will insert a blank component and hope for the best
         print("Checking to see if this child has already been created in a group clone.<br>");
         if (!in_array($template_id, array_keys($sub_objects))) {
            $template_id = -1;
         } else {
            print("ID $template_id found in result of group clone.<br>");
         }
      }
      if ($template_id == -1) {
         $childid = createObject($projectid, $listobject, $thischild, $tablename, $criteria, $target_scenarioid);
      } else {
         $childid = $sub_objects[$template_id]['new_id'];
         $addon_cvalues = array();
         # need to copy the add on properties for this and update the object
         # if we have data maps, we should assemble a query and retrieve them now
         if (count($thischild['datamap']) > 0) {
            $mquery = "  select";
            $mdel = '';
            foreach ($thischild['datamap'] as $thismap) {
               $mquery .= $mdel . " \"" . $thismap['src_name'] . "\"";
               $mdel = ',';
            }
            $mquery .= " from $tablename ";
            # whereclause from the parent group is still applicable
            $mquery .= " where $whereclause ";
            $listobject->querystring = $mquery;
            print("$mquery ; <br>");
            $listobject->performQuery();
            foreach ($thischild['datamap'] as $thismap) {
               $addon_cvalues[$thismap['dest_name']] = $listobject->getRecordValue(1,$thismap['src_name']);
            }
            $obres = updateObjectProps($projectid, $childid, $addon_cvalues);
            print($obres['innerHTML'] . "<hr>");
         }
         print("Setting up constants ... ");
         # now, set up any constants
         $addon_constants = $thischild['constants'];
         foreach ($constants as $key => $val) {
            $addon_cvalues[$key] = $val;
         }
         if (isset($thiscont[$geomcol])) {
            //$addon_cvalues['the_geom'] = $thiscont[$geomcol];
            //$addon_cvalues['geomtype'] = $geomtype;
         }
         print("Data for ADD-ON object " . print_r($addon_cvalues,1) . "<br>");
      }
      $parent_links = $thischild['parent_links'];
      foreach ($parent_links as $thislink) {
         $src_prop = $thislink['src_name'];
         $dest_prop = $thislink['dest_name'];
         $src = $thislink['src'];
         switch ($src) {
            case 'self':
               print("$src LINK: Linking $src_prop from $thischild_key to $dest_prop on $child_name <br>");
               error_log("$src LINK: Linking $src_prop from $thischild_key to $dest_prop on $child_name <br>");
               $out = createObjectLink($projectid, $target_scenarioid, $childid, $newelid, 2, $src_prop, $dest_prop);
            break;

            case 'parent':
               print("$src LINK: Linking $src_prop from $child_name to $dest_prop on $thischild_key <br>");
               error_log("$src LINK: Linking $src_prop from $child_name ($newelid) to $dest_prop on $thischild_key ($childid)<br>");
               $out = createObjectLink($projectid, $target_scenarioid, $newelid, $childid, 2, $src_prop, $dest_prop);
            break;
         }
         print($out['innerHTML'] . "<br>");
      }

   }

   # update my properties before returning
   updateObjectPropList($newelid);
   
   return $newelid;

}


# this $object_types array can contain setups for a variety of tables simultaneously, 
# so no need to overwrite if I dont want to
$object_types = array(
   'trib_links'=>array(
      0=>array('src_prop'=>'Qout', 'dest_prop'=>'Qin'),
      1=>array('src_prop'=>'OVOL', 'dest_prop'=>'OVOL_UP'),
      2=>array('src_prop'=>'OHEAT', 'dest_prop'=>'OHEAT_UP'),
      3=>array('src_prop'=>'Uout', 'dest_prop'=>'Uin')
   ),
   'outlet_links'=>array(
      0=>array('src_prop'=>'Qout', 'dest_prop'=>'Qout'),
      1=>array('src_prop'=>'OVOL', 'dest_prop'=>'OVOL'),
      2=>array('src_prop'=>'OHEAT', 'dest_prop'=>'OHEAT'),
      3=>array('src_prop'=>'Uout', 'dest_prop'=>'Uout')
   ),
   'rivername'=>array(
      'object_specs'=>array(
         'objecttype'=>'modelContainer',
         # constant values are the same for every copy of this object
         'constants'=>array(
            'starttime'=>'1999-10-01',
            'endtime'=>'1999-10-31',
            'dt'=>43200
         ),
         # data map values are taken from the shape file record column values
         # these MUST be valid column names in the source table
         'datamap'=>array(
            array(
               'src_name'=>'rivername',
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
   ),
   'catcode2'=>array(
      'object_specs'=>array(
         'objecttype'=>'modelContainer',
         'template_id'=>15714,
         'constants'=>array(
            'starttime'=>'1999-10-01',
            'endtime'=>'1999-10-31',
            'dt'=>43200
         ),
         'datamap'=>array(
            0=>array(
               'src_name'=>'catcode2',
               'dest_name'=>'name'
            ),
            1=>array(
               'src_name'=>'the_geom',
               'dest_name'=>'poly_geom'
            )
         ),
         'parent_links'=>array(
            #'the_geom'=>'the_geom'
         ),
         # children objects for this element
         'addons'=>array(
            'USGSChannelGeomObject'=>array(
               'objecttype'=>'USGSChannelGeomObject',
               'template_id'=>15721,
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
                     'src_name'=>'local_area_sqmi',
                     'dest_name'=>'area',
                     'src'=>'table', # may be self, parent, or the key-name of a sibling add-on
                     'dest'=>'self' # may be self, parent, or the key-name of a sibling add-on
                  )
               ),
               'parent_links'=>array(
                  0=>array(
                  )
               )
            ),
            'CBPDataConnection'=>array(
               'objecttype'=>'HSPFContainer',
               'template_id'=>15720,
               'constants'=>array(
                  'scid'=>2,
                  'id1'=>'river',
                  'datecolumn'=>'thisdate',
                  'description'=>'from a cloned parent group'
               ),
               'datamap'=>array(
                  0=>array(
                     'src_name'=>'catcode2',
                     'dest_name'=>'id2',
                     'src'=>'table', # may be self, parent, or the key-name of a sibling add-on
                     'dest'=>'self' # may be self, parent, or the key-name of a sibling add-on
                  )
               ),
               'parent_links'=>array(
                  0=>array(
                     'src_name'=>'OVOL_UP',
                     'dest_name'=>'OVOL_UP',
                     'src'=>'parent'
                  ),
                  1=>array(
                     'src_name'=>'OHEAT_UP',
                     'dest_name'=>'OHEAT_UP',
                     'src'=>'parent'
                  )
               )
            ),
            'GraphObject'=>array(
               'objecttype'=>'GraphObject',
               'template_id'=>15719,
               'constants'=>array(
                  'description'=>'Graph from a cloned parent group'
               ),
               'datamap'=>array(
                  0=>array(
                     'src_name'=>'catcode2',
                     'dest_name'=>'title',
                     'src'=>'table', # may be self, parent, or the key-name of a sibling add-on
                     'dest'=>'self' # may be self, parent, or the key-name of a sibling add-on
                  )
               ),
               'parent_links'=>array(
               )
            ),
            'giniGraph'=>array(
               'objecttype'=>'giniGraph',
               'template_id'=>15718,
               'constants'=>array(
                  'description'=>'Graph from a cloned parent group'
               ),
               'datamap'=>array(
                  0=>array(
                     'src_name'=>'catcode2',
                     'dest_name'=>'title',
                     'src'=>'table', # may be self, parent, or the key-name of a sibling add-on
                     'dest'=>'self' # may be self, parent, or the key-name of a sibling add-on
                  )
               ),
               'parent_links'=>array(
               )
            ),
            'USGSGageObject'=>array(
               'objecttype'=>'USGSGageObject',
               'template_id'=>15772,
               'constants'=>array(
                  'description'=>'USGS Gage',
                  'area'=>-1
               ),
               'datamap'=>array(
                  0=>array(
                     'src_name'=>'usgs_gage',
                     'dest_name'=>'staid',
                     'src'=>'table', # may be self, parent, or the key-name of a sibling add-on
                     'dest'=>'self' # may be self, parent, or the key-name of a sibling add-on
                  ),
                  1=>array(
                     'src_name'=>'usgs_gage',
                     'dest_name'=>'name',
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
);

createBranch($projectid, $listobject, $target_scenarioid, 'sc_cbp5', 'rivername', 'catcode2', 'tnode', 'fnode', $outlet, array('rivername'=>''), $object_types, $debug);
die;

/*
# USGS framework
$tablename = 'va_drainage_dd';
$containment_columns = array('regionalba', 'river_basi', 'huc_8_digi', 'station_nu');
$src_props = array('Qout');
$dest_props = array('Qin');
*/

# CBP model framework
$tablename = 'sc_cbp5';
$containment_columns = array('watershed', 'minbas', 'subbasin', 'rivername', 'catcode2');
$src_props = array('Qout','ROVOL');
$dest_props = array('Qin','ROVOL_UP');
# only use rivanna test case
#$extrawhere = " catcode2 in (select catcode2 from sc_rivanna) ";
#$aextrawhere = " a.catcode2 in (select catcode2 from sc_rivanna) ";
#$bextrawhere = " b.catcode2 in (select catcode2 from sc_rivanna) ";


# only use James River test case
//$extrawhere = " catcode2 like 'J%' ";
//$aextrawhere = " a.catcode2 like 'J%' ";
//$bextrawhere = " b.catcode2 like 'J%' ";
# only use Potomac River test case
$extrawhere = " catcode2 like 'P%' ";
$aextrawhere = " a.catcode2 like 'P%' ";
$bextrawhere = " b.catcode2 like 'P%' ";
# only use Shenandoah River test case
//$extrawhere = " catcode2 like 'PS%' ";
//$aextrawhere = " a.catcode2 like 'PS%' ";
//$bextrawhere = " b.catcode2 like 'PS%' ";

# do full bay model
#$extrawhere = " (1 = 1) ";
#$aextrawhere = " (1 = 1) ";
#$bextrawhere = " (1 = 1) ";

# should we delete any old ones, or are we just adding new ones?
$delete_old = 0;
$delete_list = array(); # if we are to delete, we can specify only a list of containers (and their children) to delete

$allcolumns = '"' . join ('","', $containment_columns) . '"';
$f_node_col = 'f_node';
$t_node_col = 't_node';
$userid = 1;
$geomcol = 'the_geom';
$geomtype = 3;
# whether we should create geometry for upper levels of containment
$union_geoms = 0;
# whether we should capture geometry for the bottom level of element
$child_geoms = 0;

if ($delete_old) {
   if (count($delete_list) > 0) {
      foreach ($delete_list as $thiselid) {
         deleteModelElement($thiselid);
      }
      $listobject->querystring = "  select max(elementid) as maxel from scen_model_element ";
      $listobject->performQuery();
      $maxel = intval($listobject->getRecordValue(1,'maxel')) + 1;
      $listobject->querystring = " select setval('scen_model_element_elementid_seq', $maxel) ";
      print("$listobject->querystring ; <br>");
      $listobject->performQuery();
   } else {
      $listobject->querystring = "  delete from scen_model_element where scenarioid = $target_scenarioid ";
      $listobject->performQuery();
      $listobject->querystring = "  delete from map_model_linkages where scenarioid = $target_scenarioid ";
      $listobject->performQuery();
      $listobject->querystring = "  select max(elementid) as maxel from scen_model_element ";
      $listobject->performQuery();
      $maxel = intval($listobject->getRecordValue(1,'maxel')) + 1;
      $listobject->querystring = " select setval('scen_model_element_elementid_seq', $maxel) ";
      print("$listobject->querystring ; <br>");
      $listobject->performQuery();
   }
   
}

$listobject->queryrecords = array();

# grab the bottom object
$colname = 'catcode2';
$objectname_col = 'rivername';
$segmentid = '4370'; # the outlet segment name - 0000 for all bay (this will not work, since there is no catcode for it), 4370 for Shenandoah
$levels = 0; # first, just get the info on the segment outlet
$data = getCBPSegList($listobject, $tablename, $colname, $segmentid, $debug,$levels);
$segments = $data['segments'];
$segnames = $data['segnames'];

# if the segmentid is '0000', then we should get them by another approach, watershed based I suppose.
# OK, get all segment 0000 outlets (3rd part of catcode2), and name them as the rivername, 
# Example: 
# create a container with the Potomac River Watershed
# then, get all 0000
   # the following query will get all rivernames with 0000 outlets in the Potomac River:
   # select rivername from sc_cbp5 where catcode2 like 'P%' and catcode2 like '%0000' group by rivername;
   # then, iterate by rivername
   # create a model container for the rivername
      # select catcode2 from sc_cbp5 
      # where catcode2 like 'P%' and catcode2 like '%0000' and rivername = '$rivername' order by rivername, catcode2;
         # Now, go through each of these catcodes, and retrieve their tributaries using the getCBPSegList routine
         
# to do ALL watersheds, we can get the watershed name (major basin name actually), and the prefix as follows:

# select majbas, substring(catcode2 from 1 for 1) as wpre from sc_cbp5 group by majbas, wpre order by wpre;
# where wpre = 'P' for potomac, etc.

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
            'scenarioid'=>$target_scenarioid,
            'newcomponenttype'=>$this_type['objecttype'],
            'name'=>$child_name
         );
         $datamaps = $this_type['datamap'];
         $ctype_name = $this_type['objecttype'];
         # check to see if we want to clone an existing object set as our template
         $template_id = -1;
         if (isset($this_type['template_id'])) {
            if ($this_type['template_id'] > 0) {
               $template_id = $this_type['template_id'];
            }
         }
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
            //$cvalues['the_geom'] = $thiscont[$geomcol];
            //$cvalues['geomtype'] = $geomtype;
         }
         $debug = 0;
         $sub_objects = array();
         if ($template_id > 0) {
            print("Trying to clone the group: $template_id <br>");
            $listobject->querystring = "  select scenarioid from scen_model_element where elementid = $template_id ";
            $listobject->performQuery();
            $src_scen = $listobject->getRecordValue(1,'scenarioid');
            $clonedata = array(
               'elements'=>array($template_id),
               'scenarioid'=>$src_scen,
               'projectid'=>$projectid,
               'dest_scenarioid'=>$target_scenarioid,
               'dest_parent'=>$parentcontainer
            );
            $copyresult = copyModelGroupResult($clonedata);
            $feedback = $copyresult['innerHTML'];
            print("Creation routine output:" . $feedback . " <br>");
            $sub_objects = $copyresult['element_map'];
            print("Group has been cloned with the following map of id values:" . print_r($sub_objects,1) . "<br>");
            $newelid = $sub_objects[$template_id]['new_id'];
            print("Attempting to update container $newelid with variables:" . print_r(array_keys($cvalues),1) . "<br>");
            error_log("Attempting to update container $newelid with variables:" . print_r(array_keys($cvalues),1) . "<br>");
            $obres = updateObjectProps($projectid, $newelid, $cvalues);
            $feedback = $obres['innerHTML'] . "<hr>";
            #$feedback .= $obres['debugHTML'] . "<hr>";
            print("Update routine output:" . $feedback . " <br>");
         } else {
            $feedback = insertBlankComponent($cvalues);
            print("Creation routine output:" . $feedback . " <br>");
            $listobject->querystring = "SELECT currval('scen_model_element_elementid_seq') ";
            print("Get parent ID:" . $listobject->querystring . " ; <br>");
            $listobject->performQuery();
            $listobject->show = 0;
            #$innerHTML .= "$listobject->outstring <br>";
            $newelid = $listobject->getRecordValue(1,'currval');
         }
         $debug = 0;
         $containers_created[$n][$child_name]['elementid'] = $newelid;
         
         foreach (array_keys($this_type['addons']) as $thischild_key) {
            # set up the initial values for these child objects
            $thischild = $this_type['addons'][$thischild_key];
            $cvalues = array(
               'activecontainerid'=>$newelid,
               'scenarioid'=>$target_scenarioid,
               'newcomponenttype'=>$thischild['objecttype']
            );
            $datamaps = $thischild['datamap'];
            $template_id = -1;
            if (isset($thischild['template_id'])) {
               if ($thischild['template_id'] > 0) {
                  $template_id = $thischild['template_id'];
               }
               # now, check to see if the parent of this object was actually cloned succesfully, and if the 
               # corresponding child object exists - if not, we will insert a blank component and hope for the best
               print("Checking to see if this child has already been created in a group clone.<br>");
               if (!in_array($template_id, array_keys($sub_objects))) {
                  $template_id = -1;
               } else {
                  print("ID $template_id found in result of group clone.<br>");
               }
            }
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
               print("<b>Setting</b> " . $thismap['src_name'] . " = $mapval <br>");
               $cvalues[$thismap['dest_name']] = $mapval;
            }
            $constants = $thischild['constants'];
            foreach ($constants as $key => $val) {
               $cvalues[$key] = $val;
            }
            $debug = 0;
            if ($template_id > 0) {
               print("Add-on component has been created already, updating properties.<br>");
               error_log("Add-on component has been created already, updating properties.<br>");
               $elid = $sub_objects[$template_id]['new_id'];
               print("Add-on component template $template_id exists as $elid .<br>");
               error_log("Add-on component template $template_id exists as $elid .<br>");
               # we have already created this child as a part of cloning the parent
               print("Setting properties on child " . $thischild['objecttype'] . " ($elid): " . print_r(array_keys($cvalues),1) . "<br>");
               $obres = updateObjectProps($projectid, $elid, $cvalues);
               $feedback = $obres['innerHTML'] . "<hr>";
               #$feedback .= $obres['debugHTML'] . "<hr>";
               $childid = $elid;
            } else {
               $feedback = insertBlankComponent($cvalues);
               $listobject->querystring = "SELECT currval('scen_model_element_elementid_seq') ";
               $listobject->performQuery();
               $listobject->show = 0;
               #$innerHTML .= "$listobject->outstring <br>";
               $childid = $listobject->getRecordValue(1,'currval');
            }
            $debug = 0;
            print("Creation routine output:" . $feedback . " <br>");
            $parent_links = $thischild['parent_links'];
            error_log("Adding links from parent to child.<br>" . print_r($parent_links,1));
            print("Adding links from parent to child.<br>" . print_r($parent_links,1));
            foreach ($parent_links as $thislink) {
               $src_prop = $thislink['src_name'];
               $dest_prop = $thislink['dest_name'];
               $src = $thislink['src'];
               switch ($src) {
                  case 'self':
                     print("$src LINK: Linking $src_prop from $thischild_key to $dest_prop on $child_name <br>");
                     error_log("$src LINK: Linking $src_prop from $thischild_key to $dest_prop on $child_name <br>");
                     $out = createObjectLink($projectid, $target_scenarioid, $childid, $newelid, 2, $src_prop, $dest_prop);
                  break;
                  
                  case 'parent':
                     print("$src LINK: Linking $src_prop from $child_name to $dest_prop on $thischild_key <br>");
                     error_log("$src LINK: Linking $src_prop from $child_name ($newelid) to $dest_prop on $thischild_key ($childid)<br>");
                     $out = createObjectLink($projectid, $target_scenarioid, $newelid, $childid, 2, $src_prop, $dest_prop);
                  break;
               }
               print($out['innerHTML'] . "<br>");
            }
            error_log("Finished parent to child links for $thischild_key");
            updateObjectPropList($childid);
         }
         updateObjectPropList($newelid);
         error_log("Finished processing " . $this_type['objecttype'] . " ($newelid)");
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
      error_log("Creating cross-container linkages at the $pname level<br>");
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
            $out = addObjectLink($projectid, $target_scenarioid, $from_objectid, $from_parentid, 2, $src_prop, $src_prop);
            #if ($debug) {
               $innerHTML .= $out['innerHTML'] . "<br>";
            #}
            
            # FROMparent to TOParent
            $out = addObjectLink($projectid, $target_scenarioid, $from_parentid, $to_parentid, 2, $src_prop, $dest_prop);
            #if ($debug) {
               $innerHTML .= $out['innerHTML'] . "<br>";
            #}
            
            # TOparent to TOChild
            $out = addObjectLink($projectid, $target_scenarioid, $to_parentid, $to_objectid, 2, $dest_prop, $dest_prop);
            #if ($debug) {
               $innerHTML .= $out['innerHTML'] . "<br>";
            #}
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
      $lquery .= "  group by a.\"$containinglevel\", b.\"$containinglevel\", ";
      $lquery .= "    a.\"$currentlevel\", b.\"$currentlevel\" ";
      print($lquery . " ; <br>");
      $listobject->querystring = $lquery;
      $listobject->performQuery();
      $clrecs = $listobject->queryrecords;
      print("Creating within-container linkages at the $pname level<br>");
      foreach ($clrecs as $thisrec) {
         $to_object = $thisrec['to_object'];
         $from_object = $thisrec['from_object'];

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
            print("Linking $from_object($from_objectid)($src_prop) to $to_object($to_objectid)($dest_prop) <br>");
            # FROMchild to FROMparent
            $out = addObjectLink($projectid, $target_scenarioid, $from_objectid, $to_objectid, 2, $src_prop, $dest_prop);
            if ($debug) {
               $innerHTML .= $out['innerHTML'] . "<br>";
            }
            $listobject->performQuery();
         }
      }
   }
}

?>
