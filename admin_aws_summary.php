<?php

include_once 'header.php';
include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';
include_once 'check_access_permissions.php';

mustHaveAllOfTheseRoles( array( 'AWS_ADMIN' ) );

echo "<h3>Annual Work Seminars Summary</h3>";

function awsOnThisBlock( $awsDays, $block, $blockSize )
{
    foreach( $awsDays as $awsDay )
    {
        $awsWeek = intval( $awsDay / $blockSize );
        if( 0 == ($block - $awsWeek ) )
            return true;
    }
    return false;
}

function daysToLine( $awsDays, $totalDays, $blockSize = 7)
{
    asort( $awsDays );

    $today = strtotime( 'now' );

    $totalBlocks = intval( $totalDays / $blockSize ) + 1;
    $line = '<td><small>';

    $line .= intval( $awsDays[0] / 30 ) . ',' ;
    for( $i = 1; $i < count( $awsDays ); $i++ )
        $line .=  intval(( $awsDays[ $i - 1 ] - $awsDays[ $i ] ) / 30) . ',';

    $line .= "</small></td><td>";

    for( $i = 0; $i <= $totalBlocks; $i++ )
    {
        if( awsOnThisBlock( $awsDays, $i, $blockSize ) )
            $line .= '|';
        else
            $line .= '.';
    }

    $line .= "</td>";
    return $line;
}


// Get AWS in roughly last 10 years.
$totalDays = 5 * 365;
$from = date( 'Y-m-d', strtotime( 'now' ) - $totalDays * 24 * 3600 );
$awses = getAWSFromPast( $from );
$speakerAWS = array( );
foreach( $awses as $aws )
{
    if( ! array_key_exists( $aws['speaker'], $speakerAWS ) )
        $speakerAWS[ $aws['speaker'] ] = array( $aws );
    else
        array_push( $speakerAWS[ $aws['speaker'] ], $aws );
}

$table = '<table border="0" class="show_aws_summary">';

$table .= '<tr>
    <th>Name <small>email</small></th>
    <th>Months between AWSes</th>
    <th>Previous AWS</th>
    </tr>';

foreach( $speakerAWS as $speaker => $awses )
{
    $table .= "<tr> <td> " . loginToText( $speaker ) 
                . "<br><small> $speaker </small>" . "</td>";
    $when = array( );
    foreach( $awses as $aws )
    {
        $awsDay = strtotime( $aws['date'] );
        $ndays = intval(( strtotime( 'now' ) - $awsDay) / (24 * 3600 ));
        array_push( $when, $ndays );
    }

    $line = daysToLine( $when, $totalDays, $blockSize = 28 );
    $table .= $line;
    $table .= "</tr>";

}

$table .= "</table>";
echo $table;

?>
