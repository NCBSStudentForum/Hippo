<?php

include_once( 'calendar/NCBSCalendar.php' );
include_once( 'database.php' );
include_once( 'methods.php' );
include_once( 'tohtml.php' );

/**
 * @brief Return link to calendar.
 * TODO: This must be specified by admin later.
 *
 * @return 
 */
function calendarURL( ) 
{
    return '
<iframe src="https://calendar.google.com/calendar/embed?src=6bvpnrto763c0d53shp4sr5rmk%40group.calendar.google.com&ctz=Asia/Calcutta" style="border: 0" width="800" height="600" frameborder="0" scrolling="no"></iframe>
    ';
}

function addEventToGoogleCalendar($calendar_name, $event, $client )
{
}

// This function uses gcalcli command to sync my local caledar with google 
// calendar.
function addAllEventsToCalednar( $calendarname, $client )
{
}

function updateEventGroupInCalendar( $gid )
{
    $events = getEventsByGroupId( $gid );
    $calendar = new NCBSCalendar( './oauth-credentials.json' );
    foreach( $events as $event )
    {
        var_dump( $event );
        $calendar->updateEvent( $event );
    }
}

?>
