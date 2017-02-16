<?php

include_once 'check_access_permissions.php';
mustHaveAllOfTheseRoles( array( 'AWS_ADMIN' ) );

include_once 'database.php';
include_once 'tohtml.php';

echo userHTML( );


echo "<h3>You are updating an upcoming AWS </h3>";

if( $_POST[ 'response' ] == 'update' )
{
    $awsId = $_POST[ 'id' ];
    $aws = getUpcomingAWSById( $awsId );

    echo '<form method="post" action="admin_aws_update_upcoming_aws_submit.php">';
    echo editableAWSTable( -1, $aws );
    echo '<input type="hidden", name="id" value="' . $awsId . '">';
    echo '</form>';
}

echo goBackToPageLink( 'admin_aws.php', 'Go back' );

?>
