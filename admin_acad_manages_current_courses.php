<?php
include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'AWS_ADMIN' ) );

echo userHTML( );

$sem = getCurrentSemester( );
$year = getCurrentYear( );

$action = 'Add';

// Get the list of all courses. Admin will be asked to insert a course into 
// database.
$allCourses = getTableEntries( 'courses_metadata', 'name' );
$coursesId = array_map( function( $x ) { return $x['id']; }, $allCourses );
asort( $coursesId );

$coursesMap = array( );

$slots = getTableEntries( 'slots', 'groupid' );
$slotMap = array();
foreach( $slots as $s )
{
    if( intval($s[ 'groupid' ]) == 0 )
        continue;

    $slotGroupId = $s[ 'groupid' ];
    if( ! array_key_exists( $slotGroupId, $slotMap ) )
        $slotMap[ $slotGroupId ] = $slotGroupId .  ' (' . $s['day'] . ':' 
                                . humanReadableTime( $s[ 'start_time' ] )
                                . '-' . humanReadableTime( $s['end_time'] ) 
                                . ')';
    else
        $slotMap[ $slotGroupId ] .= ' (' . $s['day'] . ':' 
                                . humanReadableTime( $s[ 'start_time' ] )
                                . '-' . humanReadableTime( $s['end_time'] ) 
                                . ')';
}

$slotSelect = arrayToSelectList( 'slot', array_keys($slotMap), $slotMap );

foreach( $allCourses as $c )
    $coursesMap[ $c[ 'id' ] ] = $c[ 'name' ];

$courseIdsSelect = arrayToSelectList( 'course_id', $coursesId, $coursesMap );

$venues = getTableEntries( 'venues', '', "type='LECTURE HALL'" );
$venueSelect = venuesToHTMLSelect( $venues );

// Running course for this semester.
$nextSem = getNextSemester( );
$runningCourses = getSemesterCourses( $year, $sem );
$nextSemCourses = getSemesterCourses( $nextSem[ 'year' ], $nextSem[ 'semester' ] );

$runningCourses = array_merge( $runningCourses, $nextSemCourses );

$runningCourseIds = array_map( 
            function( $x ) { return $x[ 'id']; }, $runningCourses 
        );

?>

<script type="text/javascript" charset="utf-8">
// Autocomplete running course.
$( function() {
    var coursesDict = <?php echo json_encode( $coursesMap ) ?>;
    var courses = <?php echo json_encode( $runningCourseIds ); ?>;
    $( "#running_course" ).autocomplete( { source : courses }); 
    $( "#running_course" ).attr( "placeholder", "Type course code" );
});

</script>

<?php

// Array to hold runnig course.
$default = array( 
    'course_id' => $courseIdsSelect, 'venue' => $venueSelect 
    , 'semester' => $sem
);

if( $_POST && array_key_exists( 'running_course', $_POST ) )
{
    $runningCourse = getTableEntry( 'courses', 'id'
                            , array( 'id' =>  $_POST[ 'running_course' ] ) 
                        );
    if( $runningCourse )
        $default = array_merge( $default, $runningCourse );

    $action = 'Edit';
}


echo "<h1>Running courses</h1>";

echo printInfo( "Current semester is $sem, $year." );

echo '<table class="info">';
$tobefilterd = 'id,semester,year';

echo arrayHeaderRow( $runningCourses[0], 'info', $tobefilterd );
foreach( $runningCourses as $course )
{
    $cname = getCourseName( $course[ 'course_id'] );
    $course[ 'course_id' ] = '<strong>'. $course['course_id'] . '</strong><br> ' . $cname;

    if( isCourseActive( $course ) )
        $course[ 'course_id' ] = "<blink> $symbBell </blink>" . $course[ 'course_id' ];

    echo arrayToRowHTML( $course, 'aws', $tobefilterd );
}
echo '</table>';

echo "</br>";

echo '<form method="post" action="#">';
echo '<table class="">';
echo '<tr>
        <td>
            <input id="running_course" name="running_course" type="text" >
        </td>
        <td>
            <button name="response" value="search" style="float:left" >Select</button>
        </td>
    </tr>';

// If a course has been selected then add its id to a hidden field. This entry 
// in invalid when adding a new course to current semester courses.
if( $action != 'Add' )
    echo '<input type="hidden" name="id" value="' . $course['course_id'] . '">';

echo '</table>';

echo '</form>';



echo "<h2>Add/edit runnuing courses </h2>";

echo '<form method="post" action="admin_acad_manages_current_courses_action.php">';

// We will figure out the semester by start_date .
$default[ 'slot' ] = $slotSelect;
$default[ 'venue' ] = $venueSelect;
$default[ 'semester' ] = $sem;

if( __get__( $_POST, 'running_course', '') )
{
    $action = 'Update';
    $course = getTableEntry( 'courses', 'id', array( 'id' => $_POST[ 'running_course' ]) );
    $default[ 'semester' ] = $sem;

    // Select the already assigned venue.
    $venueSelect = venuesToHTMLSelect( $venues, false, 'venue', array( $course[ 'venue' ] ) );
    $default[ 'venue' ] = $venueSelect;

    // Select the already assigned slot.
    $slotSelect = arrayToSelectList( 'slot', array_keys($slotMap), $slotMap 
            , false, $course['slot']
        );
    $default[ 'slot' ] = $slotSelect;
}
else
    $action = 'Add';

echo dbTableToHTMLTable( 'courses'
    , $default
    , 'course_id,start_date,end_date,slot,venue,note,url,ignore_tiles', $action 
    );

/* If we are updating, we might also like to remove the entry. This button also 
 * appears. Admin can remove the course schedule.
 */
if( $action == 'Update' )
    echo '<button name="response" onclick="AreYouSure(this)" 
        title="Remove this course from running courses."
        >' . 
            $symbDelete . '</button>';

echo '</form>';


echo "<br/><br/>";
echo goBackToPageLink( 'admin_acad.php', 'Go back' );

?>
