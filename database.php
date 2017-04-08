<?php

include_once 'database_init.php';
include_once "methods.php";
include_once 'ldap.php';

// Option values for event/request.
$dbChoices = array( 
    'bookmyvenue_requests.class' =>
        'UNKNOWN,TALK,INFORMAL TALK' .
        ',MEETING,LAB MEETING,THESIS COMMITTEE MEETING,JOURNAL CLUB MEETING' .
        ',SEMINAR,THESIS SEMINAR,ANNUAL WORK SEMINAR' .
        ',LECTURE,PUBLIC LECTURE,CLASS,TUTORIAL' .
        ',INTERVIEW,SPORT EVENT,CULTURAL EVENT,OTHER'
    , 'events.class' =>
        'UNKNOWN,TALK,INFORMAL TALK,LECTURE,PUBLIC LECTURE' .
        ',MEETING,LAB MEETING,THESIS COMMITTEE MEETING,JOURNAL CLUB MEETING' .
        ',SEMINAR,THESIS SEMINAR,ANNUAL WORK SEMINAR' .
        ',LECTURE,PUBLIC LECTURE,CLASS,TUTORIAL' .
        ',INTERVIEW,SPORT EVENT,CULTURAL EVENT,OTHER'
    , 'talks.class' =>
        'TALK,INFORMAL TALK,LECTURE,PUBLIC LECTURE' .
        ',SEMINAR,THESIS SEMINAR,ANNUAL WORK SEMINAR' .
        ',LECTURE,PUBLIC LECTURE,CLASS,TUTORIAL'
    );

/**
    * @brief Return a sorted array out of choices.
    *
    * @param $choices
    * @param $key
    * @param $default
    * @param $sorted
    *
    * @return 
 */
function getChoicesFromGlobalArray( $choices, $key, $default = 'UNKNOWN', $sorted = true )
{
    $choicesSplit = explode( ',', __get__( $choices, $key, '' ) );

    if( $sorted )
        sort( $choicesSplit );

    // Remove the default one and add the default at the front.
    $results = array_diff( $choicesSplit, array( $default ) );
    array_unshift( $results, $default );
    return array_unique( $results );
}


class BMVPDO extends PDO 
{
    function __construct( $host = 'localhost'  )
    {
        $conf = parse_ini_file( '/etc/hipporc', $process_section = TRUE );
        $options = array ( PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION );
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
            echo minionEmbarrassed( 
                "failed to connect to database: ".  $e->getMessage()
            );
            $this->error = $e->getMessage( );
            echo goBackToPageLink( 'index.php', 0 );
            exit;
        }

    }
}

// Construct the PDO
$db = new BMVPDO( "localhost" );

// And initiaze the database.
initialize( $db  );


function getEventsOfTalkId( $talkId )
{
    global $db;
    $entry = getTableEntry( 'events', 'external_id,status'
        , array( 'external_id' => "talks.$talkId" , 'status' => 'VALID' ) 
        );
    return $entry;
}

function getBookingRequestOfTalkId( $talkId )
{
    global $db;
    $entry = getTableEntry( 'bookmyvenue_requests', 'external_id,status'
        , array( 'external_id' => "talks.$talkId", 'status' => 'PENDING' ) 
        );
    return $entry;
}

/**
 * @brief It does the following tasks.
 *  1. Move the entruies from upcoming_aws to annual_work_seminars lists.
 *
 * @return 
 */
function doAWSHouseKeeping( )
{
    global $db;
    $oldAwsOnUpcomingTable = getTableEntries( 'upcoming_aws'
        , $orderby = 'date'
        , $where = "status='VALID' AND date < NOW( )" 
        );

    $badEntries = array( );
    foreach( $oldAwsOnUpcomingTable as $aws )
    {
        if( strlen( $aws[ 'title' ]) < 1 || strlen( $aws[ 'abstract' ] ) < 1)
        {
            array_push( $badEntries, $aws );
            continue;
        }

        // First copy the entry to AWS table.
        // id           | int(11)       | NO   | PRI | NULL     | auto_increment 
        // | speaker      | varchar(200)  | NO   | MUL | NULL     |                
        // | date         | date          | NO   |     | NULL     |                
        // | time         | time          | NO   |     | 16:00:00 |                
        // | supervisor_1 | varchar(200)  | NO   | MUL | NULL     |                
        // | supervisor_2 | varchar(200)  | YES  |     | NULL     |                
        // | tcm_member_1 | varchar(200)  | YES  |     | NULL     |                
        // | tcm_member_2 | varchar(200)  | YES  |     | NULL     |                
        // | tcm_member_3 | varchar(200)  | YES  |     | NULL     |                
        // | tcm_member_4 | varchar(200)  | YES  |     | NULL     |                
        // | title        | varchar(1000) | YES  |     | NULL     |                
        // | abstract     | text    
        $res1 = insertIntoTable( 'annual_work_seminars'
            , 'speaker,date,time,supervisor_1,supervisor_2' . 
                ',tcm_member_1,tcm_member_2,tcm_member_3,tcm_member_4' . 
                ',title,abstract', $aws 
            );

        if( $res1 )
        {
            $res2 = deleteFromTable( 'upcoming_aws', 'id', $aws );
            if( ! $res2 )
                array_push( $badEntries, $aws );
        }
        else
        {
            array_push( $badEntries, $aws );
            $html .=  printWarning( "Could not move entry to main AWS list" );
        }

    }
    return $badEntries;
}

function getVenues( $sortby = 'total_events DESC, id' )
{
    global $db;
    // Sort according to total_events hosted by venue
    $res = $db->query( "SELECT * FROM venues ORDER BY $sortby" );
    return fetchEntries( $res );
}


