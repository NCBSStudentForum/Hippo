<?php

include_once 'header.php';
include_once 'database.php';
include_once 'methods.php';
include_once "check_access_permissions.php";

mustHaveAllOfTheseRoles( array( 'AWS_ADMIN' ) );

if( isset( $_POST['response'] ) )
{
    $cwd = getcwd( );
    $resfilePath = tempnam( "/tmp", "minion" );

    echo printInfo( "Rescheduling ...." );
    $scriptPath = $cwd . '/schedule_aws.py';
    echo("<pre>Executing $scriptPath $resfilePath, timeout 20 secs</pre>");
    $command = "timeout 20 python $scriptPath $resfilePath";
    exec( $command, $out, $ret );

    // Now read the result file and show to user.
    $res = file_get_contents( $resfilePath );
    echo "<h3>Content of result </h3>";
    echo( "<pre> $res </pre>" );

    // Delete the temp file
    unlink( $resfilePath );
}

echo goBackToPageLink( "admin_aws_manages_upcoming_aws.php", "Go back" );

?>

