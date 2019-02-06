<?php

include_once(DRUPAL_ROOT . "/modules/om/lib/psql_functions.php");
include_once(DRUPAL_ROOT . "/modules/om/lib/file_functions.php");
global $session_db, $listobject, $server_ip, $outdir;
// ***********************************************************
// ***        Database & File Defaults                     ***
// ***********************************************************
$httppath = '/var/www/html';
// @todo: move this to drupal configured database objects
// model info db
$dbip = '192.168.0.21'; 
$dbpass = 'secret';
$dbuser = 'model_ro';
$dbname = 'model';
// session data database
$session_dbpass = '314159';
$session_dbuser = 'postgres';
$session_dbname = 'model_sessiondata';
$session_dbip = 'localhost';
$session_port = 5433;
// runtime dbip
$runtime_dbpass = '314159';
$runtime_dbuser = 'postgres';
$runtime_dbname = 'model_scratch';
$runtime_dbip = 'localhost';
$runtime_dbport = '5444';
$datadir = "$httppath/data"; 
$dataurl = '/data';
$outdir = "$datadir/proj$projectid/out";
$outurl = "$dataurl/proj$projectid/out";
// main database
$connstring = "host=$dbip dbname=$dbname user=$dbuser password=$dbpass";
$dbconn = pg_connect($connstring, PGSQL_CONNECT_FORCE_NEW);
$stat = pg_connection_status($dbconn);
if ($stat === PGSQL_CONNECTION_OK) {
   //error_log( 'Connection status ok');
} else {
   dpm(pg_last_error($dbconn), 'Connection status bad');
}
//dpm($stat,'pg conn status');
$listobject = new pgsql_QueryObject;
$listobject->connstring = $connstring;
$listobject->ogis_compliant = 1;
$listobject->dbconn = $dbconn;
// session db
$session_connstring = "host=$session_dbip dbname=$session_dbname user=$session_dbuser password=$session_dbpass port=$session_port";
$session_dbconn = pg_connect($session_connstring, PGSQL_CONNECT_FORCE_NEW);
$session_db = new pgsql_QueryObject;
$session_db->connstring = $session_connstring;
$session_db->ogis_compliant = 1;
$session_db->dbconn = $session_dbconn;
$session_db->adminsetuparray = $adminsetuparray;
// runtime db
// @todo
  
class DataQueryWizardHandler {
  var $db;
  var $tablename;
  var $goutdir = '';
  var $gouturl = '';
  var $imgurl = '';
  var $maxreportrecords = 100; 
  # maximum number of records to actually show in the report string, above this defers to downloadable file
  var $value_dbcolumntype = 'varchar'; // just a dummy since, we should not even need to dump this to a db table, since it is meaningless
  var $quote_tablename = 0; // if we have unorthodox tale names, we may 
  //need to quote them, but default to not, since we may be given a 
  //sub-query as a table, in which case quoting would goof it up
  var $sqlstring;
  var $is_datasource;
  var $where_functions = array(
    'eq' => '=',
    'gt' => '>',
    'lt' => '<',
    'gte' => '>=',
    'lte' => '<=',
    'ne' => '<>',
    'in' => 'in ()',
    'contains' => 'contains()',
    'notnull' => 'not null',
    'isnull' => 'is null',
  );
  var $columns = FALSE;
  
  public function buildOptionsForm(&$form, $form_state) {
    // show config options if this is added to a panel, page or block
  }
  
