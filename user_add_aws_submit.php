<?php

include_once( "database.php" );

$supervisors = $_POST[ 'supervisor' ];
$tcms = $_POST[ 'tcm_member' ];

$columns = Array( 'speaker', 'title', 'abstract', 'date', 'time' );
$data = $_POST;
$data['speaker'] = $_SESSION['user'];

$i = 0;
foreach( $supervisors as $supervisor )
{
    $i += 1;
    $newCol = "supervisor_$i";
    array_push( $columns, $newCol );
    $data[ $newCol ] = $supervisor;
}

$i = 0;
foreach( $tcms as $tcm )
{
    $i += 1;
    $newCol = "tcm_member_$i";
    array_push( $columns, $newCol );
    $data[ $newCol ] = $tcm;
}

$res = insertIntoTable( "annual_work_seminars", $columns, $data );

if( $res )
{
    echo printInfo( "Successfully updated your entry" );
    goToPage( "user.php", 0 );
    exit( 0 );
}
else 
    echo printWarning( "Could not update your entry" );

echo goBackToPageLink( "user.php", "Go back to USER page" );

?>
