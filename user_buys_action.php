<?php 

include_once "header.php";
include_once "methods.php";
include_once "database.php";
include_once 'tohtml.php';
include_once 'mail.php';
include_once 'check_access_permissions.php';


mustHaveAnyOfTheseRoles( array( 'USER' ) );

echo userHTML( );

// Create a bid.
if( $_POST[ 'response' ] == 'NewBid' )
{
    echo printInfo( "Creating a new bid .. " );
    $totalEntries = getNumberOfEntries( 'nilami_bids', 'id' );
    $id = intval( __get__( $totalEntries, 'id', 0 ) ) + 1;

    $_POST[ 'id' ] = $id;
    $_POST[ 'created_by' ] = $_SESSION[ 'user' ];
    $_POST[ 'created_on' ] = dbDateTime( 'now' );
    $_POST[ 'contact_info' ] = getLoginEmail( $_SESSION[ 'user' ] );

    // If old bid already exists then update else create a new entry.
    $oldBid = getTableEntry( 'nilami_bids', 'item_id,created_by', $_POST );

    $res = null;
    if( $oldBid )
    {
        $_POST[ 'status' ] = 'VALID';
        $_POST[ 'last_modified_on' ] = dbDateTime( 'now' );
        $res = updateTable( 'nilami_bids', 'item_id,created_by'
                    , 'bid,status', $_POST );
    }
    else
        $res = insertIntoTable(
                'nilami_bids'
                , 'id,created_by,created_on,item_id,bid,status,contact_info'
                , $_POST 
            );

    if( $res )
    {
        echo printInfo( "Successfully added your bid ... " );

        // Send email.
        $item = getTableEntry( 'nilami_items', 'id'
                        , array( 'id' => $_POST[ 'item_id' ] ) 
                    );
        $msg = initUserMsg( $item[ 'created_by' ] );
    
        $to = getLoginEmail( $item[ 'created_by' ] );
        $cclist = getLoginEmail( $_POST[ 'created_by' ] );
        $subject = 'A new bid has been made on your entry by ' . $cclist ;

        $msg = arrayToTableHTML( $_POST, 'info' );
        $msg .= "<br>";
        $msg .= "<p> Comment : " . $_POST[ 'comment' ] . "</p>";
        sendPlainTextEmail( $msg, $subject, $to, $cclist );

        echo goBack( "user_buys.php", 0 );
        exit;
    }
    else
        echo alertUser( "Could not create your entry" );
}
if( $_POST[ 'response' ] == 'Update Bid' )
{
    $res = updateTable( 'nilami_bids', 'id', 'bid,status', $_POST );
    if( $res )
    {
        echo printInfo( "Successfully updated your bid " );
        echo goBack( "user_buys.php", 0 );
        exit;
    }
    else
        echo minionEmbarrassed( "Could not update your bid!" );
}


echo goBackToPageLink( "user.php", "Go back" );

?>
