<?php

include_once './check_access_permissions.php';
mustHaveAllOfTheseRoles( array( 'USER' ) );

include_once 'database.php';
include_once 'tohtml.php';

if( $_POST[ 'response' ] == 'update' )
{
    $awsId = $_POST[ 'id' ];
    $aws = getUpcomingAWSById( $awsId );

    echo '<form method="post" action="user_aws_update_upcoming_aws_submit.php">';
    echo editableAWSTable( -1, $aws );
    echo '<input type="hidden", name="id" value="' . $awsId . '">';
    echo '</form>';

}

echo goBackToPageLink( 'user_aws.php', 'Go back' );

?>
