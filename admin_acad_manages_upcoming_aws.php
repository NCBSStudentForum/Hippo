<?php

include_once "header.php";
include_once "methods.php";
include_once "tohtml.php";
include_once 'database.php';
include_once "check_access_permissions.php";

$speakers = getLoginIds( );

?>

<!-- Script to autocomplete user -->
<script type="text/javascript" charset="utf-8">
$(function() {
    var speakers = <?php echo json_encode( $speakers ); ?>;
    $( "#autocomplete_speaker" ).autocomplete( { source : speakers }); 
});
</script>


<?php

mustHaveAllOfTheseRoles( array( "AWS_ADMIN" ) );

echo userHTML( );


echo '<h3>AWS for next week</h3>';

$upcomingAWSs = getUpcomingAWS( );
$upcomingAwsNextWeek = array( );
foreach( $upcomingAWSs as $aws )
    if( strtotime( $aws['date'] ) - strtotime( 'today' )  < 7 * 24 * 3600 )
        array_push( $upcomingAwsNextWeek, $aws );

foreach( $upcomingAwsNextWeek as $upcomingAWS )
{
    echo '<div style="font-size:small">';
    echo '<form action="admin_acad_manages_upcoming_aws_submit.php"
        method="post" accept-charset="utf-8">';
    echo '<table>';
    echo '<tr><td>';

    echo arrayToTableHTML( $upcomingAWS, 'aws' 
        , '', array( 'id', 'status', 'comment' )
    );

    echo '<input type="hidden", name="date" , value="' .  $upcomingAWS[ 'date' ] . '"/>';
    echo '<input type="hidden", name="speaker" , value="' . $upcomingAWS[ 'speaker' ] . '"/>';
    echo '<input type="hidden", name="id" , value="' . $upcomingAWS[ 'id' ] . '"/>';
    echo '</td><td>';
    echo '<button name="response" value="Reassign">Reassign</button>';
    echo "<br>";
    echo '<button name="response" title="Edit/fromat the abstract" 
                value="format_abstract">' . $symbEdit . '</button>';
    echo '<br>';
    echo '<button onclick="AreYouSure(this)" 
            name="response" title="Remove this entry from schedule" 
            value="delete">' . $symbCancel . '</button>';
    echo '</td></tr>';
    echo '</table>';
    echo '</form>';
    echo '</div>';
}


