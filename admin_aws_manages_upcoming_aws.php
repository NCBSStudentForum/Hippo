<?php

include_once "header.php";
include_once "methods.php";
include_once "tohtml.php";
include_once "check_access_permissions.php";

mustHaveAllOfTheseRoles( array( "AWS_ADMIN" ) );

echo userHTML( );

echo "<h3>I've scheduled the AWS as follows</h3>";

if( isset( $_POST['response'] ) )
{
    $cwd = getcwd( );
    echo "I must reschedule";
    $scriptPath = $cwd . '/schedule_aws.py';
    echo printInfo("Executing $scriptPath, timeout 10 secs");
    $command = "timeout 10 python $scriptPath";
    $out = shell_exec( $command );
    print_r( $out );
}

$schedule = getTentativeAWSSchedule( );

echo "<table class=\"show_schedule\">";
$header = "<tr>
    <th>Speaker</th>
    <th>Scheduled On</th>
    <th>Last AWS on</th><th># Day</th> 
    <th>#AWS</th>
    </tr>";
echo $header;

echo "<form method=\"post\" action=\"admin_aws_manages_upcoming_aws.php\">";
echo '<button type="submit" 
    value="schedule" name="response">Reschedule</button>';
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
    echo $upcomingAWS[ 'speaker' ];
    echo "</td><td>";
    echo $upcomingAWS[ 'date' ];
    echo "</td><td>";
    echo $lastAws['date'];
    echo "</td><td>";
    echo "<font color=\"blue\"> $nDays </font>";
    echo "</td><td>";
    echo count( $pastAWSes);
    echo "</td></tr>";
}
echo "</table>";

echo goBackToPageLink( "admin_aws.php", "Go back" );

?>