  public function buildForm(&$form, $form_state) {
    // get the table
    // get the columns available
    $cols = $this->columns;
    if (count($cols) > 0) {
      // show select columns
      $querycols = $this->addRows($form, $form_state, $cols);
      $form['select_block'] = array(
        '#markup' => "<br><strong>SELECT:</strong>",
      );
      $form['querycols'] = $querycols;

      // show WHERE clauses
      $form['where_block'] = array(
        '#markup' => "<strong>WHERE:</strong>",
      );
      $wherecols = $this->addClauses($form, $form_state, $cols);
      $form['wherecols'] = $wherecols;
      // show ORDER columns
      $form['order_block'] = array(
        '#markup' => "<strong>ORDER BY:</strong>",
      );
      $ordercols = $this->addOrders($form, $form_state, $cols);
      $form['ordercols'] = $ordercols;
      
      // show ORDER columns
      $form['sql_block'] = array(
        '#markup' => "<strong>Query:</strong>",
      );
      $this->assembleQuery($form, $form_state);
      $form['sql_query'] = array(
        '#markup' => "<pre>$this->sqlstring</pre>",
      );
      // if this is a REBUILD, look to see if there is an add a line request
      //  if so, add a line on the end
      // create
    }
    $form['tablename'] = array(
      '#type' => 'hidden',
      '#value' => 'tablename',
      '#default' => 'tablename',
      '#weight' => 40,
    );
    if (count($cols) > 0) {
      $sub_message = 'Execute Query';
    } else {
      $sub_message = 'Load Table Info';
    }
    $form['actions'] = array('#type' => 'actions');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t($sub_message),
      '#weight' => 40,
    );
  }
  function addRows($form, &$form_state, $cols = array()) {
    // expects cols = columns to show
    // form_state and form have values already populated
    // returns element
    $functions = array(
      'none'=>'--', 
      'sum'=>'Sum()', 
      'avg'=>'Avg()', 
      'min'=>'Min()', 
      'max'=>'Max()'
    );
    $row_template = array();
    $row_template['colname'] = array(
      '#type' => 'select',
      '#default_value' => NULL,
      '#empty_option' => '---',
      '#options' => $cols,
    );
    $row_template['function'] = array(
      '#type' => 'select',
      '#default_value' => NULL,
      '#options' => $functions,
    );
    $row_template['alias'] = array(
      '#type' => 'textfield',
      '#default_value' => '',
      '#size' => 16,
    );
    $row_template['params'] = array(
      '#type' => 'textfield',
      '#default_value' => '',
      '#size' => 16,
    );
    $row_template['addcol'] = array(
      '#type' => 'button',
      '#value' => '+',
    );
    $row_template['delcol'] = array(
      '#type' => 'button',
      '#value' => '-',
    );
    $element = array(
      '#tree' => TRUE,
    );
    $element[] = array(
      '#markup' => "<table>",
    );
    //dpm($properties_conf, "properties to add to entity_get_property_info");
    $header = array(
      'colname' =>  array(
        '#markup' => "Column",
      ),
      'function' =>  array(
        '#markup' => "Function",
      ),
      'alias' =>  array(
        '#markup' => "Alias",
      ),
      'params' =>  array(
        '#markup' => "Params",
      ),
      'addcol' =>  array(
        '#markup' => "",
      ),
      'delcol' =>  array(
        '#markup' => "",
      ),
    );
    $this->data_ui_tabularize($header);
    $element[] = $header;
    if (isset($form_state['values']['querycols'])) {
      $nc = count($form_state['values']['querycols']);
    } else {
      // @todo: we will see if this object has a saved query, and load it if so
      $nc = 1;
    }
    if ($form_state['triggering_element']) {
      $t_id = $form_state['triggering_element']['#id'];
      $t_name = $form_state['triggering_element']['#name'];
      $t_action = $form_state['triggering_element']['#value'];
      $t_parts = explode('_', $t_name);
      $t_label = $t_parts[0];
      $t_index = $t_parts[1];
      //dpm($id, 'id of input');
    }
    if (isset($t_action) and strstr($t_name,'col')) {
      switch ($t_action) {
        case '+':
        $nc++;
        break;
        case '-':
        // need to handle this after the table of column info is assembled
        break;
      }
    }
    if (isset($form_state['values']['querycols'])) {
      $row_names = array_keys($form_state['values']['querycols']);
    } else {
      // @todo: if cached, retrieve rows and set rownames here
      $row_names = array('cols_0');
    }
    $row_indices = array();
    foreach ($row_names as $rn) {
      // preserve the index passsed in so that we handle deleting columns correctly
      $r_parts = explode('_', $rn);
      $r_label = $r_parts[0];
      $r_index = $r_parts[1];
      $row_indices[] = $r_index;
    }
    $i = 0;
    if (count($row_indices) < $nc) {
      $row_indices[] = max($row_indices) + 1;
    }
    foreach ($row_indices as $i) {
      $line = $row_template;
      // set the name so that form processing knows 
      // from which row the button was clicked
      // the #id attribute will also be updated to match this automatically
      $line['delcol']['#name'] = 'delcol_' . $i;
      $line['addcol']['#name'] = 'addcol_' . $i;
      // @todo: if cached, set values here
      $this->data_ui_tabularize($line);
      $element["cols_$i"] = $line;
    }
    if (isset($t_action)) {
      switch ($t_action) {
        case '-':
        unset($element["cols_$t_index"]);
        break;
      }
    }
    $element[] = array(
      '#markup' => "</table>",
    );
    return $element;
    
  }

  function addOrders($form, &$form_state, $cols = array()) {
    // expects cols = columns to show
    // returns element
    // column defs
    $row_template = array();
    $row_template['colname'] = array(
      '#type' => 'select',
      '#empty_option' => '---',
      '#default_value' => NULL,
      '#options' => $cols,
    );
    $row_template['function'] = array(
      '#type' => 'select',
      '#default_value' => NULL,
      '#options' => array('ASC' => 'ASC', 'DESC' => 'DESC'),
    );
    $row_template['addorder'] = array(
      '#type' => 'button',
      '#value' => '+',
    );
    $row_template['delorder'] = array(
      '#type' => 'button',
      '#value' => '-',
    );
    $element = array(
      '#tree' => TRUE,
    );
    $element[] = array(
      '#markup' => "<table>",
    );
    //dpm($properties_conf, "properties to add to entity_get_property_info");
    $header = array(
      'colname' =>  array(
        '#markup' => "Column",
      ),
      'function' =>  array(
        '#markup' => "Sort",
      ),
      'addorder' =>  array(
        '#markup' => "",
      ),
      'delorder' =>  array(
        '#markup' => "",
      ),
    );
    $this->data_ui_tabularize($header);
    $element[] = $header;
    if (isset($form_state['values']['ordercols'])) {
      $nc = count($form_state['values']['ordercols']);
    } else {
      // @todo: we will see if this object has a saved query, and load it if so
      $nc = 1;
    }
    if ($form_state['triggering_element']) {
      $t_id = $form_state['triggering_element']['#id'];
      $t_name = $form_state['triggering_element']['#name'];
      $t_action = $form_state['triggering_element']['#value'];
      $t_parts = explode('_', $t_name);
      $t_label = $t_parts[0];
      $t_index = $t_parts[1];
      //dpm($t_id, 'id of input');
      //dpm($t_name, 'name of input');
    }
    if (isset($t_action) and strstr($t_name,'order')) {
      switch ($t_action) {
        case '+':
        $nc++;
        break;
        case '-':
        // need to handle this after the table of column info is assembled
        break;
      }
    }
    if (isset($form_state['values']['ordercols'])) {
      $row_names = array_keys($form_state['values']['ordercols']);
    } else {
      // @todo: if cached, retrieve rows and set rownames here
      $row_names = array('cols_0');
    }
    $row_indices = array();
    foreach ($row_names as $rn) {
      // preserve the index passsed in so that we handle deleting columns correctly
      $r_parts = explode('_', $rn);
      $r_label = $r_parts[0];
      $r_index = $r_parts[1];
      $row_indices[] = $r_index;
    }
    $i = 0;
    if (count($row_indices) < $nc) {
      $row_indices[] = max($row_indices) + 1;
    }
    foreach ($row_indices as $i) {
      $line = $row_template;
      // set the name so that form processing knows 
      // from which row the button was clicked
      // the #id attribute will also be updated to match this automatically
      $line['delorder']['#name'] = 'delorder_' . $i;
      $line['addorder']['#name'] = 'addorder_' . $i;
      // @todo: if cached, set values here
      $this->data_ui_tabularize($line);
      $element["cols_$i"] = $line;
    }
    if (isset($t_action) and strstr($t_name,'order')) {
      switch ($t_action) {
        case '-':
        unset($element["cols_$t_index"]);
        break;
      }
    }
    $element[] = array(
      '#markup' => "</table>",
    );
    return $element;
  }

  function addClauses($form, &$form_state, $cols = array()) {
    // expects cols = columns to show
    // returns element
    // column defs
    $row_template = array();
    $row_template['colname'] = array(
      '#type' => 'select',
      '#default_value' => NULL,
      '#empty_option' => '---',
      '#options' => $cols,
    );
    $row_template['function'] = array(
      '#type' => 'select',
      '#default_value' => NULL,
      '#options' => $this->where_functions,
    );
    $row_template['value'] = array(
      '#type' => 'textfield',
      '#default_value' => '',
      '#size' => 16,
    );
    $row_template['addclause'] = array(
      '#type' => 'button',
      '#value' => '+',
    );
    $row_template['delclause'] = array(
      '#type' => 'button',
      '#value' => '-',
    );
    $element = array(
      '#tree' => TRUE,
    );
    $element[] = array(
      '#markup' => "<table>",
    );
    //dpm($properties_conf, "properties to add to entity_get_property_info");
    $header = array(
      'colname' =>  array(
        '#markup' => "Column",
      ),
      'function' =>  array(
        '#markup' => "Function",
      ),
      'value' =>  array(
        '#markup' => "Value",
      ),
      'addclause' =>  array(
        '#markup' => "",
      ),
      'delclause' =>  array(
        '#markup' => "",
      ),
    );
    $this->data_ui_tabularize($header);
    $element[] = $header;
    if (isset($form_state['values']['wherecols'])) {
      $nc = count($form_state['values']['wherecols']);
    } else {
      // @todo: we will see if this object has a saved query, and load it if so
      $nc = 1;
    }
    if ($form_state['triggering_element']) {
      $t_id = $form_state['triggering_element']['#id'];
      $t_name = $form_state['triggering_element']['#name'];
      $t_action = $form_state['triggering_element']['#value'];
      $t_parts = explode('_', $t_name);
      $t_label = $t_parts[0];
      $t_index = $t_parts[1];
      //dpm($t_id, 'id of input');
      //dpm($t_name, 'name of input');
    }
    if (isset($t_action) and strstr($t_name,'clause')) {
      switch ($t_action) {
        case '+':
        $nc++;
        break;
        case '-':
        // need to handle this after the table of column info is assembled
        break;
      }
    }
    if (isset($form_state['values']['wherecols'])) {
      $row_names = array_keys($form_state['values']['wherecols']);
    } else {
      // @todo: if cached, retrieve rows and set rownames here
      $row_names = array('cols_0');
    }
    $row_indices = array();
    foreach ($row_names as $rn) {
      // preserve the index passsed in so that we handle deleting columns correctly
      $r_parts = explode('_', $rn);
      $r_label = $r_parts[0];
      $r_index = $r_parts[1];
      $row_indices[] = $r_index;
    }
    $i = 0;
    if (count($row_indices) < $nc) {
      $row_indices[] = max($row_indices) + 1;
    }
    foreach ($row_indices as $i) {
      $line = $row_template;
      // set the name so that form processing knows 
      // from which row the button was clicked
      // the #id attribute will also be updated to match this automatically
      $line['delclause']['#name'] = 'delclause_' . $i;
      $line['addclause']['#name'] = 'addclause_' . $i;
      // @todo: if cached, set values here
      $this->data_ui_tabularize($line);
      $element["cols_$i"] = $line;
    }
    if (isset($t_action) and strstr($t_name,'clause')) {
      switch ($t_action) {
        case '-':
        unset($element["cols_$t_index"]);
        break;
      }
    }
    $element[] = array(
      '#markup' => "</table>",
    );
    return $element;
  }
  
  function assembleQuery($form, &$form_state) {
    $sqlstring = '';
    //$this->reportstring .= "Checking table nameL: $this->tablename <br>";
    if ($this->tablename <> '') {
       $ptable = $this->tablename;
    } else {
      watchdog('om', "table name not specified");
       //$ptable = $this->parentobject->dbtblname;
    }

    # selected columns and any applied functions
    $selstring = '';
    $groupstring = '';
    $sdel = '';
    $gdel = '';
    foreach ($form_state['values']['querycols'] as $colkey => $thiscol) {
       $qcol = $thiscol['colname'];
       $qfunc = $thiscol['function'];
       $qalias = $thiscol['alias'];
       $qtxt = $thiscol['params'];
       $grpcol = $qcol; # assume we use the name, unless we have an alias
       if (strlen($qcol) > 0) {
          $formatted = $this->formatFunctionFull($qfunc, $qcol, $qtxt);
          $selstring .= "$sdel " . $formatted;
          $grpcol = $formatted;
          $sdel = ',';
          if (strlen($qalias) == 0) {
             $pieces = array($qcol);
             if ( (strlen($qfunc) > 0) and ($qfunc <> 'none')) {
                $pieces[] = $qfunc;
             }
             $qalias = implode("_", $pieces);
          }
          $selstring .= " AS \"$qalias\"";
          if ( !$this->isAggregate($qfunc) ) {
             $groupstring .= "$gdel $grpcol ";
             $gdel = ',';
          }
       }
    }

    # condition clause
    $wdel = '';
    $wherestring = '';
    foreach ($form_state['values']['wherecols'] as $colkey => $thiscol) {
       $wcol = $thiscol['colname'];
       $wop = $thiscol['function'];
       $wval = $thiscol['value'];
       if (strlen($wcol) > 0) {
          # must have all three, a colunn, an operator, and a comparison value
          if (strlen($wop) > 0) {
             # Format this clause
             $subclause = $this->formatWhereClause($wop, $wcol, $wval);
             if (strlen($subclause) > 0) {
                $wherestring .= "$wdel $subclause ";
                $wdel = 'AND';
             }
          }
       }
    }
    
    if (ltrim(rtrim($selstring)) == '') {
       $selstring = '*';
    }
    $this->sqlstring = "SELECT $selstring FROM ";
    if ($this->quote_tablename) {
       $this->sqlstring .= " \"$ptable\" ";
    } else {
       $this->sqlstring .= " $ptable ";
    }
    if (strlen($wherestring) > 0) {
       $this->sqlstring .= " WHERE $wherestring ";
    }
    if (strlen($groupstring) > 0) {
       $this->sqlstring .= " GROUP BY $groupstring ";
    }
    // *********************************
    // *** Handle ordering
    // *********************************
    $odel = '';
    $orderstring = '';
    foreach ($form_state['values']['ordercols'] as $colkey => $colinfo) {
      $ocol = $colinfo['colname'];
      $otype = $colinfo['function'];
      if (strlen(rtrim(ltrim($ocol))) > 0) {
         $orderstring .= " $odel \"$ocol\" $otype";
         $odel = ',';
      }
    }
    if (strlen($orderstring) > 0) {
       $this->sqlstring .= " ORDER BY $orderstring ";
    }
    $form_state['sqlstring'] = $this->sqlstring;
  }

  function isAggregate($func) {
    
    $isagg = 0;
    switch ($func) {

       case 'none':
          $isagg = 0;
       break;

       case '':
          $isagg = 0;
       break;
       
       default:
          $isagg = 1;
       break;
       
    } 
    return $isagg;
  }

  function formatFunctionFull($func, $col, $params) {
    
    $formatted = '';
    // we no longer sanitize this since drupal formapi has done it for us
    $ps = explode(",", $params);
    switch ($func) {

       case 'none':
          if ($ps[0] <> '') {
             $formatted = "round( \"$col\"::numeric, " . intval($ps[0]) . " ) ";
          } else {
             $formatted = " \"$col\" ";
          }
       break;

       case '':
          if ($ps[0] <> '') {
             $formatted = "round( \"$col\"::numeric, " . intval($ps[0]) . " ) ";
          } else {
             $formatted = " \"$col\" ";
          }
       break;
       
       case 'min':
          if ($ps[0] <> '') {
             $formatted = "round(min( \"$col\" )::numeric, " . intval($ps[0]) . " ) ";
          } else { 
             $formatted = "min( \"$col\" ) ";
          }
       break;

       case 'mean':
          if ($ps[0] <> '') {
             $formatted = "round(avg( \"$col\" )::numeric, " . intval($ps[0]) . " ) ";
          } else { 
             $formatted = "avg( \"$col\" ) ";
          }
       break;

       case 'median':
          if ($ps[0] <> '') {
             $formatted = "round(median( \"$col\" )::numeric, " . intval($ps[0]) . " ) ";
          } else { 
             $formatted = "median( \"$col\" ) ";
          }
       break;

       case 'max':
          if ($ps[0] <> '') {
             $formatted = "round(max( \"$col\" )::numeric, " . intval($ps[0]) . " ) ";
          } else { 
             $formatted = "max( \"$col\" ) ";
          }
       break;

       case 'gini':
          $formatted = "gini(array_accum( \"$col\" ) ) ";
       break;

       case 'quantile':
          switch (count($ps)) {
             case 2:
             $formatted = "round(r_quantile(array_accum( \"$col\" ), " . $ps[0] . ")::numeric, " . $ps[1] . " ) ";
             break;
             
             case 1:
             $formatted = "r_quantile(array_accum( \"$col\" ), " . $ps[0] . " ) ";
             break;
             
             default:
             $formatted = "r_quantile(array_accum( \"$col\" ), 0.5 ) ";
             break;
          }
       break;

       case 'sum':
          if ($ps[0] <> '') {
             $formatted = "round(sum( \"$col\" )::numeric, " . intval($ps[0]) . " ) ";
          } else { 
             $formatted = "sum( \"$col\" ) ";
          }
       break;

       case 'count':
          $formatted = "count( \"$col\" ) ";
       break;

       case 'stddev':
          if ($ps[0] <> '') {
             $formatted = "round(stddev( \"$col\" )::numeric, " . intval($ps[0]) . " ) ";
          } else { 
             $formatted = "stddev( \"$col\" ) ";
          }
       break;
       
       default:
          $formatted .= " $func ( \"$col\" ) ";
       break;
    }
    
    return $formatted;
  }

  function formatWhereClause($wop, $wcol, $wval) {
    $whereclause = '';

    //if (!is_numeric($wval)) {
    //   $wval = "'" . $wval . "'";
    //}
    $wop = isset($this->where_functions[$wop]) ? $this->where_functions[$wop] : $wop;

    switch ($wop) {
       case 'notnull':
          $whereclause = "\"$wcol\" IS NOT NULL ";
       break;
       
       case 'in':
          $whereclause = "\"$wcol\" IN ( $wval ) ";
       break;

       default:
          $whereclause = "\"$wcol\" $wop $wval ";
       break;
    }

    return $whereclause;
  }

  function getFunctionFormat($func) {

    $fopen = '';
    $fclose = '';

    switch ($func) {
       case 'min':
          $fopen = 'min(';
          $fclose = ')';
       break;

       case 'mean':
          $fopen = 'avg(';
          $fclose = ')';
       break;

       case 'median':
          $fopen = 'median(';
          $fclose = ')';
       break;

       case 'max':
          $fopen = 'max(';
          $fclose = ')';
       break;

       case 'gini':
          $fopen = 'gini(array_accum(';
          $fclose = '))';
       break;

       case 'sum':
          $fopen = 'sum(';
          $fclose = ')';
       break;

       case 'count':
          $fopen = 'count(';
          $fclose = ')';
       break;

       case 'stddev':
          $fopen = 'stddev(';
          $fclose = ')';
       break;

       default:
       break;
    }

    return array($fopen, $fclose);
  }


  function data_ui_tabularize(&$line, $oe = 'odd') {
    $i = 0;
    foreach (array_keys($line) as $key) {
      if ($line[$key]['#type'] == 'hidden') {
        $style = 'style=\"display:none;\"';
      } else {
        $style = '';
      }
      $line[$key]['#suffix'] = isset($line[$key]['#suffix']) ? $line[$key]['#suffix'] : '';
      $line[$key]['#prefix'] = isset($line[$key]['#prefix']) ? $line[$key]['#prefix'] : '';
      switch ($i) {
        case 0:
          $line[$key]['#prefix'] = "<tr class='tablefield-row-0 $oe'><td $style>" . $line[$key]['#prefix'];
          $line[$key]['#suffix'] .= "</td>";
        break;
        case (count($line) - 1):
          $line[$key]['#prefix'] = "<td $style>" . $line[$key]['#prefix'];
          $line[$key]['#suffix'] .= "</td></tr>";
        break;
        default:
          $line[$key]['#prefix'] = '<td $style>' . $line[$key]['#prefix'];
          $line[$key]['#suffix'] .= '</td>';
        break;
      }
      $i++;
    }
  }
  
  public function submitForm(&$form, &$form_state) {
    //dpm($program);
    $form_state['rebuild'] = TRUE;
    // do the query
  }

  function EntityDefaults() {
    // allow table to be over-ridden by settings
    $options = array(
      'table' => array ('default' =>array(), 'allow_override'=>TRUE),
    );
    return $options + parent::EntityDefaults();
  }

  function list2file($listrecs, $filename='') {

    if (strlen($filename) == 0) {
      $filename = 'datafile.' . $this->componentid . '.csv';
    }

    $datafile = $this->outdir . '/' . $filename;
    $colnames = array(array_keys($listrecs[0]));
    putDelimitedFile($datafile,$colnames,$this->translateDelim($this->delimiter),1,'unix');

    if (count($listrecs) > 0) {
      if ($this->debug) {
        $this->logDebug("Appending values to monthly $datafile<br>");
      }
      $fdel = '';
      $outform = '';
      foreach (array_keys($listrecs[0]) as $thiskey) {
        if (in_array($thiskey, array_keys($this->logformats))) {
          # get the log file format from here, if it is set
          if ($this->debug) {
            $this->logDebug("Getting format for log table " . $thiskey . "\n");
          }
          $outform .= $fdel . $this->logformats[$thiskey];
        } else {
          if (is_numeric($listrecs[0][$thiskey])) {
            $outform .= $fdel . $this->numform;
          } else {
            $outform .= $fdel . $this->strform;
          }
        }
        $fdel = $this->translateDelim($this->delimiter);
      }
      # format for output if records exist for each year in the dataset
      $outarr = nestArraySprintf($outform, $listrecs);
      putArrayToFilePlatform("$datafile", $outarr,0,'unix');
    }
    return $filename;
  }
}

