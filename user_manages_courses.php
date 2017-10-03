<?php

include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'USER' ) );

include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );

$sem = getCurrentSemester( );
$year = getCurrentYear( );


$runningCourses = array( );
$semCourses = getSemesterCourses( $year, $sem );
foreach( $semCourses as $rc )
{
    $cid = $rc[ 'course_id' ];
    $rc[ 'name' ] = getCourseName( $cid );
    $rc[ 'slot_tiles' ] = getCourseSlotTiles( $rc );
    $runningCourses[ $cid ] = $rc;
}

// User courses and slots.
$myCourses = getMyCourses( $sem, $year, $user = $_SESSION[ 'user' ] );

$mySlots = array( );
foreach( $myCourses as $c )
{
    // Get the running courses.  In rare case, use may have enrolled in course 
    // which is not running anymore.
    $course = __get__( $runningCourses, $c['course_id'], null );
    if( $course )
        $mySlots[ ] = $runningCourses[ $c[ 'course_id' ] ]['slot'];
    else 
    {
        // This course is no longer running. Drop it.
        updateTable( 'course_registration', 'student_id,year,semester,course_id'
            , 'status'
            , array( 'student_id' => $_SESSION[ 'user' ], 'year' => $year
                , 'semster' => $sem, 'course_id' => $c[ 'course_id' ]
                , 'status' => 'INVALID' 
            )
        );
    }
}

$mySlots = array_unique( $mySlots );

echo '<h1>Course enrollment</h1>';

// Check if registration is open

// Running course this semester.
$courseMap = array( );
$options = array( );

$blockedCourses = array( );
foreach( $runningCourses as $c )
{
    // Ignore any course which is colliding with any registered course.
    $cid = $c[ 'course_id' ];
    $cname = getCourseName( $cid );
    $slot = $c[ 'slot' ];
    if( in_array( $slot, $mySlots ) )
    {
        $blockedCourses[ $cid ] = $cname;
        continue;
    }
    
    if( $cid )
    {
        $options[] = $cid ;
        $courseMap[ $cid ] = getCourseName( $cid ) . 
            " (slot " . getCourseSlotTiles( $c ) . ")";
    }
}

$courseSelect = arrayToSelectList( 'course_id', $options, $courseMap );

$regOpen = isRegistrationOpen( );
if( $regOpen )
{
    echo "<h2>Registration form</h2>";

    $default = array( 'student_id' => $_SESSION[ 'user' ] 
                    , 'semester' => $sem
                    , 'year' => $year
                    , 'course_id' => $courseSelect
                    );
    echo '<form method="post" action="user_manages_courses_action.php">';
    echo dbTableToHTMLTable( 'course_registration'
        , $default
        , 'course_id:required,type' 
        , 'submit'
        , 'status,registered_on,last_modified_on,grade,grade_is_given_on'
    );
    echo '</form>';
}
else
    echo printInfo( "Course registration is not open for $sem $year"  );



/**
    * @name Show the registered courses.
    * @{ */
/**  @} */

$tofilter = 'student_id';
echo '<div style="font-size:small">';
echo '<table class="1">';
echo '<tr>';
$action = 'drop';

if( count( $myCourses ) > 0 )
{
    echo "<h1>You are registered for following courses for $sem $year</h1>";

    // Show user which slots have been blocked.
    echo alertUser( 
        "You have registered for courses running on following slots: " 
        . implode( ", ", $mySlots )
        . ". <br> All courses running these slots will not appear in your 
        registration form."
        );
}

// Dropping policy
echo printInfo( "
    <h3>Policy for dropping courses </h3> 
    Upto 30 days from starting of course, you are free to drop a course. 
    After that, you need to write to your course instructor and academic office.
    " );


$count = 0;
foreach( $myCourses as $c )
{
    // Break at 3 courses.
    if( $count % 3 == 0 )
        echo '</tr><tr>';

    echo '<td>';
    echo '<form method="post" action="user_manages_courses_action.php">';

    $cid = $c[ 'course_id' ];
    $course = getTableEntry( 'courses_metadata', 'id', array( 'id' => $cid ) );

    // If more than 30 days have passed, do not allow dropping courses. 
    if( strtotime( 'now' ) - strtotime( $runningCourses[ $cid][ 'start_date' ] ) 
            > 30 * 24 * 3600 )
    {
        $action = ''; 
    }

    // TODO: Don't show grades unless student has given feedback.
    if( strlen( $c[ 'grade' ] == 0 ) )
        $tofilter .= ',grade,grade_is_given_on';

    echo dbTableToHTMLTable( 'course_registration', $c, '', $action, $tofilter );
    echo '</form>';
    echo '</td>';

    $count += 1;
}
    
echo '</tr></table>';
echo '</div>';

if( $runningCourses )
{
    echo '<h1> Running courses </h1>';

    echo '<div style="font-size:small">';
    echo '<table class="info">';
    $ignore = 'id,semester,year,comment,ignore_tiles,slot';
    $cs = array_values( $runningCourses );
    echo arrayHeaderRow( $cs[0], 'info', $ignore );
    foreach( $cs as $rc )
        echo arrayToRowHTML( $rc, 'info', $ignore );
    echo '</table>';
    echo '</div>';
}


echo '<h1>Slots </h1>';
echo slotTable( );

echo goBackToPageLink( "user.php", "Go back" );

?>
