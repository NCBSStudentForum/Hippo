<?php 

include_once( "header.php" );
include_once( "methods.php" );
include_once( 'tohtml.php' );
include_once( "check_access_permissions.php" );

mustHaveAnyOfTheseRoles( Array( 'USER' ) );

echo userHTML( );


echo '<div class="info">';
$scheduledAWS = scheduledAWSInFuture( $_SESSION['user'] );
$tempScheduleAWS = temporaryAwsSchedule( $_SESSION[ 'user' ] );
if( $scheduledAWS )
{
    echo alertUser( "&#x2620 Your AWS has been scheduled on " . 
        humanReadableDate( $scheduledAWS[ 'date' ] )
    );
}
else if( $tempScheduleAWS )
{
    echo printInfo( "&#x2620 Your AWS is most likely to be on " . 
        humanReadableDate( $tempScheduleAWS[ 'date' ] ) );

    echo printWarning( "This date is likely to change if any other speakers
        request to change their AWS or new speakers are added." 
    );
}
else
{
    echo printInfo( "You don't have any AWS scheduled in next 12 months" );
}
echo '</div>';

echo "<h3>Manage upcoming Annual Work Seminar (AWS)</h3>";
echo "TODO";
echo "<p>User should add/update his/her upcoming AWS here </p>";

echo "<h3>Update pending requests</h3>";

$awsRequests = getAwsRequestsByUser( $_SESSION['user'] );
foreach( $awsRequests as $awsr )
{
    $id = $awsr['id'];
    echo "<form method=\"post\" action=\"user_aws_request.php\">";
    echo arrayToVerticalTableHTML( $awsr, 'aws' );
    echo "<button name=\"response\" value=\"edit\">Edit</button>";
    echo "<button name=\"response\" value=\"cancel\">Cancel</button>";
    echo "<input type=\"hidden\" name=\"id\" value=\"$id\" />";
    echo "</form>";
}


echo "<h3>Create a request</h3>";
echo "<table class=\"show_user\">";
echo '<tr><td>
    You can add a missing AWS here for review.
    </td><td> <a href="user_aws_request.php">Add previous 
    AWS</a>';
echo "</td></tr>";
echo '<tr>
    <td>Edit previous AWS. Something is wrong in my previous AWS entries.</td>
    <td>Just scroll down to the list of previous AWSes and press Edit button.</td>
    
</tr>';
echo "</table>";


echo goBackToPageLink( "user.php", "Go back" );
echo "<br />";

echo "<h3>Past Annual Work Seminar</h3>";

$awses = getMyAws( $_SESSION['user'] );

foreach( $awses as $aws )
{
    $id = $aws['id'];
    echo "<div>";
    // One can submit an edit request to AWS.
    echo "<form method=\"post\" action=\"user_aws_edit_request.php\">";
    echo arrayToVerticalTableHTML( $aws, 'aws', NULL
    , Array( 'speaker', 'id' ));
    echo "<button class=\"submit\" name=\"response\" value=\"edit\">Edit</button>";
    echo "<input type=\"hidden\" name=\"id\" value=\"$id\" />";
    echo "</form>";
    echo "<br /><br />";
    echo "</div>";
}

echo goBackToPageLink( "user.php", "Go back" );

?>
