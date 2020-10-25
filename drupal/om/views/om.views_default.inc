<?php

/**
 * Implements hook_views_default_views().
 */
function om_views_default_views() {
   $views = array();
   $view = om_views_model_show();
   $views[$view->name] = $view;
   $view = om_model_nav();
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


function om_views_model_show() {
  $view = new view();
  $view->name = 'om_model_view';
  $view->description = '';
  $view->tag = 'default';
  $view->base_table = 'dh_properties';
  $view->human_name = 'OM - Model View';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['title'] = 'OM - Model View';
  $handler->display->display_options['use_more_always'] = FALSE;
  $handler->display->display_options['access']['type'] = 'none';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['pager']['options']['items_per_page'] = '10';
  $handler->display->display_options['style_plugin'] = 'default';
  $handler->display->display_options['row_plugin'] = 'fields';
  /* Field: DH Properties: entity_type */
  $handler->display->display_options['fields']['entity_type']['id'] = 'entity_type';
  $handler->display->display_options['fields']['entity_type']['table'] = 'dh_properties';
  $handler->display->display_options['fields']['entity_type']['field'] = 'entity_type';
  $handler->display->display_options['fields']['entity_type']['exclude'] = TRUE;
  /* Field: DH Properties: Modified */
  $handler->display->display_options['fields']['modified']['id'] = 'modified';
  $handler->display->display_options['fields']['modified']['table'] = 'dh_properties';
  $handler->display->display_options['fields']['modified']['field'] = 'modified';
  $handler->display->display_options['fields']['modified']['exclude'] = TRUE;
  $handler->display->display_options['fields']['modified']['separator'] = '';
  /* Field: DH Properties: featureid */
  $handler->display->display_options['fields']['featureid']['id'] = 'featureid';
  $handler->display->display_options['fields']['featureid']['table'] = 'dh_properties';
  $handler->display->display_options['fields']['featureid']['field'] = 'featureid';
  $handler->display->display_options['fields']['featureid']['exclude'] = TRUE;
  $handler->display->display_options['fields']['featureid']['separator'] = '';
  /* Field: DH Properties: Label */
  $handler->display->display_options['fields']['pid']['id'] = 'pid';
  $handler->display->display_options['fields']['pid']['table'] = 'dh_properties';
  $handler->display->display_options['fields']['pid']['field'] = 'pid';
  $handler->display->display_options['fields']['pid']['exclude'] = TRUE;
  $handler->display->display_options['fields']['pid']['alter']['alter_text'] = TRUE;
  $handler->display->display_options['fields']['pid']['alter']['text'] = '&nbsp;';
  $handler->display->display_options['fields']['pid']['alter']['make_link'] = TRUE;
  $handler->display->display_options['fields']['pid']['alter']['path'] = '/admin/content/dh_properties/manage/[pid]';
  $handler->display->display_options['fields']['pid']['alter']['path_query_parameters'] = 'destination=om-model-info/[pid]';
  $handler->display->display_options['fields']['pid']['alter']['link_class'] = 'editlink';
  $handler->display->display_options['fields']['pid']['separator'] = '';
  /* Field: DH Properties: Label */
  $handler->display->display_options['fields']['pid_1']['id'] = 'pid_1';
  $handler->display->display_options['fields']['pid_1']['table'] = 'dh_properties';
  $handler->display->display_options['fields']['pid_1']['field'] = 'pid';
  $handler->display->display_options['fields']['pid_1']['label'] = '';
  $handler->display->display_options['fields']['pid_1']['exclude'] = TRUE;
  $handler->display->display_options['fields']['pid_1']['alter']['alter_text'] = TRUE;
  $handler->display->display_options['fields']['pid_1']['alter']['text'] = '&nbsp;';
  $handler->display->display_options['fields']['pid_1']['alter']['make_link'] = TRUE;
  $handler->display->display_options['fields']['pid_1']['alter']['path'] = '/admin/content/dh_properties/manage/[pid_1]/delete';
  $handler->display->display_options['fields']['pid_1']['alter']['path_query_parameters'] = 'destination=om-model-list/[featureid]/[entity_type]';
  $handler->display->display_options['fields']['pid_1']['alter']['link_class'] = 'subtractlink';
  $handler->display->display_options['fields']['pid_1']['element_label_colon'] = FALSE;
  $handler->display->display_options['fields']['pid_1']['separator'] = '';
  /* Field: DH Properties Revisions: propname */
  $handler->display->display_options['fields']['propname']['id'] = 'propname';
  $handler->display->display_options['fields']['propname']['table'] = 'dh_properties_revision';
  $handler->display->display_options['fields']['propname']['field'] = 'propname';
  $handler->display->display_options['fields']['propname']['label'] = '';
  $handler->display->display_options['fields']['propname']['exclude'] = TRUE;
  $handler->display->display_options['fields']['propname']['element_label_colon'] = FALSE;
  /* Field: Global: Custom text */
  $handler->display->display_options['fields']['nothing']['id'] = 'nothing';
  $handler->display->display_options['fields']['nothing']['table'] = 'views';
  $handler->display->display_options['fields']['nothing']['field'] = 'nothing';
  $handler->display->display_options['fields']['nothing']['label'] = 'Manage';
  $handler->display->display_options['fields']['nothing']['alter']['text'] = '[propname] [pid] [pid_1]';
  /* Field: DH Properties: Rendered dH Properties */
  $handler->display->display_options['fields']['rendered_entity']['id'] = 'rendered_entity';
  $handler->display->display_options['fields']['rendered_entity']['table'] = 'views_entity_dh_properties';
  $handler->display->display_options['fields']['rendered_entity']['field'] = 'rendered_entity';
  $handler->display->display_options['fields']['rendered_entity']['label'] = '';
  $handler->display->display_options['fields']['rendered_entity']['element_label_colon'] = FALSE;
  $handler->display->display_options['fields']['rendered_entity']['link_to_entity'] = 1;
  $handler->display->display_options['fields']['rendered_entity']['display'] = 'view';
  $handler->display->display_options['fields']['rendered_entity']['view_mode'] = 'plugin';
  $handler->display->display_options['fields']['rendered_entity']['bypass_access'] = 0;
  /* Contextual filter: DH Properties: Label */
  $handler->display->display_options['arguments']['pid']['id'] = 'pid';
  $handler->display->display_options['arguments']['pid']['table'] = 'dh_properties';
  $handler->display->display_options['arguments']['pid']['field'] = 'pid';
  $handler->display->display_options['arguments']['pid']['default_action'] = 'empty';
  $handler->display->display_options['arguments']['pid']['default_argument_type'] = 'fixed';
  $handler->display->display_options['arguments']['pid']['summary']['number_of_records'] = '0';
  $handler->display->display_options['arguments']['pid']['summary']['format'] = 'default_summary';
  $handler->display->display_options['arguments']['pid']['summary_options']['items_per_page'] = '25';
  $handler->display->display_options['arguments']['pid']['comparator'] = 'eq';
  $handler->display->display_options['arguments']['pid']['op_location'] = 'where';

  /* Display: Page */
  $handler = $view->new_display('page', 'Page', 'page');
  $handler->display->display_options['path'] = 'om-model-view';

  return $view;
}

function om_model_nav() {
  $view = new view();
  $view->name = 'om_model_navigation_generic';
  $view->description = '';
  $view->tag = 'default';
  $view->base_table = 'dh_properties';
  $view->human_name = 'OM Model Navigation - Generic';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['title'] = 'Attached Models';
  $handler->display->display_options['use_ajax'] = TRUE;
  $handler->display->display_options['use_more_always'] = FALSE;
  $handler->display->display_options['access']['type'] = 'none';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['pager']['options']['items_per_page'] = '20';
  $handler->display->display_options['pager']['options']['offset'] = '0';
  $handler->display->display_options['pager']['options']['id'] = '0';
  $handler->display->display_options['pager']['options']['quantity'] = '9';
  $handler->display->display_options['style_plugin'] = 'table';
  /* Footer: Global: View (Views field view) */
  $handler->display->display_options['footer']['view_field']['id'] = 'view_field';
  $handler->display->display_options['footer']['view_field']['table'] = 'views';
  $handler->display->display_options['footer']['view_field']['field'] = 'view_field';
  $handler->display->display_options['footer']['view_field']['label'] = 'Add Model';
  $handler->display->display_options['footer']['view_field']['view'] = 'add_dh_properties_jump_list';
  $handler->display->display_options['footer']['view_field']['display'] = 'page_1';
  $handler->display->display_options['footer']['view_field']['arguments'] = '!1/om_object_classes';
  /* No results behavior: Global: View (Views field view) */
  $handler->display->display_options['empty']['view_field']['id'] = 'view_field';
  $handler->display->display_options['empty']['view_field']['table'] = 'views';
  $handler->display->display_options['empty']['view_field']['field'] = 'view_field';
  $handler->display->display_options['empty']['view_field']['label'] = 'Add Model';
  $handler->display->display_options['empty']['view_field']['view'] = 'add_dh_properties_jump_list';
  $handler->display->display_options['empty']['view_field']['display'] = 'page_5';
  $handler->display->display_options['empty']['view_field']['arguments'] = '!1/om_object_classes/all/!2';
  /* Field: DH Properties: Label */
  $handler->display->display_options['fields']['pid']['id'] = 'pid';
  $handler->display->display_options['fields']['pid']['table'] = 'dh_properties';
  $handler->display->display_options['fields']['pid']['field'] = 'pid';
  $handler->display->display_options['fields']['pid']['exclude'] = TRUE;
  $handler->display->display_options['fields']['pid']['separator'] = '';
  /* Field: DH Properties: Property Name */
  $handler->display->display_options['fields']['propname']['id'] = 'propname';
  $handler->display->display_options['fields']['propname']['table'] = 'dh_properties';
  $handler->display->display_options['fields']['propname']['field'] = 'propname';
  $handler->display->display_options['fields']['propname']['label'] = 'Model Name';
  $handler->display->display_options['fields']['propname']['alter']['make_link'] = TRUE;
  $handler->display->display_options['fields']['propname']['alter']['path'] = 'ows-watershed-dash-modeling-properties/[pid]';
  /* Field: DH Properties: propcode */
  $handler->display->display_options['fields']['propcode']['id'] = 'propcode';
  $handler->display->display_options['fields']['propcode']['table'] = 'dh_properties';
  $handler->display->display_options['fields']['propcode']['field'] = 'propcode';
  $handler->display->display_options['fields']['propcode']['label'] = 'Model Code';
  /* Field: DH Properties: featureid */
  $handler->display->display_options['fields']['featureid']['id'] = 'featureid';
  $handler->display->display_options['fields']['featureid']['table'] = 'dh_properties';
  $handler->display->display_options['fields']['featureid']['field'] = 'featureid';
  $handler->display->display_options['fields']['featureid']['separator'] = '';
  /* Contextual filter: DH Properties: featureid */
  $handler->display->display_options['arguments']['featureid']['id'] = 'featureid';
  $handler->display->display_options['arguments']['featureid']['table'] = 'dh_properties';
  $handler->display->display_options['arguments']['featureid']['field'] = 'featureid';
  $handler->display->display_options['arguments']['featureid']['default_action'] = 'empty';
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
  $handler->display->display_options['arguments']['entity_type']['default_action'] = 'default';
  $handler->display->display_options['arguments']['entity_type']['default_argument_type'] = 'fixed';
  $handler->display->display_options['arguments']['entity_type']['default_argument_options']['argument'] = 'dh_feature';
  $handler->display->display_options['arguments']['entity_type']['summary']['number_of_records'] = '0';
  $handler->display->display_options['arguments']['entity_type']['summary']['format'] = 'default_summary';
  $handler->display->display_options['arguments']['entity_type']['summary_options']['items_per_page'] = '25';
  $handler->display->display_options['arguments']['entity_type']['limit'] = '0';

  /* Display: List of Models */
  $handler = $view->new_display('page', 'List of Models', 'page');
  $handler->display->display_options['defaults']['footer'] = FALSE;
  /* Footer: Global: View (Views field view) */
  $handler->display->display_options['footer']['view_field']['id'] = 'view_field';
  $handler->display->display_options['footer']['view_field']['table'] = 'views';
  $handler->display->display_options['footer']['view_field']['field'] = 'view_field';
  $handler->display->display_options['footer']['view_field']['label'] = 'Add Model';
  $handler->display->display_options['footer']['view_field']['view'] = 'add_dh_properties_jump_list';
  $handler->display->display_options['footer']['view_field']['display'] = 'page_5';
  $handler->display->display_options['footer']['view_field']['arguments'] = '!1/om_object_classes/all/[!entity_type]';
  $handler->display->display_options['defaults']['fields'] = FALSE;
  /* Field: DH Properties: featureid */
  $handler->display->display_options['fields']['featureid']['id'] = 'featureid';
  $handler->display->display_options['fields']['featureid']['table'] = 'dh_properties';
  $handler->display->display_options['fields']['featureid']['field'] = 'featureid';
  $handler->display->display_options['fields']['featureid']['separator'] = '';
  /* Field: DH Properties: entity_type */
  $handler->display->display_options['fields']['entity_type']['id'] = 'entity_type';
  $handler->display->display_options['fields']['entity_type']['table'] = 'dh_properties';
  $handler->display->display_options['fields']['entity_type']['field'] = 'entity_type';
  $handler->display->display_options['fields']['entity_type']['label'] = '';
  $handler->display->display_options['fields']['entity_type']['exclude'] = TRUE;
  $handler->display->display_options['fields']['entity_type']['element_label_colon'] = FALSE;
  /* Field: DH Properties: Label */
  $handler->display->display_options['fields']['pid']['id'] = 'pid';
  $handler->display->display_options['fields']['pid']['table'] = 'dh_properties';
  $handler->display->display_options['fields']['pid']['field'] = 'pid';
  $handler->display->display_options['fields']['pid']['relationship'] = 'crosstab_dh_feature';
  $handler->display->display_options['fields']['pid']['exclude'] = TRUE;
  $handler->display->display_options['fields']['pid']['separator'] = '';
  /* Field: DH Properties: Property Name */
  $handler->display->display_options['fields']['propname']['id'] = 'propname';
  $handler->display->display_options['fields']['propname']['table'] = 'dh_properties';
  $handler->display->display_options['fields']['propname']['field'] = 'propname';
  $handler->display->display_options['fields']['propname']['label'] = 'Models';
  $handler->display->display_options['fields']['propname']['alter']['make_link'] = TRUE;
  $handler->display->display_options['fields']['propname']['alter']['path'] = 'om-model-info/[pid]';
  /* Field: DH Properties: propcode */
  $handler->display->display_options['fields']['propcode']['id'] = 'propcode';
  $handler->display->display_options['fields']['propcode']['table'] = 'dh_properties';
  $handler->display->display_options['fields']['propcode']['field'] = 'propcode';
  $handler->display->display_options['fields']['propcode']['relationship'] = 'crosstab_dh_feature';
  $handler->display->display_options['fields']['propcode']['label'] = 'Code';
  /* Field: DH Properties: Label */
  $handler->display->display_options['fields']['pid_1']['id'] = 'pid_1';
  $handler->display->display_options['fields']['pid_1']['table'] = 'dh_properties';
  $handler->display->display_options['fields']['pid_1']['field'] = 'pid';
  $handler->display->display_options['fields']['pid_1']['label'] = '';
  $handler->display->display_options['fields']['pid_1']['alter']['alter_text'] = TRUE;
  $handler->display->display_options['fields']['pid_1']['alter']['text'] = '&nbsp;';
  $handler->display->display_options['fields']['pid_1']['alter']['make_link'] = TRUE;
  $handler->display->display_options['fields']['pid_1']['alter']['path'] = 'admin/content/dh_properties/manage/[pid_1]';
  $handler->display->display_options['fields']['pid_1']['alter']['path_query_parameters'] = 'finaldest=om-model-info/[featureid]/[entity_type]';
  $handler->display->display_options['fields']['pid_1']['alter']['link_class'] = 'editlink';
  $handler->display->display_options['fields']['pid_1']['element_label_colon'] = FALSE;
  $handler->display->display_options['fields']['pid_1']['separator'] = '';
  /* Field: DH VariableDefinition: Variable Key (may be short name or = varname) */
  $handler->display->display_options['fields']['varkey']['id'] = 'varkey';
  $handler->display->display_options['fields']['varkey']['table'] = 'dh_variabledefinition';
  $handler->display->display_options['fields']['varkey']['field'] = 'varkey';
  /* Field: Global: Custom text */
  $handler->display->display_options['fields']['nothing']['id'] = 'nothing';
  $handler->display->display_options['fields']['nothing']['table'] = 'views';
  $handler->display->display_options['fields']['nothing']['field'] = 'nothing';
  $handler->display->display_options['fields']['nothing']['label'] = '';
  $handler->display->display_options['fields']['nothing']['alter']['text'] = '&nbsp;';
  $handler->display->display_options['fields']['nothing']['alter']['make_link'] = TRUE;
  $handler->display->display_options['fields']['nothing']['alter']['path'] = 'admin/content/dh_properties/manage/[pid]/delete';
  $handler->display->display_options['fields']['nothing']['alter']['path_query_parameters'] = 'destination=om-model-info/[featureid]/[entity_type]';
  $handler->display->display_options['fields']['nothing']['alter']['link_class'] = 'subtractlink';
  $handler->display->display_options['fields']['nothing']['element_label_colon'] = FALSE;
  $handler->display->display_options['defaults']['sorts'] = FALSE;
  /* Sort criterion: DH Properties: Property Name */
  $handler->display->display_options['sorts']['propname']['id'] = 'propname';
  $handler->display->display_options['sorts']['propname']['table'] = 'dh_properties';
  $handler->display->display_options['sorts']['propname']['field'] = 'propname';
  $handler->display->display_options['sorts']['propname']['relationship'] = 'crosstab_dh_feature';
  $handler->display->display_options['defaults']['filter_groups'] = FALSE;
  $handler->display->display_options['defaults']['filters'] = FALSE;
  /* Filter criterion: DH VariableDefinition: vocabulary */
  $handler->display->display_options['filters']['vocabulary']['id'] = 'vocabulary';
  $handler->display->display_options['filters']['vocabulary']['table'] = 'dh_variabledefinition';
  $handler->display->display_options['filters']['vocabulary']['field'] = 'vocabulary';
  $handler->display->display_options['filters']['vocabulary']['value'] = array(
    'om' => 'om',
    'om_object_classes' => 'om_object_classes',
  );
  $handler->display->display_options['path'] = 'om-model-navigation';

  /* Display: To Parent Model List */
  $handler = $view->new_display('page', 'To Parent Model List', 'page_1');
  $handler->display->display_options['defaults']['style_plugin'] = FALSE;
  $handler->display->display_options['style_plugin'] = 'default';
  $handler->display->display_options['defaults']['style_options'] = FALSE;
  $handler->display->display_options['defaults']['row_plugin'] = FALSE;
  $handler->display->display_options['row_plugin'] = 'fields';
  $handler->display->display_options['defaults']['row_options'] = FALSE;
  $handler->display->display_options['defaults']['footer'] = FALSE;
  $handler->display->display_options['defaults']['empty'] = FALSE;
  $handler->display->display_options['defaults']['fields'] = FALSE;
  /* Field: DH Properties: entity_type */
  $handler->display->display_options['fields']['entity_type']['id'] = 'entity_type';
  $handler->display->display_options['fields']['entity_type']['table'] = 'dh_properties';
  $handler->display->display_options['fields']['entity_type']['field'] = 'entity_type';
  $handler->display->display_options['fields']['entity_type']['exclude'] = TRUE;
  /* Field: DH Properties: featureid */
  $handler->display->display_options['fields']['featureid']['id'] = 'featureid';
  $handler->display->display_options['fields']['featureid']['table'] = 'dh_properties';
  $handler->display->display_options['fields']['featureid']['field'] = 'featureid';
  $handler->display->display_options['fields']['featureid']['label'] = '';
  $handler->display->display_options['fields']['featureid']['alter']['alter_text'] = TRUE;
  $handler->display->display_options['fields']['featureid']['alter']['text'] = 'Up to Parent Model';
  $handler->display->display_options['fields']['featureid']['alter']['make_link'] = TRUE;
  $handler->display->display_options['fields']['featureid']['alter']['path'] = 'om-model-info/[featureid]/[entity_type]';
  $handler->display->display_options['fields']['featureid']['element_label_colon'] = FALSE;
  $handler->display->display_options['fields']['featureid']['separator'] = '';
  $handler->display->display_options['defaults']['arguments'] = FALSE;
  /* Contextual filter: DH Properties: Label */
  $handler->display->display_options['arguments']['pid']['id'] = 'pid';
  $handler->display->display_options['arguments']['pid']['table'] = 'dh_properties';
  $handler->display->display_options['arguments']['pid']['field'] = 'pid';
  $handler->display->display_options['arguments']['pid']['relationship'] = 'crosstab_dh_feature';
  $handler->display->display_options['arguments']['pid']['default_argument_type'] = 'fixed';
  $handler->display->display_options['arguments']['pid']['summary']['number_of_records'] = '0';
  $handler->display->display_options['arguments']['pid']['summary']['format'] = 'default_summary';
  $handler->display->display_options['arguments']['pid']['summary_options']['items_per_page'] = '25';
  $handler->display->display_options['arguments']['pid']['comparator'] = 'eq';
  $handler->display->display_options['arguments']['pid']['op_location'] = 'where';
  $handler->display->display_options['path'] = 'om-model-nav-to-parent';

  return $view;
}
