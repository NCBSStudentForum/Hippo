<?php

include_once ("header.php" );
include_once( "database.php" );
include_once( "tohtml.php" );


if( strcasecmp($_POST['response'], 'submit' ) == 0 )
{
    $res = updateTable( 'events', 'gid'
        , array( 'is_public_event', 'class', 'title', 'description', 'status' )
        , $_POST 
    );

    if( $res )
    {
        echo printInfo( "updated succesfully" );
        header( 
            "Location:bookmyvenue_admin_update_eventgroup.php?event_gid=" 
            .  $_POST[ 'gid' ] 
        );
        exit;
    }
    else
        echo printWarning( "Above events were not updated" );

}

echo goBackToPageLink( "admin.php", "Go back" );

?>
