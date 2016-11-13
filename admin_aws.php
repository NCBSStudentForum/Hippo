<?php

include_once 'header.php';
include_once 'tohtml.php';
include_once 'methods.php';
include_once 'database.php';
include_once 'check_access_permissions.php';

mustHaveAllOfTheseRoles( array( 'AWS_ADMIN' ) );

echo userHTML( );

echo "<h3>AWS Admin</h3>";

echo '<table border="0" class="admin">
    <tr>
        <td>AWS summary</td>
        <td><a href="admin_aws_summary.php">See AWS summary</a></td>
    </tr>
    <tr>
        <td>Manage upcoming AWS</td>
        <td><a href="admin_aws_manages_upcoming_aws.php">Manage upcoming AWSes</a></td>
    </tr>
    <tr>
    <td>Manage pending requests</td>
    <td> <a href="admin_aws_manages_requests.php">Manage pending requests</a> </td>
    </tr>
    
</table>';


?>
