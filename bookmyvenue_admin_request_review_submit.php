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

// If admin is rejecting then ask for confirmation.
if( $whatToDo == 'REJECT' )
{
    echo '<script>
        var r = confirm( "Are you sure ?");
        if( r == false ) {
            var path = window.location.pathname;
            window.location.pathname = 
                <?php echo appRootDir( ) . "bookmyvenue_admin.php" ?>;
        }
        </script>';

}

foreach( $events as $event )
{
    $event = explode( '.', $event );
    $gid = $event[0]; $rid = $event[1];
    actOnRequest( $gid, $rid, $whatToDo );
    changeIfEventIsPublic( $gid, $rid, $isPublic );
    if( $whatToDo == 'APPROVE' && $isPublic == 'YES' )
    {
        // TODO: Add this to google calendar. 
        header( "Location:bookmyvenue_admin_update_eventgroup.php?event_gid=$gid" );
        exit;
    }
}

echo goBackToPageLink( "bookmyvenue_admin.php", "Go back" );
exit( 0 );

?>
