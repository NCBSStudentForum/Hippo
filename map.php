<?php

include_once "header.php";
include_once 'tohtml.php';
include_once 'methods.php';

$imageUrl = __DIR__ . "/data/ncbs_map_route_map_to_lecture_halls.jpeg";

echo "<h1> Location of major lecture halls in NCBS </h1>";

if( file_exists( $imageUrl ) )
{
    echo displayImage( $imageUrl, $width = "800px" );
}
else
{
    echo printWarning( "No map is found." );
}

?>


