<?php

include_once( 'header.php' );
include_once( 'methods.php' );
include_once( 'tohtml.php' );

session_save_path("/tmp/");
$conf = array();
$inifile = "bmvrc";

if(file_exists($inifile)) 
    $conf = parse_ini_file($inifile, $process_section = TRUE );

if(!$conf)
{
    $error = "Failed to read  configuartion file.";
    header($error." I can't do anything anymore. Please wake up the admin.");
    exit;
}

$_SESSION['conf'] = $conf;
$_SESSION['user'] = 'unknown'; // This for testing purpose.

/* counter */
$hit_count = (int)file_get_contents('count.txt');
$hit_count++;
file_put_contents('count.txt', $hit_count);

// Now create a login form.
echo loginForm();

?>
