<?php 

include_once 'header.php' ;
include_once 'check_access_permissions.php' ;
include_once 'tohtml.php' ;
include_once 'database.php' ;
include_once 'methods.php';
include_once 'mail.php';


echo userHTML( );

mustHaveAnyOfTheseRoles( Array( 'AWS_ADMIN' ) );

$toUpdate = array( 'title', 'joined_on', 'eligible_for_aws', 'status' );
$res = updateTable( 'logins', 'login', $toUpdate, $_POST ); 
if( $res )
{
    echo printInfo( "Successfully updated : " . implode(',', $toUpdate)  );
    if( $_POST[ 'eligible_for_aws' ] == 'YES' )
    {
        $login = $_POST[ 'login' ];
        $msg = initUserMsg( $login );
        $msg .= "<p>Your name has been added to the list of AWS spakers. 
            If this is a mistake, please contact academic office </p>";
        $subject = "Your name has been added to AWS list";
        $to = getLoginEmail( $login );
        sendHTMLEmail( $msg, $subject, $to, 'hippo@lists.ncbs.res.in' );
    }

    // Rerun the scheduling script every time a change is made.
    rescheduleAWS( );
    goToPage( 'admin_acad.php', 1 );
    exit;
}

echo goBackToPageLink( 'admin.php', 'Go back' );

?>
