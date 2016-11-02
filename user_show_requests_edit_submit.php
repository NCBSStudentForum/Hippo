<?php
include_once( "header.php" );
include_once( "database.php" );

if( strcasecmp($_POST['response'], 'submit' ) == 0 )
{
    $res = updateRequestGroup( $_POST['gid'] ,  $_POST );
    if( $res )
        echo printInfo( "Successfully updated request" );
    else
        echo printWarning( "Failed to update update request" );

    goToPage( "user_show_requests.php", 1 );
}
?>
