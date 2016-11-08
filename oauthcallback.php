<?php 

include_once 'header.php';
include_once 'methods.php';
include_once 'tohtml.php';
include_once 'database.php';

require_once './calendar/NCBSCalendar.php';

echo userHTML( );

// When we come here from ./authenticate_gcalendar.php page, the GOOGLE API 
// sends us a GET response. Use this token to process all other queries.

$calendar = new NCBSCalendar( './oauth-credentials.json' );
$calendar->setAccessToken( $_GET['code'] );

$publicEvents = getPublicEvents( );

foreach( $publicEvents as $event )
{

    if( $event['calendar_id'] != '' && $event[ 'calendar_event_id' ] != '' )
    {
        echo "This event " 
            .  $event['short_description'] 
            . " is already in public calendar. Updating it .... "
            ;
        $res = $calendar->updateEvent( $event );
        if( $res )
            echo "Successfully updated. <br>" ;
        else
            echo "Failed to update. <br>";
    }
    else 
    {
        $gevent = $calendar->addNewEvent( $event );
        flush( );
        ob_flush();
    }
}

echo goBackToPageLink( "user.php", "Go back" );
echo '<br> <br> <br>';

exit;


?>
