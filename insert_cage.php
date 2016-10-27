<?php
include( 'is_valid_access.php' );

function cage_types_to_html( )
{
    $strain = $_SESSION['conf']['cage']['type'];
    foreach( $strain as $value )
        $html .= '<option value="'.$value.'">'.$value. '</option>';
    return $html;
}
?>

<h3> Inserting a new cage </h3>
<form method="post" id="form_insert_cage" action="insert_cage_action.php">

<table  id = "table_input" >
<tr>
    <td>ID of the cage </td>
    <td> <input type="text" name = "cage_id" value="" placeholder="1600xxxx" > </td>
</tr>
<tr>
    <td>Location </td>
    <td> <input type="text" name="cage_location" value="" > </td>
</tr>
<tr>
    <td>Type </td>
    <td> <select name="cage_type"> <?php echo cage_types_to_html(); ?> </select> </td>
</tr>
<tr>
    <td>Comment </td>
    <td> 
        <textarea  rows="3" cols="40" name="cage_comment" form_id="form_insert_cage"> 
        </textarea>
    </td>
</tr>
<tr>
    <td>Barcode Image</td>
    <td> 
        <input type="file" name="cage_barcode" > </input>
    </td>
</tr>

</table>

<input class="submit" type="submit" name="insert" value="Insert">

</form>

<form method="post" action="user.php">
    <input class="logout" type="submit" name="logout" value="Go Back">
</form>
