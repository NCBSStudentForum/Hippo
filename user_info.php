<?php

include_once( 'header.php' );
include_once( 'database.php' );
include_once( 'tohtml.php' );

echo userHTML( );


$user = getUserInfo( $_SESSION['user'] );

echo printInfo( "Please note that MOST of following details can not be edited by YOU.
    These are fetched from centeralized LDAP server. <br> 
    If something is wrong here, please write to academic office and/or IT section 
    " );

echo "<h3>Your details</h3>";
echo dbTableToHTMLTable( 'users', $user
    , $editables = Array( 'alternative_email' )
);


echo goBackToPageLink( "user.php", "Go back" );

?>
