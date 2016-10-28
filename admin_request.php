<?php 
include_once( "header.php" );
include_once( "methods.php" );

var_dump( $_POST );

if( $_POST['response'] == "approve" )
{
    // Approve after constructing all the events from the patterns.
    $r = getRequestById( $_POST['requestId'] );
}

goToPage( "admin.php", 5 );

?>
