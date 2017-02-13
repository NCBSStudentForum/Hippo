<?php

include_once 'header.php';
include_once 'database.php';
include_once 'mail.php';
include_once 'tohtml.php';
include_once './check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'USER' ) );


if( ! $_POST[ 'response' ] )
{
    // Go back to previous page.
    goToPage( $_SERVER[ 'HTTP_REFERER' ], 0 );
    exit;
}
else if( $_POST[ 'response' ] == 'delete' )
{
    $res = deleteFromTable( 'talks', 'id', $_POST );
    if( $res )
    {
        echo printInfo( 'Successfully delete entry' );
        // Delete the request as well.
        updateTable( 
            'bookmyvenue_requests', 'external_id', 'status'
            , array( 'external_id' => "talks." + $_POST[ 'id' ] 
                    , 'status' => 'CANCELLED' )
            );
        goBack( );
        exit;
    }
    else
        echo printWarning( "Failed to delete the talk " );
}
else if( $_POST[ 'response' ] == 'DO_NOTHING' )
{
    echo printInfo( "User said NO!" );
    goBack( $default = 'user.php' );
    exit;
}
else if( $_POST[ 'response' ] == 'edit' )
{
    echo printInfo( "Here you can only change the host, title and description
        of the talk." );

    $id = $_POST[ 'id' ];
    $talk = getTableEntry( 'talks', 'id', $_POST );

    echo '<form method="post" action="user_manage_talks_action_update.php">';
    echo dbTableToHTMLTable('talks', $talk, 'host,title,description', 'update');
    echo '</form>';
}
else if( $_POST[ 'response' ] == 'schedule' )
{
    // We are sending this to quickbook.php as GET request. Only external_id is 
    // sent to page.
    var_dump( $_POST );
    $external_id = "talks." . $_POST[ 'id' ];
    $query = "&external_id=".$external_id;
    header( "Location: quickbook.php?" . $query );
    exit;
}

echo goBackToPageLink( "user_manage_talk.php", "Go back" );
exit;

?>
