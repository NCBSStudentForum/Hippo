<?php

include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'USER' ) );

include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );

// Logic for POST requests.
$default = array( 
    'first_name' => '', 'middle_name' => '', 'last_name' => '', 'email' => ''
    , 'department' => '', 'institute' => '', 'title' => '', 'id' => ''
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

var_dump( $visitors );

$allSpeakersSearchable = array_map( function( $x ) {
        return $x[ 'first_name' ] . ' ' . $x[ 'last_name' ] .
            ' (' . $x['email'] . ')' ; 
            } , $visitors 
        );

echo printInfo( "Total speakers in my database " . count( $visitors ) );

if( array_key_exists( 'response', $_POST ) )
{
    echo "Post";
    var_dump( $_POST );

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
    

echo '<h3>Step 1 : Register your speaker </h3>';
?>

<script>
$(function() {
    var logins = <?php echo json_encode( $allSpeakersSearchable ); ?>;
    $( "#autocomplete_user" ).autocomplete( { source : logins }); 
});
</script>

<form method="post" action="">
<table class="tasks">
    <tr>
        <td>Type the email of speaker, I may be able to find him if (s)he has
            visited before. If you don't know the email, leave it blank.
        </td>
        <td>
            <input type="input" name="speaker" id="autocomplete_user" value="" />
            <button type="submit" name="response" value="SelectSpeaker">Select speaker</button>
        </td>
    </tr>
</table>
</form>

<?php

if( array_key_exists( 'response', $_POST ) && 
    $_POST[ 'response' ] == 'SelectSpeaker'
    )
{
    echo '<form method="post" action="">';
    $whatToDo = 'AddNewSpeaker';
    if( $default[ 'id' ] )
        $whatToDo = 'UpdateSpeaker';

    echo dbTableToHTMLTable( 'visitors', $default 
        , 'title,email,first_name,middle_name,last_name,department,institute'
        , $whatToDo
    );
    echo '</form>';
}

?>
