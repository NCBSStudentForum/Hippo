<?php 

include_once( "header.php" );
include_once( "methods.php" );
include_once( "database.php" );
include_once 'tohtml.php';
include_once 'check_access_permissions.php';

mustHaveAnyOfTheseRoles( array( 'USER' ) );

echo userHTML( );


$user = $_SESSION[ 'user' ];
$entries = getTableEntries( 'nilami_items', 'created_on'
                , "created_by='$user' AND status='AVAILABLE'"
            );
if( count( $entries ) > 0 )
{
    echo "<h3>My entries</h3>";
    echo "<div style=\"font-size:small\">";

    echo "<table>";
    foreach( $entries as $ent )
    {
        echo "<tr><td>";
        echo arrayToTableHTML( $ent, 'info', ''
                , 'id,created_by,status,last_updated_on,comment'
                 );
        echo "</td><td>";
        echo '<input type="hidden" name="id" value="' . $ent[ 'id' ] . '"/>';
        echo '<button name="response" value="edit">' . $symbEdit . '</td>';
        echo "</td></tr>";
    }
    echo "</table>";

    echo "</div>";
}


echo "<h3>Add/Edit a entry to Nilami Store</h3>";
echo printInfo( 
    "At least one tag is required, separete <tt>TAGS</tt> by comma or space.
    <tt>COMMENT</tt> is optional.
    " );

// Create an entry.
$default = array( 'created_by' => $_SESSION[ 'user' ] 
                , 'created_on' => dbDateTime( strtotime( 'now' ) )
                );

echo '<form method="post" action="user_sells_action.php">';
echo dbTableToHTMLTable( 'nilami_items', $default 
        , 'item_name,description,price,contact_info,comment,tags'
    );
echo '</form>';


echo goBackToPageLink( "user.php", "Go back" );

?>
