<?php

include_once( 'database.php' );
include_once( 'methods.php' );
include_once( 'tohtml.php' );
require_once './vendor/autoload.php';
require_once './template

function embdedCalendar( )
{
    $html = '
        <iframe src="https://calendar.google.com/calendar/embed?src=6bvpnrto763c0d53shp4sr5rmk%40group.calendar.google.com&ctz=Asia/Calcutta" style="border: 0" width="800" height="600" frameborder="0" scrolling="no"></iframe>';
    return $html;
}

function authenticate( )
{

    /*************************************************
     * Ensure you've downloaded your oauth credentials
     ************************************************/
    if (!$oauth_credentials = getOAuthCredentialsFile()) {
        echo missingOAuth2CredentialsWarning();
        return;
    }
    /************************************************
     * NOTICE:
     * The redirect URI is to the current page, e.g:
     * http://localhost:8080/idtoken.php
     ************************************************/
    $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
    $client = new Google_Client();
    $client->setAuthConfig($oauth_credentials);
    $client->setRedirectUri($redirect_uri);
    $client->setScopes('email');
    /************************************************
     * If we're logging out we just need to clear our
     * local access token in this case
     ************************************************/
    if (isset($_REQUEST['logout'])) {
        unset($_SESSION['id_token_token']);
    }
    /************************************************
     * If we have a code back from the OAuth 2.0 flow,
     * we need to exchange that with the
     * Google_Client::fetchAccessTokenWithAuthCode()
     * function. We store the resultant access token
     * bundle in the session, and redirect to ourself.
     ************************************************/
    if (isset($_GET['code'])) {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $client->setAccessToken($token);
        // store in the session also
        $_SESSION['id_token_token'] = $token;
        // redirect back to the example
        header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
    }
/************************************************
  If we have an access token, we can make
  requests, else we generate an authentication URL.
************************************************/
    if (
        !empty($_SESSION['id_token_token'])
        && isset($_SESSION['id_token_token']['id_token'])
    ) {
        $client->setAccessToken($_SESSION['id_token_token']);
    } else {
        $authUrl = $client->createAuthUrl();
    }
/************************************************
  If we're signed in we can go ahead and retrieve
  the ID token, which is part of the bundle of
  data that is exchange in the authenticate step
  - we only need to do a network call if we have
  to retrieve the Google certificate to verify it,
  and that can be cached.
************************************************/
    if ($client->getAccessToken()) {
        $token_data = $client->verifyIdToken();
    }
}

function addEventToGoogleCalendar($calendar_name, $event )
{
    $duration = round( (strtotime($event['end_time']) - strtotime($event['start_time'])) / 60.0 );
    authenticate( );
    return 0;

    $client = new Google_Client();
    $client->setAuthConfig( './minion-416c6a627083.json' );

    $service = new Google_Service_Calendar($client);

    $results = $service->calendarList->listCalendarList( );

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
