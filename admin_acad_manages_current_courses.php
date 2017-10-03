<?php

include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'AWS_ADMIN' ) );

include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );

$sem = getCurrentSemester( );
$year = getCurrentYear( );

$action = 'Add';

// Get the list of all courses. Admin will be asked to insert a course into 
// database.
$allCourses = getTableEntries( 'courses_metadata', 'name' );
$courseMap = array( );
foreach( $allCourses as $cr )
    $courseMap[ $cr[ 'id' ] ] = $cr;

var_dump( $courseMap );

$coursesId = array_map( function( $x ) { return $x['id']; }, $allCourses );
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
$runningCourses = getSemesterCourses( $year, $sem );
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
    $( "#running_course" ).attr( "placeholder", "autocomplete" );
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


echo printInfo( "Current semester is $sem, $year." );

/**
    * @name Show courses for this semester.
    * @{ */
/**  @} */
if( count( $runningCourses ) > 0 )
{
    echo "<h1>Running courses</h1>";
    $table = '<div style="font-size:small">';
    $table .= '<table class="info">';
    $tobefilterd = 'id,semester,year';
    $table .= arrayHeaderRow( $runningCourses[0], 'info', $tobefilterd );

    foreach( $runningCourses as $course )
    {
        $course[ 'name' ] = $allCourses[ $course[ 'course_id' ] ]['name'];
        $table .= arrayToRowHTML( $course, 'aws', $tobefilterd );
    }
    $table .= '</table>';
    $table .= '</div>';
    echo $table;

    // Interface to edit the course schedule.
    echo "</br>";
    echo '<form method="post" action="#">';
    echo '<table class="">';
    echo '<tr>
        <td><input id="running_course" name="running_course" type="text"></td>
        <td>
            <button name="response" value="search" style="float:left" >Show</button>
        </td>
        </tr>';
}
else
    echo alertUser( 'No course is running this semester!' );

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
    , 'course_id,start_date,end_date,slot,venue,note,ignore_tiles', $action 
    );

/* If we are updating, we might also like to remove the entry. This button also 
 * appears. Admin can remove the course schedule.
 */
if( $action == 'Update' )
    echo '<button name="response" onclick="AreYouSure(this)">' . 
            $symbDelete . '</button>';

echo '</form>';


echo "<br/><br/>";
echo goBackToPageLink( 'admin_acad.php', 'Go back' );

?>
