<?php

include_once( "methods.php" );
include_once('ldap.php');

class BMVPDO extends PDO {

    function __construct( $filename = "db/bmv.sqlite" ) 
    {
        $filename = realpath( $filename );
        $options = array ( 
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION 
        );
        try {
            parent::__construct( 'sqlite:' . $filename, '', '', $options );
        } catch( PODException $e) {
            echo printWarning( "failed to connect to database: ".  $e->getMessage());
            $this->error = $e->getMessage( );
        }
    }
}

// Construct the PDO
$db = new BMVPDO( "db/bmv.sqlite" );


function getVenues( )
{
    global $db;
    $res = $db->query( "SELECT * FROM venues" );
    return fetchEntries( $db );
}

// Get all requests which are pending for review.
function getPendingRequests( )
{
    return getRequests( 'pending' );
}

// Get all requests with given status.
function getRequests( $status  )
{
    global $db;
    $res = $db->query( 'SELECT * FROM requests WHERE status="'. $status . '"' );
    return fetchEntries( $res );
}

// Fetch entries from sqlite responses
function fetchEntries( $res )
{
    $array = Array( );
    if( $res ) {
        while( $row = $res->fetch( PDO::FETCH_ASSOC ) )
            array_push( $array, $row );
    }
    return $array;
}

function getRequestById( $rid )
{
    global $db;
    $stmt = $db->prepare( 'SELECT * FROM requests WHERE id=:id' );
    $stmt->bindValue( ':id', $rid );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

?>

