<?php

include_once 'header.php';
include_once 'database.php';
include_once 'tohtml.php';
include_once 'html2text.php';

echo "<h2>Browse events on a particular day</h2>";

$today = dbDate( 'today' );

$default = array( 'date' => $today );

if( $_GET )
{
    if( array_key_exists( 'date', $_GET ) )
        $default[ 'date' ] = $_GET[  'date' ];
    else
        $default = array( 'date' => $today );
}

echo '
    <form method="get" action="">
    <table border="0">
        <tr>
            <td>Select date</td>
            <td><input class="datepicker" type="text" name="date" value="' .
                    $default[ 'date' ] . '" ></td>
            <td><button type="submit" name="response"
                    title="Show events on this day"
                    value="show">Show me</button></td>
        </tr>
    </table>
    </form>
    ';

$whichDay = $default[ 'date' ];
$eventTalks = getTableEntries( 'events', 'date,start_time' , "date='$whichDay'
        AND status='VALID' AND external_id LIKE 'talks%'"
    );

// Only if a event has an external_id then push it into 'talks'
if( count( $eventTalks ) < 1 )
{
    echo alertUser( "I could not find any talk/seminar/lecture at given day!" );
}
else
{
    $talkHtml = '';

    foreach( $eventTalks as $event )
    {
        $talkId = explode( '.', $event[ 'external_id'])[1];
        $talk = getTableEntry( 'talks', 'id', array( 'id' => $talkId ) );
        if( $talk )
        {
            $talkHtml .= talkToHTML( $talk, $with_picture = true );

            $talkHtml .= "<br>";
            // Link to pdf file.
            $talkHtml.= '<a style="margin-left:500px"
                        target="_blank" href="generate_pdf_talk.php?date='
                        . $default[ 'date' ] . '&id=' . $talkId . '">
                        <i class="fa fa-download ">PDF</i></a>';
        }
    }
    echo $talkHtml;

    echo '<br>';

}

echo '<br><br>';
echo closePage( );

?>
