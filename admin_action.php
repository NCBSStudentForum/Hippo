<?php 

include_once( 'calendar/calendar.php' );
include_once( 'vendor/autoload.php' );
include_once( 'template/google-api/base.php' );

echo userHTML( );

//if( $_POST['response'] == 'add_all_events' ) 
{
    $client = new Google_Client();

    // Authenticate the client now.
    if (!$oauth_credentials = getOAuthCredentialsFile()) {
        echo missingOAuth2CredentialsWarning();
        return;
    }

    $client->setAuthConfig($oauth_credentials);
    //$redirectURI = 'http://ghevar.ncbs.res.in/minion/admin.php';
    //$client->setRedirectURI( $redirectURI );
    $client->setScopes(
        'https://www.googleapis.com/auth/calendar'
    );

    $token = $client->fetchAccessTokenWithAuthCode($_SESSION['gcal_token']);
    $client->setAccessToken($token);

    if ($client->getAccessToken())
        $token_data = $client->verifyIdToken();

    addAllEventsToCalednar( 'NCBS Calendar', $client );
}
//else
//{
    //echo printWarning( 'Invalid response by user' . $_POST['response'] );
//}

//var_dump( $_SESSION );

//echo goBackToPageLink( 'admin.php', 'Go back' );

?>
