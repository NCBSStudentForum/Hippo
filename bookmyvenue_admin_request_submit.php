<?php
include_once( "header.php" );
include_once( "database.php" );

if( ! array_key_exists( 'events', $_POST ) )
{
    echo printWarning( "You did not select any request" );
    echo goBackToPageLink( "bootstrap_admin.php", "Go back" );
    exit(0);
}

$events = $_POST['events'];
$whatToDo = $_POST['response'];
$isPublic = $_POST['isPublic'];

// Ask user if they want to put this request on public calendar.

foreach( $events as $event )
{
    $event = explode( '.', $event );
    $gid = $event[0]; $rid = $event[1];
    actOnRequest( $gid, $rid, $whatToDo );
    changeIfEventIsPublic( $gid, $rid, $isPublic );
}

goToPage( "bookmyvenue_admin.php", 1 );

?>
