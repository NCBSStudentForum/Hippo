<?php

include_once "header.php";
include_once "methods.php";
include_once "tohtml.php";
include_once 'database.php';
include_once "check_access_permissions.php";

mustHaveAllOfTheseRoles( array( "AWS_ADMIN" ) );
echo userHTML( );

$allSpeakers = array_map( function( $x ) { return $x['login']; }, getAWSSpeakers( ) );
$upcomingAWSs = getUpcomingAWS( );

$alreadyHaveAWS = array_map( function( $x ) { return $x['speaker']; }, $upcomingAWSs );
$speakers = array_values( array_diff( $allSpeakers, $alreadyHaveAWS ) );

?>

<!-- Script to autocomplete user -->
<script type="text/javascript" charset="utf-8">
$(function() {
    var speakers = <?php echo json_encode( $speakers ); ?>;
    $( ".autocomplete_speaker" ).autocomplete( { source : speakers });
});
</script>


<?php

$upcomingAWSs = getUpcomingAWS( );
$upcomingAwsNextWeek = array( );
foreach( $upcomingAWSs as $aws )
    if( strtotime( $aws['date'] ) - strtotime( 'today' )  < 7 * 24 * 3600 )
        array_push( $upcomingAwsNextWeek, $aws );

echo '<h1>Next week Annual Work Seminar</h1>';
if( count( $upcomingAwsNextWeek ) < 1 )
    echo alertUser( "No AWS found." );
else
{
    $table = '<div style="font-size:small">';
    foreach( $upcomingAwsNextWeek as $upcomingAWS )
    {
        $table .= '<form action="admin_acad_manages_upcoming_aws_submit.php"
            method="post" accept-charset="utf-8">';
        $table .= '<table>';
        $table .= '<tr><td>';

        $table .= arrayToVerticalTableHTML( $upcomingAWS, 'aws'
            , '', array( 'id', 'status', 'comment' )
        );

        $table .= '<input type="hidden", name="date" , value="' .  $upcomingAWS[ 'date' ] . '"/>';
        $table .= '<input type="hidden", name="speaker" , value="' . $upcomingAWS[ 'speaker' ] . '"/>';
        $table .= '<input type="hidden", name="id" , value="' . $upcomingAWS[ 'id' ] . '"/>';
        $table .= '</td><td>';
        $table .= '<button name="response" value="Reassign">Reassign</button>';
        $table .= "<br>";
        $table .= '<button name="response" title="Edit/fromat the abstract"
               .     value="format_abstract">' . $symbEdit . '</button>';
        $table .= '<br>';
        $table .= '<button onclick="AreYouSure(this)" name="response"
               . title="Remove this entry from schedule" value="delete">' . $symbCancel . '</button>';
        $table .= '</td></tr>';
        $table .= '</table>';
        $table .= '</form>';
    }

    $table .= '</div>';
    echo $table;
}

echo "<h1>Upcoming approved AWSs</h1>";

echo '<div style="font-size:small">';
echo awsAssignmentForm( );
echo "</div>";

echo '<br /><br />';

$awsThisWeek = 0;
$awsGroupedByDate = array( );
foreach( $upcomingAWSs as $aws )
{
    $groupDate = strtotime( $aws['date'] );
    $awsGroupedByDate[ $groupDate ][] = $aws;
}

// AWS grouped by date.
$table = '<table class="infolarge">';
foreach( $awsGroupedByDate as $groupDate => $awses )
{
    $awsThisWeek = count( $awses );

    $table .= '<tr>';
    $table .= '<td>' . humanReadableDate( $groupDate ) . '</td>';

    // Show AWSes
    foreach( $awses as $countAWS => $aws )
    {
        // Speaker name and email
        $table .= '<td>';
        $table .= loginToText( $aws['speaker'], $withEmail = false ) . 
            ' (' .  $aws['speaker'] . ')<br />' ;

        $pi = getPIOrHost( $aws[ 'speaker' ] );
        $specialization = getSpecialization( $aws[ 'speaker' ], $pi );

        // Speaker PI if any.
        $table .=  '<br />' . piSpecializationHTML( $pi, $specialization );

        // Check if user has requested AWS schedule and has it been approved.
        // If user request for rescheduling was approved, print it here.
        $request = getTableEntry(
            'aws_scheduling_request'
            , 'speaker,status'
            , array( 'status' => 'APPROVED', 'speaker' => $aws[ 'speaker' ])
        );

        if( $request )
            $table .= preferenceToHtml( $request );

        $table .= '<form action="admin_acad_manages_upcoming_aws_submit.php" method="post" accept-charset="utf-8">';
        $table .= '<input type="hidden", name="date" , value="' . $aws[ 'date' ] . '"/>';
        $table .= '<input type="hidden", name="speaker" , value="' . $aws[ 'speaker' ] . '"/>';
        $table .= '<button name="response" onclick="AreYouSure(this)"
                    title="Delete this entry" >' . $symbDelete . '</button>';
        $table .= '</form>';

        if( $aws[ 'acknowledged' ] == 'NO'  )
            $table .= "<blink><p class=\"note_to_user\">Acknowledged: " 
                . $aws[ 'acknowledged' ] . "</p></blink>";

        $table .= '</td>';
    }

    if( $awsThisWeek < 3 )
        $table .= '<td>' . awsAssignmentForm( dbDate( $groupDate ), true ) . '</td>';

    $table .= '</tr>';
}
$table .= '</table>';
echo $table;

