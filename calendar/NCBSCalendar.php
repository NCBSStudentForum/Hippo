<?php

include_once 'methods.php';
include_once 'database.php';
require_once './vendor/autoload.php';


/**
 * NCBS google calendar.
 */
class NCBSCalendar
{

    public $client = null;

    public $redirectURL = null;

    public $oauthFile = null;

    public $service = null;

    public $calID = '6bvpnrto763c0d53shp4sr5rmk@group.calendar.google.com';

    public function __construct( $oauth_file )
    {
        $this->client = new Google_Client( );
        if( file_exists($oauth_file) )
            $this->oauthFile =  $oauth_file;
        else
        {
            $ret = "
                   <h3 class='warn'>
                   Warning: You need to set the location of your OAuth2 Client Credentials from the
                   <a href='http://developers.google.com/console'>Google API console</a>.
                   </h3>
                   <p>
                   Once downloaded, move them into the root directory of this repository and
                   rename them 'oauth-credentials.json'.
                   </p>";
            echo $ret;
            return None;
        }

        $this->client->setAuthConfig( $this->oauthFile );
        $this->client->setScopes( 'https://www.googleapis.com/auth/calendar');
        $this->redirectURL = $this->client->createAuthUrl();
    }

    public function service( )
    {
        if( ! $this->service )
            $this->service = new Google_Service_Calendar( $this->client );

        return $this->service;
    }

    public function setAccessToken( $token )
    {
        $token = $this->client->fetchAccessTokenWithAuthCode($token );
        try {
            $this->client->setAccessToken($token);
        } catch (InvalidArgumentException $e) {
            echo printWarning( "Token expired! You must try again ..." );
            echo goBackToPageLink( "admin.php", "Go back"  );
            exit;
        }
    }

    public function getEvents( )
    {
        $events = $this->service()->events->listEvents( $this->calID );
        return $events;
    }

    /**
        * @brief Insert an event into public calendar.
        *
        * @param $option
        *
        * @return
     */
    public function insertEvent( $option )
    {
        $event = new Google_Service_Calendar_Event( $option );
        try
        {
            $createEvent = $this->service( )->events->insert( $this->calID, $event );
            return $createEvent;
        }
        catch (Google_ServiceException $e)
        {
            echo printWarning( "Failed to create a new event" );
            echo printWarning( "Error was : " . $e->getMessage( ) );
            return FALSE;
        }
        catch ( InvalidArgumentException $e )
        {
            echo minionEmbarrassed( 
                "I could not update public calendar"
                , "Error was " .  $e->getMessage() 
            );
        }
        return null;
    }

    public function getEvent( $calendarId, $eventId )
    {
        return $this->service( )->events->get( $calendarId, $eventId );
    }

    /**
        * @brief Update event on NCBS public calendar.
        *
        * @param $event This is our event from database.
        *
        * @return  TRUE on success, FALSE otherwise.
     */
    public function updateEvent( $event )
    {
        $gevent = $this->getEvent( 
            $event['calendar_id' ]
            , $event['calendar_event_id'] 
        );

        // Now update the summary and description of event. Changing time is not
        // allowed in any case.
        $gevent->setSummary( $event['short_description' ] );
        $gevent->setDescription( $event['description'] );
        $gevent->setHtmlLink( $event['url'] );

        try
        {
            print( gettype( $gevent ) );
            return $this->service( )->events->update( $event['calendar_id']
                , $gevent->getId( )
                , $gevent 
            );
        }
        catch ( Google_ServiceException $e )
        {
            echo printWarning(
                "This is embarassing! I could not update public calendar"
            );
            echo printWarning( "Error was : " . $e->getMessage( ) );
            return FALSE;
        }
        catch ( InvalidArgumentException $e )
        {
            echo minionEmbarrassed( 
                "I could not update public calendar"
                , "Error was " .  $e->getMessage() 
            );
        }

        return $gevent;
    }

    /**
        * @brief Insert database entry into google calendar.
        *
        * @param $event Datebase row.
        *
        * @return  new event on sucess, null otherwise
     */
    public function addNewEvent( $event )
    {
        $entry = array(
                     "summary" => $event['short_description']
                     , "description" => $event['description']
                     , 'location' => venueToText( getVenueById( $event['venue' ] ) )
                     , 'start' => array(
                         "dateTime" => $event['date'] .'T'. $event['start_time']
                         , "timeZone" => ini_get( 'date.timezone' )
                     )
                     , 'end' => array(
                         "dateTime" => $event['date'] .'T'. $event['end_time']
                         , "timeZone" => ini_get( 'date.timezone' )
                     )
                     , "htmlLink" => $event['url']
                     , "anyoneCanAddSelf" => True
                 );

        $gevent = $this->insertEvent( $entry );

        if( $gevent )
        {
            $event[ 'calendar_id' ] = $this->calID;
            $event[ 'calendar_event_id'] = $gevent->getId( );

            $res = updateTable( "events"
                                , array("gid","eid")
                                , array( "calendar_event_id","calendar_id")
                                , $event );
            return $res;
        }
        return $event;
    }
}

?>
