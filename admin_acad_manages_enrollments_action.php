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
    $_POST[ 'registered_on'] = dbDateTime( 'now' );
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
else if( in_array( $_POST[ 'response' ], getTableColumnTypes( 'course_registration', 'type' ) ) )
{
    // Drop this course for given user.
    $_POST[ 'type' ] = $_POST[ 'response' ];
    $res = updateTable( 'course_registration'
        , 'student_id,semester,year,course_id', 'type', $_POST 
    ); 
    if( $res )
        echo printInfo( "Successfully changed enrollment." );
    else
        echo minionEmbarrassed( "Failed to change enrollment for this." );
}
else if( $_POST[ 'response' ] == 'grade' )
{
    $_POST[ 'grade_is_given_on' ] = dbDate( 'now' );
    foreach( explode( ',', $_POST[ 'student_ids'] ) as $student )
    {
        $grade = $_POST[ $student ];
        $data = array( 'student_id' => $student
                        , 'semester' => $_POST[ 'semester' ]
                        , 'year' => $_POST[ 'year' ]
                        , 'course_id' => $_POST[ 'course_id' ]
                        , 'grade' => $grade 
                        , 'grade_is_given_on' => dbDate( 'now ' )
                    );

        $res = updateTable( 'course_registration'
                        , 'student_id,semester,year,course_id'
                        , 'grade,grade_is_given_on'
                        , $data 
                    );

        if( $res )
            echo printInfo( "Successfully assigned grade for " . $student );
        else
            echo alertUser( "Could not assign grade for " . $student );

        ob_flush( );

    }
}
else if( $_POST[ 'response' ] == 'enroll_new' )
{
    $emails = preg_split( "/[\s,]+/", $_POST[ 'logins'] );
    foreach( $emails as $email )
    {
        $user = findAnyoneWithEmail( $email );
        if( ! $user )
        {
            echo printWarning( "$email is not found in my database. Probably 
                a mistake. I am ignoring this candidate. " 
                );
            continue;
        }

        $user = getLoginByEmail( $email );
        $res = insertIntoTable( 'course_registration'
            , 'student_id,registered_on,course_id,semester,year,type'
            , array( 'student_id' => $user
                , 'course_id' => $_POST[ 'course_id' ]
                , 'type' => $_POST[ 'type' ]
                , 'semester' => getCurrentSemester( )
                , 'year' => getCurrentYear( ) 
                , 'registered_on' => dbDateTime( 'now' )
            )
            );
        if( $res )
            echo printInfo( "Successfully enrolled $user" );
    }
}
else
    echo alertUser( 'Unknown type of request ' . $_POST[ 'response' ] );

echo goBackToPageLink( "admin_acad_manages_enrollments.php", "Go back" );

?>
