<?php 
include_once( "header.php" );
include_once( "methods.php" );

$venues = getVenues( );

?>

<link rel="stylesheet" href="components/bootstrap2/css/bootstrap-datetimepicker.min.css">
<script type="text/javascript" src="components/bootstrap2/js/bootstrap-datetimepicker.min.js"></script>

<?php
if( ! array_key_exists( 'selected_day', $_POST) )
{
    echo printWarning( "No valid day is selected. Going back to main page" );
    goToPage( "index.php", 2 );
    exit(0);
}

$date = $_POST['selected_day'];
$day = date( 'l', strtotime( $date ) );
$events = getEvents( $date );
echo "<h2>Status of venues <font color=\"blue\">$day, $date</font> </h2>";
echo "<p>TODO: Show all venues and filter according to user input</p>";
print_r( $events );
?>

<h2>Request for booking</h2>

<p> Time must be in 24 hrs HH:MM format e.g. 9:30 (for 9:30am), 14:20 for 2:20pm etc. 
</p>

<form class="input" action="request_action.php" method="post" accept-charset="utf-8">
<?php

include_once( "methods.php" );
//var_dump( $_POST );

if( $_POST[ "response" ] == "Go back" )
{
    goToPage( "index.php", 0 );
    exit( 0 );
}

?>

<table class="input" id="table_request">
    <tr> <td>Title</td>
        <td> <input type="text" value="" > </td>
    </tr>
    <tr> <td>Venue</td>
    <td> <?php echo venuesToHTMLSelect( $venues ); ?> </td>
    </tr>
    <tr> <td>Starts on <br>
    </td>
        <td> <input type="time" name="startOn" value="" /> </td>
    </tr>
    <tr> <td>Ends on <br>
    </td>
        <td> <input type="time" name="endOn" value="" /> </td>
    </tr>
    <tr> <td>Date <br>
    </td>
        <td> 
        <input type="time" name="selected_day" value=<?php echo $_POST['selected_day'] ?> />
        </td>
    </tr>
    <tr>
        <td>Repeat pattern <br>
        <small>days,weeks,months.
                <br> Valid for maximum of 1 year
        </small>
        </td> 
        <td> 
            <input type="text" name="repeatPat" id="repeat" value="" /> 
        </td>
    </tr>
</table>
<br>

<button name="response" class="submit" type="submit" value="Submit">Submit</button>
<button name="response" class="goback" type="submit" value="Go back">Go back</button>

</form>
