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
    echo "<h2>" . $mySub[ 'jc_id' ] . "</h2>";
    $jcID = $mySub['jc_id' ];
    echo '<h3>Upcoming Presentations </h3>';

    $upcomings = getUpcomingJCPresentationsOfUser( $_SESSION['user'], $jcID );
    if( $upcomings )
    {
        echo printInfo( "You have following upcoming presentation" );
        foreach( $upcomings as $upcoming )
        {
            echo ' <form action="user_manages_jc_update_presentation.php" method="post" accept-charset="utf-8">';
            echo dbTableToHTMLTable( 'jc_presentations', $upcoming, '', 'Edit' );
            echo '</form>';
        }
    }
    else
    {
        echo printInfo( "There is no upcoming presentation assigned to you" );
    }
}

echo '<h1> NBJC presentation requests </h1>';
echo printInfo( "Upvote the paper(s) you find interesting" );

$today = dbDate( 'today' );
$requests = getTableEntries( 'jc_requests', 'date'
    , "status='VALID' AND date >= '$today'"
    );
foreach( $requests as $req )
{
    echo arrayToVerticalTableHTML( $req, 'info' );
}

echo goBackToPageLink( 'user.php', 'Go Back' );

?>
