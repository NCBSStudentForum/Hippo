<?php 

include_once 'NCBSCalendar.php';

$calendar = new NCBSCalendar( $_SESSION[ 'oauth_credential' ]
    , $_SESSION[ 'calendar_id' ] );

header( 'Location:' . $calendar->redirectURL );
exit;

?>
