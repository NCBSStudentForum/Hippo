<?php

include_once 'methods.php';
include_once 'database.php';

ini_set( 'date.timezone', 'Asia/Kolkata' );
ini_set( 'log_errors', 1 );
ini_set( 'error_log', '/var/log/hippo.log' );

$now = dbDateTime( strtotime( "now" ) );
error_log( "Running cron at $now" );
echo( "Running cron at $now" );


?>
