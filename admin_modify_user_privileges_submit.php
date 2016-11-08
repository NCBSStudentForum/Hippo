<?php 

include_once( 'header.php' );
include_once( 'check_access_permissions.php' );
include_once( 'tohtml.php' );
include_once( 'database.php' );

echo userHTML( );

mustHaveAnyOfTheseRoles( Array( 'ADMIN' ) );

//var_dump( $_POST );

$res = updateTable( 'logins', 'login', Array( 'roles', 'title' ), $_POST ); 
if( $res )
{
    echo printInfo( "Successfully updated the user" );
    goToPage( 'admin.php', 1 );
    exit;
}
echo goBackToPageLink( 'admin.php', 'Go back' );

?>
