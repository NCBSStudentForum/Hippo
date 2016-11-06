<?php

include_once( 'header.php' );
include_once( 'database.php' );

var_dump( $_POST );

$res = updateUser( $_POST['login'], $_POST );
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
