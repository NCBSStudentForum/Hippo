<?php

include_once 'header.php';
include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'AWS_ADMIN' ) );

include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );


$year = __get__( $_POST, 'year', getCurrentYear( ) );
$sem = __get__( $_POST, 'semester', getCurrentSemester( ) );

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

echo '<form action="#" method="post" accept-charset="utf-8">';

echo ' <input type="radio" name="semester" value="SPRING" ' . $springChecked . ' /> SPRING ';
echo ' <input type="radio" name="semester" value="AUTUMN" ' . $autumnChecked . ' /> AUTUMN ';
echo ' <input type="test" name="year" value="' . $year . '" />';
echo ' <button name="response" value="select_year_sem">Select Year/Semester</button>';
echo '</form>';

echo  noteWithFAIcon( "Selected semester $sem/$year", 'fa-bell-o' );

// Select semester and year here.

// Get the pervious value, else set them to empty.
$courseSelected = __get__( $_POST, 'course_id', '' );
$taskSelected = __get__( $_POST, 'task', '' );

$runningCourses = array();

foreach( getSemesterCourses( $year, $sem ) as $c )
    $runningCourses[ $c[ 'course_id' ] ] = $c;

$runningCoursesSelect = arrayToSelectList(
            'course_id'
            , array_keys( $runningCourses ), array( )
            , false, $courseSelected
        );

$taskSelect = arrayToSelectList( 'task'
                , array( 'Add enrollment', 'Change enrollment', 'Grade' )
                , array( ), false, $taskSelected
        );


// /**
//     * @brief A single button should be able to enable or disable the course
//     * registration.
//  */
// echo '<h1>Enable/disable enrollement</h1>';
//
// $schedule = getTableEntry( 'conditional_tasks', 'id', array( 'id' => 'COURSE_REGISTRATION' ) );
// if( strtotime( $schedule[ 'end_date' ] ) >= strtotime( 'today' ) )
// {
//     echo printInfo( "Course registration is OPEN." );
//     echo arrayToTableHTML( $schedule, 'info', '', 'id,status' );
// }
// else
//     echo printInfo( "Course registration is CLOSED." );
//
//
//
// echo '<h3>Update registration dates </h3>';
// echo '
//     <form method="post" action="#">
//         Opens on <input class="datepicker" name="start_date" />
//         Ends on <input class="datepicker" name="end_date" />
//         <button name="task" value="update_registration_date"> Update </button>
//     </form>
//     ';
//

echo '<h1>Manage grades/enrollement</h1>';
echo alertUser( "Selected semester $sem, $year" );

echo '<form method="post" action="">'; echo
"<table>
    <tr>
        <th>Select courses</th>
        <th>Task</th>
    </tr>
    <tr>
        <td>" . $runningCoursesSelect . "</td>
        <td>" . $taskSelect . "</td>
        <td><button type=\"submit\">Submit</button>
    </tr>
    </table>";

echo '</form>';

// Handle request here.

$taskSelected = __get__( $_POST, 'task', '' );
$_POST[ 'semester' ] = $sem;
$_POST[ 'year' ] = $year;

$whereExpr = '';
if( __get__( $_POST, 'course_id', '' ) )
    $whereExpr = whereExpr( 'semester,year,course_id', $_POST  );

// Even when students have dropped the course, show it here. Admin
// should be aware of it.
// $whereExpr .= " AND type != 'DROPPED'";
$enrollments = getTableEntries( 'course_registration' ,'student_id', $whereExpr);

//usort( $enrollments
//    , function( $x, $y ) { strcasecmp($x['student_id'], $y['student_id']); }
//);

