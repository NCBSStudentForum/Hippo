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

echo '<h2>Enrollement</h2>';
echo '<form method="post" action="">';
echo "<table>
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

if( $_POST )
{
    $courseSelected = $_POST[ 'course_id' ];
    $taskSelected = $_POST[ 'task'];
    echo '<div style="font-size:small">';

    $_POST[ 'semester' ] = $sem;
    $_POST[ 'year' ] = $year;

    if( $_POST[ 'task' ] == '' )
    {
        echo printWarning( "No task is selected" );
    }
    else if( $_POST[ 'task' ] == 'Change enrollment' )
    {
        echo printInfo( "Changing enrollment" );

        $enrollments = getTableEntries( 'course_registration'
            , 'student_id', whereExpr( 'semester,year,course_id', $_POST  )
            );

        echo '<table>';
        foreach( $enrollments as $enrol )
        {
            echo '<tr><td>';
            echo '<form method="post" 
                    action="admin_acad_manages_enrollments_action.php">';
            echo arrayToTableHTML( $enrol, 'enrollment', ''
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
        }
    }
    else
    {
        echo printInfo( "Unsupported task " . $_POST[ 'task' ] );
    }

    echo "</div>";
}

echo "<br/><br/>";
echo goBackToPageLink( 'admin_acad.php', 'Go back' );

?>
