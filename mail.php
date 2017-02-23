<?php

include_once 'database.php';

function sendEmail($msg, $sub, $to) 
{

    if( ! array_key_exists( 'send_emails', getConf( )['global' ] ) )
    {
        echo printInfo( "Email service has not been configured." );
        error_log( "Mail service is not configured" );
        return;
    }

    if( $_SESSION[ 'conf' ]['global']['send_emails' ] == false )
        return;

    $timestamp = date( 'r', strtotime( 'now' ) );

    $msg .= "
        <p>==========================================================</p>
        <p>
        This is a computer generated email, you need not reply. In case of 
        any query, please write to hippo@lists.ncbs.res.in. 
        </p>
        <p>Email generated on : $timestamp </p>
        <p>==========================================================</p>
        ";

    $msgfile = "/tmp/__msg__.html";

    file_put_contents( $msgfile, $msg );
    $to = trim( $to );

    $cmd= __DIR__ . "/sendmail.py \"$to\" \"$sub\" \"$msgfile\"";
    $out = exec( $cmd, $output, $ret );
    unlink( $msgfile );
    return true;
}

// $res = sendEmail( "testing"
//     , "Your request has been created"
//     , "dilawars@ncbs.res.in" 
//     );

?>
