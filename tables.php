<?php 

include_once 'sqlite.php';

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

function requestsToHTMLTable( $requests )
{
    $html = '<table class="request">';
    foreach( $requests as $r )
    {
        $html .= '<input type="hidden" name="requestId" value="'.$r['id'].'">';
        $html .= "<tr>";
        $html .= "<td>" . $r['requestBy'] . "</td>";
        $html .= "<td colspan=\"20\">" . $r['title'] . "</td>";
        $html .= "<td color=\"blue\">" . $r['venue'] . "</td>";
        $html .= "<td>" . $r['startOn'] . " to " . $r['endOn'] . "</td>";
        $html .= "<td>" . $r['repeatPat'] . "</td></tr>";
        $html .= '<tr>';
        $html .= "<td><input type=\"text\" placeholder=\"comment\" value=\"\"></td>";
        $html .= '<td><input name="response" type="radio" value="approve" checked>Approve</td>';
        $html .= '<td><input name="response" type="radio" value="reject">Reject </td>';
        $html .= '<td><input type="submit" value="Submit">  </td>';
        $html .= "</tr>";
    }

    $html .= '</table>';
    return $html;
}


?>