class ObjectmodelQueryWizardHandler extends DataQueryWizardHandler {
  /*
   * needs to get list of files that it could load or scenarios that it could load
   * will compile list of tables from this
   * parent class DataQueryWizardHandler will then provide the remainder of the interface
  */
  var $elementid;
  var $files;
  var $model_db;
  var $session_db;
  var $server_ip;
  var $outdir;
  
  public function __construct($config = array()) {
    global $session_db, $listobject, $server_ip, $outdir;
    if (isset($config['elementid'])) {
      $this->elementid = $config['elementid'];
    } else {
      //watchdog(WATCHDOG_ERROR,'Elementid missing from ObjectmodelQueryWizardHandler');
    }
    $this->model_db = $listobject;
    $this->session_db = $session_db;
    $this->server_ip = $server_ip;
    $this->outdir = $outdir;
    $this->columns = array();
  }
  
  public function buildOptionsForm(&$form, $form_state) {
    parent::buildOptionsForm($form, $form_state);
    // add selector to define element to use?
  }
  
  public function buildForm(&$form, $form_state) {
    // add selector to define table
    $this->model_db->debug = 1;
    $run_object = new ObjectModelRunData(
      array(
        'session_db' => $this->session_db,
        'model_db' => $this->model_db,
        'server_ip' => $this->server_ip,
        'outdir' => $this->outdir,
      )
    );
    $runs = $run_object->getModelRunList($this->elementid);
    // elementid, runid, startdate, enddate, run_Date
    $run_options = array();
    $runid = FALSE;
    if (isset($form_state['values']['runid'])) {
      $runid = $form_state['values']['runid'];
    }
    foreach ($runs as $ri) {
      $start = date('Y-m-d', strtotime($ri['starttime']));
      $end = date('Y-m-d', strtotime($ri['endtime']));
      $run_options[$ri['runid']] = "#$ri[runid] - from $start to $end (completed: $ri[run_date])";
      if ($runid == $ri[runid]) {
        $remote_url = $ri['remote_url'];
      }
    }
    $form['runid'] = array(
      '#title' => 'Select Model Scenario',
      '#weight' => -5,
      '#type' => 'select',
      '#default_value' => NULL,
      '#options' => $run_options,
    );
    if ($runid) {
      $form['download'] = array(
        '#weight' => -4,
        '#markup' => l("Download Complete Model Scenario Run File",$remote_url)
      );
      // get columns
      $run_object->loadSessionTable($this->elementid, $runid);
      $this->tablename = $run_object->session_table;
      $cols = $this->session_db->getColumns($this->tablename);
      $this->columns = array_combine($cols, $cols);
    }
    if (count($this->columns) > 0) {
      $form['qw_intro'] = array(
        '#weight' => -3,
        '#markup' => "<br>Or, use the form below to design a custom data query for the selected model run.<hr>",
      );
    }
    parent::buildForm($form, $form_state);
  }
  
}

