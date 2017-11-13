<?php
/*
 * This file is run by cron every 15 minutes.
 */

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

/* Task 0. Send email to hippo mailing list that Hippo is alive.
 */
$today = dbDate( strtotime( 'today' ) );
{
    $awayFrom = strtotime( 'now' ) - strtotime( '7:00 am' );
    if( $awayFrom >= -1 && $awayFrom < 15 * 60 )
    {
        $day = humanReadableDate( $today );
        sendPlainTextEmail( "<p>Hippo is alive on $day </p>"
            , "Hippo is ALIVE on $day"
            , 'hippo@lists.ncbs.res.in'
            );
    }
}

/*
 * Task 1. If today is Friday. Then prepare a list of upcoming AWS and send out 
 * and email at 4pm.
*/

$today = dbDate( strtotime( 'today' ) );
echo printInfo( "Today is " . humanReadableDate( $today ) );

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

            echo $mail[ 'email_body' ];

            $res = sendPlainTextEmail( $mail[ 'email_body'], $subject, $to, $cclist, null );
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

    /* Send out email to TCM members and faculty about upcoming AWS. */
    $awayFrom = strtotime( 'now' ) - strtotime( '15:00' );
    if( $awayFrom >= -1 && $awayFrom < 15 * 60 )
    {
        error_log( 'Try notifying TCM and PI about AWS' );
        $awses = getUpcomingAWS( dbDate( 'next monday' ) );
        foreach( $awses as $aws )
        {
            $speaker = loginToText( $aws[ 'speaker' ] );
            $emails = array( );
            foreach( $aws as $key => $value )
                if( preg_match( '/tcm_member_\d|supervisor_\d/', $key ) )
                    if( strlen( $value )  > 1 )
                        $emails[ ] = $value;

            foreach( $emails as $email )
            {
                $recipient = findAnyoneWithEmail( $email );
                $name = arrayToName( $recipient );
                $email = emailFromTemplate( 'NOTIFY_SUPERVISOR_TCM_ABOUT_AWS'
                    , array( 'FACULTY' => $name, 'AWS_SPEAKER' => $speaker
                            , 'AWS_DATE' => humanReadableDate( $aws[ 'date' ] ) 
                            , 'AWS_DATE_DB' => $aws[ 'date' ]
                        )
                    );
                $subject = 'Annual Work Seminar of ' . $speaker;
                $to = $recipient[ 'email' ];
                $cc = $email[ 'cc' ];
                echo "<p>Sending AWS notification $to </pre>";
                sendPlainTextEmail( $email[ 'email_body' ], $subject, $to, $cc );
            }
        }
    }
}

