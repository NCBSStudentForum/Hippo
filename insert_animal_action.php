<?php

include_once( 'is_valid_access.php' );
include_once( 'methods.php' );
include_once( 'sqlite.php' );

function insert_animal( $vars )
{
    $dbname = $_SESSION['db_unmutable'];
    $conn = sqlite_open( $dbname ) or die( "Could not open $dbname" );

    // Check if animal already exist.
    $res = $conn->query( 'SELECT * FROM animals WHERE id="' . $vars['animal_id'] . '"');
    if( $res->fetchArray( ) )
    {
        echo printInfo("Aninal with id " . $vars['animal_id'] . " already exists. 
            Will do nothing." );
        return false;
    }

    $stmt = $conn->prepare( 'INSERT OR FAIL INTO animals 
        (id, name, dob, parent_cage_id, gender, comment ) 
        VALUES
        ( :id, :name, :dob, :parent_cage_id, :gender, :comment ) '
    ); 
    $vars = array_map( 'trim', $vars );
    $stmt->bindValue( ':id', $vars['animal_id'], SQLITE3_TEXT );
    $stmt->bindValue( ':name', $vars['animal_name'], SQLITE3_TEXT );
    $stmt->bindValue( ':dob', $vars['animal_dob'], SQLITE3_TEXT );
    $stmt->bindValue( ':parent_cage_id', $vars['cage_id'], SQLITE3_TEXT );
    $stmt->bindValue( ':gender', $vars['animal_gender'], SQLITE3_TEXT );
    $stmt->bindValue( ':comment', $vars['animal_comment'], SQLITE3_TEXT );

    //TODO: Image image insertion. Not to database but to filesystem.
    $result = $stmt->execute( );

    // create an entry in log and current_status.
    $stmt = $conn->prepare( 'INSERT OR IGNORE INTO current_status SET id=:id ' );
    $stmt->bindValue( ':id', $vars['animal_id'], SQLITE3_TEXT );
    $stmt->execute( );

    $stmt = $conn->prepare( 'INSERT OR IGNORE INTO genotype SET id=:id ' );
    $stmt->bindValue( ':id', $vars['animal_id'], SQLITE3_TEXT );
    $stmt->execute( );
    
    $conn->close( );
    return $result;
};

$res = insert_animal( $_POST );

if( ! $res )
{
    echo printWarning( "Failed to insert animal with id " . $_POST['animal_id'] );
    echo( "Animal details : " . implode(" ", $_POST) . PHP_EOL );
    echo goBackToPageLink( "user.php" );
}
else
{
    echo printInfo( 'Successfully inserted animal ' . $_POST["animal_id"] );
    goToPage( 'insert_animal.php', 2 );
}

?>
