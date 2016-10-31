<?php
include_once( "header.php" );
include_once( "database.php" );

if( ! array_key_exists( 'events', $_POST ) )
{
    echo printWarning( "You did not select any request" );
    goToPage( "admin.php", 2 );
    exit(0);
}

$events = $_POST['events'];
$whatToDo = $_POST['response'];

foreach( $events as $event )
{
    echo printInfo( "Changing status to $whatToDo for request $event" );
    $event = explode( '.', $event );
    $gid = $event[0]; $rid = $event[1];
    actOnRequest( $gid, $rid, $whatToDo );
}

goToPage( "admin.php", 1 );

?>
