<?php

include_once( 'database.php' );
include_once( 'methods.php' );
include_once( 'tohtml.php' );

function embdedCalendar( )
{
    $html = '
        <iframe src="https://calendar.google.com/calendar/embed?src=6bvpnrto763c0d53shp4sr5rmk%40group.calendar.google.com&ctz=Asia/Calcutta" style="border: 0" width="800" height="600" frameborder="0" scrolling="no"></iframe>';
    return $html;
}

function addEventToGoogleCalendar($calendar_name, $event )
{
    $duration = round( (strtotime($event['end_time']) - strtotime($event['start_time'])) / 60.0 );

    // FIXME: the timeout is neccessary. We don't want the system to hang for 
    // writing to google calendar.
    //echo arrayToTableHTML( $event, 'event' );
    $cmd = 'timeout 2 gcalcli ';
    $cmd .= " --calendar '$calendar_name'";
    $cmd .= " --title '" . $event['short_description'] . "'";
    $cmd .= " --where '" . venueSummary( getVenueById($event['venue']) ) . "'";
    $cmd .= " --when '" . $event['date'] . ' ' . $event['start_time'] . "'";
    $cmd .= " --duration $duration ";
    $cmd .= " --reminder 60 ";
    $cmd .= " --description '" . $event["description"] . "'";
    $cmd .= " --who '" . $event['user'] . "@ncbs.res.in'";
    $cmd .= ' add';
    $output = Array();
    $return = Array( );
    $cmd = escapeshellcmd( $cmd );
    echo printInfo("Executing $cmd");
    exec( $cmd, $output, $return );
    if( $return == 0 )
    {
        echo printInfo( "Successfully added event to public calendar $calendar_name" );
        return 0;
    }
    else 
    {
        echo printWarning( "Could not add event to calendar $calendar_name" );
        echo printWarning( "Error was " . $output[0] );
        echo "TODO: Write email to admin";
        return -1;
    }
    ob_flush( );
}

// This function uses gcalcli command to sync my local caledar with google 
// calendar.
function addAllEventsToCalednar( $calendarname )
{
    $events = getEvents( );
    echo "Total " . count( $events ) . " to write";
    foreach( $events as $e )
        addEventToGoogleCalendar( $calendarname, $e );
}

?>
