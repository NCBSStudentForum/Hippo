<?php

include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'AWS_ADMIN', 'AWS_ADMIN', 'ADMIN' ) );

include_once 'methods.php';
include_once 'database.php';
include_once 'tohtml.php';

if( $_POST['response'] == 'DO_NOTHING' )
{
    echo printInfo( "User said do nothing.");
    goBack( "admin_acad_manages_speakers.php", 0 );
    exit;
}
else if( $_POST['response'] == 'delete' )
{
    // We may or may not get email here. Email will be null if autocomplete was 
    // used in previous page. In most cases, user is likely to use autocomplete 
    // feature.
    if( strlen($_POST[ 'id' ]) > 0 )
        $res = deleteFromTable( 'speakers', 'id', $_POST );
    else
        $res = deleteFromTable( 'speakers', 'first_name,last_name,institute', $_POST );

    if( $res )
        echo printInfo( "Successfully deleted entry" );
    else
        echo minionEmbarrassed( "Failed to delete speaker from database" );
}
else if( $_POST['response'] == 'submit' )
{
    $imgpath = getSpeakerPicturePath( $_POST );

    if( array_key_exists( 'picture', $_FILES ) && $_FILES[ 'picture' ]['name'] )
    {
        echo printInfo( "Uploading speaker image to $imgpath .. " );
        $res = uploadImage( $_FILES[ 'picture' ], $imgpath );
        if( ! $res )
            echo minionEmbarrassed( "Could not upload speaker image to $imgpath" );
    }

    $res = null;
    if( __get__( $_POST, 'id', 0 ) > 0 )
        $whereKey = 'id';
    else
        $whereKey = 'first_name,middle_name,last_name';

    $speaker = getTableEntry( 'speakers', $whereKey, $_POST );

    if( $speaker )
    {
        // Update the entry
        $res = updateTable( 'speakers', $whereKey
                    , 'honorific,email,first_name,middle_name,last_name,' .
                        'department,homepage,institute'
                    , $_POST
                );

        // Update all talks speaker entries.
        $sid = $_POST[ 'id' ];
        $res = updateTable( 'talks', 'speaker_id', 'speaker'
            , array( 'speaker_id' => $sid, 'speaker' => speakerName( $sid ) )
            );
        if( $res )
            echo printInfo( " .. updated related talks as well " );
        
    }
    else
    {
        // Insert a new entry.
        $speakerId = getUniqueFieldValue( 'speakers', 'id' );
        $_POST[ 'id' ] = intval( $speakerId ) + 1;

        $res = insertIntoTable( 'speakers'
                    , 'id,honorific,email,first_name,middle_name,last_name,' .
                        'department,homepage,institute'
                    , $_POST 
                    );
    }

    if( $res )
        echo printInfo( 'Updated/Inserted speaker' );
    else
        echo printInfo( "Failed to update/insert speaker" );

}
else   
{
    echo alertUser( "Unknown/unsupported operation " . $_POST[ 'response' ] );
}

echo goBackToPageLink( 'admin_acad_manages_speakers.php', 'Go back' );


?>
