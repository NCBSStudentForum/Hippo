<?php

include_once 'header.php';
include_once 'tohtml.php';
include_once( 'validate_privileges.php' );
include_once( 'methods.php' );
//include_once( 'authenticate_gcalendar.php' );
$_SESSION['validate_calendar'] = FALSE;
$_SESSION['token_set'] = FALSE;

if( ! array_key_exists( 'gcal_token', $_SESSION ) )
    include_once( 'oauthcallback.php' );

echo userHTML( );

if( ! requiredPrivilege( 'ADMIN' ) )
{
    echo printWarning( "You are not listed as ADMIN" );
    goToPage( "index.php" );
    exit( 0 );
}


echo "<h3>Hello admin</h3>";

echo "<form method=\"post\" action=\"admin_action.php\">";
echo "<table>";
echo "<tr><td> <button name=\"response\" value=\"add_all_events\">
    Add all public events to calendar</button></td>";
echo "</tr>";
echo "</table>";
echo "</form>";

?>

