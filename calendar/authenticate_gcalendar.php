<?php 

include_once 'NCBSCalendar.php';

$calendar = new NCBSCalendar( './oauth-credentials.json' );
header( 'Location:' . $calendar->redirectURL, False );

exit;

?>
