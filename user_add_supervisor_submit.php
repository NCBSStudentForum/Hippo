<?php 

include_once( "header.php" );
include_once( "methods.php" );
include_once( 'tohtml.php' );
include_once( "check_access_permissions.php" );

if( trim( $_POST[ 'email'] ) && trim( $_POST[ 'first_name' ] ) )
{

    try {
        $res = insertIntoTable( 'supervisors'
            , Array( "email", "first_name", "last_name", "affiliation", "url" )
            , $_POST 
        );
    } catch ( PDOException $e ) {

        echo printWarning( "Could not add supervisor" );
        echo printWarning( "\t Error was " . $e->getMessage( ) );
        echo goBackToPageLink( "user_aws_edit_request.php", "Go back to AWS" );
        exit( 0 );
    }

    if( $res )
    {
        echo printInfo( "Successfully added supervisor to list" );
        goToPage( "user_add_supervisor.php", 1 );
        exit;
    }
    else
    {
        echo printWarning( "Could not add supervisor" );
    }
}
else
{
    echo printWarning( "Incomplete details. Try again .." );
    goToPage( "user_add_supervisor.php", 1 );
    exit;
}


echo goBackToPageLink( "user_aws_edit_request.php", "Go back to AWS" );

?>
