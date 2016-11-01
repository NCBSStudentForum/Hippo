<?php 
include_once( "header.php" );
include_once( "methods.php" );
include_once( "tohtml.php" );
include_once( "is_valid_access.php" );
include_once( "database.php" );

echo welcomeUserHTML( );

// There is a form on this page which will send us to this page again. Therefore 
// we need to keep $_POST variable to a sane state.
$venues = getVenues( );
$venueSelect = venuesToHTMLSelect( $venues, true );

// We came to this page without default option. Let's fill them in $_POST. We 
// are going to iterate over this page for its mandatory to create $_POST.
if( ! array_key_exists( 'date', $_POST ) )
{
    $_POST['date'] = humanReadableDate( strtotime( 'today' ) );
    $_POST['end_date'] = humanReadableDate( strtotime( 'today' ) );
}

// Initialize dates and end_date in form.
$date = $_POST['date'];
$endDate = $_POST['end_date'];
if( ! $endDate )
    $endDate = $date;


// If no venue if selected then use all venues.
if( ! array_key_exists( 'venue', $_POST ) )
    $_POST['venue'] = array_map( function($a) { return $a['id']; }, $venues);

echo "<form method=\"post\" action=\"user.php\">
    <table>
    <tr>
        <th>Start date</th><th>End date</th><th>Select Venues<th><th> </th>
    </tr>
    <tr>
    <td><input type=\"date\" name=\"date\" value=\"$date\" ></td>
    <td><input type=\"date\" name=\"end_date\" value=\"$endDate\" ></td>
    <td>  $venueSelect </td>
    <td>
    <button style=\"float:right\" name=\"response\" value=\"submit\">Submit</button>
    </td>
    </tr>
    </table>
    </form>
    <br><br>
    ";


$day = nameOfTheDay( $date );
$endDay = nameOfTheDay( $endDate );


echo "<br>";
echo "<div class=\"info\">";
echo "You may not be able to create any request at following slots:";
echo "
    <button class=\"display_request\" style=\"width:20px;height:20px\"></button>Pending requests
    <button class=\"display_event\" style=\"width:20px;height:20px\"></button>Booked slots
    ";
echo "<br>Click on them to see details";
echo "</div>";

// Now generate the range of dates.
$numDays = getNumDaysInBetween( $date, $endDate );
for( $i = 0; $i <= $numDays; $i++ )
{
    $thisdate = humanReadableDate( strtotime( $date . " + $i days" ) );
    $thisday = nameOfTheDay( $thisdate );

    $html = "
        <div style=\"float:left\"> <font color=\"blue\">$thisday, $thisdate </font></div> 
        <!--
        <div style=\"float:right\"><font color=\"blue\">$thisday, $thisdate </font></div> -->
        ";
    // Now generate eventline for each venue.
    foreach( $_POST['venue'] as $venueid )
        $html .= eventLineHTML( $thisdate, $venueid );
    echo $html;
}

?>



