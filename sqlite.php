<?php

include_once('ldap.php');

function sqlite_open($location)
{
  $db = new SQLite3($location);
  return $db;
}

function getEvents( $date )
{
    if( ! $date )
        return NULL;

    $dbname = $_SESSION['db'];
    $conn = sqlite_open( $dbname ) or die( "Could not open $dbname" );
    $stmt = $conn->prepare( 'SELECT * FROM events WHERE endOn >= :date');
    $stmt->bindValue( ':date', $date, SQLITE3_TEXT );
    $res = $stmt->execute( );
    $arr = $res->fetchArray( SQLITE3_ASSOC );
    $conn->close();
    return $arr;
}

function getInfoForDisplay()
{
    $info = NULL;
    return $info;
}

function getVenues( )
{
    $dbname = $_SESSION['db'];
    $conn = sqlite_open( $dbname ) or die( "Could not open $dbname" );
    $res = $conn->query( "SELECT * FROM venues" );
    $array = Array();
    while( $row = $res->fetchArray( SQLITE3_ASSOC ) )
        array_push( $array, $row );

    $conn->close();
    return $array;
}

// Get all requests which are pending for review.
function getPendingRequests( )
{
    return getRequests( 'pending' );
}

// Get all requests with given status.
function getRequests( $status  )
{
    $conn = connectDB( );
    $res = $conn->query( 'SELECT * FROM requests WHERE status="'. $status . '"' );
    $array = Array();
    while( $row = $res->fetchArray( SQLITE3_ASSOC ) )
        array_push( $array, $row );
    $conn->close();
    return $array;
}

function summaryTable( )
{
    $html = "<table class=\"summary\" style=\"float: left\">";
    $animals = getAnimalList( );
    $breederCages = getListOfCages( "breeder" );
    $cages = getListOfCages( );
    $numAnimals = sizeof( $breederCages );
    $numCages = sizeof( $cages );
    $numBreederCages = sizeof( $breederCages );
    $html .= "<tr> <td>Alive animals </td><td> $numAnimals </td> </tr>";
    $html .= "<tr> <td>Total cages </td><td> $numCages </td> </tr>";
    $html .= "<tr> <td>Breeder cages </td><td> $numBreederCages </td> </tr>";
    $html .= "</table>";
    return $html;
}

function getHealth( $animal_id )
{
    $conn = connectDB( );

    $stmt = $conn->prepare( 'SELECT * FROM health WHERE id=:id' );
    $stmt->bindValue( ':id', $animal_id, SQLITE3_TEXT );

    $res = $stmt->execute( );

    if( $res )
        $result = $res->fetchArray( );
    else
        $result = Array();
    $conn->close( );
    return $result;
}

function connectDB( )
{
    $dbname = $_SESSION['db'];
    $conn = sqlite_open( $dbname ) or die ("Could not connect to $dbname " );
    return $conn;
}

function getRequestById( $rid )
{
    $conn = connectDB( );

}

?>

