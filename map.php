<script type="text/javascript">
function showLabel( msg )
{
    document.getElementById("location_info").innertHtml = msg;
}
</script>

<?php

include_once "header.php";
include_once 'tohtml.php';
include_once 'methods.php';

$imageUrl = __DIR__ . "/data/ncbs_map_route_map_to_lecture_halls.jpeg";

echo "<h1> NCSB Map</h1>";

if( file_exists( $imageUrl ) )
{
    echo  "Selected location <p id=\"location_info\"></p>";
    echo displayImage( $imageUrl, $height = "auto", $width = "1000px", $usemap = 'ncbsmap' );
    echo '
    <map name="ncbsmap">
      <area shape="rect" coords="0,0,500,500" 
        onmouseover="showLabel( \'This is map\' )" />
    </map>
    ';
}
else
{
    echo printWarning( "No map is found." );
}

echo closePage( );

?>

<!--
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.3/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.0.3/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.0.3/dist/leaflet.css"
  integrity="sha512-07I2e+7D8p6he1SIM+1twR5TIrhUQn9+I6yjqD53JQjFiMf8EtC93ty0/5vJTZGF8aAocvHYNEDJajGdNx1IsQ=="
  crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.0.3/dist/leaflet.js"
  integrity="sha512-A7vV8IFfih/D732iSSKi20u/ooOfj/AGehOKq0f4vLT1Zr2Y+RX7C+w8A1gaSasGtRUZpF/NZgzSAu4/Gc41Lg=="
  crossorigin=""></script>

<h2> Testing area </h2>

<div id="ncbsmap"></div>

<script type="text/javascript" charset="utf-8">
    
var map = L.map('ncbsmap').setView([51.505, -0.09], 13);

L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

L.marker([51.5, -0.09]).addTo(ncbsmap)
    .bindPopup('A pretty CSS3 popup.<br> Easily customizable.')
    .openPopup();

</script>

-->
