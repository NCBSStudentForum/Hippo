<?php

include_once 'header.php';
include_once 'check_access_permissions.php';
include_once 'database.php';
include_once 'tohtml.php';

mustHaveAllOfTheseRoles( array( "AWS_ADMIN" ) );

echo userHTML( );

if( $_POST[ 'response' ] == 'Reject' )
{
    echo "TODO: Need to write an email to user";
    $res = updateTable( 
        'aws_requests', 'id' , 'status'
        , array( 'id' => $_POST['request_id'], 'status' => 'REJECTED' )
    );
    if( $res )
    {
        echo printInfo( "This request has been rejected" );
        goToPage( "admin_aws_manages_requests.php", 1 );
        exit;
    }
}
elseif( $_POST['response'] == 'Accept' )
{
    echo "TODO: Need to write email";
    $speaker = $_POST[ 'speaker' ];
    $date = $_POST[ 'date' ];
    $aws = getMyAwsOn( $speaker, $date );
    $req = getAwsRequestById( $_POST[ 'request_id' ] );

    //var_dump( $req );

    $res = updateTable( 'annual_work_seminars'
            , 'speaker,date' 
            , array( 'abstract', 'title'
                , 'supervisor_1', 'supervisor_2'
                , 'tcm_member_1', 'tcm_member_2', 'tcm_member_3', 'tcm_member_4' 
                )
            , $req
            );

    if( $res )
    {
        $res = updateTable( 
            'aws_requests', 'id', 'status'
            , array( 'id' => $_POST[ 'request_id' ], 'status' => 'APPROVED' ) 
        );

        if( $res )
        {
            echo printInfo( "Successfully updated request." );
            echo goToPage( 'admin_aws_manages_requests.php', 1 );
            exit;
        }
    }
    else
        echo printWarning( "Could not update the AWS table" );
}
else
{
    echo printWarning( "Unknown request " . $_POST[ 'response' ] );
}

echo goBackToPageLink( "admin_aws_manages_requests.php", "Go back" );

?>
