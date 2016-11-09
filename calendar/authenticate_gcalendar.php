<?php 
session_start( );

include_once 'NCBSCalendar.php';

$calendar = new NCBSCalendar( $_SESSION[ 'oauth_credential' ] );

header( 'Location:' . $calendar->redirectURL );
exit;

?>