// queryWizardComponent - old version OM data ui query creator
// needs a lot of work - previous version extended modelSubObject, 
// so this may have some calls to non-existent parent methods
// this is just here now as a placeholder and code base to be reworked
class ObjectModelRunData {
  var $session_db;
  var $model_db;
  var $server_ip;
  var $outdir;
  var $session_table;
  var $dbcoltypes;
  
  function __construct($config) {
    if (isset($config['session_db'])) {
      $this->session_db = $config['session_db'];
    }
    if (isset($config['model_db'])) {
      $this->model_db = $config['model_db'];
    }
    if (isset($config['server_ip'])) {
      $this->server_ip = $config['server_ip'];
    }
    if (isset($config['outdir'])) {
      $this->outdir = $config['outdir'];
    }
    if (isset($config['dbcoltypes'])) {
      $this->dbcoltypes = $config['dbcoltypes'];
    } else {
      $this->dbcoltypes = array();
    }
  }
  
  function checkSession() {
     $this->session_db = $this->session_db;
     $sessionid = session_id();
     // session tables are shared across all users
     $sessionid = -1;
     $this->session_db->querystring = " select count(*) from sessions where session_id = '" . $sessionid . "'";
     //$this->session_db->querystring = " select count(*) from sessions where session_id = '" . $sessionid . "'";
     //error_log("Session Query: " . $this->session_db->querystring);
     $this->session_db->performQuery();
     $num = $this->session_db->getRecordValue(1,'count');
     if ($num == 0) {
        //$this->session_db->querystring = " insert into sessions ( session_id ) values ( '" . session_id() . "' )";
        $this->session_db->querystring = " insert into sessions ( session_id ) values ( '" . $sessionid . "' )";
        //error_log($this->session_db->querystring);
        $this->session_db->performQuery();
     }
  }

