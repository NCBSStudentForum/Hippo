<?php

include_once 'header.php';
include_once 'tohtml.php';
include_once 'methods.php';
include_once 'database.php';

include_once 'check_access_permissions.php';
mustHaveAllOfTheseRoles( array( 'AWS_ADMIN' ) );

$logins = getLoginIds( );

$pendingRequests = getPendingAWSRequests( );

?>

<!-- Script to autocomplete user -->
<script>
$(function() {
    var logins = <?php echo json_encode( $logins ); ?>;
    //console.log( logins );
    $( "#autocomplete_user" ).autocomplete( { source : logins }); 
    $( "#autocomplete_user1" ).autocomplete( { source : logins }); 
});
</script>


<?php

echo userHTML( );

echo '<h2 align="left">AWS Admin</h2>';

echo '<table class="admin">';
echo '
    <tr>
        <td>Manage pending requests</td>
        <td> <a href="admin_aws_manages_requests.php">Manage ' . count( $pendingRequests) . 
            ' pending requests</a> </td>
    </tr><tr>
        <td>Add AWS entry</td>
        <td> <a href="admin_aws_add_aws_entry.php">Add AWS entry</td>
    </tr>
    </tr><tr>
        <td>Generate emails and documents</td>
        <td> <a href="admin_aws_email_and_docs.php">Emails and Documents</td>
    </tr>
    ';
echo '</table>';


echo "<h3>Scheduling</h3>";
echo '
  <table border="0" class="admin">
    <tr>
      <td>Manage upcoming AWS</td>
      <td><a href="admin_aws_manages_upcoming_aws.php">Manage
      upcoming AWSes</a></td>
    </tr>
  </table>';

echo '<h3>Modify USER and AWS entry</h3>';
echo '<table class="admin">';
echo '
    <tr>
    <td>Add, Update or Delete user <br>
        <small>Type a login name and press the button.</small>
    </td>
        <td>
            <form method="post" action="admin_aws_update_user.php">
            <input id="autocomplete_user" name="login" 
                placeholder="I will autocomplete " >
            <button 
                title="Add or remove speakers from AWS list"
                name="response" value="edit">' . $symbUpdate . '</button>
            </form>
        </td>
    </tr><tr>';
echo '<td>
    Enter a login and optionally AWS date and you can delete that AWS entry 
    from my database.</td>';
echo '<td> <form method="post" action="">';
echo '
    <input id="autocomplete_user1" name="login" placeholder="AWS Speaker" type="text" />
    AWS date (optional) <input class="datepicker" name="date" value="" >
    <button name="response" value="Select">' . $symbCheck . '</button>
    </form>
    </td>
    ';
echo '</table>';

$login = null;
$date = null;
if( isset( $_POST[ 'response' ] ))
{
    if( $_POST[ 'response' ] == 'Select' )
    {
        $login = $_POST[ 'login' ];
        $date = $_POST[ 'date' ];
    }

    else if( $_POST[ 'response' ] == 'DO_NOTHING' )
    {

    }
    else if( $_POST[ 'response' ] == 'delete' )
    {
        echo "Deleting this AWS entry.";
        $res = deleteAWSEntry( $_POST['speaker'], $_POST['date' ] );
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

    /* These AWS are upcoming */
    foreach( $awss as $aws )
    {
        if( ! $aws )
            continue;

        $speaker = $aws[ 'speaker' ];
        $date = $aws['date'];
        echo "<a>Entry for $speaker (" . loginToText( $speaker ) . ") on " . 
            date( 'D M d, Y', strtotime( $date ) ) . "</a>";

        echo arrayToVerticalTableHTML( $aws, 'annual_work_seminars' );
        echo '<br>';

        /* This forms remain on this page only */
        echo '<form method="post" action="">
            <input type="hidden" name="speaker" value="' . $speaker . '">
            <input type="hidden" name="date" value="' . $date . '" >
            <button onclick="AreYouSure(this)" 
                style=\"float:right\" name="response" >'. $symbDelete . '</button>
            </form>
            ';
    }
}

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

echo "<h2>Automatic Housekeeping</h2>";

$badEntries = doAWSHouseKeeping( );
if( count( $badEntries ) == 0 )
    echo printInfo( "AWS House is in order" );
else
{
    // can't make two forms on same page with same action. They will merge.
    echo alertUser( "Following entries could not be moved to main AWS list. Most
        likely these entries have no data. You need to fix them. " 
    );

    echo '<form action="./admin_aws_update_upcoming_aws.php" method="post">';
    foreach( $badEntries as $aws )
    {
        echo alertUser( "This AWS is incomplete." );
        echo arrayToVerticalTableHTML( $aws, 'info', '', 'status,comment' );
        echo '<input type="hidden" name="response" value="update" />';
        echo '<button name="id" value="' . $aws[ 'id' ] . '">Fix</button>';
    }
    echo '</form>';
}
    
echo goBackToPageLink( "user.php", "Go back" );

?>

