<?php

include_once( "header.php" );
include_once( "methods.php" );
include_once( 'ldap.php' );
include_once( "error.php" );


class BMVPDO extends PDO 
{
    function __construct( $host = 'ghevar.ncbs.res.in'  )
    {
        $conf = $_SESSION['conf'];
        $options = array ( PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION );
        var_dump( $conf );
        $host = $conf['mysql']['host'];
        $port = $conf['mysql']['port'];
        if( $port == -1 )
            $port = 3306;

        $user = $conf['mysql']['user'];
        $password = $conf['mysql']['password'];
        $dbname = $conf['mysql']['database'];
        
        try {
            parent::__construct( 'mysql:host=' . $host . ";dbname=$dbname"
                , $user, $password, $options 
            );
        } catch( PDOException $e) {
            echo printWarning( "failed to connect to database: ".  $e->getMessage());
            $this->error = $e->getMessage( );
            echo goBackToPageLink( 'index.php', 0 );
            exit;
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

// Return the row representing venue for given venue id.
function getVenueById( $venueid )
{
    global $db;
    $stmt = $db->prepare( "SELECT * FROM venues WHERE id=:id" );
    $stmt->bindValue( ':id', $venueid );
    $stmt->execute( );
    return $stmt->fetch( PDO::FETCH_ASSOC );
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

function getEventsOn( $day )
{
    global $db;
    $stmt = $db->prepare( "SELECT * FROM events WHERE date = :date" );
    $stmt->bindValue( ':date', $day );
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
    if( ! array_key_exists( 'user', $_SESSION ) )
    {
        echo printErrorSevere( "Error: I could not determine the name of user" );
        goToPage( "user.php", 5 );
    }

    if( ! array_key_exists( 'venue', $request ) )
    {
        echo printErrorSevere( "No venue found in your request" );
        goToPage( "user.php", 5 );
    }
    $repeatPat = $request[ 'repeat_pat' ];

    if( strlen( $repeatPat ) > 0 )
        $days = repeatPatToDays( $repeatPat );
    else 
        $days = Array( $request['date'] );

    $rid = 0;
    $results = Array( );
    $res = $db->query( 'SELECT MAX(gid) AS gid FROM requests' );
    $gid = intval($res->fetch( PDO::FETCH_ASSOC )['gid']) + 1;
    foreach( $days as $day ) 
    {
        $rid += 1;
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
        if( ! $res )
        {
            echo printWarning( "Could not submit request id $gid" );
        }
        array_push( $results, $res );
    }
    return (! in_array( FALSE, $results ));
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
        , user
    ) VALUES ( 
        :gid, :eid, :short_description, :description, :date, :venue, :start_time, :end_time 
        , :user
    )');
    $stmt->bindValue( ':gid', $gid );
    $stmt->bindValue( ':eid', $rid );
    $stmt->bindValue( ':short_description', $request['title'] );
    $stmt->bindValue( ':description', $request['description'] );
    $stmt->bindValue( ':date', $request['date'] );
    $stmt->bindValue( ':venue', $request['venue'] );
    $stmt->bindValue( ':start_time', $request['start_time'] );
    $stmt->bindValue( ':end_time', $request['end_time'] );
    $stmt->bindValue( ':user', $request['user'] );
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

// Fetch all events at given venue and given day-time.
function eventsAtThisVenue( $venue, $date, $time )
{
    global $db;
    // Database reads in ISO format.
    $hDate = dbDate( $date );
    $clockT = date('H:i', $time );

    // NOTE: When people say 5pm to 7pm they usually don't want to keep 7pm slot
    // booked.
    $stmt = $db->prepare( 'SELECT * FROM events WHERE 
        date=:date AND venue=:venue AND start_time <= :time AND end_time > :time' );
    $stmt->bindValue( ':date', $hDate );
    $stmt->bindValue( ':time', $clockT );
    $stmt->bindValue( ':venue', $venue );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

// Fetch all requests for given venue and given day-time.
function requestsForThisVenue( $venue, $date, $time )
{
    global $db;
    // Database reads in ISO format.
    $hDate = dbDate( $date );
    $clockT = date('H:i', $time );

    // NOTE: When people say 5pm to 7pm they usually don't want to keep 7pm slot
    // booked.
    $stmt = $db->prepare( 'SELECT * FROM requests WHERE 
        status=:status 
        AND date=:date AND venue=:venue 
        AND start_time <= :time AND end_time > :time' );
    $stmt->bindValue( ':status', 'pending' );
    $stmt->bindValue( ':date', $hDate );
    $stmt->bindValue( ':time', $clockT );
    $stmt->bindValue( ':venue', $venue );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

function summaryTable( )
{
    global $db;
    $summary = 'Summary';
    return $summary;
}

?>

