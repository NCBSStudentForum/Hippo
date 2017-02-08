<?php

include_once 'header.php';
include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';
require_once 'class.Diff.php';

echo "<h3>Manage pending requests</h3>";


$pendingRequests = getPendingAWSRequests( );
foreach( $pendingRequests as $req )
{
    $speaker = $req['speaker'];
    $date = $req['date'];
    $aws = getMyAwsOn( $speaker, $date );
    $diff = array( );

    if( $aws )
    {
        foreach( $aws as $key => $val )
            if( array_key_exists( $key, $req ) )
                $diff[ $key ] = Diff::compare( $aws[$key], $req[$key] );
    }

    echo '<form method="post" action="admin_aws_manages_requests_submit.php">';
    echo arrayToVerticalTableHTML( $req, 'aws', ''
        , array( 'id', 'status', 'modified_on' ) 
    );

    echo '<table class="show_aws" border="0">
        <tr style="background:white">
            <td style="border:0px;min-width:50%;align:left;">
            <textarea rows="3" cols="80%" name="reason" 
                placeholder="Reason for rejection" >Reason for rejection</textarea>
            <button type="submit" name ="response" value="Reject">Reject</button>
            </td>
        </tr><tr>
            <td style="border:0px;max-width:50%;">
                <button type="submit" name ="response" value="Accept">Accept</button>
            </td>
        </tr>
    </table>';
    echo '<input type="hidden" name="request_id" value="' . $req['id'] . '" >';
    echo '<input type="hidden" name="speaker" value="' . $speaker . '" >';
    echo '<input type="hidden" name="date" value="' . $date . '" >';
    echo '</form>';

    // This button show changes made by user.
    echo '
        <button type="submit" onclick="show_changes()">Show chanages</button>
        ';
}

echo goBackToPageLink( "admin_aws.php", "Go back" );

?>

<script type="text/javascript" charset="utf-8">
    function show_changes( )
    {
        var diff = <?php echo json_encode( $diff ) ?>;
        alert( "TODO: Diff is" + diff );
    }
</script>


