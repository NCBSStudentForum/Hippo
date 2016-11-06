<?php 
ob_start();
if(!isset($_SESSION)) 
    session_start();

// If this is not a index.php page. check if access is valid. 
if( ! $_SERVER['PHP_SELF'] == 'index.php' ) 
    include_once( 'is_valid_access.php' );
?>

<!DOCTYPE html>
<html>
<head>
<link href="minion.css" rel="stylesheet" type="text/css" />
</head>
<div class="header">
<title>NCBS Minion</title>
<h1>NCBS Minion</h1>
<small>Ver 0.9.0, GNU GPL <a href="https://github.com/dilawar/ncbs-minion" target='_blank'>(c) Dilawar Singh, 2016.</a>
<a href='mailto:dilawar.s.rajput@gamil.com'>Email developer</a></small>
</div>
<br />
<br />

</html>

<!-- Here are the js script -->
<link rel="stylesheet" href="js/jquery-ui.css">
<script src="js/jquery-1.12.4.js"></script>
<script src="js/jquery-ui.js"></script>

<script src="js/jquery-ui.multidatespicker.js"></script>

<script type="text/javascript" src="js/jquery-timepicker/jquery.timepicker.js"></script>
<link rel="stylesheet" type="text/css" href="js/jquery-timepicker/jquery.timepicker.css" />

<script type="text/javascript" src="js/jquery-timepicker/lib/site.js"></script>
<link rel="stylesheet" type="text/css" href="lib/site.css" />

<script>
$( function() {
    $( "input.datepicker" ).datepicker( { dateFormat: "yy-mm-dd" } );
  } );
</script>

<script>
$(function(){
    $('input.timepicker').timepicker({ 'timeFormat' : 'H:i', 'step' : '15' });
});

</script>

<!-- CKEDIR -->
<script src="./ckeditor/ckeditor.js"></script>

