<?php

include_once( "header.php" );
include_once( "methods.php" );
include_once( "database.php" );
include_once( "tohtml.php" );

echo userHTML( );

?>

<?php

$events = getEventsOfUser( $_SESSION['user'] );

$tofilter = Array( 'is_public_event', 'eid' );
foreach( $events as $event )
{
    $gid = $event['gid'];
    echo "<table class=\"request_edit\" >";
    echo "<tr>";
    echo "<td>" . arrayToTableHTML( $event, "request", $tofilter );
    echo '<form method="post" action="user_show_events_edit.php">';
    echo "</td><td><button name=\"response\" value=\"edit\">Edit</button>";
    echo "</td><td><button name=\"response\" value=\"cancel\">Cancel</button>";
    echo "</td></tr>";
    echo "</table>";
    echo "<input type=\"hidden\" name=\"gid\" value=\"$gid\">";
    echo '</form>';
}


echo '<br>';
echo '<div style="float:left">';
echo goBackToPageLink( "user.php", "Go back" );
echo '</div>';

?>
