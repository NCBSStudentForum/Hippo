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
function getPendingRequestsGroupedByGID( )
{
    return getRequestsGroupedByGID( 'PENDING' );
}

// Get all requests with given status.
function getRequestsGroupedByGID( $status  )
{
    global $db;
    $stmt = $db->prepare( 'SELECT * FROM requests WHERE status=:status GROUP BY gid' );
    $stmt->bindValue( ':status', $status );
    $stmt->execute( );
    return fetchEntries( $stmt );
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

// Get the request when group id and request id is given.
function getRequestById( $gid, $rid )
{
    global $db;
    $stmt = $db->prepare( 'SELECT * FROM requests WHERE gid=:gid AND rid=:rid' );
    $stmt->bindValue( ':gid', $gid );
    $stmt->bindValue( ':rid', $rid );
    $stmt->execute( );
    return $stmt->fetch( PDO::FETCH_ASSOC );
}

// Return a list of requested with same group id.
function getRequestByGroupId( $gid )
{
    global $db;
    $stmt = $db->prepare( 'SELECT * FROM requests WHERE gid=:gid' );
    $stmt->bindValue( ':gid', $gid );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

// Return a list of requested with same group id and status
function getRequestByGroupIdAndStatus( $gid, $status )
{
    global $db;
    $stmt = $db->prepare( 'SELECT * FROM requests WHERE gid=:gid AND status=:status' );
    $stmt->bindValue( ':gid', $gid );
    $stmt->bindValue( ':status', $status );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

/**
    * @brief Change the status of request.
    *
    * @param $requestId
    * @param $status
    *
    * @return true on success, false otherwise.
 */
function changeRequestStatus( $gid, $rid, $status )
{
    global $db;
    $stmt = $db->prepare( "UPDATE requests SET 
        status=:status WHERE gid=:gid AND rid=:rid"
    );
    $stmt->bindValue( ':status', $status );
    $stmt->bindValue( ':gid', $gid );
    $stmt->bindValue( ':rid', $rid );
    return $stmt->execute( );
}

/**
    * @brief Get the list of events for today.
 */
function getEvents( $from = NULL )
{
    if( ! $from )
        $from = '2010-01-01';

    global $db;
    $stmt = $db->prepare( "SELECT * FROM events WHERE date >= :date" );
    $stmt->bindValue( ':date', $from );
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
    $days = repeatPatToDays( $repeatPat );
    $rid = 0;
    $results = Array( );
    foreach( $days as $day ) 
    {
        $rid += 1;
        $res = $db->query( 'SELECT MAX(gid) AS gid FROM requests' );
        $gid = ceil( floatval($res->fetch( PDO::FETCH_ASSOC )['gid'] ) );
        $query = $db->prepare( 
            "INSERT INTO requests ( 
                gid, rid, user, venue
                , title, description
                , date, start_time, end_time
                , status 
            ) VALUES ( 
                :gid, :rid, :user, :venue
                , :title, :description
                , :date , :start_time, :end_time
                , 'PENDING' 
            )");

        $query->bindValue( ':gid', $gid );
        $query->bindValue( ':rid', $rid );
        $query->bindValue( ':user', $_SESSION['user'] );
        $query->bindValue( ':venue' , $request['venue' ] );
        $query->bindValue( ':title', $request['title'] );
        $query->bindValue( ':description', $request['description'] );
        $query->bindValue( ':date', $day );
        $query->bindValue( ':start_time', $request['start_time'] );
        $query->bindValue( ':end_time', $request['end_time'] );
        $res = $query->execute();
        array_push( $results, $res );
    }
    return in_array( false, $results );
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

/**
    * @brief Create a new event in dateabase. The group id and event id of event 
    * is same as group id (gid) and rid of request which created it.
    *
    * @param $gid
    * @param $rid
    *
    * @return 
 */
function approveRequest( $gid, $rid )
{
    $request = getRequestById( $gid, $rid );

    global $db;
    $stmt = $db->prepare( 'INSERT IGNORE INTO events (
        gid, eid, short_description, description, date, venue, start_time, end_time
    ) VALUES ( 
        :gid, :eid, :short_description, :description, :date, :venue, :start_time, :end_time 
    )');
    $stmt->bindValue( ':gid', $gid );
    $stmt->bindValue( ':eid', $rid );
    $stmt->bindValue( ':short_description', $request['title'] );
    $stmt->bindValue( ':description', $request['description'] );
    $stmt->bindValue( ':date', $request['date'] );
    $stmt->bindValue( ':venue', $request['venue'] );
    $stmt->bindValue( ':start_time', $request['start_time'] );
    $stmt->bindValue( ':end_time', $request['end_time'] );
    $res = $stmt->execute();
    if( $res )
        changeRequestStatus( $gid, $rid, 'APPROVED' );
    return $res;
}

function rejectRequest( $gid, $rid )
{
    return changeRequestStatus( $gid, $rid, 'REJECTED' );
}


function actOnRequest( $gid, $rid, $status )
{
    if( $status == 'APPROVE' )
        approveRequest( $gid, $rid );
    elseif( $status == 'REJECT' )
        rejectRequest( $gid, $rid );
    else
        echo( printWarning( "unknown request " . $gid . '.' . $rid . 
        " or status " . $status ) );
}

?>

