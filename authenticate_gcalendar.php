<?php 

include_once 'calendar/NCBSCalendar.php';

$calendar = new NCBSCalendar( './oauth-credentials.json' );

// Now move the browser to $authUrl 
var_dump( $calendar );

header( 'Location: ' . $calendar->redirectURL, False );

exit;

?>
