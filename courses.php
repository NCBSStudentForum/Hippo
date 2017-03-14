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
echo '<tr><th>Course</td><td>Total enrollments</td>';

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
        <td> <button name="response" value="show_enrollment">' 
                . count( $registrations ) . '</button></td>
        </form>';
    echo '</tr>';
}
echo '</table><br/>';

echo closePage( );

if( $_POST )
{
    echo '<table class="show_info">';
    $count = 0;
    $cid = $_POST[ 'course_id'];
    echo '<h3>Enrollment for course ' . $cid . '</h3>';
    foreach( $enrollments[$cid]  as $r )
    {
        $count += 1;
        $studentId = $r[ 'student_id' ];
        $login = loginToText( $studentId );
        echo '<tr><td>' . $count . '</td><td>' . $login . '</td></tr>';
    }
    echo '</table>';
    echo closePage( );
}


?>