function getTableSchema( $tableName )
{
    global $db;
    $stmt = $db->prepare( "DESCRIBE $tableName" );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

function getVenuesGroupsByType(  )
{
    global $db;
    // Sort according to total_events hosted by venue
    $venues = getVenues( );
    $newVenues = Array( );
    foreach( $venues as $venue )
    {
        $vtype = $venue['type'];
        if( ! array_key_exists( $vtype, $newVenues ) )
            $newVenues[ $vtype ] = Array();
        array_push( $newVenues[$vtype], $venue );
    }
    return $newVenues;
}

// Return the row representing venue for given venue id.
function getVenueById( $venueid )
{
    global $db;
    $venueid = trim( $venueid );
    $stmt = $db->prepare( "SELECT * FROM venues WHERE id=:id" );
    $stmt->bindValue( ':id', $venueid );
    $stmt->execute( );
    return $stmt->fetch( PDO::FETCH_ASSOC );
}

function getPendingRequestsOnThisDay( $date )
{
    $requests = getTableEntries( 'bookmyvenue_requests', 'date,start_time'
            , "date='$date' AND status='PENDING'"
        );
    return $requests;
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
    $stmt = $db->prepare( 'SELECT * FROM bookmyvenue_requests 
        WHERE status=:status  GROUP BY gid ORDER BY date,start_time' );
    $stmt->bindValue( ':status', $status );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

// Get all events with given status.
function getEventsByGroupId( $gid, $status = NULL  )
{
    global $db;
    $query = "SELECT * FROM events WHERE gid=:gid";
    if( $status )
        $query .= " AND status=:status ";

    $stmt = $db->prepare( $query );
    $stmt->bindValue( ':gid', $gid );
    if( $status )
        $stmt->bindValue( ':status', $status );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

//  Get a event of given gid and eid. There is only one such event.
function getEventsById( $gid, $eid )
{
    global $db;
    $stmt = $db->prepare( 'SELECT * FROM events WHERE gid=:gid AND eid=:eid' );
    $stmt->bindValue( ':gid', $gid );
    $stmt->bindValue( ':eid', $eid );
    $stmt->execute( );
    return $stmt->fetch( PDO::FETCH_ASSOC );
}

/**
    * @brief Get list of requests made by this users. These requests must be 
    * newer than the current date minus 2 days and time else they won't show up.
    *
    * @param $userid
    * @param $status
    *
    * @return 
 */
function getRequestOfUser( $userid, $status = 'PENDING' )
{
    global $db;
    $stmt = $db->prepare( 
        'SELECT * FROM bookmyvenue_requests WHERE created_by=:created_by 
        AND status=:status AND date >= NOW() - INTERVAL 2 DAY
        GROUP BY gid ORDER BY date,start_time' );
    $stmt->bindValue( ':created_by', $userid );
    $stmt->bindValue( ':status', $status );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

function getEventsOfUser( $userid, $from = '-0 days', $status = 'VALID' )
{
    global $db;
    $from = date( 'Y-m-d', strtotime( $from ));
    $stmt = $db->prepare( 'SELECT * FROM events WHERE created_by=:created_by 
        AND date >= :from
        AND status=:status
        GROUP BY gid ORDER BY date,start_time' );
    $stmt->bindValue( ':created_by', $userid );
    $stmt->bindValue( ':status', $status );
    $stmt->bindValue( ':from', $from );
    $stmt->execute( );
    return fetchEntries( $stmt );

}

/**
    * @brief Get all approved events starting from given date and duration.
    *
    * @param $from
    * @param $duration
    *
    * @return 
 */
function getEventsBeteen( $from , $duration )
{
    global $db;
    $startDate = dbDate( $from );
    $endDate = dbDate( strtotime( $duration, strtotime( $from ) ) );

    $nowTime = dbTime( 'now' );

    $whereExpr = "date >= '$startDate' AND date <= '$endDate'";
    $whereExpr .= " AND status='VALID' ";

    return getTableEntries( 'events', 'date,start_time', $whereExpr );
}


// Fetch entries from database response object
function fetchEntries( $res, $how = PDO::FETCH_ASSOC )
{
    $array = Array( );
    if( $res ) {
        while( $row = $res->fetch( $how ) )
            array_push( $array, $row );
    }
    return $array;
}

// Get the request when group id and request id is given.
function getRequestById( $gid, $rid )
{
    global $db;
    $stmt = $db->prepare( 'SELECT * FROM bookmyvenue_requests WHERE gid=:gid AND rid=:rid' );
    $stmt->bindValue( ':gid', $gid );
    $stmt->bindValue( ':rid', $rid );
    $stmt->execute( );
    return $stmt->fetch( PDO::FETCH_ASSOC );
}

// Return a list of requested with same group id.
function getRequestByGroupId( $gid )
{
    global $db;
    $stmt = $db->prepare( 'SELECT * FROM bookmyvenue_requests WHERE gid=:gid' );
    $stmt->bindValue( ':gid', $gid );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

// Return a list of requested with same group id and status
function getRequestByGroupIdAndStatus( $gid, $status )
{
    global $db;
    $stmt = $db->prepare( 'SELECT * FROM bookmyvenue_requests WHERE gid=:gid AND status=:status' );
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
    $stmt = $db->prepare( "UPDATE bookmyvenue_requests SET 
        status=:status,last_modified_on=NOW() WHERE gid=:gid AND rid=:rid"
    );
    $stmt->bindValue( ':status', $status );
    $stmt->bindValue( ':gid', $gid );
    $stmt->bindValue( ':rid', $rid );
    return $stmt->execute( );
}

/**
    * @brief Change status of all request identified by group id.
    *
    * @param $gid
    * @param $status
    *
    * @return 
 */
function changeStatusOfRequests( $gid, $status )
{
    global $db;
    $stmt = $db->prepare( "UPDATE bookmyvenue_requests SET status=:status WHERE gid=:gid" );
    $stmt->bindValue( ':status', $status );
    $stmt->bindValue( ':gid', $gid );
    return $stmt->execute( );
}

function changeStatusOfEventGroup( $gid, $user, $status )
{
    global $db;
    $stmt = $db->prepare( "UPDATE events SET status=:status WHERE 
        gid=:gid AND created_by=:created_by" );
    $stmt->bindValue( ':status', $status );
    $stmt->bindValue( ':gid', $gid );
    $stmt->bindValue( ':created_by', $user );
    return $stmt->execute( );
}

/**
    * @brief Get the list of upcoming events.
 */
function getEvents( $from = 'today', $status = 'VALID' )
{
    global $db;
    $from = dbDate( $from );
    $stmt = $db->prepare( "SELECT * FROM events WHERE date >= :date AND 
        status=:status ORDER BY date,start_time " );
    $stmt->bindValue( ':date', $from );
    $stmt->bindValue( ':status', $status );
    $stmt->execute( );
    return fetchEntries( $stmt );
}


/**
  * @brief Get the list of upcoming events grouped by gid.
 */
function getEventsGrouped( $sortby = '', $from = 'today', $status = 'VALID' )
{
    global $db;
    $sortExpr = '';

    $sortby = explode( ',', $sortby );
    if( count($sortby) > 0 )
        $sortExpr = 'ORDER BY ' . implode( ', ', $sortby);

    $nowTime = dbTime( $from );
    $stmt = $db->prepare( 
        "SELECT * FROM events WHERE date >= :date 
            AND status=:status GROUP BY gid $sortExpr" 
        );
    $stmt->bindValue( ':date', $nowTime );
    $stmt->bindValue( ':status', $status );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

/**
    * @brief Get the list of upcoming events.
 */
function getPublicEvents( $from = 'today', $status = 'VALID', $ndays = 1 )
{
    global $db;
    $from = dbDate( $from );
    $end = dbDate( strtotime( $from . " +$ndays day" ) );
    $stmt = $db->prepare( "SELECT * FROM events WHERE date >= :date AND 
        date <= :end_date AND
        status=:status AND is_public_event='YES' ORDER BY date,start_time" );
    $stmt->bindValue( ':date', $from );
    $stmt->bindValue( ':end_date', $end );
    $stmt->bindValue( ':status', $status );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

/**
    * @brief Get list of public event on given day.
    *
    * @param $date
    * @param $status
    *
    * @return 
 */
function getPublicEventsOnThisDay( $date = 'today', $status = 'VALID' )
{
    global $db;
    $from = date( 'Y-m-d', strtotime( 'today' ));
    $stmt = $db->prepare( "SELECT * FROM events WHERE date = :date AND 
        status=:status AND is_public_event='YES' ORDER BY date,start_time" 
        );
    $stmt->bindValue( ':date', $date );
    $stmt->bindValue( ':status', $status );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

function getEventsOn( $day, $status = 'VALID')
{
    global $db;
    $stmt = $db->prepare( "SELECT * FROM events 
        WHERE status=:status AND date = :date ORDER BY date,start_time" );
    $stmt->bindValue( ':date', $day );
    $stmt->bindValue( ':status', $status );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

function getEventsOnThisVenueOnThisday( $venue, $date, $status = 'VALID' )
{
    global $db;
    $stmt = $db->prepare( "SELECT * FROM events 
        WHERE venue=:venue AND status=:status AND date=:date ORDER 
            BY date,start_time" );
    $stmt->bindValue( ':date', $date );
    $stmt->bindValue( ':status', $status );
    $stmt->bindValue( ':venue', $venue );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

/**
    * @brief get overlapping requests or events.
    *
    * @param $venue
    * @param 
    * @param $start_time
    * @param 
    * @param $status
    *
    * @return 
 */
function getEventsOnThisVenueBetweenTime( $venue, $date
    , $start_time, $end_time
   ,  $status = 'VALID' )
{
    global $db;
    $stmt = $db->prepare( 
        "SELECT * FROM events
        WHERE venue=:venue AND status=:status AND date=:date 
        AND ( (start_time < :start_time AND end_time > :start_time )
              OR ( start_time < :end_time AND end_time > :end_time )
              OR ( start_time >= :start_time AND end_time <= :end_time )
            )
        "
    );
    $stmt->bindValue( ':date', $date );
    $stmt->bindValue( ':start_time', $start_time );
    $stmt->bindValue( ':end_time', $end_time );
    $stmt->bindValue( ':status', $status );
    $stmt->bindValue( ':venue', $venue );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

function getRequestsOnThisVenueOnThisday( $venue, $date, $status = 'PENDING' )
{
    global $db;
    $stmt = $db->prepare( "SELECT * FROM bookmyvenue_requests 
        WHERE venue=:venue AND status=:status AND date=:date" );
    $stmt->bindValue( ':date', $date );
    $stmt->bindValue( ':status', $status );
    $stmt->bindValue( ':venue', $venue );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

function getRequestsOnThisVenueBetweenTime( $venue, $date
    , $start_time, $end_time
    , $status = 'PENDING' )
{
    global $db;
    $stmt = $db->prepare( 
        "SELECT * FROM bookmyvenue_requests 
        WHERE venue=:venue AND status=:status AND date=:date
        AND ( (start_time < :start_time AND end_time > :start_time )
              OR ( start_time < :end_time AND end_time > :end_time )
              OR ( start_time >= :start_time AND end_time <= :end_time )
            )
        " );
    $stmt->bindValue( ':date', $date );
    $stmt->bindValue( ':start_time', $start_time );
    $stmt->bindValue( ':end_time', $end_time );
    $stmt->bindValue( ':status', $status );
    $stmt->bindValue( ':venue', $venue );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

/**
    * @brief Get number of entries of a given column.
    *
    * @param $tablename
    * @param $column
    *
    * @return 
 */
function getNumberOfEntries( $tablename, $column = 'id' )
{
    global $db;
    $res = $db->query( "SELECT MAX($column) AS $column FROM $tablename" );
    return $res->fetch( PDO::FETCH_ASSOC );
}

function getUniqueFieldValue( $tablename, $column = 'id' )
{
    global $db;
    $res = $db->query( "SELECT MAX($column) AS $column FROM $tablename" );
    $res = $res->fetch( PDO::FETCH_ASSOC );
    return __get__( $res, $column , 0 );
}

/**
    * @brief Sunmit a request for review.
    *
    * @param $request
    *
    * @return  Group id of request.
 */
function submitRequest( $request )
{
    global $db;

    if( ! array_key_exists( 'user', $_SESSION ) )
    {
        echo printErrorSevere( "Error: I could not determine the name of user" );
        goToPage( "user.php", 3 );
        exit;
    }

    $request[ 'created_by' ] = $_SESSION[ 'user' ];
    $repeatPat = __get__( $request, 'repeat_pat', '' );

    if( strlen( $repeatPat ) > 0 )
        $days = repeatPatToDays( $repeatPat, $request[ 'date' ] );
    else 
        $days = Array( $request['date'] );

    if( count( $days ) < 1 )
    {
        echo minionEmbarrassed( "I could not generate list of slots for you reuqest" );
        return false;
    }

    $rid = 0;
    $res = $db->query( 'SELECT MAX(gid) AS gid FROM bookmyvenue_requests' );
    $prevGid = $res->fetch( PDO::FETCH_ASSOC);
    $gid = intval( $prevGid['gid'] ) + 1;
    foreach( $days as $day ) 
    {
        $rid += 1;
        $request[ 'gid' ] = $gid;
        $request[ 'rid' ] = $rid;
        $request[ 'date' ] = $day;

        $collideWith = checkCollision( $request );
        $hide = 'rid,external_id,description,is_public_event,url,modified_by';
        if( $collideWith )
        {
            echo '<div style="font-size:x-small">';
            echo alertUser( 'Collision with following event/request' );
            foreach( $collideWith as $ev )
                echo arrayToTableHTML( $ev, 'events', $hide );
            echo '</div>';
            continue;
        }

        $request[ 'timestamp' ] = dbDateTime( 'now' );
        $res = insertIntoTable( 'bookmyvenue_requests'
            , 'gid,rid,external_id,created_by,venue,title,description' . 
                ',date,start_time,end_time,timestamp,is_public_event,class'
            , $request 
        );

        if( ! $res )
        {
            echo printWarning( "Could not submit request id $gid" );
            return 0;
        }

    }
    return $gid;
}


function increaseEventHostedByVenueByOne( $venueId )
{
    global $db;
    $stmt = $db->prepare( 'UPDATE venues SET total_events = total_events + 1 WHERE id=:id' );
    $stmt->bindValue( ':id', $venueId );
    $res = $stmt->execute( );
    return $res;
}

/**
    * @brief check for collision.
    *
    * @param $resques
    *
    * @return 
 */
function checkCollision( $request )
{

    // Make sure this request is not clashing with another event or request.
    $events = getEventsOnThisVenueBetweenTime( 
        $request[ 'venue' ] , $request[ 'date' ] 
        , $request[ 'start_time' ], $request[ 'end_time' ]
        );
    $reqs = getRequestsOnThisVenueBetweenTime( 
        $request[ 'venue' ] , $request[ 'date' ] 
        , $request[ 'start_time' ], $request[ 'end_time' ]
        );

    $all = array();
    if( $events )
        $all = array_merge( $all, $events );

    if( $reqs )
        $all = array_merge( $all, $reqs );

    if( count( $all ) > 0 )
        return $all;

    return false;
}

/**
    * @brief Create a new event in dateabase. The group id and event id of event 
    * is same as group id (gid) and rid of request which created it. If there is 
    * alreay a event or request pending which collides with this request, REJECT 
    * it.
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

    $collideWith = checkCollision( $request );
    if( ! $collideWith )
    {
        echo alertUser( "Following request is colliding with another 
            event or request. Rejecting it.." );
        echo arrayToTableHTML( $collideWith, 'request' );
        rejectRequest( $gid, $rid );
        return false;
    }

    $stmt = $db->prepare( 'INSERT INTO events (
        gid, eid, class, external_id, title, description, date, venue, start_time, end_time
        , created_by, last_modified_on
    ) VALUES ( 
        :gid, :eid, :class, :external_id, :title, :description, :date, :venue, :start_time, :end_time 
        , :created_by, NOW()
    )');
    $stmt->bindValue( ':gid', $gid );
    $stmt->bindValue( ':eid', $rid );
    $stmt->bindValue( ':class', $request[ 'class' ] );
    $stmt->bindValue( ':external_id', $request[ 'external_id'] );
    $stmt->bindValue( ':title', $request['title'] );
    $stmt->bindValue( ':description', $request['description'] );
    $stmt->bindValue( ':date', $request['date'] );
    $stmt->bindValue( ':venue', $request['venue'] );
    $stmt->bindValue( ':start_time', $request['start_time'] );
    $stmt->bindValue( ':end_time', $request['end_time'] );
    $stmt->bindValue( ':created_by', $request['created_by'] );
    $res = $stmt->execute();
    if( $res )
    {
        changeRequestStatus( $gid, $rid, 'APPROVED' );
        // And update the count of number of events hosted by this venue.
        increaseEventHostedByVenueByOne( $request['venue'] );
        return true;
    }

    return false;
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

function changeIfEventIsPublic( $gid, $eid, $status )
{
    global $db;
    $stmt = $db->prepare( "UPDATE events SET is_public_event=:status
        WHERE gid=:gid AND eid=:eid" );
    $stmt->bindValue( ':gid', $gid );
    $stmt->bindValue( ':status', $status );
    $stmt->bindValue( ':eid', $eid );
    return $stmt->execute();
}

// Fetch all events at given venue and given day-time.
function eventsAtThisVenue( $venue, $date, $time )
{
    $venue = trim( $venue );
    $date = trim( $date );
    $time = trim( $time );

    global $db;
    // Database reads in ISO format.
    $hDate = dbDate( $date );
    $clockT = date('H:i', $time );

    // NOTE: When people say 5pm to 7pm they usually don't want to keep 7pm slot
    // booked.
    $stmt = $db->prepare( 'SELECT * FROM events WHERE 
        status=:status AND date=:date AND 
        venue=:venue AND start_time <= :time AND end_time > :time' );
    $stmt->bindValue( ':date', $hDate );
    $stmt->bindValue( ':time', $clockT );
    $stmt->bindValue( ':venue', $venue );
    $stmt->bindValue( ':status', 'VALID' );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

// Fetch all requests for given venue and given day-time.
function requestsForThisVenue( $venue, $date, $time )
{
    $venue = trim( $venue );
    $date = trim( $date );
    $time = trim( $time );

    global $db;
    // Database reads in ISO format.
    $hDate = dbDate( $date );
    $clockT = date('H:i', $time );
    //echo "Looking for request at $venue on $hDate at $clockT ";

    // NOTE: When people say 5pm to 7pm they usually don't want to keep 7pm slot
    // booked.
    $stmt = $db->prepare( 'SELECT * FROM bookmyvenue_requests WHERE 
        status=:status 
        AND date=:date AND venue=:venue
        AND start_time <= :time AND end_time > :time' 
    );
    $stmt->bindValue( ':status', 'PENDING' );
    $stmt->bindValue( ':date', $hDate );
    $stmt->bindValue( ':time', $clockT );
    $stmt->bindValue( ':venue', $venue );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

/**
    * @brief Get all public events at this time.
    *
    * @param $date
    * @param $time
    *
    * @return 
 */
function publicEvents( $date, $time )
{
    $date = trim( $date );
    $time = trim( $time );

    global $db;
    // Database reads in ISO format.
    $hDate = dbDate( $date );
    $clockT = date('H:i', $time );

    // NOTE: When people say 5pm to 7pm they usually don't want to keep 7pm slot
    // booked.
    $stmt = $db->prepare( 'SELECT * FROM events WHERE 
        date=:date AND start_time <= :time AND end_time > :time' );
    $stmt->bindValue( ':date', $hDate );
    $stmt->bindValue( ':time', $clockT );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

/**
    * @brief Update a group of requests. It can only modify fields which are set 
    * editable in function. 
    *
    * @param $gid
    * @param $options Any array as long as it contains fields with name in 
    * editables.
    *
    * @return  On success True, else False.
 */
function updateRequestGroup( $gid, $options )
{
    global $db;
    $editable = Array( "title", "description", "is_public_event" );
    $fields = Array( );
    $placeholder = Array( );
    foreach( $options as $key => $val )
    {
        if( in_array( $key, $editable ) )
        {
            array_push( $fields, $key );
            array_push( $placeholder, "$key=:$key" );
        }
    }

    $placeholder = implode( ",", $placeholder );
    $query = "UPDATE bookmyvenue_requests SET $placeholder WHERE gid=:gid";

    $stmt = $db->prepare( $query );

    foreach( $fields as $f ) 
        $stmt->bindValue( ":$f", $options[ $f ] );

    $stmt->bindValue( ':gid', $gid );
    return $stmt->execute( );
}

function updateEventGroup( $gid, $options )
{
    global $db;
    $events = getEventsByGroupId( $gid );
    $results = Array( );
    foreach( $events as $event )
    {
        $res = updateEvent( $gid, $event['eid'], $options );
        if( ! $res )
            echo printWarning( "I could not update sub-event $eid" );
        array_push( $results, $res );
    }
    return (! in_array( FALSE, $results ));

}

function updateEvent( $gid, $eid, $options )
{
    global $db;
    $editable = Array( "title", "description", "is_public_event"
        , "status", "class" );
    $fields = Array( );
    $placeholder = Array( );
    foreach( $options as $key => $val )
    {
        if( in_array( $key, $editable ) )
        {
            array_push( $fields, $key );
            array_push( $placeholder, "$key=:$key" );
        }
    }

    $placeholder = implode( ",", $placeholder );
    $query = "UPDATE events SET $placeholder WHERE gid=:gid AND eid=:eid";

    $stmt = $db->prepare( $query );

    foreach( $fields as $f ) 
        $stmt->bindValue( ":$f", $options[ $f ] );

    $stmt->bindValue( ':gid', $gid );
    $stmt->bindValue( ':eid', $eid );
    return $stmt->execute( );
}

// Create user if does not exists and fill information form LDAP server.
function createUserOrUpdateLogin( $userid, $ldapInfo = Array(), $type = null )
{
    global $db;


    if( $ldapInfo[ 'last_name' ] == 'NA' )
        $ldapInfo[ 'last_name' ] = '';

    $stmt = $db->prepare( 
       "INSERT IGNORE INTO logins
        (id, login, first_name, last_name, email, created_on, institute, laboffice) 
            VALUES 
            (:id, :login, :fname, :lname, :email,  NOW(), :institute, :laboffice)
        "
        );

    $institute = NULL;
    if( count( $ldapInfo ) > 0 ) 
        $institute = 'NCBS Bangalore';

    $stmt->bindValue( ':login', $userid );
    $stmt->bindValue( ':id', __get__( $ldapInfo, "uid", NULL ));
    $stmt->bindValue( ':fname', __get__( $ldapInfo, "first_name", NULL ));
    $stmt->bindValue( ':lname', __get__( $ldapInfo, "last_name", NULL ));
    $stmt->bindValue( ':email', __get__( $ldapInfo, 'email', NULL ));
    $stmt->bindValue( ':laboffice', __get__( $ldapInfo, 'laboffice', NULL ));
    $stmt->bindValue( ':institute', $institute );
    $stmt->execute( );

    $stmt = $db->prepare( "UPDATE logins SET last_login=NOW() WHERE login=:login" );
    $stmt->bindValue( ':login', $userid );
    return $stmt->execute( );
}

/**
    * @brief Get all logins.
    *
    * @return 
 */
function getLogins( $status = ''  )
{
    global $db;
    $where = '';
    if( $status )
        $where = " WHERE status='$status' ";
    $query = "SELECT * FROM logins $where ORDER BY joined_on DESC";
    $stmt = $db->query( $query );
    $stmt->execute( );
    return  fetchEntries( $stmt );
}

function getLoginIds( )
{
    global $db;
    $stmt = $db->query( 'SELECT login FROM logins' );
    $stmt->execute( );
    $results =  fetchEntries( $stmt );
    $logins = Array();
    foreach( $results as $key => $val )
        array_push( $logins, $val['login'] );
    return $logins;
}

/**
    * @brief Get user info from database.
    *
    * @param $user Login id of user.
    *
    * @return Array.
 */
function getUserInfo( $user )
{
    global $db;
    $stmt = $db->prepare( "SELECT * FROM logins WHERE login=:login" );
    $stmt->bindValue( ":login", $user );
    $stmt->execute( );
    return $stmt->fetch( PDO::FETCH_ASSOC );
}

function getLoginInfo( $login_name )
{
    return getUserInfo( $login_name );
}

function getLoginEmail( $login )
{
    global $db;
    $stmt = $db->prepare( "SELECT email FROM logins WHERE login=:login" );
    $stmt->bindValue( ":login", $login );
    $stmt->execute( );
    $res = $stmt->fetch( PDO::FETCH_ASSOC );

    if( strlen( trim($res[ 'email' ]) < 1 ) )
    {
        $info = getUserInfoFromLdap( $login );
        if( $info && array_key_exists( 'email', $info) && $info['email'] )
        {
            // Update user in database.
            createUserOrUpdateLogin( $login, $info );
            $alternativeEmail = __get__( $info, 'alternative_email', '' );
            $res['email'] = __get__( $info, 'email', $alternativeEmail );
        }
    }
    return $res['email'];
}

function getRoles( $user )
{
    global $db;
    $stmt = $db->prepare( 'SELECT roles FROM logins WHERE login=:login' );
    $stmt->bindValue( ':login', $user );
    $stmt->execute( );
    $res = $stmt->fetch( PDO::FETCH_ASSOC );
    return explode( ",", $res['roles'] );
}

function getMyAws( $user )
{
    global $db;

    $query = "SELECT * FROM annual_work_seminars WHERE speaker=:speaker 
        ORDER BY date DESC "; 
    $stmt = $db->prepare( $query );
    $stmt->bindValue( ':speaker', $user );
    $stmt->execute( );
    return fetchEntries( $stmt );
}


function getMyAwsOn( $user, $date )
{
    global $db;

    $query = "SELECT * FROM annual_work_seminars 
        WHERE speaker=:speaker AND date=:date ORDER BY date DESC "; 
    $stmt = $db->prepare( $query );
    $stmt->bindValue( ':speaker', $user );
    $stmt->bindValue( ':date', $date );
    $stmt->execute( );
    return $stmt->fetch( PDO::FETCH_ASSOC );
}

function getAwsById( $id )
{
    global $db;

    $query = "SELECT * FROM annual_work_seminars WHERE id=:id";
    $stmt = $db->prepare( $query );
    $stmt->bindValue( ':id', $id );
    $stmt->execute( );
    return $stmt->fetch( PDO::FETCH_ASSOC );
}

/**
    * @brief Return only recent most AWS given by this speaker.
    *
    * @param $speaker
    *
    * @return 
 */
function getLastAwsOfSpeaker( $speaker )
{
    global $db;
    $query = "SELECT * FROM annual_work_seminars WHERE speaker=:speaker 
        ORDER BY date DESC LIMIT 1";
    $stmt = $db->prepare( $query );
    $stmt->bindValue( ':speaker', $speaker );
    $stmt->execute( );
    # Only return the last one.
    return $stmt->fetch( PDO::FETCH_ASSOC );

}

/**
    * @brief Return all AWS given by this speaker.
    *
    * @param $speaker
    *
    * @return 
 */
function getAwsOfSpeaker( $speaker )
{
    global $db;
    $query = "SELECT * FROM annual_work_seminars WHERE speaker=:speaker 
        ORDER BY date DESC" ;
    $stmt = $db->prepare( $query );
    $stmt->bindValue( ':speaker', $speaker );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

function getSupervisors( )
{
    global $db;
    // First get all faculty members 
    $faculty = getFaculty( $status = 'ACTIVE' );

    // And then all supervisors.
    $stmt = $db->query( 'SELECT * FROM supervisors ORDER BY first_name' );
    $stmt->execute( );
    $supervisors = fetchEntries( $stmt );
    foreach( $supervisors as $super )
        array_push( $faculty, $super );
    return $faculty;
}


/**
    * @brief Find entry in database with given entry.
    *
    * @param $email
    *
    * @return 
 */
function findAnyoneWithEmail( $email )
{
    $res = getTableEntry( 'faculty', 'email', array( 'email' => $email ) );
    if( ! $res )
        $res = getTableEntry( 'supervisors', 'email', array('email' => $email));
    if( ! $res )
        $res = getTableEntry( 'logins', 'email', array('email' => $email));
    return $res;
}


/**
    * @brief Generate a where expression.
    *
    * @param $keys
    * @param $data
    *
    * @return 
 */
function whereExpr( $keys, $data )
{
    $whereExpr = array( );
    $keys = explode( ',', $keys );

    foreach( $keys as $k )
        $whereExpr[] = "$k='" . $data[ $k] . "'";

    return implode( ' AND ', $whereExpr );

}

/**
    * @brief 
    *
    * @param $tablename
    * @param $orderby
    * @param $where
    *
    * @return 
 */
function getTableEntries( $tablename, $orderby = '', $where = '' )
{
    global $db;
    $query = "SELECT * FROM $tablename";


    if( is_string( $where) && strlen( $where ) > 0 )
        $query .= " WHERE $where ";

    if( $orderby )
        $query .= " ORDER BY $orderby";

    $stmt = $db->query( $query );
    return fetchEntries( $stmt );
}

function getTableEntry( $tablename, $whereKeys, $data )
{
    global $db;
    if( is_string( $whereKeys ) )
        $whereKeys = explode( ",", $whereKeys );

    $where = array( );
    foreach( $whereKeys as $key )
        array_push( $where,  "$key=:$key" );

    $where = implode( " AND ", $where );

    $query = "SELECT * FROM $tablename WHERE $where";

    $stmt = $db->prepare( $query );

    foreach( $whereKeys as $key )
        $stmt->bindValue( ":$key", $data[ $key ] );


    $stmt->execute( );
    return $stmt->fetch( PDO::FETCH_ASSOC );
}


/**
    * @brief Insert a new entry in table.
    *
    * @param $tablename
    * @param $keys, Keys to update/insert in table.
    * @param $data
    *
    * @return  The id of newly inserted entry on success. Null otherwise.
 */
function insertIntoTable( $tablename, $keys, $data )
{
    global $db;

    if( gettype( $keys ) == "string" )
        $keys = explode( ',', $keys );

    $values = Array( );
    $cols = Array( );
    foreach( $keys as $k )
    {
        // If values for this key in $data is null then don't use it here.
        if( array_key_exists( $k, $data) && strlen($data[$k]) > 0 )
        {
            array_push( $cols, "$k" );
            array_push( $values, ":$k" );
        }
    }

    $keysT = implode( ",", $cols );
    $values = implode( ",", $values );

    $query = "INSERT INTO $tablename ( $keysT ) VALUES ( $values )";
    $stmt = $db->prepare( $query );

    foreach( $cols as $k )
    {
        $value = $data[$k];
        if( is_array( $value ) )
            $value = implode( ',', $value );

        $stmt->bindValue( ":$k", $value );
    }

    try 
    {
        $res = $stmt->execute( );
    } 
    catch (Exception $e )
    {
        echo minionEmbarrassed(
            "I failed to update my database. Error was " . $e->getMessage( ) );
        return null;
    }


    if( $res )
    {
        // When created return the id of table else return null;
        $stmt = $db->query( "SELECT LAST_INSERT_ID() FROM $tablename" );
        $stmt->execute( );
        return $stmt->fetch( PDO::FETCH_ASSOC );
    }
    return null;
}

/**
    * @brief Insert an entry into table. On collision, update the table.
    *
    * @param $tablename
    * @param $keys
    * @param $updatekeys
    * @param $data
    *
    * @return The value of last updated row.
 */
function insertOrUpdateTable( $tablename, $keys, $updatekeys, $data )
{
    global $db;

    if( is_string( $keys ) )
        $keys = explode( ',', $keys );

    if( is_string( $updatekeys ) )
        $updatekeys = explode( ',', $updatekeys );

    $values = Array( );
    $cols = Array( );
    foreach( $keys as $k )
    {
        // If values for this key in $data is null then don't use it here.
        if( $data[$k] && strlen( $data[ $k ] ) > 0 )
        {
            array_push( $cols, "$k" );
            array_push( $values, ":$k" );
        }
    }

    $keysT = implode( ",", $cols );
    $values = implode( ",", $values );

    $updateExpr = '';
    if( count( $updatekeys ) > 0 )
    {
        $updateExpr .= ' ON DUPLICATE KEY UPDATE ';
        foreach( $updatekeys as $k )
            // Update only if the new value is not empty.
            if( strlen( $data[ $k ] ) > 0 )
            {
                $updateExpr .= "$k=:$k,";
                array_push( $cols, $k );
            }

        // Remove last ','
        $updateExpr = rtrim( $updateExpr, "," );
    }

    $query = "INSERT INTO $tablename ( $keysT ) VALUES ( $values ) $updateExpr";
    $stmt = $db->prepare( $query );
    foreach( $cols as $k )
    {
        $value = $data[$k];
        if( is_array( $value ) )
            $value = implode( ',', $value );
        $stmt->bindValue( ":$k", $value );
    }

    $res = null;
    try {
        $res = $stmt->execute( );
    } catch ( PDOException $e ) {
        //echo $stmt->debugDumpParams( );
        echo minionEmbarrassed( "Failed to execute <pre> " . $query . "</pre>"
            , $e->getMessage( )
        );
    }

    // This is MYSQL specific. Only try this if table has an AUTO_INCREMENT 
    // id field.
    if( array_key_exists( 'id', $data) && $res )
    {
        // When created return the id of table else return null;
        $stmt = $db->query( "SELECT LAST_INSERT_ID() FROM $tablename" );
        $stmt->execute( );
        $res = $stmt->fetch( PDO::FETCH_ASSOC );
        $lastInsertId = intval( __get__($res, 'LAST_INSERT_ID()', 0 ) );

        // Store the LAST_INSERT_ID if insertion happened else the id of update 
        // execution.
        if( $lastInsertId > 0 )
            $res['id'] = $lastInsertId;
        else
            $res['id' ] = $data[ 'id' ];
        return $res;
    }
    else

    return $res;
}

/**
    * @brief Delete an entry from table. 
    *
    * @param $tableName
    * @param $keys
    * @param $data
    *
    * @return Status of execute statement.
 */
function deleteFromTable( $tablename, $keys, $data )
{
    global $db;

    if( gettype( $keys ) == "string" )
        $keys = explode( ',', $keys );

    $values = Array( );
    $cols = Array( );
    foreach( $keys as $k )
        if( $data[$k] )
        {
            array_push( $cols, "$k" );
            array_push( $values, ":$k" );
        }

    $keysT = implode( ",", $cols );
    $values = implode( ",", $values );
    $query = "DELETE FROM $tablename WHERE ";

    $whereClause = array( );
    foreach( $cols as $k )
        array_push( $whereClause, "$k=:$k" );

    $query .= implode( " AND ", $whereClause );


    $stmt = $db->prepare( $query );
    foreach( $cols as $k )
    {
        $value = $data[$k];
        if( gettype( $value ) == 'array' )
            $value = implode( ',', $value );
        $stmt->bindValue( ":$k", $value );
    }
    $res = $stmt->execute( );
    return $res;
}



/**
    * @brief A generic function to update a table.
    *
    * @param $tablename Name of table.
    * @param $wherekeys WHERE $wherekey=wherekeyval,... etc.
    * @param $keys Keys to be updated.
    * @param $data An array having all data.
    *
    * @return 
 */
function updateTable( $tablename, $wherekeys, $keys, $data )
{
    global $db;
    $query = "UPDATE $tablename SET ";

    if( gettype( $wherekeys ) == "string" ) // Only one key
        $wherekeys = explode( ",", $wherekeys );
    if( gettype( $keys ) == "string" )
        $keys = explode(",",  $keys );

    $whereclause = array( );
    foreach( $wherekeys as $wkey )
        array_push( $whereclause, "$wkey=:$wkey" );

    $whereclause = implode( " AND ", $whereclause );

    $values = Array( );
    $cols = Array();
    foreach( $keys as $k )
    {
        // If values for this key in $data is null then don't use it here.
        if( ! $data[$k] )
            $data[ $k ] = null;

        array_push( $cols, $k );
        array_push( $values, "$k=:$k" );
    }
    $values = implode( ",", $values );
    $query .= " $values WHERE $whereclause";

    $stmt = $db->prepare( $query );
    foreach( $cols as $k )
    {
        $value = $data[$k];
        if( gettype( $value ) == 'array' )
            $value = implode( ',', $value );

        $stmt->bindValue( ":$k", $value );
    }

    foreach( $wherekeys as $wherekey )
        $stmt->bindValue( ":$wherekey", $data[$wherekey] );

    $res = $stmt->execute( );
    if( ! $res )
        echo "<pre>Failed to execute $query </pre>";
    return $res;
}


/**
    * @brief Get the AWS scheduled in future for this speaker. 
    *
    * @param $speaker The speaker.
    *
    * @return  Array.
 */
function  scheduledAWSInFuture( $speaker )
{
    global $db;
    $stmt = $db->prepare( 
        "SELECT * FROM upcoming_aws WHERE
        speaker=:speaker AND date > NOW() 
        " );
    $stmt->bindValue( ":speaker", $speaker );
    $stmt->execute( );
    return $stmt->fetch( PDO::FETCH_ASSOC );
}

/**
    * @brief Check if there is a temporary AWS schedule.
    *
    * @param $speaker
    *
    * @return 
 */
function temporaryAwsSchedule( $speaker )
{
    global $db;
    $stmt = $db->prepare( 
        "SELECT * FROM aws_temp_schedule WHERE
        speaker=:speaker AND date > NOW() 
        " );
    $stmt->bindValue( ":speaker", $speaker );
    $stmt->execute( );
    return $stmt->fetch( PDO::FETCH_ASSOC );
}

/**
    * @brief Fetch faculty from database. Order by last-name
    *
    * @param $status
    *
    * @return 
 */
function getFaculty( $status = '', $order_by = 'first_name' )
{
    global $db;
    $query = 'SELECT * FROM faculty';
    $whereExpr = " WHERE affiliation != 'OTHER' ";
    if( $status )
        $query .= " $whereExpr AND status=:status ";
    else
        $query .= " $whereExpr AND status != 'INACTIVE' ";

    if( $order_by )
        $query .= " ORDER BY  '$order_by' ";

    $stmt = $db->prepare( $query );
    if( $status )
        $stmt->bindValue( ':status', $status );

    $stmt->execute( );
    return fetchEntries( $stmt );
}

/**
    * @brief Get all pending requests for this user.
    *
    * @param $user Name of the user.
    * @param $status status of the request.
    *
    * @return 
 */
function getAwsRequestsByUser( $user, $status = 'PENDING' )
{
    global $db;
    $query = "SELECT * FROM aws_requests WHERE status=:status AND speaker=:speaker";
    $stmt = $db->prepare( $query );
    $stmt->bindValue( ':status', $status );
    $stmt->bindValue( ':speaker', $user );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

function getAwsRequestById( $id )
{
    global $db;
    $query = "SELECT * FROM aws_requests WHERE id=:id";
    $stmt = $db->prepare( $query );
    $stmt->bindValue( ':id', $id );
    $stmt->execute( );
    return $stmt->fetch( PDO::FETCH_ASSOC );
}

function getPendingAWSRequests( )
{
    global $db;
    $stmt = $db->query( "SELECT * FROM aws_requests WHERE status='PENDING'" );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

function getAllAWS( )
{
    global $db;
    $stmt = $db->query( "SELECT * FROM annual_work_seminars ORDER BY date DESC"  );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

/**
    * @brief Return AWS from last n years.
    *
    * @param $years
    *
    * @return  Array of events.
 */
function getAWSFromPast( $from  )
{
    global $db;
    $stmt = $db->query( "SELECT * FROM annual_work_seminars 
        WHERE date >= '$from' ORDER BY date DESC, speaker
    " );
    $stmt->execute( );
    return fetchEntries( $stmt );
}


/**
    * @brief Get AWS users.
    *
    * @return Array containing AWS speakers.
 */
function getAWSSpeakers( $sortby = False )
{
    global $db;
    $sortExpr = '';
    if( $sortby )
        $sortExpr = " ORDER BY '$sortby'";

    $stmt = $db->query( 
        "SELECT * FROM logins WHERE status='ACTIVE' AND eligible_for_aws='YES' $sortExpr " 
    );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

/**
    * @brief Return AWS entries schedules by my minion..
    *
    * @return 
 */
function getTentativeAWSSchedule( )
{
    global $db;
    $stmt = $db->query( "SELECT * FROM aws_temp_schedule ORDER BY date" );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

/**
    * @brief Get all upcoming AWSes. Closest to today first (Ascending date).
    * 
    * @return Array of upcming AWS.
 */
function getUpcomingAWS( $monday = null )
{
    global $db;
    if( ! $monday )
        $whereExpr = 'date > CURDATE( ) ';
    else
    {
        $monday = dbDate( $monday );
        $whereExpr = "date = '$monday'";
    }

    $stmt = $db->query( 
        "SELECT * FROM upcoming_aws WHERE $whereExpr ORDER BY date" 
        );
    $stmt->execute( );
    return fetchEntries( $stmt );
}

function getUpcomingAWSById( $id )
{
    global $db;
    $stmt = $db->query( "SELECT * FROM upcoming_aws WHERE id = $id " );
    $stmt->execute( );
    return  $stmt->fetch( PDO::FETCH_ASSOC );
}

/**
    * @brief Accept a auto generated schedule. We put the entry into table 
    * upcoming_aws and delete this entry from aws_temp_schedule tables. In case 
    * of any failure, leave everything untouched.
    *
    * @param $speaker
    * @param $date
    *
    * @return 
 */
function acceptScheduleOfAWS( $speaker, $date )
{
    global $db;
    $db->beginTransaction( );

    $stmt = $db->prepare( 
        'INSERT INTO upcoming_aws ( speaker, date ) VALUES ( :speaker, :date )' 
    );

    $stmt->bindValue( ':speaker', $speaker );
    $stmt->bindValue( ':date', $date );

    try {

        $res = $stmt->execute( );
        // delete this row from temp table.
        $stmt = $db->prepare( 'DELETE FROM aws_temp_schedule WHERE 
            speaker=:speaker AND date=:date
            ' );
        $stmt->bindValue( ':speaker', $speaker );
        $stmt->bindValue( ':date', $date );
        $res = $stmt->execute( );

        // If this happens, I must not commit the previous results into table.
        if( ! $res )
        {
            $db->rollBack( );
            return False;
        }
    } 
    catch (Exception $e) 
    {
        $db->rollBack( );
        echo minionEmbarrassed( 
            "Failed to insert $speaker, $date into database: " . $e->getMessage() 
        );
        return False;
    }

    $db->commit( );
    return True;
}

/**
    * @brief Query AWS database of given query.
    *
    * @param $query
    *
    * @return  List of AWS with matching query.
 */
function queryAWS( $query )
{
    if( strlen( $query ) == 0 )
        return array( );

    if( strlen( $query ) < 3 )
    {
        echo printWarning( "Query is too small" );
        return array( );
    }

    global $db;
    $stmt = $db->query( "SELECT * FROM annual_work_seminars 
        WHERE LOWER(abstract) LIKE LOWER('%$query%')" 
    ); 
    $stmt->execute( );
    return fetchEntries( $stmt );
}

/**
    * @brief Clear a given AWS from upcoming AWS list.
    *
    * @param $speaker
    * @param $date
    *
    * @return 
 */
function clearUpcomingAWS( $speaker, $date )
{
    global $db;
    $stmt = $db->prepare( 
        "DELETE FROM upcoming_aws WHERE speaker=:speaker AND date=:date" 
    );

    $stmt->bindValue( ':speaker', $speaker );
    $stmt->bindValue( ':date', $date );
    return $stmt->execute( );
}

/**
    * @brief Delete an entry from annual_work_seminars table.
    *
    * @param $speaker
    * @param $date
    *
    * @return True, on success. False otherwise.
 */
function deleteAWSEntry( $speaker, $date )
{
    global $db;
    $stmt = $db->prepare( 
        "DELETE FROM annual_work_seminars WHERE speaker=:speaker AND date=:date" 
    );
    $stmt->bindValue( ':speaker', $speaker );
    $stmt->bindValue( ':date', $date );
    return $stmt->execute( );
}

function getHolidays( $from = NULL )
{
    global $db;
    if( ! $from )
        $from = date( 'Y-m-d', strtotime( 'today' ) );
    $stmt = $db->query( "SELECT * FROM holidays WHERE date >= '$from' ORDER BY date" );
    return fetchEntries( $stmt );
}

/**
    * @brief Fetch all existing email templates.
    *
    * @return 
 */
function getEmailTemplates( )
{
    global $db;
    $stmt = $db->query( "SELECT * FROM email_templates" );
    return fetchEntries( $stmt );
}

function getEmailTemplateById( $id )
{
    global $db;
    $stmt = $db->query( "SELECT * FROM email_templates where id='$id'" );
    return $stmt->fetch( PDO::FETCH_ASSOC );
}

function getEmailsByStatus( $status = 'PENDING' )
{
    global $db;
    $stmt = $db->query( "SELECT * FROM emails where status = '$status'
        ORDER BY when_to_send DESC
        " );
    return fetchEntries( $stmt );
}

function getEmailById( $id )
{
    global $db;
    $stmt = $db->query( "SELECT * FROM emails where id = '$id'" );
    return $stmt->fetch( PDO::FETCH_ASSOC );
}


function getUpcomingEmails( $from = null )
{
    global $db;
    if( ! $from )
        $from = dbDateTime( strtotime( 'today' ) );

    $stmt = $db->query( "SELECT *k FROM emails where when_to_send>='$from'" );
    return fetchEntries( $stmt );
}

function getSpeakers( )
{
    global $db;
    $res = $db->query( 'SELECT * FROM speakers' );
    return fetchEntries( $res );
}

/**
    * @brief Add a new talk.
    *
    * @param $data
    *
    * @return 
 */
function addNewTalk( $data )
{
    global $db;
    // Get the max id
    $res = $db->query( 'SELECT MAX(id) AS id FROM talks' );
    $maxid = $res->fetch( PDO::FETCH_ASSOC);
    $id = intval( $maxid['id'] ) + 1;

    $data[ 'id' ] = $id;
    $res = insertIntoTable( 'talks'
        , 'id,host,class,coordinator,title,speaker,speaker_id,description,created_by,created_on'
        , $data ); 

    // Return the id of talk.
    if( $res )
        return array( "id" => $id );
    else
        return null;
}

/**
    * @brief Add or update the speaker and returns the id.
    *
    * @param $data
    *
    * @return 
 */
function addOrUpdateSpeaker( $data )
{
    if( __get__( $data, 'id', 0 ) > 0 )
    {
        $speaker = getTableEntry( 'speakers', 'id', $data );
        if( $speaker )
        {
            $res = updateTable(
                'speakers', 'id'
                , 'honorific,email,first_name,middle_name,last_name,department,institute,homepage'
                , $data 
            );
            return getTableEntry( 'speakers', 'id', $speaker) ;
        }
    }

    // If we are here, then speaker is not found. Construct a new id.
    $id = getUniqueFieldValue( 'speakers', 'id' );
    $uid = intval( $id ) + 1;
    $data[ 'id' ] = $uid;
    $res = insertIntoTable( 'speakers'
        , 'id,email,honorific,first_name,middle_name,last_name,'
            . 'department,institute,homepage'
        , $data 
        );

    return getTableEntry( 'speakers', 'id', $data );
}

function getCourseName( $cid )
{
    $c =  getTableEntry( 'courses_metadata', 'id', array( 'id' => $cid ) );
    return $c['name'];
}

function getSemesterCourses( $year, $sem )
{
    $sDate = dbDate( strtotime( "$year-01-01" ) );
    $eDate = dbDate( strtotime( "$year-07-31" ) );

    if( $sem == 'MONSOON' )
    {
        $sDate = dbDate( strtotime( "$year-07-01" ) );
        $eDate = dbDate( strtotime( "$year-12-31" ) );
    }

    global $db;
    $res = $db->query( "SELECT * FROM courses WHERE 
                    start_date >= '$sDate' AND end_date <= '$eDate' " );

    return fetchEntries( $res );
}

function getMyCourses( $sem, $year, $user  )
{
    $whereExpr = "semester='$sem' AND year='$year' AND student_id='$user'";
    return getTableEntries( 'course_registration', 'status', $whereExpr );
}

/**
    * @brief Get all active recurrent events from today.
    *
    * @param $day
    *
    * @return 
 */
function getActiveRecurrentEvents( $day )
{
    global $db;

    $from = dbDate( $day );

    // We get gid of events which are still valid.
    $res = $db->query( "SELECT gid FROM events WHERE 
                            date >= '$from' AND status='VALID'" );
    $gids = fetchEntries( $res );

    $upcomingRecurrentEvents = array( );
    foreach( $gids as $gid )
    {
        $gid = $gid[ 'gid' ];

        // Must order by date.
        $gEvents = getTableEntries( 'events', 'date', "gid='$gid'" );
        if( count( $gEvents ) > 1 )
            $upcomingRecurrentEvents[ $gid ] = $gEvents;
    }

    return $upcomingRecurrentEvents;
}

/**
    * @brief Get login from logins table when name is given.
    *
    * @param $name
    *
    * @return 
 */
function getLoginByName( $name )
{
    global $db;
    $name = explode( ' ', $name );
    $fname = $name[ 0 ];
    $lname = end( $name );
    $res = $db->query( "SELECT * FROM logins WHERE
        first_name='$fname' AND last_name='$lname'" );
    return $res->fetch( PDO::FETCH_ASSOC );
}

function getSpeakerByName( $name )
{
    global $db;
    $name = explode( ' ', $name );
    $fname = $name[ 0 ];
    $lname = end( $name );

    $mname = '';
    if( count( $name ) > 2 )
        $mname = $name[1];

    $res = $db->query( "SELECT * FROM speakers WHERE
        first_name='$fname' AND middle_name='$mname' AND last_name='$lname'" );
    return $res->fetch( PDO::FETCH_ASSOC );
}

?>

