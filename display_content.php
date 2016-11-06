<?php 

function printErrorSevere($msg) 
{
    $err = "<font size=\"4\" color=\"blue\">".$msg."</font><br>";
    return $err;
}

function sendEmailToAdmin($err_msg, $db_name) {

}

function printWarning($msg) 
{
    $warn ="<p class=\"warn\">".$msg."</p>";
    return $warn;
}


function printInfo( $msg )
{
    $info ="<p class=\"info\"><font size=\"4\" >".$msg."<br></font></p>";
    return $info;
}

function alterUser( $msg )
{
   $info ="<p class=\"alert_user\">".$msg."</p>";
   return $info;

}


?>
