<?php 

require_once './vendor/autoload.php';
require_once './template/google-api/base.php';

if( $_SESSION[ 'validate_calendar' ] )
    exit( 0 );

if( ! $_SESSION['token_set'] )
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

    if (isset($_REQUEST['logout'])) {
        unset($_SESSION['id_token_token']);
    }
    if (isset($_GET['code'])) {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $client->setAccessToken($token);
        // store in the session also
        $_SESSION['id_token_token'] = $token;
    }
    if (
        !empty($_SESSION['id_token_token'])
        && isset($_SESSION['id_token_token']['id_token'])
    ) {
        $client->setAccessToken($_SESSION['id_token_token']);
    } else {
        echo "I am here";
        $authUrl = $client->createAuthUrl();
        header( 'Location: ' . $authUrl, False );
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $client->setAccessToken($token);
        // store in the session also
        $_SESSION['id_token_token'] = $token;
    }

    if ($client->getAccessToken())
        $token_data = $client->verifyIdToken();

    $_SESSION[ 'calendar_client'] = $client;
    exit( 0 );


?>
