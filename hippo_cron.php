<?php

include_once 'header.php';
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

error_log( "Running cron job at $now" );
echo( "Running cron at $now" );

function generateAWSEmail( $monday )
{

    $res = array( );

    $upcomingAws = getUpcomingAWS( $monday );
    if( ! $upcomingAws )
        $upcomingAws = getTableEntries( 'annual_work_seminars', "date" , "date='$monday'" );

    $html = '';
    if( count( $upcomingAws ) < 1 )
    {
        $html .= "<p>Greetings</p>";
        $html .= "<p>I could not find any annual work seminar 
                scheduled on " . humanReadableDate( $monday ) . ".</p>";

        $holiday = getTableEntry( 'holidays', 'date'
                        , array( 'date' => dbDate( $monday ) ) );

        if( $holiday )
        {
            $html .= "<p>It is most likely due to following event/holiday: " . 
                        strtoupper( $holiday['description'] ) . ".</p>";

        }

        $html .= "<br>";
        $html .= "<p>That's all I know! </p>";

        $html .= "<br>";
        $html .= "<p>-- NCBS Hippo</p>";

        return array( "email" => $html, "speakers" => null );

    }

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

        $cclist = 'ins@ncbs.res.in,reception@ncbs.res.in';
        $cclist .= ',multimedia@ncbs.res.in,hospitality@ncbs.res.in';
        $to = 'academic@lists.ncbs.res.in';

        $res = generateAWSEmail( $nextMonday );
        if( $res[ 'speakers'] )
        {
            $subject = 'Next week Annual Work Seminar (' . humanReadableDate( $nextMonday) . ') by ';
            $subject .= implode( ', ', $res[ 'speakers'] );
            $mail = $res[ 'email' ];
            $pdffile = $res[ 'pdffile' ];

            $res = sendPlainTextEmail( $mail[ 'email_body'], $subject, $to, $cclist, $pdffile );
            ob_flush( );
        }
        else
        {
            // There is no AWS this monday.
            $subject = 'No Annual Work Seminar next week (' .
                humanReadableDate( $nextMonday ) . ')';

            $mail = $res[ 'email' ];
            echo( "Sending to $to, $cclist with subject $subject" );
            echo( "$mail" );
            sendPlainTextEmail( $mail, $subject, $to, $cclist );
        }
    }
}
else if( $today == dbDate( strtotime( 'this monday' ) ) )
{
    // Send on 8am.
    $awayFrom = strtotime( 'now' ) - strtotime( '8:00 am' );
    if( $awayFrom >= -1 && $awayFrom < 15 * 60 )
    {
        error_log( "Monday 8am. Notify about AWS" );
        echo printInfo( "Today is Monday 8am. Send out emails for AWS" );
        $thisMonday = dbDate( strtotime( 'this monday' ) );
        $subject = 'Today\'s AWS (' . humanReadableDate( $thisMonday) . ') by ';
        $res = generateAWSEmail( $thisMonday );

        $cclist = 'ins@ncbs.res.in,reception@ncbs.res.in';
        $cclist .= ',multimedia@ncbs.res.in,hospitality@ncbs.res.in';
        $to = 'academic@lists.ncbs.res.in';

        if( $res[ 'speakers' ] )
        {
            echo printInfo( "Sending mail about today's AWS" );
            $subject .= implode( ', ', $res[ 'speakers'] );

            $mail = $res[ 'email' ]['email_body'];

            error_log( "Sending to $to, $cclist with subject $subject" );
            echo( "Sending to $to, $cclist with subject $subject" );

            $pdffile = $res[ 'pdffile' ];
            $ret = sendPlainTextEmail( $mail, $subject, $to, $cclist, $pdffile );
            ob_flush( );
        }
        else
        {
            // There is no AWS this monday.
            $subject = 'No Annual Work Seminar today : ' .
                            humanReadableDate( $nextMonday );
            $mail = $res[ 'email' ]['email_body'];
            sendPlainTextEmail( $mail, $subject, $to, $cclist );
        }
    }
}

/*
 * Task 2. Every day at 8am, check today's event and send out an email.
 */
