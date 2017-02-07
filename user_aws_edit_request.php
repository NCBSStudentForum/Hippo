<?php 

// User submit a request to change some description in his/her AWS. The request 
// must be approved by AWS_ADMIN.

include_once( "header.php" );
include_once( "methods.php" );
include_once( 'tohtml.php' );
include_once( "check_access_permissions.php" );

mustHaveAnyOfTheseRoles( Array( 'USER' ) );

echo userHTML( );

?>

<script src="ckeditor/ckeditor.js"> </script>

<?php

// If we have come here by a post request, get the speaker and date and fetch 
// the aws as default value.

$default = Array( );
$awsId = 0;
if( isset( $_POST['id'] ) )
{
    $awsId = $_POST['id'];
    $default = getAwsById( $awsId );
}

echo "<h3>Edit or add AWS entry</h3>";

echo alertUser( "Supervisor 1 must be a local faculty. Others can be from outside." );

// Now create an entry
$supervisors = getSupervisors( );
$supervisorIds = Array( );
$supervisorText = Array( );
foreach( $supervisors as $supervisor )
{
    array_push( $supervisorIds, $supervisor['email'] );
    $supervisorText[ $supervisor['email'] ] = $supervisor['first_name']
        .  ' ' . $supervisor[ 'last_name' ] ;
}

echo "<form method=\"post\" action=\"user_aws_edit_request_submit.php\">";
echo "<table class=\"input\">";
echo '
    <tr>
        <td>Title</td>
        <td><input type="text" class="long" name="title" value="' 
            . __get__( $default, 'title', '') . '" /></td>
    </tr>
    <tr>
        <td>Abstract </td>
        <td><textarea id="abstract" name="abstract" rows="10" cols="40">' 
            . __get__( $default, 'abstract', '' ) . '</textarea>
            <script> CKEDITOR.replace( "abstract" ); </script>
        </td>
    </tr>';

$addMissingUrl = '<a target="_blank" href="user_add_supervisor.php">Add missing member</a>';
for( $i = 1; $i <= 2; $i++ )
{
    $name = "supervisor_$i";
    $selected = __get__( $default, $name, "" );
    echo '
    <tr>
        <td>Supervisor ' . $i . '<br></td>
        <td>' . arrayToSelectList( $name, $supervisorIds , $supervisorText, FALSE, $selected );
    if( $i == 2 )
        echo $addMissingUrl;
    echo '</td> </tr>';
}
for( $i = 1; $i <= 4; $i++ )
{
    $name = "tcm_member_$i";
    $selected = __get__( $default, $name, "" );
    echo '
    <tr>
        <td>Thesis Committee Member ' . $i . '<br></td>
        <td>' . arrayToSelectList( $name, $supervisorIds , $supervisorText, FALSE, $selected) 
        . $addMissingUrl . '</td>';
    echo '</tr>';

}
    echo '
    <tr>
        <td>Date</td>
        <td><input class="datepicker" type="date" name="date" id="" value="' . 
            __get__($default, 'date', '' ) . '" /></td>
    </tr>
    <tr>
        <td>Time</td>
        <td><input class="timepicker" name="time" id="" value="16:00" /></td>
    </tr>
    <tr>
        <td></td>
        <td>
            <input  name="awsid" type="hidden" value="' . $awsId . '"  />
            <button class="submit" name=\"response\" value="submit">Submit</button>
        </td>
    </tr>
    ';
echo "</table>";
echo "</form>";


echo goBackToPageLink( "user.php", "Go back" );

?>
