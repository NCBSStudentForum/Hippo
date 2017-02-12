<?php

include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'USER' ) );

include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );

// Logic for POST requests.
$speaker = array( 
    'first_name' => '', 'middle_name' => '', 'last_name' => '', 'email' => ''
    , 'department' => '', 'institute' => '', 'title' => '', 'id' => ''
    , 'homepage' => ''
    );

$talk = array( 'created_by' => $_SESSION[ 'user' ] 
    , 'created_on' => dbDateTime( 'now' )
    );

?>

<script>
$(function() {
    var logins = <?php echo json_encode( $allSpeakersSearchable ); ?>;
    $( "#autocomplete_user" ).autocomplete( { source : logins }); 
});
</script>

<?php
echo '<form method="post" action="user_register_talk_action.php">';
echo printInfo( "Speaker details" );
echo dbTableToHTMLTable( 'visitors', $speaker 
    , 'title,email,first_name,middle_name,last_name,department,institute'
    , '', 'id'
    );
echo printInfo( "Talk information" );
echo dbTableToHTMLTable( 'talks', $talk
    , 'host,title,description', 'Submit', 'id,speaker,date,time,venue'
    );
echo '</form>';

?>
