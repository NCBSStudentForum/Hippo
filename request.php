<?php 
include_once( "header.php" );
include_once( "methods.php" );
?>

<?php
$date = $_POST['selected_day'];
$events = getEvents( $date );
echo "<h2>Events for $date</h2>";
print_r( $events );
?>

<h2>Request for booking</h2>

<table id="table_request">
    <tr> <th>Title</th>
        <th> <input type="text" value="" > </th>
    </tr>
    <tr> <th>Venue</th>
        <th> <input type="text" value="" > </th>
    </tr>
    <tr> <th>Starts on</th>
        <th> <input type="datetime-local" value="" > </th>
    </tr>
</table>

<?php

//echo printInfo( "Creating a request for event" );

?>
