<?php

include_once 'header.php';
include_once 'database.php';

// This page is unprotected. Anyone can read it.

echo "<p class=\"info\">
    Following graph shows the interaction among faculty. 
    <br>
    The size of node represents
    the number of AWSs I found for this faculy in my database, the thickness of edges 
    represent how many times they were involed in an AWS together either as 
    co-supervisor or thesis committee member. <br>
    External faculty is not shown in this graph.
    </p>";

$awses = getAllAWS( );
$dotText = "graph community {
    rankdir=TB;
    overlap=false;
    splines=true;
    splines=true;"
    //. "node[label=\"\"];"
    ;

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
    $size = $value['count'] / 30.0;
    $dotText .= "\t$login ["
        . "xlabel=\"$count\",xlp=\"0,0\""
        . ",color=lightblue,style=\"filled\" ,shape=circle "
        . ",fixedsize=true"
        . ",width=$size"
        . "];\n";

    foreach( array_count_values( $value['edges'] ) as $val => $penwidth )
    {
        $buddy = explode( '@', $val)[0];
        $penwidth = $penwidth / 2.0;
        $dotText .= "\t$login -- $buddy [color=\"red\",penwidth=$penwidth];\n";
    }
}

$curdir = getcwd( );
$dotText .= "}";

// Now execute neato command and generat an SVG file.
$dotFilePath = tempnam( "tmp", "graph_" );
$imgFormat = "png";
$dotFile = fopen( $dotFilePath, "w" );
fwrite( $dotFile, $dotText );

$layout = "fdp";
exec( "$layout -T$imgFormat -o $curdir/community.$imgFormat $dotFilePath", $out, $res );

// Now load the image into browser.
echo "<div class=\"easyzoom\">";
echo "<img class=\"zoom\" src=\"community.$imgFormat\" width=\"80%\" height=\"100%\" />";
echo "</div>";

// Closing this file will delete its content.
unlink( $dotFilePath );

?>
