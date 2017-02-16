<?php

include_once "header.php" ;
include_once "database.php";
include_once 'tohtml.php';
include_once 'mail.php';
include_once 'methods.php';


$whatToDo = $_POST['response'];
$isPublic = $_POST['isPublic'];

if( ! array_key_exists( 'events', $_POST ) )
{
    echo printWarning( "You did not select any request" );
    echo goBackToPageLink( "bookmyvenue_admin.php", "Go back" );
    exit(0);
}

$msg = initUserMsg( );


// If admin is rejecting then ask for confirmation.
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

$msg .= "<p>Note the following changes to your requests </p>";
$msg .= '<table border="0">';

$events = $_POST['events'];
$userEmail = '';
$eventGroupTitle = '';

foreach( $events as $event )
{
    $event = explode( '.', $event );
    $gid = $event[0]; $rid = $event[1];

    $eventInfo = getRequestById( $gid, $rid );
    $eventText = eventToText( $eventInfo );

    $userEmail = $eventInfo[ 'created_by' ];
    $eventGroupTitle = $eventInfo[ 'title' ];

    $msg .= "<tr><td> $eventText </td><td>". $whatToDo ."ED</td></tr>";

    actOnRequest( $gid, $rid, $whatToDo );
    changeIfEventIsPublic( $gid, $rid, $isPublic );

    if( $whatToDo == 'APPROVE' && $isPublic == 'YES' )
    {
        // TODO: Add this to google calendar. 
        header( "Location:bookmyvenue_admin_update_eventgroup.php?event_gid=$gid" );
        exit;
    }
}
$msg .= "</table>";

$res = sendEmail( $msg
    , "[ $whatToDo ] Your request for event title '$eventGroupTitle'  
            has been acted upon"
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
    
echo goBackToPageLink( "bookmyvenue_admin.php", "Go back" );

?>
