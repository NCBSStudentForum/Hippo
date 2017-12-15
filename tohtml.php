<?php

include_once 'methods.php';
include_once 'database.php';
include_once 'ICS.php';
include_once 'linkify.php';

$useCKEditor = false;

if( $useCKEditor )
    echo '<script src="https://cdn.ckeditor.com/4.6.2/standard/ckeditor.js"></script>';
?>

<script>
function displayEvent( button )
{
    alert( button.value );
};
function displayRequest( button )
{
    alert( button.value );
};
</script>

<?php

function fixHTML( $html, $strip_tags = false )
{
    $res = $html;
    if( $strip_tags )
        $res = strip_tags(  $res, '<br><p><a><strong><tt>' );
    // Replate all new line with space.
    $res = preg_replace( "/[\r\n]+/", ' ', $res );
    $res = str_replace( '<br />', ' ', $res );
    $res = str_replace( '<br/>', ' ', $res );
    $res = str_replace( '<br>', ' ', $res );


    return $res;
}


function addToGoogleCalLink( $event )
{
    $location = venueToText( $event[ 'venue' ] );
    $date = dateTimeToGOOGLE( $event[ 'date' ], $event[ 'start_time' ] )
                . '/' . dateTimeToGOOGLE( $event[ 'date' ], $event[ 'end_time' ] );

    $link = 'http://www.google.com/calendar/event?action=TEMPLATE';
    $link .= '&text=' . rawurlencode( $event[ 'title' ] );
    $link .= "&dates=" . $date;
    $link .= "&ctz=Asia/Kolkata";
    $link .= '&details=' . rawurlencode( $event[ 'description' ] );
    $link .= '&location=' . rawurlencode( $location );

    $res = '<a href="'. $link . '" target="_blank" >';

    // Get inline image.
    $res .= inlineImage( __DIR__ . '/data/gc_button6.png' );
    $res .= '</a>';

    return $res;
}

/* --------------------------------------------------------------------------*/
/**
    * @Synopsis  Convert event to ICS file.
    *
    * @Param $event
    *
    * @Returns
 */
/* ----------------------------------------------------------------------------*/
function eventToICAL( $event )
{
    $ics = new ICS( $event );
    return $ics;
}

function eventToICALLink( $event )
{
    return '';

    $prop = array( );
    $prop[ 'dtstart' ] = $event[ 'date' ] . ' ' . $event[ 'start_time' ];
    $prop[ 'dtend' ] = $event[ 'date' ] . ' ' . $event[ 'end_time' ];
    $prop[ 'description' ] = substr( $event[ 'description' ], 0, 200 );
    $prop[ 'location' ] = venueToText( $event[ 'venue' ] );
    $prop[ 'summary' ] = $event[ 'title' ];

    $ical = eventToICAL( $prop );

    $filename = __DIR__ . '/_ical/' . $event[ 'gid' ] . $event[ 'eid' ] . '.ics';
    file_put_contents( $filename, $ical->to_string( ) );

    $link = '.';
    if( file_exists( $filename ) )
        $link = downloadTextFile( $filename
                        , '<i class="fa fa-calendar"> <strong>iCal</strong></i>'
                        , 'link_as_button'
                    );

    return $link;
}


/**
    * @brief Generate SPEAKER HTML with homepage and link.
    *
    * @param $speaker
    *
    * @return
 */
function speakerToHTML( $speaker )
{
    if( ! $speaker )
        return alertUser( "Error: Speaker not found" );

    // Get name of the speaker.
    $name = array( );
    foreach( explode( ',', 'honorific,first_name,middle_name,last_name' ) as $k )
        if( $speaker[ $k ] )
            array_push( $name, $speaker[ $k ] );

    $name = implode( ' ', $name );

    // Start preparing speaker HTML.
    $html = $name;

    // If there is url. create a clickable link.
    if( $speaker )
    {
        if( array_key_exists('homepage', $speaker) && $speaker[ 'homepage' ] )
            $html .=  '<br><a target="_blank" href="' . $speaker['homepage'] . '">Homepage</a>';

        if( $speaker[ 'department' ] )
            $html .= "<small><br>" . $speaker[ 'department' ];

        $html .= "<br>" . $speaker[ 'institute' ] . "</small>";
    }

    return $html;
}

/**
    * @brief Convert a speaker to HTML based on its ID. Make sure it is > 0.
    *
    * @param $id
    *
    * @return
 */
function speakerIdToHTML( $id )
{
    $speaker = getTableEntry( 'speakers', 'id', array( 'id' => $id ) );
    return speakerToHTML( $speaker );
}


/**
    * @brief Summary table for front page.
    *
    * @return
 */
function summaryTable( )
{
    global $db;
    $allAWS = getAllAWS( );
    $nspeakers = count( getAWSSpeakers( ) );
    $nAws = count( $allAWS );
    $awsThisYear = count( getAWSFromPast( date( 'Y-01-01' ) ) );
    $html = '<table class="summary">';
    //$html .= "
    //    <tr>
    //        <td>$nAws AWSs </td>
    //        <td> $awsThisYear AWSs so far this year </td>
    //    </tr>";
    $html .= "</table>";
    return $html;
}

function loginForm()
{
    $conf = $_SESSION['conf'];
    /* Check if ldap server is alive. */
    $table = "";
    $table .= '<form action="login.php" method="post">';
    $table .= '<table class="login_main">';
    $table .= '<tr><td><input type="text" name="username" id="username"
        placeholder="NCBS/Instem Username" /> </td></tr>';
    $table .= '<tr><td> <input type="password"  name="pass" id="pass"
            placeholder="Password" > </td></tr>';
    $table .= '<tr><td> <input style="float: right" type="submit" name="response" value="Login" /> </td></tr>';
    $table .= '</table>';
    $table .= '</form>';
    return $table;
}

function sanitiesForTinyMCE( $text )
{
    $text = preg_replace( "/\r\n|\r|\n/", " ", $text );
    $text = str_replace( "'", "\'", $text );
    $text = htmlspecialchars_decode( $text );
    return $text;
}

function prettify( $string )
{
    // Replace _ with space.
    $string = str_replace( "_", " ", $string );

    // Uppercase first char.
    $string = ucfirst( $string );
    return $string;
}


/**
    * @brief Convert requests to HTML form for review.
    *
    * @param $requests
    *
    * @return
 */
function requestsToHTMLReviewForm( $requests )
{
    $html = '<table>';
    foreach( $requests as $r )
    {
        $html .= '<tr><td>';
        // Hide some buttons to send information to next page.
        $html .= '<input type="hidden" name="gid" value="' . $r['gid'] . '" />';
        $html .= '<input type="hidden" name="rid" value="' . $r['rid'] . '" />';
        $html .= arrayToTableHTML( $r, 'events'
                                   , ' ',  array( 'status', 'modified_by', 'timestamp', 'url' )
                                 );
        $html .= '</td>';
        $html .= '<td style="background:white">
                 <button name="response" value="Review">Review</button>
                 </td>';
        $html .= '</tr>';
    }
    $html .= '</table>';
    return $html;
}


// Return a short description of event.
function eventToText( $event )
{
    $html = 'By ' . $event['created_by'] . ', ';
    $html .= '';
    $html .= __get__( $event, 'title', '' );
    $html .= ' @' . $event['venue'] . ', ';
    $html .= humanReadableDate( $event['date'] );
    $html .= ', ' . humanReadableTime( $event['start_time'] )
             . ' to ' . humanReadableTime( $event['end_time'] ) ;
    return $html;
}

function eventToShortHTML( $event )
{
    $startT = date( 'H:i', strtotime( $event[ 'start_time' ] ) );
    $endT = date( 'H:i', strtotime( $event[ 'end_time' ] ) );
    $html = '<tt>' .  __get__( $event, 'title', '' ) . ' (' . $event['class'] . ')</tt>';
    $html .= '<br>' . $startT . ' to ' . $endT;
    $html .= ' </tt> @ <strong>' . $event['venue'] . '</strong>, ';
    $html .= '</br><small>Booked by ' . $event['created_by'] . '</small><br/>';
    return $html;
}

