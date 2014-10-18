<?php
require_once('../../../wp-load.php');
global $wpdb;
$wpdb->query( "UPDATE ". $wpdb->prefix ."adrotator SET total_clicks = total_clicks + 1  WHERE id = '".$_POST['id']."'" );
?>		