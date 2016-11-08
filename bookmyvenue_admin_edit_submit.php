<?php

include_once ("header.php" );
include_once( "database.php" );
include_once( "tohtml.php" );
include_once( "calendar/calendar.php");


//var_dump( $_POST );

if( strcasecmp($_POST['response'], 'submit' ) == 0 )
{
    $res = updateTable( 'events', 'gid'
        , array( 'is_public_event', 'class', 'short_description', 'description', 'status' )
        , $_POST 
    );

    if( $res )
    {
        echo printInfo( "updated succesfully" );
        $res = updateEventGroupInCalendar( $_POST['gid'] );
        // Update group in calendar.
        goToPage( "admin.php", 1 );
        exit( 0 );
    }
    else
        echo printWarning( "Above events were not updated" );

}

echo goBackToPageLink( "admin.php", "Go back" );

?>
