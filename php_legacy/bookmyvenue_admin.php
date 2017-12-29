<?php

// This is admin interface for book my venue.
// We are here to manage the requests.
include_once "header.php";
include_once "methods.php";
include_once "database.php";
include_once "tohtml.php";
include_once "check_access_permissions.php";

mustHaveAnyOfTheseRoles( array( 'BOOKMYVENUE_ADMIN' ) );

echo userHTML( );

echo bookmyVenueAdminTaskTable( );

echo '<h1> Pending requests </h1>';


$requests = getPendingRequestsGroupedByGID( );

if( count( $requests ) == 0 )
    echo printInfo( "Cool! No request is pending for review" );
else
    echo printInfo( "These requests needs your attention" );

$html = '<div style="font-size:small">';
$html .= '<table class="show_request">';

$tohide = 'last_modified_on,status,modified_by,timestamp,url,external_id,gid,rid';
foreach( $requests as $r )
{
    // If request date has passed, ignore it.
    if( strtotime( $r[ 'date' ] ) < strtotime( '-2 days' ) )
    {
        // Do not show requests which are more than 1 days old. Their status
        // remains PENDING all the time. Dont know what to do such
        // unapproved/expired requests.
        continue;
    }

    $html .= '<form action="bookmyvenue_admin_request_review.php" method="post">';
    $html .= '<tr><td>';
    // Hide some buttons to send information to next page.
    $html .= '<input type="hidden" name="gid" value="' . $r['gid'] . '" />';
    $html .= '<input type="hidden" name="rid" value="' . $r['rid'] . '" />';

    // If a request is coming from talk, use different background.
    $color = 'white';
    if( strpos( $r[ 'external_id'], 'talks.' ) !== false )
        $color = 'yellow';

    $html .= arrayToTableHTML( $r, 'events', $color,  $tohide );
    $html .= '</td>';
    $html .= '<td style="background:white">
        <button name="response" value="Review" title="Review request"> ' .
            $symbReview . '</button> </td>';
    $html .= '</tr>';
    $html .= '</form>';
}
$html .= '</table>';
$html .= "</div>";
echo $html;
echo goBackToPageLink( "user.php", "Go back" );

?>

<h1>Upcoming (approved) events in next 4 weeks </h1>

<?php

// Let admin search.
echo '<form action="" method="post" accept-charset="utf-8">
    <input name="query" value="" placeholder="Search using creator or title"></input>
    <button type="submit" name="response" value="search">Search</button>
</form>';

if( __get__( $_POST, 'response', '' ) == 'search' )
{
    $query = trim( $_POST[ 'query' ] );
    if( trim( $query ) )
    {
        $day = dbDate( 'yesterday' );
        $events = getTableEntries( 'events', 'date'
            , "status='VALID' AND date >= '$day' AND
                (created_by='$query' OR title LIKE '%$query%')"
        );
    }
    else
        $events = getEventsBeteen( 'today', '+2 week' );
}
else
    $events = getEventsBeteen( 'today', '+2 week' );


if( count( $events ) > 0 )
{
    $html = '<div style="font-size:small;">';
    $event = $events[0];
    $html .= "<table class=\"show_events\">";

    $tofilter = 'eid,calendar_id,calendar_event_id' .
        ',external_id,gid,last_modified_on,status,url';


    // Add extra field to create one last row.
    $html .= arrayHeaderRow( $event, 'show_events', $tofilter );

    foreach( $events as $event )
    {
        // Today's event if they are passed, don't display them.
        if( $event[ 'date' ] == dbDate( 'today' ) && $event[ 'start_time'] < dbTime( 'now' ) )
            continue;

        $gid = $event['gid'];
        $eid = $event['eid'];
        $html .= "<tr><form method=\"post\" action=\"bookmyvenue_admin_edit.php\">";
        $event[ 'edit' ] = "<td> <button title=\"Edit this entry\"  name=\"response\"
                value=\"edit\">" . $symbEdit .  "</button></td>";

        $html .= arrayToRowHTML( $event, 'events', $tofilter );

        $html .= "<input name=\"gid\" type=\"hidden\" value=\"$gid\" />";
        $html .= "<input name=\"eid\" type=\"hidden\" value=\"$eid\" />";
        $html .= "</td></form>";
        $html .= "</tr>";
    }

    $html .= "</table>";
    $html .= "</div>";
    echo $html;
}

echo goBackToPageLink( "user.php", "Go back" );

?>
