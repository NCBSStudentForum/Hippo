<?php 
include_once('methods.php');
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

function eventTable( $date )
{
}

function prettify( $string )
{
    // Replace _ with space.
    $string = str_replace( "_", " ", $string );

    // Uppercase first char.
    $string = ucfirst( $string );
    return $string;
}

function requestsToHTMLReviewForm( $requests )
{
    $html = '<table class="request">';
    foreach( $requests as $r )
    {
        $html .= "<table> <tr> <td>";
        $html .= requestToHTMLTable( $r );
        $html .= "</td><td>";
        $html .= '<input type="submit" name="response" value="Review"> </td></tr>';
    }

    $html .= '</table>';
    return $html;
}


function requestToHTMLTable( $r )
{
    $date = $r['date'];
    $day = date( 'l', strtotime($date) );
    $on = $day . ' ' . $date;
    $id = $r['gid'] . '.' . $r['rid'];
    $html = '<table class="request">';
    $html .= '<input type="hidden" name="gid" value="'.$r['gid'].'">';
    $html .= '<input type="hidden" name="rid" value="'.$r['rid'].'">';
    $html .= "<tr>";
    $html .= "<td>" . $id .  "</td>";
    $html .= "<td>" . $r['user'] . "</td>";
    $html .= "<td colspan=\"20\">" . $r['title'] . "</td>";
    $html .= "<td class=\"eventvenue\">" . $r['venue'] . "</td>";
    $html .= "<td class=\"eventtime\">" . 
        $r['start_time'] . " to " . $r['end_time'] . "<br>" . $on . 
        "</td>";
    $html .= '</table>';
    return $html;
}

// Return a short description of event.
function eventToText( $event )
{
    $html = 'By ' . $event['user'] . ', ';
    $html .= '';
    $html .= $event['short_description'];
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
    $html .= '<tr><td><small>WHAT</small></td><td>' . $event['short_description']
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

        if( count( $events ) == 0 && count($requests) == 0)
        {
            // Add a form to trigger adding event purpose.
            $html .= "<form method=\"post\" action=\"user_submit_request.php\" >";
            $html .= "<td>";
            if( $segDateTime >= strtotime( 'now' ) )
            {
                $html .= "<button class=\"add_event\" name=\"add_event\" value=\"$segTime\">+</button>";
            }
            else
            {
                $html .= "<button class=\"add_event_past\" name=\"add_event\" value=\"$segTime\" disabled></button>";

            }
            $html .= "</td>";
            // And the hidden elements to carry the values to the action page.
            $html .= '<input type="hidden" name="start_time" value="'. $segTime . '">';
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
function eventLineHTML( $date, $venueid )
{
    $venue = getVenueById( $venueid );
    $html = '<table class="eventline">';
    $startDay = '8:00';
    $dt = 60; 
    $html .= "<tr>";
    $html .= "<td><div style=\"width:100px\">$venueid</div></td>";
    for( $i = 0; $i < 12; $i++ ) // Day is 12 hours long.
    {
        $stepT = $i * $dt;
        $segTime = strtotime( $startDay ) + 60 * $stepT;
        // Each hour has 15 minutes segment. FOr each segment hourToHTMLTable 
        // create a block.
        $html .= "<td>" . hourToHTMLTable( $date, $segTime, $venueid, 4 ) . "</td>";
    }
    $html .= "</tr>";
    $html .= '</table>';
    return $html;
}

// Convert an array to HTML
function arrayToTableHTML( $array, $tablename, $background = NULL, $tobefilterd = Array() )
{
    if( $background )
        $background = "style=\"background:$background;\"";
    else
        $background = '';
    
    $table = "<table class=\"show_$tablename\" $background ;>";
    $keys = array_keys( $array );
    $toDisplay = Array();
    $table .= "<tr>";
    foreach( $keys as $k )
        if( ! in_array( $k, $tobefilterd ) )
        {
            $kval = prettify( $k );
            $label = strtoupper( $kval );
            $table .= "<td class=\"db_table_fieldname\">$label</td>";
            array_push( $toDisplay, $array[$k] );
        }

    // Also set the content as div element which can be formatted using css
    $table .= "</tr><tr>";
    foreach( $toDisplay as $v )
        $table .= "<td><div class=\"cell_content\">$v</div></td>";
    $table .= "</tr></table>";
    return $table;
}

// Convert an array to HTML table (vertical)
function arrayToVerticalTableHTML( $array, $tablename, $background = NULL, $tobefilterd = Array() )
{
    if( $background )
        $background = "style=\"background:$background;\"";
    else
        $background = '';
    
    $table = "<table class=\"show_$tablename\" $background ;>";
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
    $html .= "<tr><td><a href= \"" . appRootDir( ) . "/user.php\">Main page</a>";
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
    * @brief Convert a database table schema to HTML table. 
    *
    * @param $tablename
    * @param $defaults  . Populate table with these default values. If not found 
    * in this array, use schema defauls.
    * @param $editables
    *
    * @return 
 */

function dbTableToHTMLTable( $tablename, $defaults=Array(), $editables = Array() )
{
    $html = "<table class=\"editable_$tablename\">";
    $schema = getTableSchema( $tablename );

    foreach( $schema as $col )
    {
        $keyName = $col['Field'];
        $ctype = $col['Type'];

        $readonly = True;
        if( in_array($keyName , $editables ) )
            $readonly = False;

        // Add row to table
        $html .= "<tr><td class=\"db_table_fieldname\"> " . 
            strtoupper(prettify( $keyName )) . "</td>";

        $default = __get__( $defaults, $keyName, $col['Default'] );
        $val = "<input class=\"editable\"
            name=\"$keyName\" type=\"text\" value=\"$default\" />";

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
            $val = "<select multiple name=\"$keyName\">";
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
        else if( strcasecmp( $ctype, 'text' ) == 0 )
        {
            $val = "<textarea class=\"editable\" id=\"ckeditor\" name=\"$keyName\" >$default </textarea>";
            $val .= "<script> CKEDITOR.replace('ckeditor') </script>";
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

    // If some fields are editable then we need a submit button as well.
    if( count( $editables ) > 0 )
    {
        $html .= "<tr style=\"background:white;\"><td></td><td>";
        $html .= "<button style=\"float:right\" value=\"submit\" name=\"response\">Submit</button>";
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
    , $display = Array(), $multiple_select = FALSE 
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

?>
