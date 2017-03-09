<?php

include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'AWS_ADMIN' ) );

include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );

$sem = getCurrentSemester( );
$year = getCurrentYear( );


// Get the list of all courses. Admin will be asked to insert a course into 
// database.
$allCourses = getTableEntries( 'courses_metadata' );
$coursesId = array_map( function( $x ) { return $x['id']; }, $allCourses );
$coursesMap = array( );

foreach( $allCourses as $c )
    $coursesMap[ $c[ 'id' ] ] = $c[ 'name' ];

$courseIdsSelect = arrayToSelectList( 'course_id', $coursesId, $coursesMap );

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
    $( "#course" ).autocomplete( { source : courses }); 
    $( "#course" ).attr( "placeholder", "autocomplete" );
    $( "input[id^=courses_metadata_instructor]" ).attr( "placeholder", "autocomplete" );
});

</script>

<?php

// Array to hold runnig course.
$default = array( 'course_id' => $courseIdsSelect );
if( $_POST && array_key_exists( 'course', $_POST ) )
{
    $default[ 'id' ] = $_POST[ 'id' ];
    $runningCourse = getCourseInstanceId( $default[ 'id' ] );
    $default = array_merge( $default, $runningCourse );
}


echo "<h2>Courses running these days</h2>";

echo printInfo( "Current semester is $sem, $year" );

echo '<div style="font-size:small">';
foreach( $runningCourses as $course )
    echo arrayToTableHTML( $course, 'info', '', '', 'id' );

echo '</div>';
echo "</br>";

echo '<form method="post" action="#">';
echo '<table class="">';
echo '<tr>
        <td>
            <input id="course" type="text" >
        </td>
        <td>
            <button name="response" value="search" style="float:left" >' . 
                $symbScan . '</button>
        </td>
    </tr>';
echo '<input type="hidden" name="id" value="' . $course['course_id'] . '">';
echo '</table>';

echo '</form>';



echo "<h2>Assign a course to current sememster or edit a running course </h2>";


echo '<form method="post" action="admin_acad_manages_current_courses_action.php">';

// We will figure out the semester by start_date .
echo dbTableToHTMLTable( 'courses'
    , $default
    , 'course_id,start_date,end_date', 'Add' 
    , 'semester'
    );
echo '</form>';


echo "<br/><br/>";
echo goBackToPageLink( 'admin_acad.php', 'Go back' );

?>
