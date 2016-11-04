<?php

include_once( 'database.php' );
include_once( 'methods.php' );
include_once( 'tohtml.php' );
require_once './vendor/autoload.php';

function embdedCalendar( )
{
    $html = '
        <iframe src="https://calendar.google.com/calendar/embed?src=6bvpnrto763c0d53shp4sr5rmk%40group.calendar.google.com&ctz=Asia/Calcutta" style="border: 0" width="800" height="600" frameborder="0" scrolling="no"></iframe>';
    return $html;
}

function addEventToGoogleCalendar($calendar_name, $event )
{
    $duration = round( (strtotime($event['end_time']) - strtotime($event['start_time'])) / 60.0 );

    $client = new Google_Client();
    $client->setApplicationName("Client_Library_Examples");
    $client->setDeveloperKey("YOUR_APP_KEY");

    $service = new Google_Service_Books($client);
    $optParams = array('filter' => 'free-ebooks');
    $results = $service->volumes->listVolumes('Henry David Thoreau', $optParams);

    foreach ($results as $item) {
        echo $item['volumeInfo']['title'], "<br /> \n";
    }


    return 0;

    // FIXME: the timeout is neccessary. We don't want the system to hang for 
    // writing to google calendar.
    //echo arrayToTableHTML( $event, 'event' );

    // Before running this command make sure that we have authenticated the app.
    $cmd = 'timeout 2 /usr/local/bin/gcalcli ';
    //$cmd .= ' --configFolder ' . getCwd( );
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
function addAllEventsToCalednar( $calendarname )
{
    $events = getEvents( );
    echo "Total " . count( $events ) . " to write";
    foreach( $events as $e )
    {
        addEventToGoogleCalendar( $calendarname, $e );
        return 0;
    }
}

?>
