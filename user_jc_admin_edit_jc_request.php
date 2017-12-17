<?php

include_once 'header.php';
include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';
include_once 'mail.php';

echo userHTML( );

// If current user does not have the privileges, send her back to  home
// page.
if( ! isJCAdmin( $_SESSION[ 'user' ] ) )
{
    echo printWarning( "You don't have permission to access this page" );
    echo goToPage( "user.php", 2 );
    exit;
}

if( __get__( $_POST, 'response', '' ) == 'submit' )
{

    $res = updateTable( 'jc_requests', 'id', 'date', $_POST);
    if( $res )
    {
        $entry = getTableEntry( 'jc_requests', 'id', $_POST );

        $presenter = getLoginInfo( $entry[ 'presenter' ] );
        $entryHTML = arrayToVerticalTableHTML($entry, 'info');

        $msg = "<p>Dear " . arrayToName( $presenter ) . "</p>";
        $msg .= "<p>Your presentation request has been rescheduled by admin.
            the latest entry is following. </p>";
        $msg .= $entryHTML;
        $subject = 'Your presentation request date is changed by JC admin';
        $to = $presenter['email'];
        $res = sendHTMLEmail( $msg, $subject, $to );
        echo printInfo( 'Successfully updated presentation entry.' );
    }
}

else if( __get__( $_POST, 'response', '' ) == 'delete' )
{
    $_POST[ 'status' ] = 'CANCELLED';
    $res = updateTable( 'jc_requests', 'id', 'status', $_POST);
    if( $res )
    {
        $entry = getTableEntry( 'jc_requests', 'id', $_POST );

        $presenter = getLoginInfo( $entry[ 'presenter' ] );
        $entryHTML = arrayToVerticalTableHTML($entry, 'info');

        $msg = "<p>Dear " . arrayToName( $presenter ) . "</p>";
        $msg .= "<p>Your presentation request has been cancelled by admin.
                    the latest entry is following. </p>";
        $msg .= $entryHTML;

        $subject = 'Your presentation request is CANCELLED by JC admin';
        $to = $presenter['email'];
        $res = sendHTMLEmail( $msg, $subject, $to );
        if( $res )
        {
            echo printInfo( 'Successfully updated presentation entry.' );
            goToPage( 'user_jc_admin.php', 1 );
            exit;
        }

    }
}
else if( __get__( $_POST, 'response', '' ) == 'DO_NOTHING' )
{
    goToPage( 'user_jc_admin.php', 1 );
    exit;
}

echo '<h1>Edit presentation request</h1>';

$editables = 'date';
if( __get__( $_POST, 'response', '' ) == 'Reschedule' )
{
    $editables = 'date';
}

$entry = getTableEntry( 'jc_requests', 'id', $_POST );
echo '<form action="#" method="post" accept-charset="utf-8">';
echo dbTableToHTMLTable( 'jc_requests', $entry, $editables );
echo '</form>';


echo " <br /> <br /> ";
echo "<strong>Afer your are finished editing </strong>";
echo goBackToPageLink( 'user_jc_admin.php', 'Go Back' );

?>
