<?php 
include_once( "header.php" );
include_once( "methods.php" );
include_once( "database.php" );
include_once( "validate_privileges.php" );
include_once( "tohtml.php" );

echo userHTML( );

if( ! requiredPrivilege( 'USER' ) )
{
    echo printWarning( "You don't have enough privilege to open a request" );
    goToPage( "index.php", 1 );
    exit( 0 );
}
    
$venues = getVenues( $sortby = 'total_events' );

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
$day = nameOfTheDay( $date ); 
$events = getEvents( $date );
$dbDate = dbDate( $date );

?>

<h2>Request for booking</h2>

<div class="info"> Time format : HH:MM, 24 Hr format 
</small>9:30 (for 9:30am), 14:20 for 2:20pm etc. </small>
</div>
<br>

<form class="input" action="user_submit_request_action.php" method="post" accept-charset="utf-8">

<?php
include_once( "methods.php" );

// Generate options here.
$venue = __get__( $_POST, 'venue', '' );
$venue = trim( $venue );
if( $venue )
    $venueHTML = "<input name=\"venue\" type=\"text\" value=\"$venue\" readonly>";
else
    $venueHTML = venuesToHTMLSelect( $venues );

$startTime = __get__( $_POST, 'start_time', '' );
$calendarTime = date( 'H:i', $startTime );
$date = __get__( $_POST, 'date', '' );

?>

<table class="input" >
    <!-- hide the day -->
    <input type="hidden" name="date" value="<?php echo $date ?>" />
    <tr > <td>Title <small>A very short description for calendar</small></td>
        <td> <input name="title" type="text" value="" > </td>
    </tr>
    <tr> <td style="width:200px">Description <small> Event details. 
    Its a good idea to copy/paste here. </small>
</td>
        <td> <textarea name="description" cols="40" rows="5" > </textarea> </td>
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
        <input type="date" name="date" value=<?php echo $dbDate ?> readonly />
        </td>
    </tr>
    <tr>
        <td>Repeat pattern <br>
        <small>
        TODO: Details here.
        <br> Valid for maximum of 6 months
        </small>
        </td> 
        <td> 
            <input type="text" name="repeat_pat" id="repeat" value="" /> 
        </td>
    </tr>
</table>
<br>

<button name="response" class="submit" type="submit" value="Submit">Submit</button>
<div style="float:left">
<?php echo goBackToPageLink( "user.php", "Go back" ); ?>

</form>
