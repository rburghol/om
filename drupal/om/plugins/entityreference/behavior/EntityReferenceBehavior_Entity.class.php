<?php

class EntityReferenceBehavior_Entity extends EntityReferenceBehavior_ViewsFilterSelect {
  var $pairs = array();
  public function schema_alter(&$schema, $field) {
    // @todo: a method to check when this is added AFTER field creation
    //   so that the field table can be modified to include missing
    // add a serial field
    $field_name = $field['field_name'];
    $field_colname = $field_name . '_erefid';
    $schema['columns']['erefid'] = array(
      'description' => 'Primary key for Entity Reference Record',
      'type' => 'serial',
      'unsigned' => TRUE,
      'not null' => FALSE,
    );      
    //$schema['indexes']['erefid'] = array($field_colname);
    $schema['indexes']['erefid'] = array('erefid');
  }
  
  public function schema_retrofit($field) { 
    if ($field) {
      dpm($field,"schema_retrofit $field_name");
      $field_table = key($field['storage']['details']['sql']['FIELD_LOAD_CURRENT']);
      $revision_table = key($field['storage']['details']['sql']['FIELD_LOAD_REVISION']);
      $field_colname = $field_name . '_erefid';
      $schema = array('columns'=>array(), 'indexes'=>array(),'primary key'=>array());
      $this->schema_alter($schema, $field);
      //dpm(t("Checking for serial $field_colname on $field_table"));
      if (!db_field_exists($field_table, $field_colname)) {
        //dpm($schema,"adding $field_colname to $field_table");
        db_add_field($field_table, $field_colname, $schema['columns']['erefid'], array('indexes' => $schema['indexes']));
        $rev_schema = $schema['columns']['erefid'];
        $rev_schema['type'] = 'int';
        $rev_schema['size'] = 'big';
        //dpm($rev_schema,"adding $field_colname to $revision_table");
        db_add_field($revision_table, $field_colname, $rev_schema, array('indexes' => $schema['indexes']));
        // Get the current settings
        $result = db_query('SELECT data FROM {field_config} WHERE field_name = :name', array(':name' => $field_name))->fetchField();

        // Change the settings
        $data = unserialize($result);
        foreach ($data['settings']['handler_settings']['behaviors'] as $b => $s) {
          $data['settings']['handler_settings']['behaviors'][$b]['status'] = 0;
        }
        $data['settings']['handler_settings']['behaviors']['EntityReferenceBehavior_Entity'] = array('status' => 1);
        // Write settings back to the database.
        //dpm($data,'proposed update');
        if ($do) {
          //dpm(TRUE,'performing update');
          db_update('field_config')
            ->fields(array('data' => serialize($data)))
            ->condition('field_name', $field_name)
            ->execute();

          // Flush the caches
          drupal_flush_all_caches();
        }
      }
    } else {
      drupal_set_message("Cannot find field $field_name");
    }
  }
  
