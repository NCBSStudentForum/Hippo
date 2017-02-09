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
    <td>
    To synchronize public calendar make sure you are logged-in using correct 
    google account
    </strong>
    </td>
        <td>
            <a href="bookmyvenue_admin_synchronize_events_with_google_calendar.php">
            Synchronize public calendar </a> 
        </td>
    </tr>
    </table>
    ';

echo '<h3> Pending requests </h3>';
$requests = getPendingRequestsGroupedByGID( ); 

if( count( $requests ) == 0 )
    echo printInfo( "Cool! No request is pending for review" );


$html = '<table>';
foreach( $requests as $r )
{
    $html .= '<form action="bookmyvenue_admin_request_review.php" method="post">';
    $html .= '<tr><td>';
    // Hide some buttons to send information to next page.
    $html .= '<input type="hidden" name="gid" value="' . $r['gid'] . '" />';
    $html .= '<input type="hidden" name="rid" value="' . $r['rid'] . '" />';
    $html .= arrayToTableHTML( $r, 'events'
        , ' ',  array( 'status', 'modified_by', 'timestamp', 'url' ) 
    );
    $html .= '</td>';
    $html .= '<td style="background:white">
                    <button name="response" value="Review">Review</button>
            </td>';
    $html .= '</tr>';
    $html .= '</form>';
}
$html .= '</table>';
echo $html;

?>

<h3> Edit Upcoming Events </h3>
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

