<?php
include_once 'header.php';
include_once( 'check_access_permissions.php' );
include_once( 'tohtml.php' );

mustHaveAllOfTheseRoles( Array( 'ADMIN' ) );

// Get logins. We'll use them to autocomplete the list of users while modifying
// the privileges.
$logins = getLoginIds( );

?>

<!-- Script to autocomplete user -->
<script>
$(function() {
    var logins = <?php echo json_encode( $logins ); ?>;
    $( "#autocomplete_user" ).autocomplete( { source : logins }); 
});
</script>


<?php
echo userHTML( );

if( ! requiredPrivilege( 'ADMIN' ) )
{
    echo printWarning( "You are not listed as ADMIN" );
    goToPage( "index.php" );
    exit( 0 );
}


echo "<h2>Hello admin</h2>";


echo '<h3>User management</h3>';
echo "<table class=\"show_user\">";
echo '
    <tr>
        <td>Edit user</td>
        <td>
            <form method="post" action="admin_modify_user_privileges.php">
            <input id="autocomplete_user" name="login" placeholder="I will autocomplete " >
            <button name="response" value="edit">Add or remove privileges</button>
            </form>
        </td>
    </tr>
    ';

echo "</table>";

echo "<h3>Database management </h3>";

echo '
    <table class="show_user">
        <tr>
            <td>Update/Edit list of principal investigators</td>
            <td>
                <a href="admin_sync_faculty.php">Synchornize faculty database</a>
            </td>
        </tr>
    </table>
    ';

?>

