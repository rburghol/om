<?php

$str = "1,2,3";
$s = array();
list($s[1], $s[2], $s[3], $s[4], $s[5]) = split(',', $str);

print_r($s);
print("\n");
if ($s[5] === NULL) {
   print("s[5] evaluates null \n");
} else {
   print("No null values \n");
}
?>
