<?php
include_once( "header.php" );
include_once( "methods.php" );
include_once( "database.php" );

$res = submitRequest( $_POST );
if( $res )
{
    echo printInfo( 
        "Your request has been submitted and an email has been sent to you 
        with details.
        " );
    echo goBackToPageLink( "index.php", "Go back" );
}
else
{
    echo printWarning( 
        "Your request could not be submitted. Please notify the admin." 
    );
    echo goBackToPageLink( "index.php", "Go back");
}

?>