  public function views_data_alter(&$data, $field) {
    // see EntityReferenceBehavior_ViewsFilterSelect for example
    parent::views_data_alter($data, $field);
    // now add ts and property relationships
    $entity_info = entity_get_info($field['settings']['target_type']);
    $field_name = $field['field_name'];
    //dpm($field, "Searching for $field_name");
    if (isset($data['field_data_' . $field_name])) {
      $table_data = &$data['field_data_' . $field_name];
      $table_data['table']['group'] = 'Entity Reference';
      $table_data['table']['title'] = t('Entity Reference from $field_name to dH Properties');
      $table_data['table']['help'] = t('Entity Reference from $field_name to dH Properties.');
      //dpm($table_data,'table data');
      // Set the entity id filter to use the in_operator handler with our
      // own callback to return the values.
      $table_data[$field_name]['filter']['handler'] = 'views_handler_filter_in_operator';
      $table_data[$field_name]['filter']['options callback'] = 'entityreference_views_handler_options_list';
      $table_data[$field_name]['filter']['options arguments'] = array($field['field_name']);
      $table_data['erefid'] = array(
        'title' => t('Eref Serial ID'),
        'help' => t('Unique serial integer for each individual relationship.'),
        'filter' => array(
          'handler' => 'views_handler_filter_numeric',
        ),
        'field' => array(
          'title' => t('Eref Serial ID'), // Distinguish from the normal value handler.
          'help' => t('Unique serial integer for each individual relationship.'),
          'handler' => 'views_handler_field',
          'click sortable' => TRUE,
        ),
        'sort' => array(
          'handler' => 'views_handler_sort',
        ),
        'argument' => array(
           'handler' => 'views_handler_argument_numeric',
        ),
      );
      
      $table_data['dh_properties'] = array(
        'real field' => 'adminid',
        'relationship' => array(
          'title' => 'dH Properties Link to ' . $field_name,
          'help' => 'dH Properties Link to ' . $field_name,
          //'handler' => 'views_handler_relationship',
          // Testing -- this should avoid adding extra copies of the relationship table
          //            but trusts that the user is not specifying relationships that don't exist
          'handler' => 'views_handler_relationship_attributable',
          'label' => t('CrossTab: Link dh Properties to ' . $field_name),
          'base' => 'dh_properties',
          'base field' => 'featureid',
          'left_table' => $field_name,
          'field' => $field_name . '_erefid',
        )
      );
      if (!isset($table_data['dh_properties']['relationship']['extra'])) {
        $table_data['dh_properties']['relationship']['extra'] = array();
      }
      // must add in this fashion to prevent over-writing extra join clauses added in views UI
      $table_data['dh_properties']['relationship']['extra'][] = array(
        'field' => 'entity_type', 
        'operator' => '=', 
        'value' => $field_name
      );
    } else {
      //dpm(FALSE,"Can not find");
    }
  }

  public function presave($entity_type, $entity, $field, $instance, $langcode, &$items) {
    list($entity_id, $vid, $bundle) = entity_extract_ids($entity_type, $entity);
    $pairs = &drupal_static("$entity_type:" . $field['field_name'] . ":$entity_id");
    if (!isset($pairs)) {
      $pairs = array();
    }
    // items has target_id ONLY, but we know entity id and entity type,
    // so we can gather the erefid of the previous value if it exists
    // then we can can get any properties attached to this erefid/fieldname
    // array becomes something like:
    //   target_id => 77
    //   erefid => 132
    //   pids => array(9832,9834)
    //   delete => TRUE // default to delete, but set FALSE if in items list
    $colname = $field['field_name'] . '_erefid';
    $tblname = 'field_data_' . $field['field_name'];
    $targname = $field['field_name'] . '_target_id';
    list($entity_id, $vid, $bundle) = entity_extract_ids($entity_type, $entity);
    //dpm($items, 'items to presave()');
    // get all existing entries
    if ($entity_id > 0) {
      $q = "select entity_id, $colname, $targname from $tblname where entity_type = '$entity_type' and entity_id = $entity_id ";
      $result = db_query($q);
      while ($record = $result->fetchAssoc()) {
        $pairs[$record[$targname]] = array(
          'target_id' => $record[$targname],
          'erefid' => $record[$colname],
          'pids' => array(),
          'tids' => array(),
          'delete' => TRUE,
        );
        $q = "SELECT pid FROM {dh_properties} where entity_type = '$field[field_name]' and featureid = " . $record[$colname];
        $pairs[$record[$targname]]['pids'] = db_query($q)->fetchAllKeyed(0,0);
      }
      //dpm($pairs, 'erefs before handling field item values to presave()');
      foreach ($items as $item) {
        $target_id = $item['target_id'];    
        $pairs[$target_id] = $item;
        // don't delete
        $pairs[$target_id]['delete'] = FALSE;
        // get erefid, then get pid(s) from properties table of all props attached
        // to this erefid        
        // if erefid is null this is a new one, no worries, nothing to do
        // get new erefid by querying field for entity_type, entity_id, and targetid
        $q = "select $colname from $tblname where entity_type = '$entity_type' and $targname = $target_id and entity_id = $entity_id ";
        $erefid = db_query($q)->fetchField();
        if ($erefid) {
          $pairs[$target_id]['erefid'] = $erefid;
          $q = "SELECT pid FROM {dh_properties} where entity_type = '$field[field_name]' and featureid = $erefid ";
          $pairs[$target_id]['pids'] = db_query($q)->fetchAllKeyed(0,0);
        }
      }
      //dpm($pairs, 'erefs after handling field item values to presave()');
    }
  }
  /**
   * Act before updating an entity reference field.
   *
   * @see hook_field_update()
   */
  public function update($entity_type, $entity, $field, $instance, $langcode, &$items) {
    list($entity_id, $vid, $bundle) = entity_extract_ids($entity_type, $entity);
    $pairs = &drupal_static("$entity_type:" . $field['field_name'] . ":$entity_id");
    if (!isset($pairs)) {
      $pairs = array();
    }
  }

