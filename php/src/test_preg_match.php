<?php

$content = '[workout zone=4]10 x 100 on 1:30[/workout]';
$content .= "\nSome blither-blather\n";
$content .= '[workout]20 x 50 on :45';
$content .= "\n# do 4 of each stroke";
$content .= '[/workout]';

$regex = '/\[workout[^\]]*\](.*?)\[\/workout\]/si';
preg_match_all( $regex, $content, $matches );

print_r($matches);

#test replace
$output = str_replace($matches[0], $matches[1], $content);

print("\n Output: \n$output \n");
?>