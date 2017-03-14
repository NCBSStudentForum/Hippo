<?php

include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'USER' ) );

include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );

$sem = getCurrentSemester( );
$year = getCurrentYear( );

// User courses.
$myCourses = getMyCourses( $sem, $year, $user = $_SESSION[ 'user' ] );

$runningCourses = array( );
foreach( getSemesterCourses( $year, $sem ) as $rc )
    $runningCourses[ $rc[ 'course_id' ] ] = $rc;

echo '<h1>Course enrollment</h1>';

echo alertUser( "
    <strong>Policy for dropping a course </strong> 
    <br>  Upto 30 days from starting of course, you are free to drop a course. 
    After that, you need to write to your course instructor and academic office.
    " );

// Running course this semester.
$courseMap = array( );
$options = array( );
foreach( $runningCourses as $c )
{
    $cid = $c[ 'course_id' ];
    $options[] = $cid ;
    $courseMap[ $cid ] = $cid . ' - ' . getCourseName( $cid );
}

$courseSelect = arrayToSelectList( 'course_id', $options, $courseMap );

echo "<h2>Registration form</h2>";

$default = array( 'student_id' => $_SESSION[ 'user' ] 
                , 'semester' => $sem
                , 'year' => $year
                , 'registered_on' => dbDate( 'now' )
                , 'course_id' => $courseSelect
                );


echo '<form method="post" action="user_manages_courses_action.php">';
echo dbTableToHTMLTable( 'course_registration'
                        , $default
                        , 'course_id,type' 
                        , 'submit'
                        , 'status,registered_on,last_modified_on,grade,grade_is_given_on'
                      );
echo '</form>';

$tofilter = 'student_id';

echo '<div style="font-size:small">';

echo '<table border="1">';
echo '<tr>';

$action = 'drop';
$count = 0;

if( count( $myCourses ) > 0 )
    echo "<h2>You are registered for following courses </h2>";

foreach( $myCourses as $c )
{
    $count += 1;
    if( $count % 3 == 0 )
        echo '</tr><tr>';

    echo '<td>';
    echo '<form method="post" action="user_manages_courses_action.php">';


    $cid = $c[ 'course_id' ];
    $course = getTableEntry( 'courses_metadata', 'id', array( 'id' => $cid ) );

    // If more than 30 days have passed, do not allow registering for courses.
    if( strtotime( 'now' ) - strtotime( $runningCourses[ $cid][ 'start_date' ] ) 
            > 30 * 24 * 3600 )
    {
        echo $course['name'] . '<br>' . '<tt>' . 
            humanReadableDate( $runningCourses[ $cid ][ 'start_date' ] ) 
            . ' to ' . humanReadableDate( $runningCourses[$cid]['end_date'] )
            . '</tt>'
            ;

        $action = ''; 
    }

    // TODO: Don't show grades unless student has given feedback.
    if( strlen( $c[ 'grade' ] == 0 ) )
        $tofilter .= ',grade,grade_is_given_on';

    echo dbTableToHTMLTable( 'course_registration', $c, '', $action, $tofilter );
    echo '</form>';
    echo '</td>';
}
    
echo '</tr></table>';
echo '</div>';



echo goBackToPageLink( "user.php", "Go back" );

?>
