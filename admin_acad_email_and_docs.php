<?php

include_once 'header.php';
include_once 'database.php';
include_once 'tohtml.php';
include_once 'html2text.php';
include_once 'check_access_permissions.php';

mustHaveAllOfTheseRoles( array('AWS_ADMIN' ) );

echo userHTML( );

?>

<script type="text/javascript" charset="utf-8">
function ShowPlainEmail( button )
{
    var win = window.open('plain_email');
    win.document.write( "<pre>" + button.value + "</pre>" );
    win.select( );
}
</script>

<script type="text/javascript">
$(document).ready( function( ) {

});
</script>

<?php

/* Admin select the class of emails she needs to prepare. We remain on the same 
 * page for these tasks.
 */

$default = array( "task" => "upcoming_aws", "date" => dbDate( 'this monday' ) );
$options = array( 'This week AWS', 'This week events', 'Today\'s events' );

// Logic to keep the previous selected entry selected.
if( array_key_exists( 'response', $_POST ) )
{
    foreach( $_POST as  $k => $v )
    {
        $default[ $k ] = $v;
        if( $k == 'task' )
            $default[ $_POST[ $k ] ] = 'selected';
    }
}

// Construct user interface.
echo '
    <form method="post" action=""> <select name="task" id="list_of_tasks">';
foreach( $options as $val )
    echo "<option value=\"$val\" " . __get__( $default, $val, '') . 
        "> $val </option>";
echo '
    </select>
    <input type="text" class="datepicker" placeholder = "Select date" 
        title="Select date" value="' . $default[ 'date' ] . '" > 
    <button type="submit" name="response" title="select">' . $symbSubmit . '</button>
    </form>
    ';

if( $default[ 'task' ] == 'This week AWS' )
{
    $whichDay = $default[ 'date' ];
    $awses = getTableEntries( 'annual_work_seminars', 'date' , "date='$whichDay'" );
    $upcoming = getTableEntries( 'upcoming_aws', 'date' , "date='$whichDay'" );
    $awses = array_merge( $awses, $upcoming );
    $emailHtml = '';

    $filename = "AWS_" . $whichDay;
    foreach( $awses as $aws )
    {

        echo awsToTable( $aws, $with_picture = true );
        $emailHtml .= awsToTable( $aws, false );
        // Link to pdf file.
        echo awsPdfURL( $aws[ 'speaker' ], $aws[ 'date' ] );
        $filename .= '_' . $aws[ 'speaker' ];
    }

    $filename .= '.txt';

    $macros = array( 
        'DATE' => humanReadableDate( $awses[0]['date'] ) 
        ,  'EMAIL_BODY' => $emailHtml
        );

    $email = emailFromTemplate( 'aws_template', $macros );
    $md = html2Markdown( $emails );

    // Save the file and let the admin download it.
    file_put_contents( __DIR__ . "/data/$filename", $md);
    echo "<br><br>";
    echo '<table style="width:500px;border:1px solid"><tr><td>';
    echo downloadTextFile( $filename, 'Download mail' );
    echo "</td><td>";
    echo awsPdfURL( '', $whichDay, 'All AWS PDF' );
    echo "</td></tr>";
    echo '</table>';

} // This week AWS is over here.

// Only if the AWS date in future/today, allow admin to send emails.
if( strtotime( 'now' ) <= strtotime( $default[ 'date' ] ) )
{
    echo "TODO: Allow admin to send email";
}

echo goBackToPageLink( "admin_acad.php", "Go back" );


?>
