<?php

include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'USER' ) );

include_once 'header.php';
include_once 'database.php';
include_once 'mail.php';
include_once 'tohtml.php';

if( ! $_POST[ 'response' ] )
{
    // Go back to previous page.
    goToPage( "user_manage_talk.php", 0 );
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
        echo goToPage( 'user_manage_talk.php' , 0 );
        exit;
    }
    else
        echo printWarning( "Failed to update the talk " );
}
else
    echo printInfo( "Unknown operation " . $_POST[ 'response' ] );
    

echo goBackToPageLink( 'user_manage_talk.php', "Go back" );
exit;

?>
