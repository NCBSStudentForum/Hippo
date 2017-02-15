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

foreach( $awses as $aws )
{

    $tmpfile = tempnam( sys_get_temp_dir( ), "aws_entry" );
    $handle = fopen( $tmpfile, "w" );
    $awstext = awsToTable( $aws );
    echo $awsToTable;
}

// Only if the AWS date in future/today, allow admin to send emails.
if( strtotime( 'now' ) <= strtotime( $default[ 'date' ] ) )
{
    echo "TODO: Allow admin to send email";
}


?>
