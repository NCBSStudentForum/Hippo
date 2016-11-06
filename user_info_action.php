<?php

include_once( 'header.php' );
include_once( 'database.php' );

$res = updateTable( "logins", "login"
    , Array( "valid_until", "title", "laboffice" )
    , $_POST 
);

if( $res )
{
    echo printInfo( "User details have been updated sucessfully" );
    goToPage( 'user.php', 1 );
    exit;
}

echo printWarning( "Could not update user details " );
echo goBackToPageLink( "user.php", "Go back" );
exit;

?>
