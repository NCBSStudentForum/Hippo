<?php

include_once 'header.php';
include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );

// If current user does not have the privileges, send her back to  home
// page.
if( ! isJCAdmin( $_SESSION[ 'user' ] ) )
{
    echo printWarning( "You don't have permission to access this page" );
    echo goToPage( "user.php", 2 );
    exit;
}

// Otherwise continue.
$jcs = getJCForWhichUserIsAdmin( $_SESSION['user'] );
$jcIds = array_map( function( $x ) { return $x['jc_id']; }, $jcs );
$jcSelect = arrayToSelectList( 'jc_id', $jcIds, array(), false, $jcIds[0] );


$allPresentations = getAllPresentationsBefore( 'today' );

// Use presenter as key.
$presentationMap = array( );
foreach( $allPresentations as $p )
{
    $presentationMap[ $p['presenter'] ][] = $p;
}

// Get all upcoming presentation for all JCs I am an admin.
$upcomingJCs = array( );
foreach( $jcIds as $jc_id )
{
    $today = dbDate( 'today' );
    $upcoming = getTableEntries(
        'jc_presentations'
        , 'date'
        , "date >= '$today' AND status='VALID' AND jc_id='$jc_id'"
    );
    $upcomingJCs[ $jc_id ] = $upcoming;
}

echo '<h1>Schedule JC presentations</h1>';
// Manage presentation.
echo printInfo( 'Assign a presentation manually.' );

$table = '<table>';
$table .= '<tr>';
$table .= '<td> <input class="datepicker" name="date"
    placeholder="pick date" /> </td>';
$table .= '<td> <input name="presenter" placeholder="login id" /> </td>';
$table .= "<td> $jcSelect </td>";
$table .= '<td><button name="response" value="Assign Presentation">
    Assign</button></td>';
$table .= '</tr></table>';

echo '<form action="user_jc_admin_submit.php" method="post">';
echo $table;
echo '</form>';

// For each JC for which user is admin, show the latest entry for editing.
// NOTE: We assume that arrays are sorted according to DATE.
echo printInfo( 'Ideally the presenter should update this entry. If presenter
    has sent data over email then you need to edit this entry by yourself. Press
    <button disabled>' .  $symbEdit . '</button> below  to update.'
);

echo '<table>';
echo '<tr>';
foreach( $upcomingJCs as $jcID => $upcomings )
{
    if( count( $upcomings ) <  1 )
        continue;

    echo '<td>';
    echo "<h3>Next week entry for $jcID </h3>";
    echo ' <form action="user_jc_admin_edit_upcoming_presentation.php"
        method="post" accept-charset="utf-8">';
    echo dbTableToHTMLTable( 'jc_presentations', $upcomings[0], '', 'Edit' );
    echo '</form>';
    echo '</td>';
}
echo '</tr>';
echo '</table>';


// Show current schedule.
echo '<h2> Upcoming JC schedule </h2>';
echo '<table class="info">';
foreach( $upcomingJCs as $jcID => $upcomings )
{
    foreach( $upcomings as $i => $upcoming )
    {
        echo '<tr>';
        echo '<form method="post" action="user_jc_admin_submit.php">';
        echo arrayToRowHTML( $upcoming, 'info', 'title,description,status,url',false );
        echo '<td> <button name="response" value="Remove Presentation"
                    title="Remove this schedule" >' . $symbDelete . '</button></td>';

        echo "<input type='hidden' name='id' value='"
                . $upcoming['id'] . "' />";
        echo '</form>';
        echo '</tr>';
    }
}
echo '</table>';

echo '<h1>List of presentation requests</h1>';
echo printInfo( 'You can reschedule or cancel the request. Please let the
    requester know before doing anything evil.'
);

$requests = getTableEntries( 'jc_requests', 'date'
    , "date>='$today' AND status='VALID' "
    );
echo '<table class="info">';
echo '<th>Request</th><th>Votes</th>';

