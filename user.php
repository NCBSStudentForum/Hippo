<?php 
include_once( "header.php" );
include_once( "methods.php" );
include_once( "tohtml.php" );
include_once( "is_valid_access.php" );

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

// If no venue if selected then use all venues.
if( ! array_key_exists( 'venue', $_POST ) )
    $_POST['venue'] = array_map( function($a) { return $a['id']; }, $venues);

echo "<form method=\"post\" action=\"user.php\">
    <table>
    <tr>
        <th>Start date</th><th>End date</th><th>Select Venues<th><th> </th>
    </tr>
    <tr>
    <td><input type=\"date\" name=\"date\" placeholder=\"Select start date\" ></td>
    <td><input type=\"date\" name=\"end_date\" placeholder=\"Select end date\" ></td>
    <td>  $venueSelect </td>
    <td>
    <button style=\"float:right\" name=\"response\" value=\"submit\">Submit</button>
    </td>
    </tr>
    </table>
    </form>
    <br><br>
    ";


$date = $_POST['date'];
$endData = $_POST['end_date'];
if( ! $endDate )
    $endDate = $date;

echo "Start Date: $date to $endDate <br>";

$day = nameOfTheDay( $date );

$html = "<h3>On $day, $date </h3>";

echo "<br>";
echo "<div class=\"info\">";
echo "You may not be able to create any request at following blocks";
echo "<table>
    <tr><td>
    <button class=\"display_request\" >R</button>Pending requests
    </td><td>
    <button class=\"display_event\" >E</button>Confirmed events
    </td></tr>
    </table>";
echo "</div>";

// Now generate the range of dates.

// Now generate eventline for each venue.
foreach( $_POST['venue'] as $venueid )
    $html .= eventLineHTML( $date, $venueid );

echo $html;

?>



