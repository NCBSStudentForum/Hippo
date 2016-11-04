<?php 

include_once( 'calendar/calendar.php' );


function authenticate( $client )
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
    $client->setAuthConfig($oauth_credentials);
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
        $authUrl = $client->createAuthUrl();
        header( 'Location: ' . $authUrl, False );
        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        $client->setAccessToken($token);
        // store in the session also
        $_SESSION['id_token_token'] = $token;
    }
    if ($client->getAccessToken()) {
        $token_data = $client->verifyIdToken();
    }
    return $client;
}

//if( $_POST['response'] == 'add_all_events' ) 
{
    $client = new Google_Client();
    authenticate( $client );
    addAllEventsToCalednar( 'NCBS Calendar', $client );
}
//else
//{
    //echo printWarning( 'Invalid response by user' . $_POST['response'] );
//}

var_dump( $_SESSION );

//echo goBackToPageLink( 'admin.php', 'Go back' );

?>
