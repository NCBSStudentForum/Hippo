<?php 
session_start( );

include_once 'header.php';
include_once 'methods.php';
include_once 'tohtml.php';
include_once 'database.php';
include_once 'check_access_permissions.php';
require_once './calendar/NCBSCalendar.php';

mustHaveAllOfTheseRoles( Array( 'BOOKMYVENUE_ADMIN' ) );

?>

<!-- Progress bar holder -->
<div id="progress" style="width:500px;border:1px solid #ccc;"></div>
<!-- Progress information -->
<div id="information" style="width"></div>

<?php

echo userHTML( );

// We come here from google-calendar 
// When we come here from ./authenticate_gcalendar.php page, the GOOGLE API 
// sends us a GET response. Use this token to process all other queries.

$calendar = new NCBSCalendar( $_SESSION[ 'oauth_credential' ]
    , $_SESSION['calendar_id'] );

echo alertUser( "Just a reminder, you MUST login to google-account which own the 
    calendar " );

$calendar->setAccessToken( $_GET['code'] );

$everythingWentOk = true;

// Find event in list of events but comparing summary.
function findEvent( $events, $googleEvent )
{
    $found = false;
    foreach( $events as $e )
    {
        // Database event compared with google event summary.
        //echo "<pre>Comparing " . $e[ 'calendar_event_id' ] . " and " . 
            //$googleEvent['id'] . "</pre><br/>" ;

        if( $e[ 'calendar_event_id' ] == $googleEvent[ 'id' ] )
        {
            $found = true;
            break;
        }
    }

    return $found;
}




if( array_key_exists( 'google_command', $_SESSION ) )
{ 
    if( $_SESSION['google_command'] == 'synchronize_all_events' )
    {
        echo alertUser( "Synchronizing google calendar ..." );

        $publicEvents = getPublicEvents( $form = 'today' );
        var_dump( $publicEvents );

        $total = count( $publicEvents );

        // Update all public events first.
        echo printInfo( "Putting local update to google calendar " );
        for ($i = 0; $i < $total; $i++) 
        {
            $event = $publicEvents[ $i ];
            try {
                if( $calendar->exists( $event ) )
                    $gevent = $calendar->updateEvent( $event );
                else 
                    $gevent = $calendar->addNewEvent( $event );
            } catch ( Exception $e ) {
                echo printWarning( "Failed to add or update event: " . $e->getMessage( ) );
            }

        }

        // Now get all events from google calendar and if some of them are not 
        // in database, remove them if they are not available locally. This 
        // means some events have been deleted locally, they should be deleted 
        // from calendar as well.
        $eventsOnGoogleCalendar = $calendar->getEvents( $from = 'today' );
        $total = count( $eventsOnGoogleCalendar );
        $i = 0;
        foreach( $eventsOnGoogleCalendar as $event )
        {
            if( findEvent( $publicEvents, $event ) )
                continue;           // We are good.
            else
            {
                echo printInfo( "Deleting event: " . $event[ 'summary' ] . 
                    " because this event is not found in local database " );
                echo "</br>";
                //$calendar->deleteEvent( $event );
                ob_flush(); flush( );
            }

            $percent = intval( $i / $total * 100 ) . "%";
            echo '<script language="javascript">
                document.getElementById("progress").innerHTML="<div 
                    style=\"width:'.$percent.';background-color:#ddd;\">&nbsp;</div>";
                document.getElementById("information").innerHTML="'.$i.' row(s) processed.";
            </script>';
            $i += 1;
            echo str_repeat( ' ', 1024*64 );
            flush( );
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
                echo str_repeat( ' ', 1024*64 );
                flush( );
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
