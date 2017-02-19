<?php

include_once( 'tohtml.php' );
include_once( 'methods.php' );
include_once( 'calendar/calendar.php' );


session_save_path("/tmp/");

$conf = array();
$inifile = "/etc/hipporc";

if(file_exists($inifile)) 
    $conf = parse_ini_file($inifile, $process_section = TRUE );
else
{
    echo printWarning( "Config file is not found. Can't continue" );
    exit;
}

if(!$conf)
{
    $error = "Failed to read  configuartion file.";
    header($error." I can't do anything anymore. Please wake up the admin.");
    exit;
}

$_SESSION['conf'] = $conf;
// If user is already authenticated, redirect him to user.php
// NOTE: DO NOT put this block before loading configuration files.
if( array_key_exists( 'AUTHENTICATED', $_SESSION) && $_SESSION[ 'AUTHENTICATED' ] )
{
    if( $_SESSION[ 'user' ] != 'anonymous' )
    {
        echo printInfo( "Already logged-in" );
        goToPage( 'user.php', 0 );
        exit;
    }
}

$_SESSION['user'] = 'anonymous'; // This for testing purpose.
$_SESSION[ 'oauth_credential' ] =
    '/etc/hippo/client_secret_636127149215-mn7vk37265hlq48d39qt45asnsvdbti0.apps.googleusercontent.com.json';
$_SESSION[ 'calendar_id'] = 
    'd2jud2r7bsj0i820k0f6j702qo@group.calendar.google.com'; 

$_SESSION[ 'timezone' ] = 'Asia/Kolkata';

ini_set( 'date.timezone', 'Asia/Kolkata' );
ini_set( 'log_errors', 1 );
ini_set( 'error_log', '/tmp/__hippo__.log' );

/* counter */

//$hit_count = (int)file_get_contents('count.txt');
//$hit_count++;
//file_put_contents('count.txt', $hit_count);

$summary = summaryTable( );

// Now create a login form.
echo "<table class=\"index\">";
echo ' <tr> <td style="min-width:75%"> </p> ' .  $summary .  '</td> <td> ';

echo loginForm();
echo '</tr>';
echo "</table>";
echo "</td></tr>";
echo '<div class="public_calendar">';
echo calendarURL( );
echo '</div>';

echo "<br><br>";

include_once 'footer.php';
?>
