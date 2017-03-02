<?php

include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'AWS_ADMIN' ) );

include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );

// Javascript.
$slots = getTableEntries( 'slots' );

//var_dump( $speakers );

$slotsMap = array( );
$slotsId = array_map( function( $x ) { return $x['id']; }, $slots );

foreach( $slots as $slot )
    $slotsMap[ $slot[ 'id' ] ] = $slot;

?>

<script type="text/javascript" charset="utf-8">
// Autocomplete speaker.
$( function() {
    var slotsDict = <?php echo json_encode( $slotsMap ) ?>;
    var slots = <?php echo json_encode( $slotsId ); ?>;

    $( "#slot" ).autocomplete( { source : slots }); 
    $( "#slot" ).attr( "placeholder", "autocomplete" );

    $( "#talks_coordinator" ).autocomplete( { source : logins }); 
    $( "#talks_coordinator" ).attr( "placeholder", "autocomplete" );


    // Once email is matching we need to fill other fields.
    $( "#speakers_email" ).autocomplete( { source : emails
        , focus : function( ) { return false; }
    }).on( 'autocompleteselect', function( e, ui ) 
        {
            var email = ui.item.value;
            $('#speakers_first_name').val( speakersDict[ email ]['first_name'] );
            $('#speakers_middle_name').val( speakersDict[ email ]['middle_name'] );
            $('#speakers_last_name').val( speakersDict[ email ]['last_name'] );
            $('#speakers_department').val( speakersDict[ email ]['department'] );
            $('#speakers_institute').val( speakersDict[ email ]['institute'] );
            $('#speakers_homepage').val( speakersDict[ email ]['homepage'] );
        }
    );
    $( "#speakers_email" ).attr( "placeholder", "autocomplete" );
});
</script>

<?php

// Logic for POST requests.
$slot = array( 'id' => '', 'start_time' => '', 'end_time' => '' );

echo "<h2>Slot details</h2>";
echo '<form method="post" action="">';
echo '<input id="slot" name="id" type="text" value="" >';
echo '<button type="submit" name="response" value="show">Show details</button>';
echo '</form>';

// Show speaker image here.
if( array_key_exists( 'id', $_POST ) )
{
    // Show emage.
    $slot = $slotsMap[ $_POST['id'] ];
    echo arrayToVerticalTableHTML( $slot, 'slot' );
}

echo '<h3>Edit slot details</h3>';

echo '<form method="post" action="admin_acad_manages_speakers_action.php">';
echo dbTableToHTMLTable( 'slots', $slot 
    , 'id,start_time,end_time', 'submit', 'id');

echo '<button title="Delete this entry" type="submit" onclick="AreYouSure(this)"
    name="response" value="Delete">' . $symbDelete .
    '</button>';

echo '</form>';


echo "<br/><br/>";
echo goBackToPageLink( 'admin_acad_manage_slots.php', 'Go back' );

?>
