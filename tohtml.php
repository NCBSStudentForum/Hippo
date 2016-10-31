<?php 

include_once 'database.php';

function loginForm()
{
  $conf = $_SESSION['conf'];
  /* Check if ldap server is alive. */
  $table = "";
  $table .= '<form action="login.php" method="post">';
  $table .= '<table id="table_login_main" style="float: left">';
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

// $day is used to check if this day and hour, something is booked.
function hourToHTMLTable( $day, $hour, $venue, $section = 4 )
{
    $tableName = "<font size=\"1\">$venue</font>";
    $tableTime = "<font size=\"1\" >" . date('H:i', $hour) . "</font>";
    $html = "<table class=\"hourtable\">";
    $html .= "<tr><td colspan=\"$section\"> $tableName $tableTime </td></tr>";

    $html .= "<tr>";
    for( $i = 0; $i < $section; $i++) 
    {
        $stepT = $i * 60 / $section;
        $segTime = strtotime( "+ $stepT minutes", $hour );
        // Check  for events at this venue. If non, then display + (addEvent) 
        // button else show that this timeslot has been booked.
        $events = eventAtThisVenue( $venue, $day, $segTime );
        if( count( $events ) == 0 )
            $html .= "<td><button id=\"button_add_event\" name=\"add_event\" value=\"$segTime\">+</button></td>";
        else
            $html .= "<td>E</td>";
    }
    $html .= "</tr></table>"; 
    return $html;
}

// Convert a event into a nice looking html line.
function eventLineHTML( $day )
{

    $html = '<table class="eventline">';
    $startDay = '8:00';
    $dt = 60; // Each segment is 15 minutes wide. 
    $venues = getVenues( );
    foreach( $venues as $venue )
    {
        $html .= "<tr>";
        for( $i = 0; $i < 12; $i++ )
        {
            $stepT = $i * $dt;
            $segTime = strtotime( "+ $stepT minutes", strtotime($startDay) );
            $html .= "<td>" . hourToHTMLTable( $day, $segTime, $venue['id'], 4 ) . "</td>";
        }
        $html .= "</tr>";
    }
    $html .= '</table>';
    return $html;
}

?>
