<?xml version="1.0" encoding="UTF-8" ?>
<?php

include_once 'database.php';

function venueText( $venue )
{
    if( is_string( $venue ) )
        $venue = getVenueById( $venue );

    return $venue['name'] . ' ' . $venue['building_name'] . ', ' . $venue['location'];
}

// RSS feed.
function feedDate( $date )
{
    if( strtotime( $date ) == strtotime( 'today' ) )
        return 'Today';
    else if( strtotime( $date ) <= (strtotime( 'today' ) + 24 * 3600 ) )
        return 'Tomorrow';

    return humanReadableDate( $date );
}

$events = getPublicEvents( 'today', 'VALID', 7 );

$feed =  '<rss version="2.0">
    <channel>';

$feed .= "<title>Events over next 7 days</title>";
$feed .= "<link>" . appURL( ) . "</link>";
$feed .= "<description>NCB events list </description>";
foreach( $events as $e )
{
    if( $e['date'] == dbDate( 'today' ) )
        if( strtotime( $e['end_time'] ) < strtotime( 'now' ) )
            continue;

    $feed .= "<item>";
    $feed .= "<title>" . $e[ 'title'] . "</title>";

    $feed .= "<link> https://ncbs.res.in/hippo/events.php?date=" . $e['date'] . 
                "</link>";
    $feed .= "<description>" 
                    .  feedDate( $e[ 'date' ] ) . ", " 
                    . humanReadableTime( $e['start_time' ] ) 
                    .  " to " . humanReadableTime( $e[ 'end_time' ] )
                    . ', ' . venueText( $e[ 'venue' ], false )
                    . "</description>";
    $feed .= "<pubDate> " . date( 'r', strtotime('now') ) . "</pubDate>";
    $feed .= "</item>";
}

$feed .= '</channel>';
$feed .= '</rss>';
header( 'Content-Type: application/xhtml+xml' );

echo $feed;

?>

