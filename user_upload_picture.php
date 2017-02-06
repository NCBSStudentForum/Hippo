<?php 

include_once 'methods.php';
include_once 'check_access_permissions.php';

mustHaveAnyOfTheseRoles( array( "USER" ) );

if( $_POST[ 'Response' ] == 'upload' )
{
    $img = $_FILES[ 'picture' ];

    if( $img[ 'error' ] != UPLOAD_ERR_OK )
    {
        echo minionEmbarrassed( "This file could not be uploaded" );
        exit;
    }

    $tmppath = $img[ 'tmp_name' ];

    if( ! move_uploaded_file( 
        $tmppath , sprintf( __DIR__ . '/pictures/%s', $_SESSION[ 'user' ] )
    )) {
        echo minionEmbarrassed( "I could not upload your image!" );
        exit;
    }
    else
    {
        echo printInfo( "File is uploaded sucessfully" );
        goBack( "user_info.php", 1 );
    }
}

echo goBackToPageLink( 'user_info.php' );

?>
