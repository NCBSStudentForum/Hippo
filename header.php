<!DOCTYPE html>

<?php 
ob_start();

if(!isset($_SESSION)) 
    session_start();

// If this is not a index.php page. check if access is valid. 
if( ! $_SERVER['PHP_SELF'] == 'index.php' ) 
    include_once( 'is_valid_access.php' );

error_reporting( E_ALL );
ini_set( "display_errors", 1 );
ini_set( "log_errors", 1 );
ini_set( "error_log", "/var/log/hippo.log" );

?>

<html>
<meta content="text/html;charset=utf-8" http-equiv="Content-Type">

<div class="header">

<title>NCBS Hippo</title>

<h1><a href="index.php">NCBS Hippo</a></h1>
<!--
<a href="https://github.com/dilawar/ncbs-hippo" target='_blank'>Github</a>
<a href='https://github.com/dilawar/ncbs-hippo/issues' target="_blank">Report issue</a></small>
-->

</div>
<br />
<br />
</html>

<!-- Here are the js script -->
<script src="js/jquery-1.12.4.js"></script>
<script src="js/jquery-ui.js"></script>

<script src="js/easyzoom.js"></script>
<link rel="stylesheet" href="js/easyzoom.css">

<script src="js/jquery-ui.multidatespicker.js"></script>
<link rel="stylesheet" href="js/jquery-ui.css">


<script type="text/javascript" src="js/jquery-timepicker-1.11.9/jquery.timepicker.js"></script>

<link rel="stylesheet" type="text/css" href="js/jquery-timepicker-1.11.9/jquery.timepicker.css" />
<script type="text/javascript" src="js/jquery-timepicker-1.11.9/lib/site.js"></script>

<link href="hippo.css" rel="stylesheet" type="text/css" />

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
<!-- CKEDIR -->
<script src="tinymce/js/tinymce/tinymce.min.js"></script>

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
$symbUpdate = "&#x270d";
$symbCalendar = "&#128197";          // Does not work on chromium
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

?>
