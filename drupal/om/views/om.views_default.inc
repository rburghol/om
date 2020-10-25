<?php

/**
 * Implements hook_views_default_views().
 */
function om_views_default_views() {
   $views = array();
   $view = om_views_model_hiernav();
   $views[$view->name] = $view;
   return $views;
}


// Implements hook_view_data_alter().

function om_views_data_alter(&$data) {

  // do date setups
  $defaults_date = array(
    'field' => array(
    'handler' => 'views_handler_field_date',
    'click sortable' => TRUE,
    ),
    'sort' => array(
    'handler' => 'views_handler_sort_date',
    ),
    'filter' => array(
    'handler' => 'views_handler_filter_date',
    ),
  );
   $defaults_numeric = array(
      'title' => t('Numeric Field'),
      'help' => t('Numeric Field.'), // The help that appears on the UI,
      'argument' => array(
         'handler' => 'views_handler_argument_numeric',
      ),
      'field' => array(
         'handler' => 'views_handler_field_numeric',
         'click sortable' => TRUE,
			'float' => TRUE, // enables advanced formatting options
      ),
      'filter' => array(
         'handler' => 'views_handler_filter_numeric',
      ),
      'sort' => array(
         'handler' => 'views_handler_sort',
      )
   );
   
   $numcols = array();
   $strcols = array();
   $datecols = array();
   foreach ($strcols as $thiscol) {
      $data['om'][$thiscol] = $defaults_string;
      $data['om'][$thiscol]['title'] = t("ObectOrientedMetaModel Data $thiscol");
      $data['om'][$thiscol]['help'] = t("ObectOrientedMetaModel $thiscol");
   }
   foreach ($numcols as $thiscol) {
      $data['om'][$thiscol] = $defaults_numeric;
      $data['om'][$thiscol]['title'] = t("ObectOrientedMetaModel $thiscol");
      $data['om'][$thiscol]['help'] = t("ObectOrientedMetaModel $thiscol");
   }
   foreach ($datecols as $thiscol) {
      $data['om'][$thiscol]['field'] = $defaults_date['field'];
      $data['om'][$thiscol]['sort'] = $defaults_date['sort'];
      $data['om'][$thiscol]['filter'] = $defaults_date['filter'];
   }

}


