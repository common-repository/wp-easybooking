<?php
define("absPath",$_SERVER['DOCUMENT_ROOT']."/");

include_once(absPath.'wp-config.php');		 
include_once(absPath.'wp-load.php');
include_once(absPath.'wp-includes/wp-db.php');
global $wpdb;

echo 'here we are';
?>