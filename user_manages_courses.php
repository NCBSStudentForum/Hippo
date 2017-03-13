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

// Running course this semester.
$runningCourses = getSemesterCourses( $year, $sem );

$courseMap = array( );
$options = array( );
foreach( $runningCourses as $c )
{
    $cid = $c[ 'course_id' ];
    $options[] = $cid ;
    $courseMap[ $cid ] = $cid . ' - ' . getCourseName( $cid );
}

$courseSelect = arrayToSelectList( 'courses', $options, $courseMap );

echo "<h3>Register for courses </h3>";
$default = array( 'student_id' => $_SESSION[ 'user' ] 
                , 'semester' => $sem
                , 'year' => $year
                , 'registered_on' => dbDate( 'now' )
                , 'course_id' => $courseSelect
                );

echo '<form method="post" action="admin_acad_manages_action.php">';
echo dbTableToHTMLTable( 'course_registration'
                        , $default
                        , 'course_id,type' 
                        , 'submit', 'last_modified_on,grade,grade_is_given_on'
                      );
echo '</form>';

echo goBackToPageLink( "user.php", "Go back" );

?>
