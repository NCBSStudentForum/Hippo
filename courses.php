<?php

include_once 'header.php';
include_once 'database.php';
include_once 'tohtml.php';
include_once 'html2text.php';
include_once 'methods.php';
include_once './check_access_permissions.php';

if( ! (isIntranet() || isAuthenticated( ) ) )
{
    echo printWarning( "You must either log-in OR intranet to access this page" );
    exit;
}

?>

<script type="text/javascript" charset="utf-8">
function showCourseInfo( x )
{
    alert( "Course description:\n" +  x.value );
}
</script>

<?php

echo '<h2>Slots </h2>';
echo alertUser( "These are proposed slots. They are not official yet and subject
    to change" );
echo slotTable(  );


echo "<h2>Enrollement table for this semester courses</h2>";


$year = getCurrentYear( );
$sem = getCurrentSemester( );

$courses = getSemesterCourses( $year, $sem );


echo alertUser(
    "Click on the button to see the list of enrolled students" 
    );


$enrollments = array( );

echo '<table class="info">';

echo '<tr><th>Course</th><th>Credit</th><th>Slot, Venue</th><th>All Enrollments</th>';
foreach( $courses as $c )
{
    $cid = $c['course_id'];
    $course = getTableEntry( 'courses_metadata', 'id'
                    , array( 'id' => $c['course_id'] ) 
                );

    $whereExpr = "year='$year' AND semester='$sem' AND course_id='$cid'";
    $registrations = getTableEntries(
                        'course_registration', 'student_id', $whereExpr 
                    );

    $enrollments[ $cid ] = $registrations;

    $cinfo = html2Markdown( $course[ 'description' ] );

    echo '<tr>
        <td> <button onclick="showCourseInfo(this)" class="courseInfo" 
            value="' . $cinfo . '" >' . $cid . '</button> ' . $course[ 'name' ] . '</td>
        <form method="post" action="#">
        <input type="hidden" name="course_id" value="' . $cid . '">
        <td>' . $course[ 'credits' ] . '</td>
        <td> <strong>' . $c[ 'slot' ] . '</strong>, ' .  $c[ 'venue' ] . '</td>
        <td>' . count( $registrations ) . '</td><td>
            <button name="response" value="show_enrollment">Show list</button></td>
        </form>';
    echo '</tr>';
}
echo '</table><br/>';

echo closePage( );

if( $_POST )
{

    $cid = $_POST[ 'course_id'];
    $courseName = getCourseName( $cid );
    echo '<h3>Enrollment for course ' . $courseName .'</h3>';

    $table = '<table class="show_events">';
    $count = 0;
    foreach( $enrollments[$cid]  as $r )
    {
        $count += 1;
        $studentId = $r[ 'student_id' ];
        $login = loginToText( $studentId );
        $table .= '<tr>';
        $table .= '<td>' . $count . '</td><td>' . $login . '</td>';
        $table .= '<td>' . $r[ 'type' ] . "</td>";
        $table .= '</tr>';
    }

    $table .= '</table>';

    echo '<div style="font-size:small">';
    echo $table;
    echo '</div>';

    echo '<br>';
    echo closePage( );
}


?>
