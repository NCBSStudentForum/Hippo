<?php 

include_once( "header.php" );
include_once( "methods.php" );
include_once( "database.php" );
include_once( "check_access_permissions.php" );
include_once( "tohtml.php" );

echo userHTML( );

mustHaveAnyOfTheseRoles( array( 'USER', 'BOOKMYVENUE_ADMIN' ) );

echo printInfo( '
    To make your event visible on NCBS Google calendar, Set option 
    <tt>IS PUBLIC EVENT</tt>  to <tt>YES</tt>.
    ' );

echo alertUser(
    "Make sure to select proper <tt>CLASS</tt> for your booking. Your 
    request will be rejected if it is filed under wront <tt>CLASS</tt>
    " );
    
$venues = getVenues( $sortby = 'total_events' );

if( ! array_key_exists( 'date', $_POST) )
{
    echo printWarning( "No valid day is selected. Going back to main page" );
    goToPage( "user.php", 1 );
    exit(0);
}

$date = $_POST['date'];

$day = nameOfTheDay( $date ); 
$events = getEvents( $date );
$dbDate = dbDate( $date );

// Generate options here.
$venue = __get__( $_POST, 'venue', '' );
$venue = trim( $venue );
if( $venue )
    $venueHTML = "<input name=\"venue\" type=\"text\" value=\"$venue\" readonly>";
else
    $venueHTML = venuesToHTMLSelect( $venues );

$startTime = dbTime( $_POST[ 'start_time' ] );

// This is END time of event. It may come from user from ./quickbook.php or use 
// default of 1 hrs in future.
$defaultEndTime = __get__( $_POST, 'end_time'
    , date( 'H:i', strtotime( $startTime ) + 60*60 )
    );

$date = __get__( $_POST, 'date', '' );
$title = __get__( $_POST, 'title', '' );
$description = __get__( $_POST, 'description', '' );

// If external_id is given then this needs to go into request table. This is 
// used to fetch event data from external table. The format of this field if 
// TABLENAME.ID. 'SELF.-1' means the there is not external dependency.
$external_id = 'SELF.-1';
if( array_key_exists( 'external_id', $_POST ) )
    $external_id = $_POST[ 'external_id' ];


$default = array( 'created_by' => $_SESSION[ 'user' ] );
$default[ 'end_time' ] = $defaultEndTime;

$default = array_merge( $default, $_POST );

echo '<form method="post" action="user_submit_request_action.php">';
echo dbTableToHTMLTable( 'bookmyvenue_requests'
        , $default
        , 'class,title,description,url,is_public_event,end_time' 
        , ''
        , $hide = 'gid,rid,external_id,modified_by,timestamp,status'
        );
echo '<input type="hidden" name="external_id" value="' . $external_id . '" >';
// I need to add repeat pattern here.
echo "<br>";
echo repeatPatternTable( 'repeat_pat' );
echo '<br>';
echo '<button class="submit" name="response" value="submit">' 
        . $symbSubmit . '</button>';
echo '</form>';

echo '<br><br>';
echo goBackToPageLink( "user.php", "Go back" );
