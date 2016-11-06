<?php

include_once( 'header.php' );
include_once( 'database.php' );
include_once( 'tohtml.php' );

echo userHTML( );


$userInfo = getUserInfo( $_SESSION['user'] );

echo "<h3>Your details from LDAP</h3>";

echo alterUser( "Please note that following details can not be edited by YOU.
    These are fetched from centeralized LDAP server. <br> 
    If something is wrong here, please write to academic office and/or IT section 
    " );

echo arrayToVerticalTableHTML( $userInfo, "user", ''
   , Array( 'roles', 'status', 'institute', 'created_on', 'last_login',
   'valid_until', 'alternative_email' )
);

echo "<h3>Edit details </h3>";

echo "<form method=\"post\" action=\"user_info_action.php\">";
echo dbTableToHTMLTable( 'logins', $userInfo
      , $editables = Array( 'title', 'institute', 'valid_until' )
      );
echo "</form>";

echo goBackToPageLink( "user.php", "Go back" );

?>
