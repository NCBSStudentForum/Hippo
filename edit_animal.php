<?php

include_once( 'is_valid_access.php' );
include_once( 'sqlite.php' );
include_once( 'error.php' );
include_once( 'header.php' );
include_once( 'tables.php' );

$cages = getListOfCages( );

function change( $animal, $what )
{
    global $cages;
    $html = "<h3>Changing animal cage<h3>";
    $html .= "<p class=\"info\"> Old value of $what is already selected </p>";

    if( $what == "cage" )
        $oldval = __get__( $animal, 'current_cage_id', 'Unknown' );
    else if( $what ==  'strain' ) 
        $oldval = __get__( $animal, 'strain', 'Unknown' );
    else
        $oldval = 'Unassigned';

    $html .= "<table id=\"table_action\">";
    $html .= "<tr><td> New $what </td>";

    if( $what == "cage" )
        $html .= "<td>" . cagesToHtml( $cages, $default = $oldval ) . "</td>";

    if( $what == "strain" )
        $html .= "<td>" . strainsToHtml( ) . "</td>";

    $html .= '<td><input type="submit" name="update" value="Update ' . $what. '"></td></tr>';
    $html .= "</table>";
    return $html;
}

function updateHealth( $animal_id )
{
    $health = getHealth( $animal_id );

    $html = "<table id=\"table_action\">";
    $html .= ' <tr> 
            <td>Length</td> 
            <td> <input type="number" name="length" value="' . $health['length']. '">mm</input>
            </td> </tr> ';
    $html .= ' <tr> <td>Height</td> 
            <td> 
                <input type="number" name="height" value="' . $health['height'] . '">mm</input>
            </td> </tr> ';
    $html .= ' <tr> 
            <td>Weight</td> 
            <td> <input type="number" name="weight" value="' . $health['weight'] . '">gm</input> </td> 
        </tr> ';
    $html .= ' <tr> 
            <td>Died on</td> 
            <td> <input type="date" name="died_on" id="date_died_on" placeholder=" 
            '. $health['died_on'] . '"></input> </td> 
            <script type="text/javascript" charset="utf-8">
                var dt = new Date();
                var today = dt.getFullYear() + "-" + (dt.getMonth() + 1) + "-" + dt.getDate();
                //document.getElementById("date_died_on").value = today;
            </script>
        </tr> ';
    $html .= ' <tr> 
            <td>Reason of death</td> 
            <td> <textarea type="text" name="deadly_reason">'. $health['deadly_reason'] . '</textarea> </td> 
        </tr> ';
    $html .= '<tr> <td></td> <td>
        <input style="float: right" type="submit" name="update" value="Update Health">
        </td></tr>';
    $html .= "</table>";
    return $html;
}

if( $_POST && $_POST[ 'animal_id']  )
{
    $animal = getAnimalWithId( $_POST['animal_id'] );
    $strain = $animal['strain'];

?>

<h3> Editing/Updating animal </h3>

<?php
    echo animalToHTMLTable( $_POST['animal_id'], $show_genotype = false );
?>


<!-- Here is default form which sends to edit_animal_action.php file -->
<form method="post" action="edit_animal_action.php">

<?php

    switch( strtolower( trim($_POST['response']) ) )
    {

        // Everything is turned into lower case
    case "assign/change cage":
        echo change( $animal, "cage" );
        break;

    case "record genotype":
        echo change( $animal, "strain" );
        break;

    case "update health":
        echo "<h3>Updating health .. </h3>";
        echo "<p class=\"info\"> Old values have been selected</p>";
        echo updateHealth( $animal['id'] );
        break;

    default:
        echo printWarning( "Unsupported/unimplemented response " . $_POST["response"] );
        break;

    }

    // Need to send animal_id.
    echo '<input type="hidden" name="animal_id" value="' . $animal['id'] . '" />';
?>
</form>

<form action="user.php">
<input class="logout" type="submit" name="goback" value="Go Back">
</form>

<?php
}
else
{
    echo printInfo( "You did not select any animal");
    echo goBackToPageLink( "user.php" );
}

?>
