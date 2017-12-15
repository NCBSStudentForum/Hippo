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

echo '<h1>Manage JC schedule</h1>';

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
$requests = getTableEntries( 'jc_requests', 'date'
    , "date>='$today' AND status='VALID' "
    );
echo '<table class="show_events">';
echo '<th>Request</th><th>Votes</th>';

foreach( $requests as $i => $req )
{
    echo '<tr>';
    echo '<td>';
    echo arrayToVerticalTableHTML( $req, 'info' );

    // Another form to delete this request.
    echo ' <form action="#" method="post" accept-charset="utf-8">';
    echo "<button name='response' onclick='AreYouSure(this)'
            title='Cancel this request'>TODO: Cancel</button>";
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
    $subTable = '<table class="info">';
    $subTable .= '<th>Index</th><th>Login ID</th><th>Name</th>
        <th>#Presentation</th><th>Last Presented On</th><th></th>';
    foreach( $subs as $i => $sub )
    {
        $subTable .= '<tr>';
        $login = $sub['login'];
        $info = getLoginInfo( $login );
        $name = arrayToName( $info );
        $email = mailto( $info[ 'email' ] );

        $presentations =  __get__( $presentationMap, $login, array() );
        $numPresentations = count( $presentations );

        $lastPresentedOn = 'NA';
        if( count( $presentations ) > 0 )
            $lastPresentedOn = humanReadableDate( $presentations[0]['date'] );


        $subTable .= '<td>' . ($i+1) . "</td><td> $login </td>
            <td>$name ($email) </td>";
        $subTable .= "<td> $numPresentations </td>";
        $subTable .= "<td> $lastPresentedOn </td>";

        $subTable .= '<form method="post" action="user_jc_admin_submit.php">';
        $subTable .= '<td>';
        $subTable .= '<input type="hidden" name="login" value="' . $login . '" />';
        $subTable .= '<input type="hidden" name="jc_id" value="' . $currentJC . '" />';
        $subTable .= '<button style="float:right;" onclick="AreYouSure(this)"
                              name="response" >' . $symbDelete . '</button>';
        $subTable .= '</td>';
        $subTable .= '</form>';

        $subTable .= '</tr>';
    }

    $subTable .= '</table>';
    echo '<h2>Subscription list of ' . $currentJC . '</h2>';
    echo $subTable;
}


echo goBackToPageLink( "user.php", "Go back" );

?>
