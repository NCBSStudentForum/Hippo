<?php 

include_once( "header.php" );
include_once( "methods.php" );
include_once( 'tohtml.php' );
include_once( "check_access_permissions.php" );

mustHaveAnyOfTheseRoles( Array( 'USER' ) );

echo userHTML( );

echo "<h3>Add a missing AWS entry</h3>";

echo printInfo( " 
    If you can't find your supervior(s) and/or thesis committee member(s) in selection list,
        please create a entry for them <a href=\"" . appRootDir() . 
        "/user_add_supervisor.php\">HERE</a>" 
    );

// Now create an entry
$supervisors = getSupervisors( );
$id = Array( );
$text = Array( );
foreach( $supervisors as $supervisor )
{
    array_push( $id, $supervisor['email'] );
    $text[ $supervisor['email'] ] = $supervisor['first_name' ] . ' ' . $supervisor['last_name'];
}

$supervisorHTML = arrayToSelectList( "supervisors", $id, $text, TRUE );
$tcmHTML = arrayToSelectList( "tcm_members", $id, $text, TRUE );

echo "<form method=\"post\" action=\"user_add_aws_submit.php\">";
echo "<table class=\"input\">";
echo '
    <tr>
        <td>Title</td>
        <td><input type="text" class="long" name="title" value="" /></td>
    </tr>
    <tr>
    <td>Abstract <br><small>This is plain text.
        For best results, consider copy/paste HTML here. </small></td>
        <td><textarea name="abstract" rows="10" cols="40"></textarea></td>
    </tr>
    <tr>
        <td>Supervisor(s) <br><small>Select at least 1</small></td>
        <td>' . $supervisorHTML .  '</td>
    </tr>
    <tr>
        <td>Thesis Committee Member(s)<br><small>Select at least 1</small></td>
        <td>' . $tcmHTML .  '</td>
    </tr>
    <tr>
        <td>Date</td>
        <td><input class="datepicker" type="date" name="date" id="" value="" /></td>
    </tr>
    <tr>
        <td>Time</td>
        <td><input class="timepicker" name="time" id="" value="16:00" /></td>
    </tr>
    <tr>
        <td></td>
        <td><button class="submit" name=\"response\" value="submit">Submit</button></td>
    </tr>
    ';
echo "</table>";
echo "</form>";


echo goBackToPageLink( "user.php", "Go back" );

?>
