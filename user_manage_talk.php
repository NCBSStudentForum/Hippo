<?php

include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'USER' ) );

include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );

// Logic for POST requests.
$speaker = array( 
    'first_name' => '', 'middle_name' => '', 'last_name' => '', 'email' => ''
    , 'department' => '', 'institute' => '', 'title' => '', 'id' => ''
    , 'homepage' => ''
    );

$whereExpr = "created_by='" . $_SESSION[ 'user' ] . "'";
$whereExpr .= "AND status!='INVALID'";
$talks = getTableEntries( 'talks', '', $whereExpr );
if( count( $talks ) < 1 )
{
    echo printInfo( "You don't have any upcoming or unscheduled talk" );
}

foreach( $talks as $t )
{
    // Check if this talk has already been approved or in pending approval.
    $event = getTableEntry( 'events', 'external_id,status'
        , array( 'external_id' => 'talks.' . $t[ 'id' ], 'status' => 'VALID' )
        );
    $request = getTableEntry( 'bookmyvenue_requests', 'external_id,status'
        , array( 'external_id' => 'talks.' . $t[ 'id' ], 'status'  => 'PENDING' )
        );

    echo '<form method="post" action="user_manage_talks_action.php">';
    echo '<table border="0">';
    echo '<tr>';
    echo arrayToTableHTML( $t, 'info', '', 'created_by,status');
    echo '</tr><tr>';
    echo '
        <input type="hidden" name="id" value="' . $t[ 'id' ] . '" />
        <td><button onclick="AreYouSure(this)" name="response" 
            title="Delete this entry" >' . $symbDelete . '</button></td>';

    // If either a request of event is found, don't let user schedule the talk. 
    // She can edit the request/event.
    if( ! ($request || $events) )
        echo '<td><button style="float:right" title="Schedule this talk" 
        name="response" value="schedule">' . $symbCalendar . '</button></td>';
    else
        echo '<td></td>';

    echo '<td><button style="float:right" title="Edit this entry"
            name="response" value="edit">' . $symbEdit . '</button></td>';

    echo '</tr></table>';
    echo '</form>';

    if( $event )
    {
        echo "<strong>Following talk has been confirmed</strong>";
        $html = arrayToTableHTML( $request, 'event', ''
            , 'external_id,url,modified_by,timestamp' );
        echo $html;
    }


    if( $request )
    {
        echo "<strong>Booking request for above talk is pending review</strong>";
        $gid = $request[ 'gid' ];

        echo arrayToTableHTML( $request, 'requests', ''
            , 'external_id,url,modified_by,status,timestamp' );

        echo '<form method="post" action="user_show_requests_edit.php">';
        echo "<table class=\"show_requests\"><tr>";
        echo "<td><button name=\"response\" title=\"Cancel this request\"
            value=\"cancel\"> $symbCancel </button></td>";
        echo "<td style=\"float:right\">
            <button name=\"response\" title=\"Edit this request\"
            value=\"edit\"> $symbEdit </button></td>";
        echo "</tr></table>";
        echo "<input type=\"hidden\" name=\"gid\" value=\"$gid\">";
        echo '</form>';
    }

}
    
echo goBackToPageLink( "user.php", "Go back" );

?>