echo '<h3>Manually assign AWS</h3>';
echo printInfo( 
    'Here you can manually assign speakers to an AWS slot After assignment,
    ' );

echo '
    <form method="post" action="admin_acad_manages_upcoming_aws_submit.php">
    <table class="standout">
    <tr> <th>Pick a date</th> <th>Select speaker</th> <th></th> </tr>
    <tr>
        <td> <input class="datepicker"  name="date" value="" > </td>
        <td>
            <input id="autocomplete_speaker" name="speaker" 
                placeholder="I will autocomplete" />
        </td>
        <td> <button name="response" value="Assign">Assign</button> </td>
    </tr>
    </table>
    </form>
    ';

echo "<h3>Upcoming approved AWSs</h3>";
// Show the rest of entries grouped by date.
if( count(  $upcomingAWSs ) > 0 )
{
    $groupDate = strtotime( $upcomingAWSs[0]['date'] );
    echo '<table class="show_schedule">';
    echo '<tr> <td>' . $upcomingAWSs[0]['date'] . '</td>';
}

foreach( $upcomingAWSs as $aws )
{
    echo '<form action="admin_acad_manages_upcoming_aws_submit.php"
        method="post" accept-charset="utf-8">';
    if( strtotime( $aws['date']) != $groupDate )
    {
        $groupDate = strtotime( $aws['date'] );
        echo '</tr>';
        echo '<tr><td>' . $aws['date'] . '</td>';
    }
    echo '<td>';
    echo $aws['speaker'] . '<br>' . loginToText( $aws['speaker'], $withEmail = false );
    echo '<input type="hidden", name="date" , value="' . $aws[ 'date' ] . '"/>';
    echo '<input type="hidden", name="speaker" , value="' . $aws[ 'speaker' ] . '"/>';
    echo '<button name="response" value="delete" title="Delete this entry"
            >' . $symbDelete . '</button>';
    echo '</td>';
    echo '</form>';
}
echo '</tr></table>';

echo goBackToPageLink( "admin_acad.php", "Go back" );

echo "<h3>Temporary assignments </h3>";
echo '
    <p> Following table shows the best possible schedule I could 
    come up with for next 12 months starting today. Pressing <button 
    disabled>' . $symbAccept . '</button> will put them into upcoming
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

echo "<form method=\"post\" action=\"admin_acad_manages_upcoming_aws_submit.php\">";
echo '<button type="submit" name="response" value="Reschedule">Reschedule All</button>';
echo "</form>";

echo '<br>';

// This is used to group slots.
$weekDate = $schedule[0]['date'];

$csvdata = array( "Speaker,Scheduled on,Last AWS on,Days since last AWS,Total AWS so far" );
foreach( $schedule as $upcomingAWS )
{
    $csvLine = '';
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
    $speakerInfo = getLoginInfo( $speaker );

    $pastAWSes = getAwsOfSpeaker( $speaker );

    // This user may have not given any AWS in the past. We consider their 
    // joining date as last AWS date.
    if( count( $pastAWSes ) > 0 )
    {
        $lastAws = $pastAWSes[0];
        $lastAwsDate = $lastAws[ 'date' ];
    }
    else
        $lastAwsDate = date( 'Y-m-d', strtotime($speakerInfo[ 'joined_on']));

    $nSecs = strtotime( $upcomingAWS['date'] ) - strtotime( $lastAwsDate );
    $nDays = $nSecs / (3600 * 24 );
    $speakerInfo = $speaker . '<br>'. loginToText( $speaker, $withEmail = false );

    $csvLine .= loginToText( $speaker, true ) . ',';
    
    echo "<tr><td>";
    echo $speakerInfo;
    $intranetLink = getIntranetLink( $speaker );
    echo "<br>$intranetLink ";
    echo '<form action="admin_acad_manages_upcoming_aws_submit.php" 
            method="post" accept-charset="utf-8">
          <input type="hidden" name="speaker" value="' . $speaker . '" />
          <button name="response"  value="RemoveSpeaker"
                title="Remove this speaker from AWS speaker list" >' 
                . $symbDelete . '</button>';
    echo '</form>';
    echo "</td><td>";
    echo $upcomingAWS[ 'date' ];

    $csvLine .= $upcomingAWS['date'] . ',';

    echo "</td><td>";
    echo $lastAwsDate;
    $csvLine .= $lastAwsDate . ',';

    if( count( $pastAWSes) == 0 )
        echo "<br><small>Joining date</small>";
    echo "</td><td>";
    echo "<font color=\"blue\"> $nDays </font>";
    $csvLine .= $nDays;

    echo "</td><td>";
    echo count( $pastAWSes);

    echo "</td>";

    // Create a form to approve the schedule.
    echo '<form method="post" action="admin_acad_manages_upcoming_aws_submit.php">';
    echo '<input type="hidden" name="speaker" value="' . $speaker . '" >';
    echo '<input type="hidden" name="date" value="' . $upcomingAWS['date'] . '" >';
    echo '<td style="background:white;border:0px;">
        <button name="response" title="Confirm this slot" 
            value="Accept" >' . $symbAccept . '</button>
        </td>';
    echo "</tr>";
    echo '</form>';

    array_push( $csvdata, $csvLine );
}
echo "</table>";

$csvText = implode( "\n", $csvdata );

$upcomingAWSScheduleFile = 'upcoming_aws_schedule.csv';

$res = saveDataFile( $upcomingAWSScheduleFile, $csvText );

if( $res )
    echo downloadTextFile(  $upcomingAWSScheduleFile, "Download schedule" );

echo '<br><br>';
echo goBackToPageLink( "admin_acad.php", "Go back" );
?>
