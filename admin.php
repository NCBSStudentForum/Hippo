
<?php

include_once( 'methods.php' );
include_once( 'tohtml.php' );
include_once( 'database.php' );
include_once( 'check_access_permissions.php' );

// Get logins. We'll use them to autocomplete the list of users while modifying
// the privileges.
$logins = getLogins( );

?>

<!-- Script to autocomplete user -->
<script>
var logins = <?php echo json_decode( $logins ); ?>;
$( "autocomplete_user" ).autocomplete( { source : logins }); 
</script>


<?php
echo userHTML( );

if( ! requiredPrivilege( 'ADMIN' ) )
{
    echo printWarning( "You are not listed as ADMIN" );
    goToPage( "index.php" );
    exit( 0 );
}

echo "<h3>Hello admin</h3>";

echo "<table class=\"show_user\">";
echo '
    <tr>
        <td>Synchronize public calendar</td>
        <td>
            <a href="' . appRootDir( ) . '/admin_synchronize_public_calendar.php">
                Synchronize public calendar </a>
        </td>
    </tr>
    </table>';

echo '<h3>User management</h3>';
echo "<table class=\"show_user\">";
echo '
    <tr>
        <td>Add or remove privileges of users</td>
        <td>
            <form method="post" action="admin_modify_user_privileges.php">
            <input id="autocomplete_user" name="login">
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

