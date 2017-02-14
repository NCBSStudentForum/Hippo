<?php

include_once 'header.php';
include_once 'database.php';
include_once 'mail.php';
include_once 'tohtml.php';

//var_dump( $_POST );

// Here I get both speaker and talk details. I need a function which can either 
// insert of update the speaker table. Other to create a entry in talks table.

$res1 = insertOrUpdateTable( 'speakers'
    , 'email,first_name,middle_name,last_name,department,institute,homepage'
    , 'department,institute,homepage,email'
    , $_POST 
    );

if( $res1 )
{
    // Assign speaker id from previous query.
    $res1[ 'id' ] = $res1[ 'LAST_INSERT_ID()' ];
    $speaker = getTableEntry( 'speakers', 'id', $res1 );
    $speakerText = loginToText( $speaker );
    $_POST[ 'speaker' ] = $speakerText;
    $res2 = insertIntoTable( 'talks'
        , 'host,title,speaker,description,created_by'
        , $_POST ); 

    if( $res2 )
    {
        echo printInfo( "Successfully registered your talk." );
        goToPage( "user.php", 1 );
        exit;
    }
    else
        echo printWarning( "Oh Snap! Failed to add your talk to database." );
}
else
    echo printWarning( "Oh Snap! Failed to add speaker to database" );

echo goBackToPageLink( "user.php", "Go back" );
exit;

?>
