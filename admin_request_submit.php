<?php
include_once( "header.php" );
include_once( "database.php" );

if( ! array_key_exists( 'request', $_POST ) )
{
    echo printWarning( "You did not select any request" );
    goToPage( "admin.php", 2 );
    exit(0);
}

$requests = $_POST['request'];
$whatToDo = $_POST['response'];

foreach( $requests as $request ) {
    var_dump( $request );
    echo "<br>";
}


//goToPage( "admin.php", 1 );

?>
