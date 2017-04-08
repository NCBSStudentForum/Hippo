<?php 

function printErrorSevere($msg) 
{
    $err = "<font size=\"4\" color=\"blue\">".$msg."</font><br>";
    error_log( $msg );
    return $err;
}


function printWarning($msg) 
{
    $warn ="<p class=\"warn\">".$msg."</p>";
    error_log( $msg );
    return $warn;
}


function printInfo( $msg )
{
    $info ="<p class=\"info\">".$msg."<br></p>";
    error_log( $msg );
    return $info;
}

function alertUser( $msg )
{
   $info ="<div class=\"alert_user\"><p>".$msg."</p></div>";
   return $info;
}

function minionEmbarrassed( $msg, $info = '' )
{
    echo "<p class=\"embarassed\"> This is embarassing! <br>";
    echo " $msg <br> $info ";
    echo "I have logged this error!. ";
    error_log( "FAILED : " . $msg );
    echo "</p>";
}



?>
