<?php 

include_once 'header.php' ;
include_once 'check_access_permissions.php' ;
include_once 'tohtml.php' ;
include_once 'ldap.php';

mustHaveAnyOfTheseRoles( Array( 'ADMIN', 'AWS_ADMIN' ) );

echo userHTML( );

if( ! array_key_exists( 'login', $_POST ) )
{
    echo printInfo( "You didn't select anyone. Going back ... " );
    goToPage( 'admin_acad.php', 1 );
    exit;
}

$default = getUserInfo( $_POST['login'] );
if( ! $default )
{
    echo printWarning( 
        "Invalid username. I did not find anyone named " .
        $_POST[ 'login' ] . " on LDAP server" );
    echo goBackToPageLink( 'admin_acad.php', 'Go back' );
    exit;
}

echo '<form method="post" action="admin_acad_update_user_submit.php">';
echo dbTableToHTMLTable(
    'logins', $default
    , Array( 'status', 'title', 'eligible_for_aws', 'joined_on' ) 
    , 'submit'
    );
echo '</form>';

echo goBackToPageLink( 'admin_acad.php', 'Go back' );

?>
