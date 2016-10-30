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
    $html = '<table class="request">';
    $html .= '<input type="hidden" name="requestId" value="'.$r['id'].'">';
    $html .= "<tr>";
    $html .= "<td>" . $r['user'] . "</td>";
    $html .= "<td colspan=\"20\">" . $r['title'] . "</td>";
    $html .= "<td class=\"eventvenue\">" . $r['venue'] . "</td>";
    $html .= "<td class=\"eventtime\">" . $r['start_time'] . " to " . $r['end_time'] . " on " . $r['date'] . "</td>";
    $html .= "<td>" . $r['repeat_pat'] . "</td></tr>";
    $html .= '</table>';
    return $html;
}


?>
