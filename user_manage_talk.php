<?php

include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'USER' ) );

include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );

// Logic for POST requests.
$speaker = array( 
    'first_name' => '', 'middle_name' => '', 'last_name' => '', 'email' => ''
    , 'department' => '', 'institute' => '', 'title' => '', 'id' => ''
    , 'homepage' => ''
    );

$whereExpr = "created_by='" . $_SESSION[ 'user' ] . "'";
$whereExpr .= "AND status!='INVALID'";
$talks = getTableEntries( 'talks', '', $whereExpr );
if( count( $talks ) < 1 )
{
    echo printInfo( "You don't have any upcoming or unscheduled talk" );
}

foreach( $talks as $t )
{
    //echo dbTableToHTMLTable( 'talks', $t );
    echo '<form method="post" action="user_manage_talks_action.php">';
    echo '<table border="0">';
    echo '<tr>';
    echo arrayToTableHTML( $t, 'info', '', 'created_by');
    echo '</tr><tr>';
    echo '
        <input type="hidden" name="id" value="' . $t[ 'id' ] . '" />
        <td><button onclick="AreYouSure(this)" name="response" >Delete</button></td>
        <td><button style="float:right" name="response" value="edit">Edit</button></td>
        ';
    echo '</tr></table>';
    echo '</form>';
}
    
echo goBackToPageLink( "user.php", "Go back" );

?>
