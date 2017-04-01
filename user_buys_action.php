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
    var_dump( $_POST );
    echo printInfo( "Creating a new bid .. " );
    $totalEntries = getNumberOfEntries( 'nilami_bids', 'id' );
    $id = intval( __get__( $totalEntries, 'id', 0 ) ) + 1;

    $_POST[ 'id' ] = $id;
    $_POST[ 'created_by' ] = $_SESSION[ 'user' ];
    $_POST[ 'created_on' ] = dbDateTime( 'now' );
    $_POST[ 'contact_info' ] = getLoginEmail( $_SESSION[ 'user' ] );

    $res = insertIntoTable(
            'nilami_bids'
            , 'id,created_by,created_on,item_id,bid,status,contact_info'
            , $_POST 
        );

    if( $res )
    {
        echo printInfo( "Successfully put your bid ... " );

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
if( $_POST[ 'response' ] == 'UpdateBid' )
{
    echo printInfo( "Updating bid ..." );
}



echo goBackToPageLink( "user.php", "Go back" );

?>
