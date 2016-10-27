<?php 
include_once( "header.php" );
include_once( "methods.php" );
?>

<link rel="stylesheet" href="components/bootstrap2/css/bootstrap-datetimepicker.min.css">
<script type="text/javascript" src="components/bootstrap2/js/bootstrap-datetimepicker.min.js"></script>

<?php
$date = $_POST['selected_day'];
$events = getEvents( $date );
echo "<h2>Events for $date</h2>";
print_r( $events );
?>

<h2>Request for booking</h2>

<form class="input" action="request_submit.php" method="post" accept-charset="utf-8">
<table id="table_request">
    <tr> <th>Title</th>
        <th> <input type="text" value="" > </th>
    </tr>
    <tr> <th>Venue</th>
        <th> <input type="text" value="" > </th>
    </tr>
    <tr> <th>Starts on</th>
        <th> <input type="time" name="startOn" value="" /> </th>
    </tr>
    <tr> <th>Ends on</th>
        <th> <input type="time" name="endOn" value="" /> </th>
    </tr>
    <tr><td></td><td>
        <input id="input" type="submit" value="Submit" />
    </td>
</table>
</form>

<?php

//echo printInfo( "Creating a request for event" );

?>
