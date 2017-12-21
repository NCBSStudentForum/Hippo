<?php

require_once './cron_jobs/helper.php';

/*
* This file is run by cron every 15 minutes.
*/
/*
 * Task 1. If today is Friday. Then prepare a list of upcoming AWS and send out
 * and email at 4pm.
*/

$today = dbDate( strtotime( 'today' ) );
echo printInfo( "Today is " . humanReadableDate( $today ) );

if( trueOnGivenDayAndTime( 'this friday', '4:00 pm' ) )
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

        $res = sendHTMLEmail( $mail[ 'email_body'], $subject, $to, $cclist, null );
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
        sendHTMLEmail( $mail, $subject, $to, $cclist );
    }
}

if( trueOnGivenDayAndTime( 'this friday', '15:00' ) )
{
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
                echo "Sending AWS notification $to </pre>";
                sendHTMLEmail( $email[ 'email_body' ], $subject, $to, $cc );
            }
        }
    }
}

if( trueOnGivenDayAndTime( 'this monday', '10:00 am' ) )
{
    error_log( "Monday 10amm. Notify about AWS" );
    echo printInfo( "Today is Monday. Send out emails for AWS" );
    $thisMonday = dbDate( strtotime( 'this monday' ) );
    $subject = 'Today\'s AWS (' . humanReadableDate( $thisMonday) . ') by ';
    $res = generateAWSEmail( $thisMonday );
    $to = 'academic@lists.ncbs.res.in';

    if( $res[ 'speakers' ] )
    {
        echo printInfo( "Sending mail about today's AWS" );
        $subject .= implode( ', ', $res[ 'speakers'] );

        $mail = $res[ 'email' ]['email_body'];

        error_log( "Sending to $to, $cclist with subject $subject" );
        echo( "Sending to $to, $cclist with subject $subject" );

        $pdffile = $res[ 'pdffile' ];
        $ret = sendHTMLEmail( $mail, $subject, $to, $cclist, $pdffile );
        ob_flush( );
    }
    else
    {
        // There is no AWS this monday.
        $subject = 'No Annual Work Seminar today : ' .
            humanReadableDate( $nextMonday );
        $mail = $res[ 'email' ]['email_body'];
        sendHTMLEmail( $mail, $subject, $to, $res['email']['cc'] );
    }
}

?>
