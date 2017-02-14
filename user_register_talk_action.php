<?php

include_once 'header.php';
include_once 'database.php';
include_once 'mail.php';
include_once 'tohtml.php';

var_dump( $_POST );

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

    // This entry may be used on public calendar. Putting email anywhere on 
    // public domain is allowed.
    $speakerText = loginToText( $speaker, $withEmail = False  );
    $_POST[ 'speaker' ] = $speakerText;
    $res2 = insertIntoTable( 'talks'
        , 'host,title,speaker,description,created_by'
        , $_POST ); 

    if( $res2 )
    {
        echo printInfo( "Successfully registered your talk." );
        $startTime = $_POST[ 'start_time' ];
        $endTime = $_POST[ 'end_time' ];
        $date = $_POST[ 'end_time' ];
        $venue = $_POST[ 'venue' ];
        $reqs = getRequestsOnThisVenueBetweenTime( $venue, $date
            , $startTime, $endTime );
        $events = getEventsOnThisVenueBetweenTime( $venue, $date
            , $startTime, $endTime );
        if( $reqs || $events )
        {
            echo printInfo( "There is already an events on $venue on $date
                between $startTime and $endTime. 
                <br />
                I am redirecting you to page where you can create booking reqest
                after exploring possible options.  "
            );
            goToPage( 'user_manage_talk.php', 3 );
            exit;
        }

        // Else create a request.
        $external_id = "taks." . $res1[ 'id' ];
        $_POST[ 'external_id' ] = $external_id;
        $_POST[ 'is_public_event' ] = 'YES';

        // Modify talk title for calendar.
        $_POST[ 'title' ] = "Talk by " . $_POST[ 'speaker' ] . 'on \'' . 
            trim( $_POST[ 'title' ] ) . "'";
        $res = submitRequest( $_POST );
        if( $res )
        {
            echo printInfo( "Successfully created booking request" );
            goToPage( "user.php", 2 );
            exit;
        }
        else
            echo printWarning( "Oh Snap! Failed to create booking request" );
    }
    else
        echo printWarning( "Oh Snap! Failed to add your talk to database." );
}
else
    echo printWarning( "Oh Snap! Failed to add speaker to database" );

echo goBackToPageLink( "user.php", "Go back" );

?>
