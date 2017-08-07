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
                , array( 'Change enrollment', 'Grade' ) 
                , array( ), false, $taskSelected
        );


/**
    * @brief A single button should be able to enable or disable the course
    * registration.
 */
echo '<h1>Enable/disable enrollement</h1>';

$schedule = getTableEntry( 'conditional_tasks', 'id', array( 'id' => 'COURSE_REGISTRATION' ) );
if( strtotime( $schedule[ 'end_date' ] ) >= strtotime( 'today' ) )
{
    echo printInfo( "Course registration is OPEN." );
    echo arrayToTableHTML( $schedule, 'info', '', 'id,status' );
}
else
    echo printInfo( "Course registration is CLOSED." );



echo '<h3>Update registration dates </h3>';
echo '
    <form method="post" action="#">
        Opens on <input class="datepicker" name="start_date" />
        Ends on <input class="datepicker" name="end_date" />
        <button name="task" value="update_registration_date"> Update </button>
    </form>
    ';


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

if( __get__( $_POST, 'task', '' ) == 'update_registration_date' )
{
    $_POST[ 'id' ] = 'COURSE_REGISTRATION';
    echo printInfo( "Updating course registration dates" );
    $res = updateTable( 'conditional_tasks', 'id', 'start_date,end_date', $_POST );
    if( $res )
    {
        // Refresh page.
        header( "Refresh:0" );
    }
}
else
{
    $courseSelected = $_POST[ 'course_id' ];
    $taskSelected = $_POST[ 'task'];

    echo '<div style="font-size:small">';
    $_POST[ 'semester' ] = $sem;
    $_POST[ 'year' ] = $year;

    $enrollments = getTableEntries( 'course_registration'
        , 'student_id', whereExpr( 'semester,year,course_id', $_POST  )
    );

    if( $_POST[ 'task' ] == '' )
    {
        echo printWarning( "No task is selected" );
    }
    else if( $_POST[ 'task' ] == 'Change enrollment' )
    {
        echo printInfo( "Changing enrollment" );

        echo '<table>';
        foreach( $enrollments as $enrol )
        {
            echo '<tr><td>';
            echo '<form method="post" 
                action="admin_acad_manages_enrollments_action.php">';
            echo arrayToTableHTML( $enrol, 'info', ''
                , 'last_modified_on,grade,grade_is_given_on' );
            echo '</td><td><button name="response" value="drop">Drop</button>';
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
    else
        echo printInfo( "Unsupported task " . $_POST[ 'task' ] );


    echo "</div>";
}

echo goBackToPageLink( 'admin_acad.php', 'Go back' );

?>
