<?php

include_once 'header.php';
include_once 'check_access_permissions.php';
include_once 'database.php';
include_once 'tohtml.php';
include_once 'mail.php';

mustHaveAllOfTheseRoles( array( "AWS_ADMIN" ) );

echo userHTML( );

//var_dump( $_POST );

// Start preparing email.
$speaker = $_POST[ 'speaker' ];
$speakerInfo = getUserInfo( $speaker );
$user = loginToText( $speaker );


$msg = '<p>Dear ' . $user . ' </p>';

if( $_POST[ 'response' ] == 'Reject' )
{
    if( strlen( $_POST[ 'reason' ]) < 8 )
    {
        echo printWarning( "
            Empty reason or reason too short (less than 8 characters).
            A request can not rejected without a proper reason.
            You must enter a valid reason."
        );
        echo goBackToPageLink( "admin_acad_manages_scheduling_request.php", "Go back" );
        exit;
    }

    $rid = $_POST[ 'id' ];

    $res = updateTable( 
        'aws_scheduling_request', 'id' , 'status'
        , array( 'id' => $rid, 'status' => 'REJECTED' )
    );

    if( $res )
    {
        echo printInfo( "This request has been rejected" );
        // Now notify user.
        $msg .= "<p>Your preference for AWS dates has been rejected </p>";
        $msg .= "<p>Reason: " . $_POST[ 'reason' ] . "</p>";
        $msg .= "<p>Feel free to drop an email to hippo@lists.ncbs.res.in for
            further clarification. Please mention your request id in email.
            </p>";

        // Get the latest request.
        
        $req = getTableEntry( 
            'aws_scheduling_request', 'id', array( 'id' => $rid )
            );
        $msg .= arrayToVerticalTableHTML( $req, "request" );

        sendEmail( $msg
            , "Your preference for AWS dates (id:". $rid . ") has been rejected"
            , $speakerInfo[ 'email' ]
        );

        goToPage( "admin_acad_manages_scheduling_request.php", 1 );
        exit;
    }
}
elseif( $_POST['response'] == 'Accept' )
{
    $rid = $_POST[ 'id' ];
    $req = getTableEntry( 'aws_scheduling_request', 'id', array( 'id' => $rid ));
    $req['status'] = 'APPROVED';

    $res = updateTable( 'aws_scheduling_request', 'id', 'status', $req );
    if( $res )
    {
        // Now recompute the schedule.
        rescheduleAWS( );

        $user = loginToText( $speaker );
        $msg .= "<p>
            Your edit to your AWS entry has been approved. 
            The updated entry is following:
            </p>";

        // Get the latest request.
        $req = getTableEntry(
                'aws_scheduling_request', 'id', array( 'id' => $rid )
            );
        $msg .= arrayToVerticalTableHTML( $req, "request" );

        sendEmail( $msg
                , "Your AWS preference dates (id:$rid) have been approved"
                , $speakerInfo['email' ]
            );
        
        echo goToPage( 'admin_acad_manages_scheduling_request.php', 1 );
        exit;
    }
    else
        echo printWarning( "Could not update the AWS table" );
}
else
{
    echo printWarning( "Unknown request " . $_POST[ 'response' ] );
}

echo goBackToPageLink( "admin_acad_manages_scheduling_request.php", "Go back" );

?>