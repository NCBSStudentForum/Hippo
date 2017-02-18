<?php

include_once 'header.php';
include_once 'database.php';
include_once 'methods.php';
include_once "check_access_permissions.php";
include_once 'tohtml.php';
include_once 'mail.php';

mustHaveAllOfTheseRoles( array( 'AWS_ADMIN' ) );

function notifyUserAboutUpcomingAWS( $speaker, $date )
{
    // Now insert a entry into email database.
    $msg = getEmailTemplateById( 'aws_confirmed_notify_speaker' )[ 'description'];
    // Replace text in the template.
    $msg = str_replace( '%SPEAKER%', loginToText( $speaker ), $msg); 
    $msg = str_replace( '%DATE%', humanReadableDate( $date ), $msg ); 
    $to = getLoginEmail( $speaker ) . ',' . 'hippo@lists.ncbs.res.in';
    return sendEmail( $to, 'ATTN! Your AWS date has been fixed', $msg );
}

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


else if( $_POST[ 'response' ] == 'delete' )
{
    $res = clearUpcomingAWS( $_POST[ 'speaker'], $_POST[ 'date' ] );
    if( $res )
    {
        rescheduleAWS( );
        echo printInfo( "Successfully cleared upcoming AWS" );
        goToPage( "admin_acad_manages_upcoming_aws.php", 2 );
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

