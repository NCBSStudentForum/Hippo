<?php 
include_once( "header.php" );
include_once( "methods.php" );
include_once( "database.php" );
include_once( "check_access_permissions.php" );
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
   <tr > <td>Title <p class="note_to_user">A very short description (for
         calendar )</p></td>
      <td> <input class="user_input_text" name="title" type="text" value="" > </td>
   </tr>
   <tr> <td>Description
      </td>
      <td> 
         <textarea id="event_description" name="description" cols="40" rows="5" > </textarea> 
         <script>CKEDITOR.replace( "event_description" ) </script>
      </td>
   </tr>
   <tr> <td>Venue</td>
      <td> <?php echo $venueHTML ?> </td>
   </tr>
   <tr> <td>Starts on <br>
      </td>
      <td> <input class="timepicker" type="time" name="start_time" 
         value="<?php echo $calendarTime ?>" /> </td>
   </tr>
   <tr> <td>Ends on <br>
      </td>
      <td> <input class="timepicker" type="time" name="end_time" 
         value="<?php echo $calendarTime ?>" /> </td>
   </tr>
   <tr> <td>Date <br>
      </td>
      <td> 
         <input class="datepicker" type="date" name="date" value=<?php echo $dbDate ?> readonly />
      </td>
   </tr>
   <tr>
      <td>Repeat pattern <br>
         <p class="note_to_user">
            TODO: Details here.
            <br> Valid for maximum of 6 months
         </p>
      </td> 
      <td> 
         On <input type="text" name="day_pattern" placeholder="Sun,Mon"/ >
         every <input type="text" name="week_pattern" placeholder="first,second"/>
         week for <input type="text" name="month_pattern" placeholder="6" /> months 
      </td>
   </tr>
</table>
<br>

<button name="response" class="submit" type="submit" value="Submit">Submit</button>
<?php echo goBackToPageLink( "user.php", "Go back" ); ?>
</form>
