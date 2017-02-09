<?php

include_once 'database.php';
include_once 'methods.php';
include_once './check_access_permissions.php';

mustHaveAnyOfTheseRoles( array( "USER" ) );

$roundedTimeNow = round( time( ) / (15 * 60) ) * (15 * 60 );

$defaults = array( 
    "date" => dbDate( strtotime( 'today' ) )
    , "start_time" => date( 'H:i', $roundedTimeNow )
    , "end_time" => date( 'H:i', $roundedTimeNow + 3600 )
    , "strength" => 10
    , "has_skype" => "NO"
    , "openair" => "NO"
    );

// Since we come back to this page again and again, we reuse the previous values 
// provided by user.
foreach( $defaults as $key => $val )
    if( array_key_exists( $key, $_POST ) )
        $defaults[ $key ] = $_POST[ $key ];

$skypeYes = ''; $skypeNo = '';
if( $defaults[ 'has_skype' ] == 'YES' )
    $skypeYes = 'checked'; 
else 
    $skypeNo = 'checked';

$openAirNo = ''; $openAirYes = ' ';
if( $defaults[ 'openair' ] == 'YES' )
    $openAirYes = 'checked'; 
else 
    $openAirNo = 'checked';


echo printInfo("Do not use this interface yet. Under construction .. ");

echo '<table border="0">';
echo '<form action="" method="post" accept-charset="utf-8">';
echo '
    <tr>
        <td>Pick a date</td>
        <td><input type="date" class="datepicker" name="date" 
            value="' . $defaults[ 'date' ] . '" /> </td>
    </tr>
    <tr>
        <td>Start time </td>
        <td><input type="time" class="timepicker" name="start_time" 
            value="' . $defaults[ 'start_time'] . '" /> </td>
    </tr>
    <tr>
        <td>End time </td>
        <td><input type="text" class="timepicker" name="end_time" 
            value="' . $defaults[ 'end_time'] . '" /> </td>
    </tr>
    <tr>
        <td>Number of people? </td>
        <td><input type="text" name="strength" 
            value="' . $defaults[ 'strength' ] . '" /> </td>
    </tr>
    <tr>
        <td>Do you need skype?</td>
        <td>
            <input type="radio" name="has_skype" value="NO" ' . $skypeNo . ' /> No
            <input type="radio" name="has_skype" value="YES" ' .$skypeYes . ' /> Yes
        </td>
    </tr>
    <tr>
        <td>Prefer open-air location?</td>
        <td>
            <input type="radio" name="openair" value="NO"' . $openAirNo . ' $/> No
            <input type="radio" name="openair" value="YES"' . $openAirYes . ' /> Yes
        </td>
    </tr>
    <tr>
        <td></td>
        <td>
            <button class="submit" name="Response" value="scan">Show me venues</button>
        </td>
    </tr>
    ';

echo '</form>';
echo '</table>';

if( array_key_exists( 'Response', $_POST ) && $_POST['Response'] == "scan" )
{
    echo "<h3>I've found following available venues</h3>";

    $venues = getVenues( $sortby = 'strength' );

    echo '<table border="0">';
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
        $events = getEventsOnThisVenueBetweenTime(
            $venue[ 'id' ]
            , dbDate( $_POST[ 'date' ] )
            , $_POST[ 'start_time' ], $_POST[ 'end_time' ]
            );
        $reqs = getRequestsOnThisVenueBetweenTime( 
            $venue[ 'id' ]
            , dbDate( $_POST[ 'date' ] )
            , $_POST[ 'start_time' ], $_POST[ 'end_time' ]
            );

        if( count( $events ) > 0 || count( $reqs ) )
            continue;

        // Now construct a table and form
        echo '<tr>';
        echo '<form method="post" action="user_submit_request.php">';
        // Insert all information into form.
        echo '<input type="hidden" name="date" value="' . $defaults[ 'date' ] . '" >';

        echo '<input type="hidden" 
            name="start_time" value="' . $defaults[ 'start_time' ] . '" >';
        echo '<input type="hidden" 
            name="end_time" value="' . $defaults[ 'end_time' ] . '" >';
        echo '<input type="hidden" 
            name="venue" value="' . $venue[ 'id' ] . '" >';

        $venueT = venueToText( $venue );
        echo "<td>$venueT</td>";
        echo '<td> <button type="submit">Book</button></td>';
        echo '</form>';
        echo '</tr>';
    }
    echo '</table>';
    unset( $_POST );
}


echo goBackToPageLink( "user.php", "Go back" );

?>
