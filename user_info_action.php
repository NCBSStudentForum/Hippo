<?php

include_once 'header.php';
include_once 'database.php';
include_once 'mail.php';
include_once 'tohtml.php';

$res = updateTable( "logins", "login"
    , Array( "valid_until", "title", "laboffice"
        , 'joined_on' , 'alternative_email' )
    , $_POST 
    );

if( $res )
{
    echo printInfo( "User details have been updated sucessfully" );
    // Now send an email to user.
    $info = getUserInfo( $_SESSION[ 'user' ] );

    sendEmail( dbTableToHTMLTable( "logins", $info )
        , "Your details have been updated successfully."
        , $info[ 'email' ]
        );

    goToPage( 'user.php', 1 );
    exit;
}

echo printWarning( "Could not update user details " );
echo goBackToPageLink( "user.php", "Go back" );
exit;

?>
