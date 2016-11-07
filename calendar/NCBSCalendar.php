<?php

require_once './vendor/autoload.php';

/**
 * NCBS google calendar.
 */
class NCBSCalendar
{

    public $client = null;

    public $redirectURL = null;

    public $oauthFile = null;

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

}

?>
