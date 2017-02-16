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

$awsText = '';
foreach( $awses as $aws )
{

    $awsText .= "<h2></h2>";
    $awstext = awsToTable( $aws, $with_picture = true );
    $awsText .= $awstext;

    $texFileName = __DIR__ . "/data/" . $aws['speaker'] . $aws['date'] . ".tex";
    $outdir = __DIR__ . "/data";
    $awsTeX = awsToTex( $aws );
    file_put_contents( $texFileName, $awsTeX );
    $res = `pdflatex --output-directory $outdir $texFileName`;
    echo "<pre> $res </pre>";
}



// Save this test and convert it to pdf.
//$cmd = "python " . __DIR__ . "/html2other.py /tmp/_aws.html md";
//echo "<pre> Executing $cmd </pre>";
//$awsText = `$cmd`;
echo "$awsText ";

// ob_start( );
// $pdf = new HTML2PDF( 'P', 'A4', 'en', true, 'UTF-8', array(5,8,10,5) );
// $pdf->WriteHTML( $awsText );
// $pdf->Output( __DIR__ . '/aws.pdf', 'F' );
// ob_end_flush( );

// Only if the AWS date in future/today, allow admin to send emails.
if( strtotime( 'now' ) <= strtotime( $default[ 'date' ] ) )
{
    echo "TODO: Allow admin to send email";
}


?>
