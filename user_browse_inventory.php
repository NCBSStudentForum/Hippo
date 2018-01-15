<?php
include_once 'header.php';
include_once 'database.php';
include_once 'methods.php';
include_once 'tohtml.php';
include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles('USER' );
echo userHTML( );


$items = getTableEntries( 'inventory', 'common_name', "status='VALID'" );

if (count( $items ) < 1)
{
    echo printInfo( "No item found in inventory." );
}
else
{
    foreach( $items  as $item )
    {
        echo arrayToTableHTML( $item, 'entry_long' );
    }
}

echo " <br /> ";
echo goBackToPageLink( "user.php", "Go back" );

?>
