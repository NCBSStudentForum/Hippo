<?php
include_once( "header.php" );
include_once( "methods.php" );
include_once( "database.php" );

//var_dump( $_POST );

if( $_POST[ "response" ] == "Go back" )
{
    goToPage( "index.php", 0 );
    exit( 0 );
}

$res = submitRequest( $_POST );
if( $res )
{
    echo printInfo( 
        "Your request has been submitted and an email has been sent to you 
        with details.
        " );
    //goToPage( "index.php", 5 );
}
else
{
    echo printWarning( 
        "Your request could not be submitted. Please notify the admin." 
    );
    //goToPage( "index.php", 10 );
}

?>
