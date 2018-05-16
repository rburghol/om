<?php
// lib_wooomm.cbp.php

class CBPLandDataConnection_sub extends CBPLandDataConnection {
   //sub-comp version of land data connection
   // includes it's own land use matrix which is fixed
   var $landuse_var = 'landuse_matrix';
   var $landuse_matrix = null;
   var $lat_dd = null;
   var $lon_dd = null;
   var $matrix = array(); // array shell for storage table
   var $serialist = 'rvars,wvars,matrix';
   var $nearest_landseg = '';
   var $nhd_db = null;
   var $reload_nlcd = 0;
   var $channel_slope = 0.01;
   var $drainage_area = 10.0;
   var $channel_length = 10.0;
   var $recreate_list = 'reload_nlcd';
   
   function setState() {
      parent::setState();
      $this->initOnParent();
   }
   
   function initOnParent() {
      if (!is_array($this->wvars)) {
         $this->wvars = array();
      }
      if (!is_array($this->rvars)) {
         $this->rvars = array();
      }
      $this->rvars[] = 'year';
      foreach ( array('suro', 'agwo', 'ifwo','Qout','Runit','area_sqmi') as $thisone) {
         $this->wvars[] = $thisone;
      }
      parent::initOnParent();
   }

   function wake() {
      //error_log("$this->name calling parent wake()");
      parent::wake();
      //error_log("$this->name calling setupLanduseMatrix()");
      $this->setupLanduseMatrix();
      //$this->debugmode = 1;
   }
       
   function sleep() {
      $this->wvars = 0;
      $this->rvars = 0;
      unset($this->landuse_matrix);
      $this->landuse_matrix = -1;
      parent::sleep();
   }
   
   function logState() {
   
      // logging will be done by the parent, so no need to waste memory and time with this
   
   }

   function subState() {
      $this->initOnParent();
   }
   
   function getParentLatLon() {
      $lat_dd = null; $lon_dd = null;
      if (is_object($this->parentobject)) {
         $lat_dd = $this->parentobject->getProp($this->lat_dd);
         $lon_dd = $this->parentobject->getProp($this->lon_dd);
         //error_log("Calling getCOVACBPLandsegPointContainer($lat_dd, $lon_dd) ");
      } else {
         //error_log("Parent object not set.");
      }
      return array($lat_dd, $lon_dd);
   }
   
   function guessLandSeg() {
      // find the parents geometry and see if it overlaps with a known landseg
      // parent must have valid geometry, default to the first overlapping shape that we find
      //  php fn_findTribs.php 2 36.75639 -82.4347
      // $landseg = getCOVACBPLandsegPointContainer($latdd, $londd);
      //error_log("guessLandSeg() called for $this->name");
      $landseg = null;
      list($lat_dd, $lon_dd) = $this->getParentLatLon();
      //error_log("this->getParentLatLon() returned $lat_dd $lon_dd");
      if ( is_numeric($lat_dd) and is_numeric($lon_dd)) {
         $landseg = getCOVACBPLandsegPointContainer($lat_dd, $lon_dd);
      }
      //error_log("Land Segment: $landseg ");
      $this->nearest_landseg = $landseg;
   }
   
   function getNHDProperties() {
      $nhd = new nhdPlusDataSource;
      $nhd->init();
      list($lat_dd, $lon_dd) = $this->getParentLatLon();
      if ( is_numeric($lat_dd) and is_numeric($lon_dd)) {
         $nhd->getPointInfo($lat_dd, $lon_dd);
         error_log("Searching for coords: $lat_dd, $lon_dd");
         error_log("NLCD Land Use: " . count($nhd->nlcd_landuse));
         error_log("NHD+ Reaches: " . print_r($nhd->nhd_segments,1));
      }
      $lumatrix = $this->createLUMatrix($nhd->nlcd_landuse, 1850, 2050, 1);
      $this->assocArrayToMatrix($lumatrix);
      // get channel properties
      $this->channel_slope = $nhd->channel_slope;
      $this->drainage_area = $nhd->drainage_area;
      $this->channel_length = $nhd->channel_length;
   }
   
