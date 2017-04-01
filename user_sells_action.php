<?php 

include_once( "header.php" );
include_once( "methods.php" );
include_once( "database.php" );
include_once 'tohtml.php';
include_once 'check_access_permissions.php';

mustHaveAnyOfTheseRoles( array( 'USER' ) );

echo userHTML( );

// Create a new entry.
if( $_POST[ 'response' ] == 'submit' )
{
    //var_dump( $_POST );
    echo printInfo( "Creating a new entry" );
    $totalEntries = getNumberOfEntries( 'nilami_items', 'id' );
    $id = intval( __get__( $totalEntries, 'id', 0 ) ) + 1;
    $_POST[ 'id' ] = $id;
    $res = insertIntoTable( 'nilami_items'
        , 'id,created_by,created_on,item_name,description,price,status,'
            . 'contact_info,tags,comment'
            , $_POST 
        );
    if( $res )
    {
        echo printInfo( "Successfully put your item in nilami store" );
        echo goBack( "user.php", 2 );
        exit;
    }
    else
        echo alertUser( "Could not create your entry" );
}


echo goBackToPageLink( "user.php", "Go back" );

?>
