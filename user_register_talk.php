<?php
include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'USER' ) );

include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );

// Javascript.
$faculty = getFaculty( );
$speakers = getTableEntries( 'speakers' );
$logins = getTableEntries( 'logins' );

//var_dump( $speakers );

$speakersMap = array( );
foreach( $speakers as $visitor )
    if( strlen( $visitor[ 'email' ] ) > 0 )
        $speakersMap[ $visitor[ 'email' ] ] = $visitor;

// This must not be a key => value array else autocomplete won't work. Or have 
// any null value,
$speakersIds = array( );
foreach( $speakers as $x )
    if( $x[ 'email' ] )
        $speakersIds[] = $x[ 'email' ];

$faculty = array_map( function( $x ) { return loginToText( $x ); }, $faculty );
$logins = array_map( function( $x ) { return loginToText( $x ); }, $logins );

?>

<script type="text/javascript" charset="utf-8">
// Autocomplete speaker.
$( function() {
    var speakersDict = <?php echo json_encode( $speakersMap ) ?>;
    var host = <?php echo json_encode( $faculty ); ?>;
    var logins = <?php echo json_encode( $logins ); ?>;

    // These emails must not be key value array.
    var emails = <?php echo json_encode( $speakersIds ); ?>;
    //console.log( emails );

    $( "#talks_host" ).autocomplete( { source : host }); 
    $( "#talks_host" ).attr( "placeholder", "autocomplete" );

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
$speaker = array( 
    'first_name' => '', 'middle_name' => '', 'last_name' => '', 'email' => ''
    , 'department' => '', 'institute' => '', 'title' => '', 'id' => ''
    , 'homepage' => ''
    );

$talk = array( 'created_by' => $_SESSION[ 'user' ] 
    , 'created_on' => dbDateTime( 'now' )
    );

echo '<form method="post" action="user_register_talk_action.php">';
echo "<h3>Speaker details</h3>";
echo printInfo( 
    "Email id of speaker is desirable. It helps keeping database clean 
    by avoidling duplicate entries (and make autocompletion possible).");

echo printInfo( 
    "<strong>First name</strong> and <strong>institute</strong> are required 
    fields.  ");

echo dbTableToHTMLTable( 'speakers', $speaker 
    , 'honorific,email,homepage,first_name,middle_name,last_name,department,institute'
    , '', 'id'
    );
echo "<h3>Talk information</h3>" ;
echo dbTableToHTMLTable( 'talks', $talk
    , 'host,coordinator,title,description'
    , ''
    , $hide = 'id,speaker,status,created_on'
    );

echo "<h3>Optionally create a booking request</h3>" ;
echo printInfo( 
    "
    $symbWarn I may not be able to book if there is aleady any pending 
    booking request at your preferred venue/slot. In any case,
    you can book later by clicking on <strong>Manage my talks</strong> in 
    your HOME page.
    <br />
    "
    );

$venueSelect = venuesToHTMLSelect( );
echo "<table class=\"editable\" >";
echo '<tr>
        <td class="db_table_fieldname">Venue</td> <td>' . $venueSelect . '</td>
    </tr>';
echo '<tr>
        <td class="db_table_fieldname">date</td> 
        <td><input name="date" class="datepicker" type=\"date\" ></td>
    </tr>';
echo '<tr>
        <td class="db_table_fieldname">start time</td> 
        <td><input name="start_time" class="timepicker" type=\"time\" ></td>
    </tr>';
echo '<tr>
        <td class="db_table_fieldname">end time</td> 
        <td><input name="end_time" class="timepicker" type=\"time\" ></td>
    </tr>';
echo "</table>";
echo '<button class="submit" title="Submit talk" 
    name="response" value="submit">' . $symbCheck . "</button>";
echo '</form>';

echo "<br/><br/>";
echo goBackToPageLink( 'user.php', 'Go back' );

?>
