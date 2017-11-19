<?php

include_once 'header.php';
include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'AWS_ADMIN' ) );

include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );

$nextSem = getNextSemester( );
$year = $nextSem[ 'year' ];
$sem = $nextSem[ 'semester' ];

echo "<h2>Scheduling for $sem, $year </h2>";

$upcomingCourses = getSemesterCourses( $year, $sem );

$table = '<table class="info">';
$table .= arrayHeaderRow( $upcomingCourses[0], 'info' );
foreach( $upcomingCourses as $course )
    $table .= arrayToRowHTML( $course, 'course' );
$table .= '</table>';
echo $table;





?>
