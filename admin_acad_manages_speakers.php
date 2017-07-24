<?php

include_once 'header.php';
include_once 'check_access_permissions.php';

mustHaveAnyOfTheseRoles( array( 'AWS_ADMIN', 'BOOKMYVENUE_ADMIN' ) );

include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );

// Javascript.
$faculty = getFaculty( );
$speakers = getTableEntries( 'speakers' );
$logins = getTableEntries( 'logins' );

$speakersMap = array( );
foreach( $speakers as $visitor )
    if( strlen( $visitor[ 'email' ] ) > 0 )
        $speakersMap[ $visitor[ 'email' ] ] = $visitor;
    else
        $speakersMap[ nameArrayToText( $visitor ) ] = $visitor;

// This must not be a key => value array else autocomplete won't work. Or have 
// any null value,
$speakersIds = array( );
foreach( $speakers as $x )
    if( $x[ 'email' ] )
        $speakersIds[] = $x[ 'email' ];
    else
        $speakersIds[] = nameArrayToText( $visitor );

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
    $('#speakers_email').val( email );
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


// Form to upload a picture

echo "<h3>Speaker details</h3>";

echo '<form method="post" action="">';
echo '<input id="speakers_email" name="email" type="text" value="" >';
echo '<button type="submit" name="response" value="show">Show details</button>';
echo '</form>';

// Show speaker image here.
if( array_key_exists( 'email', $_POST ) )
{
    // Show emage.
    if( __get__( $_POST, 'email', '' ) )
    {
        $speaker = __get__( $speakersMap, $_POST['email'], '' );
        if( $speaker )
        {
            $picPath = getSpeakerPicturePath( $speaker );
            echo showImage( $picPath );
            echo arrayToVerticalTableHTML( $speaker, 'info' );
        }
        else
            echo alertUser( "No speaker is found for entry : " . $_POST[ 'email' ] );
    }
}

echo '<h3>Edit speaker details</h3>';

echo printInfo( 
    "Email id of speaker is very desirable but not neccessary. <br>
    <small>
        It helps keeping database clean and makes autocompletion possible.
    </small>
    "
    );

echo printInfo( 
    "<strong>First name</strong> and <strong>institute</strong> are required 
    fields.  ");

echo '<form method="post" enctype="multipart/form-data" 
        action="admin_acad_manages_speakers_action.php">';

echo '<table><tr>';
echo '<td class="db_table_fieldname">Speaker picture</td><td>';
echo '<input type="file" name="picture" id="picture" value="" />';
echo '</td></tr></table>';

echo dbTableToHTMLTable( 'speakers', $speaker 
    , 'honorific,email,homepage,first_name,middle_name,last_name,department,institute'
    , 'submit'
    );
echo '<button title="Delete this entry" type="submit" onclick="AreYouSure(this)"
    name="response" value="Delete">' . $symbDelete .
    '</button>';

echo '</form>';


echo "<br/><br/>";
echo goBackToPageLink( 'admin_acad.php', 'Go back' );

?>
