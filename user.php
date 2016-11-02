<?php 
include_once( "header.php" );
include_once( "methods.php" );
include_once( "tohtml.php" );
include_once( "is_valid_access.php" );
include_once( "database.php" );

echo userHTML( );

?>

<script>
$( function() {
    var today = new Date();
    var tomorrow = (new Date()).setDate( today.getDate( ) + 1 );
    $( "#datepicker" ).multiDatesPicker( { 
        dateFormat : "y-m-d"
        , addDates : [ today, tomorrow ] 
    });
} );
</script>


<?php

// There is a form on this page which will send us to this page again. Therefore 
// we need to keep $_POST variable to a sane state.
$venues = getVenues( );
$venueSelect = venuesToHTMLSelect( $venues, TRUE );

// We came to this page without default option. Let's fill them in $_POST. We 
// are going to iterate over this page for its mandatory to create $_POST.
if( ! array_key_exists( 'picked_dates', $_POST ) )
    $_POST['picked_dates'] = humanReadableDate( strtotime( 'today' ) );

// Initialize dates and end_date in form.
$dates = explode( ",", $_POST['picked_dates']);

// FIXME: complicated logic here.
// If no venue if selected then use all venues. If from previous step we already 
// have some veneues selected then keep using them. NOTE: We convert the array 
// into string since we want to use these values in next iteration as <input> 
// value. 
if( ! array_key_exists( 'venue', $_POST ) )
{
    if( ! array_key_exists( 'selected_venues_before', $_POST ) )
        $_POST['venue'] = implode( "###", array_map( function($a) { return $a['id']; }, $venues));
    else
        $_POST['venue'] = $_POST[ 'selected_venues_before' ];
}

// To be sure that we can post this value as value of <input 
// name="selected_venues_before">.
if( is_array($_POST['venue']) )
    $_POST['venue'] = implode( "###", $_POST['venue'] );

echo "<form method=\"post\" action=\"user.php\">
    <table>
    <tr>
        <th>Pick dates</th><th>Select Venues<th><th> </th>
    </tr>
    <tr>
    <td><input type=\"text\" id=\"datepicker\" name=\"picked_dates\" value=\"\"></td>
    <td>  $venueSelect </td>
    <td>
    <button style=\"float:right\" name=\"response\" value=\"submit\">Submit</button> ";

    // NOTE: These venues were selected on previous steps. When Submit is pressed. And no
    // venue is selected,we keep displaying these venues --> 
   echo " <input type=\"hidden\" name=\"selected_venues_before\" value=\" " .
       $_POST['venue'] . "\">
    </td>
    </tr>
    </table>
    </form>
    <br><br>
    ";


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
foreach( $dates as $date )
{
    $thisdate = humanReadableDate( strtotime( $date  ) );
    $thisday = nameOfTheDay( $thisdate );

    $html = "
        <div style=\"float:left\"> <font color=\"blue\">$thisday, $thisdate </font></div> 
        <!--
            <div style=\"float:right\"><font color=\"blue\">$thisday, $thisdate </font></div> 
        -->
        ";
    // Now generate eventline for each venue.
    foreach( explode("###", $_POST['venue']) as $venueid )
        $html .= eventLineHTML( $thisdate, $venueid );
    echo $html;
}

?>