   function createLUMatrix($lu, $minyear, $maxyear, $translate = 1) {
      $lr = array();
      foreach ( $lu as $thislu => $thisarea ) {
         if (substr($thislu,0,4) == 'nlcd') {
            $lr[] = array('luname'=>$thislu, $minyear => round($thisarea,3), $maxyear => round($thisarea,3));
         }
      }
      error_log("Land use recs: " . print_r($lr,1));
      if ($translate) {
         $lr = translateNLCDtoCBP($lu, $minyear, $maxyear);
      }
      return $lr;
   }

   function setDataColumnTypes() {
      parent::setDataColumnTypes();
      foreach ($this->wvars as $thisvar) {
         if ($this->debug) {
            $this->logDebug("Setting $this->name" . "_" . "$thisvar to type float8 on parent.<br>\n");
            //error_log("Setting $this->name" . "_" . "$thisvar to type float8 on parent.<br>\n");
         }
         if (is_object($this->parentobject)) {
            if (method_exists($this->parentobject, 'setSingleDataColumnType')) {
               $this->parentobject->setSingleDataColumnType($this->name . "_" . $thisvar, 'float8', 0.0);
            }
         }
         $this->vars[] = $this->name . "_" . $thisvar;
      }
   }  
   
   function postStep() {
      $this->setStateVar('Runit', $this->state['Qout'] / $this->state['area_sqmi']);
      parent::postStep();
   }
   
   function create() {
      parent::create();
      //error_log("Create routine called on $this->name with $this->text2table");
      $this->setupLanduseMatrix();
      // check to see if the text2table field has anything in it
      // if so, parse the text appropriately
      //error_log("Storage matrix created $this->name ");
      $this->guessLandSeg();
      $this->getNHDProperties();
      $this->reload_nlcd = 0;
      //error_log("Finished loading land use\n");
   }
   
   function assocArrayToMatrix($thisarray = array()) {
   // this may be called by routines that want to set a matrix (such as setNLCDLanduse), so we pass this info to the sub-comp landuse_matrix 
      if (isset($this->processors['landuse_matrix'])) {
         $this->processors['landuse_matrix']->assocArrayToMatrix($thisarray);
         $this->matrix = $this->processors['landuse_matrix']->matrix;
      }
      
   }
   
   function setupLanduseMatrix($text2table = '') {
      //error_log("Called setupLanduseMatrix()");
      $this->landuse_matrix = new dataMatrix;
      $this->landuse_matrix->name = 'storage_stage_area';
      $this->landuse_matrix->wake();
      //$this->landuse_matrix->debug = 1;
      //$this->landuse_matrix->debugmode = 1;
      $this->landuse_matrix->numcols = 3;
      $this->landuse_matrix->fixed_cols = false;
      $this->landuse_matrix->valuetype = 2; // 2 column lookup (col & row)
      $this->landuse_matrix->keycol1 = 'year'; // key for 1st lookup variable
      $this->landuse_matrix->lutype1 = 0; // lookup type for first lookup variable: 0 - exact match; 1 - interpolate values; 2 - stair step
      $this->landuse_matrix->keycol2 = ''; // key for 1st lookup variable
      $this->landuse_matrix->lutype2 = 1; // lookup type for 2nd lookup variable: 0 - exact match; 1 - interpolate values; 2 - stair step
      // add a row for the header line
      if ( !is_array($this->matrix) or (count($this->matrix) == 0) or ($text2table <> '') ) {
         $default_landuses = array( 'for', 'hyo', 'pas', 'hwm', 'pul', 'iml', 'puh', 'imh', 'ext', 'bar');
         $this->landuse_matrix->numrows = count($default_landuses) + 1;
         $this->landuse_matrix->matrix[] = 'luname';
         $this->landuse_matrix->matrix[] = 1900;
         $this->landuse_matrix->matrix[] = 2050;
         foreach ($default_landuses as $thislu) {
            $this->landuse_matrix->matrix[] = $thislu; // the name
            $this->landuse_matrix->matrix[] = 0; // vaue for start year
            $this->landuse_matrix->matrix[] = 0; // value for end year
         }
         $this->matrix = $this->landuse_matrix->matrix;
         //error_log("Creating land use matrix anew: ");
      } else {
         if ($text2table <> '') {
            $this->landuse_matrix->text2table = $text2table;
            $this->landuse_matrix->create();
            //error_log("Creating land use matrix from text string: ");
         } else {
            $this->landuse_matrix->matrix = $this->matrix;// map the text mo to a numerical description
            $this->landuse_matrix->numrows = count($this->landuse_matrix->matrix) / 3.0;
            //error_log("Creating land use matrix from stored data ");
         }
      }
      
      $this->addOperator('landuse_matrix', $this->landuse_matrix, 0);
      //error_log("Adding Landuse Sub-comp 'landuse_matrix': " . print_r($this->landuse_matrix->matrix, 1) );
   }
    
