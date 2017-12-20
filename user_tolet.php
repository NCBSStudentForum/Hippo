<!--
<script src="http://maps.googleapis.com/maps/api/js?libraries=places" type="text/javascript">
</script>

<script type="text/javascript">
    function initialize() {
        var input = document.getElementById('apartments_address');
        var autocomplete = new google.maps.places.Autocomplete(input);
        google.maps.event.addListener(autocomplete, 'place_changed', function () {
            var place = autocomplete.getPlace();
            document.getElementById('city2').value = place.name;
            document.getElementById('cityLat').value = place.geometry.location.lat();
            document.getElementById('cityLng').value = place.geometry.location.lng();
            alert("This function is working!");
            alert(place.name);
            alert(place.address_components[0].long_name);

        });
    }
    google.maps.event.addDomListener(window, 'load', initialize);
</script>
-->

<?php

include_once( "header.php" );
include_once( "methods.php" );
include_once( "database.php" );
include_once 'tohtml.php';
include_once 'check_access_permissions.php';

mustHaveAnyOfTheseRoles( array( 'USER' ) );

echo userHTML( );
$user = whoAmI( );

// All alerts.
$allAlerts = getTableEntries( 'alerts' );
$count = array( );
foreach( $allAlerts as $alert )
    $count[ $alert[ 'value' ] ] = 1 + __get__( $count, $alert['value'], 0 );

$apartmentTypes = array( 'SHARE', '1BHK', '2BHK', '3BHK', 'STUDIO', 'PALACE' );
$apartmentSelect = arrayToSelectList( 'value', $apartmentTypes );


// Create alerts.
echo " <h2>My Email Alerts</h2> ";
echo printInfo( "You will recieve email whenever following types of listings are added by others." );

$where = "login='$user' AND on_table='apartments' AND  on_field='type'";
$myAlerts = getTableEntries( 'alerts', 'login', $where );

echo '<table><tr>';
if( count( $myAlerts ) > 0 )
{
    echo '<td>';
    foreach( $myAlerts as $alert )
    {
        echo ' <form action="user_tolet_action.php" method="post" > ';
        echo dbTableToHTMLTable( 'alerts', $alert, '', 'Delete Alert', 'on_field,on_table' );
        echo '</form>';
    }
    echo '</td>';
}
echo '</tr></table>';

echo '<h3>Create new alerts</h3>';
// New alert.
echo ' <form action="user_tolet_action.php" method="post" > ';
echo '<table> <tr> ';
echo '<td>Apartment Type : ' . $apartmentSelect . '</td>';
echo ' <input type="hidden" name="login" value="' . $user . '" />';
echo ' <input type="hidden" name="on_table" value="apartments" />';
echo ' <input type="hidden" name="on_field" value="type" />';
echo '<td> <button name="response" value="New Alert">Add Alert </button> </td>';
echo ' </tr></table>';
echo '</form>';


// Show all apartments.
echo ' <h2>My TO-LET listing </h2> ';

$action = 'Add new listing';

$myApartments = getTableEntries( 'apartments', 'type'
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
            <button name="response" value="Update" > ' . $symbEdit . '
        </button> </td> ';
    echo '<input type="hidden" name="id" value="' . $apt[ 'id' ] . '" />';
    echo ' </form> ';
    echo '</tr>';
}

echo '</table>';
echo '</div>';


$default = array(  'created_by' => $_SESSION[ 'user' ]
        , 'created_on' => dbDateTime( 'now' )
        , 'open_vacancies' => 1
    );

// If edit button is pressed.
if( 'Update' == __get__( $_POST, 'response', '' ) )
{
    $default = getTableEntry( 'apartments', 'id', $_POST );
    $default[ 'last_modified_on' ] = dbDateTime( 'now' );
    $action = 'Update listing';
}

// Fill a new form.
echo '<div style="font-size:small;">';
echo ' <form method="post" action="user_tolet_action.php">';

// Create an editable entry.
$editable = 'type,open_vacancies,address,description,owner_contact,rent,advance';
if( 'Update listing' == $action )
    $editable .= ',status';

echo " <h2>$action</h2> ";

echo dbTableToHTMLTable( 'apartments', $default , $editable , $action);
echo '</form>';
echo '</div>';

echo goBackToPageLink( "user.php", "Go back" );

?>
