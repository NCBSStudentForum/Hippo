<?php 

include_once 'header.php';
include_once 'methods.php';
include_once 'tohtml.php';

require_once './calendar/NCBSCalendar.php';

echo userHTML( );

// When we come here from ./authenticate_gcalendar.php page, the GOOGLE API 
// sends us a GET response. Use this token to process all other queries.

$calendar = new NCBSCalendar( './oauth-credentials.json' );
$client = $calendar->client;
$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
$client->setAccessToken($token);

// Now get the list of calendars.

$service = new Google_Service_Calendar( $client );
$caledars = $service->calendarList->listCalendarList( );
//foreach( $caledars as $calendar )
//   print_r( $calendar );

$calid = '6bvpnrto763c0d53shp4sr5rmk@group.calendar.google.com';
$events = $service->events->listEvents( $calid );
foreach( $events as $event )
{
    //var_dump( $event );
    //print_r( $event );
    echo $event['summary'];
    echo "<br>==========================<br>";
}

exit;

//goToPage( "admin.php", 0 );

?>
