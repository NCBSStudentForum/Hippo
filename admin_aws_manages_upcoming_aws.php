<?php

include_once "header.php";
include_once "methods.php";
include_once "tohtml.php";
include_once "check_access_permissions.php";

mustHaveAllOfTheseRoles( array( "AWS_ADMIN" ) );

echo userHTML( );
echo '<h3>Upcoming AWS for next week</h3>';

$upcomingAWSs = getUpcomingAWS( );

// For the next week.
$upcomingAwsNextWeek = array( );
foreach( $upcomingAWSs as $aws )
    if( strtotime( $aws['date'] ) - strtotime( 'today' )  < 7 * 24 * 3600 )
        array_push( $upcomingAwsNextWeek, $aws );

foreach( $upcomingAwsNextWeek as $upcomingAWS )
{
    echo arrayToTableHTML( $upcomingAWS, 'aws' , ''
        , array( 'id', 'status', 'comment' )
    );
    echo "<br>";
}

echo "<h3>All upcoming AWS </h3>";

// Show the rest of entries grouped by date.
$groupDate = strtotime( $upcomingAWSs[0]['date'] );
echo '<table class="show_schedule">';
echo '<tr> <td>' . $upcomingAWSs[0]['date'] . '</td>';
foreach( $upcomingAWSs as $aws )
{
    if( strtotime( $aws['date']) != $groupDate )
    {
        $groupDate = strtotime( $aws['date'] );
        echo '</tr>';
        echo '<tr><td>' . $aws['date'] . '</td>';
    }
    echo '<td>' . $aws['speaker'] . '<br>' 
        . loginToText( $aws['speaker']) . '</td>';
}
echo '</tr></table>';

echo "<h3>Temporary assignments </h3>";
echo '
    <p class="info"> Following table shows the best possible schedule I could come up with for 
    next 20 weeks. Pressing <tt>Approve</tt> button will put them into upcoming
    AWS list.
    </p>';

$schedule = getTentativeAWSSchedule( );

echo "<table class=\"show_schedule\">";
$header = "<tr>
    <th>Speaker</th>
    <th>Scheduled On</th>
    <th>Last AWS on</th><th># Day</th> 
    <th>#AWS</th>
    </tr>";
echo $header;

echo "<form method=\"post\" action=\"admin_aws_manages_upcoming_aws_submit.php\">";
echo '<button type="submit" 
    value="schedule" name="response" value="Reschedule">Reschedule All</button>';
echo "</form>";
echo '<br>';

// This is used to group slots.
$weekDate = $schedule[0]['date'];
foreach( $schedule as $upcomingAWS )
{
    // When a new group of AWS starts, create a new table.
    if( $weekDate != $upcomingAWS['date'] )
    {
        // New wee starts.
        echo "</table>";
        echo "<br>";
        echo "<table class=\"show_schedule\">";
        $weekDate = $upcomingAWS[ 'date' ]; 
        echo $header;
    }

    $speaker = $upcomingAWS[ 'speaker' ];
    $pastAWSes = getAwsOfSpeaker( $speaker );

    // These are in descending ORDER.
    $lastAws = $pastAWSes[0];
    $lastAwsDate = $lastAws[ 'date' ];

    $nSecs = strtotime( $upcomingAWS['date'] ) - strtotime( $lastAwsDate );
    $nDays = $nSecs / (3600 * 24 );
    
    echo "<tr><td>";
    echo $speaker;
    echo "</td><td>";
    echo $upcomingAWS[ 'date' ];
    echo "</td><td>";
    echo $lastAws['date'];
    echo "</td><td>";
    echo "<font color=\"blue\"> $nDays </font>";
    echo "</td><td>";
    echo count( $pastAWSes);
    echo "</td>";

    // Create a form to approve the schedule.
    echo '<form method="post" action="admin_aws_manages_upcoming_aws_submit.php">';
    echo '<input type="hidden" name="speaker" value="' . $speaker . '" >';
    echo '<input type="hidden" name="date" value="' . $upcomingAWS['date'] . '" >';
    echo '<td style="background:white;border:0px;">
        <button name="response" value="Accept" >Accept</button>
        </td>';
    echo "</tr>";
    echo '</form>';
}
echo "</table>";

echo goBackToPageLink( "admin_aws.php", "Go back" );

?>
