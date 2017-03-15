<?php

include_once 'header.php';
include_once 'database.php';
include_once 'tohtml.php';
include_once 'html2text.php';
include_once './check_access_permissions.php';

if( ! (isIntranet() || isAuthenticated( ) ) )
{
    echo printWarning( "You must either log-in OR intranet to access this page" );
    exit;
}


echo "<h2>Enrollement table for this semester courses</h2>";

$year = getCurrentYear( );
$sem = getCurrentSemester( );

$courses = getSemesterCourses( $year, $sem );

echo '<table border="1">';
echo '<tr><th>Course</td><th>All Enrollments</th>';

echo alertUser(
    "Click on the button to see the list of enrolled students" 
    );

echo slotTable( );

$enrollments = array( );
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

    echo '<tr>
        <td> ' . $cid . ' - ' . $course[ 'name' ] . '</td>
        <form method="post" action="#">
        <input type="hidden" name="course_id" value="' . $cid . '">
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
    echo '<h3>Enrollment for course ' . $cid . '</h3>';

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