function requestToShortHTML( $request )
{
    $startT = date( 'H:i', strtotime( $request[ 'start_time' ] ) );
    $endT = date( 'H:i', strtotime( $request[ 'end_time' ] ) );
    $html = '<tt>' .  __get__( $request, 'title', '' ) . ' (' . $request['class'] . ')</tt>';
    $html .= '<br>' . $startT . ' to ' . $endT;
    $html .= ' </tt> @ <strong>' . $request['venue'] . '</strong>, ';
    $html .= '</br><small>Requested by ' . $request['created_by'] . '</small>';
    $html .= '<br><small>Created on: ' . humanReadableDate( $request['timestamp']) .
                    ' ' . humanReadableTime( $request['timestamp'] ) .
                    '</small><br/>';
    return $html;
}


function eventSummaryHTML( $event, $talk = null)
{
    $date = humanReadableDate( $event[ 'date' ] );
    $startT = humanReadableTime( $event[ 'start_time' ] );
    $endT = humanReadableTime( $event[ 'end_time' ] );
    $time = "$startT to $endT";
    $venue = venueSummary( $event[ 'venue'] );

    if( $talk )
        $title = talkSummaryLine( $talk );
    else
        $title = $event[ 'title'];

    $html = "<h2>" . $title . "</h2>";
    $html .= '<table class="show_events">';

    if( $talk )
    {
        $speaker = $talk[ 'speaker' ];
        $html .= "<tr><td> Host </td><td>" . loginToText( $talk[ 'host' ] ) ."</td></tr>";
        $html .= "<tr><td> Coordinator </td><td>" .
                     loginToText( $talk[ 'coordinator' ] ) ."</td></tr>";
    }

    $html .= "<tr><td> Where </td><td>  $venue </td></tr>";
    $html .= "<tr><td> When </td><td>" . $date . ", " . $time . " </td></tr>";
    $html .= '</table>';

    // Add google and ical links.
    $html .= '<div class="strip_from_md">';
    $html .=  addToGoogleCalLink( $event );
    $html .= '</div>';

    return $html;
}

// Return a short description of event for main page.
function eventSummary( $event )
{
    $html = '<table class=\"event_summary\">';
    $html .= '<tr><td><small>WHEN</small></td><td>' .  date( 'l M d, Y', strtotime($event['date']));
    $html .= date('H:i', strtotime($event['start_time']))  . ' to ' .
             date( 'H:i', strtotime(  $event['end_time'])) . '</td></tr>';

    $html .= '<tr><td><small>WHERE</small></td><td>'.  $event['venue'] . "</td></tr>";
    $html .= '<tr><td><small>WHAT</small></td><td>' . $event['title']
             . "</td></tr>";
    $html .= "</table>";
    return $html;
}


function requestToText( $req )
{
    $html = 'By ' . $req['created_by'] . ', ';
    $html .= $req['title'];
    $html .= ' @' . $req['venue'] . ', ';
    $html .= $req['start_time'] . ' to ' . $req['end_time'];
    $html .= "; ";
    return $html;
}

// $day and $hour are used to check if at this day and hour  this venue is
// booked or have pending requests.
function hourToHTMLTable( $day, $hour, $venue, $section = 4 )
{
    //$tableName = "<font style=\"font-size:12px\">" . strtoupper($venue). "</font><br>";
    $tableTime = "<font style=\"font-size:12px\" >" . date('H:i', $hour) . "</font>";
    $html = "<table class=\"hourtable\">";
    $html .= "<tr><td colspan=\"$section\"> $tableTime </td></tr>";

    $html .= "<tr>";
    for( $i = 0; $i < $section; $i++)
    {
        $stepT = $i * 60 / $section;
        $segTime = strtotime( "+ $stepT minutes", $hour );
        $segDateTime = strtotime( $day . ' ' . date('H:i', $segTime ));

        // Check  for events at this venue. If non, then display + (addEvent)
        // button else show that this timeslot has been booked.

        $events = eventsAtThisVenue( $venue, $day, $segTime );
        $requests = requestsForThisVenue( $venue, $day, $segTime );

        // If there is a public event at this time, change the color of all
        // button at all venues. Thats clue to user that something else has been
        // approved at this time.
        $is_public_event = '';
        if( count( publicEvents( $day, $segTime ) ) > 0 )
            $is_public_event = '_with_public_event"';

        if( count( $events ) == 0 && count($requests) == 0)
        {

            // Add a form to trigger adding event purpose.
            $html .= "<form method=\"post\" action=\"user_submit_booking_request.php\" >";
            $html .= "<td>";
            if( $segDateTime >= strtotime( 'now' ) )
                $html .= "<button class=\"add_event$is_public_event\" name=\"add_event\" value=\"$segTime\">+</button>";
            else
                $html .= "<button class=\"add_event_past$is_public_event\" name=\"add_event\" value=\"$segTime\" disabled></button>";

            $html .= "</td>";
            // And the hidden elements to carry the values to the action page.
            $html .= '<input type="hidden" name="start_time" value="'.
                     dbTime($segTime) . '">';
            $html .= '<input type="hidden" name="date" value="'. $day . '">';
            $html .= '<input type="hidden" name="venue" value="'. $venue . '">';
            $html .= "</form>";
        }
        else
        {
            if( count( $events ) > 0 )
            {
                $msg = '';
                foreach( $events as $e )
                    $msg .= eventToText( $e );
                $html .= "<td><button class=\"display_event\"
                         value=\"$msg\" onclick=\"displayEvent(this)\"></button></td>";
            }
            elseif( count( $requests ) > 0 )
            {
                $msg = '';
                foreach( $requests as $r )
                    $msg .= requestToText( $r );
                $html .= "<td><button class=\"display_request\"
                         value=\"$msg\" onclick=\"displayRequest(this)\"></button></td>";
            }
        }
    }
    $html .= "</tr></table>";
    return $html;
}

// Convert a event into a nice looking html line.
function eventLineHTML( $date, $venueid, $start = '8:00', $end = '18:00' )
{
    $venue = getVenueById( $venueid );
    $venueText = venueSummary( $venueid );
    $html = '<table class="eventline">';
    $startDay = $start;
    $dt = 60;
    $html .= "<tr>";
    $html .= "<td><div style=\"width:100px\">$venueText</div></td>";
    $duration = ( strtotime( $end ) - strtotime( $start ) ) / 3600;
    for( $i = 0; $i < $duration; $i++ )
    {
        $stepT = $i * $dt;
        $segTime = strtotime( $startDay ) + 60 * $stepT;
        // Each hour has 15 minutes segment. For each segment hourToHTMLTable
        // create a block.
        $html .= "<td>" . hourToHTMLTable( $date, $segTime, $venueid, 4 ) . "</td>";
    }
    $html .= "</tr>";
    $html .= '</table>';
    return $html;
}

// Convert a event into a readonly event line.
function readOnlyEventLineHTML( $date, $venueid )
{
    $events = getEventsOnThisVenueOnThisday( $venueid, $date );
    $requests = getRequestsOnThisVenueOnThisday( $venueid, $date );

    $html = '';
    if( count( $events ) + count( $requests ) > 0 )
    {
        $html .= '<table class="show_calendar">';
        $html .= "<tr> <td> $venueid </td>";

        $html .= "<td> <table class=\"show_info\"><tr>";
        foreach( $requests as $req )
            $html .= '<td> Unapproved:<br>' . requestToText( $req ) . "</td>";

        foreach( $events as $event )
            $html .=  "<td>" . eventToText( $event ) . "</td>";
        $html .= "</tr></table>";

        $html .= "</td></tr>";
        $html .= '</table>';
    }
    return $html;
}

/**
    * @brief Convert each array to a single HTML row.
    *
    * @param $array
    * @param $tablename
    * @param $tobefilterd
    *
    * @return
 */
