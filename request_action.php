<?php
include_once( "header.php" );
include_once( "methods.php" );
include_once( "sqlite.php" );

//var_dump( $_POST );

if( $_POST[ "response" ] == "Go back" )
{
    goToPage( "index.php", 0 );
    exit( 0 );
}

$repeatPat = $_POST[ 'repeatPat' ];
$conn = connectDB( );

$query = $conn->prepare( 
    "INSERT INTO requests ( 
        requestBy, venue, title, description, startOn, endOn, repeatPat, timestamp, status 
    ) VALUES ( 
        :requestBy, :venue, :title, :description, :startOn, :endOn, :repeatPat, 'date(now)', 'pending' 
    )");

$query->bindValue( ':requestBy', $_SESSION['user'] );
$query->bindValue( ':venue' , $_POST['venueId' ] );
$query->bindValue( ':title', $_POST['title'] );
$query->bindValue( ':description', $_POST['description'] );
$query->bindValue( ':startOn', $_POST['startOn'] );
$query->bindValue( ':endOn', $_POST['endOn'] );
$query->bindValue( ':repeatPat', $_POST['repeatPat'] );
$res = $query->execute();
$conn->close();

echo printInfo( "Your request has been submitted." );
goToPage( "index.php", 3 );

?>
