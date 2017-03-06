<?php

include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'AWS_ADMIN' ) );

include_once 'header.php';
include_once 'database.php';
include_once 'mail.php';
include_once 'tohtml.php';

if( ! $_POST[ 'response' ] )
{
    // Go back to previous page.
    goToPage( "admin_acad.php", 0 );
    exit;
}
else if( $_POST[ 'response' ] == 'submit' )
{
    $res = updateTable( 'talks', 'id'
                , 'class,host,coordinator,title,description'
                , $_POST 
            );
    if( $res )
    {
        echo printInfo( 'Successfully updated entry' );
        echo goToPage( 'admin_aws_manages_talks.php' , 0 );
        exit;
    }
    else
        echo printWarning( "Failed to update the talk " );
}
else
    echo printInfo( "Unknown operation " . $_POST[ 'response' ] );
    

echo goBackToPageLink( 'admin_acad_manages_talks.php', "Go back" );
exit;

?>
