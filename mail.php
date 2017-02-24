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

    $msgfile = tempnam( '/tmp', 'hippo_temp_msg' );

    file_put_contents( $msgfile, $msg );
    $to = implode( ' -t ', explode( ',', trim( $to ) ) );

    $cmd= __DIR__ . "/sendmail.py \"$to\" \"-s $sub\" \"-i $msgfile\"";
    $out = exec( $cmd, $output, $ret );
    unlink( $msgfile );
    return true;
}

function sendPlainTextEmail($msg, $sub, $to, $attachment = null) 
{
    if( ! array_key_exists( 'send_emails', getConf( )['global' ] ) )
    {
        echo printInfo( "Email service has not been configured." );
        error_log( "Mail service is not configured" );
        return;
    }

    if( getConf( )['global']['send_emails' ] == false )
    {
        echo "<br>Sending emails has been disabled in this installation";
        return;
    }

    $timestamp = date( 'r', strtotime( 'now' ) );

    $msg .= "
        <p>==========================================================</p>
        <p>
        This is a computer generated email, you need not reply. In case of 
        any query, please write to hippo@lists.ncbs.res.in. 
        </p>
        <p>==========================================================</p>
        ";

    $textMail = html2Markdown( $msg );

    $msgfile = tempnam( '/tmp', 'hippo_msg' );
    file_put_contents( $msgfile, $textMail );

    $to =  implode( ' -t ', explode( ',', trim( $to ) ) );

    $cmd= __DIR__ . "/sendmail.py -t $to -s '$sub' -i '$msgfile' ";
    if( $attachment )
    {
        foreach( explode( ',', $attachment ) as $f )
            $cmd .= " -a '$f' ";
    }

    echo "<pre> $cmd </pre>";
    $out = exec( $cmd, $output, $ret );
    unlink( $msgfile );
    return true;
}


// $res = sendEmail( "testing"
//     , "Your request has been created"
//     , "dilawars@ncbs.res.in" 
//     );

?>
