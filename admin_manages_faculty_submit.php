<?php

include_once "database.php";
include_once "tohtml.php";
include_once "check_access_permissions.php";

mustHaveAnyOfTheseRoles( array( 'ADMIN', 'AWS_ADMIN' ) );

if( $_POST['response'] == 'submit' )
{
    $_POST[ 'modified_on' ] = date( 'Y-m-d H:i:s', strtotime( 'now' ));
    $res = updateTable( 'faculty', 'email'
        , array( 'first_name' , 'middle_name', 'last_name'
        , 'status', 'modified_on', 'url', 'affiliation' ), $_POST 
        );

    if( $res )
    {
        echo printInfo( 'Successfully updated faculty' );
        goBack( 'admin_manages_faculty.php', 1 );
        exit;
    }
    else
    {
        echo minionEmbarrassed( "I could not update faculty" );
        echo goBackToPageLink( $_SERVER[ "HTTP_REFERER" ], "Go back" );
        exit;
    }
}
else if( $_POST['response'] == 'add' )
{
    $_POST[ 'modified_on' ] = date( 'Y-m-d H:i:s', strtotime( 'now' ));
    $res = insertIntoTable( 
        'faculty'
        , array( 'email', 'first_name' , 'middle_name', 'last_name'
        , 'status', 'modified_on', 'url', 'affiliation' ), $_POST 
        );

    if( $res )
    {
        echo printInfo( 'Successfully added a new faculty' );
        goToPage( $_SERVER[ "HTTP_REFERER" ], 1 );
        exit;
    }
    else
    {
        echo minionEmbarrassed( "I could not edit new faculty" );
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
