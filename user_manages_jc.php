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
    $buttonVal = 'Subscribe';
    if( isSubscribedToJC( $_SESSION['user'], $jc['id'] ) )
        $buttonVal = 'Unsubscribe';

    $table .= '<tr>';
    $table .= '<td>' . $jc['id'] . '</td>';
    $table .=  '<form action="user_manages_jc_action.php" method="post" accept-charset="utf-8">';
    $table .= "<td> <button name=\"response\" value=\"$buttonVal\">$buttonVal</button></td>";
    $table .= '<input type="hidden" name="jc_id" value="' . $jc['id'] . '" />';
    $table .= '<input type="hidden" name="login" value="' . $_SESSION['user'] . '" />';
    $table .= '</form>';
    $table .= '</tr>';
}
$table .= '</table>';
echo $table;

echo '<h1>Upcoming Presentations </h1>';

$mySubs = getUserJCs( $login = $_SESSION[ 'user' ] );
foreach( $mySubs as $mySub )
{
    $jcID = $mySub['jc_id' ];
    $upcomingPresentations = getUpcomingJCPresentations( $jcID );
    if( count( $upcomingPresentations ) > 0 )
    {
        foreach( $upcomingPresentations as $presentation )
        {
            echo arrayToTableHTML( $presentation, 'info' );
        }
    }
    else
    {
        echo printInfo( "No upcoming presentation is found in database!" );
    }
}

echo goBackToPageLink( 'user.php', 'Go Back' );

?>