function arrayToRowHTML( $array, $tablename, $tobefilterd = '', $withtr=true )
{
    if( $withtr )
        $row = '<tr>';
    else
        $row = '';

    if( is_string( $tobefilterd ) )
        $tobefilterd = explode( ',', $tobefilterd );

    $keys = array_keys( $array );
    $toDisplay = Array();
    foreach( $keys as $k )
        if( ! in_array( $k, $tobefilterd ) )
            $toDisplay[] = $array[ $k ];

    foreach( $toDisplay as $v )
    {
        if( isStringAValidDate( $v ) )
            $v = humanReadableDate( $v );
        $v = linkify( $v );

        $row .= "<td><div class=\"cell_content\">$v</div></td>";
    }

    if( $withtr )
        $row  .= "</tr>";
    else
        $row .= '';

    return $row;

}

/**
    * @brief Convert an array to HTML header row. Only th fields are used.
    *
    * @param $array
    * @param $tablename
    * @param $tobefilterd
    *
    * @return
 */
function arrayHeaderRow( $array, $tablename, $tobefilterd = '', $sort_button = false )
{
    $hrow = '';
    $keys = array_keys( $array );
    $toDisplay = Array();
    $hrow .= "<tr>";

    if( is_string( $tobefilterd ) )
        $tobefilterd = explode( ',', $tobefilterd );

    foreach( $keys as $k )
        if( ! in_array( $k, $tobefilterd ) )
        {
            $kval = prettify( $k );
            $label = strtoupper( $kval );
            $sortButton = '';
            if( $sort_button )
            {
                $sortButton = '<table class="sort_button"><tr>';
                $sortButton .= "<td><button class='sort' name='response' value='sort'>
                    <i class='fa fa-sort-asc'></i>
                    </button></td>";
                $sortButton .= "<td><button class='sort' name='response' value='sort'>
                    <i class='fa fa-sort-desc'></i>
                    </button></td>";
                $sortButton .= '<input type="hidden" name="key" value="' . $k . '" />';
                $sortButton .= '</tr></table>';
            }
            $hrow .= "<th class=\"db_table_fieldname\">$label $sortButton</th>";
        }

    return $hrow;
}

function arrayToTHRow( $array, $tablename, $tobefilterd = '', $sort_button  = false )
{
    return arrayHeaderRow( $array, $tablename, $tobefilterd
                , $sort_button
            );
}

// Convert an array to HTML
function arrayToTableHTML( $array, $tablename, $background = ''
        , $tobefilterd = '', $header = true )
{
    if( $background )
        $background = "style=\"background:$background;\"";

    if( is_string( $tobefilterd ) )
        $tobefilterd = explode( ',', $tobefilterd );

    $table = "<table class=\"show_$tablename\" $background>";
    $keys = array_keys( $array );
    $toDisplay = Array();
    if( $header )
    {
        $table .= "<tr>";
        $table .= arrayHeaderRow( $array, $tablename, $tobefilterd );
        $table .= "</tr>";
    }
    $table .= arrayToRowHTML( $array, $tablename, $tobefilterd );
    $table .= "</table>";
    return $table;
}

// Convert an array to HTML table (vertical)
function arrayToVerticalTableHTML( $array, $tablename
                                   , $background = NULL, $tobefilterd = '' )
{
    if( $background )
        $background = "style=\"background:$background;\"";
    else
        $background = '';

    if( is_string( $tobefilterd ) )
        $tobefilterd = explode( ",", $tobefilterd );

    $table = "<table class=\"show_$tablename\" $background>";
    $keys = array_keys( $array );
    $toDisplay = Array();
    foreach( $keys as $k )
        if( ! in_array( $k, $tobefilterd ) )
        {
            $table .= "<tr>";
            $kval = prettify( $k );
            $label = strtoupper( $kval );
            $table .= "<td class=\"db_table_fieldname\">$label</td>";

            // Escape some special chars speacial characters.
            $text = linkify( $array[ $k ] );

            $table .= "<td><div class=\"cell_content\">$text</div></td>";
            $table .= "</tr>";
        }

    // Also set the content as div element which can be formatted using css
    $table .= "</table>";
    return $table;
}


function requestToHTML( $request )
{
    return arrayToTableHTML( $request, "request" );
}


function arrayToHtmlTableOfLogins( $logins )
{
    $table = '<table>';

    foreach( $logins as $i => $login )
    {
        $table .= "<tr><td> " . ($i + 1) . "</td><td>"
            . arrayToName( $login ) . "</td><td>" . $login['email']
            . "</td></tr>";

    }
    $table.= '</table>';
    return $table;
}

function userHTML( )
{
    $html = "<table class=\"user_float\">";
    $html .= "<tr colspan=\"2\"><th>Hi " . $_SESSION['user'] . "</th>";
    $html .= '<th><a href="logout.php"><i class="fa fa-sign-out"></i>SignOut</a></th>';
    $html .= '</tr>';
    $html .= "<tr><td><a href=\"quickbook.php\"><i class=\"fa fa-hand-pointer-o\"></i>QuickBook</a>";
    $html .= "<td><a href=\"user.php\"><i class=\"fa fa-home\"></i>My Home</a>";
    $html .= "</tr>";
    $html .= "</table>";
    return $html;
}

/*
function venuesToCheckButtons( $venues )
{
    $html = "<table>";
    foreach( $venues as $venue )
    {
        $html .= '<tr><td><input type="radio" name="venue[]" value="' . $venue['id']
            . '">' . $venue['id'] .  "</td></tr>";
    }
    $html .= "</table>";
    return $html;
}
 */

function venuesToHTMLCheck( $groupedVenues, $grouped )
{
    $html = '<table class="venues">';
    $html .= "<tr>";
    foreach( array_keys( $groupedVenues ) as $venueType )
        $html .= "<td> $venueType </td>";
    $html .= "</tr><tr>";
    foreach( array_values($groupedVenues) as  $venues )
        $html .= "<td> " . venuesToChekcButtons( $venues ) . "</td>";
    $html .= "</tr></table>";
    return $html;
}

function venueSummary( $venue )
{
    if( is_string( $venue ) )
        $venue = getVenueById( $venue );

    return trim( $venue['name'] . ' [' . $venue[ 'type' ] . '], ' .
        $venue['building_name'] . ', ' . $venue['location'] );
}

function requestToEditableTableHTML( $request, $editables = Array( ) )
{
    $html = "<table class=\"request_show_edit\">";
    foreach( $request as $key => $value )
    {
        $editHTML = $value;
        if( in_array( $key, $editables ) )
        {
            $inType = "input";
            $props = "style=\"width:100%;\"";
            $text = "";
            if( $key == "description" )
            {
                $inType = "textarea";
                $props  = $props . " rows=\"4\"";
                $text = $value;
            }

            $editHTML = "<$inType $props name=\"$key\" value=\"$value\">$text</$inType>";
        }
        $html .= "<tr> <td>$key</td><td> $editHTML </td> </tr>";
    }
    $html .= "</table>";
    return $html;
}

/**
    * @brief Add tinyMCE editor.
    *
    * @param $id
    *
    * @return
    */
function editor_script( $id, $default = '' )
{
    $editor = "<script>
        tinymce.init( { selector : '#" . $id . "'
        , init_instance_callback: \"insert_content\"
        , plugins : [ 'image imagetools link paste code wordcount fullscreen table' ]
        , paste_as_text : false
        , paste_enable_default_filters: false
        , height : 300
        , paste_data_images: true
        , cleanup : false
        , verify_html : false
        , cleanup_on_startup : false
        , toolbar1 : 'undo redo | insert | stylesheet | bold italic'
        + ' | alignleft aligncenter alignright alignjustify'
        + ' | bulllist numlist outdent indent | link image'
        , toolbar2 : \"imageupload\",
            setup: function(editor) {
                var inp = $('<input id=\"tinymce-uploader\" ' +
                    'type=\"file\" name=\"pic\" accept=\"image/*\"'
                    + ' style=\"display:none\">'
                );
                $(editor.getElement()).parent().append(inp);
                inp.on(\"change\",function(){
                    var input = inp.get(0);
                    var file = input.files[0];
                    var fr = new FileReader();
                    fr.onload = function() {
                        var img = new Image();
                        img.src = fr.result;
                        editor.insertContent(
                            '<img src=\"' + img.src + '\"/><br/>'
                        );
                        inp.val('');
            }
            fr.readAsDataURL(file);
            });

            editor.addButton( 'imageupload', {
            text:\"Insert image\",
                icon: false,
                onclick: function(e) {
                    inp.trigger('click');
            }
            });
            }
            });

            function insert_content( inst ) {
                inst.setContent( '$default' );
            }
                                    </script>";

    return $editor;

}


