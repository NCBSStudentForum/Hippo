<?php

include_once 'header.php';
include_once 'methods.php';
include_once 'database.php';
include_once 'tohtml.php';

// This page displays all events on campus. Select all venues.
$venues = getVenues( $sortby = 'id' );
$venuesDict = array( );
foreach( $venues as $v )
    $venuesDict[ $v[ 'id' ] ] = $v;

$venuesIds = array_map( function( $v ) { return $v['id']; }, $venues );

$defaults = array( 'venues' => implode(',', $venuesIds)
        , 'date' => dbDate( 'today' )
        );

if( array_key_exists( 'date', $_GET ) )
    $defaults[ 'date' ] = $_GET[ 'date' ];

if( array_key_exists( 'venues', $_GET ) )
    $defaults[ 'venues' ] = implode( ',', $_GET[ 'venues' ] );


echo '<form action="" method="get" accept-charset="utf-8">
    <table class="info">
    <tr>
        <td> <input type="date" class="datepicker" name="date" value="' . 
            $defaults[ 'date' ] . '" /> </td>
            <td> <button name="response" value="' . $defaults[ 'venues' ] . 
                '" >' . $symbScan . '</button> </td>
    </tr>
    </table>
    </form>';

$calendarDate = humanReadableDate( $defaults[ 'date' ] );
echo "<h1> Table of events on $calendarDate </h1>";

foreach( explode( ",", $defaults[ 'venues' ]) as $venueId )
{
    $events = getEventsOnThisVenueOnThisday( $venueId, $defaults[ 'date' ] );
    if( count( $events ) < 1 )
        continue;

    echo venueToText( $venuesDict[ $venueId ] );
    echo '<table>';
    echo '<tr>';
    foreach( $events as $ev )
    {
        echo "<td style=\"min-width:150px;max-width:300px;border:1px dotted;\">";
        echo eventToHTML( $ev );
        echo "</td>";
    }
    echo '</tr>';
    echo '</table>';
    echo '</br>';
}

echo closePage( );

?>