echo goBackToPageLink( "admin_acad.php", "Go back" );

echo "<h1>Temporary assignments</h1>";

echo "<form method=\"post\" action=\"admin_acad_manages_upcoming_aws_submit.php\">";
echo '<button name="response" value="Reschedule">Recompute Schedule</button>';
echo "</form>";


echo alertUser( 'Following is the best possible schedule I was able to
    come up. Press <button disabled>' . $symbAccept . '</button> to accept the 
    a speaker for given slot.' );

$schedule = getTentativeAWSSchedule( );
$scheduleMap = array( );
foreach( $schedule as $sch )
    $scheduleMap[ $sch['date'] ][ ] = $sch;

$header = "<tr>
    <th>Speaker</th>
    <th>Scheduled On</th>
    <th>Last AWS on</th><th># Day</th>
    <th>#AWS</th>
    </tr>";

echo '<br>';

// This is used to group slots.
$weekDate = $schedule[0]['date'];

$csvdata = array( "Speaker,Scheduled on,Last AWS on,Days since last AWS,Total AWS so far" );

$allDates = array( );
foreach( $scheduleMap as $date => $schedule )
{
    // check if this date is one week away from previous date.
    if( count( $allDates ) > 0 )
    {
        $prevDate = end( $allDates );
        $noAWSWeeks =  (strtotime( $date ) -  strtotime( $prevDate )) / 7 / 24 / 3600;
        for( $i = $noAWSWeeks - 1; $i > 0; $i-- )
        {
            $nextDate = humanReadableDate( strtotime( $date ) - $i*7*24*3600 );
            echo printWarning( "No AWS is scheduled for '$nextDate'." );
        }
    }
    $allDates[ ] = $date;
    
    // Show table.
    $table = '<table class="show_schedule">';
    foreach( $schedule as $i => $upcomingAWS )
    {
        $table .= $header;

        $table .= '<tr>';
        $csvLine = '';

        $speaker = $upcomingAWS[ 'speaker' ];
        $speakerInfo = getLoginInfo( $speaker );

        $pastAWSes = getAwsOfSpeaker( $speaker );

        // Get PI/HOST and speaker specialization.
        $pi = getPIOrHost( $speaker );
        $specialization = getSpecialization( $speaker, $pi );

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
        $speakerInfo = loginToText( $speaker, false ) . " ($speaker)";
        $csvLine .= loginToText( $speaker, true ) . ',';

        $table .= "<tr><td>";
        $table .= $speakerInfo;

        // Add PI and specialization info.
        $table .= piSpecializationHTML( $pi, $specialization );

        $intranetLink = getIntranetLink( $speaker );

        $table .= "<br /> $intranetLink ";
        $table .= '<form action="admin_acad_manages_upcoming_aws_submit.php"
            method="post" accept-charset="utf-8">
            <input type="hidden" name="speaker" value="' . $speaker . '" />
            <button name="response"  value="RemoveSpeaker" 
                title="Remove this speaker from AWS speaker list" >'
                . $symbDelete . '</button>';
        $table .= '</form>';

        // Check if user has requested AWS schedule and has it been approved.
        $request = getTableEntry(
            'aws_scheduling_request'
            , 'speaker,status'
            , array( 'status' => 'APPROVED', 'speaker' => $upcomingAWS[ 'speaker' ])
        );

        // If user request for rescheduling was approved, print it here.
        if( $request )
            $table .= preferenceToHtml( $request );

        $table .= "</td><td>";
        $table .= humanReadableDate( $upcomingAWS[ 'date' ] );

        $csvLine .= $upcomingAWS['date'] . ',';

        $table .= "</td><td>";
        $table .= $lastAwsDate;
        $csvLine .= $lastAwsDate . ',';

        if( count( $pastAWSes) == 0 )
            $table .= "<br><small>Joining date</small>";
        $table .= "</td><td>";
        $table .= "<font color=\"blue\"> $nDays </font>";
        $csvLine .= $nDays;

        $table .= "</td><td>";
        $table .= count( $pastAWSes);

        $table .= "</td>";

        // Create a form to approve the schedule.
        $table .= '<form method="post" action="admin_acad_manages_upcoming_aws_submit.php">';
        $table .= '<input type="hidden" name="speaker" value="' . $speaker . '" >';
        $table .= '<input type="hidden" name="date" value="' . $upcomingAWS['date'] . '" >';
        $table .= '<td style="background:white;border:0px;">
            <button name="response" title="Confirm this slot"
            value="Accept" >' . $symbAccept . '</button>
            </td>';
        $table .= "</tr>";
        $table .= '</form>';

        array_push( $csvdata, $csvLine );

        $table .= '</tr>';
    }
    $table .= '</table>';

    // show table.
    echo $table;
    echo '<br />';
}

$csvText = implode( "\n", $csvdata );

$upcomingAWSScheduleFile = 'upcoming_aws_schedule.csv';

$res = saveDataFile( $upcomingAWSScheduleFile, $csvText );

if( $res )
    echo downloadTextFile(  $upcomingAWSScheduleFile, "Download schedule" );

echo '<br><br>';
echo goBackToPageLink( "admin_acad.php", "Go back" );

?>