foreach( $requests as $i => $req )
{
    echo '<tr>';
    echo '<td>';
    echo arrayToVerticalTableHTML( $req, 'info', '', 'id,status' );

    // Another form to delete this request.
    echo ' <form action="user_jc_admin_edit_jc_request.php" method="post">';
    echo "<button name='response' onclick='AreYouSure(this)'
            title='Cancel this request'>Cancel</button>";
    echo "<button name='response' title='Reschedule' value='Reschedule'>Reschdule</button>";
    echo "<input type='hidden' name='id' value='" . $req[ 'id' ] . "' />";
    echo '</form>';
    echo "</td>";

    $votes = count( getVotes( "jc_requests." .  $req['id'] ) );
    echo "<td> $votes </td>";

    echo '</tr>';
}
echo '</table>';



echo "<h1>Manage subscriptions</h1>";

// Show table and task here.
$form = '<form method="post" action="user_jc_admin_submit.php">';
$form .= '<input type="text" name="logins" placeholder="ram,shyam,jack" />';
$form .= $jcSelect;
$form .= ' <button name="response" value="Add">Add Subscription</button>';
$form .= '</form>';
echo $form;

foreach( $jcIds as $currentJC )
{
    $subs = getJCSubscriptions( $currentJC );
    $allEmails = array( );

    // Create a subscription table.
    $allSubs = array( );
    foreach( $subs as $i => $sub )
    {
        $login = $sub['login'];
        $info = getLoginInfo( $login );
        $email = mailto( $info[ 'email' ] );
        $allEmails[] = $info['email'];

        $presentations =  __get__( $presentationMap, $login, array() );
        $numPresentations = count( $presentations );

        $lastPresentedOn = '0';
        if( count( $presentations ) > 0 )
            $lastPresentedOn = humanReadableDate( $presentations[0]['date'] );

        $row = array(
            'Name' => arrayToName( $info )
            , 'Email' => $email
            , 'PI/HOST' => getPIOrHost( $login )
            , '#Presentations' => $numPresentations
            , 'Last Presented On' => humanReadableDate( $lastPresentedOn )
            //, 'Months On Campus' => diffDates( 'today', $info['joined_on'], 'month' )
        );
        $allSubs[] = $row;
    }


    // Sort by last presented on.
    sortByKey( $allSubs, 'Last Presented On' );

    $subTable = '<table class="sortable info">';
    $subTable .= arrayToTHRow( $allSubs[0], 'sorttable', '' );
    foreach( $allSubs as $i => $sub )
    {
        $subTable .= '<form method="post" action="user_jc_admin_submit.php">';
        $subTable .= '<tr>';
        $subTable .= arrayToRowHTML( $sub, 'sorttable', '', false );
        $subTable .= '<input type="hidden" name="login" value="' . $login . '" />';
        $subTable .= '<input type="hidden" name="jc_id" value="' . $currentJC . '" />';
        $subTable .= '<td>';
        $subTable .= '<button style="float:right;" onclick="AreYouSure(this)"
                              name="response" >' . $symbDelete . '</button>';
        $subTable .= '</td>';
        $subTable .= '</tr>';
        $subTable .= '</form>';
    }
    $subTable .= '</table>';

    echo '<h2>Subscription list of ' . $currentJC . '</h2>';
    echo printInfo( 'Total subscriptions: ' . count( $allSubs ) . '.' );

    // Link to write to all members.
    if( count( $allEmails ) > 0 )
    {
        $mailtext = implode( ",", $allEmails );
        echo '<div>' .  mailto( $mailtext, 'Send email to all subscribers' ) . "</div>";
    }
    echo $subTable;
}


// Rate tasks.
echo '<h1>Rare tasks</h1>';
echo '
    <form action="user_jc_admin_edit_jc_request.php"
        method="post" accept-charset="utf-8">

    <table border="0">
        <tr>
            <td> <input type="text" name="new_admin"
                placeholder="email or login id"
                id="" value="" />
            </td>
            <td>' .  $jcSelect . '</td>' .
            '</td>
            <td>
                <button type="submit" name="response"
                value="transfer_admin">
                    Transfer Admin Rights</button>
            </td>
        </tr>
    </table>

    </form>';

echo '<br />';
echo '<br />';
echo goBackToPageLink( "user.php", "Go back" );

?>
