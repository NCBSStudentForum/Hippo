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

    $jcDay = strtotime( 'This ' . $jc[ 'day' ] );
    $nWeeksFromjcDay = $jcDay + 3 * 24 * 3600;  // Three weeks from this monday.

    echo printInfo( "JcID is $jcID." );

    if( isNowEqualsGivenDayAndTime( $jcDay, '10:00' ) )
    {
        echo printInfo( "Scheduling for $jcID" );

        // check if there is anyone scheduled on nWeeksFromjcDay
        $schedule = getJCPresentations( $jcID, $nWeeksFromjcDay, '' );
        if( count( $schedule ) > 0 )
        {
            echo printInfo( "$jcID already have a schedule on " . humanReadableDate( $nWeeksFromjcDay) );
            continue;
        }

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
