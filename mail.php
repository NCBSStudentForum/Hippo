<?php

function sendEmail($msg, $sub, $to) 
{
    $timestamp = date( 'r', strtotime( 'now' ) );

    $msg .= "
        <p>==========================================================</p>
        <p>
        This is a computer generated email, you need not reply. In case of 
        any query, please write to acadoffice@ncbs.res.in.  </p>

        <p>If you are not the intended recipeint of this email, please write 
        to acadoffice@ncbs.res.in . </p>
        ";

    $msg .= "<p>Created on : $timestamp </p>";
    $msgfile = "/tmp/__msg__.html";

    file_put_contents( $msgfile, $msg );
    $to = trim( $to );

    $cmd= __DIR__ . "/sendmail.py '$to' '$sub' '$msgfile' 2>&1 | tee /tmp/_sendmail.log_";
    $out = shell_exec( $cmd );

    unlink( $msgfile );

    return $out;
}

// $res = sendEmail( "testing"
//     , "Your request has been created"
//     , "dilawars@ncbs.res.in" 
//     );

?>
