<?php

include_once 'header.php';
include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'AWS_ADMIN' ) );

include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );


$year = __get__( $_GET, 'year', getCurrentYear( ) );
$sem = __get__( $_GET, 'semester', getCurrentSemester( ) );

$springChecked = ''; $autumnChecked = '';
if( $sem == 'SPRING' )
{
    $springChecked = 'checked';
    $autumnChecked = '';
}
else
{
    $autumnChecked = 'checked';
    $springChecked = '';
}

echo selectYearSemesterForm( $year, $sem );

echo  noteWithFAIcon( "Selected semester $sem/$year", 'fa-bell-o' );

// Select semester and year here.

// Get the pervious value, else set them to empty.
$courseSelected = __get__( $_POST, 'course_id', '' );
$taskSelected = 'Grade';

$runningCourses = array();

foreach( getSemesterCourses( $year, $sem ) as $c )
    $runningCourses[ $c[ 'course_id' ] ] = $c;

$runningCoursesSelect = arrayToSelectList(
            'course_id'
            , array_keys( $runningCourses ), array( )
            , false, $courseSelected
        );

echo ' <br /> ';
echo ' <h1>Manage Grades</h1> ';
echo '<form method="post" action="">';
echo "<table>
    <tr>
        <td>" . $runningCoursesSelect . "</td>
        <td><button type=\"submit\">Submit</button>
    </tr>
    </table>";
echo '</form>';

$_POST[ 'semester' ] = $sem;
$_POST[ 'year' ] = $year;
$whereExpr = '';
if( __get__( $_POST, 'course_id', '' ) )
    $whereExpr = whereExpr( 'semester,year,course_id', $_POST  );

$enrollments = getTableEntries( 'course_registration' ,'student_id', $whereExpr);

if( count( $enrollments ) > 0 )
{
    if( ! $_POST[ 'course_id' ] )
        echo alertUser( "No course is selected" );
    else
    {
        echo alertUser( "Grading for course " . $_POST[ 'course_id' ] );
        $form = '<form method="post" action="admin_acad_manages_enrollments_action.php">';

        $hide = 'registered_on,last_modified_on,status,grade_is_given_on';
        $table = '<table class="info sortable">';
        $ids = array( ); /* Collect all student ids.  */
        $grades = array( );
        $table .= arrayToTHRow( $enrollments[0], 'info', $hide );
        foreach( $enrollments as $enrol )
        {
            $table .= '<tr>';
            $table .= arrayToRowHTML( $enrol, 'info', $hide, true, false );
            $table .= "<td>" . gradeSelect( $enrol['student_id'], $enrol[ 'grade' ] ) . "</td>";
            $table .= "<td> <button response='Assign'>Assign</button> </td>";
            $table .= '</tr>';
            $ids[ ] =  $enrol[ 'student_id' ];
            $table .= '<input type="hidden" name="student_id" value="' . $enrol['student_id'] . '" >';
        }

        $table .= '<input type="hidden" name="course_id" value="' . $enrol['course_id'] . '" >';
        $table .= '<input type="hidden" name="year" value="' . $enrol[ 'year'] . '" >';
        $table .= '<input type="hidden" name="semester" value="' . $enrol['semester'] . '" >';
        $table .= '<input type="hidden" name="student_ids" value="' . implode(',', $ids) . '" >';
        $table .= '</table>';

        $form .= $table;
        $form .= '<button class="submit" name="response" value="grade">Assign All</button>';
        $form .= '</form>';

        echo "<div style='border:1px'> $form </div>";
    }
}

echo ' <br /> ';
echo goBackToPageLink( 'admin_acad.php', 'Go back' );

echo "<h1>All enrollments for $sem/$year</h1>";
$enrolls = getTableEntries( 'course_registration', 'course_id'
        , "status='VALID' AND year='$year' AND semester='$sem'"
    );
$courseMap = array( );
foreach( $enrolls as $e )
    $courseMap[$e['course_id']][] = $e;

foreach( $courseMap as $cid => $enrolls )
{
    if( ! $cid )
        continue;

    sortByKey( $enrolls, 'student_id' );

    $cname = getCourseName( $cid );
    echo "<h2>$cid: $cname </h2>";
    echo '<table class="tiles">';
    echo '<tr>';
    foreach( $enrolls as $i => $e )
    {
        $student = $e[ 'student_id'];
        $sname = arrayToName( getLoginInfo( $student ) );
        $grade = $e[ 'grade' ];
        $type = $e[ 'type'];
        echo "<td> $sname <br /> $type <br /> $grade </td>";
        if( ($i+1) % 5 == 0 )
            echo '</tr><tr>';

    }
    echo '</tr>';
    echo '</table>';
}

echo '<br />';
echo goBackToPageLink( 'admin_acad.php', 'Go back' );


?>
