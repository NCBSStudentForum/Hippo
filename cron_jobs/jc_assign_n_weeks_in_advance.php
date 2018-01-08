<?php

require_once 'cron_jobs/helper.php';
require_once 'actions/jc_admin.php';


////////////////////////////////////////////////////////////////////////////
// JOURNAL CLUB

echo "<p>Scheduling JCs </p>";

$jcs = getActiveJCs( );
foreach( $jcs as $jc )
{
    $jcID = $jc[ 'id' ];

    $jcDay =  'This ' . $jc[ 'day' ];
    $nWeeksFromjcDay = strtotime($jcDay) + 3 * 7 * 24 * 3600;  // Three weeks from this monday.

    if( isNowEqualsGivenDayAndTime( $jcDay, dbTime( $jc['time'] ) ) )
    {
        echo printInfo( "JcID is $jcID with $jcDay." );
        error_log( "Scheduling for $jcID" );
        echo printInfo( "Scheduling for $jcID" );
        // check if there is anyone scheduled on nWeeksFromjcDay
        $schedule = getJCPresentations( $jcID, $nWeeksFromjcDay, '' );
        if( count( $schedule ) > 0 )
        {
            error_log( "$jcID alreay have a schedule on " . humanReadableDate( $nWeeksFromjcDay ) );
            echo printInfo( "$jcID already have a schedule on " . humanReadableDate( $nWeeksFromjcDay) );
            continue;
        }

        error_log( "Finding speaker" );
        // Else find someone and assign.
        $presenter = pickPresenter( $jcID );
        $res = fixJCSchedule( $presenter
                , array( 'date' => dbDate( $nWeeksFromjcDay ), 'jc_id' => $jcID )
            );

        if( $res )
            echo printInfo( "Success! " );
    }
}

?>
