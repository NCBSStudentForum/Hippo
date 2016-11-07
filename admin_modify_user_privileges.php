<?php 

include_once( 'header.php' );
include_once( 'check_access_permissions.php' );
include_once( 'tohtml.php' );

mustHaveAnyOfTheseRoles( Array( 'ADMIN' ) );
echo userHTML( );

if( ! $_POST['login'] )
{
    echo printInfo( "You didn't select anyone. Going back ... " );
    goToPage( 'admin.php', 1 );
    exit;
}

$default = getUserInfo( $_POST['login'] );

echo '<form method="post" action="admin_modify_user_privileges_submit.php">';
echo dbTableToHTMLTable( 'logins', $default, $editables = Array( 'roles', 'title' ) );
echo '</form>';

echo goBackToPageLink( 'admin.php', 'Go back' );

?>
