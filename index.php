<!DOCTYPE html>

<?php 

include('header.php');
include('methods.php');
include('tables.php');

session_save_path("/tmp/");
$conf = array();
$inifile = "jinawarrc";
if(file_exists($inifile)) {
	$conf = parse_ini_file($inifile, $process_sections = true );
}

if(!$conf)
{
    $error = "Failed to read  configuartion file.";
    header($error." I can't do anything anymore. Please wake up the admin.");
    exit;
}

$_SESSION['conf'] = $conf;

// Has two tables mice, cages and users.
$_SESSION['db_unmutable'] = 'db/jinawar.sqlite';

// Has multable tables, log, users etc.
$_SESSION['db_mutable'] = 'db/record.sqlite';

/* counter */
$hit_count = (int)file_get_contents('count.txt');
$hit_count++;
file_put_contents('count.txt', $hit_count);

?>

<font size="3" color="black">
<?php
    echo summaryTable( );
    echo loginForm(); 
?>

</body></html>
