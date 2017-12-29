<?php

include_once 'header.php';
include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'AWS_ADMIN' ) );

include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );


$year = getCurrentYear( );
$sem = getCurrentSemester( );

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

if( ! $_POST )
    exit;

$courseSelected = $_POST[ 'course_id' ];
$taskSelected = $_POST[ 'task'];

echo '<div style="font-size:small">';
$_POST[ 'semester' ] = $sem;
$_POST[ 'year' ] = $year;

$whereExpr = whereExpr( 'semester,year,course_id', $_POST  );

// Even when students have dropped the course, show it here. Admin
// should be aware of it.
// $whereExpr .= " AND type != 'DROPPED'";
$enrollments = getTableEntries( 'course_registration' ,'student_id', $whereExpr);

//usort( $enrollments
//    , function( $x, $y ) { strcasecmp($x['student_id'], $y['student_id']); }
//);

if( $_POST[ 'task' ] == '' )
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
    if( ! $_POST[ 'course_id' ] )
        echo alertUser( "No course is selected" );
    else
    {
        echo printInfo( "Grading for course " . $_POST[ 'course_id' ] );
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


echo "</div>";

echo goBackToPageLink( 'admin_acad.php', 'Go back' );

?>
