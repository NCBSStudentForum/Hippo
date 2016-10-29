<?php

include_once( "methods.php" );
include_once('ldap.php');

class BMVPDO extends PDO 
{
    function __construct( $filename = "db/bmv.sqlite" ) 
    {
        $filename = realpath( $filename );
        $options = array ( PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION );
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
    return fetchEntries( $res );
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
    return $stmt->fetch( PDO::FETCH_ASSOC );
}

/**
    * @brief Get the list of events for today.
 */
function getEvents( $date = NULL )
{
    global $db;
    $stmt = $db->query( "SELECT * FROM events WHERE date > :date" );
    if( ! $date )
        $date = strtotime( 'today' );
    $stmt->bindValue( ':date', $date );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

/**
    * @brief Sunmit a request for review.
    *
    * @param $request
    *
    * @return 
 */
function submitRequest( $request )
{
    global $db;
    $repeatPat = $request[ 'repeatPat' ];
    $query = $db->prepare( 
        "INSERT INTO requests ( 
            requestBy, venue, title, description, date, startOn, endOn, repeatPat, timestamp, status 
        ) VALUES ( 
            :requestBy, :venue, :title, :description, :date, :startOn, :endOn, :repeatPat, 'date(now)', 'pending' 
        )");

    $query->bindValue( ':requestBy', $_SESSION['user'] );
    $query->bindValue( ':venue' , $request['venueId' ] );
    $query->bindValue( ':title', $request['title'] );
    $query->bindValue( ':description', $request['description'] );
    $query->bindValue( ':date', $request['date'] );
    $query->bindValue( ':startOn', $request['startOn'] );
    $query->bindValue( ':endOn', $request['endOn'] );
    $query->bindValue( ':repeatPat', $request['repeatPat'] );
    return $query->execute();
}

?>

