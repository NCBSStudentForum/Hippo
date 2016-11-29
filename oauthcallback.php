<?php 
session_start( );

include_once 'header.php';
include_once 'methods.php';
include_once 'tohtml.php';
include_once 'database.php';
include_once 'check_access_permissions.php';
require_once './calendar/NCBSCalendar.php';

mustHaveAllOfTheseRoles( Array( 'BOOKMYVENUE_ADMIN' ) );

echo userHTML( );

// We come here from google-calendar 
// When we come here from ./authenticate_gcalendar.php page, the GOOGLE API 
// sends us a GET response. Use this token to process all other queries.

$calendar = new NCBSCalendar( $_SESSION[ 'oauth_credential' ]
    , $_SESSION['calendar_id'] );

$calendar->setAccessToken( $_GET['code'] );

$everythingWentOk = true;

// Find event in list of events but comparing summary.
function findEvent( $events, $event )
{
    foreach( $events as $e )
        if( $e[ 'summary' ] == $event[ 'summary' ] )
            return true;
    return false;
}


if( array_key_exists( 'google_command', $_SESSION ) )
{ 
    if( $_SESSION['google_command'] == 'synchronize_all_events' )
    {
        echo printInfo( "Synchronizing google calendar ..." );
        $publicEvents = getPublicEvents( );
        $total = count( $publicEvents );
        for ($i = 0; $i < $total; $i++) 
        {
            $event = $publicEvents[ $i ];

            if( $calendar->exists( $event ) )
                $gevent = $calendar->updateEvent( $event );
            else 
                $gevent = $calendar->addNewEvent( $event );

            echo "... Done with " . $i+1 . " out of total $total events <br>";
            ob_flush(); flush( );
        }

        // Now get all events from google calendar and if some of them are not 
        // in database, remove them.
        $eventsOnGoogleCalendar = $calendar->getEvents( );
        foreach( $eventsOnGoogleCalendar as $event )
        {
            if( findEvent( $publicEvents, $event ) )
                continue;           // We are good.
            else
            {
                echo "<pre>Deleting event: " . $event[ 'summary' ] . "</pre>";
                $calendar->deleteEvent( $event );
                ob_flush(); flush( );
            }
        }
    }
    else if( $_SESSION[ 'google_command' ] == 'update_eventgroup' )
    {
        $events = getEventsByGroupId( $_SESSION[ 'event_gid' ] );
        $total = count( $events );
        for( $i = 0; $i < $total; $i++ )
        {
            $event = $events[ $i ];
            if( $event[ 'is_public_event' ] == 'YES' )
            {
                // Insert is needed if an event is made public.
                $calendar->insertOrUpdateEvent( $event );
                echo "... Done updating event " .  $i + 1 . " of $total <br>";
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
    echo printInfo( "No command is given regarging google calendar" );

echo goBackToPageLink( "bookmyvenue_admin.php", "Go back" );
echo '<br> <br> <br>';
exit;

?>
