<?php

include_once './check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'USER' ) );

include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );

echo printInfo( '
    Here you can create an entry for talk. After creating a talk, you may 
    have to book a venue for it.' );

?>
