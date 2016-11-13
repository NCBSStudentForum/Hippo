<?php 

include_once( "header.php" );
include_once( "methods.php" );
include_once( 'tohtml.php' );
include_once( "check_access_permissions.php" );

mustHaveAnyOfTheseRoles( Array( 'USER' ) );

echo userHTML( );

echo "<h3>Manage upcoming Annual Work Seminar (AWS)</h3>";

$upcomingAWS = scheduledAWSInFuture( $_SESSION['user'] );
if( ! $upcomingAWS )
{
    echo printInfo( "No AWS has been scheduled for you yet!" );
}
else {
    echo printInfo( "You have AWS tentatively scheduled on : " . 
        humanReadableDate( $upcomingAWS['tentatively_scheduled_on'] ) 
    );
    echo "TODO: Ask for postpone/swap with others?";
}


echo "<h3>Update pending requests</h3>";

$awsRequests = getAwsRequestsByUser( $_SESSION['user'] );
foreach( $awsRequests as $awsr )
{
    $id = $awsr['id'];
    echo "<form method=\"post\" action=\"user_aws_request.php\">";
    echo arrayToTableHTML( $awsr, 'aws' );
    echo "<button name=\"response\" value=\"edit\">Edit</button>";
    echo "<button name=\"response\" value=\"cancel\">Cancel</button>";
    echo "<input type=\"hidden\" name=\"id\" value=\"$id\" />";
    echo "</form>";
}


echo "<h3>Create a request</h3>";
echo "<table class=\"show_user\">";
echo '<tr><td>
    You can add a missing AWS here for review.
    </td><td> <a href="' . appRootDir() .  '/user_aws_request.php">Add previous 
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
    echo "<form method=\"post\" action=\"user_aws_request.php\">";
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