/**
    * @brief Convert a database table schema to HTML table to user to
    * edit/update.
    *
    * @param $tablename Name of table (same as database)
    * @param $defaults Default values to pass to entries.
    * @param $editables These keys will be convert to appropriate input fields.
    * @param $button_val What value should be visible on 'response' button?
    * @param $hide These keys will be hidden to user.
    *
    * @return  An html table. You need to wrap it in a form.
 */
function dbTableToHTMLTable( $tablename, $defaults=Array()
    , $editables = '', $button_val = 'submit', $hide = '' )
{
    global $symbUpdate, $symbCheck;
    global $symbEdit;
    global $dbChoices;
    global $useCKEditor;

    $html = "<table class=\"editable_$tablename\" id=\"$tablename\">";
    $schema = getTableSchema( $tablename );

    if( is_string( $editables ) )
        $editables = explode( ",", $editables );

    /*
     *  Editabale can have keyval:attribs format. Separate out the extra format
     *  from keys.
     */
    $attribMap = array( );
    $editableKeys = array( );
    foreach( $editables as $v )
    {
        $temp = explode( ":", $v );
        $editableKeys[ ] = $temp[0];
        if( count( $temp ) > 1 )
            $attribMap[ $temp[0] ] = array_slice( $temp, 1 );
    }


    if( is_string( $hide ) )
        $hide = explode( ",", $hide );

    // Sort the schema in the same order as editable.
    foreach( $schema as $col )
    {
        $keyName = $col['Field'];

        // If this entry is in $hide value, do not process it.
        if( in_array( $keyName, $hide ) )
            continue;

        $ctype = $col['Type'];

        // Add row to table
        $columnText = strtoupper( prettify( $keyName ) );

        // Update column text if 'required' is in attributes.
        $attribs = __get__( $attribMap, $keyName, null );
        $required = false;
        if( $attribs )
            if( in_array( 'required', $attribs ) )
                $required = true;

        if( $required )
            $columnText .= '<strong>*</strong>';

        $inputId = $tablename . "_" . $keyName;
        $html .= "<tr><td class=\"db_table_fieldname\" > $columnText </td>";

        $default = __get__( $defaults, $keyName, $col['Default'] );

        // DIRTY HACK: If value is already a html entity then don't use a input
        // tag. Currently only '<select></select> is supported
        if( preg_match( '/\<select.*?\>(.+?)\<\/select\>/', $default ) )
        {
            $val = $default;
        }
        else
        {
            $val = "<input class=\"editable\"
                   name=\"$keyName\" type=\"text\" value=\"$default\" id=\"$inputId\"
                   />";
        }

        // Genearte a select list of ENUM type class.
        $match = Array( );
        if( preg_match( '/^varchar\((.*)\)$/', $ctype ) )
        {
            $classKey = $tablename . '.' . $keyName;
            if( array_key_exists( $classKey, $dbChoices ) )
            {
                $val = "<select name=\"$keyName\">";
                $choices = getChoicesFromGlobalArray( $dbChoices, $classKey );

                foreach( $choices as $k => $v )
                {
                    $selected = '';
                    $v = str_replace( "'", "", $v );
                    if( $v == $default )
                        $selected = 'selected';
                    $val .= "<option value=\"$v\" $selected> $v </option>";
                }
                $val .= "</select>";
            }

        }
        elseif( preg_match( "/^enum\((.*)\)$/" , $ctype, $match ) )
        {
            $val = "<select name=\"$keyName\">";
            foreach( explode(",", $match[1] ) as $v )
            {
                $selected = '';
                $v = str_replace( "'", "", $v );
                if( $v == $default )
                    $selected = 'selected';
                $val .= "<option value=\"$v\" $selected> $v </option>";
            }

            $val .= "</select>";
        }

        // TODO generate a multiple select for SET typeclass.
        else if( preg_match( "/^set\((.*)\)$/", $ctype, $match ) )
        {
            $val = "<select multiple name=\"" . $keyName . '[]' . "\">";
            foreach( explode(",", $match[1] ) as $v )
            {
                $selected = '';
                $v = str_replace( "'", "", $v );
                // If it is set, there might be multiple values here. So check
                // in all of them.
                if( in_array($v, explode(',', $default) ) )
                    $selected = 'selected';
                $val .= "<option value=\"$v\" $selected> $v </option>";
            }
            $val .= "</select>";
        }
        else if( strpos( strtolower($ctype), 'text' ) !== false )     // TEXT or MEDIUMTEXT
        {
            // NOTE: name and id should be same of ckeditor to work properly.
            // Sometimes we have two fileds with same name in two tables, thats
            // a sticky situation.

            $default = sanitiesForTinyMCE( $default );

            $val = "<textarea class=\"editable\" \
                id=\"$inputId\" name=\"$keyName\" >" . $default . "</textarea>";

            // Either use CKEDITOR or tinymce.
            if( $useCKEditor )
                $val .= "<script> CKEDITOR.replace( '$inputId' ); </script>";
            else
            {
                $val .= editor_script( $inputId, $default );
            }
        }
        else if( strcasecmp( $ctype, 'date' ) == 0 )
            $val = "<input class=\"datepicker\" name=\"$keyName\" value=\"$default\" />";
        else if( strcasecmp( $ctype, 'datetime' ) == 0 )
            $val = "<input class=\"datetimepicker\" name=\"$keyName\" value=\"$default\" />";
        else if( strcasecmp( $ctype, 'time' ) == 0 )
            $val = "<input class=\"timepicker\" name=\"$keyName\" value=\"$default\" />";

        // If not in editables list, make field readonly.
        // When the value is readonly. Just send the value as hidden input and
        // display the default value.
        $readonly = True;
        if( in_array($keyName , $editableKeys ) )
            $readonly = False;

        if( $readonly )
        {
            $val = "<input type=\"hidden\" id=\"$inputId\"
                    name=\"$keyName\" value=\"$default\"/>$default";
        }


        $html .= "<td>" . $val . "</td>";
        $html .= "</tr>";
    }

    // If some fields are editable then we need a submit button as well unless
    // user pass an empty value
    $buttonSym = ucfirst( $button_val );

    // DONT use symbols. They are not visible.
    //if( strtolower($button_val) == 'submit' )
    //    $buttonSym = "&#10003";
    //else if( strtolower( $button_val ) == 'update' )
    //    $buttonSym = $symbUpdate;
    //else if( strtolower( $button_val ) == 'edit' )
    //    $buttonSym = $symbEdit;

    // Let JS add extra rows here.
    $html .= '<tr><td></td><td><table id="' . $tablename . '_extra_rows"> </table></td>';

    if( count( $editableKeys ) > 0 && strlen( $button_val ) > 0 )
    {
        $html .= "<tr style=\"background:white;\"><td></td><td>";
        $html .= "<button style=\"float:right\" value=\"$button_val\"
                 title=\"$button_val\" name=\"response\">" . $buttonSym . "</button>";
        $html .= "</td></tr>";
    }
    $html .= "</table>";
    return $html;
}

/**
    * @brief Deprecated: Convert an event to an editable table.
    *
    * @param $event
    * @param $editables
    *
    * @return
 */
