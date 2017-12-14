<!DOCTYPE html>

<?php
ob_start();

/**
 * @brief Return link to calendar.
 * @return
 */
function calendarURL( )
{
    return
        'https://calendar.google.com/calendar/embed?src=d2jud2r7bsj0i820k0f6j702qo%40group.calendar.google.com&ctz=Asia/Calcutta';
}


if(!isset($_SESSION))
    session_start();

// If this is not a index.php page. check if access is valid.
if( ! $_SERVER['PHP_SELF'] == 'index.php' )
    include_once( 'is_valid_access.php' );

ini_set( 'date.timezone', 'Asia/Kolkata' );

?>

<html>
<meta content="text/html;charset=utf-8" http-equiv="Content-Type">

<div class="header">

<title>NCBS Hippo</title>

<h1><a href="index.php">NCBS Hippo</a></h1>

<div style="font-size:small">
<table class="public_links">
    <tr>
    <td>
    <a href="allevents.php" target="hippo_popup">Bookings</a>
    </td>
    <td>
    <a href="aws.php" target="hippo_popup">AWSs</a>
    </td>
    <td>
    <a href="events.php" target="hippo_popup">Talks</a>
    </td>
    <!-- <td> <a href="user_aws_search.php" target="hippo_popup">Search AWS</a> </td> -->
    <td> <a href="statistics.php" target="hippo_popup" >Statistics </a> </td>
    <!-- <td> <a href="active_speakers.php" target="hippo_popup" >AWS speakers</a></td> -->
    <td> <a href="courses.php" target="hippo_popup" >Courses</a></td>
    <td> <a href="map.php" target="hippo_popup" >Map</a></td>
    <!--
    RSS has been thorougly abused to make it work with NCBS screen.
    <td> <a href="rss.php" target="hippo_popup" >
            <img src="data/feed-icon-14x14.2168a573d0d4.png"
                alt="Subscribe to public events">
            </a></td>
    -->
    <td> <a href="howto.php" target="hippo_popup" >HOWTO</a></td>
    </tr>
</table>
</div>

</div>
<br />
<br />
</html>

<!--  REQUIRED -->
<script src="./node_modules/jquery/dist/jquery.js"></script>

<script src="./node_modules/jquery-ui-dist/jquery-ui.min.js"></script>
<link href="./node_modules/jquery-ui-dist/jquery-ui.min.css" rel="stylesheet" type="text/css" />

<!--
<link rel="stylesheet" href="./node_modules/bootstrap/dist/css/bootstrap.min.css"/>
-->

<script src="./node_modules/jquery-timepicker/jquery.timepicker.js"></script>
<link href="./node_modules/jquery-timepicker/jquery.timepicker.css" rel="stylesheet" type="text/css" />

<script src="./node_modules/jquery-ui-multi-date-picker/dist/jquery-ui.multidatespicker.js"></script>

<link href="hippo.css" rel="stylesheet" type="text/css" />

<!-- Disable favicon requests -->
<link rel="icon" href="data:,">

<!-- Font awesome -->
<link rel="stylesheet" href="./node_modules/font-awesome/css/font-awesome.css"/>

<script>
$( function() {
    $( "input.datepicker" ).datepicker( { dateFormat: "yy-mm-dd" } );
  } );
</script>

<script>
$( function() {
    $( "input.datetimepicker" ).datepicker( { dateFormat: "yy-mm-dd" } );
  } );
</script>

<script>
$(function(){
    $('input.timepicker').timepicker( {
            minTime : '8am'
            , scrollDefault : 'now'
            , timeFormat : 'H:mm', interval : '15'
    });
});
</script>

<!-- Make sure date is in yyyy-dd-mm format e.g. 2016-11-31 etc. -->
<script>
$( function() {
    var today = new Date();
    var tomorrow = (new Date()).setDate( today.getDate( ) + 1 );
    $( "input.multidatespicker" ).multiDatesPicker( {
        dateFormat : "yy-m-d"
    });
} );

</script>


<script src="./node_modules/tinymce/tinymce.min.js"></script>

<!-- confirm on delete -->
<script type="text/javascript" charset="utf-8">
function AreYouSure( button )
{
    var x = confirm( "ALERT! Destructive operation! Continue?" );
    if( x )
        button.setAttribute( 'value', 'delete' );
    else
        button.setAttribute( 'value', 'DO_NOTHING' );
    return x;
}
</script>

<script type="text/javascript">
  (function() {
    var blinks = document.getElementsByTagName('blink');
    var visibility = 'hidden';
    window.setInterval(function() {
      for (var i = blinks.length - 1; i >= 0; i--) {
        blinks[i].style.visibility = visibility;
      }
      visibility = (visibility === 'visible') ? 'hidden' : 'visible';
    }, 1000);
  })();
</script>


<!-- load basic unicode chara -->
<?php

// Always parse the config file and save it in the session.
$inifile = '/etc/hipporc';
if(file_exists($inifile))
{
    $conf = parse_ini_file($inifile, $process_section = TRUE );
    $_SESSION[ 'conf' ] = $conf;
}
else
{
    die( "Config file is not found. Can't continue" );
    exit;
}


//$symbEdit = "&#9998";              // pencil
$symbEdit = "&#x270d";                // Writing hand
$symbCalendar = "&#128197";          // Does not work on chromium
$symbCalendar = "Schedule It";
$symbDelete = "&#10008";
$symbCancel = "&#10008";

$symbReject = "Reject";
$symbApprove = "Approve";
$symbAccept = "Accept";

$symbScan = "&#8981";
$symbThumbsDown = "&#128078";
$symbPerfect = "&#128076";
//$symbReview = "&#128065";
$symbReview = 'Review';
$symbUpload = "&#8682";
$symbWarn = "&#9888";
$symbCheck = "&#10003";
$symbSubmit = $symbCheck;
$symbUpdate = $symbCheck;
$symbRupee = '&#8377';
$symbStuckOutTounge = "&#9786";
$symbBell = '&#128365';

?>
