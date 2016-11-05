<?php 

include_once( "header.php" );
include_once( "methods.php" );
include_once( "database.php" );

$conf = $_SESSION['conf'];
$ldap = $_POST['username'];
$pass = $_POST['pass'];

$_SESSION['AUTHENTICATED'] = FALSE;

/* continue */
$conn = imap_open( "{imap.ncbs.res.in:993/ssl/readonly}INBOX", $ldap, $pass, OP_HALFOPEN );
if(!$conn) 
{
    echo printErrorSevere("FATAL : Username or password is incorrect.");
    goToPage( 'index.php', 2 );
}

else 
{
    echo printInfo( "Login successful" );
    createUserOrUpdateLogin( $ldap );
    imap_close( $conn );
    $_SESSION['AUTHENTICATED'] = TRUE;
    $_SESSION['user'] = $ldap;
    goToPage( "user.php", 0 );
}

?>
