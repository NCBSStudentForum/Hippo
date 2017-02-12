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

$talk = array( 
    );
    

function findSpeakerDetails( $email, $all )
{
    foreach( $all as $speaker )
        if( $speaker[ 'email' ] == $email )
            return $speaker;
    return null;
}

$visitors = getVisitors( );
$faculty = getFaculty( );

//var_dump( $visitors );

$allSpeakersSearchable = array_map( function( $x ) {
        return $x[ 'first_name' ] . ' ' . $x[ 'last_name' ] .
            ' (' . $x['email'] . ')' ; 
            } , $visitors 
        );

//echo printInfo( "Total speakers in my database " . count( $visitors ) );

if( array_key_exists( 'response', $_POST ) )
{
    //var_dump( $_POST );

    preg_match( "/.*\((.+@.+)\)/", $_POST[ 'speaker' ], $email);
    if( count( $email ) > 0 )
    {
        $email = $email[1];
        $default[ 'email' ] = $email;
    }

    $speaker = findSpeakerDetails( $email, $visitors );
    if( $speaker )
        $default = array_merge( $default, $speaker );

    // Now if response is AddNew, add a new visitor else update it.
    if( $_POST[ 'response' ] == 'AddNewSpeaker' )
        $res = insertIntoTable( 'visitors'
            , 'email,first_name,middle_name,last_name,department,institute'
            , $_POST 
            );
    else if( $_POST[ 'response' ] == "SelectSpeaker" )
    {
        if( array_key_exists( 'id', $_POST) && $_POST[ 'id' ] )
            $res = updateTable( 'visitors'
                , 'id'
                , 'email,first_name,middle_name,last_name,department,institute'
                , $default 
                );
    }
    else
        echo printInfo( "Unknown response " . $_POST[ 'response' ] );
}
    
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
    , 'host,title,description', 'Submit', 'id,speaker'
    );
echo '</form>';

?>
