<?php 
include_once( "header.php" );
include_once( "methods.php" );

$venues = getVenues( );

?>

<link rel="stylesheet" href="components/bootstrap2/css/bootstrap-datetimepicker.min.css">
<script type="text/javascript" src="components/bootstrap2/js/bootstrap-datetimepicker.min.js"></script>

<?php
if( ! array_key_exists( 'date', $_POST) )
{
    echo printWarning( "No valid day is selected. Going back to main page" );
    goToPage( "index.php", 2 );
    exit(0);
}

$date = $_POST['date'];
$day = date( 'l', $date );
$events = getEvents( $date );
$calendarDate = date( 'Y-m-d', $date);

?>

<h2>Request for booking</h2>

<p class="info"> Time must be in 24 hrs HH:MM format e.g. 9:30 (for 9:30am), 14:20 for 2:20pm etc. 
</p>

<form class="input" action="user_request_action.php" method="post" accept-charset="utf-8">

<?php
include_once( "methods.php" );

// Generate options here.
$venue = __get__( $_POST, 'venue', '' );
if( $venue )
    $venueHTML = '<input name="venue" type="text" value="'.$venue.'" readonly>';
else
    $venueHTML = venuesToHTMLSelect( $venues );

$startTime = __get__( $_POST, 'start_time', '' );
$calendarTime = date( 'H:i', $startTime );
$date = __get__( $_POST, 'date', '' );

?>

<table class="input" id="table_request">
    <!-- hide the day -->
    <input type="hidden" name="date" value="<?php echo $date ?>" />
    <tr> <td>Title</td>
        <td> <input name="title" type="text" value="" > </td>
    </tr>
    <tr> <td>Description</td>
        <td> <textarea name="description" cols="22" rows="3" > </textarea> </td>
    </tr>
    <tr> <td>Venue</td>
    <td> <?php echo $venueHTML ?> </td>
    </tr>
    <tr> <td>Starts on <br>
    </td>
    <td> <input type="time" name="start_time" 
            value="<?php echo $calendarTime ?>" /> </td>
    </tr>
    <tr> <td>Ends on <br>
    </td>
        <td> <input type="time" name="end_time" value="" /> </td>
    </tr>
    <tr> <td>Date <br>
    </td>
        <td> 
        <input type="date" name="date" value=<?php echo $calendarDate ?> />
        </td>
    </tr>
    <tr>
        <td>Repeat pattern <br>
        <small>days,weeks,months.
                <br> Valid for maximum of 1 year
        </small>
        </td> 
        <td> 
            <input type="text" name="repeat_pat" id="repeat" value="" /> 
        </td>
    </tr>
</table>
<br>

<button name="response" class="submit" type="submit" value="Submit">Submit</button>
<button name="response" class="goback" type="submit" value="Go back">Go back</button>

</form>
