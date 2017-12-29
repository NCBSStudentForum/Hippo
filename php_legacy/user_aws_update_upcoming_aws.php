<?php

include_once 'header.php';
include_once './check_access_permissions.php';
mustHaveAllOfTheseRoles( array( 'USER' ) );

include_once 'database.php';
include_once 'tohtml.php';

echo userHTML( );

echo "<h2>You are updating your upcoming AWS </h2>";

echo alertUser( "If you can't find supervisors/TCM members in drop down list,
    you'd have to go back and add them. The facility to do so is in your home 
    page (<tt>My Home</tt> link in top-right corner)." );

echo printInfo( 
    '<strong>DO NOT COPY/PASTE from Office/Word/Webpage/etc.</strong>' 
    );
echo '
    <p>
    Copy/paste usually inserts special characters; they can break my PDF convertor.
    If you paste from other application, be sure to ckick on <tt>Tools -> Source 
    code</tt> in editor below to see what has been pasted. Remove as much formatting 
    as you can. Ideally, you should paste plain text and format it to your heart 
    desire.
    </p>
    ' ;

echo "<br>";

if( $_POST[ 'response' ] == 'update' )
{
    $awsId = $_POST[ 'id' ];
    $aws = getUpcomingAWSById( $awsId );

    echo '<form method="post" action="user_aws_update_upcoming_aws_submit.php">';
    echo editableAWSTable( -1, $aws );
    echo '<input type="hidden", name="id" value="' . $awsId . '">';
    echo '</form>';
}

echo goBackToPageLink( 'user_aws.php', 'Go back' );

?>
