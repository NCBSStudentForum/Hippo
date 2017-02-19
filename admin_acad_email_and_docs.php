<?php

include_once 'header.php';
include_once 'database.php';
include_once 'tohtml.php';
include_once 'html2text.php';
include_once 'check_access_permissions.php';
mustHaveAllOfTheseRoles( array('AWS_ADMIN' ) );

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
    var sel = $("#select_tasks");
    sel.data( "prev", sel.val() );

    sel.change( function( data ) {
        var jqThis = $(this);
        console.log( jqThis );
        alert.window( "Howdy" );
    });
});
</script>

<?php

/* Admin select the class of emails she needs to prepare. We remain on the same 
 * page for these tasks.
 */

$default = array( "task" => "upcoming_aws", "date" => dbDate( 'this monday' ) );
echo '
    <form method="post" action="">
    <select name="task" id="select_tasks">
        <option value="upcoming_aws" selected>Upcoming AWS this week</option>
        <option value="upcoming_events_week">Upcoming Events this week</option>
        <option value="upcoming_events_today">Upcoming Events this day</option>
    </select>
    <input type="text" class="datepicker" placeholder = "Select date" 
        title="Select date" value="' . $default[ 'date' ] . '" > 
    <button type="submit" name="response" title="select">' . $symbSubmit . '</button>
    </form>
    ';

if( array_key_exists( 'response', $_POST ) )
{
    foreach( $_POST as  $k => $v )
        $default[ $k ] = $v;

}

print_r( $default );

exit;


$today = dbDate( 'next monday' );

if( array_key_exists( 'date', $_POST ) )
    $default[ 'date' ] = $_POST[  'date' ];
else
    $default = array( 'date' => $today );

echo '
    <form method="post" action="">
    <table border="0">
        <tr>
            <td>Select date</td>
            <td><input class="datepicker" type="text" name="date" value="' . 
                    $default[ 'date' ] . '" ></td>
            <td><button type="submit" name="response" value="scan">' . 
                $symbScan . '</button></td>
        </tr>
    </table>
    </form>
    ';

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
$template = getEmailTemplateById( 'aws_template' )['description'];
if( ! $template )
{
    echo alertUser( "No template found with id: aws_template. I won't 
        be able to generate email"
    );
} 
else 
{
    $template = str_replace( '@DATE@', humanReadableDate( $awses[0]['date'] ) 
        , $template ); 
    $template = str_replace( '@EMAIL_BODY@', $emailHtml, $template ); 

    $md = html2Markdown( $template );
    // Save the file and let the admin download it.
    file_put_contents( __DIR__ . "/data/$filename", $md);
    echo "<br><br>";
    echo '<table style="width:500px;border:1px solid"><tr><td>';
    echo downloadTextFile( $filename, 'Download mail' );
    echo "</td><td>";
    echo awsPdfURL( '', $whichDay, 'All AWS PDF' );
    echo "</td></tr>";
    echo '</table>';
}

// Only if the AWS date in future/today, allow admin to send emails.
if( strtotime( 'now' ) <= strtotime( $default[ 'date' ] ) )
{
    echo "TODO: Allow admin to send email";
}

echo goBackToPageLink( "admin_acad.php", "Go back" );


?>
