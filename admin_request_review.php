<?php 

include_once( "header.php" );
include_once( "methods.php" );
include_once( "tohtml.php" );

?>

<script type="text/javascript">
function toggleMe(source) {
    var checkboxes = document.getElementsByName( 'request[]' );
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
        foreach( $days as $day )
        {
            echo "<tr>";
            $rid = $r['id']; 
            $r['date'] = $day;
            $r['repeat_pat'] = '';
            echo "<td><input type=\"checkbox\" name=\"request[]\"
                    value=\"request_$rid\"></td>";
            echo "<td>" . requestToHTMLTable( $r ) . "</td>";
            echo "</tr>";
            echo '<input type="hidden" name="parent_id" value="'.$r["id"].'">';
        }
        echo "</td></tr> </table>";
        echo "</form>";
    }
}

//goToPage( "admin.php", 5 );

?>