function om_views_model_hiernav() {
  $view = new view();
  $view->name = 'ows_model_to_feature_pages';
  $view->description = '';
  $view->tag = 'default';
  $view->base_table = 'dh_properties';
  $view->human_name = 'OWS - Model to Feature Pages';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['title'] = 'OWS - Model to Feature Pages';
  $handler->display->display_options['use_more_always'] = FALSE;
  $handler->display->display_options['group_by'] = TRUE;
  $handler->display->display_options['access']['type'] = 'none';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['pager']['options']['items_per_page'] = '10';
  $handler->display->display_options['style_plugin'] = 'default';
  $handler->display->display_options['row_plugin'] = 'fields';
  /* Relationship: to dH Feature */
  $handler->display->display_options['relationships']['crosstab_dh_feature']['id'] = 'crosstab_dh_feature';
  $handler->display->display_options['relationships']['crosstab_dh_feature']['table'] = 'dh_properties';
  $handler->display->display_options['relationships']['crosstab_dh_feature']['field'] = 'crosstab_dh_feature';
  $handler->display->display_options['relationships']['crosstab_dh_feature']['ui_name'] = 'to dH Feature';
  $handler->display->display_options['relationships']['crosstab_dh_feature']['label'] = 'to dH Feature';
  /* Field: DH Feature: Bundle */
  $handler->display->display_options['fields']['bundle']['id'] = 'bundle';
  $handler->display->display_options['fields']['bundle']['table'] = 'dh_feature';
  $handler->display->display_options['fields']['bundle']['field'] = 'bundle';
  $handler->display->display_options['fields']['bundle']['relationship'] = 'crosstab_dh_feature';
  $handler->display->display_options['fields']['bundle']['exclude'] = TRUE;
  $handler->display->display_options['fields']['bundle']['machine_name'] = TRUE;
  /* Field: DH Properties: featureid */
  $handler->display->display_options['fields']['featureid']['id'] = 'featureid';
  $handler->display->display_options['fields']['featureid']['table'] = 'dh_properties';
  $handler->display->display_options['fields']['featureid']['field'] = 'featureid';
  $handler->display->display_options['fields']['featureid']['exclude'] = TRUE;
  $handler->display->display_options['fields']['featureid']['separator'] = '';
  /* Field: DH Feature: Bundle */
  $handler->display->display_options['fields']['bundle_1']['id'] = 'bundle_1';
  $handler->display->display_options['fields']['bundle_1']['table'] = 'dh_feature';
  $handler->display->display_options['fields']['bundle_1']['field'] = 'bundle';
  $handler->display->display_options['fields']['bundle_1']['relationship'] = 'crosstab_dh_feature';
  $handler->display->display_options['fields']['bundle_1']['label'] = '';
  $handler->display->display_options['fields']['bundle_1']['exclude'] = TRUE;
  $handler->display->display_options['fields']['bundle_1']['element_label_colon'] = FALSE;
  /* Field: Global: Custom text */
  $handler->display->display_options['fields']['nothing']['id'] = 'nothing';
  $handler->display->display_options['fields']['nothing']['table'] = 'views';
  $handler->display->display_options['fields']['nothing']['field'] = 'nothing';
  $handler->display->display_options['fields']['nothing']['label'] = '';
  $handler->display->display_options['fields']['nothing']['alter']['text'] = 'OWS [bundle_1] page';
  $handler->display->display_options['fields']['nothing']['alter']['make_link'] = TRUE;
  $handler->display->display_options['fields']['nothing']['alter']['path'] = 'ows-[bundle]-dash-info/[featureid]';
  $handler->display->display_options['fields']['nothing']['element_label_colon'] = FALSE;
  /* Contextual filter: DH Properties: featureid */
  $handler->display->display_options['arguments']['featureid']['id'] = 'featureid';
  $handler->display->display_options['arguments']['featureid']['table'] = 'dh_properties';
  $handler->display->display_options['arguments']['featureid']['field'] = 'featureid';
  $handler->display->display_options['arguments']['featureid']['default_argument_type'] = 'fixed';
  $handler->display->display_options['arguments']['featureid']['summary']['number_of_records'] = '0';
  $handler->display->display_options['arguments']['featureid']['summary']['format'] = 'default_summary';
  $handler->display->display_options['arguments']['featureid']['summary_options']['items_per_page'] = '25';
  $handler->display->display_options['arguments']['featureid']['comparator'] = 'eq';
  $handler->display->display_options['arguments']['featureid']['op_location'] = 'where';
  /* Contextual filter: DH Properties: entity_type */
  $handler->display->display_options['arguments']['entity_type']['id'] = 'entity_type';
  $handler->display->display_options['arguments']['entity_type']['table'] = 'dh_properties';
  $handler->display->display_options['arguments']['entity_type']['field'] = 'entity_type';
  $handler->display->display_options['arguments']['entity_type']['default_argument_type'] = 'fixed';
  $handler->display->display_options['arguments']['entity_type']['summary']['number_of_records'] = '0';
  $handler->display->display_options['arguments']['entity_type']['summary']['format'] = 'default_summary';
  $handler->display->display_options['arguments']['entity_type']['summary_options']['items_per_page'] = '25';
  $handler->display->display_options['arguments']['entity_type']['limit'] = '0';
  /* Filter criterion: DH Properties: entity_type */
  $handler->display->display_options['filters']['entity_type']['id'] = 'entity_type';
  $handler->display->display_options['filters']['entity_type']['table'] = 'dh_properties';
  $handler->display->display_options['filters']['entity_type']['field'] = 'entity_type';
  $handler->display->display_options['filters']['entity_type']['value'] = 'dh_feature';
  $handler->display->display_options['filters']['entity_type']['op_location'] = 'where';

  /* Display: Page */
  $handler = $view->new_display('page', 'Page', 'page');
  $handler->display->display_options['path'] = 'ows-model-to-feature-pages';

  return $view;
}