<?php 

include_once( "header.php" );
include_once( "methods.php" );
include_once( 'tohtml.php' );
include_once( "check_access_permissions.php" );

mustHaveAnyOfTheseRoles( Array( 'USER' ) );

echo userHTML( );

echo '<div style="border:1px dotted">';
$scheduledAWS = scheduledAWSInFuture( $_SESSION['user'] );
$tempScheduleAWS = temporaryAwsSchedule( $_SESSION[ 'user' ] );
if( $scheduledAWS )
{
    echo alertUser( "
        <font color=\"blue\">&#x2620 Your AWS has been scheduled on " . 
        humanReadableDate( $scheduledAWS[ 'date' ] ) . '</font>'
    );
    echo printWarning( "
        <x-small>
        This date is very unlikely to change without your approval.
        In rare events when we cancel the AWS altogether for this day (happens once 
        or twice a year), we'll schedule you in ASAP.
        </x-small>
        "
    );
}
else 
{
    if( $tempScheduleAWS )
    {
        echo printInfo( "<font color=\"blue\">&#x2620 Your AWS is most likely to be on " . 
            humanReadableDate( $tempScheduleAWS[ 'date' ] ) . "</font>" .
            ". Once confirmed, I will notify you immediately, and once more 
            at least 1 months in advance." );

        echo printWarning( 
            "This date may change a little if any other speaker's
            request to change their AWS is approved or new speakers are added. 
            Once approved, this date is very unlikely to change without your approval.
            " 
        );
    }
    else
        echo printInfo( "You don't have any AWS scheduled in next 12 months" );

    echo printInfo( 
        "If you are not happy with above tentative schedule, hurry up 
        and let me know your preferred dates. I will try my best to assign you on 
        or very near to these dates but I can not promise your requested slot.
        "
        );

    // Here user can submit preferences.
    $prefs = getTableEntry( 'aws_scheduling_request', 'login'
                    , array( 'login' => $_SESSION[ 'user' ] )
                    );
    
    if( ! $prefs )
    {
        echo '<form method="post" action="user_aws_scheduling_request.php">';
        echo '<button type="submit">Create preference</button>';
        echo '<input type="hidden" name="login" value="' . $_SESSION[ 'user' ] . '">';
        echo '</form>';
    }
    else
    {
        echo '<form method="post" action="user_aws_scheduling_request.php">';
        echo dbTableToHTMLTable( 'aws_scheduling_request', $prefs, '', 'edit' );
        echo '<input type="hidden" name="login" value="'. $_SESSION[ 'user' ] . '">';
        echo '</form>';
    }
    
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
