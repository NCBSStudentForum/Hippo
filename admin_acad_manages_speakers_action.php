<?php

include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'AWS_ADMIN', 'ADMIN' ) );

include_once 'methods.php';
include_once 'database.php';
include_once 'tohtml.php';

$imgpath = getSpeakerPicturePath( $_POST );
if( $_FILES && array_key_exists( 'picture', $_FILES ) )
    uploadImage( $_FILES[ 'picture' ], $imgpath );

$res = insertOrUpdateTable( 'speakers'
        , 'email,title,first_name,middle_name,last_name,department,homepage,institute'
        , 'title,first_name,middle_name,last_name,department,homepage,institute'
        , $_POST 
    );

if( $res )
{
    echo printInfo( 'Updated/Inserted speaker' );
}

echo goBackToPageLink( 'admin_acad_manages_speakers.php', 'Go back' );



?>
