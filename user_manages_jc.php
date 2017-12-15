<?php

include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'USER' ) );

include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );

$jcs = getJournalClubs( );

echo '<h1>Table of All Journal Clubs</h1>';
$table = '<table class="info">';
foreach( $jcs as $jc )
{
    $jcInfo = getJCInfo( $jc );

    $buttonVal = 'Subscribe';
    if( isSubscribedToJC( $_SESSION['user'], $jc['id'] ) )
        $buttonVal = 'Unsubscribe';

    $table .= '<tr>';
    $table .= '<td>' . $jc['id'] . '</td>';
    $table .= '<td>' . $jcInfo[ 'title' ] . '</td>';
    $table .=  '<form action="user_manages_jc_action.php"
        method="post" accept-charset="utf-8">';
    $table .= "<td> <button name=\"response\"
        value=\"$buttonVal\">$buttonVal</button></td>";
    $table .= '<input type="hidden" name="jc_id" value="' . $jc['id'] . '" />';
    $table .= '<input type="hidden" name="login" value="'
                .  $_SESSION['user'] . '" />';
    $table .= '</form>';
    $table .= '</tr>';
}
$table .= '</table>';
echo $table;

$mySubs = getUserJCs( $login = $_SESSION[ 'user' ] );
foreach( $mySubs as $mySub )
{
    echo "<h1>Upcoming presentations for " . $mySub[ 'jc_id' ] . "</h1>";

    echo printInfo( "Following presentations are fixed. If any of these
        presentation belongs to you, you can edit it by pressing the 'Edit' button. "
    );

    $jcID = $mySub['jc_id' ];
    $upcomings = getUpcomingJCPresentations( $jcID );
    echo '<table>';
    echo '<tr>';
    if( count( $upcomings ) > 0 )
    {
        foreach( $upcomings as $i => $upcoming )
        {
            echo '<td>';
            // If it is MINE then make it editable.
            if( $upcoming[ 'presenter' ] == whoAmI( ) )
            {
                echo ' <form action="user_manages_jc_update_presentation.php" 
                    method="post" accept-charset="utf-8">';
                echo dbTableToHTMLTable( 'jc_presentations', $upcoming, '', 'Edit' );
                echo '</form>';
            }
            else
                echo arrayToVerticalTableHTML( $upcoming, 'info', '', 'id,status' );
            echo '</td>';

            if( ($i+1) % 3  == 0 )
                echo '</tr><tr>';
        }
    }
    echo '</tr>';
    echo '</table>';
}


echo '<h1>JC presentation requests </h1>';

$today = dbDate( 'today' );
$requests = getTableEntries( 'jc_requests', 'date'
    , "status='VALID' AND date >= '$today'"
    );
if( count( $requests ) > 0 )
{
    echo printInfo(
        "Following presentation requests have been made. If you like any paper to
        be presented, please vote for it. JC coordinators only see the number of
        votes which might helps them breaking the tie if any.
        "
    );

    echo '<table>';
    foreach( $requests as $req )
    {
        echo '<tr>';
        echo '<td>';
        echo arrayToVerticalTableHTML( $req, 'info', '', 'id,status' );

        $voteId = "jc_requests." . $req['id'];
        $action = 'Add My Vote';
        if( getMyVote( $voteId ) )
            $action = 'Remove My Vote';

        echo '</td>';
        echo ' <form action="user_manages_jc_update_presentation.php" method="post" accept-charset="utf-8">';
        echo ' <input type="hidden" name="id" value="' . $voteId . '" />';
        echo ' <input type="hidden" name="voter" value="' . whoAmI( ) . '" />';
        echo "<td> <button name='response' value='$action'>$action</button></td>";
        echo '</form>';
        echo '</tr>';
    }
    echo '<table>';
}
else
{
    echo printInfo( "No presentation request has been made yet
        or the existing ones have been cancelled by users."
    );
}

echo goBackToPageLink( 'user.php', 'Go Back' );

?>