function eventToEditableTableHTML( $event, $editables = Array( ) )
{
    $html = "<table class=\"request_show_edit\">";
    foreach( $event as $key => $value )
    {
        $editHTML = $value;
        if( in_array( $key, $editables ) )
        {
            $inType = "input";
            $props = "style=\"width:100%;\"";
            $text = "";
            if( $key == "description" )
            {
                $inType = "textarea";
                $props  = $props . " rows=\"4\"";
                $text = $value;
            }

            $editHTML = "<$inType $props name=\"$key\" value=\"$value\">$text</$inType>";
        }
        $html .= "<tr> <td>$key</td><td> $editHTML </td> </tr>";
    }
    $html .= "</table>";
    return $html;
}

/**
    * @brief Convert a array into select list.
    *
    * @param $name Name of the select list.
    * @param $options Options to populate.
    * @param $display Search fo text for each option here if not then prettify
    * the option and show to user.
    * @param $multiple_select If true then allow user to select multiple
    * entries.
    * @param $selected If not '' then select this one by default.
    *
    * @return HTML <select>
 */
function arrayToSelectList( $name, $options
        , $display = array()
        , $multiple_select = false
        , $selected = ''
    )
{
    $html = '';
    if( ! $multiple_select )
    {
        $html .= "<select class=\"$name\" name=\"$name\">";
        $html .= "<option selected value=\"\">-- Select one --</option>";
    }
    else
    {
        $html .= "<select class=\"$name\" multiple size=\"4\" name=\"$name\">";
        $html .= "<option selected disabled>-- Select multiple --</option>";
    }

    foreach( $options as $option )
    {
        $selectText = "";

        if( $option == $selected )
            $selectText = " selected";

        $html .= "<option value=\"$option\" $selectText >"
                 .  __get__( $display, $option, prettify( $option ) )
                 . "</option>";
    }

    $html .= "</select>";
    return $html;
}

/**
    * @brief Convert login/speaker to text. First name + Middle name + Last name format.
    *
    * @param $login
    * @param $withEmail
    *
    * @return A string of length.
 */
function loginToText( $login, $withEmail = true, $autofix = true )
{
    if( ! $login )
        return '';

    // If only login name is give, query database to get the array. Otherwise
    // assume that an array has been given to use.
    if( is_string( $login ) )
    {
        if( strlen( trim($login) ) < 1 )
            return '';
        $user = getUserInfo( $login );
    }
    else if( is_array( $login ) )
        $user = $login;
    else
        $user = $login;


    if( __get__( $user, 'first_name', '' ) == __get__( $user, 'last_name', ''))
    {
        $email = __get__( $user, 'email', '' );
        if( $email )
        {
            $ldap = getUserInfoFromLdap( $user[ 'email'] );
            if( $ldap )
                $user = array_merge( $user, $ldap );
        }
    }

    if( is_bool( $user ) )
        return $login;

    // Return first name + middle name + last name.
    $name = array( );
    foreach( explode( ',', 'first_name,middle_name,last_name' ) as $key )
    {
        if( array_key_exists( $key, $user ) )
            array_push( $name, $user[ $key ] );
    }

    if( is_array( $name ) )
        $text = implode( ' ', $name );

    if( $autofix )
        $text = fixName( $text );

    if( $withEmail )
        if( array_key_exists( 'email', $user) && $user[ 'email' ] )
            $text .= " (" . $user['email'] . ")";

    if( strlen( trim($text) ) < 1 )
        return $login;

    // If honorific exits in login/speaker; then prefix it.
    if( is_array( $user) && array_key_exists( 'honorific', $user ) )
        $text = trim( $user[ 'honorific' ] . ' ' . $text );

    return $text;
}

function loginToHTML( $login, $withEmail = true ) {
    // If only login name is give, query database to get the array. Otherwise
    // assume that an array has been given to use.
    if( is_string( $login ) )
        $user = getUserInfo( $login );
    else
        $user = $login;

    if( ! $user )
        return $login;

    // Return first name + middle name + last name.
    $name = array( );
    foreach( explode( ',', 'first_name,middle_name,last_name' ) as $key )
        if( array_key_exists( $key, $user ) )
            array_push( $name, $user[ $key ] );
    $text = implode( ' ', $name );

    if( $withEmail )
        if( array_key_exists( 'email', $user) && $user[ 'email' ] )
            $text = "<a href=\"mailto:" . $user['email'] . "\"> $text </a>";

    if( strlen( trim($text) ) < 1 )
        return $login;

    return fixName( $text );
}

/**
    * @brief Get link from intranet.
    *
    * @param User login.
    *
    * @return
 */
function getIntranetLink( $login )
{
    $html = "<font style=\"font-size:x-small\"><a
            href=\"https://intranet.ncbs.res.in/people-search?name=$login\"
            target=\"_blank\">Profile on Intranet</a></font>"
            ;
    return $html;
}

/**
    * @brief Return a AWS table which is editable by user. When $default array
    * is present, use it to construct the table. Else query the AWS table.
    * Passing array is useful when AWS is coming from some other table such as
    * upcoming_aws etc.
    *
    * @return  A editable table with submit button.
 */
function editableAWSTable( $awsId = -1,  $default = NULL )
{
    if( $awsId > 0 && ! $default )
        $default = getAwsById( $awsId );

    // Now create an entry
    $supervisors = getSupervisors( );
    $supervisorIds = Array( );
    $supervisorText = Array( );
    foreach( $supervisors as $supervisor )
    {
        array_push( $supervisorIds, $supervisor['email'] );
        $supervisorText[ $supervisor['email'] ] = $supervisor['first_name']
                .  ' ' . $supervisor[ 'last_name' ] ;
    }

    $html = "<table class=\"input\">";
    $text = sanitiesForTinyMCE( __get__( $default, 'abstract', '' ));
    $html .= '
             <tr>
             <td>Title</td>
             <td><input type="text" class="long" name="title" value="'
             . __get__( $default, 'title', '') . '" /></td>
             </tr>
             <tr>
             <td>Abstract </td>
             <td>
             <textarea class="editable" id="abstract" name="abstract">' .
             $text . '</textarea> ' .
             editor_script( 'abstract', $text ) .
             '</td> </tr>'
             ;

    for( $i = 1; $i <= 2; $i++ )
    {
        $name = "supervisor_$i";
        $selected = __get__( $default, $name, "" );
        $html .= '
                 <tr>
                 <td>Supervisor ' . $i . '<br></td>
                 <td>' . arrayToSelectList(
                     $name, $supervisorIds , $supervisorText
                     , FALSE, $selected
                 );

        $html .= '</td> </tr>';
    }
    for( $i = 1; $i <= 4; $i++ )
    {
        $name = "tcm_member_$i";
        $selected = __get__( $default, $name, "" );
        $html .= '
                 <tr>
                 <td>Thesis Committee Member ' . $i . '<br></td>
                 <td>' . arrayToSelectList( $name, $supervisorIds , $supervisorText, FALSE, $selected)
                 . '</td>';
        $html .= '</tr>';

    }
    $html .= '
             <tr>
             <td>Date</td>
             <td><input class="datepicker"  name="date" value="' .
             __get__($default, 'date', '' ) . '" readonly ></td>
             </tr>
             <tr>
             <td>Time</td>
             <td><input class="timepicker" name="time" value="16:00" readonly/></td>
             </tr>
             <tr>
             <td></td>
             <td>
             <input  name="awsid" type="hidden" value="' . $awsId . '"  />
             <button class="submit" name="response" value="submit">Submit</button>
             </td>
             </tr>
             ';
    $html .= "</table>";
    return $html;

}

/**
    * @brief Initialize user message.
    *
    * @param $user Login id of user.
    *
    * @return First part of the message.
 */
function initUserMsg( $user = null )
{
    if( ! $user )
        $user = $_SESSION[ 'user' ];

    $msg = "<p> Dear " . loginToText( $user ) . "<p>";
    return $msg;
}