  function getModelRunList ($elementid, $runids = -1, $debug = 1) {
     if (is_array($runids)) {
        $runlist = join(",", $runids);
     }
     
     $this->model_db->querystring = "  select elementid, runid, run_date, starttime, endtime, remote_url ";
     $this->model_db->querystring .= " from scen_model_run_elements ";
     $this->model_db->querystring .= " where elementid = $elementid ";
     if ($runids <> -1) {
        $this->model_db->querystring .= " and runid in ($runlist) ";
     }
     $this->model_db->querystring .= " order by runid ";
     if ($debug) {
        error_log($this->model_db->querystring);
     }
     $this->model_db->performQuery();
     return $this->model_db->queryrecords;
  }

  function getSessionTableNames($elementid, $runid = -1, $data_element = '') {
     // check for session existence
     $innerHTML = '';
     $this->checkSession();
     $rm = 0;
     $remote = 0;
     // make table name
     if (strlen($data_element) == 0) {
        // session tables are shared across all useres
        $sessionid = -1;
        // this is the master log table for this element
        if ($runid >= 0) {
           $session_table = $sessionid . "_$runid" . "_$elementid";
        } else {
           $session_table = $sessionid . "_$elementid";
        }
        $this->model_db->querystring = "  select output_file, remote_url, host, run_date from scen_model_run_elements where runid = $runid and elementid = $elementid";
        //$innerHTML .= "$this->model_db->querystring .<br>";
        //error_log("Session Query: " . $this->model_db->querystring);
        $this->model_db->performQuery();
        if (count($this->model_db->queryrecords) > 0) {
           $file_host = $this->model_db->getRecordValue(1,'host');
           if ($file_host <> $this->server_ip) {
              $filename = $this->model_db->getRecordValue(1,'remote_url');
              $remote = 1;
           } else {
              $filename = $this->model_db->getRecordValue(1,'output_file');
           }
           $innerHTML .= "This IP: $this->server_ip, file IP: $file_host ...";
           $run_date = $this->model_db->getRecordValue(1,'run_date');
        } else {
           $filename = $this->outdir . "/runlog" . $runid . "." . $elementid . ".log";
           $innerHTML .= "Failed to locate run record $filename . ";
           $innerHTML .= "Query: " . $this->model_db->querystring . "<br>";
           $rm = 1;
        }
     }
     
     return array('tablename'=>$session_table, 'filename'=>$filename, 'innerHTML'=>$innerHTML, 'run_date' => $run_date, 'record_missing'=>$rm, 'remote' => $remote, 'query'=>$this->model_db->querystring);
  }

