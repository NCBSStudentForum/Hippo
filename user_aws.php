<?php 

include_once( "header.php" );
include_once( "methods.php" );
include_once( 'tohtml.php' );
include_once( "check_access_permissions.php" );

mustHaveAnyOfTheseRoles( Array( 'USER' ) );

echo userHTML( );

echo "<h3>Manage upcoming Annual Work Seminar (AWS)</h3>";
echo "TODO";

echo "<h3>Past Annual Work Seminar</h3>";

$awses = getMyAws( $_SESSION['user'] );

foreach( $awses as $aws )
{
    $id = $aws['id'];
    echo "<div>";
    echo "<form method=\"post\" action=\"user_aws_edit.php\">";
    echo arrayToVerticalTableHTML( $aws, 'aws', NULL
    , Array( 'speaker', 'tentatively_scheduled_on', 'id' ));
    echo "<button class=\"submit\" name=\"response\" value=\"edit\">Edit</button>";
    echo "<input type=\"hidden\" name=\"id\" value=\"$id\" />";
    echo "</form>";
    echo "<br /><br />";
    echo "</div>";
}

echo "<h3>Add a missing entry</h3>";
echo "<table class=\"show_user\">";
echo '<tr><td>You can add a missing entry here (TODO: Make it available till we 
    collect all data)
    </td><td> <a href="' . appRootDir() .  '/user_aws_edit.php">Add previous 
    AWS</a>';
echo "</td></tr>";
echo "</table>";


echo goBackToPageLink( "user.php", "Go back" );

?>
