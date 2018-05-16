<?php

/**
 * Implements hook_views_default_views().
 */
function om_views_default_views() {
   $views = array();
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
