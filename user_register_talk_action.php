<?php

include_once 'header.php';
include_once 'database.php';
include_once 'mail.php';
include_once 'tohtml.php';

/* ALL EVENTS GENERATED FROM THIS INTERFACE ARE SUITABLE FOR GOOGLE CALENDAR. */

// Here I get both speaker and talk details. I need a function which can either 
// insert of update the speaker table. Other to create a entry in talks table.
// Sanity check 
if( ! ( $_POST['first_name']  && $_POST[ 'institute' ] && $_POST[ 'title' ] 
    && $_POST[ 'description' ] ) )
{
    echo printInfo( 'Incomplete entry. Required fields: First name, last name, 
        institute, title and description of talk. ' );
    echo arrayToVerticalTableHTML( $_POST, 'info' );
    echo goBackToPageLink( 'user_register_talk.php', 'Go back' );
    exit;
}
else                // Everything is fine.
{
    $filename = $_POST[ 'first_name' ] . $_POST[ 'middle_name' ] . 
        $_POST[ 'last_name' ] . '.png' ;

    print_r( $_FILES );
    if( $_FILES[ 'picture' ] )
    {
        echo "Uploading image";
        uploadImage( $_FILES['picture'], $filename );
    }

    // Insert the speaker into table. if it already exists, just update the 
    // values.
    $res1 = insertOrUpdateTable( 'speakers'
        , 'email,first_name,middle_name,last_name,department,institute,homepage'
        , 'department,institute,homepage,email'
        , $_POST 
    );

    if( $res1 )  // Sepeaker is successfully updated. Move on.
    {
        // Assign speaker id from previous query.
        $res1[ 'id' ] = $res1[ 'LAST_INSERT_ID()' ];
        $speaker = getTableEntry( 'speakers', 'id', $res1 );

        // This entry may be used on public calendar. Putting email anywhere on 
        // public domain is allowed.
        $speakerText = loginToText( $speaker, $withEmail = False  );
        $_POST[ 'speaker' ] = $speakerText;

        $res2 = insertIntoTable( 'talks'
            , 'host,title,speaker,description,created_by,created_on'
            , $_POST ); 

        if( $res2 )
        {
            $talkId = $res2[ 'LAST_INSERT_ID()'];
            echo printInfo( "Successfully registered your talk with id $talkId" );
            $startTime = $_POST[ 'start_time' ];
            $endTime = $_POST[ 'end_time' ];
            $date = $_POST[ 'end_time' ];
            $venue = $_POST[ 'venue' ];

            if( $venue && $startTime && $endTime && $date )
            {
                /* Check if there is a conflict between required slot and already 
                 * booked events or requests. If no then book else redirect user to 
                 * a page where he can make better decisions.
                 */

                $reqs = getRequestsOnThisVenueBetweenTime( $venue, $date
                    , $startTime, $endTime );
                $events = getEventsOnThisVenueBetweenTime( $venue, $date
                    , $startTime, $endTime );
                if( $reqs || $events )
                {
                    echo printInfo( "There is already an events on $venue on $date
                        between $startTime and $endTime. 
                        <br />
                        I am redirecting you to page where you can browse all venues 
                       and create suitable booking request."
                    );
                    goToPage( 'user_manage_talk.php', 10 );
                    exit;
                }
                else 
                {
                    // Else create a request with external_id as talkId.
                    $external_id = "talks." . $talkId;
                    $_POST[ 'external_id' ] = $external_id;
                    $_POST[ 'is_public_event' ] = 'YES';

                    // Modify talk title for calendar.
                    $_POST[ 'title' ] = "Talk by " . $_POST[ 'speaker' ] . ' on \'' . 
                        trim( $_POST[ 'title' ] ) . "'";

                    $res = submitRequest( $_POST );
                    if( $res )
                        echo printInfo( "Successfully created booking request" );
                    else
                        echo printWarning( "Oh Snap! Failed to create booking request" );
                }
            }
        }
        else
            echo printWarning( "Oh Snap! Failed to add your talk to database." );
    }
    else
        echo printWarning( "Oh Snap! Failed to add speaker to database" );
}

echo goBackToPageLink( "user.php", "Go back" );

?>
