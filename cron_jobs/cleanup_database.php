<?php

require_once 'cron_jobs/helper.php';

/* Every monday, check students who are not eligible for AWS anymore */
if( trueOnGivenDayAndTime( 'this monday', '17:00' ) )
{
    echo printInfo( 'Monday, removing students who have given PRE_SYNOPSIS SEMINAR and thesis SEMINAR' );

    // In last two weeks.
    $cutoff = dbDate( strtotime( 'today' ) - 14 * 24 * 3600 );

    $presynAWS = getTableEntries( 'annual_work_seminars', 'date'
        , "IS_PRESYNOPSIS_SEMINAR='YES' AND date > '$cutoff'" );

    foreach( $presynAWS as $aws )
    {
        $speaker = $aws[ 'speaker' ];
        if( isEligibleForAWS( $speaker ) )
            removeAWSSpeakerFromList( $speaker );
    }

    /* Now removing students with THESIS SEMINAR */
    echo printInfo( "Removing students who have given thesis seminar" );
    $thesisSeminars = getTableEntries( 'talks', 'id' , "class='THESIS SEMINAR'" );
    foreach( $thesisSeminars as $talk )
    {
        $speaker = getSpeakerByID( $talk['speaker_id'] ) or getSpeakerByName( $talk[ 'speaker' ] );
        $login = findAnyoneWithEmail( __get__( $speaker, 'email', '' ) );
        if( $login )
        {
            $login = $login[ 'login'];
            if( isEligibleForAWS( $login ) )
                removeAWSSpeakerFromList( $login );
        }
    }

    // Also cleanup the AWS preferences.
    $today = dbDate( 'today' );
    $prefs = getTableEntries( 'aws_scheduling_request'
        , 'id'
        , "first_preference < '$today' AND 'second_preference' < '$today' AND status='APPROVED'"
    );

    foreach( $prefs as $p )
    {
        echo printInfo( "Removing preferences " . $p['id'] );

        // Since we don't have expired field.
        $p[ 'status' ] = 'CANCELLED';
        updateTable( 'aws_scheduling_request', 'id', 'status', $p );
    }
}

?>
