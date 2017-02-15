<?php 

include_once 'methods.php';
include_once 'database.php';
include_once 'tohtml.php';

?>

<script>
function displayEvent( button ) {
    alert( button.value );
};
function displayRequest( button ) {
    alert( button.value );
};
</script>

<?php
function loginForm()
{
  $conf = $_SESSION['conf'];
  /* Check if ldap server is alive. */
  $table = "";
  $table .= '<form action="login.php" method="post">';
  $table .= '<table class="login_main">';
     $table .= '<tr><td><small>NCBS/InStem Username</small> </td></tr> ';
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
    $text = preg_replace( "/\r\n|\r|\n/", "<br/>", $text );
    $text = str_replace( "'", "\'", $text );
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
    $html .= $event['start_time'] . ' to ' . $event['end_time'];
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
    $html = 'By ' . $req['user'] . ', ';
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
    $html = '<table class="eventline">';
    $startDay = $start;
    $dt = 60; 
    $html .= "<tr>";
    $html .= "<td><div style=\"width:100px\">$venueid</div></td>";
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


// Convert an array to HTML
function arrayToTableHTML( $array, $tablename, $background = ''
    , $tobefilterd = Array() )
{
    if( $background )
        $background = "style=\"background:$background;\"";

    if( is_string( $tobefilterd ) )
        $tobefilterd = explode( ',', $tobefilterd );
    
    $table = "<table class=\"show_$tablename\" $background>";
    $keys = array_keys( $array );
    $toDisplay = Array();
    $table .= "<tr>";
    foreach( $keys as $k )
        if( ! in_array( $k, $tobefilterd ) )
        {
            $kval = prettify( $k );
            $label = strtoupper( $kval );
            $table .= "<th class=\"db_table_fieldname\">$label</th>";

            array_push( $toDisplay, $array[$k] );
        }

    $table .= "</tr><tr>";
    foreach( $toDisplay as $v )
    {
        if( isStringAValidDate( $v ) )
            $v = humanReadableDate( $v );

        $table .= "<td><div class=\"cell_content\">$v</div></td>";
    }

    $table .= "</tr></table>";
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
    $html = "<table class=\"editable_$tablename\">";
    $schema = getTableSchema( $tablename );

    if( is_string( $editables ) )
        $editables = explode( ",", $editables );
    if( is_string( $hide ) )
        $hide = explode( ",", $hide );

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
        $val = "<input class=\"editable\"
            name=\"$keyName\" type=\"text\" value=\"$default\" id=\"$inputId\"
            />";

        // Genearte a select list of ENUM type class.
        $match = Array( );
        if( preg_match( "/^enum\((.*)\)$/" , $ctype, $match ) )
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
        else if( strcasecmp( $ctype, 'text' ) == 0 )
        {
            // NOTE: name and id should be same of ckeditor to work properly.
            // Sometimes we have two fileds with same name in two tables, thats 
            // a sticky situation.
            
            $default = sanitiesForTinyMCE( $default );


            $val = "<textarea class=\"editable\" \
                id=\"$inputId\" name=\"$keyName\" > $default </textarea>";
            $val .= "<script>
                tinymce.init( { selector : '#" . $inputId . "'
                        , init_instance_callback: \"insert_content\"
                    } );
                function insert_content( inst ) {
                    inst.setContent( '$default' );
                }
                </script>";
        }
        else if( strcasecmp( $ctype, 'date' ) == 0 )
           $val = "<input class=\"datepicker\" name=\"$keyName\" value=\"$default\" />";
        else if( strcasecmp( $ctype, 'datetime' ) == 0 )
           $val = "<input class=\"datetimepicker\" name=\"$keyName\" value=\"$default\" />";
        else if( strcasecmp( $ctype, 'time' ) == 0 )
           $val = "<input id=\"timepicker\" name=\"$keyName\" value=\"$default\" />";

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
    if( $button_val == 'submit' )
        $buttonSym = "&#10003";

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
    , $display = array(), $multiple_select = FALSE 
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

    if( array_key_exists( 'first_name', $user ) )
        $text = $user['first_name'] . ' ' . $user[ 'last_name' ];

    if( $withEmail )
        if( array_key_exists( 'email', $user) && $user[ 'email' ] )
            $text .= " (" . $user['email'] . ")";

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
                            , init_instance_callback: "insert_content"
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
            <td>' . arrayToSelectList( $name, $supervisorIds , $supervisorText, FALSE, $selected );

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

function initUserMsg( )
{
    $msg = "<p> Dear " . loginToText( $_SESSION[ 'user' ] ) . "<p>";
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

function breakAt( $text, $width = 80 )
{
    $newTxt =  `echo '$text' | fold -w $width -s -`;
    return str_replace( '\n', '<br/>', $newTxt );
}

function awsToTable( $aws )
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

    $title = __ucwords__( $aws[ 'title' ]);
    $abstract = $aws[ 'abstract' ];

    $html = '<style type="text/css">
        .email { border:1px solid; } 
        .email tr td {background-color: ivory; } 
        </style>';
    $html .=  '<table style="width:600px;" class="email">
        <tr>
            <td>Speaker</td>
            <td>' . $speaker . '</td>
        </tr>
        <tr>
            <td>Supervisors</td>
            <td>' . implode( "<br/>", $supervisors ) . '</td>
        </tr>
        <tr>
            <td>Thesis Committee Members</td>
            <td>' . implode( "<br/>", $tcm) . '</td>
        </tr>
        <tr>
            <td>Title</td>
            <td>' . $title . '</td>
        </tr>
        <tr>
            <td>Abstract</td>
            <td>' . $abstract . '</td>
        </tr>
            
        </table>';

    return $html;

}

function closePage( )
{
    return "<a href=\"javascript:window.close();\">Close Window</a>";
}
?>
