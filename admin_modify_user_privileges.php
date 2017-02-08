<?php 

include_once( 'header.php' );
include_once( 'check_access_permissions.php' );
include_once( 'tohtml.php' );

mustHaveAnyOfTheseRoles( Array( 'ADMIN', 'AWS_ADMIN' ) );

echo userHTML( );

if( ! array_key_exists( 'login', $_POST ) )
{
    echo printInfo( "You didn't select anyone. Going back ... " );
    goToPage( 'admin.php', 1 );
    exit;
}

$default = getUserInfo( $_POST['login'] );

echo '<form method="post" action="admin_modify_user_privileges_submit.php">';
echo dbTableToHTMLTable( 'logins', $default
    , $editables = Array( 'roles', 'status', 'title', 'eligible_for_aws', 'joined_on' ) );
echo '</form>';

echo goBackToPageLink( 'admin.php', 'Go back' );

?>
