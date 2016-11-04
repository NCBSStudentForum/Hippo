<?php

include_once( 'header.php' );
include_once( 'methods.php' );
include_once( 'tohtml.php' );

function embdedCalendar( )
{
    $html = '
        <iframe src="https://calendar.google.com/calendar/embed?src=6bvpnrto763c0d53shp4sr5rmk%40group.calendar.google.com&ctz=Asia/Calcutta" style="border: 0" width="800" height="600" frameborder="0" scrolling="no"></iframe>';
    return $html;
}

session_save_path("/tmp/");
$conf = array();
$inifile = "minionrc";

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

/* counter */
$hit_count = (int)file_get_contents('count.txt');
$hit_count++;
file_put_contents('count.txt', $hit_count);

// Now create a login form.
echo "<table class=\"index\">";
//echo "<tr><td>";
echo loginForm();
//echo "</td></tr><tr><td>";
//echo "</td></tr>";
echo "</table>";

echo embdedCalendar( );
?>
