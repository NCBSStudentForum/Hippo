<?php

include_once( 'header.php' );
include_once( 'methods.php' );
include_once( 'database.php' );
include_once( 'tohtml.php' );
include_once( 'calendar/calendar.php' );

session_save_path("/tmp/");
$conf = array();
$inifile = "hipporc";

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

$_SESSION['user'] = 'anonymous'; // This for testing purpose.
$_SESSION[ 'oauth_credential' ] = __DIR__ . '/oauth-credentials.json';
$_SESSION[ 'calendar_id'] = '6bvpnrto763c0d53shp4sr5rmk@group.calendar.google.com';
$_SESSION[ 'timezone' ] = 'Asia/Kolkata';

ini_set( 'date.timezone', 'Asia/Kolkata' );
ini_set( 'log_errors', 1 );
ini_set( 'error_log', '/tmp/__minion__.log' );

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

?>
