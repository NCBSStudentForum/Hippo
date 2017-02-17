<?php

include_once 'header.php';
include_once 'database.php';
include_once 'tohtml.php';
include_once 'html2text.php';
include_once 'check_access_permissions.php';

mustHaveAllOfTheseRoles( array('AWS_ADMIN' ) );

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
$emailHtml = '';

foreach( $awses as $aws )
{

    echo awsToTable( $aws, $with_picture = true );
    $emailHtml .= awsToTable( $aws, false );
    // Link to pdf file.
    echo awsPdfURL( $aws[ 'speaker' ], $aws[ 'date' ] );
}

$template = str_replace( '@DATE@', humanReadableDate( $awses[0]['date'] ) 
    , $template ); 
$template = str_replace( '@EMAIL_BODY@', $emailHtml, $template ); 

echo "<h2>Email </h2>";
$md = html2Markdown( $template );
echo "<pre> $md </pre>";

// Only if the AWS date in future/today, allow admin to send emails.
if( strtotime( 'now' ) <= strtotime( $default[ 'date' ] ) )
{
    echo "TODO: Allow admin to send email";
}


?>
