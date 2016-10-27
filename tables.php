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

function animalToHTMLTable( $animal_id, $show_genotype = true, $show_health = true )
{
    $animal = getAnimalWithId( $animal_id );
    __log__( 'Showing animal ' . implode( ',', $animal ) );
    $html = '';
    $html .= "<table class=\"info\" >
        <tr> <td>Animal Id</td>";
    $html .=  "<td>" . $animal['id'] . "</td>   
            </tr> <tr> <td >Name</td> <td>" . $animal['name'] . " </td> </tr>";
    $html .= "<tr>
            <td >Gender</td> 
            <td>" . $animal['gender'] .  "</td> </tr>";
    $html .= " <tr> <td>Strain</td> 
            <td> " . $animal['strain'] . "</td> </tr>";
    $html .= "<tr> <td >Current cage ID</td> <td> " . $animal['current_cage_id'] 
             . " </td> </tr>";
    $html .= "<tr> <td>Date of birth</td> <td> " 
            .  $animal['dob'] . " </td> </tr>";
    $html .= "<tr> <td>Age</td> <td> " . ageInDays( $animal['dob'] ) 
        . " <font color=\"blue\"> " .  
        strtoupper($animal['status']) . " </font> " . " </td> </tr> ";

    $html .=  "<tr> <td>Born in cage with ID</td><td> " 
        . $animal['parent_cage_id'] . " </td> </tr> ";
    $html .= "</table> <br />";
    if( $show_genotype )
    {
        $html .= " <table  class=\"info\">";
        $html .= "<tr> <td>Is transgenic</td> <td> " .  
                $animal['is_transgenic'] . " </td> </tr>";
        $html .= "<tr> <td>Genotype done on</td> <td> " .
            $animal['genotype_done_on'] . " </td> </tr> ";

        $html .= " <tr> <td>Genotype done by</td> <td>" .  $animal['genotype_done_by'] . " </td> </tr>";
        $html .= " </table> <br />";
    }

    if( $show_health )
    {
        $html .= "<table class=\"info\" >";
        $html .= "<tr> <td>Weight</td> <td> " . $animal['weight'] . "gm </td> </tr>";
        $html .= "<tr> <td>Length</td> <td> " .  $animal['length'] . " mm</td> </tr> <tr>";
        $html .= " <td>Height</td> <td> " .  $animal['height'] . " mm</td> </tr>";
        $html .= " <tr> <td>Condition</td> <td> " .  $animal['condition'] . "</td> </tr>";

        if( $animal['status'] != "alive" )
        {
            $html .= '<tr><td><font color="blue">Died on</strong></td>';
            $html .= '<td>' . $animal['died_on'] . '</td>';
            $html .= '</tr>';
            $html .= '<tr><td>Deadly reason</td>';
            $html .= '<td>' . $animal['deadly_reason'] . '</td>';
            $html .= '</tr>';
        }
        $html .= "</table> <br />";

        //$html .= "<table  class=\"info\" >";
        //$html .= "<tr> TODO: Write log </tr> </table>";
    }

    return $html;
}


?>