function dataURI( $filepath, $mime )
{
    $contents = file_get_contents($filepath);
    $base64   = base64_encode($contents);
    return ('data:' . $mime . ';base64,' . $base64);
}

function __ucwords__( $text )
{
    return ucwords( strtolower( $text ) );
}

/**
    * @brief NOTE: Must not have any decoration. Used in sending emails.
    * Squirrel mail html2text may not work properly.
    *
    * @param $aws
    * @param $with_picture
    *
    * @return
 */
function awsToHTML( $aws, $with_picture = false )
{
    $speaker = __ucwords__( loginToText( $aws[ 'speaker' ] , false ));

    $supervisors = array( __ucwords__(
                              loginToText( findAnyoneWithEmail( $aws[ 'supervisor_1' ] ), false ))
                          ,  __ucwords__(
                              loginToText( findAnyoneWithEmail( $aws[ 'supervisor_2' ] ), false ))
                        );
    $supervisors = array_filter( $supervisors );

    $tcm = array( );
    array_push( $tcm, __ucwords__(
                    loginToText( findAnyoneWithEmail( $aws[ 'tcm_member_1' ] ), false ))
                , __ucwords__(
                    loginToText( findAnyoneWithEmail( $aws[ 'tcm_member_2' ] ), false ))
                ,  __ucwords__(
                    loginToText( findAnyoneWithEmail( $aws[ 'tcm_member_3' ] ), false ))
                , __ucwords__(
                    loginToText( findAnyoneWithEmail( $aws[ 'tcm_member_4' ] ), false ))
              );
    $tcm = array_filter( $tcm );

    $title = $aws[ 'title' ];
    if( strlen( $title ) == 0 )
        $title = "Not yet disclosed!";

    $abstract = $aws[ 'abstract' ];
    if( strlen( $abstract ) == 0 )
        $abstract = "Not yet disclosed!";

    $html = "<div class=\"show_aws\">";
    $html .= "<div width=600px><hr width=600px align=left> </div>";

    // Adding css inline screw up the email view. Dont do it.

    $user = $aws[ 'speaker' ];

    // Add a table only if there is a picture. Adding TD when there is no picture
    // screws up the formatting of emails.
    if( $with_picture )
    {
        $html .=  '<table class="email">';
        $html .= '<tr>';
        $imgHtml = getUserPicture( $user, 'hippo' );
        $html .= "<td float=\"left\"> <div> $imgHtml </div> </td>";
        $html .= "<td><h2>$speaker on '$title' </h2> </td>";
        $html .= "</tr>";
        $html .= "</table>";
    }
    else
        $html .= "<h2>$speaker on '$title' </h2>";


    $html .=  '<table class="email" style="width:500px">';
    $html .= ' <tr> <td>' . smallCaps( 'Supervisors') . '</td>
             <td>' . implode( ", ", $supervisors ) . '</td>
             </tr>
             <tr>
             <td>' . smallCaps( 'Thesis Committee Members') . '</td>
             <td>' . implode( ", ", $tcm) . '</td>
             </tr>
             </table>';

    $html .= "<br>";
    $html .= "$abstract";
    $html .= "</div>";
    return $html;

}

function speakerName( $speaker, $with_email = false )
{
    // NOTE: Do not use is_int here.
    if( is_numeric( $speaker ) )                        // Got an id.
        $speaker = getTableEntry( 'speakers', 'id'
                        , array( 'id' => $speaker )
                    );

    $name = __get__( $speaker, 'honorific', '' );
    if( $name )
        $name .= ' ';

    $name .= $speaker[ 'first_name' ];

    if( __get__( $speaker, 'middle_name', '' ) )
        $name .= ' ' . $speaker[ 'middle_name' ];

    $name .= ' ' . $speaker[ 'last_name' ];

    if( $with_email )
    {
        $email = __get__( $speaker, 'email', '' );
        if( $email )
            $name .= " <$email>";
    }
    return $name;
}

function arrayToName( $arr, $with_email = false )
{
    return speakerName( $arr, $with_email );
}


/**
    * @brief Convert an event entry to HTML.
    *
    * @param $talk Talk/event entry.
    * @param $with_picture Fetch entry with picture.
    *
    * @return
 */
function talkToHTML( $talk, $with_picture = false )
{

    $speakerId = intval( $talk[ 'speaker_id' ] );

    // If speaker id is > 0, then use it to fetch the entry. If not use the
    // speaker name. There was a design problem in the begining, some speakers
    // do not have unique id but only email. This has to be fixed.
    if( $speakerId > 0 )
    {
        $speakerArr = getTableEntry( 'speakers', 'id', array( 'id' => $speakerId ) );
        $speakerName = speakerName( $speakerId );
    }
    else
    {
        $speakerArr = getSpeakerByName( $talk[ 'speaker' ] );
        $speakerName = speakerName( $speakerArr );
    }


    $hostEmail = $talk[ 'host' ];

    // Either NCBS or InSTEM.
    $hostInstitite = emailInstitute( $hostEmail );

    // Get its events for venue and date.
    $event = getEventsOfTalkId( $talk[ 'id' ] );

    $where = venueSummary( $event[ 'venue' ] );
    $when = humanReadableDate( $event[ 'date' ] ) . ', ' .
            humanReadableTime( $event[ 'start_time'] );

    $title = '(' . __ucwords__($talk[ 'class' ]) . ') ' . $talk[ 'title' ];

    $html = '<div style="width:550px;text-align:justify">';
    $html .= '<table border="0"><tr>';
    $html .= '<td colspan="2"><h1>' . $title . '</h1></td>';
    $html .= "</tr><tr>";

    if( $with_picture )
    {
        $imgpath = getSpeakerPicturePath( $speakerId );
        $html .= '<td>' . showImage( $imgpath, 'auto', '200px' ) . '</td>';
    }

    // Speaker info
    if( $speakerId > 0 )
        $speakerHMTL = speakerIdToHTML( $speakerId );
    else
        $speakerHMTL = speakerToHTML( $speakerArr );

    $html .= '<td> <br>' . $speakerHMTL ;

    // Hack: If talk is a THESIS SEMINAR then host is thesis advisor.
    if( $talk['class'] == 'THESIS SEMINAR' )
        $html .= '<br><br> Supervisor: ' . loginToHTML( $talk[ 'host' ] );
    else
        $html .= '<br><br> Host: ' . loginToHTML( $talk[ 'host' ] );

    $html .= '<br><br>';
    $html .= '<div style="font-variant:small-caps;text-decoration:none;">';
    $html .= '<table><tr>
                <td class="when"> <i class="fa fa-clock-o fs-spin"></i>' . $when . '</td>
            </tr><tr>
                <td class="where"> <i class="fa fa-thumb-tack fs-spin fa-fw"></i>' . $where . '</td>
            </tr><tr>
                <td>Coordinator: ' . loginToText( $talk[ 'coordinator' ] ) . '</td>';
    $html .= '</tr>';


    // Add links to google,ical.
    $html .= '<tr>';
    $html .=  '<td>';
    $html .= '<a target="_blank" href="' . appURL( ) .'events.php?date='
                 . $event[ 'date' ] . '">Permanent link</a>';

    $googleCalLink = addToGoogleCalLink( $event );
    $icalLink = eventToICALLink( $event );

    $html .= "</td>";
    $html .= '</tr>';

    $html .= '</table>';
    $html .= '</div>';
    $html .= '</td>';
    $html .= '</tr></table>';

    $html .= "<p>" . fixHTML( $talk[ 'description' ] ) . '</p>';

    $html .= "</div>";

    // Add the calendar links
    $html .= "<br><br>";

    $html .=  "<div class=\"strip_from_md\">
        <table><tr>
        <td> $googleCalLink </td>
        <td> $icalLink </td>
        </tr></table>
        </div>";


    return $html;
}

function printableCharsOnly( $html )
{
    return preg_replace('/[\x00-\x1F\x7F]/u', '', $html );
}

function closePage( )
{
    return "<div><a href=\"javascript:window.close();\">Close Window</a></div>";
}

