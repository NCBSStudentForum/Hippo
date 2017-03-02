<?php

include_once 'header.php';
include_once 'database.php';
include_once 'mail.php';
include_once 'tohtml.php';
include_once 'check_access_permissions.php';

mustHaveAnyOfTheseRoles( array( 'USER' ) );

echo userHTML( );

echo alertUser( "<small>If monday is not chosen as preference
    , I will pick first monday next to chosen date while scheduling.
    </small>" 
    );

if( ! __get__( $_POST, 'created_on', null ) )
    $_POST[ 'created_on' ] = dbDateTime( 'now' );

if( ! __get__( $_POST, 'speaker', null ) )
    $_POST[ 'speaker' ] = $_SESSION[ 'user' ];

// 
echo '<form method="post" action="user_aws_scheduling_request_submit.php">';
echo dbTableToHTMLTable( 'aws_scheduling_request'
        , $_POST, 'first_preference,second_preference,reason'
        , 'submit' 
    );

echo '<input type="hidden" name="created_on" value="' . dbDateTime( 'now' ) . '">';
echo '</form>';

echo goBackToPageLink( 'user_aws.php', 'Go back' );

?>
