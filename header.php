<!DOCTYPE html>

<?php 
ob_start();

/**
 * @brief Return link to calendar.
 * @return 
 */
function calendarURL( ) 
{
    return 'https://calendar.google.com/calendar/embed?src=d2jud2r7bsj0i820k0f6j702qo%40group.calendar.google.com&ctz=Asia/Calcutta';
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

<h1><a href="index.php" style="text-decoration:none">NCBS Hippo</a></h1>
<!--
<a href="https://github.com/dilawar/ncbs-hippo" target='_blank'>Github</a>
<a href='https://github.com/dilawar/ncbs-hippo/issues' target="_blank">Report issue</a></small>
-->
<div style="font-size:small">
<table class="public_links">
    <tr>
    <td>
    <a href="allevents.php" target="_blank">All Bookings</a>
    </td>
    <td>
    <a href="aws.php" target="_blank">AWSs</a>
    </td>
    <td>
    <a href="events.php" target="_blank">Talks</a>
    </td>
    <td> <a href="user_aws_search.php" target="_blank">Search AWS</a> </td>
    <td> <a href="community_graphs.php" target="_blank" >Community graph</a> </td>
    <td> <a href="statistics.php" target="_blank" >Statistics </a> </td>
    <!-- <td> <a href="active_speakers.php" target="_blank" >AWS speakers</a></td> -->
    <td> <a href="courses.php" target="_blank" >Courses</a></td>
    <td> <a href="map.php" target="_blank" >Map</a></td>
    <!--
    RSS has been thorougly abused to make it work with NCBS screen.
    <td> <a href="rss.php" target="_blank" >
            <img src="data/feed-icon-14x14.2168a573d0d4.png" 
                alt="Subscribe to public events">
            </a></td>
    -->
    <td> <a href="howto.php" target="_blank" >HOWTO</a></td>
    </tr>
</table>
</div>

</div>
<br />
<br />
</html>

<!-- Here are the js script -->
<script src="js/jquery-1.12.4.js"></script>
<script src="js/jquery-ui.js"></script>
<link rel="stylesheet" href="js/jquery-ui.css">

<script src="js/easyzoom.js"></script>
<link rel="stylesheet" href="js/easyzoom.css">

<script src="js/jquery-ui.multidatespicker.js"></script>

<!-- Sweet alert -->
<script src="./node_modules/sweetalert/dist/sweetalert.min.js"></script>
<link rel="stylesheet" type="text/css" href="./node_modules/sweetalert/dist/sweetalert.css">



<script type="text/javascript" src="js/jquery-timepicker-1.11.9/jquery.timepicker.js"></script>

<link rel="stylesheet" type="text/css" href="js/jquery-timepicker-1.11.9/jquery.timepicker.css" />
<script type="text/javascript" src="js/jquery-timepicker-1.11.9/lib/site.js"></script>

<link href="hippo.css" rel="stylesheet" type="text/css" />

<!-- Disable favicon requests -->
<link rel="icon" href="data:,">


<script>
$( function() {
    $( "input.datepicker" ).datepicker( { dateFormat: "yy-mm-dd" } );
  } );
$( function() {
    $( "input.datetimepicker" ).datepicker( { dateFormat: "yy-mm-dd" } );
  } );
</script>

<script>
$(function(){
    $('input.timepicker').timepicker( { 
            minTime : '8am'
            , scrollDefault : 'now'
            , timeFormat : 'H:i', step : '15' 
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
<script src="tinymce/js/tinymce/tinymce.min.js"></script>


<!-- confirm on delete -->
<script type="text/javascript" charset="utf-8">
function AreYouSure( button )
{
    swal({
      title: "Are you sure?",
      text: "This is a destructive operation.",
      type: "warning",
      showCancelButton: true,
      confirmButtonColor: "#DD6B55",
      confirmButtonText: "Yes, continue!",
      closeOnConfirm: false,
      closeOnCancel: false
    },
    function( x ){
        console.log( x );
        if( x )
            button.setAttribute( 'value', 'delete' );
        else
            button.setAttribute( 'value', 'DO_NOTHING' );
        return x;
    });
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
    }, 500);
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
    echo printWarning( "Config file is not found. Can't continue" );
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

?>
