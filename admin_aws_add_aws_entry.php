<?php

include_once 'check_access_permissions.php';
include_once 'database.php';
include_once 'tohtml.php';

mustHaveAllOfTheseRoles( array( 'ADMIN', 'AWS_ADMIN' ) );

echo userHTML( );

echo '<h3>Add new AWS entry</h3>';

// We are using a generic function to create table. We need to add user name as  
// well.

echo '<form method="post" action=""> ';
echo editableAWSTable( );
echo '</form>';

echo goBackToPageLink( 'aws_admin.php', 'Go back' );
exit(0);

?>
