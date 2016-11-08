<?php 

include_once 'header.php';
include_once 'methods.php';
include_once 'tohtml.php';
include_once 'database.php';

require_once './calendar/NCBSCalendar.php';

echo userHTML( );

// We come here from google-calendar 
// When we come here from ./authenticate_gcalendar.php page, the GOOGLE API 
// sends us a GET response. Use this token to process all other queries.

$calendar = new NCBSCalendar( './oauth-credentials.json' );
$calendar->setAccessToken( $_GET['code'] );

$publicEvents = getPublicEvents( );

if( array_key_exists( 'google_command', $_SESSION ) )
{ 
    //
    if( $_SESSION['google_command'] == 'synchronize_all_events' )
    {

        foreach( $publicEvents as $event )
        {
            //if( $event['calendar_id'] != '' && $event[ 'calendar_event_id' ] != '' )
            if( $calendar->exists( $event ) )
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
    }
    else if( $_SESSION[ 'google_command' ] == 'update_eventgroup' )
    {
        $events = getEventsByGroupId( $_SESSION[ 'event_gid' ] );
        $total = count( $events );
        echo printInfo( "Updating total " . $total 
            . " events with group id " . $_SESSION['event_gid'] );


        for( $i = 1; $i <= $total; $i++ )
        {
            $event = $events[ $i - 1 ];
            if( $event[ 'is_public_event' ] == 'YES' )
            {
                $calendar->insertOrUpdateEvent( $event );
                echo "... Done updating event $i of $total <br>";
                ob_flush( ); flush();
            }
        }
    }

    else
        echo printWarning(
            "Unsupported  command " .  $_SESSION['google_command'] 
        );
}
else
{
    echo printInfo( "No command is given regarging google calendar" );
}

echo goBackToPageLink( "user.php", "Go back" );
echo '<br> <br> <br>';

exit;


?>
