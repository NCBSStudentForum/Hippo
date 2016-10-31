<?php

include_once( "methods.php" );
include_once('ldap.php');

class BMVPDO extends PDO 
{
    function __construct( $host = 'ghevar.ncbs.res.in'  )
    {
        $options = array ( PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION );
        try {
            parent::__construct( 'mysql:host=' . $host . ';dbname=bookmyvenue'
                , 'bookmyvenueuser', 'bookmyvenue', $options 
            );
        } catch( PODException $e) {
            echo printWarning( "failed to connect to database: ".  $e->getMessage());
            $this->error = $e->getMessage( );
        }
    }
}

// Construct the PDO
$db = new BMVPDO( "ghevar.ncbs.res.in" );


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
    * @brief Change the status of request.
    *
    * @param $requestId
    * @param $status
    *
    * @return true on success, false otherwise.
 */
function changeRequestStatus( $requestId, $status )
{
    global $db;
    $stmt = $db->prepare( "UPDATE requests SET status=:status WHERE id=:id" );
    $stmt->bindValue( ':status', $status );
    $stmt->bindValue( ':id', $requestId );
    return $stmt->execute( );
}

/**
    * @brief Get the list of events for today.
 */
function getEvents( $date = NULL )
{
    global $db;
    $stmt = $db->query( "SELECT * FROM events" );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

function getEventsOnThisDayAndThisVenue( $date, $venue )
{
    global $db;
    $stmt = $db->prepare( "SELECT * FROM events WHERE date=:date AND venue=:venue" );
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
    $repeatPat = $request[ 'repeat_pat' ];
    $query = $db->prepare( 
        "INSERT INTO requests ( 
            user, venue, title, description
            , date, start_time, end_time
            , does_repeat, repeat_pat
            , timestamp, status 
        ) VALUES ( 
            :user, :venue, :title, :description
            , :date , :start_time, :end_time
            , :does_repeat, :repeat_pat
            , 'date(now)', 'pending' 
        )");

    $query->bindValue( ':user', $_SESSION['user'] );
    $query->bindValue( ':venue' , $request['venue' ] );
    $query->bindValue( ':title', $request['title'] );
    $query->bindValue( ':description', $request['description'] );
    $query->bindValue( ':date', $request['date'] );
    $query->bindValue( ':start_time', $request['start_time'] );
    $query->bindValue( ':end_time', $request['end_time'] );
    $query->bindValue( ':repeat_pat', $request['repeat_pat'] );
    if( strlen( trim($request['repeat_pat']) > 0 ) )
        $query->bindValue( ':does_repeat', 'Yes' );
    else
        $query->bindValue( ':does_repeat', 'No' );
    //echo $query->debugDumpParams();
    return $query->execute();
}

/**
    * @brief Check if a venue is available or not for the given day and given 
    * time.
    *
    * @param $venue
    * @param $date
    * @param $startOn
    * @param $endOn
    *
    * @return 
 */
function isVenueAvailable( $venue, $date, $startOn, $endOn )
{
    $answer = true;
    $allEventsOnThisday = getEventsOnThisDayAndThisVenue( $date, $venue );
    return $answer;
}

function approveRequest( $eventId, $requestId )
{
    $request = getRequestById( $requestId );

    global $db;
    $stmt = $db->prepare( 'INSERT IGNORE INTO events (
        id, short_description, description, date, venue, start_time, end_time
    ) VALUES ( 
        :id, :short_description, :description, :date, :venue, :start_time, :end_time 
    )');
    $stmt->bindValue( ':id', $eventId );
    $stmt->bindValue( ':short_description', $request['title'] );
    $stmt->bindValue( ':description', $request['description'] );
    $stmt->bindValue( ':date', $request['date'] );
    $stmt->bindValue( ':venue', $request['venue'] );
    $stmt->bindValue( ':start_time', $request['start_time'] );
    $stmt->bindValue( ':end_time', $request['end_time'] );
    $res = $stmt->excute();
    if( $res )
    {
        // Change the status of request 
        changeRequestStatus( $requestId, 'approved' );
    }
    return $res;
}

function rejectRequest( $eventId, $requestId )
{
    return changeRequestStatus( $requestId, 'rejected' );
}


function actOnRequest( $eventId, $requestId, $status )
{
    echo "Changing status to $status ";
    if( $status == 'Approve' )
        approveRequest( $eventId, $requestId );
    elseif( $status == 'Reject' )
        rejectRequest( $eventId, $requestId );
    else
        echo( printWarning( "unknown request " . $requestId ) );
}

?>

