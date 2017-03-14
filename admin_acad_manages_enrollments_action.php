<?php

include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'AWS_ADMIN' ) );

include_once 'database.php';
include_once 'tohtml.php';
include_once 'methods.php';

echo userHTML( );

// Manage courses. Add into course_registration.
if( $_POST[ 'response' ] == 'submit' )
{
    $_POST[ 'last_modified_on' ] = dbDateTime( 'now' );
    $res = insertIntoTable( 'course_registration'
                , 'student_id,semester,year,type,course_id,registered_on,last_modified_on'
                , $_POST );

    if( $res )
    {
        echo printInfo( "You are successfully registered for the course" );
        goBack( 'user_manages_courses.php', 1 );
        exit;
    }
    else
    {
        echo minionEmbarrassed( "Failed to register you for the course." );
        echo printWarning( "Most likely you are already registered for this course" );
    }
    
}
else if( $_POST[ 'response' ] == 'drop' )
{
    // Drop this course for given user.
    $res = deleteFromTable( 'course_registration'
                        , 'student_id,semester,year,course_id'
                        , $_POST ); 
    if( $res )
        echo printInfo( "Successfully dropped course." );
    else
        echo minionEmbarrassed( "Failed to drop course" );
}

else
    echo alertUser( 'Unknown type of request ' . $_POST[ 'response' ] );

echo goBackToPageLink( "admin_acad_manages_enrollments.php", "Go back" );

?>
