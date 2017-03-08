<?php

include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'USER' ) );

include_once "header.php" ;
include_once "methods.php" ;
include_once "database.php" ;
include_once "mail.php";
include_once 'tohtml.php';

// verify the request.
function verifyRequest( $request )
{
    if( ! isset( $request ) )
        return "Empty request";

    // Check the end_time must be later than start_time .
    // At least 15 minutes event
    if( strtotime( $request['end_time'] ) - strtotime( $request['start_time'] ) < 900 )
    {
        $msg = "The event must be at least 15 minute long";
        $msg .= " Start time " . $request[ 'start_time' ] . " to end time " .
            $request[ 'end_time' ];
        return $msg;
    }
    if( ! isset( $request['venue'] ) )
    {
        return "No venue found in your request. If you think this is a bug, 
           please write to hippo@lists.ncbs.res.in " ;
    }
    return "OK";
}


$msg = verifyRequest( $_POST );

if( $msg == "OK" )
{
    // Generate repeat pattern from days, week and month repeat patter. If we 
    // are coming here from quickbook.php, it may not be here.
    if( array_key_exists( 'day_pattern', $_POST ) )
    {
        $repeatPat = constructRepeatPattern( 
            $_POST['day_pattern'], $_POST['week_pattern'] , $_POST['month_pattern']
            );

        echo "<pre>Repeat pattern $repeatPat </pre>";
        $_POST['repeat_pat']  = $repeatPat;
    }

    $gid = submitRequest( $_POST );

    // Unset POST here so refresh page does not cause creation of another 
    // request.
    $_POST = array( );

    if( $gid > 0 )
    {
        $userInfo = getLoginInfo( $_SESSION[ 'user' ] );
        $userEmail = $userInfo[ 'email' ];
        $msg = initUserMsg( $_SESSION[ 'user' ] );

        $msg .= "<p>Your booking request id $gid has been created. </p>";
        $msg .= arrayToVerticalTableHTML( getRequestByGroupId( $gid )[0], 'request' );
        $msg .= "<p>You can edit/cancel the request anytime you like </p>";

        sendPlainTextEmail( $msg
            , "Your booking request (id-$gid) has been recieved"
            , $userEmail 
            );

        // Send email to hippo@lists.ncbs.res.in 
        sendPlainTextEmail( "<p>Details are following </p>" . $msg
            , "A new booking request has been created by $userEmail"
            , 'hippo@lists.ncbs.res.in'
            );

        //goToPage( "user.php", 1 );
        //exit;
    }
    else
    {
        echo printWarning( 
            "Your request could not be submitted. Please notify the admin." 
        );
        echo goBackToPageLink( "user.php", "Go back" );
        exit;
    }
}
else
{
    echo printWarning( "There was an error in request" );
    echo printWarning( $msg );
    echo goBackToPageLink( "user.php", "Go back" );
    exit;
}

echo goBackToPageLink( "user.php", "Go back" );


?>
