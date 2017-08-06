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
foreach( getSemesterCourses( $year, $sem ) as $rc )
    $runningCourses[ $rc[ 'course_id' ] ] = $rc;

// User courses and slots.
$myCourses = getMyCourses( $sem, $year, $user = $_SESSION[ 'user' ] );
$mySlots = array( );
foreach( $myCourses as $c )
    $mySlots[ ] = $runningCourses[ $c[ 'course_id' ] ]['slot'];
$mySlots = array_unique( $mySlots );

echo '<h1>Course enrollment</h1>';

echo printInfo( "
    <h3>Policy for dropping courses </h3> 
    Upto 30 days from starting of course, you are free to drop a course. 
    After that, you need to write to your course instructor and academic office.
    " );

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
    
    $options[] = $cid ;
    $courseMap[ $cid ] = getCourseName( $cid ) . 
        " (slot " . $c[ 'slot' ] . ")";
}

// Show user which slots have been blocked.
echo alertUser( 
    "You have registered for courses running on following slots: " 
    . implode( ", ", $mySlots )
    . ". <br> Any course running on any of these slots will not appear in your 
    registration form."
    );

$courseSelect = arrayToSelectList( 'course_id', $options, $courseMap );

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


/**
    * @name Show the registered courses.
    * @{ */
/**  @} */

$tofilter = 'student_id';
echo '<div style="font-size:small">';
echo '<table class="1">';
echo '<tr>';
$action = 'drop';
$count = 0;

if( count( $myCourses ) > 0 )
    echo "<h1>You are registered for following courses </h1>";

foreach( $myCourses as $c )
{
    $count += 1;

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

    // Put course name in this table.
    $c[ 'course_id' ] .=  ' : ' . $course[ 'name' ];

    echo dbTableToHTMLTable( 'course_registration', $c, '', $action, $tofilter );
    echo '</form>';
    echo '</td>';
}
    
echo '</tr></table>';
echo '</div>';

echo '<h1>Slots </h1>';
echo slotTable( );

echo goBackToPageLink( "user.php", "Go back" );

?>
