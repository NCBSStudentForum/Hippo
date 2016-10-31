<?php 
include_once( "header.php" );
include_once( "methods.php" );
include_once( "tohtml.php" );
include_once( "is_valid_access.php" );


// There is a form on this page which will send us to this page again. Therefore 
// we need to keep $_POST variable to a sane state.
if( ! array_key_exists( 'date', $_POST ) )
    $_POST['date'] = strtotime( 'today' );

$venues = getVenues( );
$venueSelect = venuesToHTMLSelect( $venues );

echo "<form method=\"post\" action=\"user.php\">
    <input type=\"date\" name=\"date\" placeholder=\"Select date\" >
    $venueSelect
    <button name=\"response\" value=\"submit\">Submit</button>
    </form>";


$date = $_POST['date'];
$calendarDate = humanReadableDate( $date );
$day = humanReadableDay( $calendarDate );

echo "<h3>On $day, $calendarDate </h3>";
$html = eventLineHTML( $date );
echo $html;

?>



