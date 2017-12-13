<?php

include_once 'header.php';
include_once 'database.php';
include_once 'methods.php';
include_once "check_access_permissions.php";
include_once 'tohtml.php';
include_once 'mail.php';

mustHaveAllOfTheseRoles( array( 'AWS_ADMIN' ) );


if( $_POST['response'] == "Reschedule" )
{
    rescheduleAWS( );
    goToPage( 'admin_acad_manages_upcoming_aws.php', 1);
    exit;
}

else if( $_POST[ 'response' ] == 'Accept' || $_POST[ 'response' ] == 'Assign' )
{
    $speaker = $_POST[ 'speaker' ];
    $date = $_POST[ 'date' ];
    echo printInfo( "Assigning $speaker to $date" );
    $res = acceptScheduleOfAWS( $speaker, $date );

    if( $res )
    {
        echo printInfo( "Successfully assigned" );

        // When accepting the computed schedule, we don't want to run the
        // rescheduling algo.
        if( $_POST[ 'response' ] == 'Assign' )
            rescheduleAWS( );

        // Send email to user.
        $res = notifyUserAboutUpcomingAWS( $_POST[ 'speaker' ], $_POST[ 'date' ] );
        if( $res )
        {
            goToPage( "admin_acad_manages_upcoming_aws.php", 1 );
            exit;
        }
        else
        {
            printInfo( "Failed to send email to user" );
        }
    }
}
else if( $_POST[ 'response' ] == 'format_abstract' )
{
    // Update the user entry
    echo printInfo( "Admin is allowed to reformat the entry. That is why only
        abstract can be modified here" );

    $aws = getTableEntry( 'upcoming_aws', "speaker,date", $_POST );
    if( ! $aws )
        echo alertUser( "Nothing to update" );
    else
    {
        echo '<form method="post" action="admin_acad_manages_upcoming_aws_reformat.php">';
        echo dbTableToHTMLTable( 'upcoming_aws', $aws, 'abstract' );
        echo '</form>';
    }
}
else if( $_POST[ 'response' ] == 'RemoveSpeaker' )
{
    $data = array( 'eligible_for_aws' => 'NO', 'login' => $_POST[ 'speaker' ] );
    $res = updateTable( 'logins', 'login', 'eligible_for_aws', $data );
    if( $res )
    {
        echo printInfo(
            "Successfully removed user from AWS list.
            Recomputing schedule ... "
            );
        ob_flush( );
        // Send email to speaker.
        $subject = "Your name has been removed from AWS list";
        $msg = "<p>Dear " . loginToText( $_POST[ 'speaker' ] ) . " </p>";
        $msg .= "<p>
            Your name has been removed from the list of potential AWS
            speaker. If this is a mistake, please write to Academic Office.
            </p>";

        $to = getLoginEmail( $_POST[ 'speaker' ] );
        sendHTMLEmail( $msg, $subject, $to, 'hippo@lists.ncbs.res.in' );

        // And reschedule AWS entry.
        rescheduleAWS( );
        goToPage( "admin_acad_manages_upcoming_aws.php", 1 );
        exit;
    }
}

else if( $_POST[ 'response' ] == 'delete' )
{
    $res = clearUpcomingAWS( $_POST[ 'speaker'], $_POST[ 'date' ] );
    if( $res )
    {
        rescheduleAWS( );
        echo printInfo( "Successfully cleared upcoming AWS" );

        $admin = $_SESSION[ 'user' ];

        // Notify the hippo list.
        $msg = "<p>Hello " . loginToHTML( $_POST[ 'speaker' ] ) . "</p>";
        $msg .= "<p>
            Your upcoming AWS schedule has been removed by Hippo admin ($admin).
             If this is a  mistake, please write to acadoffice@ncbs.res.in
            as soon as possible.
            </p>
            <p> The AWS schedule which is removed is the following </p>
            ";

        $data = array( );
        $data[ 'id' ] = $_POST[ 'id' ];
        $data[ 'speaker' ] = $_POST[ 'speaker' ];
        $data[ 'date' ] = $_POST[ 'date' ];

        $msg .= arrayToVerticalTableHTML( $data, 'info' );

        sendHTMLEmail( $msg
            , "Your AWS schedule has been removed from upcoming AWS list"
            , $to = getLoginEmail( $_POST[ 'speaker' ] )
            , $cclist = "acadoffice@ncbs.res.in,hippo@lists.ncbs.res.in"
            );
        goToPage( "admin_acad_manages_upcoming_aws.php", 1 );
        exit;
    }
}
else if( $_POST[ 'response' ] == "DO_NOTHING" )
{
    echo printInfo( "User cancelled the previous action" );
    goBack( );
    exit;
}
else
{
    echo printWarning( "To Do " . $_POST[ 'response' ] );
}

echo goBackToPageLink( "admin_acad_manages_upcoming_aws.php", "Go back" );

?>
