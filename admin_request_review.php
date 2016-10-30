<?php 

include_once( "header.php" );
include_once( "methods.php" );
include_once( "tohtml.php" );

?>

<script type="text/javascript">
function toggleMe(source) {
    var checkboxes = document.getElementsByName( 'events[]' );
    for( var index = 0; index < checkboxes.length; ++index )
        checkboxes[index].checked = source.checked;
}
</script>


<?php
if( $_POST['response'] == "Review" )
{
    // Approve after constructing all the events from the patterns.
    $r = getRequestById( $_POST['requestId'] );
    // First insert this request into event calendar.
    echo "<h2> We are processing following request </h2>";
    echo requestToHTMLTable( $r );

    if( $r['does_repeat'] && strlen(trim($r['repeat_pat'])) > 0 )
    {
        $days = repeatPatToDays( $r['repeat_pat'] );
        $numEvents = count( $days );
        echo printInfo("Due to repeat pattern, 
            this will lead to creation of following $numEvents events"
        );

        echo '<form method="post" action="admin_request_submit.php">';
        echo "<table>";
        echo "<tr> 
            <td><input name=\"request[]\" type=\"checkbox\" 
                onclick=\"toggleMe(this)\" />Check all</td>
                <!-- Here we create the button to submit requests -->
                <td> 
                    <button name=\"response\" value=\"approve\">Approve selected </button>
                    <button name=\"response\" value=\"reject\">Reject selected</button>
                </td>
            </tr>
            ";
        $childrenId = 0;
        foreach( $days as $day )
        {
            $rid = $r['id']; 
            $eventId = "$rid."."$childrenId"; 
            $childrenId += 1;
            $r['date'] = $day;
            $r['repeat_pat'] = '';
            echo "<tr>";
            echo "<td><input type=\"checkbox\" name=\"events[]\" value=\"$eventId\"></td>";
            echo "<td>" . requestToHTMLTable( $r ) . "</td>";
            echo '<td>TODO: check venue</td>';
            echo '</tr>';
            echo '<input type="hidden" name="request_id" value="'. $rid . '">';
        }
        echo '</td>';
        echo "</tr> </table>";
        echo "</form>";
    }
}

//goToPage( "admin.php", 5 );

?>
