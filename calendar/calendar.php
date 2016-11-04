<?php

include_once( 'database.php' );
include_once( 'methods.php' );
include_once( 'tohtml.php' );


function addEventToGoogleCalendar($calendar_name, $event, $client )
{
    $duration = round( (strtotime($event['end_time']) - strtotime($event['start_time'])) / 60.0 );



    return 0;

    // FIXME: the timeout is neccessary. We don't want the system to hang for 
    // writing to google calendar.
    //echo arrayToTableHTML( $event, 'event' );

    // Before running this command make sure that we have authenticated the app.
    $cmd = 'timeout 2 gcalcli ';
    $cmd .= ' --configFolder ' . getCwd( );
    //$cmd .= " --client_id $clientId";
    //$cmd .= " --client_secret $clientSecret";
    $cmd .= " --calendar '$calendar_name'";
    $cmd .= " --title '" . $event['short_description'] . "'";
    $cmd .= " --where '" . venueSummary( getVenueById($event['venue']) ) . "'";
    $cmd .= " --when '" . $event['date'] . ' ' . $event['start_time'] . "'";
    $cmd .= " --duration $duration ";
    $cmd .= " --reminder 60 ";
    $cmd .= " --description '" . $event["description"] . "'";
    // These are attendees.
    //$cmd .= " --who '" . $event['user'] . "@ncbs.res.in'";
    $cmd .= ' add';
    $cmd = escapeshellcmd( $cmd );

    echo("<br>Executing: $cmd");
    $output = NULL; $return = NULL;
    exec( $cmd, $output, $return );

    echo( "<br>Command said: <br>" );
    var_dump( $return );
    var_dump( $output );
    echo( "<br>" );

    if( $return == 0 )
    {
        echo printInfo( "Successfully added event to public calendar $calendar_name" );
        return 0;
    }
    else 
    {
        echo printWarning( "Could not add event to calendar $calendar_name" );
        echo printWarning( "Error was " . $output );
        echo printWarning("TODO: Write email to admin");
        return -1;
    }
}

// This function uses gcalcli command to sync my local caledar with google 
// calendar.
function addAllEventsToCalednar( $calendarname, $client )
{
    $events = getEvents( );
    echo "Total " . count( $events ) . " to write";
    $service = new Google_Service_Calendar($client);
    $results = $service->calendarList->listCalendarList( );
    var_dump( $results );


    foreach( $events as $e )
    {
        addEventToGoogleCalendar( $calendarname, $client );
        return 0;
    }
}

?>
