<?php

include_once 'header.php';
include_once 'methods.php';
include_once 'database.php';

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

/**
    * @brief Generate SPEAKER HTML with homepage and link.
    *
    * @param $speaker
    *
    * @return 
 */
function speakerToHTML( $speaker )
{
    if( is_string( $speaker ) )
    {
        $speaker = explode( ' ', $speaker );
        $fname = $speaker[0];
        $lname = end( $speaker );
        $speaker[ 'first_name' ] = $fname;
        $speaker[ 'last_name' ] = $lname;
        $speaker = getTableEntry( 'speakers', 'first_name,last_name', $speaker );
    }

    // Get name of the speaker.
    $name = array( );
    foreach( explode( ',', 'first_name,middle_name,last_name' ) as $k )
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
    $table .= '<tr><td><small>NCBS/InSTEM Username </td></tr> ';
    $table .= '<tr><td><input type="text" name="username" id="username" /> </td></tr>';
    $table .= '<tr><td><small>Password</small></td></tr>';
    $table .= '<tr><td> <input type="password"  name="pass" id="pass"> </td></tr>';
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

function eventSummaryHTML( $event, $talk = null)
{
    $date = humanReadableDate( $event[ 'date' ] );
    $startT = humanReadableTime( $event[ 'start_time' ] );
    $endT = humanReadableTime( $event[ 'end_time' ] );
    $time = "$startT to $endT";
    $venue = venueSummary( $event[ 'venue'] );

    $html = "<h1>" . $event[ 'title' ] . "</h1>";
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
            $html .= "<form method=\"post\" action=\"user_submit_request.php\" >";
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
function arrayToRowHTML( $array, $tablename, $tobefilterd = '' )
{
    $row = '<tr>';
    if( is_string( $tobefilterd ) )
        $tobefilterd = explode( ',', $tobefilterd );

    $keys = array_keys( $array );
    $toDisplay = Array();
    foreach( $keys as $k )
        if( ! in_array( $k, $tobefilterd ) )
            array_push( $toDisplay, $array[$k] );

    foreach( $toDisplay as $v )
    {
        if( isStringAValidDate( $v ) )
            $v = humanReadableDate( $v );

        $row .= "<td><div class=\"cell_content\">$v</div></td>";
    }

    $row  .= "</tr>";
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
function arrayHeaderRow( $array, $tablename, $tobefilterd )
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
            $hrow .= "<th class=\"db_table_fieldname\">$label</th>";
        }

    return $hrow;
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
            $table .= "<td><div class=\"cell_content\">$array[$k]</div></td>";
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

function userHTML( )
{
    $html = "<table class=\"user_float\">";
    $html .= "<tr colspan=\"2\"><th>Hi " . $_SESSION['user'] . "</th></tr>";
    $html .= "<tr><td><a href=\"quickbook.php\">QuickBook</a>";
    $html .= '</td><td><a href="user_aws.php">MyAWS</a></td>';
    $html .= "</tr><tr>";
    $html .= "<td><a href=\"user.php\">My Home</a>";
    $html .= '</td><td><a href="logout.php">Logout</a></td>';
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

    return $venue['name'] . ' ' . $venue['building_name'] . ', ' . $venue['location'];
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
function dbTableToHTMLTable( $tablename
                             , $defaults=Array(), $editables = ''
                                     , $button_val = 'submit', $hide = ''
                           )
{
    global $symbUpdate, $symbCheck;
    global $symbEdit;
    global $dbChoices;
    global $useCKEditor;

    $html = "<table class=\"editable_$tablename\">";
    $schema = getTableSchema( $tablename );

    if( is_string( $editables ) )
        $editables = explode( ",", $editables );
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

        // If not in editables list, make field readonly.
        $readonly = True;
        if( in_array($keyName , $editables ) )
            $readonly = False;

        // Add row to table
        $html .= "<tr><td class=\"db_table_fieldname\"> " .
                 strtoupper(prettify( $keyName )) . "</td>";

        $default = __get__( $defaults, $keyName, $col['Default'] );

        $inputId = $tablename . "_" . $keyName;

        // DIRTY HACK: If value is already a html entity then don't use a input
        // tag. Currently only '<select></select> is supported
        if( preg_match( '#<select.*?>(.*?)</select>#', $default ) )
            $val = $default;
        else
            $val = "<input class=\"editable\"
                   name=\"$keyName\" type=\"text\" value=\"$default\" id=\"$inputId\"
                   />";

        // Genearte a select list of ENUM type class.
        $match = Array( );
        if( preg_match( '/^varchar\((.*)\)$/', $ctype ) )
        {
            $classKey = $tablename . '.' . $keyName;
            if( array_key_exists( $classKey, $dbChoices ) )
            {
                $val = "<select name=\"$keyName\">";
                foreach( explode( ',', $dbChoices[ $classKey ] ) as $v )
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
                $val .= "<script>
                        tinymce.init( { selector : '#" . $inputId . "'
                        , init_instance_callback: \"insert_content\"
                        , plugins : [ 'image imagetools link paste code wordcount fullscreen table' ]
                        , paste_as_text : true
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
            }
        }
        else if( strcasecmp( $ctype, 'date' ) == 0 )
            $val = "<input class=\"datepicker\" name=\"$keyName\" value=\"$default\" />";
        else if( strcasecmp( $ctype, 'datetime' ) == 0 )
            $val = "<input class=\"datetimepicker\" name=\"$keyName\" value=\"$default\" />";
        else if( strcasecmp( $ctype, 'time' ) == 0 )
            $val = "<input class=\"timepicker\" name=\"$keyName\" value=\"$default\" />";

        // When the value is readonly. Just send the value as hidden input and
        // display the default value.
        if( $readonly )
            $val = "<input type=\"hidden\" name=\"$keyName\" value=\"$default\"/>$default";


        $html .= "<td>" . $val . "</td>";
        $html .= "</tr>";
    }

    // If some fields are editable then we need a submit button as well unless
    // user pass an empty value
    $buttonSym = ucfirst( $button_val );

    if( strtolower($button_val) == 'submit' )
        $buttonSym = "&#10003";
    else if( strtolower( $button_val ) == 'update' )
        $buttonSym = $symbUpdate;
    else if( strtolower( $button_val ) == 'edit' )
        $buttonSym = $symbEdit;

    if( count( $editables ) > 0 && strlen( $button_val ) > 0 )
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
        , $multiple_select = FALSE
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
function loginToText( $login, $withEmail = true )
{
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
            $text .= " (" . $user['email'] . ")";

    if( strlen( trim($text) ) < 1 )
        return $login;

    // If honorific exits in login/speaker; then prefix it.
    if( is_array( $user) && array_key_exists( 'honorific', $user ) )
        $text = trim( $user[ 'honorific' ] . ' ' . $text );

    return $text;
}

function loginToHTML( $login, $withEmail = true )
{
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

    return $text;
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
            target=\"_blank\">Show on intranet</a></font>"
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
             $text . '</textarea>
             <script>
             tinymce.init( { selector : "#abstract"
                     , height : 300
                     , theme : "modern"
                     , plugins : [ "paste wordcount fullscreen table textcolor"
                     , "imagetools toc code" ]
                     , init_instance_callback: "insert_content"
                     , paste_as_text : true
                 } );
             function insert_content( inst ) {
             inst.setContent( \'' . $text . '\');
         }
             </script>
             </td>
             </tr>';

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

    $html = "<div style=\"width:500px\">";

    // Adding css inline screw up the email view. Dont do it.

    if( $with_picture )
    {
        $html .=  '<table class="email">';
        $html .= '<tr><td style="width:500px"></td>';
        $user = $aws[ 'speaker' ];
        $imgHtml = getUserPicture( $user );
        $html .= "<td float=\"right\"> <div> $imgHtml </div>";
        $html .= "</td></tr>";
        $html .= "</table>";
    }

    $html .= "<h3>\"$title\" by $speaker </h2>";

    $html .=  '<table class="email" style="width:500px;border:1px dotted">';
    $html .= '
             <tr>
             <td>Supervisors</td>
             <td>' . implode( ", ", $supervisors ) . '</td>
             </tr>
             <tr>
             <td>Thesis Committee Members</td>
             <td>' . implode( ", ", $tcm) . '</td>
             </tr>
             </table>';

    $html .= "<br>";
    $html .= "$abstract";
    $html .= "</div>";
    return $html;

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

    $speaker = $talk[ 'speaker' ] ;
    $hostEmail = $talk[ 'host' ];

    // Either NCBS or InSTEM.
    $hostInstitite = emailInstitute( $hostEmail );

    // Get its events for venue and date.
    $event = getEventsOfTalkId( $talk[ 'id' ] );

    $where = venueSummary( $event[ 'venue' ] );
    $when = humanReadableDate( $event[ 'date' ] ) . ', ' .
            humanReadableTime( $event[ 'start_time'] );

    $title = __ucwords__($talk[ 'class' ]) . ' by ' . $talk[ 'speaker' ] . " on '"
             . $talk[ 'title' ] . "'";

    $html = '<div style="width:550px;text-align:justify">';
    $html .= '<table border="0"><tr>';
    //$html .= '<th colspan="2"><font size="5">' . $talk[ 'title' ] . '</font></th>';
    $html .= '<td colspan="2"><h1>' . $talk[ 'title' ] . '</h1></td>';
    $html .= "</tr><tr>";

    if( $with_picture )
    {
        $imgpath = getSpeakerPicturePath( $speaker );
        $html .= '<td>' . showImage( $imgpath, 'auto', '200px' ) . '</td>';
    }

    $html .= '<td> <br>' . speakerToHTML( $talk['speaker'] );

    // Hack: If talk is a THESIS SEMINAR then host is thesis advisor.
    if( $talk['class'] == 'THESIS SEMINAR' )
        $html .= '<br><br> Supervisor: ' . loginToHTML( $talk[ 'host' ] );
    else
        $html .= '<br><br> Host: ' . loginToHTML( $talk[ 'host' ] );

    $html .= '<br><div style="font-size:small">';
    $html .= '<table><tr><td>' . $when . '</td></tr><tr><td>' . $where
             . '</td></tr><tr><td>Coordinator: ' . loginToText( $talk[ 'coordinator' ] );
    $html .= '</td></tr><tr><td>';
    $html .= '<a target="_blank" href="' . appURL( ) .'events.php?date='
                 . $event[ 'date' ] . '">Permanent link</a>';
    $html .= '</td></tr></table>';
    $html .= '</div>';
    $html .= '</td>';
    $html .= '</tr></table>';

    $html .= "<p>" . fixHTML( $talk[ 'description' ] ) . '</p>';

    $html .= "</div>";

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
function downloadTextFile( $filename, $msg = 'Download file' )
{
    //if( strpos( '/data/', $filename ) !== false )
    //$filename = basename( $filename );

    //if( ! file_exists( getDataDir( ) ."/$filename" ) )
    //$msg = "File doesn't exists";

    $url = '<div><a target="_blank" href="download_file.php?filename='
           . $filename .  '">' . $msg .'</a></div>';
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
        return '';
    }

    foreach( $options as $key => $value )
    $desc = str_replace( "@$key@", $value, $desc );

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

function showImage( $picpath, $height = 'auto', $width = 'auto' )
{
    if( ! file_exists( $picpath ) )
        $picpath = nullPicPath( );

    $html = '<img class="login_picture" width="' . $width
            . '" height="' . $height . '" src="'
            . dataURI( $picpath, 'image/jpg' ) . '" >';
    return $html;
}

/**
    * @brief Return an empty image.
    *
    * @return
 */
function nullPicPath( )
{
    $conf = getConf( );
    return $conf['data']['user_imagedir'] . '/hippo.jpg';
}

function inlineImageOfSpeaker( $speaker, $height = 'auto', $width = 'auto')
{
    $picName = str_replace( ' ', '', $speaker );
    $conf = getConf( );
    $picPath = $conf['data']['user_imagedir'] . '/' . $picName . '.jpg';
    $conf = getConf( );
    if( ! file_exists( $picPath ) )
        $picPath = $conf['data']['user_imagedir'] . '/hippo.jpg';

    return showImage( $picPath, $height, $width );
}

function getSlotAtThisTime( $day, $slot_time, $slots = null )
{
    if( ! $slots )
        $slots = getTableEntries( 'slots' );

    $slot = null;
    foreach( $slots as $s )
    {
        if( strcasecmp( $s[ 'day' ], $day ) == 0 )
        {
            if( dbTime( $s[ 'start_time' ]) == $slot_time )
                return $s;
        }
    }

    return $slot;
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
    $maxCols = ( 18 - 9 ) * 4;

    $html .= "<tr>";
    for ($i = -1; $i < $maxCols; $i++)
        $html .= "<th width=\" " . $width . "\"></th>";
    $html .= "</tr>";

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
                $id = $slot[ 'id' ];
                $bgColor = 'lightblue';

                if( ! is_numeric( $id[0] ) )
                    $bgColor = 'red';

                $ncols = intval( $duration / (60 * 15) ); // Each column is 15 minutes.
                $html .= "<td style=\"background:$bgColor\" colspan=\"$ncols\">
                         $id <br> <small> <tt>$text</tt> </small> </td>";

                // Increase $i by ncols - 1. 1 is increased by loop.
                if( $ncols > 1 )
                    $i += $ncols - 1;
            }
            else
                $html .= "<td></td>";
        }
        $html .= "</tr>";
    }

    $html .= '</table>';

    return $html;

}

function coursesTable( )
{
    $courses = getTableEntries( 'courses_metadata' );
    $html = '<table class="show_aws">';
    foreach( $courses as $c )
    {
        $instructors = array( );
        foreach( $c as $k => $v )
        if( $v && strpos( $k, 'instructor_') !== false )
            $instructors[] = $v;

        $html .= "<tr>";
        $html .= "<td>" . $c[ 'id' ] . "</td>";
        $html .= "<td>" . $c[ 'credits' ] . "</td>";
        $html .= "<td>" . $c[ 'name' ] . "</td>";
        $html .= "<td>" . implode('<br>', $instructors) . "</td>";
        $html .= "</tr>";

    }
    $html .= '</table>';
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
?>
