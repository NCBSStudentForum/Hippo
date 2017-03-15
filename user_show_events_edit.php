<?php 

include_once( "header.php" );
include_once( "methods.php" );
include_once( "database.php" );
include_once( "tohtml.php" );

//var_dump( $_POST );

$gid = $_POST['gid'];


if( strtolower($_POST['response']) == 'edit' )
{
    echo printInfo( 
        "You are editing an upcoming event. You can only modify 
        title, description, end time and class of the events."
        );
    $events = getEventsByGroupId( $gid );
    // We only edit once request and all other in the same group should get 
    // modified accordingly.
    $event = $events[0];
    echo "<form method=\"post\" action=\"user_show_events_edit_submit.php\">";
    echo dbTableToHTMLTable( 'events', $event
        , 'title,description,class,end_time' 
        );
    echo "<input type=\"hidden\" name=\"gid\" value=\"$gid\" />";
    echo "</form>";
}

else if( strtolower($_POST['response']) == 'delete' )
{
    $eid = trim( $_POST[ 'eid' ] );

    $gText = "$gid . $eid";

    if( ! $eid )
        $res = updateTable( 'events', 'gid,created_by', 'status',
                        array( 'gid' => $gid, 'created_by' => $_SESSION[ 'user'] 
                                , 'status' => 'CANCELLED' 
                            )
                        );
    else
        $res = updateTable( 'events', 'gid,eid,created_by', 'status',
                        array( 'gid' => $gid, 'created_by' => $_SESSION[ 'user'] 
                                , 'status' => 'CANCELLED' 
                                , 'eid' => $eid
                            )
                        );

    if( $res )
    {
        echo printInfo( "Successfully cancelled event $gText" );
        //goToPage( "user_show_events.php", 0 );
        //exit;
    }
    else
        echo printWarning( "Could not cancel event $gText" );

}
else if( $_POST['response'] == "DO_NOTHING" )
{
    echo printInfo( "User cancelled. Going back" );
    goBack( "user_show_events.php" );
    exit;
}
else
{
    echo printWarning( "Bad response " .  $_POST['response']  );
}

echo goBackToPageLink( "user_show_events.php", "Go back");
