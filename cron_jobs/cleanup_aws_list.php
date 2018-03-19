<?php

require_once 'cron_jobs/helper.php';

function changeAWSEligibility( $speaker )
{
    echo printInfo( "$speaker is eligible for AWS. removing from list" );
    $res = updateTable( 'logins', 'login', 'eligible_for_aws'
        , array( 'login' => $speaker, 'eligible_for_aws' => 'NO' )
    );
    if( $res )
        echo printInfo( "Successfully removed" );

}

/* Every monday, check students who are not eligible for AWS anymore */
if( trueOnGivenDayAndTime( 'this monday', '16:45' ) )
{
    echo printInfo( 'Monday, removing students who have given PRE_SYNOPSIS SEMINAR and thesis SEMINAR' );

    // In last two weeks.
    $cutoff = strtotime( 'today' ) - 14 * 24 * 2600;

    $presynAWS = getTableEntries( 'annual_work_seminars', 'date'
        , "IS_PRESYNOPSIS_SEMINAR='YES' AND date > '$cutoff'" );

    foreach( $presynAWS as $aws )
    {
        $speaker = $aws[ 'speaker' ];
        if( isEligibleForAWS( $speaker ) )
            changeAWSEligibility( $speaker );
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
                changeAWSEligibility( $login );
        }

    }



}

?>