   // *************************************************
   // *********           display interface
   //*************************************************
   function showEditForm($formname, $disabled=0) {
      if (is_object($this->listobject)) {
         $returnInfo = array();
         $returnInfo['name'] = $this->name;
         $returnInfo['description'] = $this->description;
         $returnInfo['debug'] = '';
         $returnInfo['elemtype'] = get_class($this);
         $returnInfo['innerHTML'] = '';
         $innerHTML = '';
         # set up div to contain each seperate multi-parameter block
         $innerHTML .= showHiddenField("numrows", $this->numrows, 1);
         $aset = $this->listobject->adminsetuparray[get_class($this)];
         foreach (array_keys($aset['column info']) as $tc) {
            $props[$tc] = $this->getProp($tc);
         }
         $formatted = showFormVars($this->listobject,$props,$aset,0, 1, 0, 0, 1, 0, -1, NULL, 1);
         $innerHTML .= "<table><tr>";
         $innerHTML .= $this->showFormHeader($formatted,$formname, $disabled );
         $innerHTML .= "<hr> ";
         $innerHTML .= $this->showFormBody($formatted,$formname, $disabled );
         $innerHTML .= "<hr> ";
         $innerHTML .= $this->showFormFooter($formatted,$formname, $disabled );
         $innerHTML .= "</tr></table>";
         
         // show the formatted matrix
         //$this->formatMatrix();
         //$innerHTML .= print_r($this->matrix_formatted,1) . "<br>";
         $returnInfo['innerHTML'] = $innerHTML;
         return $returnInfo;
      }
   }
   
   function showFormHeader($formatted,$formname, $disabled = 0) {
      $innerHTML .= "<b>Name:</b> " . $formatted->formpieces['fields']['name'] . " | ";
      $innerHTML .= "<b>Debug?:</b> " . $formatted->formpieces['fields']['debug'] . " | ";
      $innerHTML .= "<b>Debug Mode?:</b> " . $formatted->formpieces['fields']['debugmode'] . " | ";
      $innerHTML .= "<b>Exec. Rank:</b> " . $formatted->formpieces['fields']['exec_hierarch'] . "<BR>";
      $innerHTML .= "<b>Description:</b> " . $formatted->formpieces['fields']['description'];
      $innerHTML .= "<br><b>Scenario:</b> " . $formatted->formpieces['fields']['scid'];
      $innerHTML .= " | <b>Landseg ID:</b> " . $formatted->formpieces['fields']['id2'];
      $innerHTML .= " | <b>Data Timestep:</b> " . $formatted->formpieces['fields']['hspf_timestep'];
      $innerHTML .= "<br><b>Latitude:</b> " . $formatted->formpieces['fields']['lat_dd'];
      $innerHTML .= " | <b>Longitude:</b> " . $formatted->formpieces['fields']['lon_dd'];
      //$innerHTML .= "<br><b>Nearest Land Segment:</b> " . $this->nearest_landseg;
      $innerHTML .= "<br><b>Nearest Land Segment:</b> " . $formatted->formpieces['fields']['nearest_landseg'];
      $innerHTML .= "<br><b>Reload NHD Landuse on Save?:</b> " . $formatted->formpieces['fields']['reload_nlcd'];
      
      $innerHTML .= "<br>NHD Props:  ";
      $innerHTML .= "<b>Channel Slope (ft/ft):</b> " . $formatted->formpieces['fields']['channel_slope'];
      $innerHTML .= "<b>Channel Length(ft):</b> " . $formatted->formpieces['fields']['channel_length'];
      $innerHTML .= "<b>Drainage (sqmi):</b> " . $formatted->formpieces['fields']['drainage_area'];
      return $innerHTML;
   }
   
