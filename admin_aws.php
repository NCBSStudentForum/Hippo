<?php

include_once 'header.php';
include_once 'tohtml.php';
include_once 'methods.php';
include_once 'database.php';
include_once 'check_access_permissions.php';

$logins = getLoginIds( );

$pendingRequests = getPendingAWSRequests( );

?>

<!-- Script to autocomplete user -->
<script>
$(function() {
    var logins = <?php echo json_encode( $logins ); ?>;
    $( "#autocomplete_user" ).autocomplete( { source : logins }); 
});
</script>


<?php

mustHaveAllOfTheseRoles( array( 'AWS_ADMIN' ) );

echo userHTML( );

echo "<h2>AWS Admin</h2>";

echo '<table class="admin">';
echo '<tr>
        <td>Manage pending requests</td>
        <td> <a href="admin_aws_manages_requests.php">Manage ' . count( $pendingRequests) . 
            ' pending requests</a> </td>
    </tr><tr>
        <td>Manage speakers</td>
        <td> <a href="admin_aws_edit_speakers.php">Manage speakers </td>
    </tr>';
echo '</table>';

echo "<h3> Information</h3>";
echo '
  <table border="0" class="admin">
    <tr>
    <td>AWS summary
    <small>
        See the summary of all AWSs. You may be able to missing AWS entry in "Date Wise" list. 
    </small>
    </td>

      <td><a href="admin_aws_summary_user_wise.php">User wise</a>
      || <a href="admin_aws_summary_date_wise.php">Date
      wise</a></td>
    </tr>

  </table>';

echo "<h3>Scheduling</h3>";
echo '
  <table border="0" class="admin">
    <tr>
      <td>Manage upcoming AWS</td>
      <td><a href="admin_aws_manages_upcoming_aws.php">Manage
      upcoming AWSes</a></td>
    </tr>
  </table>';

echo '<h3> Danger zone</h3>';
echo '
    <form method="get" action="">
    Pick an login ID <input id="autocomplete_user" name="login" type="text" />
    Optionally select AWS date<input class="datepicker" name="date" value="" >
    <button name="response" value="Select">Select</button>
    </form>
    ';

$login = null;
$date = null;
if( isset( $_GET[ 'response' ] ))
{
    if( $_GET[ 'response' ] == 'Select' )
    {
        $login = $_GET[ 'login' ];
        $date = $_GET[ 'date' ];
    }
    else if( $_GET[ 'response' ] == 'Delete' )
    {
        echo "Deleting this AWS entry.";
        $res = deleteAWSEntry( $_GET['speaker'], $_GET['date' ] );
        if( $res )
        {
            echo printInfo( "Successfully deleted" );
            goToPage( 'admin_aws.php', 0);
            exit;
        }
    }

    $awss = array( );
    if( $login and $date )
        $awss = array( getMyAwsOn( $login, $date ) );
    else if( $login )
        $awss = getAwsOfSpeaker( $login );

    foreach( $awss as $aws )
    {
        $speaker = $aws[ 'speaker' ];
        $date = $aws['date'];
        echo "<a>Entry for $speaker (" . loginToText( $speaker ) . ") on " . 
            date( 'D M d, Y', strtotime( $date ) ) . "</a>";

        echo arrayToVerticalTableHTML( $aws, 'annual_work_seminars' );
        echo '<br>';
        echo '<form method="get" action="">
            <input type="hidden" name="speaker" value="' . $speaker . '">
            <input type="hidden" name="date" value="' . $date . '" >
            <button style=\"float:right\" name="response" value="Delete">Delete</button>
            </form>
            ';
    }
}


?>

