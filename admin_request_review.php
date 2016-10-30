<?php 
include_once( "header.php" );
include_once( "methods.php" );
include_once( "tohtml.php" );

//var_dump( $_POST );

if( $_POST['response'] == "Review" )
{
    // Approve after constructing all the events from the patterns.
    $r = getRequestById( $_POST['requestId'] );
    // First insert this request into event calendar.
    echo "<h2> We are processing following request </h2>";
    echo requestToHTMLTable( $r );

    if( trim($r['repeatPat']) )
    {
        $days = repeatPatToDays( $r['repeatPat'] );
        $numEvents = count( $days );
        echo printInfo("Due to repeat pattern, 
            this will lead to creation of following $numEvents events"
        );
        foreach( $days as $day )
        {
            $r['date'] = $day;
            $r['repeatPat'] = '';
            echo requestToHTMLTable( $r );
            echo isVenueAvailable( $r['venue'], $r['date']
                , $r['startOn'], $r['endOn']  );
        }
    }
}

//goToPage( "admin.php", 5 );

?>
