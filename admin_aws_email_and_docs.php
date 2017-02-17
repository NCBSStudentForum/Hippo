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


<?php

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

$template = getEmailTemplateById( 'aws_template' )['description'];
if( ! $template )
{
    echo alertUser( "No template found with id: aws_template. I won't 
        be able to generate email"
    );
}

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

$template = str_replace( '@DATE@', humanReadableDate( $awses[0]['date'] ) 
    , $template ); 
$template = str_replace( '@EMAIL_BODY@', $emailHtml, $template ); 
echo "<pre> $template </pre>";

$md = html2Markdown( $template );

// Save the file and let the admin download it.
file_put_contents( __DIR__ . "/data/$filename", $md);
//echo "Saved to $filename";
echo downloadTextFile( $filename, 'Download mail' );

// Only if the AWS date in future/today, allow admin to send emails.
if( strtotime( 'now' ) <= strtotime( $default[ 'date' ] ) )
{
    echo "TODO: Allow admin to send email";
}


?>
