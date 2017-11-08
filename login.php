<?php 

include_once( "header.php" );
include_once( "methods.php" );
include_once( "database.php" );
include_once( "ldap.php" );

$conf = $_SESSION['conf'];
$login = $_POST['username'];

// If user use @instem.ncbs.res.in or @ncbs.res.in, ignore it.
$ldap = explode( '@', $login);
$ldap = $ldap[0];

$pass = $_POST['pass'];

$_SESSION['AUTHENTICATED'] = FALSE;

$auth = authenticateUsingLDAP( $ldap, $pass );

if(! $auth) 
{
    echo printErrorSevere("FATAL : Username or password is incorrect.");
    goToPage( 'index.php', 2 );
}
else 
{
    echo printInfo( "Login successful" );

    $_SESSION['AUTHENTICATED'] = TRUE;
    $_SESSION['user'] = $ldap;

    $ldapInfo = getUserInfoFromLdap( $ldap );
    $email = $ldapInfo[ 'email' ];
    $_SESSION['email'] = $email;

    $type = __get__( $ldapInfo, 'title', 'UNKNOWN' );

    // In any case, create a entry in database.
    createUserOrUpdateLogin( $ldap, $ldapInfo, $type );

    // Update email id.
    $res = updateTable( 'logins', 'login', 'email'
                , array( 'login' => $ldap, 'email' => $email )
            );

    // If user title is unspecified then redirect him/her to edit user info
    $userInfo = getUserInfo( $ldap );
    if( $userInfo['title'] == 'UNSPECIFIED' )
    {
       echo printInfo( "Please review your details " );
       goToPage( "user_info.php", 1 );
       exit;
    }

    goToPage( "user.php", 0 );
    exit;
}
?>
