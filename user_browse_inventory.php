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
    $hide = 'id,status,last_modified_on,edited_by';
    echo ' <table class="info sorttabe">';
    echo arrayHeaderRow( $items[0], 'info sorttable', $hide );
    foreach( $items  as $item )
        echo arrayToRowHTML( $item, 'info sorttable', $hide );
    echo '</table>';
}

echo " <br /> ";
echo goBackToPageLink( "user.php", "Go back" );

?>
