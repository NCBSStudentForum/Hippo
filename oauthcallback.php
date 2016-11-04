<?php 

require_once 'header.php';
require_once './vendor/autoload.php';
require_once './template/google-api/base.php';


var_dump( $_SESSION['gcal_token'] );

if( ! array_key_exists( 'gcal_token', $_SESSION ) )
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


    if (isset($_GET['code'])) {
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $client->setAccessToken($token);
        // store in the session also
        $_SESSION['gcal_token'] = $token;
        goToPage( "admin.php", 0 );
        exit( 0 );
    }

    echo "Redirecting for authentication";
    $authUrl = $client->createAuthUrl();
    header( 'Location: ' . $authUrl, False );
    exit( 0 );
}

goToPage( "admin.php", 0 );

?>
