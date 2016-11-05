<?php 

include_once( "header.php" );
include_once( "methods.php" );
include_once( 'tohtml.php' );
include_once( "check_access_permissions.php" );

$res = insertIntoTable( 'supervisors'
    , Array( "email", "first_name", "last_name", "url" )
    , $_POST 
);

if( $res )
{
    echo printInfo( "Successfully added supervisor to list" );
    goToPage( "user_add_supervisor.php", 1 );
}
else
{
    echo printWarning( "Could not add supervisor" );
    echo userHTML( );
}

echo goBackToPageLink( "user_add_aws.php", "Go back to AWS" );

?>
