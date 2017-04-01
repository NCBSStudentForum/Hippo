<?php 

include_once "header.php";
include_once "methods.php";
include_once "database.php";
include_once 'tohtml.php';
include_once 'mail.php';
include_once 'check_access_permissions.php';


mustHaveAnyOfTheseRoles( array( 'USER' ) );

echo userHTML( );

// Create a bid.
if( $_POST[ 'response' ] == 'New Alert' )
{
    echo printInfo( "Creating a new alert .. " );
    $res = insertIntoTable( 'alerts', 'login,on_table,on_field,value', $_POST );
    if( $res )
    {
        echo printInfo( "Successfully created a new alert. " );
        goBack( "user_tolet.php", 1 );
        exit;
    }
    else
        echo minionEmbarrassed( "Failed to create an alert" );


}


echo goBackToPageLink( "user_tolet.php", "Go back" );

?>
