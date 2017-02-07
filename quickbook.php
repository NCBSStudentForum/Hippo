<?php

include_once 'database.php';
include_once 'methods.php';
include_once './check_access_permissions.php';

mustHaveAnyOfTheseRoles( array( "USER" ) );

$today = dbDate( strtotime( 'today' ) );
$now = date( 'H:m', strtotime( 'now' ) );

echo "<strong> Do not use this interface yet. Under construction .. </strong>";

echo '<table border="0">';
echo '<form action="" method="post" accept-charset="utf-8">';
echo '
    <tr>
        <td>Pick a date</td>
        <td><input type="date" class="datepicker" name="date" 
            value="' . $today . '" /></td>
    </tr>
    <tr>
        <td>Pick a time</td>
        <td><input type="time" class="timepicker" name="time" 
            value="' . $now . '" /></td>
    </tr>
    <tr>
        <td>Number of people? </td>
        <td><input type="text" name="strength" value="10" placeholder="10"/></td>
    </tr>
    <tr>
        <td>Do you need skype?</td>
        <td>
            <input type="radio" name="has_skype" value="NO" checked $/> No
            <input type="radio" name="has_skype" value="YES" /> Yes
        </td>
    </tr>
    <tr>
        <td>Prefer open-air location?</td>
        <td>
            <input type="radio" name="openair" value="NO" checked $/> No
            <input type="radio" name="openair" value="YES" /> Yes
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <button class="submit" name="Response" value="scan">Scan</button>
        </td>
    </tr>
    ';

echo '</form>';
echo '</table>';

if( array_key_exists( 'Response', $_POST ) && $_POST['Response'] == "scan" )
{
    $venues = getVenues( $sortby = 'strength' );
    foreach ($venues as $venue) 
    {
        if( $venue[ 'strength' ] < $_POST[ 'strength' ] )
            continue;

        // One can reduce a Kernaugh map here. The expression is A' + B where
        // A is request for skype variable and B is has_skype field of 
        // venue. We take its negative and use continue.
        if( $_POST[ 'has_skype' ] == 'YES' && ! ($venue[ 'has_skype' ] == 'YES') )
            continue;

        // Similarly, openair.
        if( $_POST[ 'openair' ] == 'YES' && ! ($venue[ 'type' ] == 'OPEN AIR') )
            continue;

        // Cool. Now check if any event is scheduled at this venue.
        $events = getEventsOnThisVenueOnThisDatetime(
            $venue[ 'id' ]
            , dbDate( $_POST[ 'date' ] )
            , $_POST[ 'time' ]
            );
        $reqs = getRequestsOnThisVenueOnThisDatetime( 
            $venue[ 'id' ]
            , dbDate( $_POST[ 'date' ] )
            , $_POST[ 'time' ]
            );

        if( count( $events ) > 0 || count( $reqs ) )
            continue;

        echo venueToText( $venue );
        echo "<br>";
    }

    unset( $_POST );
}


echo goBackToPageLink( "user.php", "Go back" );

?>
