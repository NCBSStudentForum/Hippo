<?php

include_once( 'header.php' );
include_once( 'database.php' );
include_once( 'tohtml.php' );

echo userHTML( );


$userInfo = getUserInfo( $_SESSION['user'] );

echo "<h3>Your details from LDAP</h3>";

echo arrayToVerticalTableHTML( $userInfo, "user", ''
   , Array( 'roles', 'status', 'institute', 'created_on', 'last_login',
   'valid_until', 'alternative_email' )
);

echo "<h3>Edit details </h3>";

echo alertUser( "&#x26a0 Atlest, select your TITLE and JOINED ON date." );

echo "<form method=\"post\" action=\"user_info_action.php\">";
echo dbTableToHTMLTable( 'logins', $userInfo
    , $editables = Array( 'title', 'first_name', 'last_name'
        , 'alternative_email' , 'institute', 'valid_until', 'joined_on'
    )
      );
echo "</form>";

if( ! $userInfo['eligible_for_aws'] )
    echo printWarning( "If you should be 'ELIGIBLE FOR AWS', let academic office know." );

echo goBackToPageLink( "user.php", "Go back" );

?>
