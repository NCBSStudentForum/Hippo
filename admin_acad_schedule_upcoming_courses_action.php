<?php

include_once 'methods.php';
include_once 'tohtml.php';
include_once 'check_access_permissions.php';

mustHaveAnyOfTheseRoles( array( "AWS_ADMIN" ) );

/* POST */
if( __get__( $_POST, 'response', '' ) == 'Add' )
{
    $cid = __get__( $_POST, 'course_id', 0 );
    if( strlen( trim($cid) ) > 0 )
    {
        echo printInfo( "Adding preference for $cid" );
        $_POST[ 'course_id' ] = getCourseCode( $_POST[ 'course_id' ] );
        insertOrUpdateTable( 'upcoming_course_schedule'
             , 'course_id,slot,venue', 'course_id,slot,venue,weight,comment', $_POST
            );
        goBack( "admin_acad_schedule_upcoming_courses.php" );
        exit;
    }
}
else if( __get__( $_POST, 'response', '' ) == 'Delete' )
{
    if( __get__( $_POST, 'id', 0 ) > 0 )
    {
        echo printInfo( "Deleting the schedule with id " . $_POST['id'] );
        deleteFromTable( 'upcoming_course_schedule', 'id', $_POST );
    }
    goBack( "admin_acad_schedule_upcoming_courses.php" );
    exit;
}
else
    echo printInfo( "Unknown request " . $_POST[ 'response' ] );

echo goBackToPageLink( 'admin_acad_schedule_upcoming_courses.php', 'Go back' );

?>
