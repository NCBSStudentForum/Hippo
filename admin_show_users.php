<?php

include_once 'header.php';
include_once 'tohtml.php' ;
include_once 'database.php';
include_once 'check_access_permissions.php';

mustHaveAllOfTheseRoles( array( 'ADMIN' ) );

$logins = getLogins( );

echo '<table border="0">';
$i = 0;
foreach( $logins as $login )
{
    $i += 1;
    echo '<tr>';
    echo "<td>$i </td>";
    echo "<td>";
    echo arrayToTableHTML( $login, 'info', ''
        , array( 'alternative_email', 'email', 'roles', 'created_on', 'last_login' )
    );
    echo "</td>";
    echo '</tr>';
}

echo '</table>';

echo goBackToPageLink( "admin.php", "Go back" );

?>
