<?php 

include_once( "is_valid_access.php" );
include_once( "error.php" );
include_once( "methods.php" );

function insert_cage( $vars )
{
    $dbname = $_SESSION['db_unmutable'];
    $conn = sqlite_open( $dbname ) or die( "Could not open $dbname" );

    // Check if cage already exist.
    $res = $conn->query( 'SELECT * FROM cages WHERE id="' . $vars['cage_id'] . '"');
    if( $res->fetchArray( ) )
    {
        echo printWarning("Cage with id " . $vars['cage_id'] . " already exists. 
            Will do nothing." );
        return false;
    }

    $stmt = $conn->prepare( 'INSERT OR FAIL INTO cages 
        (id, type, location , barcode, comment ) 
        VALUES
        ( :id, :type, :location, :barcode, :comment ) '
    ); 
    $vars = array_map( 'trim', $vars );
    $stmt->bindValue( ':id', $vars['cage_id'], SQLITE3_TEXT );
    $stmt->bindValue( ':type', $vars['cage_type'], SQLITE3_TEXT );
    $stmt->bindValue( ':location', $vars['cage_location'], SQLITE3_TEXT );
    $stmt->bindValue( ':barcode', $vars['cage_barcode'], SQLITE3_BLOB );
    $stmt->bindValue( ':comment', $vars['cage_comment'], SQLITE3_TEXT );
    $result = $stmt->execute( );
    var_dump( $result );
    return $result;
}

$res = insert_cage( $_POST );
if( $res )
{
    echo "<p> Successfully inserted cage. </p>";
    goToPage( "insert_cage.php", 1 );
}
else
{
    echo printWarning( "Failed to insert cage" );
    echo $res;
    echo "<a href=\"insert_cage.php\">Go back</a>";
}

?>
