<?php
# logout script
include_once('./config.php');
session_destroy();
$loggedin = 0;
include_once('./login.php');

?>