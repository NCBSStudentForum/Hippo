<?php 

include_once 'header.php';
include_once 'methods.php';
include_once 'tohtml.php';

require_once './calendar/NCBSCalendar.php';

echo userHTML( );

// When we come here from ./authenticate_gcalendar.php page, the GOOGLE API 
// sends us a GET response. Use this token to process all other queries.

$calendar = new NCBSCalendar( './oauth-credentials.json' );
$calendar->setAccessToken( $_GET['code'] );

// Now get all events
foreach( $calendar->getEvents() as $event )
{
    //var_dump( $event );
    //print_r( $event );
    echo $event['summary'];
    echo "<br>==========================<br>";
}

exit;

//goToPage( "admin.php", 0 );

?>
