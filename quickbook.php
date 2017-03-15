<?php

include_once 'database.php';
include_once 'methods.php';
include_once 'tohtml.php';
include_once './check_access_permissions.php';

mustHaveAnyOfTheseRoles( array( "USER" ) );

echo userHTML( );

$roundedTimeNow = round( time( ) / (15 * 60) ) * (15 * 60 );

$defaults = array( 
    "date" => dbDate( strtotime( 'today' ) )
    , "start_time" => date( 'H:i', $roundedTimeNow )
    , "end_time" => date( 'H:i', $roundedTimeNow + 3600 )
    , "strength" => 10
    , "has_skype" => "NO"
    , "has_projector" => "NO"
    , "openair" => "NO"
    , "title" => ''
    );

$external_id = null;
if( array_key_exists( 'external_id', $_GET ) )
{
    $external_id = $_GET[ 'external_id' ];
    $expr = explode( ".", $external_id );
    $tableName = $expr[ 0 ];
    $id = $expr[ 1 ];
    $entry = getTableEntry( $tableName, 'id', array( "id" => $id ) );
    echo printInfo( "Scheduling for a following talk" );
    echo arrayToTableHTML( $entry, 'talk', '', 'id,status,date,time,venue,venue' );
    $defaults = array_merge( $defaults, $entry );
}

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

$projectorYes = ''; $projectorNo = '';
if( $defaults[ 'has_projector' ] == 'YES' )
    $projectorYes = 'checked'; 
else 
    $projectorNo = 'checked';

$openAirNo = ''; $openAirYes = ' ';
if( $defaults[ 'openair' ] == 'YES' )
    $openAirYes = 'checked'; 
else 
    $openAirNo = 'checked';


echo alertUser(
    'A powerful booking interface is also available 
    if you need to explore other events/dates/venues 
    <a href="bookmyvenue_browse.php">TAKE ME THERE</a>
    '
    );

echo '<br />';
echo '<table style="min-width:300px;max-width:500px",border="0">';
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
        <td><input type="time" class="timepicker" name="end_time" 
            value="' . $defaults[ 'end_time'] . '" /> </td>
    </tr>
    <tr>
        <td>Mininum seatings required? </td>
        <td><input type="text" name="strength" 
            value="' . $defaults[ 'strength' ] . '" /> </td>
    </tr>
    <tr>
        <td>Do you need video-conference facility?</td>
        <td>
            <input type="radio" name="has_skype" value="NO" ' . $skypeNo . ' /> No
            <input type="radio" name="has_skype" value="YES" ' .$skypeYes . ' /> Yes
        </td>
    </tr>
    <tr>
        <td>Do you need a projector?</td>
        <td>
        <input type="radio" name="has_projector" 
            value="NO" ' . $projectorNo . ' /> No
        <input type="radio" name="has_projector" 
                value="YES" ' .$projectorYes . ' /> Yes
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
        <td style="text-align:right">
        <button title="Scan for venues" 
            style="font-size:large" name="Response" value="scan">
                Show me <br> available venues</button>
        </td>
    </tr>
    ';

echo '</form>';
echo '</table>';

$date = __get__( $_POST, 'date', dbDate(strtotime( 'today' )) );

// Get list of public events on user request day and show them to him. So he can 
// decides if some other timeslot should be used.
$publicEvents = getPublicEventsOnThisDay( $date );
if( count( $publicEvents ) > 0 )
{
    echo alertUser( "FYI. Following public events are happening on the campus on 
        selected date" );
    foreach( $publicEvents as $event )
        echo arrayToTableHTML( $event, 'events', ''
        , array( 'gid', 'eid', 'description', 'status', 'is_public_event'
            , 'external_id'
            , 'calendar_id', 'calendar_event_id', 'last_modified_on' 
            )
        );
}

if( array_key_exists( 'Response', $_POST ) && $_POST['Response'] == "scan" )
{
    $date = humanReadableDate( $_POST[ 'date' ] );

    echo printInfo( "I found following available venues for $date" );
    echo "<br/>";

    $venues = getVenues( $sortby = 'name' );

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

        $all = array( );
        if( $events )
            $all = array_merge( $all, $events );
        if( $reqs )
            $all = array_merge( $all, $reqs );

        // If there is already any request or event on this venue, do not book.
        if( count( $all ) > 0 )
        {
            echo '<tr><td colspan="2">';
            echo alertUser( "Venue " . $venue[ 'id' ] . " is taken " .
                    " by following booking requests/events" 
                );

            echo '<div style="font-size:x-small">';
            foreach( $all as $r )
                echo arrayToTableHTML( $r, 'info', ''
                        , 'is_public_event,url,description,gid,rid,external_id,modified_by,timestamp'
                    );
            echo '</div>';
            echo '</td></tr>';
            continue;
        }

        // Now construct a table and form
        echo '<tr>';
        echo '<form method="post" action="user_submit_request.php">';

        // Create hidden fields from defaults.
        echo '<input type="hidden" name="title" value="' . $defaults['title' ] . '">';
        echo '<input type="hidden" name="description" 
            value="' . $defaults[ 'title' ] . '">';
        echo '<input type="hidden" name="external_id" 
            value="' . $external_id . '">';
        // Insert all information into form.
        echo '<input type="hidden" name="date" value="' . $defaults[ 'date' ] . '" >';

        echo '<input type="hidden" 
            name="start_time" value="' . $defaults[ 'start_time' ] . '" >';
        echo '<input type="hidden" 
            name="end_time" value="' . $defaults[ 'end_time' ] . '" >';
        echo '<input type="hidden" 
            name="venue" value="' . $venue[ 'id' ] . '" >';

        $venueT = venueSummary( $venue );
        echo "<td>$venueT</td>";
        echo '<td> <button type="submit" title="Book this venue">' . $symbCheck . '</button></td>';
        echo '</form>';
        echo '</tr>';
    }
    echo '</table>';
    unset( $_POST );
}


echo goBackToPageLink( "user.php", "Go back" );

?>
