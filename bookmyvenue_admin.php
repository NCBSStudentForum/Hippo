<?php 

// This is admin interface for book my venue.
// We are here to manage the requests.
include_once "header.php";
include_once "methods.php";
include_once "database.php";
include_once "tohtml.php";
include_once "check_access_permissions.php";

echo userHTML( );

if( ! requiredPrivilege( 'BOOKMYVENUE_ADMIN' ) )
{
    echo printWarning( "You don't have enough privileges to user this interface" );
    goToPage( "user.php", 3 );
    exit( 0 );
}


echo '<h2> Calendar administration </h2>';

echo '<table class="show_user">
    <tr>
        <td>Synchronize public calendar</td>
        <td>
            <a href="bookmyvenue_admin_synchronize_events_with_google_calendar.php">
            Synchronize public calendar </a> 
        </td>
    </tr>
    </table>
    ';

echo '<h2> Pending requests </h2>';
$requests = getPendingRequestsGroupedByGID( ); 
if( count( $requests ) == 0 )
    echo printInfo( "Cool! No request is pending for review" );

?>

<form action="bookmyvenue_admin_request_review.php" method="post" accept-charset="utf-8">
<?php echo requestsToHTMLReviewForm( $requests ); ?>
</form>


<h2> Edit Upcoming Events </h2>
<?php
$html = '';
$events = getEventsGrouped( $sortby = 'date' );

$html .= "<table>";
foreach( $events as $event )
{
    $gid = $event['gid'];
    $eid = $event['eid'];
    $html .= "<form method=\"post\" action=\"bookmyvenue_admin_edit.php\">";
    $html .= "<tr><td>";
    $html .= arrayToTableHTML( $event, 'events', ''
        , Array( 'eid', 'calendar_id' , 'calendar_event_id' ) 
    );
    $html .= "</td>";
    $html .= "<td> <button name=\"response\" value=\"edit\">Edit</button></td>";
    $html .= "<input name=\"gid\" type=\"hidden\" value=\"$gid\" />";
    $html .= "<input name=\"eid\" type=\"hidden\" value=\"$eid\" />";
    $html .= "</form></tr>";
}

$html .= "</table>";
echo $html;

echo goBackToPageLink( "user.php", "Go back" );

?>

