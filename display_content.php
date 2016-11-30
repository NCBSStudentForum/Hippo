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

function alertUser( $msg )
{
   $info ="<p class=\"alert_user\">".$msg."</p>";
   return $info;

}

function minionEmbarrassed( $msg, $info = '' )
{
    echo "<p class=\"embarassed\"> This is embarassing! <br>";
    echo " $msg <br> $info ";
    echo "I have logged this error! If possible please notify the admin. ";
    error_log( "failed to updated" );
    echo "</p>";
}



?>
