<?php

include_once ("header.php" );
include_once( "database.php" );
include_once( "tohtml.php" );


//var_dump( $_POST );

if( strcasecmp($_POST['response'], 'submit' ) == 0 )
{
    $res = updateEventGroup( $_POST['gid'], $_POST );
    if( $res )
    {
        echo printInfo( "updated succesfully" );
        goToPage( "admin.php", 1 );
        exit( 0 );
    }
    else
        echo printWarning( "Above events were not updated" );

}

echo goBackToPageLink( "admin.php", "Go back" );

?>
