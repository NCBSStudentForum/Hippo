<?php

include_once 'header.php';
include_once 'database.php';

if( isset( $_POST['months'] ) )
    $howManyMonths = intval( $_POST[ 'months' ] );
else
    $howManyMonths = 36;

echo '
    <form method="post" action="">
    Show AWS interaction in last <input type="text" name="months" 
        value="' . $howManyMonths . '" />
    months.
    <button name="response" value="Submit">Submit</button>
    </form>
    ';


$from = date( 'Y-m-d', strtotime( 'today' ) - $howManyMonths * 30 * 24 * 3600 );

$fromD = date( 'M d, Y', strtotime( $from ) );
echo "<p class=\"info\">
    Following graph shows the interaction among faculty since $fromD.
    Number on the node is the number of AWSs supervised by faculty since $fromD.
    <br>
    Thickness of an edge represent how many times thes two faculty were involed 
    in an AWS together either as co-supervisor or thesis committee member. 
    <br>
    External faculty is not shown in this graph.
    </p>";

$awses = getAWSFromPast( $from  );
$dotText = "digraph community {
    rankdir=TB;
    overlap=false;
    sep=\"+30\";
    splines=true;
    ";

echo printInfo( "Total AWS found in database since $fromD: " . count( $awses ) );

$community = array();

// Store all $pis.
$pis = array( );
foreach( $awses as $aws )
{
    $pi = $aws['supervisor_1'];
    array_push( $pis, $pi );
    if( ! array_key_exists( $pi, $community ) )
        $community[ $pi ] = array( 'count' => 0, 'edges' => array( ) );
}
$pis = array_unique( $pis );

foreach( $awses as $aws )
{
    $pi = $aws[ 'supervisor_1' ];
    $community[ $pi ]['count'] += 1;

    // Co-supervisor is a edge
    if( $aws[ "supervisor_2" ] )
    {
        // Only if PI is from NCBS.
        if( in_array( $aws['supervisor_2'], $pis ) )
            array_push( $community[ $pi ]['edges'], $aws[ "supervisor_2" ] );
    }

    // All TCM members are edges
    for( $i = 1; $i < 5; $i++ )
    {
        if( ! array_key_exists( "tcm_member_$i", $aws ) )
            continue;
        if( strpos($aws[ "tcm_member_$i" ], '@') == false ) // Valid email id.
            continue;

        if( in_array( $aws["tcm_member_$i"], $pis ) )
            array_push( $community[ $pi ][ 'edges' ], $aws["tcm_member_$i"] );
    }
}

// Now generate dot text.
foreach( $community as $pi => $value )
{
    $login = explode( '@', $pi)[0];
    $count = $value[ 'count' ];
    $width = max(0.5, $value['count'] / 30.0);
    $dotText .= "\t$login ["
        //. "xlabel=\"$count\",xlp=\"0,0\","
        . "color=lightblue,style=\"filled\" ,shape=circle,"
        . "fixedsize=true,"
        . "width=$width"
        . "];\n";

    foreach( array_count_values( $value['edges'] ) as $val => $edgeNum )
    {
        $buddy = explode( '@', $val)[0];
        $penwidth = min(4, $edgeNum / 2.0);
        $color = 1.0 / $edgeNum;
        $dotText .= "\t$login -> $buddy [ "
                . "color=red,"
                . "penwidth=$penwidth,"
                . "taillabel=$edgeNum," 
                . "arrowhead=halfopen,"
                . "];\n";
    }
}

$curdir = getcwd( );
$dotText .= "}";

$dotFilePath = tempnam( "tmp", "graph_" );
$imgFormat = "svg";
$dotFile = fopen( $dotFilePath, "w" );
fwrite( $dotFile, $dotText );

$layout = "neato";
$imgfilename = "community_$from.$imgFormat";

//Create both SVG and PNG.
exec( "$layout -T$imgFormat -o $curdir/$imgfilename $dotFilePath", $out, $res );
exec( "$layout -Tpng -o $curdir/fallback.png $dotFilePath", $out, $res );

// Now load the image into browser.
echo "<div class=\"image\">";
echo "<object width=\"100%\" data=\"$imgfilename\" type=\"image/svg+xml\">
    <img src=\"fallback.png\" />
    </object>
    ";
echo "</div>";

// Closing this file will delete its content.
unlink( $dotFilePath );

//echo "<pre> $dotText </pre>";
echo goBackToPageLink( "index.php", "Go back" );

?>
