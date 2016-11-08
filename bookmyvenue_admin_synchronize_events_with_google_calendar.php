<?php

include_once 'header.php';
include_once 'database.php';


// Before calling this script, the user must set 
// $_SESSION['google_calendar_command'] variable. Accordingly 
// ./oauthcallback.php file will handle the request. Most of the commands 
// implementation must be simple and should only require setting up few 
// SESSION variables.

$_SESSION[ 'google_command'] = 'synchronize_all_events';
// include_once 'calendar/authenticate_gcalendar.php';
header( 'Location:calendar/authenticate_gcalendar.php' );
exit;


?>
