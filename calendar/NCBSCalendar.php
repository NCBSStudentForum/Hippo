<?php

include_once 'methods.php';

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

    public $calid = '6bvpnrto763c0d53shp4sr5rmk@group.calendar.google.com';

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
        $this->client->setAccessToken($token);
    }

    public function getEvents( )
    {
        $events = $this->service()->events->listEvents( $this->calid );
        return $events;
    }

    public function createEvent( $options )
    {
        $event = new Google_Event( );
        $event->setSummary( $option[ 'title' ] );
        $event->setLocation( __get__($option, 'location', 'unspecified' ) );
        $event->setStart( $option[ 'start_datetime' ] );
        $event->setEnd( $option['end_datetime'] );
        $createEvent = $this->service( )->events->insert( $this->calid, $event );
        return $createEvent;
    }


}

?>
