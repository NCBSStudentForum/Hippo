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
    <input class="datepicker" placeholder = "Select date" 
        title="Select date" name="date" value="' . $default[ 'date' ] . '" > 
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

        echo awsToHTML( $aws, $with_picture = true );
        $emailHtml .= awsToHTML( $aws, false );
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
    $md = html2Markdown( $email );

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
else if( $default[ 'task' ] == 'This week events' )
{
    $html = printInfo( "List of public events for the week starting " 
        . humanReadableDate( $default[ 'date' ] ) 
        );
    $events = getEventsBeteen( $from = 'this monday', $duration = '+7 day' );

    foreach( $events as $event )
    {
        if( $event[ 'is_public_event' ] == 'NO' )
            continue;

        // We just need the summary of every event here.
        $html .= eventSummaryHTML( $event );
        $html .= "<br>";
    }

    // Add a google calendar link
    $html .= "<br><br>";
    echo( $html );

    // Generate email
    // getEmailTemplates
    $template = getEmailTemplateById( 'this_week_events' );
    if( $template )
    {
        $email = emailFromTemplate( 'this_week_events'
            , array( "EMAIL_BODY" => $html ) 
            );
        $md = html2Markdown( $email );
        $emailFileName = 'Events_Of_Week_' .$default[ 'date' ] . '.txt';

        // Save the content of email to a file and generate a link to show to 
        // user.
        saveDownloadableFile( $emailFileName, $md );
        echo downloadTextFile( $emailFileName, 'Download email' );
    }
    else
    {
        echo alertUser( "No template found with id this_week_events. 
            You should tell this to Hippo's admin" 
            );
    }
}
else if( $default[ 'task' ] == 'Today\'s events' )
{
    // List todays events.

    // Get all ids on this day.
    $date = $default[ 'date' ];
    echo "<h3> Events on " . humanReadableDate( $date ) . " </h3>";
    $entries = getEventsOn( $date );
    $html = '';
    foreach( $entries as $entry )
    {
        if( $entry[ 'is_public_event' ] == 'YES' )
        {
            $talkid = explode( '.', $entry[ 'external_id' ])[1];
            $talk = getTableEntry( 'talks', 'id', array( 'id' => $talkid ) );
            echo talkToHTML( $talk, true );
            $html .= talkToHTML( $talk, false );
        }
    }

    $md = html2Markdown( $html, $strip_inline_image = true );
    $emailFileName = 'EVENT_' . $default['date'] . '.txt';
    saveDownloadableFile( $emailFileName, $md );
    echo downloadTextFile( $emailFileName, 'Download email' );

    echo '<br>';
    // Link to pdf file.
    echo '<a target="_blank" href="generate_pdf_talk.php?date=' 
            . $default[ 'date' ] . '">Download pdf</a>';
    echo '<br>';

}

// Only if the AWS date in future/today, allow admin to send emails.
if( strtotime( 'now' ) <= strtotime( $default[ 'date' ] ) )
{
    echo "TODO: Allow admin to send email";
}

echo goBackToPageLink( "admin_acad.php", "Go back" );

?>
