<?php

include_once 'header.php';
include_once 'database.php';
include_once 'tohtml.php';
include_once 'check_access_permissions.php';

mustHaveAllOfTheseRoles( array( 'USER' ) );

echo '<h3>Submit a request for AWS</h3>';



?>
