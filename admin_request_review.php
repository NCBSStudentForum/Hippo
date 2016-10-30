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

    if( $r['does_repeat'] && strlen(trim($r['repeat_pat'])) > 0 )
    {
        $days = repeatPatToDays( $r['repeat_pat'] );
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
                , $r['start_time'], $r['end_time']  );
        }
    }
}

//goToPage( "admin.php", 5 );

?>
