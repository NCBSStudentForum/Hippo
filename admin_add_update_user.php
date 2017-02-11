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
    goToPage( 'admin.php', 1 );
    exit;
}

$default = getUserInfo( $_POST['login'] );
$buttonVal = 'Update';

if( ! $default )
{
    $default = getUserInfoFromLdap( $_POST[ 'login' ] );
    if( ! $default )
    {
        echo printWarning( 
            "Invalid username. I did not find anyone named " .
            $_POST[ 'login' ] . " on LDAP server" );
        echo goBackToPageLink( 'admin.php', 'Go back' );
        exit;
    }

    $default[ 'login' ] = $_POST[ 'login' ];
    $buttonVal = 'Add New';
}

echo '<form method="post" action="admin_add_update_user_submit.php">';
echo dbTableToHTMLTable(
    'logins', $default
    , Array( 'roles', 'status', 'title', 'eligible_for_aws', 'joined_on' ) 
    , $buttonVal
    );

echo  '<br/><br/>';
echo '<button type="submit" name="response" value="Delete">Delete User!</button>';
echo '</form>';

echo goBackToPageLink( 'admin.php', 'Go back' );

?>
