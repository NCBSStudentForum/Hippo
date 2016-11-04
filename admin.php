<?php

include_once( 'validate_privileges.php' );
include_once( 'methods.php' );
include_once( 'tohtml.php' );

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

