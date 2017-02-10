<?php

include_once 'database.php';

function sendEmail($msg, $sub, $to) 
{
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

    $cmd= __DIR__ . "/sendmail.py '$to' '$sub' '$msgfile' ";
    $out = exec( $cmd, $output, $ret );
    return $ret;
}

function sendEmailById( $id )
{
    $email = getEmailById( $id );
    if( ! $email )
        return false;

    $msg = $email[ 'msg' ];
    $sub = $email[ 'subject' ];
    $to = $email[ 'recipients' ];
    return sendEmail( $msg, $sub, $to );
}

// $res = sendEmail( "testing"
//     , "Your request has been created"
//     , "dilawars@ncbs.res.in" 
//     );

?>
