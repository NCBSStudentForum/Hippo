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
  $table .= '<tr><td><small>NCBS Username</small> </td></tr> ';
  $table .= '<tr><td><input type="text" name="username" id="username" /> </td></tr>';
  $table .= '<tr><td><small>NCBS Password</small></td></tr>';
  $table .= '<tr><td> <input type="password"  name="pass" id="pass"> </td></tr>';
  $table .= '<tr><td> <input style="float: right" type="submit" name="response" value="Login" /> </td></tr>';
  $table .= '</table>';
  $table .= '</form>';
  return $table;
}

function eventTable( $date )
{
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
        if( count( $events ) == 0 and count($requests) == 0)
        {
            // Add a form to trigger adding event purpose.
            $html .= "<form method=\"post\" action=\"user_request.php\" >";
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
    $dt = 60; // Each segment is 15 minutes wide. 
    $html .= "<tr>";
    $html .= "<td><div style=\"width:100px\">$venueid</div></td>";
    for( $i = 0; $i < 12; $i++ )
    {
        $stepT = $i * $dt;
        $segTime = strtotime( "+ $stepT minutes", strtotime($startDay) );
        $html .= "<td>" . hourToHTMLTable( $date, $segTime, $venueid, 4 ) . "</td>";
        //if( ($i+1) % 6 == 0 )
            //$html .= '</tr><tr><td></td>';
    }
    $html .= "</tr>";
    $html .= '</table>';
    return $html;
}

// Convert an array to HTML
function arrayToTableHTML( $r, $tablename )
{
    $table = "<table class=\"$tablename\">";
    $keys = array_keys( $r );
    $vals = array_values( $r );
    $table .= "<tr>";
    foreach( $keys as $k )
        $table .= "<td>$k</td>";
    $table .= "</tr><tr>";
    foreach( $vals as $v )
        $table .= "<td>$v</td>";
    $table .= "</tr></table>";
    return $table;
}

function requestToHTML( $request )
{
    return arrayToTableHTML( $request, "request" );
}

// Welcome user div.
function userHTML( )
{
    $html = '<div class="user">';
    $html .= 'Welcome <font color="blue">' . $_SESSION['user'] . '</font>';
    $html .= '<table class="user">';
    $html .= '<tr><td>
            <a href="user_show_requests.php">My requests</a>
        </td><td>
            <a href="user_show_events.php">My events</a></td>
        </td></tr>';
    $html .= "</table>";
    $html .= '</div>';
    return $html;
}

function venuesToChekcButtons( $venues )
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


?>
