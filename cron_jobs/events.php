<?php

require_once './cron_jobs/helper.php';

if( trueOnGivenDayAndTime( 'this sunday', '7:00 pm' ) )
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

        sendHTMLEmail( $templ[ 'email_body'], $subject, $to, $cclist );
    }
    else
    {
        $html .= "<p> I could not find any event in my database! </p>";
        $html .=  "<p> -- Hippo </p>";
        sendHTMLEmail( $html, $subject, $to, $cclist );
    }
}

/*
 * Task 2. Send today's event every day at 8am.
 */
if( trueOnGivenDayAndTime( 'today', '8:00' ) )
{
    error_log( "8am. Event for today" );
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
                        $attachment = '';
                        sendHTMLEmail( $msg, $subject, $to, $ccs, $attachment );
                    }
                }
            }
        }
        ob_flush( );
    }
    else
        error_log( "No event found on day " . $today );
}


?>
