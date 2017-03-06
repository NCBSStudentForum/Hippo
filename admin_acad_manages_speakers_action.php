<?php

include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'AWS_ADMIN', 'ADMIN' ) );

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
    if( strlen($_POST[ 'email' ]) > 0 )
        $res = deleteFromTable( 'speakers', 'email', $_POST );
    else
        $res = deleteFromTable( 'speakers', 'first_name,last_name,institute', $_POST );

    if( $res )
        echo printInfo( "Successfully deleted entry" );
    else
        echo minionEmbarrassed( "Failed to delete speaker from database" );
}
else   // update
{
    $imgpath = getSpeakerPicturePath( $_POST );

    if( $_FILES && array_key_exists( 'picture', $_FILES ) )
        $res = uploadImage( $_FILES[ 'picture' ], $imgpath );

    if( ! $res )
        echo minionEmbarrassed( "Could not upload speaker image to $imgpath" );

    $res = insertOrUpdateTable( 'speakers'
            , 'honorific,email,first_name,middle_name,last_name,department,homepage,institute'
            , 'honorific,first_name,middle_name,last_name,department,homepage,institute'
            , $_POST 
        );

    if( $res )
    {
        echo printInfo( 'Updated/Inserted speaker' );
    }

}

echo goBackToPageLink( 'admin_acad_manages_speakers.php', 'Go back' );


?>
