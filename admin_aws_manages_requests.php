<?php

include_once 'header.php';
include_once 'database.php';

echo "<h3>Manage pending requests</h3>";

$pendingRequests = getPendingAWSRequests( );
foreach( $pendingRequests as $req )
    echo arrayToTableHTML( $req, 'aws' );


?>
