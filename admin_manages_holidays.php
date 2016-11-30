<?php

include_once 'database.php';
include_once 'tohtml.php';
include_once 'check_access_permissions.php';

mustHaveAllOfTheseRoles( Array( 'ADMIN' ) );


echo userHTML( );

echo goBackToPageLink( "admin.php", "Go back" );

?>
