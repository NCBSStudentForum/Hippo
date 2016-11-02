<?php 

include_once( "header.php" );
include_once( "methods.php" );
include_once( "database.php" );
include_once( "tohtml.php" );

//var_dump( $_POST );

$gid = $_POST['gid'];

$editable = Array( "short_description", "description" );

if( strtolower($_POST['response']) == 'edit' )
{
    echo "<p class=\"info\"> You can only change fields: " . implode( ", ", $editable ) 
        . " here. </p>";

    $events = getEventsByGroupId( $gid );
    // We only edit once request and all other in the same group should get 
    // modified accordingly.
    $event = $events[0];
    echo "<form method=\"post\" action=\"user_show_events_edit_submit.php\">";
    echo eventToEditableTableHTML( $event, $editable );
    echo "<input type=\"hidden\" name=\"gid\" value=\"$gid\" />";
    echo "<button class=\"submit\" name=\"response\" value=\"submit\">Submit</button>";
    echo "</form>";
}

else if( strtolower($_POST['response']) == 'cancel' )
{
    $res = changeStatusOfEventGroup( $_POST['gid'], $_SESSION['user'], 'CANCELLED' );
    if( $res )
    {
        echo printInfo( "Successfully cancelled event" );
        goToPage( "user_show_events.php", 0 );
    }
    else
        echo printWarning( "Could not cancel event " . $_POST['gid'] );

}
else
{
    echo printWarning( "Bad response " .  $_POST['response']  );
}

echo goBackToPageLink( "user_show_requests.php", "Go back");
