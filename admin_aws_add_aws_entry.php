<?php

include_once 'check_access_permissions.php';
include_once 'database.php';
include_once 'tohtml.php';

mustHaveAllOfTheseRoles( array( 'ADMIN', 'AWS_ADMIN' ) );

echo userHTML( );

echo '<h3>Add new AWS entry</h3>';

?>
