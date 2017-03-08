<?php

include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'AWS_ADMIN' ) );

include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );
?>

<script type="text/javascript" charset="utf-8">
// Autocomplete speaker.
$( function() {
    var slotsDict = <?php echo json_encode( $slotsMap ) ?>;
    var slots = <?php echo json_encode( $slotsId ); ?>;
    $( "#slot" ).autocomplete( { source : slots }); 
    $( "#slot" ).attr( "placeholder", "autocomplete" );
});

</script>

<?php

// Javascript.
$courses = getTableEntries( 'courses_metadata' );
$coursesMap = array( );
$coursesId = array_map( function( $x ) { return $x['id']; }, $courses );

foreach( $courses as $c )
    $coursesMap[ $c['id'] ] = $c['id'] . ' - ' . $c[ 'name' ];

$courseIdsSelect = arrayToSelectList( 'course_id', $coursesId, $coursesMap );


echo "<h2>Courses running these days</h2>";

$year = date( 'Y', strtotime( 'today' ) );
$sem = getCurrentSemester( );
$runningCourses = getSemesterCourses( $year, $sem );

echo printInfo( "Current semester is $sem, $year" );
foreach( $runningCourses as $course )
{
    $course[ 'name' ] = $coursesMap[ $course[ 'course_id' ] ];
    echo arrayToTableHTML( $course, 'course', '', '', 'id' );
}

echo "<h2>Assign a course to current sememster</h2>";


echo '<form method="post" action="admin_acad_manages_courses_action.php">';

// We will figure out the semester by start_date .
echo dbTableToHTMLTable( 'courses'
    , array( "course_id" => $courseIdsSelect )
    , 'course_id,start_date,end_date', 'Add' 
    , 'semester'
    );
echo '</form>';


echo "<br/><br/>";
echo goBackToPageLink( 'admin_acad.php', 'Go back' );

?>
