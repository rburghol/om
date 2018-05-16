<?php

/**
 * @file
 * Views' relationship handlers.
 */

/**
 * @defgroup views_relationship_handlers Views relationship handlers
 * @{
 * Handlers to tell Views how to create alternate relationships.
 */

/**
 * Simple relationship handler that allows a attributes to be joined to field table entries
 * which then permits things like "attributable" Entity References - used by dh_properties and dh_timeseries
 *
 *
 * @ingroup views_relationship_handlers
 */
class views_handler_relationship_attributable extends views_handler {
  /**
   * Init handler to let relationships live on tables other than
   * the table they operate on.
   */
  function init(&$view, &$options) {
    parent::init($view, $options);
    if (isset($this->definition['relationship table'])) {
      $this->table = $this->definition['relationship table'];
    }
    if (isset($this->definition['relationship field'])) {
      // Set both real_field and field so custom handler
      // can rely on the old field value.
      $this->real_field = $this->field = $this->definition['relationship field'];
    }
  }

  /**
   * Get this field's label.
   */
  function label() {
    if (!isset($this->options['label'])) {
      return $this->ui_name();
    }
    return $this->options['label'];
  }

  function option_definition() {
    $options = parent::option_definition();


    // Relationships definitions should define a default label, but if they aren't get another default value.
    if (!empty($this->definition['label'])) {
      $label = $this->definition['label'];
    }
    else {
      $label = !empty($this->definition['field']) ? $this->definition['field'] : $this->definition['base field'];
    }

    $options['label'] = array('default' => $label, 'translatable' => TRUE);
    $options['required'] = array('default' => FALSE, 'bool' => TRUE);

    return $options;
  }

  /**
   * Default options form that provides the label widget that all fields
   * should have.
   */
  function options_form(&$form, &$form_state) {
    parent::options_form($form, $form_state);
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => t('Identifier'),
      '#default_value' => isset($this->options['label']) ? $this->options['label'] : '',
      '#description' => t('Edit the administrative label displayed when referencing this relationship from filters, etc.'),
      '#required' => TRUE,
    );

    $form['required'] = array(
      '#type' => 'checkbox',
      '#title' => t('Require this relationship'),
      '#description' => t('Enable to hide items that do not contain this relationship'),
      '#default_value' => !empty($this->options['required']),
    );
  }

  /**
   * Ensure the main table for this handler is in the query. This is used
   * a lot.
   */
  function ensure_my_table() {
    // Looks at table and table_queue
    // @todo: eliminate the "Do not use a relationship" option from the relationship config
    //        if the base table of the relationship is = to the view base table
    //        the standard handler will allow none to be selected -- this should not occur however
    //        since it DOES occur, we should anticipate and handle that (and then later fix the config screen)
    //        For now, it seems to handle this by returning the first matching relationship it encounters
    //        Which works OK, but we should not allow this assumed behavior
    //if ($this->table == 'field_data_field_link_agchem_material') {
    //  dpm($this,'views relationship');
    // dpm($this->query->table_queue,'table_queue before');
    //}
    if (!isset($this->table_alias)) {
      if (!method_exists($this->query, 'ensure_table')) {
        vpr(t('Ensure my table called but query has no ensure_table method.'));
        return;
      }
      if (!array_key_exists($this->table, $this->query->table_queue)) {
        return FALSE;
      }
      //$this->table_alias = $this->query->ensure_table($this->table, $this->relationship);
      // temporarily use local method for debugging
      $this->table_alias = $this->query->table_queue[$this->table]['alias'];
    }
    return $this->table_alias;
  }

  /**
   * Called to implement a relationship in a query.
   */
  function query() {
    // Figure out what base table this relationship brings to the party.
    $table_data = views_fetch_data($this->definition['base']);
    $base_field = empty($this->definition['base field']) ? $table_data['table']['base']['field'] : $this->definition['base field'];
    // this is over-ridden locally see this->ensure_my_table()
    $this->ensure_my_table();

    $def = $this->definition;
    $def['table'] = $this->definition['base'];
    $def['field'] = $base_field;
    $def['left_table'] = $this->table_alias;
    $def['left_field'] = $this->real_field;
    if (!empty($this->options['required'])) {
      $def['type'] = 'INNER';
    }

    if (!empty($this->definition['extra'])) {
      $def['extra'] = $this->definition['extra'];
    }

    if (!empty($def['join_handler']) && class_exists($def['join_handler'])) {
      $join = new $def['join_handler'];
    }
    else {
      $join = new views_join();
    }

    $join->definition = $def;
    $join->options = $this->options;
    $join->construct();
    $join->adjusted = TRUE;

    // use a short alias for this:
    $alias = $def['table'] . '_' . $this->table;

    $this->alias = $this->query->add_relationship($alias, $join, $this->definition['base'], $this->relationship);

    // Add access tags if the base table provide it.
    if (empty($this->query->options['disable_sql_rewrite']) && isset($table_data['table']['base']['access query tag'])) {
      $access_tag = $table_data['table']['base']['access query tag'];
      $this->query->add_tag($access_tag);
    }
  }

  /**
   * You can't groupby a relationship.
   */
  function use_group_by() {
    return FALSE;
  }
}

/**
 * @}
 */