  function checkSessionTable($elementid, $runid = -1, $data_element = '') {
     $tinfo = array('table_exists'=>0, 'file_exists'=>0, 'innerHTML'=>'', 'tablename'=>'');
     // make table name
     $sinfo = $this->getSessionTableNames($elementid, $runid, $data_element);
     $tinfo['innerHTML'] .= $sinfo['innerHTML'];
     $session_table = $sinfo['tablename'];
     $filename = $sinfo['filename'];
     $tinfo['remote'] = $sinfo['remote'];
     $tinfo['innerHTML'] .= $filename . "<br>";
     $tinfo['tablename'] = $session_table;
     // if table exists, don't do anything, just get the last version
     // only would drop the table if the user made a call to the routine to change the model run/scenario
     // which doesn't happen through this display routine
     
     if ($this->session_db->tableExists($session_table) == 1) {
        $tinfo['table_exists'] = 1;
        $this->session_table = $session_table;
     }
     //if (file_exists($filename)) {
     if (!$tinfo['record_missing']) {
        $fe = fopen($filename,'r');
        if ($fe) {
           $tinfo['file_exists'] = 1;
        }
        fclose($fe);
     } else {
        $tinfo['file_exists'] = 0;
     }
     
     return $tinfo;
  }

  function loadSessionTable($elementid, $runid = -1, $data_element = '') {

     $dbcoltypes = $this->dbcoltypes;
     error_log("Data types:" . print_r($dbcoltypes, 1));
     $tableinfo = $this->checkSessionTable($elementid, $runid, $data_element);
     $file_exists = $tableinfo['file_exists'];
     $remote_file = $tableinfo['remote'];
     
     $lobj = array('innerHTML'=>'', 'session_table'=>'', 'error'=>0);
     // make table name
     $sinfo = $this->getSessionTableNames($elementid, $runid, $data_element);
     $session_table = $sinfo['tablename'];
     $filename = $sinfo['filename'];
     $run_date = $sinfo['run_date'];
     $lobj['innerHTML'] .= $sinfo['innerHTML'];
     
     // if table exists, don't do anything, just get the last version
     // only would drop the table if the user made a call to the routine to change the model run/scenario
     // which doesn't happen through this display routine
     
     if (!($this->session_db->tableExists($session_table) == 1)) {
        $loadtable = 1;
        $this->session_table = $session_table;
     } else {
        $loadtable = 0;
        // a table exists, but lets check to make sure that it is updated
        if ($file_exists) {

           if ($remote_file) {
              $file_epoch = filemtime_remote($filename);
              $lobj['innerHTML'] .= "File is to be retrieved from a remote host: $host ...";
           } else {
              $file_epoch = filemtime($filename);
           }
           $lobj['innerHTML'] .= "Checking $filename ...";
           $this->session_db->querystring = " select creation_date from session_tbl_log where tablename = '$session_table'";
           $this->session_db->performQuery();
           if (count($this->session_db->queryrecords) > 0) {
              $tabledate = $this->session_db->getRecordValue(1,'creation_date');
              $table_epoch = date('U',strtotime($tabledate));
           } else {
              $tabledate = '';
              $table_epoch = -1;
           }
           if ($file_epoch > $table_epoch) {
              //refresh table
              $this->session_db->querystring = " delete from session_tbl_log where tablename = '$session_table'";
              $this->session_db->performQuery();
              $this->session_db->querystring = " drop table \"$session_table\" ";
              $this->session_db->performQuery();
              $loadtable = 1;
           }
        } else {
           $lobj['innerHTML'] .= "Can not locate $filename exiting...";
           $lobj['error'] = 1;
           $loadtable = 0;
        }
     }
     $lobj['innerHTML'] .= "Selected $filename (run on $run_date) <br>";
     if ($loadtable) {
        $lobj['innerHTML'] .= "Loading $filename  <br>";
        $t = time();
        $darr = delimitedFileToTable($this->session_db, $filename, ',', $session_table, 1, -1, array(), $dbcoltypes, 0);
        $t2 = time() - $t;
        $lobj['innerHTML'] .= "Loaded " . count($darr) . " data lines.<br>";
        error_log("Loaded " . count($darr) . " data lines in $t2 seconds<br>");
        $this->session_db->querystring = " insert into session_tbl_log (tablename) values ('$session_table') ";
        $this->session_db->performQuery();
        if (count($darr) == 0) {
           $lobj['error'] = 1;
        }      
     }
     $lobj['session_table'] = $session_table;
     return $lobj;
  }

}

