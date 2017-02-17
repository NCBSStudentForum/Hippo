<?php 

include_once __DIR__ . '/../check_access_permissions.php';
mustHaveAllOfTheseRoles( array( 'BOOKMYVENUE_ADMIN' ) );

include_once 'NCBSCalendar.php';

$calendar = new NCBSCalendar( $_SESSION[ 'oauth_credential' ]
    , $_SESSION[ 'calendar_id' ] );

// Check here if a user is logged in, if not she needs to be redirected back to 
// admin with proper message.
// TODO: Check if user has the proper authentication,
header( 'Location:' . $calendar->redirectURL );
exit;

?>