if( $taskSelected == '' )
{
    echo printWarning( "No task is selected" );
}
else if( $_POST[ 'task' ] == 'Change enrollment' )
{
    echo "<h2>Changing enrollment</h2>";

    echo printInfo( "Press in button to change the status of ennrollment." );

    $types = getTableColumnTypes( 'course_registration', 'type' );

    $i = 0;
    echo '<table>';
    foreach( $enrollments as $enrol )
    {
        $i += 1;
        echo "<tr><td>$i</td><td>";
        echo '<form method="post"
            action="admin_acad_manages_enrollments_action.php">';

        echo arrayToTableHTML( $enrol, 'info', ''
            , 'last_modified_on,grade,grade_is_given_on,status' );

        $type = $enrol[ 'type' ];

        foreach( $types as $t )
        {
            if( $t == $type )
                continue ;

            echo '</td><td><button name="response" value="' . $t
                . '">'. $t . '</button>';
        }

        echo '</td></tr>';
        echo '<input type="hidden" name="year" value="' . $enrol[ 'year'] . '" >';
        echo '<input type="hidden" name="semester" value="' . $enrol['semester'] . '" >';
        echo '<input type="hidden" name="student_id" value="' . $enrol['student_id'] . '" >';
        echo '<input type="hidden" name="course_id" value="' . $enrol['course_id'] . '" >';
        echo '</form>';
    }
    echo '</table>';

}
else if( $_POST[ 'task' ] == 'Grade' )
{
    if( count( $enrollments ) > 0 )
    {
        if( ! $_POST[ 'course_id' ] )
            echo alertUser( "No course is selected" );
        else
        {
            echo alertUser( "Grading for course " . $_POST[ 'course_id' ] );
            echo '<form method="post"
                action="admin_acad_manages_enrollments_action.php">';

            echo '<table>';
            $ids = array( );
            $grades = array( );
            foreach( $enrollments as $enrol )
            {
                echo '<tr><td>';
                echo arrayToTableHTML( $enrol, 'enrollment', ''
                    , 'registered_on,last_modified_on,status,grade_is_given_on' );
                echo "</td>";
                echo "<td>" . gradeSelect( $enrol['student_id'], $enrol[ 'grade' ] ) . "</td>";
                echo '</tr>';
                $ids[ ] =  $enrol[ 'student_id' ];
            }

            echo '<input type="hidden" name="course_id" value="' . $enrol['course_id'] . '" >';
            echo '<input type="hidden" name="year" value="' . $enrol[ 'year'] . '" >';
            echo '<input type="hidden" name="semester" value="' . $enrol['semester'] . '" >';
            echo '<input type="hidden" name="student_ids" value="' . implode(',', $ids) . '" >';
            echo '<tr><td></td><td><button name="response" value="grade">Assign</button></td></tr>';
            echo '</table>';
            echo '</form>';
        }
    }
    else
    {
        echo printInfo( "No enrollment found for this course" );
    }
}
else if( $_POST[ 'task' ] == 'Add enrollment' )
{
    $course = $_POST[ 'course_id' ] ;
    $cname = getCourseName( $course );
    echo "<h2> Adding new enrollments to $cname </h2>";

    echo printInfo( "Add valid student emails. One email each line." );

    $form = '<form method="post" action="admin_acad_manages_enrollments_action.php">';
    $form .= '<table>';
    $form .= '<tr><td>';
    $form .= '<textarea cols="30" rows="4" name="logins" placeholder="gabbar@ncbs.res.in"></textarea>';
    $form .= '</td><td>';
    $form .= arrayToSelectList( 'type', array( 'CREDIT', 'AUDIT' )
                , array( 'CREDIT' ,'AUDIT' ), false, 'CREDIT'  );
    $form .= '</td><td>';
    $form .= '<button type="submit" name="response" value="enroll_new">Enroll</button>';
    $form .= '</td></tr>';
    $form .= '</table>';
    $form .= '<input type="hidden" name="course_id" value="' . $course .  '">';
    $form .= '</form>';
    echo $form;
}
else
    echo printInfo( "Unsupported task " . $_POST[ 'task' ] );


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

    $cname = getCourseName( $cid );
    echo "<h2>$cid: $cname </h2>";
    echo '<table class="info">';
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
