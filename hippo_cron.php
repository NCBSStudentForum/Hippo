<?php

include_once 'methods.php';
include_once 'database.php';
include_once 'tohtml.php';
include_once 'mail.php';

ini_set( 'date.timezone', 'Asia/Kolkata' );
ini_set( 'log_errors', 1 );
ini_set( 'error_log', '/var/log/hippo.log' );


// Directory to store the mdsum of sent emails.
$maildir = getDataDir( ) . '/_mails';
if( ! file_exists( $maildir ) )
    mkdir( $maildir, 0777, true );

$now = dbDateTime( strtotime( "now" ) );
echo( "Running cron at $now" );

function generateAWSEmail( $monday )
{

    $result = array( );

    $upcomingAws = getUpcomingAWS( $monday );
    if( count( $upcomingAws ) < 1 )
        return null;

    $html = '';

    $speakers = array( );
    $logins = array( );
    $outfile = getDataDir( ) . "AWS_" . $monday . "_";

    foreach( $upcomingAws as $aws )
    {
        $html .= awsToHTML( $aws );
        array_push( $logins, $aws[ 'speaker' ] );
        array_push( $speakers, __ucwords__( loginToText( $aws['speaker'], false ) ) );
    }

    $outfile .= implode( "_", $logins );  // Finished generating the pdf file.
    $pdffile = $outfile . ".pdf";
    $res[ 'speakers' ] = $speakers;

    $data = array( 'EMAIL_BODY' => $html
        , 'DATE' => humanReadableDate( $monday ) 
        , 'TIME' => '4:00 PM'
    );

    $mail = emailFromTemplate( 'aws_template', $data );

    echo "Generating pdf";
    $script = __DIR__ . '/generate_pdf_aws.php';
    $cmd = "php -q -f $script date=$monday";
    echo "Executing <pre> $cmd </pre>";
    ob_flush( );

    $ret = `$cmd`;

    if( ! file_exists( $pdffile ) )
    {
        echo printWarning( "Could not generate PDF $pdffile." );
        echo $res;
        $pdffile = '';
    }

    $res[ 'pdffile' ] = $pdffile;
    $res[ 'email' ] = $mail;
    return $res;
}


/*
 * Task 1. If today is Friday. Then prepare a list of upcoming AWS and send out 
 * and email at 4pm.
 */
$today = dbDate( strtotime( 'today' ) );
echo printInfo( "Today is $today" );

if( $today == dbDate( strtotime( 'this friday' ) ) )
{
    // Send any time between 4pm and 4:15 pm.
    $awayFrom = strtotime( 'now' ) - strtotime( '4:00 pm' );
    if( $awayFrom >= -1 && $awayFrom < 15 * 60 )
    {
        echo printInfo( "Today is Friday 4pm. Send out emails for AWS" );
        $nextMonday = dbDate( strtotime( 'next monday' ) );
        $subject = 'Next Week AWS (' . humanReadableDate( $nextMonday) . ') by ';

        $res = generateAWSEmail( $nextMonday );
        if( $res )
        {
            $subject = 'Next Week AWS (' . humanReadableDate( $nextMonday) . ') by ';
            $subject .= implode( ', ', $res[ 'speakers'] );

            //$cclist = 'ins@ncbs.res.in,reception@ncbs.res.in';
            //$cclist .= ',multimedia@ncbs.res.in,hospitality@ncbs.res.in';
            //$to = 'academic@lists.ncbs.res.in';

            $cclist = 'dilawar.s.rajput@gmail.com';
            $to = 'dilawars@ncbs.res.in';

            $mail = $res[ 'email' ];

            // generate md5 of email. And store it in archive.
            $archivefile = $maildir . '/' . md5($subject . $mail) . '.email';
            if( file_exists( $archivefile ) )
            {
                echo printInfo( "This email has already been sent. Doing nothing" );
            }
            else 
            {
                $pdffile = $res[ 'pdffile' ];
                $res = sendPlainTextEmail( $mail, $subject, $to, $cclist, $pdffile );
                echo printInfo( "Saving the mail in archive" . $archivefile );
                file_put_contents( $archivefile, "SENT" );
            }
            ob_flush( );
        }
    }
}
//else if( $today == dbDate( strtotime( 'this monday' ) ) )
{
    // Send on 10am.
    $awayFrom = strtotime( 'now' ) - strtotime( '10:00 am' );
    //if( $awayFrom >= -1 && $awayFrom < 15 * 60 )
    {
        echo printInfo( "Today is Monday 10am. Send out emails for AWS" );
        $thisMonday = dbDate( strtotime( 'this monday' ) );
        $subject = 'Today\'s AWS (' . humanReadableDate( $thisMonday) . ') by ';

        $res = generateAWSEmail( $thisMonday );
        if( $res )
        {
            $subject .= implode( ', ', $res[ 'speakers'] );
            //$cclist = 'ins@ncbs.res.in,reception@ncbs.res.in';
            //$cclist .= ',multimedia@ncbs.res.in,hospitality@ncbs.res.in';
            //$to = 'academic@lists.ncbs.res.in';

            $cclist = 'dilawar.s.rajput@gmail.com,hippo@lists.ncbs.res.in';
            $to = 'dilawars@ncbs.res.in';

            $mail = $res[ 'email' ];

            // generate md5 of email. And store it in archive.
            $archivefile = $maildir . '/' . md5($subject . $mail) . '.email';
            if( file_exists( $archivefile ) )
            {
                echo printInfo( "This email has already been sent. Doing nothing" );
            }
            else 
            {
                $pdffile = $res[ 'pdffile' ];
                $ret = sendPlainTextEmail( $mail, $subject, $to, $cclist, $pdffile );
                echo printInfo( "Return value $ret" );
                echo printInfo( "Saving the mail in archive" . $archivefile );
                file_put_contents( $archivefile, "SENT" );
            }
            ob_flush( );
        }
    }
}
//else
//{
    //echo printInfo( "Today is neither Friday nor monday" );
    //ob_flush( );
//}

?>
