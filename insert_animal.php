<?php
include_once( 'is_valid_access.php' );
include_once( 'methods.php' );

$breederCages = getListOfCages( "breeder" );
?>

<h3> Inserting a new animal </h3>

<p> These are most basic attributes of the animal. Other options are available 
on <strong>Edit/Update animal</strong> page </p>

<form method="post" action="insert_animal_action.php" id="form_insert_animal" >
<table  id = "table_input" >
<tr>
    <td>ID of the animal*<br> <small> TODO: pattern </small> </td>
    <td> <input type="text" name="animal_id" value="" required > </td>
</tr>
<tr>
    <td>Name of the animal<br> <small> Optional</small> </td>
    <td> <input type="text" name="animal_name" value="" required > </td>
</tr>
<tr>
    <td>Date of birth*<br> <small> YYYY-MM-DD </small> </td>
    <td> <input type="date" name="animal_dob"  value="" required > </td>
</tr>
<tr>
    <td>Gender*</td>
    <td> 
        <input type="radio" name = "animal_gender" value="male" checked >male</input>
        <input type="radio" name = "animal_gender" value="male" >female</input>
        <input type="radio" name = "animal_gender" value="other" >other</input>
    </td>
</tr>

<tr>
    <td>Parent cage ID*<br><small>Animal was born in this cage</small></td> 
    <td> <?php echo cagesToHtml( $breederCages ); ?> </td>
</tr>

<tr>
    <td>Image of the animal </td>
    <td> <input type="file" name="animal_image" id="animal_image" value="" /> </td>
</tr>
<tr>
    <td>Comment </td>
    <td> 
        <textarea  rows="3" cols="40" name="animal_comment" form_id="form_insert_animal"> 
        </textarea>
    </td>
</tr>

</table>
    <input class="submit" type="submit" name="insert" value="Insert">
</form>

<form method="post" action="user.php">
    <input class="logout" type="submit" name="logout" value="Go Back">
</form>
