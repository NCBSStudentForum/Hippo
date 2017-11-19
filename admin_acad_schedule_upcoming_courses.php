<?php
include_once 'header.php';
include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'AWS_ADMIN' ) );

include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );
$nextSem = getNextSemester( );
$year = $nextSem[ 'year' ];
$sem = $nextSem[ 'semester' ];

$upcomingCourses = getSemesterCourses( $year, $sem );
$runningCourseMapForAutoCompl = [];
foreach( $upcomingCourses as $x )
{
    $cid = $x['id'] . ': ' . getCourseName( $x['course_id'] );
    $runningCourseMapForAutoCompl[ $cid ] = $x['id'];
}
?>

<script type="text/javascript" charset="utf-8">
// Autocomplete running course. Append course name for better searching.
$( function() {
    var courses = <?php echo json_encode( array_keys($runningCourseMapForAutoCompl) ); ?>;
    $( "#upcoming_course_schedule_id" ).autocomplete({ source : courses } );
    $( "#upcoming_course_schedule_id" ).attr( "placeholder", "Type course code/name" );
});
</script>

<?php

echo slotTable( );

echo "<h2>Scheduling for $sem, $year </h2>";
$editable = 'id,slot,venue,weight';

$slotMap = getSlotMap( );
$lhs = getVenuesByType( 'LECTURE HALL' );

$venueSelectList = venuesToHTMLSelect( $lhs );
$slotSelectList = arrayToSelectList( 'slot', array_keys( $slotMap ) );

$action = 'Add';
$default = array( 'slot' => $slotSelectList, 'venue' => $venueSelectList );

// Form: Add new scheduling entry.
$form = '<form action="" method="post" accept-charset="utf-8">';
$form .= dbTableToHTMLTable( 'upcoming_course_schedule', $default, $editable, $action );
$form .= '</form>';
echo $form;

/* POST */
if( __get__( $_POST, 'response', '' ) == 'Add' )
{
    $cid = __get__( $_POST, 'id', '' );
    if( strlen( trim($cid) ) > 0 )
    {
        $courseID = $runningCourseMapForAutoCompl[ $cid ];
        echo printInfo( "Adding preference for $courseID" );
        insertOrUpdateTable( 'upcoming_course_schedule'
             , 'id,slot,venue', 'id,slot,venue,weight,comment', $_POST
            );
        $_POST[ 'response' ] = '';
    }
}
else if( __get__( $_POST, 'response', '' ) == 'Delete' )
{
    if( __get__( $_POST, 'id', 0 ) > 0 )
    {
        echo printInfo( "Deleting the schedule" );
        deleteFromTable( 'upcoming_course_schedule', 'id', $_POST );
    }
    $_POST[ 'response' ] = '';
}

$entries = getTableEntries( 'upcoming_course_schedule' );

$tofilter = '';
if( count( $entries ) > 0 )
{
    echo '<h2>Current list of preferences</h2>';
    echo printInfo( "Total entries : " . count( $entries ) );
    $table = ' <table class="info"> ';
    $table .= arrayHeaderRow( $entries[0], 'info' );
    foreach( $entries as $entry )
    {
        $table .= '<form action="#" method="post" accept-charset="utf-8">';
        $table .= '<tr>' . arrayToRowHTML( $entry, 'info', $tofilter, false );
        $table .= '<td><button name="response" value="Delete">Delete</button></td>';
        $table .= '<input type="hidden" name="id" value="' . $entry['id'] . '">';
        $table .= '</tr>';
        $table .= '</form>';
    }
    $table .= '</table>';
    echo $table;
}


?>
