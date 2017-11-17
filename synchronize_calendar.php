<?php

// This script is to be called by cron.
$_SESSION[ 'user' ] = 'hippo';
$_SESSION[ 'google_command'] = 'synchronize_all_events';
$res = shell_exec( 'php -e oauthcallback.php' );
echo $res;
exit;

?>

