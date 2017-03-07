<?php

include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'AWS_ADMIN', 'ADMIN' ) );

include_once 'methods.php';
include_once 'database.php';
include_once 'tohtml.php';

// We are here to send an email. 

$templ = json_decode( $_POST[ 'templalte' ] );
$_POST[ 'msg' ] = $templ[ 'email_body'] ;
echo dbTableToHTMLTable( 'emails', $_POST );

echo goBackToPageLink( 'admin_acad_email_and_docs.php', 'Go back' );


?>
