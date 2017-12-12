<?php

include_once 'header.php';
include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';
include_once 'check_access_permissions.php';

mustHaveAllOfTheseRoles( array( 'AWS_ADMIN' ) );

?>

<script type="text/javascript" charset="utf-8">
    function showAWSDetails( ) {
        var text = <?php echo json_encode( $aws ) ?>;
       alert( text );
    }
</script>

<?php

/**
    * @brief Put a AWS on this block.
    *
    * @param $awsDays
    * @param $block
    * @param $blockSize
    *
    * @return
 */
function awsOnThisBlock( $awsDays, $block, $blockSize )
{
    foreach( $awsDays as $awsDay )
    {
        $awsWeek = intval( $awsDay / $blockSize );
        if( 0 == ($block - $awsWeek ) )
            return true;
    }
    return false;
}

function daysToLine( $awsDays, $totalDays, $blockSize = 7)
{
    $today = strtotime( 'now' );
    $totalBlocks = intval( $totalDays / $blockSize ) + 1;
    $line = '<td><small>';


    // These are fixed to 4 weeks (a month).
    $line .= intval( $awsDays[0] / 30.41 ) . ',' ;
    for( $i = 1; $i < count( $awsDays ); $i++ )
        $line .=  intval(( $awsDays[ $i ] - $awsDays[ $i - 1 ] ) / 30.41 ) . ',';

    $line .= "</small></td><td>";

    for( $i = 0; $i <= $totalBlocks; $i++ )
    {
        if( awsOnThisBlock( $awsDays, $i, $blockSize ) )
            $line .= '|';
        else
            $line .= '.';
    }

    $line .= "</td>";
    return $line;
}

echo ' <h2>List of active speakers</h2> ';
$awsSpeakers = getTableEntries( 'logins', 'login'
    , "eligible_for_aws='YES' AND status='ACTIVE'
    " );

$speakerPiMap = array( );
foreach( $awsSpeakers as $login )
{
    $piOrHost = getPIOrHost( $login[ 'login' ] );
    $speakerPiMap[ $piOrHost ][] = $login;
}

ksort( $speakerPiMap );

foreach( $speakerPiMap as $pi => $speakers )
{
    echo "<h3>AWS Speaker list for " . $pi . "</h3>";

    $table = "<table class=\"info\">";
    $table .="<tr>";

    $i = 0;
    foreach( $speakers as $login )
    {
        if( ! $login )
            continue;

        $speaker = getLoginInfo( $login['login'] );

        $i ++;
        $table .= "<td> $i " . arrayToName( $speaker ) . "<br />
            <tt>(" .  $speaker[ 'email' ] . ")</tt></td>";
        if( $i % 4 == 0 )
            $table .= "<tr></tr>";
    }
    $table .= "</tr>";
    $table .= "</table>";
    echo $table;
}


echo goBackToPageLink( "admin_acad.php", "Go back" );


?>
