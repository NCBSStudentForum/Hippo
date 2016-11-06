<?php

include_once( "header.php" );
include_once( "database.php" );

// If $awsid > 0 that means we are here to edit, else we are here to create new 
// entry. TODO: Once editing is not allowed, make sure we assert this.
$awsId = intval( $_POST[ 'awsid' ] );


$columns = Array( 'speaker', 'title', 'abstract', 'date', 'time' 
    , 'supervisor_1', 'supervisor_2'
    , 'tcm_member_1', 'tcm_member_2', 'tcm_member_3', 'tcm_member_4' 
);

$data = $_POST;
$data['speaker'] = $_SESSION['user'];

if( $awsId > 0 )
{
    $data['id'] = $awsId;
    // Update table, 'id' is the primary key.
    $res = updateTable( 'annual_work_seminars', 'id', $columns, $data );
}
else
    $res = insertIntoTable( 'annual_work_seminars', $columns, $data );

if( $res )
{
    echo printInfo( "Successfully addd/updated your entry" );
    goToPage( "user.php", 0 );
    exit( 0 );
}
else 
    echo printWarning( "Could not update your entry" );

echo goBackToPageLink( "user.php", "Go back to USER page" );
exit;

?>
