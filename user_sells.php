<?php 

include_once( "header.php" );
include_once( "methods.php" );
include_once( "database.php" );
include_once 'tohtml.php';
include_once 'check_access_permissions.php';

mustHaveAnyOfTheseRoles( array( 'USER' ) );

echo userHTML( );

// Create an entry.
$default = array( 'created_by' => $_SESSION[ 'user' ] 
                , 'created_on' => dbDateTime( strtotime( 'now' ) )
                );
echo dbTableToHTMLTable( 'nilami_items', $default 
        , 'item_name,description,price,contact_info,comment,tags'
    );


echo goBackToPageLink( "user.php", "Go back" );

?>
