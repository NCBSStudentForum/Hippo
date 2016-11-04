<?php 

include_once( 'calendar/calendar.php' );

if( $_POST['response'] == 'add_all_events' ) 
{
    addAllEventsToCalednar( 'NCBS Calendar' );
}
else
{
    echo printWarning( 'Invalid response by user' . $_POST['response'] );
}

echo goBackToPageLink( 'admin.php', 'Go back' );

?>
