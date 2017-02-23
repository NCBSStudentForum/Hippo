<?php

include_once "header.php" ;
include_once "database.php";
include_once 'tohtml.php';
include_once 'mail.php';
include_once 'methods.php';
include_once 'check_access_permissions.php';

mustHaveAllOfTheseRoles( array( 'BOOKMYVENUE_ADMIN' ) );

echo printInfo( "Submitting request to database ..." );

$whatToDo = $_POST['response'];
$isPublic = $_POST['isPublic'];

if( ! array_key_exists( 'events', $_POST ) )
{
    echo printWarning( "You did not select any request" );
    echo goBackToPageLink( "bookmyvenue_admin.php", "Go back" );
    exit(0);
}



// If admin is rejecting and have not given any confirmation, ask for it.
if( $whatToDo == 'REJECT' )
{
    // If no valid response is given, rejection of request is not possible.
    if( strlen( $_POST[ 'reason' ] ) < 5 )
    {
        echo printWarning( "Before you can reject a request, you must provide
            a valid reason (more than 5 characters long)" );
        goToPage( "bookmyvenue_admin.php", 3 );
        exit;
    }
}


$msg = "<p>Some changes have been made to your request. The latest entry looks
        like the following. </p>";

$msg .= '<table border="0">';
$events = $_POST['events'];
$userEmail = '';
$eventGroupTitle = '';

if( count( $events ) < 1 )
{
    echo minionEmbarrassed( "I could not find an event." );
    echo goBackToPageLink( "bookmyvenue_admin.php", "Go back" );
    exit;
}
else
{
    $group = array( );
    foreach( $events as $event )
    {
        $event = explode( '.', $event );
        $gid = $event[0]; $rid = $event[1];

        $eventInfo = getRequestById( $gid, $rid );
        $eventText = eventToText( $eventInfo );
        array_push( $group, $eventInfo );

        $userEmail = getLoginEmail(  $eventInfo[ 'created_by' ] );
        $eventGroupTitle = $eventInfo[ 'title' ];

        $msg .= "<tr><td> $eventText </td><td>". $whatToDo ."ED</td></tr>";

        try {
            actOnRequest( $gid, $rid, $whatToDo );
        } catch ( Exception $e ) {
            echo printWarning( "Failed to update request: " . $e->getMessage( ) );
            echo goBackToPageLink( "bookmyvenue_admin.php", "Go back" );
            exit;
        }
        changeIfEventIsPublic( $gid, $rid, $isPublic );

        if( $whatToDo == 'APPROVE' && $isPublic == 'YES' )
        {
            echo printInfo( "<pre>TODO</pre>Sync calendar here" );
            // TODO: Add this to google calendar. Test it before you enable it.
            //header( "Location:bookmyvenue_admin_update_eventgroup.php?event_gid=$gid" );
            //exit;
        }
    }
    $msg .= "</table>";

    // Append user email to front.
    $msg = "<p>Dear " . loginToText( $group[0]['created_by' ], true ) . '</p>' . $msg;

    $res = sendEmail( $msg
        , "Your request for event title '$eventGroupTitle'  has been acted upon"
        , $userEmail 
        );

    if( $res )
    {
        goToPage( "bookmyvenue_admin.php", 0 );
        exit;
    }
    else
    {
        echo minionEmbarrassed( "I failed to send email to user " );
    }
}
    
echo goBackToPageLink( "bookmyvenue_admin.php", "Go back" );

?>