   function showFormBody($formatted,$formname, $disabled = 0) {
      $innerHTML .= "<table><tr>";
      $innerHTML .= "<td valign=top>";
      $innerHTML .= "<b>Land Use Values (acres):</b> <br>";
      //$this->landuse_matrix->debug = 1;
      $innerHTML .= $this->landuse_matrix->showFormBody($formatted,$formname, $disabled);
      $innerHTML .= "</td>";
      return $innerHTML;
   }
   
   function showFormFooter($formatted,$formname, $disabled = 0) {
      $innerHTML = '';
      return $innerHTML;
   }
   // *************************************************
   // *********       END display interface
   // *************************************************
}

class nhdPlusDataSource {
   var $nhd_db = null;
   var $units = 'ft';
   var $host = '192.168.0.13';
   var $dbname = 'va_hydro';
   var $username = 'usgs_ro';
   var $password = '@ustin_CL';
   var $port = 5432;
   var $dbconn = null;
   var $length_units = 'feet';
   var $area_units = 'acres';
   var $channel_info = null;
   var $nlcd_landuse = null;
   var $nhd_segments = null;
   var $channel_slope = null;
   var $drainage_area = null;
   var $channel_length = null;
   
   function init() {
   if (class_exists('pgsql_QueryObject')) {
         $this->nhd_db = new pgsql_QueryObject;
         $this->nhd_db->dbconn = pg_connect("host=$this->host port=$this->port dbname=$this->dbname user=$this->username password=$this->password");
      } else {
         error_log("Cannot locate class pgsql_QueryObject ");
      }
   }
   
   function getPointInfo($lat_dd, $lon_dd) {
      if (is_object($this->nhd_db)) {
         if (is_numeric($lat_dd) and is_numeric($lon_dd)) {
            // general location
            $outlet_info = findNHDSegment($this->nhd_db, $lat_dd, $lon_dd);
            $comid = $outlet_info['comid'];
            $tribs = findTribs($this->nhd_db, $comid, $this->debug);
            $this->nhd_segments = $tribs['segment_list'];
            //error_log("Found tribs: " . print_r($this->nhd_segments,1));
            // reach characteristics
            $cinfo = getNHDChannelInfo($this->nhd_db, $comid, $this->nhd_segments, $this->units, $this->debug);
            //$cinfo = getNHDChannelInfo($this->nhd_db, $comid, $this->nhd_segments, $this->units, 1);
            //error_log("Found Channel Info: " . print_r($cinfo,1));
            $this->channel_slope = round($cinfo['c_slope'],4);
            $this->channel_length = round($cinfo['reachlen'],1);
            // need different units for drainage area
            $cinfo = getNHDChannelInfo($this->nhd_db, $comid, $this->nhd_segments, 'mi', $this->debug);
            $this->drainage_area = round($cinfo['drainage_area'],2);
            // land use info
            $this->nlcd_landuse = getNHDLandUse($this->nhd_db, $this->nhd_segments, $this->area_units, $this->debug);
         }
      }
   }

}

?>