if( $today == dbDate( strtotime( 'this monday' ) ) )
{
    // Send on 10 am about AWS
    $awayFrom = strtotime( 'now' ) - strtotime( '10:00 am' );
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

if( $today == dbDate( strtotime( 'this sunday' ) ) )
{
    // Send on 7pm about this week events.
    $awayFrom = strtotime( 'now' ) - strtotime( '7:00 pm' );
    if( $awayFrom >= -1 && $awayFrom < 15 * 60 )
    {
        echo printInfo( "Today is Sunday 7pm. Send out emails for week events." );
        $thisMonday = dbDate( strtotime( 'this monday' ) );
        $subject = 'This week ( ' . humanReadableDate( $thisMonday) . ' ) events ';

        $cclist = '';
        $to = 'academic@lists.ncbs.res.in';

        $html = "<p>Greetings!</p>";

        $html .= printInfo( "List of events for the week starting " 
                    . humanReadableDate( $thisMonday ) 
                );

        $events = getEventsBeteen( $from = 'today', $duration = '+6 day' );

        if( count( $events ) > 0 )
        {
            foreach( $events as $event )
            {
                if( $event[ 'is_public_event' ] == 'NO' )
                    continue;

                $externalId = $event[ 'external_id'];
                if( ! $externalId )
                    continue;

                $id = explode( '.', $externalId);
                $id = $id[1];
                if( intval( $id ) < 0 )
                    continue;

                $talk = getTableEntry( 'talks', 'id', array( 'id' => $id ) );

                // We just need the summary of every event here.
                $html .= eventSummaryHTML( $event, $talk );
                $html .= "<br>";
            }

            $html .= "<br><br>";

            // Generate email
            // getEmailTemplates
            $templ = emailFromTemplate( 'this_week_events'
                , array( "EMAIL_BODY" => $html ) 
            );

            sendPlainTextEmail( $templ[ 'email_body'], $subject, $to, $cclist );
        }
        else
        {
            $html .= "<p> I could not find any event in my database! </p>";
            $html .=  "<p> -- Hippo </p>";
            sendPlainTextEmail( $html, $subject, $to, $cclist );
        }
    }
}

/*
 * Task 2. Send today's event every day at 8am.
 */
$awayFrom = strtotime( 'now' ) - strtotime( '8:00' );
$today = dbDate( strtotime( 'today' ) );
if( $awayFrom >= -1 && $awayFrom < 15 * 60 )
{
    $todaysEvents = getPublicEventsOnThisDay( $today );

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
                    $html = talkToHTML( $talk );
                    $nTalks += 1;

                    // Now prepare an email to sent to mailing list.
                    $macros = array( 'EMAIL_BODY' => $html, 'DATE' => $today );
                    $subject = "Today (" . humanReadableShortDate( $today ) . "): " ;
                    $subject .= talkToShortEventTitle( $talk );

                    $template = emailFromTemplate( 'todays_events', $macros );

                    if( array_key_exists( 'email_body', $template ) && $template[ 'email_body' ] )
                    {
                        // Send it out.
                        $to = $template[ 'recipients' ];
                        $ccs = $template[ 'cc' ];
                        $msg = $template[ 'email_body' ];
                        sendPlainTextEmail( $msg, $subject, $to, $ccs, $attachment );
                    }
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
            // DO NOT SEND attachment.
            //$attachment = $pdffile;
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
$endDay = strtotime( 'next friday' );
$startDay = $endDay - (3 * 24 * 3600 );
if( $today > $startDay && $today <= $endDay ) 
{
    $awayFrom = strtotime( 'now' ) - strtotime( '10:00 am' );
    if( $awayFrom > -1 && $awayFrom < 15 * 60 )
    {
        // Every day 10 am. Annoy.
        $upcomingAws = getUpcomingAWS( 'next monday' );
        foreach( $upcomingAws as $aws )
        {
            if( $aws[ 'title' ] && $aws['abstract'] )
                continue;

            // Otherwise annoy
            $subject = "Details of your upcoming AWS are still incomplete!";
            $to = getLoginEmail( $aws[ 'speaker' ] );
            $pi = getPIOrHost( $aws[ 'speaker' ] );

            $macros = array( 'USER' => loginToHTML( $aws['speaker'] )
                            , 'DATE' => humanReadableDate( $today ) 
                        );

            $templ = emailFromTemplate( 'hippo_annoys_aws_speaker', $macros );

            // Log it.
            error_log( "AWS entry incomplete. Annoy " . $to  );
            sendPlainTextEmail( 
                $templ[ 'email_body' ], $subject, $to, $templ[ 'cc' ] 
                );
        }

    }
}

/* Everyday check for recurrent events. On 7 days before last events send 
 * and email to person who booked it.
 */
{
    $today = 'today';
    $awayFrom = strtotime( 'now' ) - strtotime( '13:00' );

    if( $awayFrom > -1 && $awayFrom < 15 * 60 )
    {
        echo printInfo( "Checking for recurrent events expiring in 7 days" );
        echo printInfo( "Checking for recurrent events expirings in future" );

        // Get all events which are grouped.
        $groupEvents = getActiveRecurrentEvents( 'today' );

        foreach( $groupEvents as $gid => $events )
        {
            // Get last event of the group.
            $e = end( $events );
            $lastEventOn = $e[ 'date' ];
            $createdBy = $e[ 'created_by' ];
            $eventHtml = arrayToVerticalTableHTML( $e, 'event' );
            $template = emailFromTemplate( 'event_expiring'
                    , array( 'USER' => loginToText( $createdBy ) 
                        , 'EVENT_BODY' => $eventHtml ) 
                    );

            $to = getLoginEmail( $createdBy );

            echo printInfo( "Group id $gid by $to last event $lastEventOn" );

            $cclist = $template[ 'cc' ];
            $title = $e['title'];

            if( strtotime( $today ) + (7 * 24 * 3600) == strtotime( $lastEventOn ) )
            {
                $subject = "IMP! Your recurrent booking '$title' is expiring in 7 days";
                echo printInfo( $subject );
                sendPlainTextEmail( $template[ 'email_body' ]
                    , $subject, $to, $cclist );
            }
            else if( strtotime( $today ) + (1 * 24 * 3600) == strtotime( $lastEventOn ) )
            {
                $subject = "ATTN! Your recurrent booking '$title' is expiring tomorrow";
                echo printInfo( $subject );
                sendPlainTextEmail( $template[ 'email_body' ]
                    , $subject, $to, $cclist );
            }
        }
    }
}

/* If user has not acknowledged their aws date, send them this reminder on even
 * days; at 10 AM.
 */
{
    $today = 'today';
    $awayFrom = strtotime( 'now' ) - strtotime( '10:00' );
    $dayNo = date( 'N', strtotime( 'today' ) );

    // Send this reminder only on even days.
    if( $dayNo % 2 == 0 && $awayFrom > -1 && $awayFrom < 15 * 60 )
    {
        echo printInfo( "Checking for upcoming aws which has not been acknowleged" );

        // Get all events which are grouped.
        $nonConfirmedUpcomingAws = getUpcomingAWS( );

        foreach( $nonConfirmedUpcomingAws as $aws )
        {
            if( $aws[ 'acknowledged' ] == 'YES' )
            {
                echo printInfo( $aws[ 'speaker' ] . " has already confirmed " );
                continue;
            }

            //var_dump( $aws );
            $speaker = $aws[ 'speaker' ];
            $table = arrayToVerticalTableHTML( $aws, 'aws' );
            $to = getLoginEmail( $speaker );
            $email = emailFromTemplate( 'REMIND_SPEAKER_TO_CONFIRM_AWS_DATE'
                , array( 'USER' => loginToHTML( $speaker )
                        , 'AWS_DATE' => humanReadableDate( $aws[ 'date' ] ) )
                );

            $subject = "Please confirm your annual work seminar (AWS) date";
            $body = $email[ 'email_body' ] . 
                "<p> This email was automatically generated and sent on " . 
                humanReadableDate( 'now' ) . 
                ". If this is mistake, please write to acadoffice@ncbs.res.in.</p>";

            // Add PI to cc list.
            $cclist = $email[ 'cc' ];
            $pi = getPIOrHost( $speaker );
            if( $pi )
                $cclist .= ",$pi";

            echo printInfo( "Sending reminder to $to " );
            sendPlainTextEmail( $body, $subject, $to, $cclist );
        }
    }
}

/* Assign AWS after 10 weeks . */
if( dbDate( 'today' ) == dbDate( strtotime( 'this monday' ) ) )
{
    $awayFrom = strtotime( 'now' ) - strtotime( '13:00' );
    if( $awayFrom > -1 && $awayFrom < 15 * 60 )
    {
        $afterEightWeeks = dbDate( strtotime( $today ) + 10 * 7 * 86400 );
        echo printInfo( "Today is monday and after 8 weeks $afterEightWeeks" );

        echo humanReadableDate( $afterEightWeeks );
        // Get scheduled AWS on this week.
        $awses = getTentativeAWSSchedule( $afterEightWeeks );

        // Assign these AWS and send email to speaker.
        foreach( $awses as $aws )
        {
            $speaker = $aws[ 'speaker' ];
            $date = $aws[ 'date' ];
            $res = acceptScheduleOfAWS( $speaker, $date );
            if( $res )
            {
                echo printInfo( "Successfully assigned" );
                rescheduleAWS( );
                $res = notifyUserAboutUpcomingAWS( $speaker, $date );
            }
        }
    }
}

/* --------------------------------------------------------------------------*/
/**
    * @Synopsis  Every two months, first Saturday, 10a.m.; notify each faculty
    * about their AWS candidates.
 */
/* ----------------------------------------------------------------------------*/

$intMonth = intval( date( 'm', strtotime( 'today' ) ) );
// Nothing to do on odd months.
if( $intMonth % 2 == 0 )
{
    $year = getCurrentYear( );
    $month = date( 'M', strtotime( 'today' ));
    $firstSat = strtotime( 'first Saturday', strtotime( "$month $year" ) );
    if( dbDate( $firstSat ) == dbDate( 'today' ) )
    {
        // At 10 am.
        $awayFrom = strtotime( 'now' ) - strtotime( '10:00' );
        if( $awayFrom > -1 && $awayFrom < 15 * 60 )
        {
            $speakers = getAWSSpeakers( );
            $facultyMap = array( );
            foreach( $speakers as $speaker )
            {
                $login = $speaker[ 'login' ];
                $pi = getPIOrHost( $login );
                if( $pi )
                    $facultyMap[ $pi ] =  __get__($facultyMap, $pi, '' ) . ',' . $login;
            }

            // Now print the names.
            foreach( $facultyMap as $fac => $speakers )
            {
                $table = '<table border="1">';
                foreach( explode( ",", $speakers ) as $login )
                {
                    if( ! trim( $login ) )
                        continue;

                    $speaker = loginToHTML( $login, true );
                    $table .= " <tr> <td>$speaker</td> </tr>";
                }
                $table .= "</table>";

                $faculty = arrayToName( findAnyoneWithEmail( $fac ) );
                $email = emailFromTemplate( 'NOTIFY_SUPERVISOR_AWS_CANDIDATES'
                    , array( 'FACULTY' => $faculty, 'LIST_OF_AWS_SPEAKERS' => $table )
                    , 'TIMESTAMP' => dbDateTime( 'now' )
                );

                $body = $email[ 'email_body' ];
                $subject = 'Review the list of AWS speakers of your lab';
                $to = $fac;
                sendPlainTextEmail( $subject, $body, $to );
            }
        }
    }
}

?>
