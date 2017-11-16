<?php
// Let user download it.
$fileUrl= __DIR__ . '/data/' . $_GET['filename'];

header("Cache-Control: public" );
header("Content-Description: iCal File Transfer" );
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=" .  urlencode(basename($fileUrl)));   
header("Content-Type: application/octet-stream");
header("Content-Transfer-Encoding: Binary" );
header("Content-Type: application/force-download");
header("Content-Length: " . filesize($fileUrl));
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
readfile( $fileUrl );
?>
