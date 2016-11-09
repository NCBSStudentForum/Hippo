<?php

include_once "header.php" ;
include_once "database.php";
include_once 'tohtml.php';


$whatToDo = $_POST['response'];
$isPublic = $_POST['isPublic'];

if( ! array_key_exists( 'events', $_POST ) )
{
    echo printWarning( "You did not select any request" );
    echo goBackToPageLink( "bookmyvenue_admin.php", "Go back" );
    exit(0);
}
$events = $_POST['events'];
foreach( $events as $event )
{
    $event = explode( '.', $event );
    $gid = $event[0]; $rid = $event[1];
    actOnRequest( $gid, $rid, $whatToDo );
    changeIfEventIsPublic( $gid, $rid, $isPublic );
}

goToPage( "bookmyvenue_admin.php", 1 );
exit( 0 );

?>