function awsPdfURL( $speaker, $date, $msg = 'Download PDF' )
{
    $get = "date=$date";
    if( $speaker )
        $get .= "&speaker=$speaker";

    // Link to pdf file.
    $url = '<div><a target="_blank" href="generate_pdf_aws.php?' .
           $get . '">' . $msg . '</a></div>';

    return $url;
}

/**
    * @brief Download text file of given name. This file must exists in data
    * folder.
    *
    * @param $filename
    * @param $msg
    *
    * @return
 */
function downloadTextFile( $filepath, $msg = 'Download file', $class = 'download_link' )
{
    $url = '<a class="' . $class . '" target="_blank" href="download_file.php?filename='
           . $filepath .  '">' . $msg .'</a>';
    return $url;
}


/**
    * @brief Generate a two column table for user to fill-in.
    *
    * @return
 */
// <td>Repeat pattern for recurrent events <br> (optional) <br>
//     <p class="note_to_user"> Valid for maximum of 6 months </p>
//     </td>
function repeatPatternTable( $className )
{
    $html = '<h4>RECURRENT EVENTS (optional)</h4>';

    $html .= "<p style=\"color:blue\">Some examples of recurrent events.</p>";

    $html .= "<div style=\"font-size:small\">";
    $html .= '<table class="' . $className . '">';
    $html .= '<tr><td> Every saturday, every week
             , for 3 months  </td>';
    $html .= '<td>
             <input disabled value="Sat">
             </td>
             <td>
             <input disabled value="">
             </td>
             <td>
             <input disabled value="3">
             </td>
             </tr>';
    $html .= '<tr><td> Every monday and thursday, every week
             , for 5 months  </td>';
    $html .= '<td>
             <input disabled value="Mon,Thu">
             </td>
             <td>
             <input disabled value="">
             </td>
             <td>
             <input disabled value="5">
             </td>
             </tr>';
    $html .= '<tr><td> Every Tuesday, first and third week
             , for 4 months </td>';
    $html .= '<td>
             <input disabled value="Tue">
             </td>
             <td>
             <input disabled value="first,third">
             </td>
             <td>
             <input disabled value="4">
             </td>
             </tr>';

    $html .= '</table>';
    $html .= "</div>";

    $html .= "<br>";
    $html .= '<table class="' . $className . '">';
    $html .= ' <tr>
             <td> <p style="color:blue">Your recurrent pattern here </p></td>
             <td> <input type="text" name="day_pattern" / > </td>
             <td> <input type="text" name="week_pattern" /></td>
             <td><input type="text" name="month_pattern" placeholder="6" /></td>
             </tr>';
    $html .= "</table>";
    return $html;
}

/**
    * @brief Generate a email statement form given template id. Templte must
    * exits in a database table.
    *
    * @param $templateName
    * @param $options
    *
    * @return
 */
function emailFromTemplate( $templateName, $options )
{
    $templ = getEmailTemplateById( $templateName );
    $desc = $templ['description'];

    if( ! $desc )
    {
        echo alertUser( "No template found with id: aws_template. I won't
                        be able to generate email"
                      );
        return null;
    }

    foreach( $options as $key => $value )
    {
        $desc = str_replace( "@$key@", $value, $desc );
    }

    $templ[ 'email_body' ] = $desc;
    return $templ;
}


function googleCaledarURL( )
{

    $url = "https://calendar.google.com/calendar/embed?";
    $url .= "src=d2jud2r7bsj0i820k0f6j702qo%40group.calendar.google.com";
    $url .= "&ctz=Asia/Calcutta";
    return $url;
}

function inlineImage( $picpath, $class = 'inline_image', $height = 'auto', $width = 'auto' )
{
    if( ! file_exists( $picpath ) )
        $picpath = nullPicPath( );

    $html = '<img class="'.$class . '" width="' . $width
            . '" height="' . $height . '" src="'
            . dataURI( $picpath, 'image/jpg' ) . '" >';
    return $html;
}

function showImage( $picpath, $height = 'auto', $width = 'auto' )
{
    return inlineImage( $picpath, $class = 'login_picture', $height, $width );
}



/**
    * @brief Display any image.
    *
    * @param $picpath
    * @param $height
    * @param $width
    *
    * @return
 */
function displayImage( $picpath, $height = 'auto', $width = 'auto', $usemap = '' )
{
    if( ! file_exists( $picpath ) )
        $picpath = nullPicPath( );

    $html = '<img width="' . $width . '" height="' . $height . '" src="'
            . dataURI( $picpath, 'image/jpg' ) . '" usemap="#' . $usemap . '"  >';
    return $html;
}


/**
    * @brief Return an empty image.
    *
    * @return
 */
function nullPicPath( $default = 'null' )
{
    $conf = getConf( );
    return $conf['data']['user_imagedir'] . '/' . $default . '.jpg' ;
}

function inlineImageOfSpeakerId( $id, $height = 'auto', $width = 'auto')
{
    $picPath = getSpeakerPicturePathById( $id );
    return showImage( $picPath, $height, $width );
}

function inlineImageOfSpeaker( $speaker, $height = 'auto', $width = 'auto')
{
    $picPath = getSpeakerPicturePath( $speaker );
    return showImage( $picPath, $height, $width );
}

/**
    * @brief Convert slots to a HTML table.
    *
    * @return
 */
function slotTable( $width = "15px" )
{

    $days = array( 'Mon', 'Tue', 'Wed', 'Thu', 'Fri' );
    $html = '<table class="timetable">';

    // Generate columns. Each one is 15 min long. Starting from 9am to 6:00pm
    $maxCols = intval( ( 17.5 - 9 ) * 4 );

    //for ($i = 0; $i < $maxCols; $i++)
    //    $html .= '<th> </th>';


    // Check which slot is here.
    $slots = getTableEntries('slots' );
    // each day is row.
    foreach( $days as $day )
    {
        $html .= "<tr>";
        $html .= "<tr> <td>$day</td> ";

        for ($i = 0; $i < $maxCols; $i++)
        {
            $slotTime = dbTime( strtotime( '9:00 am' . ' +' . ( $i * 15 ) . ' minute' ) );
            $slot = getSlotAtThisTime( $day, $slotTime, $slots );

            if( $slot )
            {
                $duration = strtotime( $slot[ 'end_time' ] )  -
                            strtotime( $slot[ 'start_time' ] );

                $text = humanReadableTime( $slot[ 'start_time' ] ) . ' - ' .
                        humanReadableTime(  $slot[ 'end_time' ] );

                $bgColor = 'lightblue';
                $id = $slot[ 'id' ];
                $gid = $slot[ 'groupid' ];

                if( ! is_numeric( $id[0] ) )
                    $bgColor = 'red';


                $ncols = intval( $duration / (60 * 15) ); // Each column is 15 minutes.

                $html .= "<td id=\"slot_$id\" style=\"background:$bgColor\" colspan=\"$ncols\">
                         <button onClick=\"showRunningCourse(this)\"
                          id=\"slot_$gid\" value=\"$id\" class=\"invisible\"> $id </button>
                         <br> <small> <tt>$text</tt> </small> </td>";

                // Increase $i by ncols - 1. 1 is increased by loop.
                if( $ncols > 1 )
                    $i += $ncols - 1;
            }
            else
                $html .= "<td> </td>";
        }
        $html .= "</tr>";
    }

    $html .= '</table>';

    return $html;

}

