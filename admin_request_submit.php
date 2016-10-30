<?php
include_once( "header.php" );
include_once( "database.php" );

if( ! array_key_exists( 'events', $_POST ) )
{
    echo printWarning( "You did not select any request" );
    goToPage( "admin.php", 2 );
    exit(0);
}

$requests = $_POST['events'];
$whatToDo = $_POST['response'];
$requestId = $_POST['request_id'];

foreach( $requests as $request ) {
    echo printInfo( "Changing status to $whatToDo for request $request" );
    actOnRequest( $request, $requestId, $whatToDo );
}

goToPage( "admin.php", 1 );

?>
