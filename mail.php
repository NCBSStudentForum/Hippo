<?php

include('html2text.php');

function sendEmailWithSub($msg, $sub, $to) 
{
    $msg .= "<p>
        This is a computer generated email, you need not reply. In case of 
        any query, please write to acadoffice@ncbs.res.in.  </p>

        <p>If you are not the intended recipeint of this email, please write 
        to acadoffice@ncbs.res.in . </p>
        ";

    $msg = html2text($msg);
    $muttfile = __DIR__ . "/muttrc";
    $cmd="echo '$msg' | mutt -F $muttfile -s '$sub' $to";
    echo( "Executing $cmd" );
    exec( $cmd, $out);
    return $out;
}

$res = sendEmailWithSub( "testing", "Your request has been created", "dilawars@ncbs.res.in" );

?>
