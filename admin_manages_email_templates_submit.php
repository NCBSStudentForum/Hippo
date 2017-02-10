<?php

include_once "database.php";
include_once "tohtml.php";
include_once "check_access_permissions.php";

mustHaveAnyOfTheseRoles( array( 'ADMIN' ) );

if( $_POST['response'] == 'edit' )
{
    $_POST[ 'modified_on' ] = date( 'Y-m-d H:i:s', strtotime( 'now' ));
    $res = updateTable( 'email_templates', 'id'
        , array('when_to_send', 'description' ), $_POST
    );

    if( $res )
    {
        echo printInfo( 'Successfully updated email templates' );
        goToPage( $_SERVER[ "HTTP_REFERER" ], 1 );
        exit;
    }
    else
    {
        echo minionEmbarrassed( "I could not update email templates" );
        echo goBackToPageLink( $_SERVER[ "HTTP_REFERER" ], "Go back" );
        exit;
    }
}
else if( $_POST['response'] == 'add' )
{
    $_POST[ 'modified_on' ] = date( 'Y-m-d H:i:s', strtotime( 'now' ));
    $res = insertIntoTable( 
        'email_templates' , array( 'id', 'when_to_send', 'description'), $_POST 
        );

    if( $res )
    {
        echo printInfo( 'Successfully added a new email template' );
        goToPage( $_SERVER[ "HTTP_REFERER" ], 1 );
        exit;
    }
    else
    {
        echo minionEmbarrassed( "I could not add new email template" );
        echo goBackToPageLink( $_SERVER[ "HTTP_REFERER" ], "Go back" );
        exit;
    }
}
else
{
    echo printWarning( "Unknown response code from server " . $_POST[ 'response' ]
    );
    echo goBackToPageLink( $_SERVER[ "HTTP_REFERER"], "Go back" );
    exit;
}


?>
