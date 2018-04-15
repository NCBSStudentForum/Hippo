<?php

require_once 'cron_jobs/helper.php';

if( trueOnGivenDayAndTime( 'this sunday', '18:00' ) )
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
    $events = array_filter( $events
        , function($x) { return $x['is_public_event'] == 'YES' && $x['external_id'] ; } 
    );

    echo( "Total public events" . count( $events ) );
    if( count( $events ) > 0 )
    {
        foreach( $events as $event )
        {
            $externalId = $event[ 'external_id'];
            $id = explode( '.', $externalId);
            $id = $id[1];
            if( intval( $id ) < 0 )
            {
                echo printWarning( "Invalid id for this event" );
                continue;
            }

            $talk = getTableEntry( 'talks', 'id', array( 'id' => $id ) );

            // We just need the summary of every event here.
            $html .= eventSummaryHTML( $event, $talk );
            $html .= "<br>";
        }

        $html .= "<br><br>";

        // Generate email
        $templ = emailFromTemplate( 'this_week_events' , array( "EMAIL_BODY" => $html ));
        sendHTMLEmail( $templ[ 'email_body'], $subject, $to, $cclist );
    }
    else
    {
        $html .= "<p>Sigh! I could not find any event scheduled for upcoming week.</p>";
        $html .=  "<p> -- NCBS Hippo </p>";
        sendHTMLEmail( $html, $subject, $to, $cclist );
    }
}

?>