class queryWizardComponent {

  var $goutdir = '';
  var $gouturl = '';
  var $imgurl = '';
  var $maxreportrecords = 100; 
  # maximum number of records to actually show in the report string, above this defers to downloadable file
  var $value_dbcolumntype = 'varchar'; // just a dummy since, we should not even need to dump this to a db table, since it is meaningless
  var $quote_tablename = 0; // if we have unorthodox tale names, we may 
  //need to quote them, but default to not, since we may be given a 
  //sub-query as a table, in which case quoting would goof it up
  # query specific properties
  # column props
  #var $qcols = array();
  #var $qcols_func = array();
  #var $qcols_alias = array();
  var $qcols;
  var $qcols_func;
  var $qcols_alias;
  var $qcols_txt; # text entry column, now it is manually entered, but this is unsafe, so we should soon make it a wizard type column
  # column props
  var $wcols = array();
  var $wcols_op = array();
  var $wcols_value = array();
  var $wcols_refcols = array();
  # order by props
  var $ocols = array();
  var $loggable = 0;
  // data source info
  var $is_datasource = 1;
  var $datasource_name = 'parent.publicvars';
  var $tablename = '';
  var $sqlstring = '';


  # requires lib_plot.php to produce graphic output

  function setState() {
    parent::setState();
    /*
    $this->qcols = array('name','Qout','Qin');
    $this->qcols_func = array('','min','min');
    $this->qcols_alias = array('a','b','c');
    */
    $this->reportstring = '';
  }

