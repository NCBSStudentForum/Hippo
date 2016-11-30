<?php 
include_once( "header.php" );
include_once( "methods.php" );
include_once( "tohtml.php" );
include_once( "database.php" );
include_once 'display_content.php';
include_once "./check_access_permissions.php";

mustHaveAnyOfTheseRoles( 
    array( 'USER', 'ADMIN', 'BOOKMYVENUE_ADMIN', 'AWS_ADMIN', 'JC_ADMIN' ) 
);

echo userHTML( );

// There is a form on this page which will send us to this page again. Therefore 
// we need to keep $_POST variable to a sane state.
$venues = getVenues( );
$venueSelect = venuesToHTMLSelect( $venues, TRUE );


// Get the holiday on particular day. Write it infront of date to notify user.
$holidays = array( );
foreach( getTableEntries( 'holidays', 'date' ) as $holiday )
    $holidays[ $holiday['date'] ] = $holiday['description'];


// FIXME: complicated logic here.
// If no venue if selected then use all venues. If from previous step we already 
// have some veneues selected then keep using them. NOTE: We convert the array 
// into string since we want to use these values in next iteration as <input> 
// value.  Similarly do it for dates.
if( ! array_key_exists( 'picked_dates', $_POST ) )
{
    if( ! array_key_exists( 'selected_dates_before', $_POST ) )
        $_POST['picked_dates'] = dbDate(strtotime( 'today' ) );
    else
        $_POST['picked_dates']  = $_POST['selected_dates_before'];
}

// Initialize dates and end_date in form.
$dates = explode( ",", $_POST['picked_dates']);

if( ! array_key_exists( 'venue', $_POST ) )
{
    if( ! array_key_exists( 'selected_venues_before', $_POST ) )
        $_POST['venue'] = implode( "###", array_map( function($a) { 
            return $a['id']; }, $venues)
            );
    else
        $_POST['venue'] = $_POST[ 'selected_venues_before' ];
}

// To be sure that we can post this value as value of <input 
if( is_array($_POST['venue']) )
    $_POST['venue'] = implode( "###", $_POST['venue'] );

$pickedDates = $_POST['picked_dates'];
echo "<form method=\"post\" action=\"bookmyvenue_browse.php\">
    <table>
    <tr>
    <th>
        Step 1: Pick dates
        <p class=\"note_to_user\">You can select multiple dates by clicking on them</p>
    </th>
    <th>
        Step 2: Select Venues
        <p class=\"note_to_user\">You can select multiple venues by holding 
            down Ctrl or Shift key</p>
    </th>
    <th>
        Step 3: Press <button disabled>Filter</button> to filter out other venues
    </th>
    </tr>
    <tr>
    <td><input type=\"text\" class=\"multidatespicker\" 
            name=\"picked_dates\" value=\"$pickedDates\"></td>
    <td>  $venueSelect </td>
    <td>
    <button style=\"float:right\" name=\"response\" value=\"submit\">Filter</button> ";

    // NOTE: These venues were selected on previous steps. When Submit is pressed. And no
    // venue is selected,we keep displaying these venues --> 
   echo " <input type=\"hidden\" name=\"selected_venues_before\" value=\" " .  $_POST['venue'] . "\">";
   echo " <input type=\"hidden\" name=\"selected_dates_before\" value=\" " .  $_POST['picked_dates'] . "\">";
   echo " </td> </tr> </table> </form> <br> ";


   echo alertUser( 
       "
       <button class=\"display_request\" style=\"width:20px;height:20px\"></button>Pending requests
       <button class=\"display_event\" style=\"width:20px;height:20px\"></button>Booked slots
       <button class=\"display_event_with_public_event\" style=\"width:20px;height:20px\"></button>There is a public event at this slot.
       "
   );


echo "<h3>Step 4: Press + button to create an event at this time slot</h3>";
// Now generate the range of dates.
foreach( $dates as $date )
{
    $thisdate = humanReadableDate( strtotime( $date  ) );
    $thisday = nameOfTheDay( $thisdate );

    $holidayText = '';
    if( array_key_exists( $date, $holidays ) )
        $holidayText =  '<div style="float:right"> &#9786 ' . $holidays[ $date ] . '</div>';

    $html = "<h4 class=\"info\"> <font color=\"blue\">
        $thisday, $thisdate, $holidayText </font></h4>";

    // Now generate eventline for each venue.
    foreach( explode("###", $_POST['venue']) as $venueid )
        $html .= eventLineHTML( $date, $venueid );

    echo $html;
}

?>