function coursesTable( $editable = false )
{
    $courses = getTableEntries( 'courses_metadata' );
    $html = '<div style="font-size:small">';
    $html .= '<table class="show_aws">';
    foreach( $courses as $c )
    {
        $instructors = array( );
        foreach( $c as $k => $v )
        if( $v && strpos( $k, 'instructor_') !== false )
            $instructors = array_merge($instructors, explode( ',', $v ) );

        $html .= "<tr>";
        $html .= "<td>" . $c[ 'id' ] . "</td>";
        $html .= "<td>" . $c[ 'credits' ] . "</td>";
        $html .= "<td>" . $c[ 'name' ] . "</td>";
        $html .= "<td>" . $c[ 'description' ] . "</td>";
        $html .= "<td>" . implode('<br>', $instructors) . "</td>";
        if( $editable )
        {
            $html .= '<td> <button name="response" value="Edit">Edit</button> </td>';
            $html .= '<input type="hidden" name="id" value="' . $c['id'] . '">';
        }

        $html .= "</tr>";

    }
    $html .= '</table>';
    $html .= '</div>';
    return $html;
}

/**
    * @brief Create a select list with default value selected.
    *
    * @param $default
    *
    * @return
 */
function gradeSelect( $name, $default = 'X' )
{
    if( strlen( $default ) == 0 )
        $default = 'X';

    $select = arrayToSelectList(
            $name
            , array( 'A+', 'A', 'B+', 'B', 'C+', 'C', 'F', 'X' )
            , array( ), false, $default
        );
    return $select;
}

function talkSummaryLine( $talk )
{

    $title = __ucwords__( $talk[ 'title' ] );
    $msg = __ucwords__ ( $talk['class'] ) . ' by ';
    $msg .= $talk[ 'speaker' ];
    $msg .= " : '$title'";
    return $msg;
}

function preferenceToHtml( $request )
{

    $html = '<div class="aws_preference"><br />User Preferences: <br />';
    $prefs = array( );
    if( $request[ 'first_preference' ] )
        $prefs[ ] =  humanReadableDate( $request[ 'first_preference' ] );
    if( $request[ 'second_preference' ] )
        $prefs[ ] = humanReadableDate( $request[ 'second_preference' ] );

    $html .= implode( ' <br /> ', $prefs );
    $html .= '</div>';
    return $html;
}

/**
    * @brief Reutrn colored text.
    *
    * @param $txt
    * @param $color
    *
    * @return
 */
function colored( $txt, $color = 'black' )
{
    return "<font color=\"$color\"> $txt </font>";

}


function getCourseShortInfoText( $course )
{
    if( is_string( $course ) )
        $course = getCourseById( $course );

    $text = $course[ 'name' ];

    return $text;
}

function getCourseInstructors( $c )
{
    if( is_string( $c ) )
        $c =  getCourseById( $c );

    $instructors = array( );
    foreach( $c as $k => $v )
    {
        if( contains( 'instructor_', $k ) )
            foreach( explode( ",", $v) as $i )
            {
                if( $i )
                {
                    $name = arrayToName( findAnyoneWithEmail( $i ) );
                    $instructors[ ] = "<small><a id=\"emaillink\" href=\"mailto:$v\" target=\"_top\">
                        $name </a></small>";
                }
            }
    }

    $instructors = implode( '<br>', $instructors );
    return $instructors;

}

function getCourseInfo( $cid )
{
    $c =  getCourseById( $cid );
    $instructors = getCourseInstructors( $c );
    return $html . '<br>' . $instructors;
}


function bookmyVenueAdminTaskTable( )
{
    $html = '<table class="tasks">
        <tr>
        <td>
            Book using old interface
        </td>
        <td>
            <a href="bookmyvenue_browse.php">OLD BOOKING INTERFACE</a>
        </td>
        </tr>
        </table>'
    ;

    $html .= '<br>';
    $html .= '<table class="tasks">
        <tr>
        <td>
           <strong>Make sure you are logged-in using correct google account </strong>
            </strong>
        </td>
            <td>
                <a href="bookmyvenue_admin_synchronize_events_with_google_calendar.php">
                Synchronize public calendar </a>
            </td>
        </tr>
        <tr>
        <td>
            Add/Update/Delete venues
        </td>
            <td>
                <a href="bookmyvenue_admin_manages_venues.php"> Manage venues </a>
            </td>
        </tr>
        <tr>
            <td>Send emails manually (and generate documents)</td>
            <td> <a href="admin_acad_email_and_docs.php">Send emails</td>
        </tr>
        <tr>
            <td>Manage talks and seminars. </td>
            <td> <a href="admin_acad_manages_talks.php">Manage talks/seminar</td>
        </tr>
        <tr>
            <td>Add or update speakers. </td>
            <td> <a href="admin_acad_manages_speakers.php">Manage speakers</td>
        </tr>
        </table>' ;
    return $html;
}

function smallCaps( $text )
{
    return "<font style=\"font-variant:small-caps\"> $text </font>";
}


/* --------------------------------------------------------------------------*/
/**
    * @Synopsis  Course to HTML row.
    *
    * @Param $c
    *
    * @Returns
 */
/* ----------------------------------------------------------------------------*/
function courseToHTMLRow( $c, $slot, $sem, $year, &$enrollments )
{
    $cid = $c[ 'id' ];

    $whereExpr = "year='$year' AND semester='$sem' AND course_id='$cid'
                AND type!='DROPPED'";
    $registrations = getTableEntries(
        'course_registration', 'student_id', $whereExpr
    );

    $enrollments[ $cid ] = $registrations;

    $cinfo = $c[ 'description' ];
    $cname = $c[ 'name' ];
    $cr = $c[ 'credits' ];

    $note = '';
    if( $c[ 'note' ] )
        $note = colored( '* ' . $c[ 'note' ], 'blue' );

    $cinfo = "<p><strong>Credits: $cr </strong></p>" . $cinfo;

    $schedule = humanReadableDate( $c[ 'start_date' ] ) . ' - '
        . humanReadableDate( $c[ 'end_date' ] );

    $slotInfo = getCourseSlotTiles( $c, $slot );
    $instructors = getCourseInstructors( $cid );

    $row = '<tr>
        <td> <button id="$cid" onclick="showCourseInfo(this)" class="courseInfo"
        value="' . $cinfo . '" title="' . $cname . '" >' . $cname . '</button><br>'
        . $instructors . '</td>
        <td>' .  $schedule . '</td>
        <td>' . "<strong> $slotInfo </strong> <br>"
        .  '<strong>' . $note . '</strong></td><td>'
        .  $c[ 'venue' ] . '</td>
        <td>' . count( $registrations ) . '</td>'
        ;

    // If url is found, put it in page.
    if( $c['url'] )
        $row .= '<td><a target="_blank" href="' . $c['url']
        . '">Course page</a></td>';
    else
        $row .= '<td></td>';

    return $row;
}


function mailto( $email, $text = '' )
{
    if( ! $text )
        $text = $email;

    $html = "<a href=\"mailto:" . $email . "\"> $text </a>";
    return $html;
}

function piSpecializationHTML( $pi, $specialization )
{
    return "<br><small><tt> $specialization <br>PI: $pi</tt></small>";
}

function goBackToPageLink( $url, $title = "Go back" )
{

    $html = "<div class=\"goback\">";
    $html .= "<a style=\"float: left\" href=\"$url\">
            <i class=\"fa fa-step-backward fa-3x\"></i>
            <font color=\"blue\" size=\"5\">$title</font>
        </a></div><br/><br/>";
    return $html;
}

/**
    * @brief Go back to referer page.
    *
    * @param $defaultPage
    *
    * @return
 */
function goBack( $default = '', $delay = 0 )
{
    if( ! $default )
        $url = __get__( $_SERVER, 'HTTP_REFERER', 'index.php' );
    else
        $url = $default;

    goToPage( $url, $delay );
}

function presentationToHTML( $presentation )
{
    $html = __get__( $presentation, 'description', '' );
    if( ! trim($html) )
        $html .= '<p>Not disclosed yet</p>';

    if( $presentation[ 'url' ] )
    {
        $html .= ' <br /> ';
        $html .= '<p>More information/resources may be available at <a href="' . $presentation[ 'url' ] . '">
                    this link</a>';
    }
    return $html;
}

function getPresentationTitle( $presentation )
{
    $title = __get__( $presentation, 'title', '' );
    if( ! $title )
        $title = 'Not disclosed yet';

    return $title;
}


?>
