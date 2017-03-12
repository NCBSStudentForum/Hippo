<?php

include_once 'database.php';
include_once 'tohtml.php';
include_once 'check_access_permissions.php';

mustHaveAllOfTheseRoles( Array( 'ADMIN' ) );

echo userHTML( );

$default = array( );
$action = 'Add';

$faculty = getFaculty( );
$facultyMap = array( );
foreach( $faculty as $fac )
    $facultyMap[ $fac[ 'email' ] ] = $fac;

$facultyEmails = array_keys( $facultyMap );

?>

<script type="text/javascript" charset="utf-8">
$( function() {
    var emails = <?php echo json_encode( $facultyEmails ) ?>;
    $( "#faculty" ).autocomplete( { source : emails }); 
    $( "#faculty" ).attr( "placeholder", "autocomplete" );
});
</script>

<?php

echo '<form method="post" action="">
    Email of facutly <input id="faculty" name="faculty_email">
    <button type="submit" name="response" value="search">Search</button>
    </form>';

if( array_key_exists( 'response', $_POST ) )
{
    $faculty = getTableEntry( 'faculty', 'email', array( 'email' => $_POST['faculty_email'] ) );
    $default = array_merge( $default, $faculty );
    $action = 'Update';
}

echo '<br/><br/>';

echo '<form method="post" action="admin_manages_faculty_submit.php">';
echo dbTableToHTMLTable( 'faculty'
    , $default
    , array( 'email', 'first_name', 'middle_name', 'last_name'
    , 'status', 'affiliation', 'url' ), $action
);
echo "</form>";



echo "<h3>Update existing faculty</h3>";

//foreach( $faculty as $fac )
//{
//    echo '<form method="post" action="admin_manages_faculty_submit.php">';
//    echo dbTableToHTMLTable( 'faculty', $fac
//        ,  array( 'first_name', 'middle_name', 'last_name'
//        , 'status', 'url', 'affiliation' ), 'edit' );
//    echo '</form>';
//}

echo goBackToPageLink( "admin.php", "Go back" );

?>