  function wake() {
    parent::wake();
    if (is_object($this->parentobject)) {
       $this->tablename = $this->parentobject->dbtblname;
    } else {
       $this->tablename = '';
    }
    /*
    $this->qcols = array('name','Qout','Qin');
    $this->qcols_func = array('','min','min');
    $this->qcols_alias = array('a','b','c');
    */
  }

  function step() {
    // all step methods MUST call getInputs(), execProcessors(),  and logstate()
    parent::step();
  }

  function evaluate() {
    # do nothing
    $this->result = NULL;
  }

  function finish() {
    //$this->runAndDisplayResult()
  }

  function runAndDisplayResult() {
    # create query string, need to know parent name, or simply pass the SQL to the parent. hmm. which to do?
    $this->assembleQuery();

    if ($this->debug) {
       $this->reportstring .= $this->sqlstring . "<br>";
    }
    if (is_object($this->listobject)) {
       $this->listobject->queryrecords = $this->executeQuery();
       $this->listobject->show = 0;
       $this->listobject->showList();
       $filename = $this->list2file($this->listobject->queryrecords);
       #$filename = $this->list2file(array());
       if (count($this->listobject->queryrecords) <= $this->maxreportrecords) {
          $this->reportstring .= "<b>Query Output:</b><br> " . $this->listobject->outstring . "<br>";
       } else {
          $this->listobject->queryrecords = array_slice($this->listobject->queryrecords, 0, $this->maxreportrecords - 1);
          $this->listobject->show = 0;
          $this->listobject->showList();
          $this->reportstring .= "<b>Query Output:</b><br> " . $this->listobject->outstring . "<br><b>Max display records exceeded, download file (below) to view results.<br>";
       }
       $this->reportstring .= "<b>Download Data:</b><br> <a href=" . $this->outurl . '/' . $filename . ">Click Here</a><br>";
    }
  }

  function executeQuery() {

    if (is_object($this->listobject)) {
       if ($this->listobject->tableExists( $this->tablename)) {
          $this->listobject->querystring = $this->sqlstring;
          $this->listobject->performQuery();
          $theserecs = $this->listobject->queryrecords;
        } else {
          $this->reportstring .= "<b>Query Error:</b><br> Query output requested on $this->name, but $this->tablename dioes not exist. ";
          $this->reportstring .= "Be sure that 'Log to db' is selected in parent object: " . $this->parentobject->name . " <br>";
       }
    } else {
       $theserecs = array();
       $this->reportstring .= "<b>Query Error:</b><br> Query output requested on $this->name, but listobject not enabled. ";
       $this->reportstring .= "Be sure that 'Log to db' is selected in parent object: " . $this->parentobject->name . " <br>";
    }
    
    /*
    if (is_object($this->listobject) and $this->parentobject->log2db) {
       $this->listobject->querystring = $this->sqlstring;
       $this->listobject->performQuery();
       $theserecs = $this->listobject->queryrecords;
    } else {
       $theserecs = array();
       $this->reportstring .= "<b>Query Error:</b><br> Query output requested on $this->name, but listobject not enabled. ";
       $this->reportstring .= "Be sure that 'Log to db' is selected in parent object: " . $this->parentobject->name . " <br>";
    }
    */
    
    return $theserecs;
  }

  function getPropertyClass($propclass) {
    # Call parent method to get any standard classes matching the criteria
    $returnprops = parent::getPropertyClass($propclass);
    foreach ($propclass as $thisclass) {
      switch ($thisclass) {
        case 'data_columns':
        $selectcolumns = array(
           'qcols'=>$this->qcols,
           'qcols_func'=>$this->qcols_func,
           'qcols_alias'=>$this->qcols_alias,
           'qcols_txt'=>$this->qcols_txt
        );
        $tpa = $this->makeAssoc($selectcolumns);
        $props = array();
        foreach ($tpa as $thisrow) {
          if ($thisrow['qcols_alias'] == '') {
            if ( in_array($thisrow['qcols_func'], array ('none', '')) ) {
              $val = $thisrow['qcols'];
            } else {
              $val = $thisrow['qcols_func'];
            }
          } else {
            $val = $thisrow['qcols_alias'];
          }
          $props[] = $val;
        }
        $returnprops = array_unique(array_merge($returnprops, $props));
        break;

        case 'broadcast_vars':
        $returnprops = array_unique(array_merge($returnprops, $this->broadcast_varname));
        break;
      }
    }
    #$returnprops = $this->plotgens;
    return $returnprops;
  }
}

?>