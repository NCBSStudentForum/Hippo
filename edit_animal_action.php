<?php

include_once( "is_valid_access.php" );
include_once( "sqlite.php" );
include_once( "error.php" );

$animalId = $_POST['animal_id'];

function update( $what, $value, $animal_id )
{
    $dbname = $_SESSION['db_unmutable'];
    $conn = sqlite_open( $dbname ) or die( "Failed to connect to $dbname" );
    $stmt = $conn->prepare( 
        "INSERT OR REPLACE INTO current_status ( id, $what ) VALUES ( :id, :val )" 
    );

    $stmt->bindValue( ':id', $animal_id, SQLITE3_TEXT );
    $stmt->bindValue( ':val', $value, SQLITE3_TEXT );
    $res = $stmt->execute( );

    $conn->close();
    if( $res ) 
        return true;
    return false;
}

function updateHealth( $animal_id, $array )
{
    $conn = connetJinawar( );
    $stmt = $conn->prepare( 
        'INSERT OR REPLACE INTO health (id, height, length, weight)
        VALUES (:id, :height, :length, :weight)' 
    );
    $stmt->bindValue( ':id', $animal_id, SQLITE3_TEXT );
    $stmt->bindValue( ':height', (int)$array['height'], SQLITE3_INTEGER );
    $stmt->bindValue( ':weight', (int)$array['weight'], SQLITE3_INTEGER );
    $stmt->bindValue( ':length', (int)$array['length'], SQLITE3_INTEGER );
    $res = $stmt->execute( );

    if( $array['died_on'] )
    {
        $stmt = $conn->prepare( 
            'UPDATE health SET 
                died_on=:died_on, deadly_reason=:deadly_readon
            WHERE id=:id
            ' );
        $stmt->bindValue( ':id', $animal_id, SQLITE3_TEXT );
        $stmt->bindValue( ':died_on', $array['died_on'], SQLITE3_TEXT );
        $stmt->bindValue( ':deadly_reason', $array['deadly_reason'], SQLITE3_TEXT );
        $res = $stmt->execute( );

        $stmt = $conn->prepare( 'UPDATE animals SET status=:status WHERE id=:id' );
        $stmt->bindValue( ':id', $animal_id, SQLITE3_TEXT );
        $stmt->bindValue( ':status', 0, SQLITE3_INTEGER );
        $res = $stmt->execute( );
    }

    $conn->close( );
    return $res;
}

switch( strtolower(trim($_POST['update'])) ) 
{

case "update cage":
    $cageId = $_POST['cage_id'];
    if( update( 'current_cage_id', $cageId, $animalId ) )
    {
        echo printInfo( "Successfully udpated." );
        goToPage( "user.php", 1 );
    }
    else
    {
        echo printWarning( "Could not update the cage" );
        echo goBackToPageLink( "user.php" );
    }
    break;

case "update health":
    $res = updateHealth( $_POST['animal_id'], $_POST );
    if( $res )
    {
        echo printInfo( "Helath updated successfuly" );
        goToPage( "user.php", 2 );
    }
    else
    {
        echo printWarning( "Could not update health" );
        goBackToPageLink( "user.php" );
    }
    break;

case "update strain":
    $strain = $_POST['animal_strain'];
    if( update( 'strain', $strain, $animalId ) )
    {
        echo printInfo( "Successfully updated" );
        goToPage( "user.php", 1 );
    }
    else
    {
        echo printWarning( "Could not update the cage" );
        echo goBackToPageLink( "user.php" );
    }
    break;

default:
    echo printWarning( "Unknown update operation ".  $_POST['update'] );
    echo implode( ' ', $_POST );
    echo goBackToPageLink( 'user.php' );
    break;
}

?>
