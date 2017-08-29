<?php

include_once 'check_access_permissions.php';
mustHaveAnyOfTheseRoles( array( 'AWS_ADMIN', 'ADMIN' ) );

include_once 'methods.php';
include_once 'database.php';
include_once 'tohtml.php';


if( $_POST['response'] == 'DO_NOTHING' )
{
    echo printInfo( "User said do nothing.");
    goBack( "admin_acad_manages_current_courses.php", 0 );
    exit;
}
else if( $_POST['response'] == 'delete' )
{
    // We may or may not get email here. Email will be null if autocomplete was 
    // used in previous page. In most cases, user is likely to use autocomplete 
    // feature.
    if( strlen($_POST[ 'id' ]) > 0 )
    {
        $res = deleteFromTable( 'courses', 'id', $_POST );
        if( $res )
        {
            echo printInfo( "Successfully deleted entry" );
            goBack( 'admin_acad_manages_current_courses.php', 0 );
            exit;
        }
        else
            echo minionEmbarrassed( "Failed to delete speaker from database" );
    }
}
else
{
    $_POST[ 'semester' ] = getCurrentSemester( );
    $_POST[ 'year' ] = getCurrentYear( );

    $courseAtThisSlotVenue = getTableEntry( 'courses'
            , 'slot,venue,year,semester', $_POST );

    if( $courseAtThisSlotVenue )
    {
        // If different course is alredy on this slot/venue.
        if( $courseAtThisSlotVenue[ 'course_id' ] != $_POST[ 'course_id' ] )
        {
            echo printWarning( "Following course is already assigned at this slot/venue" );
            echo arrayToVerticalTableHTML( $courseAtThisSlotVenue, 'info' );
            echo goBackToPageLink( "admin_acad_manages_current_courses.php" );
            exit;
        }
    }

    if ( $_POST[ 'response' ] == 'Add' ) 
    {
        echo printInfo( "Adding a new course in current course list" );
        if( strlen( $_POST[ 'course_id' ] ) > 0 )
        {
            $id = getCourseInstanceId( $_POST[ 'course_id' ] );
            $_POST[ 'id' ] = $id;
            $_POST['semester'] = getSemester( $_POST[ 'start_date' ] );

            $res = insertIntoTable( 
                'courses'
                , 'id,course_id,year,semester,start_date,end_date,slot,venue,note' 
                , $_POST 
            );

            if( ! $res )
                echo printWarning( "Could not add course to list" );
            else
            {
                goBack( 'admin_acad_manages_current_courses.php', 1 );
                exit;
            }
        }
        else
            echo printWarning( "Could ID can not be empty" );


    }
    else if ( $_POST[ 'response' ] == 'Update' ) 
    {
        $res = updateTable( 'courses'
            , 'course_id'
            , 'semester,year,start_date,end_date,slot,venue,note'
            , $_POST 
        );

        if( $res )
        {
            echo printInfo( 'Updated course' );
            goBack( 'admin_acad_manages_current_courses.php', 1);
            exit;
        }
    }
}

echo goBackToPageLink( 'admin_acad_manages_current_courses.php', 'Go back' );


?>
