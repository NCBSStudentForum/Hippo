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
    echo '<div style="float:left">';
    echo goBackToPageLink( "user.php", "Go back" );
    echo '</div>';
}
else
{
    echo printWarning( 
        "Your request could not be submitted. Please notify the admin." 
    );
    echo '<div style="float:left">';
    echo goBackToPageLink( "user.php", "Go back" );
    echo '</div>';
}

?>
