<?php

include_once( 'header.php' );
include_once( 'methods.php' );
include_once( 'tohtml.php' );
include_once( 'calendar/calendar.php' );

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

ini_set( 'date.timezone', 'Asia/Kolkata' );

/* counter */
$hit_count = (int)file_get_contents('count.txt');
$hit_count++;
file_put_contents('count.txt', $hit_count);

// Now create a login form.
echo "<table class=\"index\">";
echo '
<tr>
   <td style="min-width:75%">

      <p class=\"info\">
      This is ALPHA test run to get feedback from users.
      <br><br>

      To report issues/bugs or to submit feature request or to comment, please
      use <a href="https://goo.gl/forms/1yMRtfslqazaMoBY2" target="_black">this
         form</a>

      </p>
   </td>
   <td>
   ';
echo loginForm();
echo '</tr>';
echo "</table>";
echo "</td></tr>";
echo '<div class="public_calendar">';
echo calendarURL( );
echo '</div>';

//echo embdedCalendar( );
?>
