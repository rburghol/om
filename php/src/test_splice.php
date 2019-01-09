<?php
$array1 = array(175 => "green", 12 => "brown", 17 => "blue", 21 => "red");
$array2 = array(12 => "brown");
$result = array_diff_assoc($array1, array(21 => $array1[21]));
print_r($result);


$array1 = array("green", "brown", "blue", "red");
$array2 = array("brown");
$result = array_diff($array1, $array2);
print_r($result);
?>