$awayFrom = strtotime( 'now' ) - strtotime( '8:00 am' );
$today = dbDate( strtotime( 'today' ) );
if( $awayFrom >= -1 && $awayFrom < 15 * 60 )
{
    $todaysEvents = getPublicEventsOnThisDay( $today );

    $html = '';
    $nTalks = 0;

    if( count( $todaysEvents ) > 0 )
    {
        foreach( $todaysEvents as $event )
        {
            $external_id = $event[ 'external_id' ];

            // External id has the format TALKS.TALK_ID
            $talkid = explode( '.', $external_id );
            if( count( $talkid ) == 2 )
            {
                $data = array( 'id' => $talkid[1] );
                $talk = getTableEntry( 'talks', 'id', $data );
                if( $talk )
                {
                    $html .= talkToHTML( $talk );
                    $nTalks += 1;
                }
            }
        }

        // Generate pdf now.
        $pdffile = getDataDir( ) . "/EVENTS_$today.pdf";
        $script = __DIR__ . '/generate_pdf_talk.php';
        $cmd = "php -q -f $script date=$today";
        echo "Executing <pre> $cmd </pre>";
        $res = `$cmd`;

        $attachment = '';
        if( file_exists( $pdffile ) )
        {
            echo printInfo( "Successfully generated PDF file" );
            $attachment = $pdffile;
        }

        // Now prepare an email to sent to mailing list.
        $macros = array( 'EMAIL_BODY' => $html, 'DATE' => $today );
        $subject = "Today's events - " . humanReadableDate( $today ) ;

        $template = emailFromTemplate( 'todays_events', $macros );

        // Send emails out only if number of talks are more than 0.
        if( $nTalks > 0 )
        {
            if( array_key_exists( 'email_body', $template ) && $template[ 'email_body' ] )
            {
                // Send it out.
                $to = $template[ 'recipients' ];
                $ccs = $template[ 'CC' ];
                $msg = $template[ 'email_body' ];
                sendPlainTextEmail( $msg, $subject, $to, $ccs, $attachment );
            }
        }
        else
        {
            error_log( 'No public talk or event found for ' . $today );
        }

        ob_flush( );
    }
    else
        error_log( "No event found on day " . $today );
}

/*
 * Task 3: Annoy AWS speaker if they have not completed their entry.
 */
$today = strtotime( 'today' );
$startDay = strtotime( 'this wednesday' );
$endDay = strtotime( 'this friday' );
if( $today >= $startDay && $today <= $endDay ) 
{
    $awayFrom = strtotime( 'now' ) - strtotime( '10:00 am' );
    if( $awayFrom > -1 && $awayFrom < 15 )
    {
        // Every day 10 am. Annoy.
        $upcomingAws = getUpcomingAWS( 'next monday' );
        foreach( $upcomingAws as $aws )
        {
            if( $aws[ 'title' ] && $aws['abstract'] )
                continue;

            // Otherwise annoy
            $subject = "Details of your upcoming AWS are incomplete, human!";
            $to = getEmailById( $aws[ 'speaker' ] );

            $macros = array( 'USER' => getUserInfo( $aws['speaker'] )
                            , 'DATE' => humanReadableDate( $today ) 
                        );
            $templ = emailFromTemplate( 'hippo_annoys_aws_speaker', $data );

            // Log it.
            error_log( "AWS entry incomplete. Annoy " . $to  );
            sendPlainTextEmail( 
                $templ[ 'email_body' ], $subject, $to, $templ[ 'cc' ] 
                );
        }

    }
}

/* Everyday at 1pm check for recurrent events. On 7 days before last events send 
 * and email to person who booked it.
 */
{
    $today = 'today';
    $awayFrom = strtotime( 'now' ) - strtotime( '1:00 pm' );
    //if( $awayFrom > -1 && $awayFrom < 15 )
    {
        echo printInfo( "Checking for recurrent events expiring in 7 days" );
        // Get all events which are grouped.
        $groupEvents = getActiveRecurrentEvents( 'today' );
        foreach( $groupEvents as $gid => $events )
        {
            $e = end( $events );
            $lastEventOn = $e[ 'date' ];

            $createdBy = $e[ 'created_by' ];

            $eventHtml = arrayToVerticalTableHTML( $e, 'event' );

            $template = emailFromTemplate( 'event_expiring'
                    , array( 'USER' => loginToText( $createdBy ) 
                        , 'EVENT_BODY' => $eventHtml ) 
                    );
            $to = getLoginEmail( $createdBy );
            $cclist = $template[ 'cc' ];
            $title = $e['title'];

            if( strtotime( $today ) == strtotime( $lastEventOn ) + 7 * 24 * 3600 )
            {
                $subject = "Your recurrent booking '$title' is expiring in 7 days";
                echo printInfo( $subject );
                sendPlainTextEmail( $template[ 'email_body' ]
                    , $subject, $to, $cclist );
            }
            if( strtotime( $today ) == strtotime( $lastEventOn ) + 1 * 3600 )
            {
                $subject = "Your recurrent booking '$title' is expiring tomorrow";
                echo printInfo( $subject );
                sendPlainTextEmail( $template[ 'email_body' ]
                    , $subject, $to, $cclist );
            }
        }
    }
}

?>
