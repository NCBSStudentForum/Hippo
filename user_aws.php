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
        In rare case if we cancel the AWS altogether for this day (happens once 
        or twice a year), we'll schedule you to nearest possible available slot.
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
            ".<br>Once confirmed, I will notify you immediately, 
            and once more, at least 28 days in advance." );

        echo printWarning( 
            "This date may change a little if any other speaker's
            request to change their AWS is approved or new speakers are added. 
            After approval, this date is very unlikely to change without your consent.
            " 
        );
    }
    else
        echo printInfo( 
            "You don't have any AWS scheduled in next 12 months. This happens if 
            you already given 3 AWS or more. If this is not the case, you should 
            write to academic office.
        " );


    // Here user can submit preferences.
    $prefs = getTableEntry( 'aws_scheduling_request', 'speaker,status'
                , array( 'speaker' => $_SESSION[ 'user' ]
                    , 'status' => 'PENDING' ) );

    $approved  = getTableEntry( 'aws_scheduling_request', 'speaker,status'
                , array( 'speaker' => $_SESSION[ 'user' ]
                    , 'status' => 'APPROVED' ) );
    
    if( ! $prefs )
    {
        echo printInfo( 
            "If you can not make it on this date, hurry up 
            and let me know your preferred dates. I will try my best to assign you on 
            or very near to these dates. Make sure you are available near these dates 
            as well (+/- 2 weeks).
            "
            );

        echo '<form method="post" action="user_aws_scheduling_request.php">';
        echo '<button type="submit">Create preference</button>';
        echo '<input type="hidden" name="speaker" value="' . $_SESSION[ 'user' ] . '">';
        echo '</form>';
    }
    else if( $prefs[ 'status' ] == 'PENDING' )
    {
        echo printInfo( "You preference for AWS schedule is pending. If you have 
            changed your mind, cancel it. After approval, you wont be able to modify 
            this request. We usually wait for two days before approving.
            " );

        echo '<form method="post" action="user_aws_scheduling_request.php">';
        echo dbTableToHTMLTable( 'aws_scheduling_request', $prefs, '', 'edit' );
        echo '<input type="hidden" name="created_on" 
                value="' . dbDateTime( 'now' ) . '" >';
        echo '</form>';
        // Cancel goes directly to cancelling the request. Only non-approved 
        // requests can be cancelled.
        echo '<form method="post" action="user_aws_scheduling_request_submit.php">';
        echo '<button onclick="AreYouSure(this)" 
                name="response" title="Cancel this request" 
                type="submit">' . $symbCancel . '</button>';
        echo '<input type="hidden" name="id" value="'. $prefs[ 'id' ].'">';
        echo '</form>';
    } 

    if( $approved )
    {
        echo '<strong>You already have a request below.
            Notice that request is only effective when its <tt>STATUS</tt> has 
            changed to <tt>APPROVED</tt>. </strong>';
        echo arrayToTableHTML( $approved, 'info' );
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
