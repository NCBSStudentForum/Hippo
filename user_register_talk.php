<?php
include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'USER' ) );

include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );

// Javascript.
$faculty = getFaculty( );
$visitors = getTableEntries( 'visitors' );
$visitorsMap = array( );
foreach( $visitors as $visitor )
    if( strlen( $visitor[ 'email' ] ) > 0 )
        $visitorsMap[ $visitor[ 'email' ] ] = $visitor;

$visitorsIds = array_map( function( $x ) {return $x['email']; }, $visitors );
$logins = array_map( function( $x ) { return loginToText( $x ); }, $faculty );
?>

<script type="text/javascript" charset="utf-8">
// Autocomplete speaker.
$( function() {
    var visitors = <?php echo json_encode( $visitorsMap ) ?>;
    var data = <?php echo json_encode( $logins ); ?>;
    var emails = <?php echo json_encode( $visitorsIds ); ?>;
    $( "#talks_host" ).autocomplete( { source : data }); 
    $( "#talks_host" ).attr( "placeholder", "autocomplete" );

    // Once email is matching we need to fill other fields.
    $( "#visitors_email" ).attr( "placeholder", "autocomplete" );
    $( "#visitors_email" ).autocomplete( {
        source : emails, focus : function( ) { return false; } 
    }).on( 'autocompleteselect', function( e, ui ) 
        {
            var email = ui.item.value;
            $('#visitors_first_name').val( visitors[ email ]['first_name'] );
            $('#visitors_middle_name').val( visitors[ email ]['middle_name'] );
            $('#visitors_last_name').val( visitors[ email ]['last_name'] );
            $('#visitors_department').val( visitors[ email ]['department'] );
            $('#visitors_institute').val( visitors[ email ]['institute'] );
            $('#visitors_homepage').val( visitors[ email ]['homepage'] );
        });



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
    "Email id of visitor is desirable. It just help keeping the database clean 
    by avoidling duplicate entries and make autocompletion possible.");

echo printInfo( 
    "<strong>First name</strong> and <strong>institute</strong> are required 
    fields.  ");

echo dbTableToHTMLTable( 'visitors', $speaker 
    , 'title,email,homepage,first_name,middle_name,last_name,department,institute'
    , '', 'id'
    );
echo printInfo( "Talk information" );
echo dbTableToHTMLTable( 'talks', $talk
    , 'host,title,description', 'Submit', 'id,speaker,date,time,venue'
    );
echo '</form>';

echo goBackToPageLink( 'user.php', 'Go back' );

?>
