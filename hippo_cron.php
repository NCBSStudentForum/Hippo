<?php

include_once 'methods.php';
include_once 'database.php';
include_once 'tohtml.php';

ini_set( 'date.timezone', 'Asia/Kolkata' );
ini_set( 'log_errors', 1 );
ini_set( 'error_log', '/var/log/hippo.log' );

$now = dbDateTime( strtotime( "now" ) );
error_log( "Running cron at $now" );
echo( "Running cron at $now" );

/*
 * Task 0. Get list of today's public events 
 */
$today = dbDate( strtotime( 'today' ) );
$events = getPublicEventsOnThisDay( $today );
if( count( $events ) < 1 )
{
    echo printInfo( "No event found for today" );
}
else
{
    echo printInfo( "TODO: Prepare emails" );
    var_dump ( $events );
}

/*
 * Task 1. If today is Friday. Then prepare a list of upcoming AWS and send out 
 * and email at 4pm.
 */
if( $today = dbDate( strtotime( 'this friday' ) ) )
{
    echo printInfo( "Today is Friday. Send out emails for AWS" );
    $nextMonday = dbDate( strtotime( 'next monday' ) );
    $upcomingAws = getUpcomingAWS( $nextMonday );
    $html = '';
    $subject = 'Next Week AWS (' . humanReadableDate( $nextMonday) . ') by ';

    $speakers = array( );
    $logins = array( );

    $outfile = getDataDir( ) . "AWS_$nextMonday_";
    foreach( $upcomingAws as $aws )
    {
        $html .= awsToHTML( $aws );
        array_push( $logins, $aws[ 'speaker' ] );
        array_push( $speakers, __ucwords__( loginToText( $aws['speaker'], false ) ) );
    }
    $outfile .= implode( "_", $logins );  // Finished generating the pdf file.
    $pdffile = $outfile . ".pdf";

    $subject .= implode( ', ', $speakers );
    $data = array( 'EMAIL_BODY' => $html
        , 'DATE' => humanReadableTime( $nextMonday ) 
        );

    $mail = emailFromTemplate( 'aws_template', $data );
    $textMail = html2Markdown( $mail );

    echo "Generating pdf";
    $script = __DIR__ . '/generate_pdf_aws.php';
    $cmd = "php -q -f $script date=$nextMonday";
    echo "Executing <pre> $cmd </pre>";
    ob_flush( );
    $res = `$cmd`;

    if( file_exists( $pdffile ) )
    {
        echo "TODO: Attach pdf in email";
    }
    else
    {
        echo printWarning( "Could not generate PDF file. No attachment." );
        echo $res;
    }
    ob_flush( );

    // Cool. Now prepare mail.
    sendMail( $textMail, $subject, 'hippo@lists.ncbs.res.in', $pdfFile );
}


?>
