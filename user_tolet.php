<?php 

include_once( "header.php" );
include_once( "methods.php" );
include_once( "database.php" );
include_once 'tohtml.php';
include_once 'check_access_permissions.php';

mustHaveAnyOfTheseRoles( array( 'USER' ) );

echo userHTML( );
$user = $_SESSION[ 'user' ];


// All alerts.
$allAlerts = getTableEntries( 'alerts' );

$count = array( );
foreach( $allAlerts as $alert )
    $count[ $alert[ 'value' ] ] = 1 + __get__( $count, $alert['value'], 0 );

echo '<strong>Total alerts for <tt>TO-LET</tt> </strong>';
echo ' <table border="1"> <tr> ';
foreach( $count as $val => $num )
    echo " <td> <tt>$val</tt>  $num</td> ";
echo '</tr> </table>';

$apartmentTypes = array( 'SHARE', '1BHK', '2BHK', '3BHK', 'STUDIO', 'PALACE' );
$apartmentSelect = arrayToSelectList( 'value', $apartmentTypes );


// Create alerts.
echo ' <form action="user_tolet_action.php" method="post" > ';
echo '<table> <tr> ';
echo '<td> Add new alert: ' . $apartmentSelect . '</td>';
echo ' <input type="hidden" name="login" value="' . $user . '" />';
echo ' <input type="hidden" name="on_table" value="apartments" />';
echo ' <input type="hidden" name="on_field" value="type" />';
echo '<td> <button name="response" value="New Alert">Add new alert </button> </td>';
echo ' </tr></table>';
echo '</form>';

$where = "login='$user' AND on_table='apartments' AND  on_field='type'";
$myAlerts = getTableEntries( 'alerts', 'login', $where );

if( count( $myAlerts ) > 0 )
{
    echo " <h2>My alerts</h2> ";
    echo ' <table border="1"> ';
    echo '<tr>';
    foreach( $myAlerts as $alert )
    {
        echo ' <form action="user_tolet_action.php" method="post" > ';
        echo '<td> ';
        echo $alert[ 'value' ];
        echo '<button name="response" 
            title="Delete this alert" 
            value="Delete">'. $symbDelete .'</button>';
        echo '</td>';
        echo '</form>';
    }
    echo '</tr></table>';
}

// Show all apartments.
echo ' <h2>My TO-LET listing </h2> ';
$action = 'Add New';

$myApartments = getTableEntries( 'apartments', 'created_on DESC'
            , "status='AVAILABLE' AND created_by='$user' " 
        );

echo '<div style="font-size:small;">';
echo '<table border="0">';
foreach( $myApartments as $apt )
{
    echo '<tr>';
    echo ' <form method="post" action=""> ';
    echo '<td>' . arrayToTableHTML( $apt, 'info', ''
                    , 'status,last_modified_on,created_by'  ) . '</td>';
    echo ' <td>
            <button name="response" value="Edit Entry" > ' . $symbEdit . '
        </button> </td> ';
    echo '<input type="hidden" name="id" value="' . $apt[ 'id' ] . '" />';
    echo ' </form> ';
    echo '</tr>';
}
    
echo '</table>';
echo '</div>';


$default = array(  'created_by' => $_SESSION[ 'user' ]
        , 'created_on' => dbDateTime( 'now' )
    );

// If edit button is pressed.
if( 'Edit Entry' == __get__( $_POST, 'response', '' ) )
{
    $default = getTableEntry( 'apartments', 'id', $_POST );
    $default[ 'last_modified_on' ] = dbDateTime( 'now' );
    $action = 'Update Entry';
}

// Fill a new form.
echo '<div style="font-size:small;">';
echo ' <form method="post" action="user_tolet_action.php">';
echo dbTableToHTMLTable( 'apartments', $default 
        , 'title,type,address,description,owner_contact,rent,advance'
        , $action
        );
echo '</form>';
echo '</div>';

echo goBackToPageLink( "user.php", "Go back" );

?>
