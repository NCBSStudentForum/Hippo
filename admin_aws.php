<?php

include_once 'header.php';
include_once 'tohtml.php';
include_once 'methods.php';
include_once 'database.php';
include_once 'check_access_permissions.php';

mustHaveAllOfTheseRoles( array( 'AWS_ADMIN' ) );

echo userHTML( );

echo "<h3>Manage pending requests</h3>";

$pendingRequests = getPendingAWSRequests( );
foreach( $pendingRequests as $req )
    echo arrayToTableHTML( $req, 'aws' );

?>
