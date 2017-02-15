<?php

include_once 'header.php';
include_once 'database.php';
include_once 'tohtml.php';
include_once 'html2text.php';
include_once 'check_access_permissions.php';
require_once 'vendor/autoload.php';

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

$awsText = '';
foreach( $awses as $aws )
{

    $awsText .= "<h2></h2>";
    $user = $aws[ 'speaker' ];
    $awstext = awsToTable( $aws );
    $imgHtml = getUserPicture( $user );
    //$awsText .= "<div float=\"right\"> $imgHtml </div>";
    $awsText .= $awstext;
}



// Save this test and convert it to pdf.
file_put_contents( '_aws.html', $awsText );

$cmd = "python " . __DIR__ . "/html2other.py _aws.html md";
echo "<pre> Executing $cmd </pre>";
$awsText = `$cmd`;
echo "<pre> $awsText </pre>";

ob_start( );
$pdf = new HTML2PDF( 'P', 'A4', 'en', true, 'UTF-8', array(5,8,10,5) );
$pdf->WriteHTML( $awsText );
$pdf->Output( __DIR__ . '/aws.pdf', 'F' );
ob_end_flush( );

// Only if the AWS date in future/today, allow admin to send emails.
if( strtotime( 'now' ) <= strtotime( $default[ 'date' ] ) )
{
    echo "TODO: Allow admin to send email";
}


?>
