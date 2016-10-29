<?php 
include_once( "header.php" );
include_once( "methods.php" );

var_dump( $_POST );

if( $_POST['response'] == "approve" )
{
    // Approve after constructing all the events from the patterns.
    $r = getRequestById( $_POST['requestId'] );
    // First insert this request into event calendar.
    var_dump( $r );
}

//goToPage( "admin.php", 5 );

?>
