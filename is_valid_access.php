<?php

include_once( "header.php" );
include_once( 'methods.php' );

if( ! (array_key_exists( 'AUTHENTICATED', $_SESSION) && $_SESSION['AUTHENTICATED'] == true) )
{
    echo printWarning( "You should not be here. Not authenticated" );
    goToPage( "index.php", 3 );
}

?>