  /**
   * Act after updating an entity reference field.
   *
   * @see hook_field_attach_update()
   */
  public function postUpdate($entity_type, $entity, $field, $instance) {
    list($entity_id, $vid, $bundle) = entity_extract_ids($entity_type, $entity);
    // pairs contains the src + dest from this field prior to update
    // because the eref update process involves deleting and re-adding all erefs
    // we need to update all property and TS values associated with these erefs
    // to use the new erefid
    $pairs = &drupal_static("$entity_type:" . $field['field_name'] . ":$entity_id");
    if (!isset($pairs)) {
      $pairs = array();
    }
    $colname = $field['field_name'] . '_erefid';
    $tblname = 'field_data_' . $field['field_name'];
    $targname = $field['field_name'] . '_target_id';
    //dpm($pairs, 'erefs before handling field item values to postUpdate()');
    foreach ($pairs as $item) {
      $target_id = $item['target_id'];
      // check if erefid is null this is a new one, no worries, nothing to do
      if ($item['delete']) {
        // marked for deletion so handle all props and ts values
        if (count($item['pids'])) {
          // do an update
          //dpm($item['pids'],'deleting');
          entity_delete_multiple('dh_properties', $item['pids']);
        }
        if (count($item['tids'])) {
          // do an update
          //dpm($item['tids'],'deleting');
          entity_delete_multiple('dh_timeseries', $item['tids']);
        }
      } else {
        if (isset($item['erefid'])) {
          $target_id = $item['target_id'];
          // get new erefid by querying field for entity_type, entity_id, and targetid
          $q = "select $colname from $tblname where entity_type = '$entity_type' and $targname = $target_id and entity_id = $entity_id ";
          $erefid = db_query($q)->fetchField();
          //dpm($pairs[$target_id], "updating erefid from ". $pairs[$target_id]['erefid'] . " to $erefid");
          $pairs[$target_id]['erefid'] = $erefid;
          // get erefid, then get pid(s) from properties table of all props attached
          // to this erefid
          if (count($item['pids'])) {
            // do an update
            $props = entity_load('dh_properties', $item['pids']);
            foreach ($props as $thisprop) {
              $thisprop->featureid = $erefid;
              $thisprop->save();
            }
            //$pids = implode(',', $item['pids']);
            //$q = " update {dh_properties} set featureid = $erefid where pid in ($pids) ";
            //db_query($q);
          }
        }
      }
    }
  }
  
  /**
   * Act before deleting an entity with an entity reference field.
   *
   * @see hook_field_delete()
   */
  public function delete($entity_type, $entity, $field, $instance, $langcode, &$items) {
    // @todo: 
    // find eref items that are affiliated with the entity in question 
    // and delete all props and ts attached to those erefs
  }
}
