<?php

include_once 'header.php';
include_once 'database.php';
include_once 'methods.php';
include_once "check_access_permissions.php";

mustHaveAllOfTheseRoles( array( 'AWS_ADMIN' ) );

if( isset( $_POST['response'] ) )
{
    $cwd = getcwd( );
    $resfile = tmpfile( );
    $meta = stream_get_meta_data( $resfile );
    $resfilePath = $meta[ 'uri' ];

    echo printInfo( "Rescheduling ...." );
    $scriptPath = $cwd . '/schedule_aws.py';
    echo printInfo("Executing $scriptPath $resfilePath, timeout 20 secs");
    $command = "timeout 20 python $scriptPath $resfilePath";
    exec( $command, $out, $ret );
    echo( "Output of command: " );
    print_r( $out );
    echo( "Return code: " . $ret );
}

echo goBackToPageLink( "admin_aws_manages_upcoming_aws.php", "Go back" );

?>

