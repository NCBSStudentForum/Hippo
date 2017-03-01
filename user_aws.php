<?php 

include_once( "header.php" );
include_once( "methods.php" );
include_once( 'tohtml.php' );
include_once( "check_access_permissions.php" );

mustHaveAnyOfTheseRoles( Array( 'USER' ) );

echo userHTML( );

// Logic to add your preference.
if( array_key_exists( 'response', $_POST ) )
{
    $res = insertOrUpdateTable( 'my_aws_preference'
        , 'login,first_preference,second_preference'
        , 'first_preference,second_preference'
        , $_POST 
    );
}

echo '<div class="info">';
$scheduledAWS = scheduledAWSInFuture( $_SESSION['user'] );
$tempScheduleAWS = temporaryAwsSchedule( $_SESSION[ 'user' ] );
if( $scheduledAWS )
{
    echo alertUser( "
        <strong>&#x2620 Your AWS has been scheduled on " . 
        humanReadableDate( $scheduledAWS[ 'date' ] ) . '</strong>'
    );
}
else 
{
    if( $tempScheduleAWS )
    {
        echo printInfo( "&#x2620 Your AWS is most likely to be on " . 
            humanReadableDate( $tempScheduleAWS[ 'date' ] ) );

        echo printWarning( "This date is likely to change if any other speakers
            request to change their AWS or new speakers are added." 
        );
    }
    else
        echo printInfo( "You don't have any AWS scheduled in next 12 months" );

    echo '<h3>You can give two preferences for your upcoming AWS date </h3>';
    // Here user can submit preferences.
    $prefs = getTableEntry( 'my_aws_preference', 'login'
                    , array( 'login' => $_SESSION[ 'user' ] )
                    );

    if( $prefs )
        echo arrayToTableHTML( $prefs, 'info' );

    echo '<form method="post" action="">';
    echo dbTableToHTMLTable( 'my_aws_preference', $prefs 
        , 'first_preference,second_preference' );
    echo '<input type="hidden" name="login" value="'. $_SESSION[ 'user' ] . '">';
    echo '</form>';
}


echo '</div>';
if( $scheduledAWS )
{

    echo printInfo( 'Please fill-in details of your upcoming  AWS below.
         We will use this to generate the notification email
         and document. You can change it as many times as you like 
         <small> (Note: We will not store the old version).</small>
        ' );
    $id = $scheduledAWS[ 'id' ];
    echo "<form method=\"post\" action=\"user_aws_update_upcoming_aws.php\">";
    echo arrayToVerticalTableHTML( $scheduledAWS, 'aws', NULL
        , Array( 'speaker', 'id' ));
    echo "<button class=\"submit\" name=\"response\" 
        title=\"Update this entry\" value=\"update\">" 
            . $symbEdit . "</button>";
    echo "<input type=\"hidden\" name=\"id\" value=\"$id\" />";
    echo "</form>";
}

$awsRequests = getAwsRequestsByUser( $_SESSION['user'] );
if( count( $awsRequests ) > 0 )
    echo "<h3>Update pending requests</h3>";

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




echo goBackToPageLink( "user.php", "Go back" );
echo "<br />";

echo "<h3>Past Annual Work Seminar</h3>";

echo "<table>";
echo '<tr><td>
    If you notice a missing AWS in your list, please emails details to 
    <a href="mailto:hippo@lists.ncbs.res.in" target="_black">hippo@lists.ncbs.res.in</a>
    ';
echo "</td></tr>";
echo "</table>";
echo "<br/>";

$awses = getMyAws( $_SESSION['user'] );

foreach( $awses as $aws )
{
    $id = $aws['id'];
    echo "<div>";
    // One can submit an edit request to AWS.
    echo "<form method=\"post\" action=\"user_aws_edit_request.php\">";
    echo arrayToVerticalTableHTML( $aws, 'aws', NULL
    , Array( 'speaker', 'id' ));
    echo "<button title=\"Edit this entry\" 
            name=\"response\" value=\"edit\">" . $symbEdit . "</button>";
    echo "<input type=\"hidden\" name=\"id\" value=\"$id\" />";
    echo "</form>";
    echo "<br /><br />";
    echo "</div>";
    echo awsPdfURL( $aws['speaker' ], $aws[ 'date' ] );
}

echo goBackToPageLink( "user.php", "Go back" );

?>
