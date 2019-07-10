<?php

error_log("Element $elementid init() Returned from calling routine.");
$debugstring = '';

error_log("Column Defs:" . print_r($thisobject->column_defs,1));

$thisobject->initTimer();
$thisobject->step();
$phub_class = get_class($thisobject->processors['broadcast_hydro']);
error_log("Phub class: $phub_class");
$phubhub_class = get_class($phub->parentHub);
error_log("Phubhub class: $phubhub_class");
$phub = $thisobject->processors['broadcast_hydro'];
error_log("PhubPhub ardata = " . print_r($phub->parentHub->arData,1));
error_log("Phub ardata = " . print_r($phub->arData,1));


?>