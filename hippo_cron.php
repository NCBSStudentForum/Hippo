<?php

include_once 'methods.php';
include_once 'database.php';
include_once 'tohtml.php';
include_once 'mail.php';

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
    // Send any time between 4pm and 4:15 pm.
    $awayFrom = strtotime( 'now' ) - strtotime( '4:00 pm' );
    if( $awayFrom > 0 && $awayFrom < 15 * 60 )
    {
        echo printInfo( "Today is Friday. Send out emails for AWS" );
        $nextMonday = dbDate( strtotime( 'next monday' ) );
        $upcomingAws = getUpcomingAWS( $nextMonday );
        $html = '';
        $subject = 'Next Week AWS (' . humanReadableDate( $nextMonday) . ') by ';

        $speakers = array( );
        $logins = array( );

        $outfile = getDataDir( ) . "AWS_" . $nextMonday . "_";
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
            , 'DATE' => humanReadableDate( $nextMonday ) 
            , 'TIME' => '4:00 PM'
            );

        $mail = emailFromTemplate( 'aws_template', $data );

        echo "Generating pdf";
        $script = __DIR__ . '/generate_pdf_aws.php';
        $cmd = "php -q -f $script date=$nextMonday";
        echo "Executing <pre> $cmd </pre>";
        ob_flush( );
        $res = `$cmd`;

        if( ! file_exists( $pdffile ) )
        {
            echo printWarning( "Could not generate PDF $pdffile." );
            echo $res;
            $pdffile = '';
        }

        // Cool. Now prepare mail.
        echo "Sending out email";

        $cclist = 'ins@ncbs.res.in,reception@ncbs.res.in';
        $cclist .= ',multimedia@ncbs.res.in,hospitality@ncbs.res.in';
        $to = 'academic@lists.ncbs.res.in';

        // Extra protection. If this email has been sent before, do not send it 
        // again.
        $maildir = getDataDir( ) . '/_mails';
        if( ! file_exists( $maildir ) )
            mkdir( $maildir, 0777, true );

        // generate md5 of email. And store it in archive.
        $archivefile = $maildir . '/' . md5($mail) . '.email';
        if( file_exists( $archivefile ) )
        {
            echo printInfo( "This email has already been sent. Doing nothing" );
        }
        else 
        {
            $res = sendPlainTextEmail( $mail, $subject, $to, $cclist, $pdffile );
            echo printInfo( "Saving the mail in archive" . $archivefile )
            file_put_contents( $archivefile, "SENT" );
        }
        ob_flush( );
    }
}

?>
