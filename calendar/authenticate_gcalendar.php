<?php 

include_once 'calendar/NCBSCalendar.php';

$calendar = new NCBSCalendar( './oauth-credentials.json' );
header( 'Location: ' . $calendar->redirectURL, False );

exit;

?>
